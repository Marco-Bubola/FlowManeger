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
    f.sobrando = sorted(p.name for p in na_pasta.values() if p not in usadas)
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
                f = ler_ficha(p)
                f.status, f.mensagem, f.resultado = "feita", "Já cadastrada no site.", []
                try:
                    info = json.loads((p / "resultado.json").read_text(encoding="utf-8"))
                    f.resultado = info.get("produtos") or []
                    f.mensagem = info.get("mensagem") or f.mensagem
                except Exception:
                    pass
                fichas.append(f)
    return fichas


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

    def cadastrar(self, ficha, fotos):
        """Envia a ficha e as fotos e aperta Cadastrar. Devolve a lista de produtos criados."""
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
        } for i in range(itens.count())]

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
  document.querySelectorAll('.ui-pdp-variations__picker').forEach(p => {
    const tt = p.querySelector('.ui-pdp-variations__title, .ui-pdp-variations__label, p');
    const nome = tt ? tt.innerText.split(':')[0].trim() : '';
    const ops = [];
    p.querySelectorAll('a, button, [role=option], .ui-pdp-thumbnail').forEach(o => {
      const v = (o.getAttribute('aria-label') || o.getAttribute('title') || o.innerText || '').trim();
      if (v && v.length < 60 && !ops.includes(v)) ops.push(v);
    });
    if (nome && ops.length) r.variacoes.push({nome, opcoes: ops});
  });
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
    t = re.sub(r"(?i),?\s*(selecionad[oa]|indispon[ií]vel|esgotad[oa])\b.*$", "", str(texto)).strip()
    if ":" in t and t.split(":")[0].strip().lower() == nome.lower():
        t = t.split(":", 1)[1].strip()
    return t


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
        fotos, alt, vistas = [], [], set()
        for u in info.get("imagens") or []:
            if "mlstatic" not in u or not re.search(r"\d+-ML[A-Z]\d+_\d+", u):
                continue
            k = _chave_foto_ml(u)
            if k not in vistas:
                vistas.add(k)
                fotos.append(foto_grande_ml(u))
                alt.append(u)
        info["imagens"], info["imagens_alt"] = fotos[:12], alt[:12]
        specs, vistos = [], set()
        for k, v in info.get("specs") or []:
            if k.lower() not in vistos and v:
                vistos.add(k.lower())
                specs.append([k, v])
        info["specs"] = specs
        for p in info.get("variacoes") or []:
            p["opcoes"] = [o for o in dict.fromkeys(_limpar_opcao(p["nome"], x) for x in p["opcoes"]) if o][:20]
        if not info.get("titulo"):
            raise RuntimeError("A página aberta não parece um anúncio do Mercado Livre.")
        return info

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


def salvar_fotos_ml(info, destino, baixar, max_fotos=10):
    """Baixa as fotos de um anúncio lido (info["imagens"]) para a pasta, como 01-ml.jpg, 02-ml.jpg..."""
    nomes = []
    if not baixar or Image is None:
        return nomes
    import io
    alt = info.get("imagens_alt") or []
    for n, url in enumerate((info.get("imagens") or [])[:max_fotos], 1):
        corpo = baixar(url)
        if not corpo and n - 1 < len(alt) and alt[n - 1] != url:
            corpo = baixar(alt[n - 1])
        if not corpo:
            continue
        try:
            with Image.open(io.BytesIO(corpo)) as im:
                im = im.convert("RGB")
                nome = f"{n:02d}-ml.jpg"
                im.save(Path(destino) / nome, "JPEG", quality=90, optimize=True)
                nomes.append(nome)
        except Exception:
            continue
    return nomes


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
    tam = next((p for p in pickers if re.search(r"(?i)tamanho|medida|dimens", p["nome"])), None)
    cor = next((p for p in pickers if re.search(r"(?i)\bcor\b|cores|acabamento", p["nome"])), None)
    valores, por = [], None
    if tam and cor:
        por = "Tamanho e cor"
        valores = [f"{t} · {c}" for t in tam["opcoes"] for c in cor["opcoes"]]
    elif tam or cor or pickers:
        p = tam or cor or pickers[0]
        por = "Tamanho" if p is tam else "Cor" if p is cor else p["nome"].capitalize()
        valores = list(p["opcoes"])
    if valores:
        dados["variacao_por"] = por
        dados["variacoes"] = [dict(json.loads(json.dumps(base)), valor=v) for v in valores[:40]]
    else:
        dados["variacoes"] = [base]
    dados["mercado_livre"] = {"link_referencia": info.get("link"), "titulo_anuncio": titulo[:60],
                              "tipo_anuncio": "classico", "quantidade_anuncio": 10}
    destino = nova_pasta(pasta_produtos, dados)
    nomes = salvar_fotos_ml(info, destino, baixar, max_fotos)
    dados["variacoes"][0]["fotos"] = nomes
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


def rodar(fichas, cfg, avisar, parar, site=None):
    """Cadastra as fichas prontas, uma por vez. avisar(evento, ficha, texto) atualiza a janela."""
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
            if not ficha.pronta:
                continue
            avisar("inicio", ficha, f"Cadastrando {ficha.nome}...")
            tmp = Path(tempfile.mkdtemp(prefix="robo-stylo-"))
            try:
                fotos = preparar_fotos(ficha, tmp, int(r.get("lado_maximo_foto")), int(r.get("tamanho_maximo_foto_kb")))
                res = site.cadastrar(ficha, fotos)
                if res is None:
                    ficha.status, ficha.mensagem = "ja", "Todos os códigos já estavam cadastrados."
                else:
                    ficha.status, ficha.resultado = "feita", res
                    ficha.mensagem = f"{len(res)} produto(s) criado(s)."
                    feitas += 1
                try:
                    (ficha.pasta / "resultado.json").write_text(json.dumps({
                        "quando": datetime.now().isoformat(timespec="seconds"), "site": r.get("site"),
                        "mensagem": ficha.mensagem, "produtos": ficha.resultado}, ensure_ascii=False, indent=2), encoding="utf-8")
                except OSError:
                    pass
                if sim(r.get("mover_feitos")):
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
