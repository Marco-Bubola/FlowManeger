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
    sem_peso = []
    soma_custo = sum((numero(i.get("quantidade")) or 1) * numero(i.get("valor"))
                     for i in d.get("ficha_de_custo") or [] if isinstance(i, dict))
    for i, v in enumerate(variacoes, 1):
        rotulo = str(v.get("valor") or "").strip() or (f"variação {i}" if multi else "produto")
        if numero(v.get("venda")) <= 0:
            f.erros.append(f"{rotulo}: falta o preço de venda.")
        if v.get("custo") in (None, "") and soma_custo <= 0:
            f.avisos.append(f"{rotulo}: sem custo (entra R$ 0,00).")
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

    if sem_peso:
        quais = "" if not multi else (" em " + ", ".join(sem_peso) if len(sem_peso) < len(variacoes) else " em nenhuma variação")
        f.avisos.append(f"Sem peso da embalagem{quais} (o frete do Mercado Livre precisa).")
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


def listar_fichas(pasta_produtos):
    raiz = Path(pasta_produtos)
    raiz.mkdir(parents=True, exist_ok=True)
    fichas = []
    for p in sorted(raiz.iterdir(), key=lambda x: x.name.lower()):
        if p.is_dir() and p.name.lower() not in ("feitos", "_modelo") and not p.name.startswith("."):
            fichas.append(ler_ficha(p))
    return fichas


def nome_de_pasta(dados):
    cod = str(dados.get("codigo") or "").strip()
    nome = str(dados.get("nome") or "produto").strip()
    bruto = f"{cod} - {nome}" if cod else nome
    bruto = re.sub(r'[\\/:*?"<>|]', " ", bruto)
    return re.sub(r"\s+", " ", bruto).strip().rstrip(".")[:90] or "produto"


def criar_pasta_da_ficha(pasta_produtos, texto, fotos):
    """Para o botão "Colar ficha da IA": cria a pasta com ficha.md e copia as fotos."""
    dados = extrair_json(texto)
    destino = Path(pasta_produtos) / nome_de_pasta(dados)
    n = 2
    base = destino
    while destino.exists():
        destino = base.with_name(f"{base.name} ({n})")
        n += 1
    destino.mkdir(parents=True)
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
