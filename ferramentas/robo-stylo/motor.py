# -*- coding: utf-8 -*-
"""
Motor do robô de cadastro da Móveis Stylo.

Lê as fichas (ficha.md + fotos, uma pasta por produto), confere tudo antes de
mexer no site, prepara as fotos e cadastra cada produto na tela
Produtos › Importar ficha do FlowManeger, usando um navegador com o login salvo.

A janela (janela.pyw) usa este arquivo; ele também roda sozinho para testes:
    python motor.py --conferir
    python motor.py --cadastrar
"""
import configparser
import csv
import json
import os
import re
import shutil
import sys
import tempfile
import time
import unicodedata
import hashlib
import urllib.error
import urllib.parse
import urllib.request
import uuid
from dataclasses import dataclass, field
from datetime import datetime
from pathlib import Path

try:
    from PIL import Image, ImageOps
except ImportError:  # sem Pillow as fotos vão como estão
    Image = None

BASE = Path(__file__).resolve().parent
FOTO_EXTS = {".jpg", ".jpeg", ".png", ".webp"}
FOTO_CONVERTER = {".bmp", ".gif", ".tif", ".tiff", ".jfif"}
FICHA_NOMES = ("ficha.md", "ficha.txt", "ficha.json")
LIMITE_SITE = 4 * 1024 * 1024  # a tela aceita até 4 MB por foto


class Parar(Exception):
    """O usuário apertou Parar."""


# ------------------------------------------------------------ configuração

PADRAO = {
    "site": "https://flowmaneger.com",
    "pasta_produtos": str(BASE / "produtos"),
    "mover_feitos": "sim",
    "navegador_visivel": "sim",
    "lado_maximo_foto": "1600",
    "tamanho_maximo_foto_kb": "1500",
    "pausa_entre_produtos": "1",
    "tema": "claro",
    "margem_padrao": "30",
    "valor_hora": "",
    "frete_estimado": "",
    "gemini_chave": "",
    "gemini_modelo": "gemini-2.5-flash",
    "gemini_modelo_imagem": "gemini-3.1-flash-image",
    "canva_link": "https://www.canva.com/ai",
    "pasta_downloads": "",
    "marca_dagua": "Móveis Stylo",
    "preco_de_pct": "",
    "folga_caixa": "4",
    "prazo_padrao": "15",
    "completar_ao_importar": "sim",
    "atualizar_sozinho": "sim",
    "atualizar_repo": "Marco-Bubola/FlowManeger",
    "atualizar_ref": "claude/project-thread-l778hs",
    "atualizar_pasta": "ferramentas/robo-stylo",
    "atualizar_token": "",
    "copia_diaria": "sim",
    "pasta_copias": "",
    "copias_guardar": "14",
}


def ler_config():
    cfg = configparser.ConfigParser()
    cfg.read_dict({"robo": PADRAO})
    arq = BASE / "config.ini"
    if arq.exists():
        cfg.read(arq, encoding="utf-8")
    if not cfg["robo"].get("pasta_produtos", "").strip():
        cfg["robo"]["pasta_produtos"] = str(BASE / "produtos")
    if not cfg["robo"].get("site", "").strip():
        cfg["robo"]["site"] = PADRAO["site"]
    return cfg


def salvar_config(cfg):
    with open(BASE / "config.ini", "w", encoding="utf-8") as f:
        cfg.write(f)


def sim(valor):
    return str(valor).strip().lower() in ("sim", "s", "1", "true", "yes", "verdadeiro")


# ------------------------------------------------------------ ficha

@dataclass
class Ficha:
    pasta: Path
    arquivo: Path = None
    dados: dict = field(default_factory=dict)
    erros: list = field(default_factory=list)
    avisos: list = field(default_factory=list)
    fotos: list = field(default_factory=list)       # Path das fotos usadas pela ficha
    faltando: list = field(default_factory=list)    # nomes citados na ficha que não estão na pasta
    sobrando: list = field(default_factory=list)    # fotos da pasta que a ficha não cita
    status: str = "pronta"                          # pronta | erro | feita | ja | falhou
    mensagem: str = ""
    resultado: list = field(default_factory=list)

    @property
    def nome(self):
        return str(self.dados.get("nome") or self.pasta.name)

    @property
    def variacoes(self):
        v = self.dados.get("variacoes")
        return [x for x in v if isinstance(x, dict)] if isinstance(v, list) else []

    @property
    def ativas(self):
        """As variações ligadas (as desligadas ficam na ficha, mas não sobem para o site)."""
        return [v for v in self.variacoes if v.get("ativa", True) is not False]

    @property
    def pronta(self):
        return not self.erros and self.status in ("pronta", "falhou")

    @property
    def todas_fotos(self):
        """As fotos citadas (na ordem da ficha) e depois as outras da pasta."""
        return list(self.fotos) + [self.pasta / n for n in self.sobrando]

    @property
    def modificada(self):
        try:
            return (self.arquivo or self.pasta).stat().st_mtime
        except OSError:
            return 0


def extrair_json(texto):
    """O último bloco ```json``` da ficha (ou o texto inteiro, se for só JSON)."""
    texto = texto.lstrip("﻿")
    blocos = re.findall(r"```(?:json)?\s*(\{.*?\})\s*```", texto, flags=re.S)
    if blocos:
        bruto = blocos[-1]
    else:
        m = re.search(r"\{.*\}", texto, flags=re.S)
        if not m:
            raise ValueError("Não achei o bloco JSON na ficha. Peça para a IA responder no formato da Parte 2 do questionário.")
        bruto = m.group(0)
    bruto = re.sub(r",\s*([}\]])", r"\1", bruto)  # vírgula sobrando (erro comum de IA)
    try:
        return json.loads(bruto)
    except json.JSONDecodeError as e:
        linha = bruto.splitlines()[e.lineno - 1].strip() if e.lineno - 1 < len(bruto.splitlines()) else ""
        raise ValueError(f"O JSON da ficha tem erro na linha {e.lineno}: {e.msg}. Trecho: {linha[:80]}") from None


def numero(v):
    if isinstance(v, (int, float)) and not isinstance(v, bool):
        return float(v)
    s = re.sub(r"[^\d,.\-]", "", str(v or ""))
    if s in ("", "-"):
        return 0.0
    if "," in s:
        s = s.replace(".", "").replace(",", ".")
    try:
        return float(s)
    except ValueError:
        return 0.0


def achar_ficha(pasta):
    for n in FICHA_NOMES:
        if (pasta / n).exists():
            return pasta / n
    outros = sorted(p for p in pasta.iterdir() if p.is_file() and p.suffix.lower() in (".md", ".txt", ".json"))
    return outros[0] if outros else None


def ler_ficha(pasta):
    """Lê e confere a ficha de uma pasta. Nunca levanta erro: os problemas ficam em ficha.erros."""
    f = Ficha(pasta=Path(pasta))
    f.arquivo = achar_ficha(f.pasta)
    if not f.arquivo:
        f.erros.append("Falta o arquivo ficha.md nesta pasta.")
        f.status = "erro"
        return f
    try:
        f.dados = extrair_json(f.arquivo.read_text(encoding="utf-8", errors="replace"))
    except ValueError as e:
        f.erros.append(str(e))
        f.status = "erro"
        return f
    if not isinstance(f.dados, dict):
        f.erros.append("A ficha precisa ser um objeto JSON { ... }.")
        f.dados = {}
        f.status = "erro"
        return f

    d = f.dados
    if not str(d.get("nome") or "").strip():
        f.erros.append('Falta o "nome" do produto.')

    variacoes = f.variacoes
    if not variacoes:
        # Ficha sem lista: os campos soltos viram um produto só
        if d.get("venda") is not None:
            variacoes = [{k: d.get(k) for k in ("codigo", "custo", "venda", "preco_tabela", "estoque", "embalagem", "medidas_peca_cm", "fotos")}]
            d["variacoes"] = variacoes
        else:
            f.erros.append('Falta a lista "variacoes" (com pelo menos 1 item com "venda").')
    if len(variacoes) > 40:
        f.erros.append(f"São {len(variacoes)} variações; o máximo é 40.")

    multi = len(variacoes) > 1 or any(str(v.get("valor") or "").strip() for v in variacoes)
    desligadas = [v for v in variacoes if v.get("ativa", True) is False]
    if desligadas:
        variacoes = [v for v in variacoes if v.get("ativa", True) is not False]
        if not variacoes:
            f.erros.append("Todas as variações estão desligadas: ligue pelo menos uma para subir.")
        else:
            f.avisos.append(f"{len(desligadas)} variação(ões) desligada(s): ficam na ficha, mas não sobem.")
    codigos = {}
    sem_peso, sem_custo = [], []
    soma_custo = sum((numero(i.get("quantidade")) or 1) * numero(i.get("valor"))
                     for i in d.get("ficha_de_custo") or [] if isinstance(i, dict))
    for i, v in enumerate(variacoes, 1):
        rotulo = str(v.get("valor") or "").strip() or (f"variação {i}" if multi else "produto")
        if numero(v.get("venda")) <= 0:
            f.erros.append(f"{rotulo}: falta o preço de venda.")
        if v.get("custo") in (None, "") and soma_custo <= 0:
            sem_custo.append(rotulo)
        if multi and not str(v.get("valor") or "").strip():
            f.erros.append(f"Variação {i}: falta o \"valor\" (ex.: 30 cm).")
        cod = str(v.get("codigo") or "").strip().lower()
        if cod:
            if cod in codigos:
                f.erros.append(f"O código {v.get('codigo')} aparece duas vezes.")
            codigos[cod] = True
        emb = v.get("embalagem") or d.get("embalagem") or {}
        if not isinstance(emb, dict) or numero(emb.get("peso_kg")) <= 0:
            sem_peso.append(rotulo)

    if sem_custo:
        quais = "" if not multi else (" em " + ", ".join(sem_custo) if len(sem_custo) < len(variacoes) else " em nenhuma variação")
        f.avisos.append(f"Sem custo{quais} (entra R$ 0,00).")
    if sem_peso:
        quais = "" if not multi else (" em " + ", ".join(sem_peso) if len(sem_peso) < len(variacoes) else " em nenhuma variação")
        f.avisos.append(f"Sem peso da embalagem{quais} (o frete do Mercado Livre precisa).")
    if not str(d.get("codigo") or "").strip() and not any(str(v.get("codigo") or "").strip() for v in variacoes):
        f.avisos.append("Sem código: o site cria um código automático (ex.: VAR-1A2B3C). Dá para pôr o código no editor.")
    if sim(d.get("sob_encomenda")) and numero(d.get("prazo_dias")) <= 0:
        f.avisos.append('Sob encomenda sem "prazo_dias".')
    if multi and not str(d.get("variacao_por") or "").strip():
        f.avisos.append('Sem "variacao_por": entra como Tamanho.')

    # Fotos citadas x fotos da pasta (aceita o nome com ou sem extensão)
    na_pasta = {p.name.lower(): p for p in f.pasta.iterdir()
                if p.is_file() and p.suffix.lower() in FOTO_EXTS | FOTO_CONVERTER}
    por_stem = {}
    for p in na_pasta.values():
        por_stem.setdefault(p.stem.lower(), p)
    usadas = []
    for v in variacoes:
        for nome in v.get("fotos") or []:
            nome = str(nome).strip().replace("\\", "/").split("/")[-1]
            if not nome:
                continue
            p = na_pasta.get(nome.lower()) or por_stem.get(Path(nome).stem.lower())
            if p:
                if p not in usadas:
                    usadas.append(p)
            elif nome not in f.faltando:
                f.faltando.append(nome)
    f.fotos = usadas
    so_desligadas = set()
    for v in desligadas:
        for nome in v.get("fotos") or []:
            nome = str(nome).strip().replace("\\", "/").split("/")[-1]
            p = na_pasta.get(nome.lower()) or por_stem.get(Path(nome).stem.lower())
            if p and p not in usadas:
                so_desligadas.add(p)
    f.sobrando = sorted(p.name for p in na_pasta.values() if p not in usadas and p not in so_desligadas)
    if f.faltando:
        f.avisos.append("Fotos citadas na ficha que não estão na pasta: " + ", ".join(f.faltando))
    if not usadas:
        if f.sobrando:
            f.avisos.append("A ficha não cita as fotos: vão todas as da pasta, em ordem de nome.")
        else:
            f.avisos.append("Sem fotos: o produto entra sem foto.")

    f.status = "erro" if f.erros else "pronta"
    return f


IGNORAR = ("feitos", "_modelo", "_lixeira")


def listar_fichas(pasta_produtos, incluir_feitas=False):
    raiz = Path(pasta_produtos)
    raiz.mkdir(parents=True, exist_ok=True)
    fichas = []
    for p in sorted(raiz.iterdir(), key=lambda x: x.name.lower()):
        if p.is_dir() and p.name.lower() not in IGNORAR and not p.name.startswith("."):
            fichas.append(ler_ficha(p))
    if incluir_feitas and (raiz / "feitos").is_dir():
        for p in sorted((raiz / "feitos").iterdir(), key=lambda x: x.stat().st_mtime, reverse=True):
            if p.is_dir():
                fichas.append(ler_feita(p))
    return fichas


def assinatura_ficha(f):
    """O que vai para o site (textos, preços, medidas, fotos). O estoque fica de fora: atualizar não mexe nele."""
    try:
        dados = json.loads(json.dumps(f.dados))
        for v in dados.get("variacoes") or []:
            if isinstance(v, dict):
                v.pop("estoque", None)
        fotos = []
        for q in f.todas_fotos:
            try:
                fotos.append([q.name, q.stat().st_size])
            except OSError:
                fotos.append([q.name, 0])
        txt = json.dumps({"d": dados, "f": sorted(fotos)}, ensure_ascii=False, sort_keys=True)
        return hashlib.sha1(txt.encode("utf-8")).hexdigest()
    except Exception:
        return ""


def ler_feita(p):
    """Uma ficha da pasta feitos, com o que o site devolveu no cadastro (resultado.json).

    Se a ficha mudou depois do último envio, fica "editada": pronta para subir de novo."""
    f = ler_ficha(Path(p))
    f.status, f.mensagem, f.resultado = "feita", "Já cadastrada no site.", []
    arq = f.pasta / "resultado.json"
    try:
        info = json.loads(arq.read_text(encoding="utf-8"))
    except Exception:
        info = None
    if isinstance(info, dict):
        f.resultado = info.get("produtos") or []
        f.mensagem = info.get("mensagem") or f.mensagem
        atual = assinatura_ficha(f)
        if not info.get("assinatura") and atual:
            # fichas cadastradas antes desta versão: guarda como estão agora
            info["assinatura"] = atual
            try:
                arq.write_text(json.dumps(info, ensure_ascii=False, indent=2), encoding="utf-8")
            except OSError:
                pass
        elif atual and info.get("assinatura") != atual and not f.erros:
            f.status = "editada"
            f.mensagem = "Editada depois do cadastro: suba de novo para o site levar as mudanças."
    return f


# ------------------------------------------------------------ editar fichas

MODELO_VAZIO = {
    "versao": 1, "nome": "", "codigo": "", "categoria": "", "marca": "Móveis Stylo", "modelo": None,
    "material": "", "acabamento": "", "uso": "", "descricao": "", "o_que_vem_na_caixa": "", "montagem": "",
    "carga_maxima_kg": None, "garantia_meses": 3, "condicao": "novo", "codigo_barras": None,
    "sob_encomenda": False, "prazo_dias": None, "ficha_tecnica": [], "ficha_de_custo": [],
    "variacao_por": None,
    "variacoes": [{"valor": None, "codigo": "", "custo": None, "venda": None, "preco_tabela": None, "estoque": 0,
                   "medidas_peca_cm": {}, "embalagem": {}, "fotos": []}],
    "mercado_livre": {"link_referencia": None, "titulo_anuncio": "", "tipo_anuncio": "classico", "quantidade_anuncio": 10},
}


def texto_da_ficha(dados):
    nome = str(dados.get("nome") or "Produto")
    corpo = json.dumps(dados, ensure_ascii=False, indent=2)
    return f"# Ficha · {nome}\n\n```json\n{corpo}\n```\n"


def salvar_ficha(ficha, dados):
    """Grava a ficha (mantém o resto da pasta) e devolve a ficha relida."""
    arq = ficha.arquivo if ficha.arquivo and ficha.arquivo.suffix.lower() in (".md", ".txt") else ficha.pasta / "ficha.md"
    if ficha.arquivo and ficha.arquivo != arq and ficha.arquivo.exists():
        ficha.arquivo.unlink()
    arq.write_text(texto_da_ficha(dados), encoding="utf-8")
    return ler_ficha(ficha.pasta)


def nova_pasta(pasta_produtos, dados):
    destino = Path(pasta_produtos) / nome_de_pasta(dados)
    base, n = destino, 2
    while destino.exists():
        destino = base.with_name(f"{base.name} ({n})")
        n += 1
    destino.mkdir(parents=True)
    return destino


def nova_ficha(pasta_produtos, nome="Novo produto"):
    dados = json.loads(json.dumps(MODELO_VAZIO))
    dados["nome"] = nome
    destino = nova_pasta(pasta_produtos, dados)
    (destino / "ficha.md").write_text(texto_da_ficha(dados), encoding="utf-8")
    return destino


def excluir_ficha(ficha, pasta_produtos):
    """Não apaga: move para produtos\\_lixeira (dá para recuperar)."""
    lixo = Path(pasta_produtos) / "_lixeira"
    lixo.mkdir(exist_ok=True)
    destino = lixo / ficha.pasta.name
    if destino.exists():
        destino = lixo / f"{ficha.pasta.name} {datetime.now():%Y%m%d-%H%M%S}"
    shutil.move(str(ficha.pasta), str(destino))
    return destino


def duplicar_ficha(ficha, pasta_produtos):
    dados = json.loads(json.dumps(ficha.dados))
    dados["nome"] = f"{ficha.nome} (cópia)"
    dados["codigo"] = ""
    for v in dados.get("variacoes") or []:
        if isinstance(v, dict):
            v["codigo"] = ""
    destino = nova_pasta(pasta_produtos, dados)
    for p in ficha.todas_fotos:
        if p.exists():
            shutil.copy2(p, destino / p.name)
    (destino / "ficha.md").write_text(texto_da_ficha(dados), encoding="utf-8")
    return destino


def adicionar_fotos(pasta, arquivos):
    """Copia as fotos para a pasta da ficha (nome repetido ganha -2, -3...). Devolve os nomes."""
    nomes = []
    for a in arquivos:
        a = Path(a)
        if not a.is_file() or a.suffix.lower() not in FOTO_EXTS | FOTO_CONVERTER:
            continue
        destino = Path(pasta) / a.name
        if destino.exists() and destino.resolve() == a.resolve():
            nomes.append(a.name)
            continue
        n = 2
        while destino.exists():
            destino = Path(pasta) / f"{a.stem}-{n}{a.suffix}"
            n += 1
        shutil.copy2(a, destino)
        nomes.append(destino.name)
    return nomes


def remover_foto(pasta, nome):
    """Tira a foto da pasta (vai para a subpasta _removidas, não apaga)."""
    origem = Path(pasta) / nome
    if not origem.exists():
        return
    lixo = Path(pasta) / "_removidas"
    lixo.mkdir(exist_ok=True)
    destino = lixo / nome
    if destino.exists():
        destino = lixo / f"{origem.stem}-{datetime.now():%H%M%S}{origem.suffix}"
    shutil.move(str(origem), str(destino))


def nome_de_pasta(dados):
    cod = str(dados.get("codigo") or "").strip()
    nome = str(dados.get("nome") or "produto").strip()
    bruto = f"{cod} - {nome}" if cod else nome
    bruto = re.sub(r'[\\/:*?"<>|]', " ", bruto)
    return re.sub(r"\s+", " ", bruto).strip().rstrip(".")[:90] or "produto"


def criar_pasta_da_ficha(pasta_produtos, texto, fotos):
    """Para o botão "Colar ficha da IA": cria a pasta com ficha.md e copia as fotos."""
    dados = extrair_json(texto)
    destino = nova_pasta(pasta_produtos, dados)
    (destino / "ficha.md").write_text(texto.strip() + "\n", encoding="utf-8")
    for p in fotos:
        p = Path(p)
        if p.is_file():
            shutil.copy2(p, destino / p.name)
    return destino


# ------------------------------------------------------------ fotos

def preparar_fotos(ficha, pasta_tmp, lado_max=1600, kb_max=1500):
    """Cópias das fotos prontas para o site: viradas certas, até lado_max px e kb_max KB.
    Mantém o nome (o site acha a foto pelo nome, com ou sem extensão)."""
    fotos = ficha.fotos or [ficha.pasta / n for n in ficha.sobrando]
    prontas = []
    for p in fotos:
        destino = Path(pasta_tmp) / p.name
        if Image is None:
            if p.suffix.lower() not in FOTO_EXTS:
                raise ValueError(f"A foto {p.name} precisa ser JPG, PNG ou WEBP.")
            if p.stat().st_size > LIMITE_SITE:
                raise ValueError(f"A foto {p.name} passa de 4 MB.")
            shutil.copy2(p, destino)
            prontas.append(destino)
            continue
        try:
            with Image.open(p) as im:
                im = ImageOps.exif_transpose(im)
                grande = max(im.size) > lado_max
                pesada = p.stat().st_size > kb_max * 1024
                if not grande and not pesada and p.suffix.lower() in FOTO_EXTS:
                    shutil.copy2(p, destino)
                else:
                    if grande:
                        im.thumbnail((lado_max, lado_max), Image.LANCZOS)
                    if im.mode in ("RGBA", "LA", "P"):
                        fundo = Image.new("RGB", im.size, (255, 255, 255))
                        im = im.convert("RGBA")
                        fundo.paste(im, mask=im.split()[-1])
                        im = fundo
                    elif im.mode != "RGB":
                        im = im.convert("RGB")
                    destino = destino.with_suffix(".jpg")
                    qualidade = 88
                    im.save(destino, "JPEG", quality=qualidade, optimize=True)
                    while destino.stat().st_size > kb_max * 1024 and qualidade > 50:
                        qualidade -= 8
                        im.save(destino, "JPEG", quality=qualidade, optimize=True)
        except Exception as e:
            raise ValueError(f"Não consegui abrir a foto {p.name}: {e}") from None
        prontas.append(destino)
    return prontas


# ------------------------------------------------------------ site

class Site:
    """Navegador com o login da Móveis Stylo salvo (perfil próprio, separado do da Boticário)."""

    def __init__(self, base_url, visivel=True, avisar=print, parar=lambda: False):
        self.base = base_url.rstrip("/")
        self.visivel = visivel
        self.avisar = avisar
        self.parar = parar
        self.pw = None
        self.ctx = None
        self.page = None

    @property
    def url_ficha(self):
        return self.base + "/products/importar-ficha"

    def abrir(self):
        if self.page and not self.page.is_closed():
            return
        from playwright.sync_api import sync_playwright
        if not self.pw:
            self.pw = sync_playwright().start()
        opcoes = dict(headless=not self.visivel, viewport={"width": 1280, "height": 860}, accept_downloads=False,
                      args=["--disable-blink-features=AutomationControlled"])
        self.ctx = None
        for canal in ("msedge", "chrome"):
            try:
                self.ctx = self.pw.chromium.launch_persistent_context(str(BASE / f"perfil-{canal}"), channel=canal, **opcoes)
                break
            except Exception:
                continue
        if self.ctx is None:
            self.ctx = self.pw.chromium.launch_persistent_context(
                str(BASE / "perfil-navegador"), executable_path=os.environ.get("ROBO_CHROMIUM") or None, **opcoes)
        self.page = self.ctx.pages[0] if self.ctx.pages else self.ctx.new_page()
        self.page.set_default_timeout(30000)

    def _na_tela_de_login(self):
        try:
            u = self.page.url
        except Exception:
            return False
        return any(p in u for p in ("/login", "/register", "/forgot-password", "/two-factor"))

    def entrar(self, esperar_login=600):
        """Abre a tela Importar ficha. Se pedir login, espera a pessoa entrar na janela do navegador."""
        self.abrir()
        try:
            resp = self.page.goto(self.url_ficha, wait_until="domcontentloaded")
        except Exception as e:
            if "ERR_NAME_NOT_RESOLVED" in str(e) or "ERR_CONNECTION" in str(e) or "ERR_INTERNET" in str(e):
                raise RuntimeError(f"Não consegui abrir {self.base}. Confira a internet e o endereço do site nas Configurações.") from None
            raise
        if self._na_tela_de_login():
            self.avisar("login")
            fim = time.time() + esperar_login
            while self._na_tela_de_login():
                if self.parar():
                    raise Parar()
                if time.time() > fim:
                    raise RuntimeError("O login não foi feito a tempo. Entre na conta e tente de novo.")
                if self.page.is_closed():
                    raise RuntimeError("A janela do navegador foi fechada.")
                time.sleep(1)
            time.sleep(1.5)
            resp = self.page.goto(self.url_ficha, wait_until="domcontentloaded")
        if resp and resp.status == 404:
            raise RuntimeError("O site ainda não tem a tela Importar ficha. Ela precisa estar no ar.")
        if (resp and resp.status >= 500) or "Server Error" in (self.page.title() or ""):
            raise RuntimeError(f"O site respondeu com erro {resp.status if resp else ''}. Tente de novo em alguns minutos.")
        self.page.wait_for_selector("#ficha-file", state="attached", timeout=30000)
        self.avisar("conectado")

    def cadastrar(self, ficha, fotos, atualizar=False):
        """Envia a ficha e as fotos e aperta Cadastrar. Devolve a lista de produtos criados.

        Com atualizar, marca "Atualizar os que já estão cadastrados": os códigos que já
        existem no site recebem preços, textos, medidas e fotos da ficha (o estoque não muda)."""
        pg = self.page
        if self._na_tela_de_login() or not pg.url.startswith(self.url_ficha):
            self.entrar()
        else:
            pg.goto(self.url_ficha, wait_until="domcontentloaded")
            pg.wait_for_selector("#ficha-file", state="attached")
        if self._na_tela_de_login():
            self.entrar()

        pg.set_input_files("#ficha-file", str(ficha.arquivo))
        pg.wait_for_selector("[data-testid=ficha-preview], [data-testid=ficha-error]", timeout=45000)
        if pg.locator("[data-testid=ficha-error]").count():
            raise RuntimeError("O site não aceitou a ficha: " + pg.locator("[data-testid=ficha-error]").inner_text().strip())

        for n, foto in enumerate(fotos, 1):
            if self.parar():
                raise Parar()
            self.avisar(f"foto {n}/{len(fotos)}")
            for tentativa in (1, 2):
                try:
                    pg.set_input_files("#ficha-photo", str(foto))
                    pg.wait_for_selector(f'[data-testid=ficha-photo-count][data-count="{n}"]', timeout=90000)
                    break
                except Exception:
                    erro = pg.locator(".ep-err").all_inner_texts()
                    if erro:
                        raise RuntimeError(f"Foto {foto.name}: {erro[0].strip()}") from None
                    if tentativa == 2:
                        raise RuntimeError(f"A foto {foto.name} não subiu (internet lenta?).") from None
                    time.sleep(2)

        prev = pg.locator("[data-testid=ficha-preview]")
        prev.wait_for(timeout=30000)
        if atualizar:
            caixa = pg.locator("#ficha-update")
            if caixa.count():
                if not caixa.is_checked():
                    caixa.check()
                pg.wait_for_function("() => { const p = document.querySelector('[data-testid=ficha-preview]');"
                                     " return p && Number(p.dataset.update || 0) > 0; }", timeout=30000)
            elif prev.get_attribute("data-update") is None and int(prev.get_attribute("data-ok") or 0) == 0:
                raise RuntimeError("O site ainda não tem a opção de atualizar produtos. Peça para subir a atualização do site.")
        erros = int(prev.get_attribute("data-errors") or 0)
        ok = int(prev.get_attribute("data-ok") or 0)
        if erros:
            textos = [t.strip() for t in pg.locator("[data-testid=ficha-preview] .if-alert").all_inner_texts()]
            raise RuntimeError("A ficha tem erro: " + " | ".join(textos))
        if ok == 0:
            return None  # tudo já cadastrado

        if self.parar():
            raise Parar()
        botao = pg.locator("[data-testid=ficha-import]")
        botao.wait_for(state="visible")
        pg.wait_for_function("() => { const b = document.querySelector('[data-testid=ficha-import]'); return b && !b.disabled; }", timeout=30000)
        botao.click()
        pg.wait_for_selector("[data-testid=ficha-result]", timeout=120000)
        itens = pg.locator("[data-testid=ficha-result] a[data-product-id]")
        return [{
            "id": itens.nth(i).get_attribute("data-product-id"),
            "codigo": itens.nth(i).get_attribute("data-product-code"),
            "nome": itens.nth(i).locator(".if-name").inner_text().strip(),
            "link": itens.nth(i).get_attribute("href"),
            "acao": itens.nth(i).locator(".if-tag").inner_text().strip() if itens.nth(i).locator(".if-tag").count() else "",
        } for i in range(itens.count())]

    def pegar_chave(self, nome_pc="", esperar_login=600):
        """Pede ao site (já logado neste navegador) a chave do robô para salvar as fichas no banco."""
        self.abrir()
        url = self.base + "/robo/conectar?pc=" + urllib.parse.quote(nome_pc or "Robô Móveis Stylo")
        self.page.goto(self.url_ficha, wait_until="domcontentloaded")    # só confere o login; a chave não aparece na tela
        if self._na_tela_de_login():
            self.avisar("login")
            fim = time.time() + esperar_login
            while self._na_tela_de_login():
                if self.parar():
                    raise Parar()
                if time.time() > fim:
                    raise RuntimeError("O login não foi feito a tempo. Entre na conta e tente de novo.")
                if self.page.is_closed():
                    raise RuntimeError("A janela do navegador foi fechada.")
                time.sleep(1)
            time.sleep(1.5)
        try:
            resp = self.ctx.request.get(url, headers={"Accept": "application/json"})   # usa o login deste navegador
            dados = resp.json()
            chave = dados["robo_chave"]
        except Exception:
            raise RuntimeError("O site ainda não tem a parte das fichas no banco. Ela precisa estar no ar.") from None
        return chave, dados.get("usuario") or ""

    def mostrar(self, url):
        try:
            self.abrir()
            self.page.goto(url, wait_until="domcontentloaded")
            self.page.bring_to_front()
        except Exception:
            pass

    def fechar(self):
        for f in (lambda: self.ctx and self.ctx.close(), lambda: self.pw and self.pw.stop()):
            try:
                f()
            except Exception:
                pass
        self.ctx = self.page = self.pw = None


# ------------------------------------------------------------ Canva IA (robô faz tudo)

def _e_imagem(d):
    return d[:3] == b"\xff\xd8\xff" or d[:8] == b"\x89PNG\r\n\x1a\n" or (d[:4] == b"RIFF" and d[8:12] == b"WEBP")


class PararCanva(RuntimeError):
    """Um passo que não adianta repetir nos outros pedidos."""


def _lado(d):
    try:
        from PIL import Image
        import io
        with Image.open(io.BytesIO(d)) as im:
            return max(im.size)
    except Exception:
        return 0


class CanvaIA:
    """Abre o Canva IA num navegador do robô (perfil próprio, login uma vez), manda as fotos e
    os pedidos um por um e baixa as imagens que o Canva cria.

    O Canva muda a tela de vez em quando: as buscas abaixo tentam vários jeitos (português e inglês).
    Se algo não for achado, salva canva-erro.png e canva-erro.html na pasta do robô para conferir."""

    CAIXA = ("textarea:visible", "[contenteditable='true'][role='textbox']:visible", "[contenteditable='true']:visible",
             "[role='textbox']:visible")
    ENVIAR = ("button[aria-label*='Enviar' i]", "button[aria-label*='Gerar' i]", "button[aria-label*='Criar' i]",
              "button[aria-label*='Send' i]", "button[aria-label*='Submit' i]", "button[aria-label*='Generate' i]",
              "button[type='submit']")
    BAIXAR = ("button[aria-label*='Baixar' i]", "button[aria-label*='Download' i]", "a[download]",
              "button:has-text('Baixar')", "button:has-text('Download')")
    LOGIN = ("/login", "/signup", "/signin", "accounts.google", "/oauth", "/auth")

    def __init__(self, link, avisar=print, parar=lambda: False, pasta_erro=None):
        self.link = (link or "").strip() or "https://www.canva.com/ai"
        self.avisar = avisar
        self.parar = parar
        self.pasta_erro = Path(pasta_erro or BASE)
        self.pw = self.ctx = self.page = None

    # ---- navegador
    def abrir(self):
        from playwright.sync_api import sync_playwright
        self.pw = sync_playwright().start()
        opcoes = dict(headless=False, no_viewport=True, accept_downloads=True, chromium_sandbox=(os.name == "nt"),
                      args=["--start-maximized"], ignore_default_args=["--enable-automation"])
        for canal in ("msedge", "chrome"):
            try:
                self.ctx = self.pw.chromium.launch_persistent_context(str(BASE / f"perfil-canva-{canal}"), channel=canal, **opcoes)
                break
            except Exception:
                continue
        if self.ctx is None:
            self.ctx = self.pw.chromium.launch_persistent_context(
                str(BASE / "perfil-canva"), executable_path=os.environ.get("ROBO_CHROMIUM") or None, **opcoes)
        self.page = self.ctx.pages[0] if self.ctx.pages else self.ctx.new_page()
        self.page.set_default_timeout(20000)

    def fechar(self):
        for f in (lambda: self.ctx.close(), lambda: self.pw.stop()):
            try:
                f()
            except Exception:
                pass
        self.pw = self.ctx = self.page = None

    def _checar(self):
        if self.parar():
            raise Parar()
        if self.page is None or self.page.is_closed():
            raise RuntimeError("A janela do Canva foi fechada.")

    def _salvar_erro(self):
        try:
            self.page.screenshot(path=str(self.pasta_erro / "canva-erro.png"), full_page=False)
            (self.pasta_erro / "canva-erro.html").write_text(f"<!-- {self.page.url} -->\n" + self.page.content(), encoding="utf-8")
        except Exception:
            pass

    def _caixa(self, espera=0):
        fim = time.time() + espera
        while True:
            for sel in self.CAIXA:
                try:
                    loc = self.page.locator(sel)
                    n = loc.count()
                except Exception:
                    continue
                melhor, area = None, 0
                for i in range(min(n, 12)):
                    try:
                        bb = loc.nth(i).bounding_box()
                    except Exception:
                        bb = None
                    if bb and bb["width"] * bb["height"] > area and bb["width"] > 200:
                        melhor, area = loc.nth(i), bb["width"] * bb["height"]
                if melhor is not None:
                    return melhor
            if time.time() >= fim:
                return None
            self._checar()
            time.sleep(1)

    def _reabrir(self):
        """A aba do Canva fechou (ou travou): abre outra no mesmo navegador. Devolve False se o navegador fechou."""
        try:
            self.page = self.ctx.new_page()
        except Exception:
            self.fechar()
            try:
                self.abrir()
            except Exception:
                return False
        try:
            self.page.set_default_timeout(20000)
            self.page.goto(self.link, wait_until="domcontentloaded", timeout=60000)
            return self._caixa(espera=20) is not None
        except Exception:
            return False

    def _no_login(self):
        try:
            u = self.page.url.lower()
        except Exception:
            return False
        return any(p in u for p in self.LOGIN)

    def _logado(self):
        """Sem botão Entrar / Cadastre-se visível na tela."""
        try:
            return not self.page.evaluate("""() => [...document.querySelectorAll('a,button,[role=button]')].some(b => {
                const t = (b.innerText || '').trim();
                if (!/^(entrar|fazer login|log in|login|sign in|cadastre-se|cadastrar|inscreva-se|sign up|registre-se|criar conta)$/i.test(t)) return false;
                const r = b.getBoundingClientRect(); return r.width > 0 && r.height > 0; })""")
        except Exception:
            return True

    def entrar(self, esperar_login=900):
        """Abre o Canva IA. Se não estiver logado, espera a pessoa entrar na janela."""
        if self.page is None:
            self.abrir()
        self.page.goto(self.link, wait_until="domcontentloaded", timeout=60000)
        caixa = self._caixa(espera=12)
        time.sleep(2)
        if caixa is None or self._no_login() or not self._logado():
            self.avisar("login")
            fim = time.time() + esperar_login
            while True:
                self._checar()
                if time.time() > fim:
                    raise RuntimeError("O login no Canva não foi feito a tempo. Entre na conta e tente de novo.")
                if not self._no_login() and self._logado():
                    caixa = self._caixa(espera=0)
                    if caixa is not None:
                        time.sleep(2)
                        break
                    if "/ai" not in self.page.url and "/login" not in self.page.url:
                        try:
                            self.page.goto(self.link, wait_until="domcontentloaded", timeout=60000)
                        except Exception:
                            pass
                time.sleep(2)
        self.avisar("conectado")
        return caixa

    # ---- imagens na tela
    def _imagens(self):
        """[(src, largura, altura)] das imagens grandes na tela agora."""
        try:
            return self.page.evaluate("""() => [...document.images]
                .filter(i => i.complete && i.naturalWidth >= 400 && i.naturalHeight >= 400)
                .filter(i => { const r = i.getBoundingClientRect(); return r.width >= 120 && r.height >= 120; })
                .map(i => { const s = i.currentSrc || i.src; i.dataset.roboSrc = s; return [s, i.naturalWidth, i.naturalHeight]; })""") or []
        except Exception:
            return []

    def _ocupado(self):
        try:
            return bool(self.page.evaluate("""() => {
                const t = document.body ? document.body.innerText : '';
                if (/(Gerando|Criando|Generating|Creating|Pensando|Thinking)\\b/i.test(t.slice(-4000))) return true;
                return !!document.querySelector('[role=progressbar], [aria-busy=true]');
            }"""))
        except Exception:
            return False

    def _bytes(self, src):
        if src.startswith("data:"):
            import base64
            return base64.b64decode(src.split(",", 1)[1])
        if src.startswith("blob:"):
            import base64
            b64 = self.page.evaluate("""async (u) => { const b = await (await fetch(u)).blob();
                return await new Promise(r => { const f = new FileReader(); f.onload = () => r(f.result.split(',')[1]); f.readAsDataURL(b); }); }""", src)
            return base64.b64decode(b64)
        r = self.ctx.request.get(src, timeout=60000)
        if not r.ok:
            raise RuntimeError(f"o Canva respondeu {r.status}")
        return r.body()

    def _bytes_na_pagina(self, src):
        import base64
        b64 = self.page.evaluate("""async (u) => { const b = await (await fetch(u, {credentials: 'include'})).blob();
            return await new Promise(r => { const f = new FileReader(); f.onload = () => r(f.result.split(',')[1]); f.readAsDataURL(b); }); }""", src)
        return base64.b64decode(b64)

    def _bytes_print(self, src):
        imgs = self.page.locator("img[data-robo-src]")
        for i in range(imgs.count()):
            if imgs.nth(i).get_attribute("data-robo-src") == src:
                return imgs.nth(i).screenshot(type="png", timeout=10000)
        return None

    def _voltar_ao_chat(self):
        """Depois de uma imagem o Canva pode abri-la por cima do chat: fecha e volta para a caixa de texto."""
        caixa = self._caixa(espera=3)
        if caixa is not None:
            return caixa
        for _ in range(3):
            try:
                self.page.keyboard.press("Escape")
            except Exception:
                pass
            time.sleep(0.8)
            caixa = self._caixa(espera=2)
            if caixa is not None:
                return caixa
        for sel in ("button[aria-label*='painel' i]", "button[aria-label*='panel' i]", "button[aria-label*='chat' i]",
                    "button[aria-label*='conversa' i]", "button[aria-label*='lateral' i]", "button[aria-label*='sidebar' i]",
                    "button[aria-label*='Mostrar' i]", "button[aria-label*='Fechar' i]", "button[aria-label*='Voltar' i]",
                    "button[aria-label*='Close' i]", "button[aria-label*='Back' i]"):
            try:
                b = self.page.locator(sel)
                if b.count() and b.first.is_visible():
                    b.first.click(timeout=3000)
                    caixa = self._caixa(espera=3)
                    if caixa is not None:
                        return caixa
            except Exception:
                continue
        # não achou: abre uma conversa nova do Canva IA (as fotos vão de novo)
        self.nova_conversa = True
        try:
            self.page.goto(self.link, wait_until="domcontentloaded", timeout=60000)
        except Exception:
            pass
        return self._caixa(espera=20)

    def _baixar_pelo_botao(self, src):
        """Passa o mouse na imagem e clica em Baixar. Devolve os bytes ou None."""
        try:
            img = None
            imgs = self.page.locator("img[data-robo-src]")
            for i in range(imgs.count()):
                if imgs.nth(i).get_attribute("data-robo-src") == src:
                    img = imgs.nth(i)
                    break
            if img is None:
                return None
            img.scroll_into_view_if_needed(timeout=5000)
            img.hover(timeout=5000)
            time.sleep(0.6)
            for sel in self.BAIXAR:
                bt = self.page.locator(sel)
                for i in range(min(bt.count(), 8)):
                    b = bt.nth(i)
                    if not b.is_visible():
                        continue
                    try:
                        with self.page.expect_download(timeout=30000) as dl:
                            b.click(timeout=5000)
                        caminho = dl.value.path()
                        return Path(caminho).read_bytes() if caminho else None
                    except Exception:
                        continue
        except Exception:
            return None
        return None

    def _n_imgs(self):
        try:
            return self.page.evaluate("() => document.images.length")
        except Exception:
            return 0

    def _apareceram(self, antes, espera=10):
        fim = time.time() + espera
        while time.time() < fim:
            if self._n_imgs() > antes:
                return True
            time.sleep(0.5)
        return False

    def _colar_ou_soltar(self, caixa, fotos, jeito):
        """Cola (paste) ou solta (drop) as fotos na caixa do Canva, como se fosse o Ctrl+V / arrastar."""
        import base64
        arqs = [{"nome": Path(f).name, "tipo": "image/png" if str(f).lower().endswith(".png") else "image/jpeg",
                 "b64": base64.b64encode(Path(f).read_bytes()).decode()} for f in fotos]
        caixa.evaluate("""(el, [arqs, jeito]) => {
            const dt = new DataTransfer();
            for (const a of arqs) {
                const bin = atob(a.b64); const u = new Uint8Array(bin.length);
                for (let i = 0; i < bin.length; i++) u[i] = bin.charCodeAt(i);
                dt.items.add(new File([u], a.nome, {type: a.tipo}));
            }
            el.focus();
            if (jeito === 'colar') {
                el.dispatchEvent(new ClipboardEvent('paste', {clipboardData: dt, bubbles: true, cancelable: true}));
            } else {
                for (const t of ['dragenter', 'dragover', 'drop'])
                    el.dispatchEvent(new DragEvent(t, {dataTransfer: dt, bubbles: true, cancelable: true}));
            }
        }""", [arqs, jeito])

    def _colocar_fotos(self, caixa, fotos):
        """Tenta todos os jeitos. Devolve True se as fotos apareceram na tela."""
        antes = self._n_imgs()
        try:
            if self._enviar_fotos(fotos) and self._apareceram(antes):
                return True
        except Exception:
            pass
        for jeito in ("colar", "soltar"):
            try:
                self._colar_ou_soltar(caixa, fotos, jeito)
                if self._apareceram(antes, 8):
                    return True
            except Exception:
                pass
        return False

    def _enviar_fotos(self, fotos):
        inp = self.page.locator("input[type=file]")
        if not inp.count():
            for sel in ("button[aria-label*='Adicionar' i]", "button[aria-label*='Enviar arquivo' i]", "button[aria-label*='Upload' i]",
                        "button[aria-label*='Carregar' i]", "button[aria-label*='imagem' i]", "button[aria-label*='mídia' i]",
                        "button[aria-label*='Add' i]", "button[aria-label*='anexar' i]", "button[aria-label*='attach' i]",
                        "button:has-text('+')"):
                b = self.page.locator(sel)
                if b.count() and b.first.is_visible():
                    try:
                        with self.page.expect_file_chooser(timeout=6000) as fc:
                            b.first.click()
                        fc.value.set_files([str(f) for f in fotos])
                        return True
                    except Exception:
                        try:
                            self.page.keyboard.press("Escape")
                        except Exception:
                            pass
                        inp = self.page.locator("input[type=file]")
                        if inp.count():
                            break
            if not inp.count():
                return False
        alvo = inp.first
        for i in range(inp.count()):
            acc = (inp.nth(i).get_attribute("accept") or "").lower()
            if not acc or "image" in acc or ".png" in acc or ".jpg" in acc:
                alvo = inp.nth(i)
                break
        multi = alvo.get_attribute("multiple") is not None
        if multi:
            alvo.set_input_files([str(f) for f in fotos])
        else:
            for f in fotos:
                alvo.set_input_files(str(f))
                time.sleep(2)
        return True

    def _escrever(self, caixa, texto):
        caixa.click()
        try:
            self.page.keyboard.press("Control+A")
            self.page.keyboard.press("Delete")
        except Exception:
            pass
        self.page.keyboard.insert_text(texto)
        time.sleep(0.8)

    def _mandar(self, caixa):
        for sel in self.ENVIAR:
            b = self.page.locator(sel)
            for i in range(min(b.count(), 6)):
                try:
                    if b.nth(i).is_visible() and b.nth(i).is_enabled():
                        b.nth(i).click(timeout=4000)
                        return
                except Exception:
                    continue
        caixa.press("Enter")

    def criar(self, fotos, pedidos, receber, espera_max=300):
        """pedidos: [(chave, rótulo, texto)]. receber(chave, rótulo, n, total, [bytes], erro) é chamado a cada pedido."""
        caixa = self.entrar()
        conhecidas = {s for s, *_ in self._imagens()}
        enviadas = False
        total = len(pedidos)
        for n, (k, rot, texto) in enumerate(pedidos, 1):
            self._checar()
            self.avisar(f"pedido:{n}:{total}:{rot}")
            try:
                self.nova_conversa = False
                caixa = self._voltar_ao_chat() if n > 1 else self._caixa(espera=20)
                if self.nova_conversa:
                    enviadas = False
                    conhecidas |= {s for s, *_ in self._imagens()}
                if caixa is None:
                    self._salvar_erro()
                    raise PararCanva("não achei a caixa de texto do Canva IA")
                if not enviadas and fotos:
                    if not self._colocar_fotos(caixa, fotos):
                        self._salvar_erro()
                        antes = self._n_imgs()
                        self.avisar("fotos_manual")
                        fim = time.time() + 300
                        while time.time() < fim and not self._apareceram(antes, 2):
                            self._checar()
                        if time.time() >= fim:
                            raise PararCanva("não consegui colocar as fotos no Canva IA")
                    time.sleep(3 + 1.5 * len(fotos))
                    fim = time.time() + 90
                    while self._ocupado() and time.time() < fim:
                        self._checar()
                        time.sleep(1)
                    conhecidas |= {s for s, *_ in self._imagens()}
                    caixa = self._caixa(espera=10) or caixa
                    enviadas = True
                self._escrever(caixa, texto)
                self._mandar(caixa)
                # espera as imagens novas aparecerem e pararem de mudar
                inicio, ultimo, novas = time.time(), None, []
                time.sleep(5)
                while time.time() - inicio < espera_max:
                    self._checar()
                    agora = [s for s, *_ in self._imagens() if s not in conhecidas]
                    if any(s.startswith("http") for s in agora):      # as fotos enviadas voltam como blob:/data:
                        agora = [s for s in agora if s.startswith("http")]
                    if agora != novas:
                        novas, ultimo = agora, time.time()
                    if novas and not self._ocupado() and time.time() - ultimo > 6:
                        break
                    time.sleep(1.5)
                if not novas:
                    raise RuntimeError("o Canva não mostrou nenhuma imagem nova (demorou mais de "
                                       f"{espera_max // 60} minutos ou pediu alguma coisa na tela)")
                dados = []
                for src in novas[:4]:
                    self._checar()
                    # pega a imagem direto (sem clicar em Baixar: no Canva isso abre a imagem por cima
                    # do chat e, no Edge, a janela "Salvar no OneDrive")
                    d = None
                    for jeito in (self._bytes, self._bytes_na_pagina, self._bytes_print):
                        try:
                            d = jeito(src)
                        except Exception:
                            d = None
                        if d and _e_imagem(d):
                            break
                    if d and _e_imagem(d):
                        dados.append(d)
                conhecidas |= set(novas)
                if not dados:
                    raise RuntimeError("as imagens apareceram mas não consegui baixar")
                try:
                    if "/ai" in self.page.url:
                        self.conversa = self.page.url          # volta aqui se o Canva sair do chat
                except Exception:
                    pass
                receber(k, rot, n, total, dados, None)
            except (Parar, PararCanva):
                raise
            except Exception as e:
                if self.page is not None and not self.page.is_closed():
                    self._salvar_erro()
                receber(k, rot, n, total, [], str(e).splitlines()[0] if str(e) else repr(e))
                if self.page is None or self.page.is_closed():
                    if not self._reabrir():
                        raise
                    enviadas = False


# ------------------------------------------------------------ Mercado Livre

JS_BUSCA = r"""
() => {
  const vistos = new Set(), out = [];
  const cards = document.querySelectorAll('li.ui-search-layout__item, .poly-card, .ui-search-result__wrapper, .andes-card.ui-search-result');
  for (const c of cards) {
    const a = c.querySelector('a.poly-component__title, .poly-component__title a, h3 a, h2 a, a.ui-search-link, a.ui-search-item__group__element, a[href*="MLB"]');
    if (!a || !a.href) continue;
    const link = a.href.split('#')[0];
    if (vistos.has(link)) continue;
    const t = (c.querySelector('.poly-component__title, .ui-search-item__title, h3, h2') || a).innerText.trim();
    if (!t) continue;
    let preco = null;
    const caixa = c.querySelector('.poly-price__current, .ui-search-price__second-line, .ui-search-price') || c;
    const valores = [...caixa.querySelectorAll('.andes-money-amount')].filter(x =>
        !x.classList.contains('andes-money-amount--previous') && !x.closest('s') && !x.closest('.andes-money-amount--previous'));
    if (valores.length) {
      const f = valores[0].querySelector('.andes-money-amount__fraction');
      const ce = valores[0].querySelector('.andes-money-amount__cents');
      if (f) preco = parseFloat(f.innerText.replace(/\./g, '') + '.' + (ce ? ce.innerText.trim() : '00'));
    }
    const img = c.querySelector('img');
    let src = img ? (img.getAttribute('data-src') || img.currentSrc || img.src || '') : '';
    if (src.startsWith('data:')) src = '';
    const vend = c.querySelector('.poly-component__seller, .ui-search-official-store-label');
    vistos.add(link);
    out.push({titulo: t, link, preco, imagem: src, vendedor: vend ? vend.innerText.trim() : ''});
    if (out.length >= 60) break;
  }
  return out;
}
"""

JS_PRODUTO = r"""
() => {
  const r = {titulo: '', preco: null, preco_de: null, imagens: [], descricao: '', specs: [], categoria: [], variacoes: [], marca: '', gtin: ''};
  for (const s of document.querySelectorAll('script[type="application/ld+json"]')) {
    try {
      const j = JSON.parse(s.textContent);
      const lista = Array.isArray(j) ? j : (j['@graph'] || [j]);
      for (const o of lista) {
        if (o['@type'] === 'Product') {
          r.titulo = r.titulo || o.name || '';
          const of = Array.isArray(o.offers) ? o.offers[0] : o.offers;
          if (of && of.price && r.preco === null) r.preco = parseFloat(of.price);
          let im = o.image; if (typeof im === 'string') im = [im];
          (im || []).forEach(u => r.imagens.push(u));
          if (o.brand) r.marca = typeof o.brand === 'string' ? o.brand : (o.brand.name || '');
          r.gtin = o.gtin13 || o.gtin || '';
        } else if (o['@type'] === 'BreadcrumbList' && o.itemListElement) {
          o.itemListElement.forEach(i => { const n = i.name || (i.item && i.item.name); if (n) r.categoria.push(n); });
        }
      }
    } catch (e) {}
  }
  const h1 = document.querySelector('h1.ui-pdp-title') || document.querySelector('h1');
  if (h1 && h1.innerText.trim()) r.titulo = h1.innerText.trim();
  const mp = document.querySelector('meta[itemprop="price"]');
  if (mp && mp.content) r.preco = parseFloat(mp.content);
  const ant = document.querySelector('.ui-pdp-price__original-value, s.andes-money-amount--previous, .andes-money-amount--previous');
  if (ant) {
    const f = ant.querySelector('.andes-money-amount__fraction'), ce = ant.querySelector('.andes-money-amount__cents');
    if (f) r.preco_de = parseFloat(f.innerText.replace(/\./g, '') + '.' + (ce ? ce.innerText.trim() : '00'));
  }
  document.querySelectorAll('.ui-pdp-gallery img, figure.ui-pdp-gallery__figure img, img.ui-pdp-image').forEach(img => {
    const u = img.getAttribute('data-zoom') || img.getAttribute('data-src') || img.src;
    if (u && !u.startsWith('data:')) r.imagens.push(u);
  });
  const og = document.querySelector('meta[property="og:image"]');
  if (og && og.content) r.imagens.push(og.content);
  const d = document.querySelector('.ui-pdp-description__content, .ui-pdp-description p');
  if (d) r.descricao = d.innerText.trim();
  document.querySelectorAll('.andes-table tr, .ui-vpp-striped-specs__table tr, .ui-pdp-specs__table tr').forEach(tr => {
    const th = tr.querySelector('th'), td = tr.querySelector('td');
    if (th && td && th.innerText.trim()) r.specs.push([th.innerText.trim(), td.innerText.trim()]);
  });
  document.querySelectorAll('.ui-vpp-highlighted-specs__key-value, .ui-pdp-highlighted-specs-res__key-value').forEach(el => {
    const t = el.innerText.trim(), i = t.indexOf(':');
    if (i > 0) r.specs.push([t.slice(0, i).trim(), t.slice(i + 1).trim()]);
  });
  if (!r.categoria.length) document.querySelectorAll('.andes-breadcrumb a').forEach(a => r.categoria.push(a.innerText.trim()));
  return r;
}
"""

JS_PICKERS = r"""
() => {
  // Seletores de variação do anúncio (Cor, Tamanho...). Marca cada opção com data-robo-op="p-i" para o robô clicar.
  const NOMES = /^(cor|cores|tamanho|tamanhos|medida|medidas|dimens[õo]es|modelo|voltagem|estampa|acabamento|material|formato|tipo|capacidade|quantidade)\b/i;
  // "Botão 1 de 5, Amarelo" / "Botón 1 de 5, Amarelo" / "Button 1 of 5" → "Amarelo"
  const limpar = t => String(t || '').replace(/\s+/g, ' ').trim()
      .replace(/^[^\d,]{0,12}\s\d+\s+(de|of)\s+\d+[,.:;-]?\s*/i, '')
      .replace(/,?\s*(selecionad[oa]|seleccionad[oa]|selected|indispon[ií]vel|no disponible|esgotad[oa]|agotad[oa])\b.*$/i, '').trim();
  const rotulo = el => {
    const img = el.querySelector('img');
    const cands = [el.getAttribute('aria-label'), el.getAttribute('title'), el.getAttribute('data-title'),
                   img && img.getAttribute('alt'), el.innerText];
    for (const c of cands) { const t = limpar(c); if (t && t.length < 60) return t; }
    return '';
  };
  const out = [], usados = new Set();
  const montar = (caixa, nome, selecionado) => {
    const ops = [];
    caixa.querySelectorAll('a, button, [role=option], [role=radio], li.ui-pdp-variations__picker-item').forEach(o => {
      if (usados.has(o) || o.closest('[data-robo-op]')) return;
      if (o.querySelector('a, button') && o.tagName === 'LI') return;
      const t = rotulo(o);
      // sem nome só vale se for uma bolinha com foto (a cor marcada às vezes vem só como "Botão 5 de 5")
      if (!t && !o.querySelector('img')) return;
      if (t && (NOMES.test(t + ':') && /:/.test(t) || ops.some(x => x.texto === t))) return;
      if (/^(ver mais|ver menos|mais op|guia de)/i.test(t)) return;
      usados.add(o);
      const marca = out.length + '-' + ops.length;
      o.setAttribute('data-robo-op', marca);
      const href = o.tagName === 'A' && o.href && !o.href.startsWith('javascript') && !o.getAttribute('href').startsWith('#') ? o.href.split('#')[0] : '';
      const sel = o.getAttribute('aria-checked') === 'true' || o.getAttribute('aria-selected') === 'true' || o.getAttribute('aria-current') === 'true'
                  || /selected|--SELECTED|selecionad/i.test(o.className + ' ' + (o.getAttribute('aria-label') || ''));
      const im = o.querySelector('img');
      const foto = im ? (im.getAttribute('data-zoom') || im.getAttribute('data-src') || im.currentSrc || im.src || '') : '';
      ops.push({texto: t, href, marca, selecionado: sel, foto: foto.startsWith('data:') ? '' : foto});
    });
    if (nome && ops.length) out.push({nome, selecionado, opcoes: ops});
  };
  // 1) caixas do ML (vários nomes de classe já usados)
  document.querySelectorAll('.ui-pdp-variations__picker, [class*="variations__picker"]:not([class*="__picker-"]), [data-testid*="picker"]').forEach(p => {
    if (p.closest('[data-robo-picker]')) return;
    p.setAttribute('data-robo-picker', '1');
    const tt = p.querySelector('.ui-pdp-variations__title, .ui-pdp-variations__label, [class*="variations__label"], p, span');
    const linha = tt ? tt.innerText.replace(/\s+/g, ' ').trim() : '';
    const nome = linha.split(':')[0].trim();
    const selecionado = linha.includes(':') ? linha.split(':').slice(1).join(':').trim() : '';
    if (nome && nome.length < 30) montar(p, nome, selecionado);
  });
  // 2) qualquer texto "Cor: Preto" / "Tamanho: 30 cm" seguido de botões de opção
  if (!out.length) {
    document.querySelectorAll('p, span, div, label, h3, h4').forEach(el => {
      if (el.children.length > 3) return;
      const t = (el.innerText || '').replace(/\s+/g, ' ').trim();
      if (t.length > 60 || !t.includes(':') || !NOMES.test(t)) return;
      if (el.closest('[data-robo-picker], .ui-pdp-specs, .andes-table, .ui-vpp-highlighted-specs, .ui-pdp-description, header, footer')) return;
      let caixa = el.parentElement;
      for (let i = 0; i < 4 && caixa; i++, caixa = caixa.parentElement) {
        const n = caixa.querySelectorAll('a, button, [role=option], [role=radio]').length;
        if (n >= 2) break;
      }
      if (!caixa) return;
      caixa.setAttribute('data-robo-picker', '1');
      montar(caixa, t.split(':')[0].trim(), t.split(':').slice(1).join(':').trim());
    });
  }
  return out;
}
"""

JS_GALERIA = r"""
() => {
  const r = {imagens: [], preco: null, preco_de: null, selecionados: []};
  document.querySelectorAll('.ui-pdp-gallery img, figure.ui-pdp-gallery__figure img, img.ui-pdp-image').forEach(img => {
    const u = img.getAttribute('data-zoom') || img.getAttribute('data-src') || img.src;
    if (u && !u.startsWith('data:')) r.imagens.push(u);
  });
  if (!r.imagens.length) {
    const og = document.querySelector('meta[property="og:image"]');
    if (og && og.content) r.imagens.push(og.content);
  }
  const mp = document.querySelector('meta[itemprop="price"]');
  if (mp && mp.content) r.preco = parseFloat(mp.content);
  const ant = document.querySelector('.ui-pdp-price__original-value, s.andes-money-amount--previous, .andes-money-amount--previous');
  if (ant) {
    const f = ant.querySelector('.andes-money-amount__fraction'), ce = ant.querySelector('.andes-money-amount__cents');
    if (f) r.preco_de = parseFloat(f.innerText.replace(/\./g, '') + '.' + (ce ? ce.innerText.trim() : '00'));
  }
  return r;
}
"""

ML_BUSCA = "https://lista.mercadolivre.com.br/"
BLOQUEIO_ML = ("account-verification", "/gz/", "captcha", "/login", "registration")


def eh_link_ml(texto):
    t = str(texto or "").strip().lower()
    return t.startswith("http") and ("mercadolivre.com" in t or "mercadolibre.com" in t) or bool(re.fullmatch(r"mlb-?\d{6,}", t))


def foto_grande_ml(url):
    """Troca a miniatura do Mercado Livre pela foto no tamanho original."""
    u = str(url or "").split("?")[0]
    u = re.sub(r"D_Q_NP_(2X_)?", "D_NQ_NP_", u)
    return re.sub(r"-[A-Z](\.\w+)$", r"-O\1", u)


def _chave_foto_ml(url):
    m = re.search(r"(\d+-ML[A-Z]\d+_\d+)", url)
    return m.group(1) if m else url


def _limpar_opcao(nome, texto):
    t = re.sub(r"^[^\d,]{0,12}\s\d+\s+(de|of)\s+\d+[,.:;-]?\s*", "", re.sub(r"\s+", " ", str(texto or "")).strip(), flags=re.I)
    t = re.sub(r"(?i),?\s*(selecionad[oa]|seleccionad[oa]|selected|indispon[ií]vel|no disponible|esgotad[oa]|agotad[oa])\b.*$", "", t).strip()
    if ":" in t and t.split(":")[0].strip().lower() == nome.lower():
        t = t.split(":", 1)[1].strip()
    return t


def _fotos_ml(urls, maximo=12):
    """Só as fotos do anúncio (mlstatic), sem repetir, no tamanho original. Devolve (fotos, alternativas)."""
    fotos, alt, vistas = [], [], set()
    for u in urls or []:
        if "mlstatic" not in u or not re.search(r"\d+-ML[A-Z]\d+_\d+", u):
            continue
        k = _chave_foto_ml(u)
        if k not in vistas:
            vistas.add(k)
            fotos.append(foto_grande_ml(u))
            alt.append(u)
    return fotos[:maximo], alt[:maximo]


def _array_json(texto, inicio):
    """Recorta o array JSON que começa em texto[inicio] == '['."""
    nivel, dentro, esc = 0, False, False
    for i in range(inicio, min(len(texto), inicio + 400000)):
        c = texto[i]
        if dentro:
            if esc:
                esc = False
            elif c == "\\":
                esc = True
            elif c == '"':
                dentro = False
        elif c == '"':
            dentro = True
        elif c in "[{":
            nivel += 1
        elif c in "]}":
            nivel -= 1
            if nivel == 0:
                return texto[inicio:i + 1]
    return None


def _texto_json(v):
    if isinstance(v, dict):
        v = v.get("text") or v.get("value") or v.get("name") or ""
    return re.sub(r"\s+", " ", str(v or "")).strip()


def pickers_do_html(html):
    """Plano B: os seletores de variação que o ML guarda no JSON da página ("pickers")."""
    for m in re.finditer(r'"pickers"\s*:\s*\[', html or ""):
        bruto = _array_json(html, m.end() - 1)
        if not bruto:
            continue
        try:
            lista = json.loads(bruto)
        except Exception:
            continue
        out = []
        for p in lista if isinstance(lista, list) else []:
            if not isinstance(p, dict):
                continue
            nome = _texto_json(p.get("label") or p.get("name") or p.get("id")).rstrip(":").strip()
            sel = _texto_json(p.get("selected_option") or p.get("selected_label") or "")
            ops = []
            for o in p.get("products") or p.get("options") or p.get("values") or []:
                if not isinstance(o, dict):
                    continue
                t = _texto_json(o.get("label") or o.get("name") or o.get("text"))
                if not t or any(x["texto"] == t for x in ops):
                    continue
                href = o.get("permalink") or o.get("url") or o.get("link") or ""
                if isinstance(href, dict):
                    href = href.get("url") or ""
                pic = o.get("picture") or o.get("thumbnail") or {}
                pid = pic.get("id") if isinstance(pic, dict) else ""
                ops.append({"texto": t, "href": str(href or ""), "marca": "", "selecionado": bool(o.get("selected")),
                            "foto": f"https://http2.mlstatic.com/D_NQ_NP_{pid}-O.jpg" if pid else ""})
            if nome and ops:
                out.append({"nome": nome, "selecionado": sel, "opcoes": ops})
        if out:
            return out
    return []


def _eh_tamanho(nome):
    return bool(re.search(r"(?i)tamanho|medida|dimens|capacidade|voltagem|quantidade", nome or ""))


class MercadoLivre:
    """Busca e lê anúncios do Mercado Livre na janela do navegador do robô (aba própria)."""

    def __init__(self, site, avisar=print, parar=lambda: False):
        self.site = site
        self.avisar = avisar
        self.parar = parar
        self.page = None

    def _pagina(self):
        self.site.abrir()
        if self.page is None or self.page.is_closed():
            self.page = self.site.ctx.new_page()
            self.page.set_default_timeout(40000)
        return self.page

    def _esperar_liberar(self, espera=300):
        """Se o Mercado Livre pedir verificação ou login, espera a pessoa resolver no navegador."""
        pg = self.page
        if not any(b in pg.url for b in BLOQUEIO_ML):
            return False
        self.avisar("ml_verificar")
        try:
            pg.bring_to_front()
        except Exception:
            pass
        fim = time.time() + espera
        while any(b in pg.url for b in BLOQUEIO_ML):
            if self.parar():
                raise Parar()
            if time.time() > fim:
                raise RuntimeError("O Mercado Livre pediu uma verificação no navegador que não foi feita.")
            time.sleep(1)
        time.sleep(1.5)
        return True

    def _rolar(self, vezes=4):
        for _ in range(vezes):
            try:
                self.page.mouse.wheel(0, 1600)
            except Exception:
                pass
            time.sleep(0.4)

    def buscar(self, termo, pagina=1):
        """Uma página de resultados (cerca de 50 anúncios). pagina=2, 3... traz os seguintes."""
        pg = self._pagina()
        slug = re.sub(r"\s+", "-", str(termo).strip())
        from urllib.parse import quote
        url = ML_BUSCA + quote(slug, safe="-")
        if pagina > 1:
            url += f"_Desde_{(pagina - 1) * 48 + 1}_NoIndex_True"
        pg.goto(url, wait_until="domcontentloaded")
        if self._esperar_liberar():
            pg.goto(url, wait_until="domcontentloaded")
        try:
            pg.wait_for_selector("li.ui-search-layout__item, .poly-card, .ui-search-rescue, .ui-search-result", timeout=20000)
        except Exception:
            pass
        self._rolar(3)
        itens = pg.evaluate(JS_BUSCA)
        if not itens and pagina == 1:
            try:
                html = pg.content()
            except Exception:
                html = ""
            if "ui-empty-state" in html and ("Hubo un error" in html or "not-found" in html):
                raise RuntimeError("O Mercado Livre não mostrou a busca (bloqueou o navegador). Tente de novo com o navegador aberto.")
        for i in itens:
            i["imagem"] = i.get("imagem") or ""
        return itens

    def baixar(self, url, timeout=30000):
        """Bytes de uma imagem (usa a sessão do navegador)."""
        try:
            r = self._pagina().request.get(url, timeout=timeout)
            return r.body() if r.ok else None
        except Exception:
            return None

    def extrair(self, link):
        pg = self._pagina()
        link = str(link).strip()
        if re.fullmatch(r"(?i)mlb-?\d{6,}", link):
            link = "https://produto.mercadolivre.com.br/MLB-" + re.sub(r"\D", "", link)
        pg.goto(link, wait_until="domcontentloaded")
        if self._esperar_liberar():
            pg.goto(link, wait_until="domcontentloaded")
        try:
            pg.wait_for_selector("h1", timeout=25000)
        except Exception:
            raise RuntimeError("Não consegui abrir esse anúncio do Mercado Livre.") from None
        self._rolar(5)
        for texto in ("Ver todas as características", "Ver mais características", "Ver descrição completa"):
            try:
                b = pg.get_by_text(texto, exact=False).first
                if b.count() and b.is_visible():
                    b.click(timeout=3000)
                    time.sleep(0.6)
            except Exception:
                pass
        info = pg.evaluate(JS_PRODUTO)
        info["link"] = pg.url.split("#")[0].split("?")[0]
        info["imagens"], info["imagens_alt"] = _fotos_ml(info.get("imagens"))
        info["variacoes"] = self._variacoes(link, info)
        specs, vistos = [], set()
        for k, v in info.get("specs") or []:
            if k.lower() not in vistos and v:
                vistos.add(k.lower())
                specs.append([k, v])
        info["specs"] = specs
        if not info.get("titulo"):
            raise RuntimeError("A página aberta não parece um anúncio do Mercado Livre.")
        return info

    def _variacoes(self, link, info, max_opcoes=15):
        """Lê os seletores de variação (Cor, Tamanho...) e abre cada opção para pegar as fotos e o preço dela.
        Devolve [{nome, opcoes: [nomes], detalhes: {opção: {imagens, imagens_alt, preco, preco_de}}}]."""
        pg = self.page
        try:
            pickers = pg.evaluate(JS_PICKERS)
        except Exception:
            pickers = []
        if not pickers:
            try:
                pickers = pickers_do_html(pg.content())
            except Exception:
                pickers = []
        saida = []
        for p in pickers[:2]:
            sel = _limpar_opcao(p["nome"], p.get("selecionado") or "")
            todas = [(o, _limpar_opcao(p["nome"], o.get("texto"))) for o in p.get("opcoes") or []]
            sem_nome = [o for o, t in todas if not t]
            # a opção marcada às vezes só diz "Botão 5 de 5": o nome dela é o que aparece em "Cor: Preto"
            dono = None
            if sel and sem_nome and all(t != sel for _, t in todas):
                dono = next((o for o in sem_nome if o.get("selecionado")), sem_nome[0] if len(sem_nome) == 1 else None)
            ops = []
            for o, t in todas:
                if o is dono:
                    t = sel
                    o = dict(o, selecionado=True)
                if t and t.lower() != p["nome"].lower() and all(t != x["texto"] for x in ops):
                    ops.append(dict(o, texto=t))
            if sel and all(sel != x["texto"] for x in ops) and not ops:
                ops.append({"texto": sel, "href": "", "marca": "", "selecionado": True})
            if ops:
                saida.append({"nome": p["nome"], "selecionado": sel, "opcoes": ops[:max_opcoes]})
        if not saida:
            return []
        self.avisar(f"variações: " + " × ".join(f"{p['nome']} ({len(p['opcoes'])})" for p in saida))

        base = pg.url.split("#")[0]
        atual = {"imagens": info.get("imagens") or [], "imagens_alt": info.get("imagens_alt") or [],
                 "preco": info.get("preco"), "preco_de": info.get("preco_de")}
        try:  # a cor que já está aberta: só a galeria dela (sem as fotos extras do og:image / JSON)
            g = pg.evaluate(JS_GALERIA)
            f0, a0 = _fotos_ml(g.get("imagens"))
            if f0:
                atual["imagens"], atual["imagens_alt"] = f0, a0
        except Exception:
            pass
        for p in saida:
            det = {}
            for o in p["opcoes"]:
                if self.parar():
                    raise Parar()
                nome = o["texto"]
                if o.get("selecionado") or nome == p.get("selecionado"):
                    det[nome] = dict(atual)
                    continue
                try:
                    if o.get("href"):
                        pg.goto(o["href"], wait_until="domcontentloaded")
                        self._esperar_liberar()
                    elif o.get("marca"):
                        if pg.url.split("#")[0] != base:
                            pg.goto(base, wait_until="domcontentloaded")
                            pg.evaluate(JS_PICKERS)
                        alvo = pg.locator(f'[data-robo-op="{o["marca"]}"]').first
                        if not alvo.count():
                            continue
                        antes = pg.url
                        alvo.click(timeout=5000)
                        try:
                            pg.wait_for_url(lambda u: u != antes, timeout=6000)
                        except Exception:
                            pass
                    else:
                        if o.get("foto"):
                            det[nome] = {"imagens": [o["foto"]], "imagens_alt": [], "preco": None, "preco_de": None}
                        continue
                    try:
                        pg.wait_for_selector("h1", timeout=20000)
                    except Exception:
                        pass
                    time.sleep(1.5)
                    g = pg.evaluate(JS_GALERIA)
                    fotos, alt = _fotos_ml(g.get("imagens"))
                    if o.get("marca") and o.get("href") and fotos and fotos == atual["imagens"]:
                        # o link abriu a mesma galeria da cor inicial: tenta clicando na bolinha da cor
                        try:
                            pg.goto(base, wait_until="domcontentloaded")
                            pg.evaluate(JS_PICKERS)
                            alvo = pg.locator(f'[data-robo-op="{o["marca"]}"]').first
                            if alvo.count():
                                alvo.click(timeout=5000)
                                time.sleep(2.5)
                                g = pg.evaluate(JS_GALERIA)
                                fotos, alt = _fotos_ml(g.get("imagens"))
                        except Exception:
                            pass
                    if fotos == atual["imagens"] and o.get("foto"):
                        fotos, alt = _fotos_ml([o["foto"]]) or ([o["foto"]], [])
                    if not fotos and o.get("foto"):
                        fotos = [o["foto"]]
                    det[nome] = {"imagens": fotos, "imagens_alt": alt, "preco": g.get("preco"), "preco_de": g.get("preco_de")}
                except Parar:
                    raise
                except Exception:
                    continue
            p["detalhes"] = det
            p["opcoes"] = [o["texto"] for o in p["opcoes"]]
        try:
            if pg.url.split("#")[0] != base:
                pg.goto(base, wait_until="domcontentloaded")
        except Exception:
            pass
        return saida

    def preco_atual(self, link):
        """Título e preço de hoje de um anúncio (para vigiar concorrentes). Preço None se o anúncio sumiu."""
        pg = self._pagina()
        pg.goto(link, wait_until="domcontentloaded")
        if self._esperar_liberar():
            pg.goto(link, wait_until="domcontentloaded")
        try:
            pg.wait_for_selector("h1", timeout=20000)
        except Exception:
            return None, None
        r = pg.evaluate("""() => {
          const h1 = document.querySelector('h1.ui-pdp-title') || document.querySelector('h1');
          let preco = null;
          const mp = document.querySelector('meta[itemprop="price"]');
          if (mp && mp.content) preco = parseFloat(mp.content);
          if (preco === null) {
            for (const s of document.querySelectorAll('script[type="application/ld+json"]')) {
              try { const j = JSON.parse(s.textContent); const o = Array.isArray(j) ? j[0] : j;
                    const of = o && o.offers && (Array.isArray(o.offers) ? o.offers[0] : o.offers);
                    if (of && of.price) { preco = parseFloat(of.price); break; } } catch (e) {}
            }
          }
          const pausado = !!document.querySelector('.ui-pdp-message--warning, .ui-pdp-stock-information__title--out');
          return {titulo: h1 ? h1.innerText.trim() : '', preco, pausado};
        }""")
        return r.get("titulo"), (None if r.get("pausado") and not r.get("preco") else r.get("preco"))

    def fechar(self):
        try:
            if self.page and not self.page.is_closed():
                self.page.close()
        except Exception:
            pass
        self.page = None


def _limpar_descricao(texto):
    t = re.sub(r"https?://\S+", "", str(texto or ""))
    t = re.sub(r"\(?\d{2}\)?\s?9?\d{4}[-\s]?\d{4}", "", t)
    return re.sub(r"\n{3,}", "\n\n", t).strip()[:5000]


def _candidatas_ml(url, alt=None):
    """Endereços da mesma foto do ML, do maior para o menor (o ML guarda vários tamanhos)."""
    k = re.search(r"(\d+-ML[A-Z]\d+_\d+)", str(url or ""))
    out = []
    if k:
        k = k.group(1)
        u = str(url).split("?")[0]
        casa = u[:u.index("D_")] if "D_" in u else "https://http2.mlstatic.com/"
        out += [f"{casa}D_NQ_NP_2X_{k}-F.webp", f"{casa}D_NQ_NP_{k}-O.webp", f"{casa}D_NQ_NP_{k}-O.jpg", f"{casa}D_NQ_NP_2X_{k}-F.jpg"]
    out += [u for u in (url, alt) if u]
    return list(dict.fromkeys(out))


def baixar_foto_grande(baixar, url, alt=None, bom=1000):
    """Baixa a versão de maior resolução da foto. Devolve (Image RGB, lado maior) ou (None, 0)."""
    import io
    melhor, lado = None, 0
    for u in _candidatas_ml(url, alt):
        corpo = baixar(u)
        if not corpo:
            continue
        try:
            im = Image.open(io.BytesIO(corpo))
            im.load()
        except Exception:
            continue
        if max(im.size) > lado:
            melhor, lado = im, max(im.size)
        if lado >= bom:
            break
    if melhor is None:
        return None, 0
    if melhor.mode in ("RGBA", "LA", "P"):
        fundo = Image.new("RGB", melhor.size, (255, 255, 255))
        rgba = melhor.convert("RGBA")
        fundo.paste(rgba, mask=rgba.split()[-1])
        return fundo, lado
    return melhor.convert("RGB"), lado


def salvar_fotos_ml(info, destino, baixar, max_fotos=10):
    """Baixa as fotos de um anúncio lido (info["imagens"]) para a pasta, como 01-ml.jpg, 02-ml.jpg..."""
    nomes = []
    if not baixar or Image is None:
        return nomes
    alt = info.get("imagens_alt") or []
    for n, url in enumerate((info.get("imagens") or [])[:max_fotos], 1):
        im, _ = baixar_foto_grande(baixar, url, alt[n - 1] if n - 1 < len(alt) else None)
        if im is None:
            continue
        nome = f"{n:02d}-ml.jpg"
        im.save(Path(destino) / nome, "JPEG", quality=92, optimize=True)
        nomes.append(nome)
    return nomes


def _nome_opcao(texto):
    from unicodedata import normalize
    t = normalize("NFKD", str(texto)).encode("ascii", "ignore").decode().lower()
    return re.sub(r"[^a-z0-9]+", "-", t).strip("-")[:30] or "opcao"


def salvar_fotos_variacoes(info, destino, baixar, max_fotos=10):
    """Baixa as fotos de cada opção de variação lida do anúncio (cada cor tem as suas).
    Devolve {("cor"|"tam", opção): [nomes]}. Cada cor ganha os próprios arquivos (preto-01-ml.jpg...),
    mesmo quando a foto se repete em outra cor, para cada foto pertencer a uma cor só."""
    if not baixar or Image is None:
        return {}
    pickers = [p for p in info.get("variacoes") or [] if p.get("detalhes")]
    tam = next((p for p in pickers if _eh_tamanho(p["nome"])), None)
    saida, cache = {}, {}
    for p in pickers:
        tipo = "tam" if p is tam else "cor"
        # só baixa por tamanho quando o anúncio não varia por cor (senão as fotos vêm da cor)
        if tipo == "tam" and len(pickers) > 1:
            continue
        for opcao, d in p["detalhes"].items():
            nomes, vistas, alt = [], set(), d.get("imagens_alt") or []
            for n, url in enumerate(d.get("imagens") or [], 1):
                if len(nomes) >= max_fotos:
                    break
                k = _chave_foto_ml(url)
                if k in vistas:
                    continue
                vistas.add(k)
                if k not in cache:
                    cache[k] = baixar_foto_grande(baixar, url, alt[n - 1] if n - 1 < len(alt) else None)[0]
                im = cache[k]
                if im is None:
                    continue
                nome = f"{_nome_opcao(opcao)}-{len(nomes) + 1:02d}-ml.jpg"
                im.save(Path(destino) / nome, "JPEG", quality=92, optimize=True)
                nomes.append(nome)
            if nomes:
                saida[(tipo, opcao)] = nomes
    return saida


def copia_das_fichas(pasta_produtos, pasta_copias=None, guardar=14):
    """Uma cópia por dia da pasta das fichas (zip). Devolve o arquivo criado, ou None se a de hoje já existe."""
    import zipfile
    origem = Path(pasta_produtos)
    if not origem.is_dir():
        return None
    destino = Path(pasta_copias) if str(pasta_copias or "").strip() else BASE / "copias"
    destino.mkdir(parents=True, exist_ok=True)
    arq = destino / f"fichas-{datetime.now():%Y-%m-%d}.zip"
    if arq.exists():
        return None
    tmp = arq.with_suffix(".parcial")
    with zipfile.ZipFile(tmp, "w", zipfile.ZIP_DEFLATED) as z:
        for p in origem.rglob("*"):
            if p.is_file():
                rel = p.relative_to(origem)
                comprime = zipfile.ZIP_STORED if p.suffix.lower() in FOTO_EXTS else zipfile.ZIP_DEFLATED
                z.write(p, rel, compress_type=comprime)
    tmp.replace(arq)
    antigas = sorted(destino.glob("fichas-*.zip"))
    for velha in antigas[:-max(int(guardar or 14), 1)]:
        try:
            velha.unlink()
        except OSError:
            pass
    return arq


def ficha_do_ml(info, pasta_produtos, baixar=None, max_fotos=10):
    """Cria uma pasta de ficha nova com os dados e as fotos de um anúncio do Mercado Livre."""
    dados = json.loads(json.dumps(MODELO_VAZIO))
    titulo = re.sub(r"\s+", " ", str(info.get("titulo") or "")).strip()
    dados["nome"] = titulo[:120] or "Produto do Mercado Livre"
    cat = [c for c in info.get("categoria") or [] if c and c.lower() not in ("início", "inicio", "voltar à lista", "voltar")]
    dados["categoria"] = cat[-1] if cat else ""
    specs = {k.lower(): v for k, v in info.get("specs") or []}
    dados["material"] = specs.get("material") or specs.get("materiais") or ""
    dados["acabamento"] = specs.get("acabamento") or specs.get("tipo de acabamento") or ""
    dados["modelo"] = None
    carga = re.search(r"([\d.,]+)\s*kg", specs.get("carga máxima") or specs.get("capacidade máxima de carga") or specs.get("peso máximo suportado") or "")
    dados["carga_maxima_kg"] = numero(carga.group(1)) if carga else None
    dados["descricao"] = _limpar_descricao(info.get("descricao"))
    dados["ficha_tecnica"] = [{"nome": k, "valor": v} for k, v in info.get("specs") or []
                              if k.lower() not in ("marca", "modelo", "código universal de produto", "gtin", "sku")][:40]
    preco = info.get("preco")
    preco_de = info.get("preco_de")
    base = {"valor": None, "codigo": "", "custo": None, "venda": round(float(preco), 2) if preco else None,
            "preco_tabela": round(float(preco_de), 2) if preco_de else None, "estoque": 0,
            "medidas_peca_cm": {}, "embalagem": {}, "fotos": []}
    def cm(*nomes):
        for n in nomes:
            m = re.search(r"([\d.,]+)\s*(mm|cm|m)\b", specs.get(n) or "")
            if m:
                v = numero(m.group(1))
                return round(v / 10 if m.group(2) == "mm" else v * 100 if m.group(2) == "m" else v, 1)
        return None
    medidas = {k: v for k, v in {"altura": cm("altura"), "largura": cm("largura"), "profundidade": cm("profundidade", "comprimento"),
                                 "diametro": cm("diâmetro", "diametro")}.items() if v}
    base["medidas_peca_cm"] = medidas
    pickers = [p for p in info.get("variacoes") or [] if p.get("opcoes")]
    tam = next((p for p in pickers if _eh_tamanho(p["nome"])), None)
    cor = next((p for p in pickers if p is not tam), None)
    combos = []  # (valor, opção de tamanho, opção de cor)
    if tam and cor:
        por = "Tamanho e cor"
        combos = [(f"{t} · {c}", t, c) for t in tam["opcoes"] for c in cor["opcoes"]]
    elif tam:
        por = "Tamanho"
        combos = [(t, t, None) for t in tam["opcoes"]]
    elif cor:
        por = "Cor"
        combos = [(c, None, c) for c in cor["opcoes"]]
    if len(combos) == 1:  # uma opção só (ex.: anúncio com uma cor): não é variação
        combos = []
    det_tam = (tam or {}).get("detalhes") or {}
    det_cor = (cor or {}).get("detalhes") or {}
    if combos:
        dados["variacao_por"] = por
        dados["variacoes"] = []
        for valor, t, c in combos[:40]:
            v = dict(json.loads(json.dumps(base)), valor=valor)
            # preço: o da opção de tamanho manda (cor quase nunca muda o preço); senão o da cor
            d = det_tam.get(t) if t else None
            d = d if d and d.get("preco") else det_cor.get(c) if c else None
            if d and d.get("preco"):
                v["venda"] = round(float(d["preco"]), 2)
                v["preco_tabela"] = round(float(d["preco_de"]), 2) if d.get("preco_de") else None
            dados["variacoes"].append(v)
    else:
        dados["variacoes"] = [base]
    dados["mercado_livre"] = {"link_referencia": info.get("link"), "titulo_anuncio": titulo[:60],
                              "tipo_anuncio": "classico", "quantidade_anuncio": 10}
    destino = nova_pasta(pasta_produtos, dados)
    fotos_var = salvar_fotos_variacoes(info, destino, baixar, max_fotos)
    if fotos_var and combos:
        # cada variação recebe as fotos da cor dela (ou do tamanho, se o anúncio só varia por tamanho)
        for v, (valor, t, c) in zip(dados["variacoes"], combos):
            v["fotos"] = fotos_var.get(("cor", c)) or fotos_var.get(("tam", t)) or []
        if not any(v["fotos"] for v in dados["variacoes"]):
            dados["variacoes"][0]["fotos"] = salvar_fotos_ml(info, destino, baixar, max_fotos)
    else:
        dados["variacoes"][0]["fotos"] = salvar_fotos_ml(info, destino, baixar, max_fotos)
    (destino / "ficha.md").write_text(texto_da_ficha(dados), encoding="utf-8")
    (destino / "origem-ml.json").write_text(json.dumps(info, ensure_ascii=False, indent=2), encoding="utf-8")
    return destino


# ------------------------------------------------------------ rodada completa

def relatorio_path():
    pasta = BASE / "relatorios"
    pasta.mkdir(exist_ok=True)
    return pasta / f"relatorio-{datetime.now():%Y-%m-%d}.csv"


def anotar_relatorio(ficha):
    arq = relatorio_path()
    novo = not arq.exists()
    with open(arq, "a", newline="", encoding="utf-8-sig") as f:
        w = csv.writer(f, delimiter=";")
        if novo:
            w.writerow(["Hora", "Pasta", "Produto", "Situação", "Códigos", "Links", "Mensagem"])
        w.writerow([
            datetime.now().strftime("%H:%M:%S"), ficha.pasta.name, ficha.nome,
            {"feita": "Cadastrado", "ja": "Já existia", "falhou": "Falhou", "erro": "Ficha com erro"}.get(ficha.status, ficha.status),
            ", ".join(r["codigo"] or "" for r in ficha.resultado),
            " ".join(r["link"] or "" for r in ficha.resultado),
            ficha.mensagem,
        ])


def _slug(t):
    t = unicodedata.normalize("NFKD", str(t)).encode("ascii", "ignore").decode().lower()
    return re.sub(r"[^a-z0-9]+", "-", t).strip("-")


def codigos_no_site(ficha):
    """[(variação, código)] como o site cadastra: o código da variação ou o que o site cria (CODIGO-VALOR)."""
    base = str(ficha.dados.get("codigo") or "").strip()
    vs = ficha.variacoes
    multi = len(vs) > 1 or bool(str((vs[0].get("valor") if vs else "") or "").strip())
    feitos = [str(x.get("codigo") or "") for x in ficha.resultado or []]
    out = []
    for v in vs:
        if v.get("ativa", True) is False:
            continue
        cod = str(v.get("codigo") or "").strip()
        if not cod and base:
            valor = str(v.get("valor") or "").strip() or ("Padrão" if multi else "")
            cod = f"{base}-{_slug(valor).upper()}" if multi else base
        if not cod and len(vs) == 1 and len(feitos) == 1:
            cod = feitos[0]
        out.append((v, cod))
    return out


def mover_para_feitos(ficha, pasta_produtos):
    destino_raiz = Path(pasta_produtos) / "feitos"
    destino_raiz.mkdir(exist_ok=True)
    destino = destino_raiz / ficha.pasta.name
    if destino.exists():
        destino = destino_raiz / f"{ficha.pasta.name} {datetime.now():%Y%m%d-%H%M%S}"
    shutil.move(str(ficha.pasta), str(destino))
    antiga = ficha.pasta
    ficha.pasta = destino
    ficha.fotos = [destino / p.relative_to(antiga) for p in ficha.fotos]
    if ficha.arquivo:
        ficha.arquivo = destino / ficha.arquivo.relative_to(antiga)


def ficha_para_envio(ficha, pasta_tmp):
    """Se houver variações desligadas, manda para o site uma cópia da ficha só com as ligadas."""
    import copy
    if len(ficha.ativas) == len(ficha.variacoes):
        return ficha
    dados = json.loads(json.dumps(ficha.dados))
    dados["variacoes"] = [v for v in dados.get("variacoes") or [] if isinstance(v, dict) and v.get("ativa", True) is not False]
    envio = copy.copy(ficha)
    envio.arquivo = Path(pasta_tmp) / "ficha.md"
    envio.arquivo.write_text(texto_da_ficha(dados), encoding="utf-8")
    return envio


def rodar(fichas, cfg, avisar, parar, site=None, atualizar=False):
    """Cadastra as fichas prontas, uma por vez. avisar(evento, ficha, texto) atualiza a janela.

    Com atualizar, também manda as fichas já cadastradas: o site atualiza os produtos
    que já existem (preços, textos, medidas, fotos; o estoque não muda)."""
    r = cfg["robo"]
    proprio = site is None
    site = site or Site(r.get("site"), visivel=sim(r.get("navegador_visivel")),
                        avisar=lambda t: avisar("site", None, t), parar=parar)
    feitas = falhas = 0
    try:
        site.entrar()
        for ficha in fichas:
            if parar():
                raise Parar()
            if not (ficha.pronta or (atualizar and not ficha.erros and ficha.dados)):
                continue
            ja_feita = ficha.status in ("feita", "editada")
            assinatura = assinatura_ficha(ficha)
            avisar("inicio", ficha, f"{'Atualizando' if atualizar else 'Cadastrando'} {ficha.nome}...")
            tmp = Path(tempfile.mkdtemp(prefix="robo-stylo-"))
            try:
                fotos = preparar_fotos(ficha, tmp, int(r.get("lado_maximo_foto")), int(r.get("tamanho_maximo_foto_kb")))
                res = site.cadastrar(ficha_para_envio(ficha, tmp), fotos, atualizar=atualizar)
                if res is None:
                    ficha.status, ficha.mensagem = "ja", "Todos os códigos já estavam cadastrados."
                    if ja_feita:
                        ficha.status = "feita"
                else:
                    novos = sum(1 for x in res if (x.get("acao") or "").lower() != "atualizado")
                    mudados = len(res) - novos
                    if atualizar and ficha.resultado:
                        # junta com o que já tinha: o primeiro continua sendo o principal
                        por_id = {str(x.get("id")): x for x in ficha.resultado}
                        for x in res:
                            por_id[str(x.get("id"))] = {**por_id.get(str(x.get("id")), {}), **x}
                        res = list(por_id.values())
                    ficha.status, ficha.resultado = "feita", res
                    ficha.mensagem = " e ".join(t for t in [
                        f"{novos} produto(s) criado(s)" if novos else "",
                        f"{mudados} atualizado(s)" if mudados else ""] if t) + "."
                    feitas += 1
                try:
                    (ficha.pasta / "resultado.json").write_text(json.dumps({
                        "quando": datetime.now().isoformat(timespec="seconds"), "site": r.get("site"),
                        "mensagem": ficha.mensagem, "produtos": ficha.resultado, "assinatura": assinatura},
                        ensure_ascii=False, indent=2), encoding="utf-8")
                except OSError:
                    pass
                if sim(r.get("mover_feitos")) and ficha.pasta.parent.name.lower() != "feitos":
                    mover_para_feitos(ficha, r.get("pasta_produtos"))
            except Parar:
                raise
            except Exception as e:
                ficha.status, ficha.mensagem = "falhou", str(e).splitlines()[0][:300]
                falhas += 1
            finally:
                shutil.rmtree(tmp, ignore_errors=True)
            anotar_relatorio(ficha)
            avisar("fim", ficha, ficha.mensagem)
            time.sleep(float(r.get("pausa_entre_produtos") or 0))
    finally:
        if proprio:
            site.fechar()
    return feitas, falhas


# ------------------------------------------------------------ fichas no banco do site (FlowManeger)
#
# O site guarda cada ficha (ficha.md + fotos) na tabela robo_fichas. O robô continua trabalhando na pasta
# produtos do PC (é a cópia local) e sincroniza: o que mudou aqui sobe, o que mudou no site (outro PC) desce.
# Nada é apagado de verdade: aqui vai para _lixeira/_removidas, no site vai para o histórico/lixeira.

ARQ_CONTA = BASE / "conta-site.json"
ARQ_SYNC = BASE / "sync-fichas.json"
EXTS_NUVEM = {".jpg", ".jpeg", ".png", ".webp", ".gif", ".md", ".txt", ".json", ".csv", ".pdf"}


class ErroNuvem(RuntimeError):
    pass


def ler_conta():
    try:
        d = json.loads(ARQ_CONTA.read_text(encoding="utf-8"))
        return d if d.get("chave") else None
    except Exception:
        return None


def salvar_conta(site, chave, usuario):
    tmp = ARQ_CONTA.with_suffix(".tmp")
    tmp.write_text(json.dumps({"site": site.rstrip("/"), "chave": chave, "usuario": usuario,
                               "desde": datetime.now().isoformat(timespec="seconds")}, ensure_ascii=False, indent=2), encoding="utf-8")
    os.replace(tmp, ARQ_CONTA)


def desconectar_conta():
    if ARQ_CONTA.exists():
        ARQ_CONTA.replace(ARQ_CONTA.with_name("conta-site-antiga.json"))


class Nuvem:
    def __init__(self, site, pasta_produtos, avisar=lambda t: None, ocupadas=lambda: set()):
        self.site = site.rstrip("/")
        self.raiz = Path(pasta_produtos)
        self.avisar = avisar
        self.ocupadas = ocupadas
        conta = ler_conta()
        self.chave = conta["chave"] if conta and conta.get("site", "").rstrip("/") == self.site else None

    @property
    def conectada(self):
        return bool(self.chave)

    # ---- http
    def _req(self, metodo, caminho, campos=None, arquivo=None, query=None, binario=False, tempo=60):
        url = self.site + "/robo-api" + caminho + ("?" + urllib.parse.urlencode(query) if query else "")
        cab = {"Authorization": "Bearer " + (self.chave or ""), "Accept": "application/json", "User-Agent": "RoboMoveisStylo"}
        corpo = None
        if metodo == "POST":
            fronteira = "----robo" + uuid.uuid4().hex
            partes = []
            for k, v in (campos or {}).items():
                partes.append(f'--{fronteira}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n'.encode() + str(v).encode("utf-8") + b"\r\n")
            if arquivo:
                nome_campo, caminho_arq, nome = arquivo
                nome_seguro = nome.replace('"', "")
                partes.append(f'--{fronteira}\r\nContent-Disposition: form-data; name="{nome_campo}"; filename="{nome_seguro}"\r\n'
                              f"Content-Type: application/octet-stream\r\n\r\n".encode("utf-8") + Path(caminho_arq).read_bytes() + b"\r\n")
            partes.append(f"--{fronteira}--\r\n".encode())
            corpo = b"".join(partes)
            cab["Content-Type"] = "multipart/form-data; boundary=" + fronteira
        req = urllib.request.Request(url, data=corpo, headers=cab, method=metodo)
        try:
            with urllib.request.urlopen(req, timeout=tempo) as r:
                dados = r.read()
        except urllib.error.HTTPError as e:
            texto = e.read().decode("utf-8", "replace")
            try:
                msg = json.loads(texto).get("erro") or json.loads(texto).get("message")
            except Exception:
                msg = ""
            if e.code == 401:
                self.chave = None
                raise ErroNuvem(msg or "O robô não está conectado ao site. Conecte de novo nas Configurações.") from None
            if e.code == 404 and caminho == "/eu":
                raise ErroNuvem("O site ainda não tem a parte das fichas no banco.") from None
            if e.code in (404, 405) and caminho.startswith("/estoque"):
                raise ErroNuvem("o site ainda não tem a parte do estoque (precisa subir a atualização do site).") from None
            if e.code == 413:
                raise ErroNuvem("Arquivo grande demais para o site.") from None
            raise ErroNuvem(msg or f"O site respondeu com erro {e.code}.") from None
        except urllib.error.URLError as e:
            raise ErroNuvem(f"Sem conexão com o site ({e.reason}).") from None
        return dados if binario else json.loads(dados.decode("utf-8") or "{}")

    # ---- estado da última sincronização
    def _ler_estado(self):
        try:
            d = json.loads(ARQ_SYNC.read_text(encoding="utf-8"))
            if d.get("site") == self.site and d.get("pasta") == str(self.raiz):
                return d
        except Exception:
            pass
        return {"site": self.site, "pasta": str(self.raiz), "fichas": {}, "hash": {}}

    def _gravar_estado(self, est):
        tmp = ARQ_SYNC.with_suffix(".tmp")
        tmp.write_text(json.dumps(est, ensure_ascii=False, indent=1), encoding="utf-8")
        os.replace(tmp, ARQ_SYNC)

    def _sha1(self, p, est):
        st = p.stat()
        chave = str(p)
        c = est["hash"].get(chave)
        if c and c[0] == st.st_size and c[1] == st.st_mtime_ns:
            return c[2]
        h = hashlib.sha1()
        with open(p, "rb") as f:
            for bloco in iter(lambda: f.read(1 << 20), b""):
                h.update(bloco)
        est["hash"][chave] = [st.st_size, st.st_mtime_ns, h.hexdigest()]
        return h.hexdigest()

    def _locais(self, est):
        saida = {}
        for situacao, raiz in (("aberta", self.raiz), ("feita", self.raiz / "feitos")):
            if not raiz.is_dir():
                continue
            for p in raiz.iterdir():
                if not p.is_dir() or p.name.startswith(".") or (situacao == "aberta" and p.name.lower() in IGNORAR):
                    continue
                if p.name in saida:
                    continue
                arqs = {a.name: self._sha1(a, est) for a in sorted(p.iterdir())
                        if a.is_file() and a.suffix.lower() in EXTS_NUVEM and not a.name.startswith(".")}
                saida[p.name] = {"dir": p, "situacao": situacao, "arquivos": arqs}
        return saida

    @staticmethod
    def _editada(L):
        return max([L["dir"].stat().st_mtime] + [(L["dir"] / n).stat().st_mtime for n in L["arquivos"]])

    @staticmethod
    def _assinatura(situacao, arquivos):
        return situacao + "|" + "|".join(f"{n}:{h}" for n, h in sorted(arquivos.items()))

    # ---- sincronizar
    def testar(self):
        return self._req("GET", "/eu")

    # ---- estoque no site
    def estoque(self, codigos):
        """{codigo em minúsculas: {id, codigo, nome, estoque, kit, encomenda, venda, repetido}}"""
        codigos = [c for c in dict.fromkeys(str(c).strip() for c in codigos) if c]
        if not self.chave:
            raise ErroNuvem("O robô não está conectado ao site. Conecte em Configurações.")
        if not codigos:
            return {}
        return self._req("GET", "/estoque", query={"codigos": ",".join(codigos)}, tempo=30).get("produtos") or {}

    def somar_estoque(self, itens):
        """itens = [(codigo, quantos)]. Devolve o mesmo formato de estoque(), com "antes" e "erro"."""
        lista = [{"codigo": c, "somar": int(n)} for c, n in itens if str(c).strip() and int(n)]
        if not lista:
            return {}
        return self._req("POST", "/estoque/somar", campos={"itens": json.dumps(lista)}, tempo=60).get("produtos") or {}

    def sincronizar(self, parar=lambda: False):
        if not self.chave:
            raise ErroNuvem("O robô não está conectado ao site.")
        est = self._ler_estado()
        remotas = {f["pasta"]: f for f in self._req("GET", "/fichas")["fichas"]}
        locais = self._locais(est)
        ocupadas = {Path(x).name for x in (self.ocupadas() or set())}
        res = {"subiram": 0, "desceram": 0, "excluidas": 0, "erros": []}
        for pasta in sorted(set(remotas) | set(locais)):
            if parar():
                break
            if pasta in ocupadas:
                continue
            L, R, st = locais.get(pasta), remotas.get(pasta), est["fichas"].get(pasta)
            try:
                if L:
                    sig_l = self._assinatura(L["situacao"], L["arquivos"])
                    mudou_aqui = st is None or st.get("sig") != sig_l
                if L and not R:
                    self._subir(pasta, L, None, est); res["subiram"] += 1
                elif L and R:
                    sig_r = self._assinatura(R["situacao"], {a["nome"]: a["sha1"] for a in R["arquivos"]})
                    mudou_la = st is None or st.get("versao") != R["versao"]
                    if R["excluida"]:
                        if mudou_aqui:
                            self._subir(pasta, L, R, est); res["subiram"] += 1
                        else:
                            self._para_lixeira(L["dir"]); est["fichas"].pop(pasta, None); res["excluidas"] += 1
                    elif sig_l == sig_r and R["completa"]:
                        est["fichas"][pasta] = {"versao": R["versao"], "sig": sig_l, "em": time.time()}
                    elif st is None and R["completa"]:
                        # Primeira vez deste PC e já existe no site uma ficha diferente com o mesmo nome (veio de
                        # outro PC): não mistura. A deste PC ganha o nome do PC e sobe separada; a do site desce.
                        novo = self._nome_livre(f"{pasta} ({self.pc})", remotas, locais)
                        dest = L["dir"].with_name(novo)
                        shutil.move(str(L["dir"]), str(dest))
                        L2 = {**L, "dir": dest}
                        self._subir(novo, L2, None, est); res["subiram"] += 1
                        res.setdefault("separadas", []).append(novo)
                        self._descer(pasta, R, None, est); res["desceram"] += 1
                    elif mudou_aqui and mudou_la:
                        # mudou nos dois lugares: fica a mais nova; a outra é guardada (lixeira aqui ou histórico no site)
                        recente_l = self._editada(L)
                        if st and recente_l <= float(st.get("em") or 0):
                            recente_l = time.time()   # mudou depois da última sincronização (ex.: cópia antiga colocada de volta)
                        try:
                            recente_r = datetime.fromisoformat(R.get("editada_em") or R["atualizada"]).timestamp()
                        except Exception:
                            recente_r = 0
                        if recente_l >= recente_r or not R["completa"]:
                            self._subir(pasta, L, R, est); res["subiram"] += 1
                        else:
                            self._guardar_copia(L["dir"])
                            self._descer(pasta, R, L, est); res["desceram"] += 1
                    elif mudou_aqui or not R["completa"]:
                        self._subir(pasta, L, R, est); res["subiram"] += 1
                    elif mudou_la:
                        self._descer(pasta, R, L, est); res["desceram"] += 1
                elif R and not L:
                    if R["excluida"]:
                        est["fichas"].pop(pasta, None)
                    elif st is not None and st.get("versao") == R["versao"]:
                        # foi apagada (lixeira) neste PC: tira da lista do site também (lá não apaga os arquivos)
                        self._req("POST", "/fichas/excluir", {"pasta": pasta})
                        est["fichas"].pop(pasta, None); res["excluidas"] += 1
                    elif R["completa"]:
                        self._descer(pasta, R, None, est); res["desceram"] += 1
            except ErroNuvem as e:
                res["erros"].append(f"{pasta}: {e}")
                if not self.chave:
                    break
            finally:
                self._gravar_estado(est)
        vivos = {str(v["dir"] / n) for v in locais.values() for n in v["arquivos"]}
        est["hash"] = {k: v for k, v in est["hash"].items() if k in vivos}
        self._gravar_estado(est)
        return res

    @property
    def pc(self):
        return (os.environ.get("COMPUTERNAME") or "outro PC").strip()

    @staticmethod
    def _nome_livre(nome, remotas, locais):
        base, n = nome, 2
        while nome in remotas or nome in locais:
            nome = f"{base} {n}"
            n += 1
        return nome

    def _subir(self, pasta, L, R, est):
        self.avisar(f"Salvando no site: {pasta}")
        conteudo = ""
        for nome in ("ficha.md", "ficha.txt", "ficha.json"):
            if nome in L["arquivos"]:
                conteudo = (L["dir"] / nome).read_text(encoding="utf-8", errors="replace")
                break
        else:
            outro = next((n for n in L["arquivos"] if n.lower().endswith((".md", ".txt"))), None)
            if outro:
                conteudo = (L["dir"] / outro).read_text(encoding="utf-8", errors="replace")
        lista = [{"nome": n, "sha1": h} for n, h in L["arquivos"].items()]
        campos = {"pasta": pasta, "conteudo": conteudo, "situacao": L["situacao"], "arquivos": json.dumps(lista),
                  "editada_em": datetime.fromtimestamp(max(self._editada(L), float((est["fichas"].get(pasta) or {}).get("em") or 0) + 1)
                                                        ).astimezone().isoformat(timespec="seconds")}
        if R:
            campos["versao_base"] = R["versao"]
        resp = self._req("POST", "/fichas/salvar", campos)
        for nome in resp.get("faltam") or []:
            self.avisar(f"Salvando no site: {pasta} · {nome}")
            resp = self._req("POST", "/fichas/arquivo", {"pasta": pasta, "nome": nome}, arquivo=("arquivo", L["dir"] / nome, nome), tempo=180)
        est["fichas"][pasta] = {"versao": resp["versao"], "sig": self._assinatura(L["situacao"], L["arquivos"]), "em": time.time()}

    def _descer(self, pasta, R, L, est):
        self.avisar(f"Trazendo do site: {pasta}")
        destino = (self.raiz / "feitos" if R["situacao"] == "feita" else self.raiz) / pasta
        destino.parent.mkdir(parents=True, exist_ok=True)
        if L and L["dir"] != destino:
            if destino.exists():
                self._para_lixeira(destino)
            shutil.move(str(L["dir"]), str(destino))
        destino.mkdir(exist_ok=True)
        tmp = destino / ".baixando"
        tmp.mkdir(exist_ok=True)
        novos = {}
        for a in R["arquivos"]:
            atual = destino / a["nome"]
            if atual.exists() and self._sha1(atual, est) == a["sha1"]:
                novos[a["nome"]] = a["sha1"]
                continue
            dados = self._req("GET", "/fichas/arquivo", query={"pasta": pasta, "nome": a["nome"]}, binario=True, tempo=180)
            if a.get("sha1") and hashlib.sha1(dados).hexdigest() != a["sha1"]:
                raise ErroNuvem(f"o arquivo {a['nome']} veio incompleto")
            (tmp / a["nome"]).write_bytes(dados)
            novos[a["nome"]] = a["sha1"]
        for p in list(destino.iterdir()):
            if p.is_file() and p.suffix.lower() in EXTS_NUVEM and p.name not in novos and not p.name.startswith("."):
                remover_foto(destino, p.name)       # vai para _removidas, não apaga
        for p in tmp.iterdir():
            os.replace(p, destino / p.name)
        tmp.rmdir()
        arqs = {n: self._sha1(destino / n, est) for n in novos}
        est["fichas"][pasta] = {"versao": R["versao"], "sig": self._assinatura(R["situacao"], arqs), "em": time.time()}

    def _para_lixeira(self, pasta_dir):
        lixo = self.raiz / "_lixeira"
        lixo.mkdir(exist_ok=True)
        destino = lixo / pasta_dir.name
        if destino.exists():
            destino = lixo / f"{pasta_dir.name} {datetime.now():%Y%m%d-%H%M%S}"
        shutil.move(str(pasta_dir), str(destino))

    def _guardar_copia(self, pasta_dir):
        lixo = self.raiz / "_lixeira"
        lixo.mkdir(exist_ok=True)
        shutil.copytree(pasta_dir, lixo / f"{pasta_dir.name} (antes do site {datetime.now():%Y%m%d-%H%M%S})")


if __name__ == "__main__":
    cfg = ler_config()
    if len(sys.argv) > 2 and sys.argv[1] == "--site":
        cfg["robo"]["site"] = sys.argv[2]
        del sys.argv[1:3]
    fichas = listar_fichas(cfg["robo"]["pasta_produtos"])
    for f in fichas:
        print(f"[{f.status}] {f.pasta.name}: {len(f.variacoes)} variação(ões), {len(f.fotos)} foto(s)")
        for e in f.erros:
            print("   ERRO:", e)
        for a in f.avisos:
            print("   aviso:", a)
    if "--cadastrar" in sys.argv:
        feitas, falhas = rodar(fichas, cfg, lambda ev, f, t: print(ev, f.nome if f else "", t), lambda: False)
        print(f"Cadastradas: {feitas}  Falhas: {falhas}")
    if "--sincronizar" in sys.argv:
        # Conecta (abre o navegador do robô só se precisar entrar na conta) e manda/traz todas as fichas.
        site_url = cfg["robo"]["site"].rstrip("/")
        conta = ler_conta()
        if not conta or conta.get("site", "").rstrip("/") != site_url:
            s = Site(site_url, visivel=True, avisar=print)
            try:
                chave, usuario = s.pegar_chave(nome_pc=os.environ.get("COMPUTERNAME") or "")
            finally:
                s.fechar()
            salvar_conta(site_url, chave, usuario)
            print(f"Conectado ao FlowManeger como {usuario}.")
        res = Nuvem(site_url, cfg["robo"]["pasta_produtos"], avisar=print).sincronizar()
        print(f"Salvas no site: {res['subiram']}  Trazidas do site: {res['desceram']}  Lixeira: {res['excluidas']}")
        for n in res.get("separadas") or []:
            print("   Mesmo nome com conteúdo diferente em outro PC; esta ficou separada como:", n)
        for e in res["erros"]:
            print("   ERRO:", e)
