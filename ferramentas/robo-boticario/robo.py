# -*- coding: utf-8 -*-
"""
Robô de imagens do FlowManeger.

Para cada produto carregado na tela Produtos > Upload do flowmanger.com:
  1. procura a imagem na pasta de imagens (pelo código e pelo nome);
  2. se não achar, procura o código no site de revendedores do Grupo Boticário,
     baixa a primeira imagem do produto e salva na pasta com o nome do produto;
  3. coloca a imagem no card do produto (como se você tivesse escolhido o arquivo).

O robô NÃO clica em salvar: no final você confere a tela e salva.
"""
import argparse
import base64
import configparser
import csv
import io
import mimetypes
import os
import re
import sys
import time
from datetime import datetime
from pathlib import Path
from urllib.parse import quote, urljoin, urlparse

from rapidfuzz import fuzz
from unidecode import unidecode

try:
    from PIL import Image
except ImportError:  # Pillow é opcional
    Image = None

from playwright.sync_api import sync_playwright, TimeoutError as PWTimeout

BASE = Path(__file__).resolve().parent
EXTS = {".jpg", ".jpeg", ".png", ".webp", ".gif", ".bmp"}
MAX_BYTES = 5 * 1024 * 1024  # limite da tela de upload do FlowManeger
UUID = r"[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}"


# ---------------------------------------------------------------- utilidades

class Parado(Exception):
    """Você clicou em Parar na janela do robô."""


class Controle:
    """Ponte entre o robô e a janela: avisos, pedidos de ação (login, clicar na foto) e o botão Parar."""

    def __init__(self):
        import threading
        self.ouvintes = []
        self.parar = threading.Event()
        self.acao = None          # {"tipo": ..., "titulo": ..., "texto": ..., "botoes": [...]}
        self.resposta = None

    def log(self, msg):
        for f in list(self.ouvintes):
            try:
                f(msg)
            except Exception:
                pass

    def checar(self):
        if self.parar.is_set():
            raise Parado()

    def pedir(self, tipo, titulo, texto, botoes=()):
        self.resposta = None
        self.acao = {"tipo": tipo, "titulo": titulo, "texto": texto, "botoes": list(botoes)}

    def responder(self, valor):
        self.resposta = valor

    def pegar_resposta(self):
        v, self.resposta = self.resposta, None
        return v

    def limpar(self):
        self.acao = None
        self.resposta = None


CONTROLE = Controle()


def log(msg=""):
    try:
        print(msg, flush=True)
    except Exception:
        pass  # pythonw: sem janela preta, sem saída
    CONTROLE.log(str(msg))


def esperar(segundos):
    """time.sleep que obedece o botão Parar."""
    fim = time.time() + segundos
    while time.time() < fim:
        CONTROLE.checar()
        time.sleep(min(0.25, max(0.0, fim - time.time())))


def normalizar(texto):
    """Maiúsculas, sem acento, só letras/números, espaços simples."""
    t = unidecode(str(texto or "")).upper()
    t = re.sub(r"\(\d+\)", " ", t)          # "(2)" de cópias do Windows
    t = re.sub(r"[/\-]", "", t)             # "D/COL" vira "DCOL", "CUIDE-SE" vira "CUIDESE" (igual ao app)
    t = re.sub(r"[^A-Z0-9,]+", " ", t)
    t = t.replace(",", " ")
    return re.sub(r"\s+", " ", t).strip()


PULAR_PADRAO = "CJ SCH | CJ FLAC | DEM | DEMONSTRADOR | AMOSTRA | FLACONETE | SACHE"


def eh_amostra(nome, termos):
    """Amostras e flaconetes não têm foto no site: o robô pula esses produtos."""
    n = normalizar(nome)
    return any(f" {t} " in f" {n} " for t in termos)  # palavra inteira: "DEM" não pega "DEMAQUILANTE"


def tamanhos(texto_normalizado):
    """Tamanhos da embalagem (200ML, 75ML, 250G...). Produto de tamanho diferente não serve."""
    return set(re.findall(r"(\d+)\s*(ML|M|G|KG|L)\b", texto_normalizado))


def so_digitos(codigo):
    return re.sub(r"\D", "", str(codigo or ""))


def nome_limpo_para_arquivo(nome):
    """Igual ao 'copiar nome' do FlowManeger (tira / ( ) -) e sem caracteres proibidos no Windows."""
    n = re.sub(r"[/()\-]", "", str(nome or ""))
    n = re.sub(r'[\\:*?"<>|]', " ", n)
    n = re.sub(r"\s+", " ", n).strip().rstrip(".")
    return (n or "produto")[:150]


def cfg_bool(cfg, chave, padrao):
    return cfg.getboolean("robo", chave, fallback=padrao)


def cfg_lista(cfg, chave, padrao):
    bruto = cfg.get("seletores", chave, fallback="").strip()
    if not bruto:
        return padrao
    return [s.strip() for s in bruto.split("|") if s.strip()]


# ------------------------------------------------------------ pasta de imagens

class PastaImagens:
    def __init__(self, pasta, minimo):
        self.pasta = Path(pasta)
        self.minimo = minimo
        self.recarregar()

    def recarregar(self):
        self.arquivos = []
        if not self.pasta.exists():
            log(f"ATENÇÃO: a pasta de imagens não existe: {self.pasta}")
            return
        for p in self.pasta.iterdir():
            if p.is_file() and p.suffix.lower() in EXTS and "placeholder" not in p.name.lower():
                stem = p.stem
                self.arquivos.append({
                    "path": p,
                    "norm": normalizar(stem),
                    "digitos": re.findall(r"\d{5,}", re.sub(UUID, " ", stem)),
                    "mtime": p.stat().st_mtime,
                })

    def procurar(self, nome, codigo):
        """Devolve (Path, motivo) ou (None, motivo)."""
        cod = so_digitos(codigo)
        # 1) código no nome do arquivo (ex. "...-bot-87408-kit..." ou "B87408")
        if len(cod) >= 5:
            por_codigo = [a for a in self.arquivos if cod in a["digitos"]]
            if por_codigo:
                melhor = max(por_codigo, key=lambda a: a["mtime"])
                return melhor["path"], f"pasta (código {cod})"

        alvo = normalizar(nome)
        if not alvo:
            return None, "sem nome"

        # 2) nome igual depois de normalizar
        iguais = [a for a in self.arquivos if a["norm"] == alvo]
        if iguais:
            return max(iguais, key=lambda a: a["mtime"])["path"], "pasta (nome igual)"

        # 3) nome parecido
        melhor, nota = None, 0
        tam = tamanhos(alvo)
        for a in self.arquivos:
            tam_arq = tamanhos(a["norm"])
            if tam and tam_arq and tam != tam_arq:
                continue
            s = fuzz.token_sort_ratio(alvo, a["norm"])
            if s > nota or (s == nota and melhor and a["mtime"] > melhor["mtime"]):
                melhor, nota = a, s
        if melhor and nota >= self.minimo:
            return melhor["path"], f"pasta (nome parecido {nota:.0f}%)"
        return None, (f"não achou na pasta (mais parecido: {melhor['path'].name} {nota:.0f}%)" if melhor else "pasta vazia")

    def salvar(self, nome, codigo, dados, ext):
        base = nome_limpo_para_arquivo(nome)
        destino = self.pasta / f"{base}{ext}"
        if destino.exists():
            destino = self.pasta / f"{base} - {so_digitos(codigo) or 'novo'}{ext}"
        n = 2
        while destino.exists():
            destino = self.pasta / f"{base} - {so_digitos(codigo)} ({n}){ext}"
            n += 1
        destino.write_bytes(dados)
        self.recarregar()
        return destino


# ------------------------------------------------------------------ imagens

LEVE = 350 * 1024  # foto maior que isso é reduzida antes de ir para a tela (a tela manda todas as fotos a cada clique)


def ajustar_tamanho(dados, ext):
    """Deixa a foto leve: até 1200 px e ~350 KB. Foto pesada deixa a tela de upload lenta e pode falhar ao salvar."""
    if Image is None or (len(dados) <= LEVE and ext in (".jpg", ".jpeg", ".png", ".webp")):
        return dados, ext
    try:
        img = Image.open(io.BytesIO(dados))
        if img.mode in ("RGBA", "LA", "P"):
            fundo = Image.new("RGB", img.size, "white")
            img = img.convert("RGBA")
            fundo.paste(img, mask=img.split()[-1])
            img = fundo
        else:
            img = img.convert("RGB")
        img.thumbnail((1200, 1200))
        buf = io.BytesIO()
        img.save(buf, "JPEG", quality=86, optimize=True)
        return buf.getvalue(), ".jpg"
    except Exception:
        return dados, ext


def extensao_de(url, content_type):
    ct = (content_type or "").split(";")[0].strip().lower()
    ext = mimetypes.guess_extension(ct) if ct else None
    if ext == ".jpe":
        ext = ".jpg"
    if not ext or ext not in EXTS:
        ext = Path(urlparse(url).path).suffix.lower()
    return ext if ext in EXTS else ".jpg"


def url_maior_vtex(url):
    """Imagens VTEX vêm como /arquivos/ids/123-500-500/ ; pede uma maior."""
    return re.sub(r"(/arquivos/ids/\d+)-\d+-\d+", r"\1-1000-1000", url)


# ---------------------------------------------------------------- FlowManeger

class FlowManeger:
    def __init__(self, page, site):
        self.page = page
        self.url_upload = site.rstrip("/") + "/products/upload"

    def abrir(self):
        try:
            resp = self.page.goto(self.url_upload, wait_until="domcontentloaded")
        except Exception as e:
            if "ERR_NAME_NOT_RESOLVED" in str(e) or "ERR_CONNECTION" in str(e):
                log(f"\nNão consegui abrir {self.url_upload}.")
                log("Confira em Ajustes o endereço do FlowManeger (o que você usa no navegador).")
                raise RuntimeError("não consegui abrir o FlowManeger")
            raise
        if self._na_tela_de_login():
            log("Faça login no FlowManeger na janela do Chrome do robô. Vou esperar...")
            CONTROLE.pedir("login_app", "Entre no FlowManeger",
                           "Na janela do Chrome do robô, entre com seu e-mail e senha (o login pelo Google costuma ser "
                           "bloqueado em navegador de robô). Eu continuo sozinho depois.")
            while self._na_tela_de_login():
                esperar(2)
            CONTROLE.limpar()
            esperar(2)
            resp = self.page.goto(self.url_upload, wait_until="domcontentloaded")
        if (resp and resp.status >= 500) or "Server Error" in (self.page.title() or ""):
            log(f"\nO FlowManeger respondeu com erro {resp.status if resp else ''} na tela {self.url_upload}.")
            log("Isso é um erro do site, não do robô. Confira se essa tela abre no seu navegador normal.")
            raise RuntimeError("o FlowManeger respondeu com erro na tela de upload")

    def _na_tela_de_login(self):
        try:
            u = self.page.url
        except Exception:
            return False
        return any(p in u for p in ("/login", "/register", "/forgot-password"))

    def enviar_nota(self, arquivo):
        log(f"Enviando a nota: {arquivo}")
        self.page.set_input_files("#pdf_file", str(arquivo))
        time.sleep(3)  # espera o envio do arquivo terminar
        botao = self.page.locator('button[wire\\:click="processUpload"]').first
        botao.wait_for(state="visible", timeout=30000)
        botao.click()

    def esperar_produtos(self, nota_enviada):
        if not nota_enviada:
            log("Envie a nota (PDF/CSV) na tela de upload da janela do Chrome do robô.")
            CONTROLE.pedir("nota", "Envie a nota no site",
                           "Na janela do Chrome do robô, em Produtos › Upload, envie a nota. Quando os produtos aparecerem, eu começo.")
        log("Esperando os produtos aparecerem na tela...")
        while True:
            if self.page.locator('[id^="image-input-"]').count() > 0:
                break
            esperar(1)
        CONTROLE.limpar()
        esperar(2)  # deixa o Livewire terminar de preencher

    def produtos(self):
        return self.page.evaluate(
            """() => Array.from(document.querySelectorAll('[id^="image-input-"]')).map(inp => {
                const i = inp.id.replace('image-input-', '');
                const val = f => {
                    const el = document.querySelector(`[wire\\\\:model\\\\.lazy="productsUpload.${i}.${f}"]`);
                    return el ? el.value : '';
                };
                const img = document.getElementById('product-image-' + i);
                const src = img ? (img.getAttribute('src') || '') : '';
                return {
                    index: parseInt(i, 10),
                    nome: val('name'),
                    codigo: val('product_code'),
                    tem_imagem: src.startsWith('data:') || (!!src && !src.includes('product-placeholder')),
                };
            })"""
        )

    def colocar_imagem(self, index, arquivo):
        """Entrega o arquivo ao campo de foto do card, como se você tivesse escolhido."""
        dados = Path(arquivo).read_bytes()
        ext = Path(arquivo).suffix.lower()
        dados, ext = ajustar_tamanho(dados, ext)
        mime = mimetypes.types_map.get(ext, "image/jpeg") if ext != ".webp" else "image/webp"

        self.page.set_input_files(
            f"#image-input-{index}",
            files=[{"name": Path(arquivo).stem + ext, "mimeType": mime, "buffer": dados}],
        )
        # confere se a página aceitou; se não, chama o Livewire direto
        for _ in range(20):
            if self._card_tem_imagem(index):
                return True
            time.sleep(0.25)
        b64 = f"data:{mime};base64," + base64.b64encode(dados).decode()
        self.page.evaluate(
            """([i, b64]) => {
                const inp = document.getElementById('image-input-' + i);
                const root = inp && inp.closest('[wire\\\\:id]');
                const img = document.getElementById('product-image-' + i);
                if (img) img.src = b64;
                if (root && window.Livewire) window.Livewire.find(root.getAttribute('wire:id')).call('updateProductImage', i, b64);
            }""",
            [index, b64],
        )
        time.sleep(1.5)
        return self._card_tem_imagem(index)

    def estado_servidor(self):
        """O que o site realmente guardou de cada card (nome, código e se a foto chegou).
        A foto que aparece no card é só a prévia: o que vale na hora de salvar é isto."""
        try:
            return self.page.evaluate(
                """() => {
                    const inp = document.querySelector('[id^="image-input-"]');
                    let el = inp;
                    while (el && !(el.hasAttribute && el.hasAttribute('wire:id'))) el = el.parentElement;
                    if (!el || !window.Livewire) return null;
                    const w = window.Livewire.find(el.getAttribute('wire:id'));
                    if (!w) return null;
                    let lista = null;
                    try { lista = w.get ? w.get('productsUpload') : null; } catch (e) {}
                    if (!lista) lista = w.productsUpload;
                    if (!lista) return null;
                    return Object.entries(lista).map(([i, p]) => ({
                        index: parseInt(i, 10), nome: (p && p.name) || '', codigo: (p && p.product_code) || '',
                        foto: !!(p && (p.image_base64 || p.temp_image)),
                    }));
                }"""
            )
        except Exception:
            return None

    def reenviar_imagem(self, index, arquivo):
        """Manda a foto direto para o site (sem passar pelo campo de arquivo)."""
        dados = Path(arquivo).read_bytes()
        ext = Path(arquivo).suffix.lower()
        dados, ext = ajustar_tamanho(dados, ext)
        mime = "image/webp" if ext == ".webp" else mimetypes.types_map.get(ext, "image/jpeg")
        b64 = f"data:{mime};base64," + base64.b64encode(dados).decode()
        self.page.evaluate(
            """([i, b64]) => {
                let el = document.getElementById('image-input-' + i) || document.querySelector('[id^="image-input-"]');
                while (el && !(el.hasAttribute && el.hasAttribute('wire:id'))) el = el.parentElement;
                const img = document.getElementById('product-image-' + i);
                if (img) img.src = b64;
                if (el && window.Livewire) return window.Livewire.find(el.getAttribute('wire:id')).call('updateProductImage', i, b64);
            }""",
            [index, b64],
        )
        esperar(2)

    def remover_amostras(self, termos):
        """Tira da tela (botão Remover do card) os produtos de amostra, para não serem salvos.
        A tela renumera os cards a cada remoção, então remove um por vez, sempre conferindo o nome."""
        removidos = []
        for _ in range(200):
            alvo = [p for p in self.produtos() if eh_amostra(p["nome"], termos)]
            if not alvo:
                break
            p = max(alvo, key=lambda x: x["index"])
            antes = self.page.locator('[id^="image-input-"]').count()
            self.page.evaluate(
                """i => {
                    const alvo = 'removeProduct(' + i + ')';
                    const b = [...document.querySelectorAll('button, a, div')].find(el => el.getAttribute('wire:click') === alvo);
                    if (b) { b.click(); return; }
                    let el = document.getElementById('image-input-' + i);
                    while (el && !(el.hasAttribute && el.hasAttribute('wire:id'))) el = el.parentElement;
                    if (el && window.Livewire) window.Livewire.find(el.getAttribute('wire:id')).call('removeProduct', i);
                }""",
                p["index"],
            )
            for _ in range(60):
                if self.page.locator('[id^="image-input-"]').count() < antes:
                    break
                time.sleep(0.25)
            else:
                log(f"   não consegui remover {p['nome']} da tela; remova à mão antes de salvar")
                break
            esperar(1)
            removidos.append(p)
            log(f"   removido da tela: {p['codigo']} - {p['nome']}")
            time.sleep(0.5)
        return removidos

    # ---------- código de barras (campo barcode-input-N; só existe nas versões novas da tela)
    def tem_campo_barras(self, index):
        return self.page.locator(f"#barcode-input-{index}").count() > 0

    def codigo_barras(self, index):
        return self.page.evaluate(
            "i => { const el = document.getElementById('barcode-input-' + i); return el ? el.value.trim() : null; }",
            index,
        )

    def buscar_barras_no_app(self, index, espera=15):
        """Clica no botão "Buscar" do próprio card (o app procura o EAN nas lojas do grupo)."""
        self.page.evaluate(
            """i => {
                const alvo = 'lookupBarcode(' + i + ')';
                const b = [...document.querySelectorAll('button, a')].find(el => el.getAttribute('wire:click') === alvo);
                if (b) { b.click(); return; }
                let el = document.getElementById('barcode-input-' + i);
                while (el && !(el.hasAttribute && el.hasAttribute('wire:id'))) el = el.parentElement;
                if (el && window.Livewire) window.Livewire.find(el.getAttribute('wire:id')).call('lookupBarcode', i);
            }""",
            index,
        )
        fim = time.time() + espera
        while time.time() < fim:
            time.sleep(0.5)
            v = self.codigo_barras(index)
            if v:
                return v
        return None

    def colocar_barras(self, index, ean):
        sel = f"#barcode-input-{index}"
        self.page.fill(sel, ean)
        self.page.dispatch_event(sel, "change")
        time.sleep(1)
        if self.codigo_barras(index) != ean:
            self.page.evaluate(
                """([i, ean]) => {
                    let el = document.getElementById('barcode-input-' + i);
                    while (el && !(el.hasAttribute && el.hasAttribute('wire:id'))) el = el.parentElement;
                    if (el && window.Livewire) window.Livewire.find(el.getAttribute('wire:id')).set('productsUpload.' + i + '.barcode', ean);
                }""",
                [index, ean],
            )
            time.sleep(1)
        return self.codigo_barras(index) == ean

    def _card_tem_imagem(self, index):
        return self.page.evaluate(
            "i => { const im = document.getElementById('product-image-' + i); return !!im && (im.getAttribute('src') || '').startsWith('data:'); }",
            index,
        )


# ------------------------------------------------------- site de revendedores

JS_BANNER = """(msg) => {
    let b = document.getElementById('__robo_banner');
    if (!b) {
        b = document.createElement('div');
        b.id = '__robo_banner';
        b.style.cssText = 'position:fixed;z-index:2147483647;left:0;right:0;top:0;padding:12px 16px;background:#7c3aed;color:#fff;font:600 15px/1.4 sans-serif;box-shadow:0 2px 8px #0005';
        document.documentElement.appendChild(b);
    }
    b.innerHTML = msg;
}"""

JS_MODO_ASSISTIDO = """() => {
    window.__roboImg = null;
    if (window.__roboClick) return;
    window.__roboClick = (e) => {
        if (e.target && e.target.id === '__robo_pular') { window.__roboImg = 'PULAR'; e.preventDefault(); e.stopPropagation(); return; }
        if (!e.altKey) return;
        let el = e.target;
        let img = el.tagName === 'IMG' ? el : (el.querySelector && el.querySelector('img'));
        if (!img && el.closest) { const c = el.closest('picture,figure,div'); img = c && c.querySelector('img'); }
        if (img) { window.__roboImg = img.currentSrc || img.src; e.preventDefault(); e.stopPropagation(); }
    };
    document.addEventListener('click', window.__roboClick, true);
}"""


class Revendedores:
    BUSCA_PADRAO = [
        'input[type="search"]',
        'input[placeholder*="usca" i]',
        'input[placeholder*="esquis" i]',
        'input[placeholder*="procur" i]',
        'input[aria-label*="usca" i]',
        'input[name*="search" i]',
        'input[name*="busca" i]',
        'input[id*="search" i]',
    ]
    RESULTADO_PADRAO = [
        'a[href$="/p"]',
        'a[href*="/p?"]',
        '[class*="productSummary" i] a[href]',
        '[class*="product-summary" i] a[href]',
        '[class*="product-card" i] a[href]',
        '[class*="productCard" i] a[href]',
        '[data-testid*="product" i] a[href]',
        'article a[href]',
    ]
    IMAGEM_PADRAO = [
        '[class*="productImage" i] img',
        '[class*="product-image" i] img',
        '[class*="gallery" i] img',
        '[class*="carousel" i] img',
        '[class*="swiper" i] img',
        'main picture img',
    ]

    def __init__(self, ctx, site, cfg):
        self.ctx = ctx
        self._page = None
        self.site = site.rstrip("/")
        # página de pesquisa de produtos de quem já está logado (a raiz do site é a página de cadastro)
        self.pagina_busca = (cfg.get("robo", "pagina_busca", fallback="") or "").strip() or self.site + "/departamentos"
        self.cfg = cfg
        self.busca = cfg_lista(cfg, "campo_busca", self.BUSCA_PADRAO)
        self.resultado = cfg_lista(cfg, "primeiro_resultado", self.RESULTADO_PADRAO)
        self.imagem = cfg_lista(cfg, "imagem_produto", self.IMAGEM_PADRAO)
        self.url_busca = cfg.get("seletores", "url_busca", fallback="").strip()
        self.assistido = cfg_bool(cfg, "modo_assistido", True)
        self.pausa = cfg.getfloat("robo", "pausa_entre_buscas", fallback=3.0)
        self.aberto = False
        self.falhou = False

    @property
    def page(self):
        """Aba do site de revendedores. Se você fechar a aba, o robô abre outra."""
        if self._page is None or self._page.is_closed():
            self._page = self.ctx.new_page()
            self.aberto = False
            self.falhou = False
        return self._page

    def banner(self, msg):
        try:
            self.page.evaluate(JS_BANNER, msg)
        except Exception:
            pass

    def abrir(self):
        """Abre o site (só na primeira vez que precisa). Devolve False se o site não abrir."""
        if self.aberto:
            return True
        if self.falhou:
            return False
        for tentativa in range(3):
            try:
                self.page.goto(self.pagina_busca, wait_until="domcontentloaded", timeout=45000)
                self.aberto = True
                break
            except Exception as e:
                log(f"   O site de revendedores não abriu (tentativa {tentativa + 1}/3): {str(e).splitlines()[0]}")
                time.sleep(3)
        if not self.aberto:
            self.falhou = True
            log("   Sigo só com a pasta. Os produtos sem foto ficam listados no final.")
            return False
        self.garantir_login()
        return True

    def aceitar_cookies(self):
        """Fecha a faixa de cookies (ela cobre a página e atrapalha os cliques)."""
        try:
            self.page.evaluate(
                """() => {
                    const alvo = ['aceitar todos os cookies', 'aceitar todos', 'aceitar cookies', 'aceitar', 'rejeitar todos'];
                    const bt = document.getElementById('onetrust-accept-btn-handler');
                    if (bt && bt.offsetParent) { bt.click(); return true; }
                    const els = [...document.querySelectorAll('button, a[role=button]')].filter(b => b.offsetParent);
                    for (const t of alvo) {
                        const b = els.find(e => (e.innerText || '').trim().toLowerCase() === t);
                        if (b) { b.click(); return true; }
                    }
                    return false;
                }"""
            )
        except Exception:
            pass

    def _na_pagina_de_cadastro(self):
        """A raiz do site é a página 'Seja um revendedor' (botões Já tenho cadastro / Cadastre-se):
        quem cai nela não está logado, mesmo sem pedir senha."""
        try:
            return self.page.evaluate(
                """() => {
                    const vis = [...document.querySelectorAll('a, button')].filter(e => e.offsetParent);
                    const txt = vis.map(e => (e.innerText || '').trim().toLowerCase());
                    return txt.includes('já tenho cadastro') || txt.includes('ja tenho cadastro') ||
                           (txt.includes('cadastre-se') && !document.querySelector('input[type=search]'));
                }"""
            )
        except Exception:
            return False

    def precisa_login(self):
        try:
            u = self.page.url.lower()
        except Exception:
            return False
        if any(p in u for p in ("login", "signin", "sign-in", "entrar", "auth", "acesso", "cadastro")):
            return True
        try:
            if self.page.locator('input[type="password"]:visible').count() > 0:
                return True
        except Exception:
            return False
        return self._na_pagina_de_cadastro()

    def garantir_login(self):
        self.aceitar_cookies()
        if not self.precisa_login():
            return
        log("O site de revendedores pediu login. Entre na janela do Chrome do robô; eu espero.")
        CONTROLE.pedir("login_site", "Entre no site de revendedores",
                       "Na janela do Chrome do robô, clique em “Já tenho cadastro” e entre com seu login. "
                       "Esse Chrome é só do robô: o login feito no seu Chrome normal não vale aqui, mas depois "
                       "da primeira vez ele lembra. Quando entrar, eu volto para a pesquisa sozinho.",
                       ["Já entrei"])
        self.banner("🤖 Robô: clique em <b>Já tenho cadastro</b> e entre. Depois eu continuo sozinho.")
        tentou = time.time()
        while True:
            esperar(2)
            self.aceitar_cookies()
            if CONTROLE.pegar_resposta() == "Já entrei":
                break
            # parado na página inicial (não na tela de senha)? de vez em quando tenta a pesquisa de novo
            try:
                raiz = urlparse(self.page.url).path.strip("/") == ""
            except Exception:
                raiz = False
            if raiz and time.time() - tentou > 12:
                tentou = time.time()
                try:
                    self.page.goto(self.pagina_busca, wait_until="domcontentloaded", timeout=45000)
                except Exception:
                    pass
                esperar(2)
                if not self.precisa_login():
                    break
            if not self.precisa_login():
                # entrou: confere indo para a pesquisa (se voltar para o cadastro, ainda não está logado)
                try:
                    self.page.goto(self.pagina_busca, wait_until="domcontentloaded", timeout=45000)
                except Exception:
                    pass
                esperar(2)
                if not self.precisa_login():
                    break
        CONTROLE.limpar()
        if self.page.url.split("?")[0].rstrip("/") != self.pagina_busca.rstrip("/"):
            try:
                self.page.goto(self.pagina_busca, wait_until="domcontentloaded", timeout=45000)
            except Exception:
                pass
        esperar(2)

    # ---------- estratégia 1: API de busca da loja (VTEX), dentro da sua sessão
    def _api(self, codigo, nome):
        termos = [codigo] + ([nome] if nome else [])
        urls = []
        for t in termos:
            q = quote(t)
            urls += [
                f"{self.site}/api/catalog_system/pub/products/search?fq=alternateIds_RefId:{q}",
                f"{self.site}/api/catalog_system/pub/products/search?ft={q}",
                f"{self.site}/api/io/_v/api/intelligent-search/product_search/?query={q}&count=1",
            ]
        for u in urls:
            try:
                r = self.page.request.get(u, timeout=15000)
                if not r.ok or "json" not in (r.headers.get("content-type") or ""):
                    continue
                data = r.json()
                lista = data.get("products", []) if isinstance(data, dict) else data
                for prod in lista or []:
                    for item in prod.get("items", []) or []:
                        imgs = item.get("images") or []
                        if imgs and imgs[0].get("imageUrl"):
                            return imgs[0]["imageUrl"]
            except Exception:
                continue
        return None

    def buscar_ean(self, codigo):
        """EAN do produto pela busca da loja (VTEX), só pelo código. Devolve texto ou None."""
        codigo = so_digitos(codigo) or codigo
        if not self.abrir():
            return None
        q = quote(codigo)
        for u in [
            f"{self.site}/api/catalog_system/pub/products/search?fq=alternateIds_RefId:{q}",
            f"{self.site}/api/catalog_system/pub/products/search?fq=skuId:{q}",
        ]:
            try:
                r = self.page.request.get(u, timeout=15000)
                if not r.ok or "json" not in (r.headers.get("content-type") or ""):
                    continue
                for prod in r.json() or []:
                    for item in prod.get("items", []) or []:
                        ean = so_digitos(item.get("ean") or "")
                        if 8 <= len(ean) <= 14:
                            return ean
            except Exception:
                continue
        return None

    # ---------- estratégia 2: usar a tela de busca do site
    def _primeiro_visivel(self, seletores, timeout=6000):
        fim = time.time() + timeout / 1000
        while time.time() < fim:
            for s in seletores:
                try:
                    loc = self.page.locator(s)
                    for i in range(min(loc.count(), 8)):
                        el = loc.nth(i)
                        if el.is_visible():
                            return el
                except Exception:
                    continue
            time.sleep(0.4)
        return None

    def _pesquisar(self, codigo):
        if self.url_busca:
            self.page.goto(self.url_busca.replace("{codigo}", quote(codigo)), wait_until="domcontentloaded")
            return True
        if self.page.url.split("?")[0].rstrip("/") != self.pagina_busca.rstrip("/"):
            self.page.goto(self.pagina_busca, wait_until="domcontentloaded")
        self.garantir_login()
        campo = self._primeiro_visivel(self.busca)
        if not campo:
            # às vezes o campo só aparece depois de clicar na lupa
            lupa = self._primeiro_visivel(['button[aria-label*="usca" i]', '[class*="search" i] button', '[class*="Search" i] svg'], 2000)
            if lupa:
                lupa.click()
                campo = self._primeiro_visivel(self.busca, 3000)
        if not campo:
            return False
        campo.click()
        campo.fill("")
        campo.type(codigo, delay=40)
        campo.press("Enter")
        try:
            self.page.wait_for_load_state("networkidle", timeout=12000)
        except PWTimeout:
            pass
        return True

    def _abrir_primeiro_resultado(self, codigo):
        # se a busca já caiu direto na página do produto, não precisa clicar
        if self._achar_imagem_produto(rapido=True):
            return True
        link = self._primeiro_visivel(self.resultado, 8000)
        if not link:
            return False
        link.click()
        try:
            self.page.wait_for_load_state("networkidle", timeout=12000)
        except PWTimeout:
            pass
        return True

    def _achar_imagem_produto(self, rapido=False):
        el = self._primeiro_visivel(self.imagem, 1500 if rapido else 6000)
        if el:
            src = el.evaluate("im => im.currentSrc || im.src")
            if src and not src.startswith("data:"):
                return src
        if rapido:
            return None
        # sem seletor: pega a maior imagem visível da página (normalmente a foto principal)
        return self.page.evaluate(
            """() => {
                let best = null, area = 0;
                for (const im of document.images) {
                    const r = im.getBoundingClientRect();
                    const a = r.width * r.height;
                    const src = im.currentSrc || im.src || '';
                    if (!src || src.startsWith('data:') || /logo|icon|sprite|banner/i.test(src)) continue;
                    if (r.width < 150 || r.height < 150) continue;
                    if (a > area) { area = a; best = src; }
                }
                return best;
            }"""
        )

    def _assistido(self, codigo, nome):
        log("   Não achei sozinho. Na janela do site: abra o produto e clique na foto segurando ALT. Ou clique em Pular.")
        CONTROLE.pedir("foto", f"Ajude com {codigo}",
                       f"Não achei sozinho {codigo} – {nome}. No Chrome do robô, abra o produto e clique na foto "
                       "segurando a tecla ALT.", ["Pular"])
        self.banner(
            f"🤖 Robô: não achei sozinho <b>{codigo}</b> – {nome}. Abra o produto e clique na foto "
            f"<u>segurando ALT</u>. &nbsp;<button id='__robo_pular' style='margin-left:8px;padding:4px 12px;border-radius:6px;border:0;cursor:pointer'>PULAR</button>"
        )
        fim = time.time() + 300
        while time.time() < fim:
            CONTROLE.checar()
            if CONTROLE.pegar_resposta() == "Pular":
                CONTROLE.limpar()
                return None
            try:
                self.page.evaluate(JS_MODO_ASSISTIDO) if not self.page.evaluate("() => !!window.__roboClick") else None
                v = self.page.evaluate("() => window.__roboImg")
                if v == "PULAR":
                    CONTROLE.limpar()
                    return None
                if v:
                    CONTROLE.limpar()
                    return v
                if not self.page.evaluate("() => !!document.getElementById('__robo_banner')"):
                    self.banner(f"🤖 Robô: clique na foto de <b>{codigo}</b> segurando ALT. "
                                f"<button id='__robo_pular' style='margin-left:8px;padding:4px 12px;border-radius:6px;border:0;cursor:pointer'>PULAR</button>")
            except Exception:
                pass  # página navegando
            time.sleep(0.7)
        CONTROLE.limpar()
        return None

    def baixar(self, url):
        url = urljoin(self.page.url, url)
        for tentativa in (url_maior_vtex(url), url):
            try:
                r = self.page.request.get(tentativa, timeout=20000)
                if r.ok and (r.headers.get("content-type") or "").startswith("image/"):
                    return r.body(), extensao_de(tentativa, r.headers.get("content-type"))
            except Exception:
                continue
        return None, None

    def buscar_imagem(self, codigo, nome):
        """Devolve (bytes, ext, origem) ou (None, None, motivo)."""
        codigo = so_digitos(codigo) or codigo
        self.page.bring_to_front()
        if not self.abrir():
            return None, None, "site de revendedores não abriu"
        self.garantir_login()

        url = self._api(codigo, None)
        origem = "site (busca da loja)"
        if not url:
            origem = "site (página do produto)"
            try:
                if self._pesquisar(codigo) and self._abrir_primeiro_resultado(codigo):
                    url = self._achar_imagem_produto()
            except Exception as e:
                log(f"   Erro navegando no site: {e}")
        if not url and self.assistido:
            origem = "site (você clicou na foto)"
            url = self._assistido(codigo, nome)
        self.banner("🤖 Robô trabalhando...")
        esperar(self.pausa)
        if not url:
            return None, None, "não achou no site"
        dados, ext = self.baixar(url)
        if not dados:
            return None, None, f"não conseguiu baixar {url}"
        return dados, ext, origem


# ------------------------------------------------- lojas públicas do grupo

class LojasPublicas:
    """Sites das lojas do grupo (boticario.com.br, eudora.com.br...). Roda do seu computador, então não é bloqueado
    como o servidor do site. Dá o código de barras (EAN) e uma foto grande do produto."""

    LOJAS_PADRAO = "https://www.boticario.com.br | https://www.eudora.com.br | https://www.quemdisseberenice.com.br | https://www.oui.com.br"

    def __init__(self, ctx, cfg):
        self.ctx = ctx
        bruto = cfg.get("robo", "lojas_publicas", fallback=self.LOJAS_PADRAO)
        self.lojas = [u.strip().rstrip("/") for u in bruto.split("|") if u.strip()]
        self.cache = {}
        self.fora = set()  # loja que não respondeu: não insiste

    def _get(self, url):
        try:
            r = self.ctx.request.get(url, timeout=12000, headers={"Accept": "application/json"})
            if r.ok and "json" in (r.headers.get("content-type") or ""):
                return r.json()
        except Exception:
            pass
        return None

    @staticmethod
    def _refs(prod, item):
        refs = {str(prod.get("productReference") or ""), str(prod.get("productReferenceCode") or "")}
        for r in item.get("referenceId") or []:
            refs.add(str(r.get("Value") or ""))
        refs.add(str(item.get("itemId") or ""))
        return {so_digitos(r) for r in refs if r}

    def _escolher(self, lista, codigo, nome):
        alvo = normalizar(nome)
        melhor, nota_m = None, 0
        for prod in lista or []:
            if not isinstance(prod, dict):
                continue
            for item in prod.get("items") or []:
                imgs = [i.get("imageUrl") for i in item.get("images") or [] if i.get("imageUrl")]
                achado = {"ean": so_digitos(item.get("ean") or ""), "imagem": imgs[0] if imgs else None,
                          "nome": prod.get("productName") or item.get("name") or ""}
                if codigo and codigo in self._refs(prod, item):
                    return achado
                if alvo:
                    n = normalizar(achado["nome"])
                    tam_a, tam_b = tamanhos(alvo), tamanhos(n)
                    if tam_a and tam_b and tam_a != tam_b:
                        continue
                    nota = fuzz.token_set_ratio(alvo, n)
                    if nota > nota_m:
                        melhor, nota_m = achado, nota
        return melhor if nota_m >= 82 else None

    # As lojas do grupo deixaram de ser VTEX: hoje os links são /produto/<nome>?q=E90083
    # (letra da marca + código da nota). Então o robô usa a busca do próprio site, numa aba
    # escondida do Chrome dele, e confere o código no link antes de aceitar o produto.
    PREFIXOS = {"boticario": "B", "eudora": "E", "quemdisseberenice": "Q", "oui": "O"}
    BUSCAS = ["/busca?q={q}", "/pesquisa?q={q}", "/search?q={q}", "/busca/{q}", "/{q}?map=ft"]

    def _prefixo(self, loja):
        host = urlparse(loja).netloc.lower()
        for k, v in self.PREFIXOS.items():
            if k in host:
                return v
        return ""

    def _aba(self):
        if getattr(self, "aba", None) is None or self.aba.is_closed():
            self.aba = self.ctx.new_page()
        return self.aba

    def _diagnostico(self, nome, html):
        """Guarda a página que não deu certo (até 8 por nota) para dar para entender depois."""
        self.diags = getattr(self, "diags", 0)
        if self.diags >= 8 or not html:
            return
        self.diags += 1
        try:
            pasta = BASE / "diagnostico"
            pasta.mkdir(exist_ok=True)
            (pasta / (re.sub(r"[^\w.-]+", "_", nome)[:80] + ".html")).write_text(html, encoding="utf-8")
        except Exception:
            pass

    def _links_produto(self, pg):
        try:
            pg.wait_for_selector('a[href*="/produto/"], a[href$="/p"], a[href*="/p?"]', timeout=7000)
        except Exception:
            pass
        try:
            return pg.evaluate(
                r"""() => {
                    const vistos = new Set(), out = [];
                    for (const a of document.querySelectorAll('a[href]')) {
                        const h = a.href;
                        if (!/\/produto\/|\/p($|\?)/.test(h) || vistos.has(h)) continue;
                        vistos.add(h);
                        const img = a.querySelector('img');
                        const t = (a.innerText || (img && img.alt) || a.title || '').trim().split('\n')[0];
                        out.push({href: h, texto: t});
                    }
                    return out.slice(0, 60);
                }"""
            ) or []
        except Exception:
            return []

    def _dados_produto(self, pg):
        """EAN, foto grande, nome e os códigos que aparecem na página do produto."""
        try:
            return pg.evaluate(
                r"""() => {
                    const html = document.documentElement.outerHTML;
                    let ean = null;
                    for (const s of document.querySelectorAll('script[type="application/ld+json"]')) {
                        const m = (s.textContent || '').match(/"gtin(?:13|14|12|8)?"\s*:\s*"?(\d{8,14})/i);
                        if (m) { ean = m[1]; break; }
                    }
                    if (!ean) {
                        const m = html.match(/["']?(?:ean|ean13|gtin13|gtin|barcode|codigoDeBarras|codigo_barras|eanCode)["']?\s*[:=]\s*["']?(\d{8,14})/i);
                        if (m) ean = m[1];
                    }
                    if (!ean) {
                        const t = document.body ? document.body.innerText : '';
                        const m = t.match(/(?:EAN|GTIN|c[oó]d(?:igo)?\.?\s*de\s*barras)\s*[:#]?\s*(\d{8,14})/i);
                        if (m) ean = m[1];
                    }
                    const og = document.querySelector('meta[property="og:image"]');
                    let imagem = og && og.content;
                    if (!imagem) {
                        let maior = 0;
                        for (const i of document.images) {
                            const a = (i.naturalWidth || 0) * (i.naturalHeight || 0);
                            if (a > maior && i.naturalWidth >= 300) { maior = a; imagem = i.currentSrc || i.src; }
                        }
                    }
                    const h1 = document.querySelector('h1');
                    return {ean, imagem: imagem || null, nome: (h1 && h1.innerText.trim()) || document.title, html};
                }"""
            )
        except Exception:
            return None

    def _pela_pagina(self, loja, codigo, nome):
        """Pesquisa no site da loja como uma pessoa faria e abre o produto certo."""
        if loja in self.sem_pagina:
            return None
        pg = self._aba()
        pre = self._prefixo(loja)
        buscas = []
        if codigo:
            buscas += [pre + codigo] if pre else []
            buscas += [codigo]
        if nome:
            buscas.append(nome[:60])
        padroes = [self.padrao[loja]] if loja in self.padrao else self.BUSCAS
        alvo = normalizar(nome)
        for q in buscas:
            por_codigo = q != nome[:60]
            for padrao in padroes:
                CONTROLE.checar()
                url = loja + padrao.format(q=quote(q))
                try:
                    pg.goto(url, wait_until="domcontentloaded", timeout=20000)
                except Exception:
                    continue
                links = self._links_produto(pg)
                if not links:
                    continue
                self.padrao.setdefault(loja, padrao)  # esta loja pesquisa assim
                escolhido = None
                if codigo:  # o link traz o código da nota (ex.: ?q=E89767): é ele
                    for l in links:
                        if re.search(rf"(?<!\d){re.escape(codigo)}(?!\d)", l["href"]):
                            escolhido = l
                            break
                if not escolhido and alvo:
                    melhor, nota_m = None, 0
                    for l in links:
                        n = normalizar(l["texto"])
                        if not n:
                            continue
                        if tamanhos(alvo) and tamanhos(n) and tamanhos(alvo) != tamanhos(n):
                            continue
                        nota = fuzz.token_set_ratio(alvo, n)
                        if nota > nota_m:
                            melhor, nota_m = l, nota
                    if nota_m >= 82:
                        escolhido = melhor
                if not escolhido and por_codigo and len(links) <= 3:
                    escolhido = links[0]  # busca pelo código com 1 a 3 resultados: confere na página
                if not escolhido:
                    break  # a busca funcionou, mas nada bate: próxima pesquisa
                try:
                    pg.goto(escolhido["href"], wait_until="domcontentloaded", timeout=20000)
                    pg.wait_for_timeout(1200)
                except Exception:
                    break
                d = self._dados_produto(pg) or {}
                html = d.pop("html", "") or ""
                tem_codigo = bool(codigo) and (re.search(rf"(?<!\d){re.escape(codigo)}(?!\d)", html + escolhido["href"]) is not None)
                n = normalizar(d.get("nome") or "")
                parecido = bool(alvo and n) and fuzz.token_set_ratio(alvo, n) >= 82 and not (
                    tamanhos(alvo) and tamanhos(n) and tamanhos(alvo) != tamanhos(n))
                if not (tem_codigo or parecido):
                    self._diagnostico(f"{urlparse(loja).netloc}-{codigo}-produto-errado", html)
                    break
                ean = so_digitos(d.get("ean") or "")
                if not (8 <= len(ean) <= 14):
                    self._diagnostico(f"{urlparse(loja).netloc}-{codigo}-sem-ean", html)
                    ean = ""
                return {"ean": ean, "imagem": d.get("imagem"), "nome": d.get("nome") or ""}
        if loja not in self.padrao:
            self.falhas[loja] = self.falhas.get(loja, 0) + 1
            if self.falhas[loja] == 1:
                try:
                    self._diagnostico(f"{urlparse(loja).netloc}-{codigo}-busca", pg.content())
                except Exception:
                    pass
            if self.falhas[loja] >= 3:  # a pesquisa desse site não abre: não perde mais tempo nele
                self.sem_pagina.add(loja)
        return None

    def buscar(self, codigo, nome):
        """Devolve {"ean", "imagem", "nome", "loja"} ou None."""
        codigo = so_digitos(codigo)
        chave = (codigo, nome)
        if chave in self.cache:
            return self.cache[chave]
        for attr, vazio in (("padrao", {}), ("falhas", {}), ("sem_pagina", set())):
            if not hasattr(self, attr):
                setattr(self, attr, vazio)
        achado = None
        # a loja que deu certo da última vez vem primeiro
        ordem = sorted(self.lojas, key=lambda l: 0 if l == getattr(self, "ultima", None) else 1)
        for loja in ordem:
            if loja not in self.fora:  # 1) catálogo VTEX antigo, se a loja ainda tiver
                CONTROLE.checar()
                tentativas = []
                if codigo:
                    for ref in (codigo, self._prefixo(loja) + codigo, codigo.lstrip("0")):
                        tentativas.append(f"{loja}/api/catalog_system/pub/products/search?fq=alternateIds_RefId:{quote(ref)}")
                if nome:
                    tentativas.append(f"{loja}/api/catalog_system/pub/products/search?ft={quote(nome[:60])}")
                respondeu = False
                for u in tentativas:
                    dados = self._get(u)
                    if dados is None:
                        if not respondeu:
                            break  # nem respondeu: não é mais VTEX
                        continue
                    respondeu = True
                    achado = self._escolher(dados if isinstance(dados, list) else dados.get("products", []), codigo, nome)
                    if achado:
                        break
                if not respondeu:
                    self.fora.add(loja)
            if not achado:  # 2) pesquisa do site, como uma pessoa
                try:
                    achado = self._pela_pagina(loja, codigo, nome)
                except Parado:
                    raise
                except Exception:
                    achado = None
            if achado and (achado.get("ean") or achado.get("imagem")):
                achado["loja"] = urlparse(loja).netloc.replace("www.", "")
                self.ultima = loja
                break
            achado = None
        self.cache[chave] = achado
        return achado

    def baixar(self, url):
        for tentativa in (url_maior_vtex(url), url):
            try:
                r = self.ctx.request.get(tentativa, timeout=20000)
                if r.ok and (r.headers.get("content-type") or "").startswith("image/"):
                    return r.body(), extensao_de(tentativa, r.headers.get("content-type"))
            except Exception:
                continue
        return None, None


def ean_da_pagina(page):
    """Procura o código de barras escrito na página do produto (EAN, GTIN, código de barras)."""
    try:
        return page.evaluate(
            r"""() => {
                for (const s of document.querySelectorAll('script[type="application/ld+json"]')) {
                    const m = (s.textContent || '').match(/"gtin1?[0-9]?"\s*:\s*"(\d{8,14})"/);
                    if (m) return m[1];
                }
                const t = document.body ? document.body.innerText : '';
                const m = t.match(/(?:EAN|GTIN|c[oó]d(?:igo)?\.?\s*de\s*barras)\s*[:#]?\s*(\d{8,14})/i);
                return m ? m[1] : null;
            }"""
        )
    except Exception:
        return None


# ---------------------------------------------------------------------- main

def abrir_navegador(pw, cfg):
    """Usa o Google Chrome (ou o Edge) instalado no computador. Alguns sites, como o de
    revendedores, recusam o navegador de teste do Playwright. Se não tiver nenhum, usa o dele."""
    preferido = cfg.get("robo", "navegador", fallback="chrome").strip().lower()
    canais = [c for c in [preferido, "chrome", "msedge"] if c and c != "playwright"]
    if os.environ.get("ROBO_CHROMIUM"):
        canais = []
    opcoes = dict(
        headless=os.environ.get("ROBO_HEADLESS") == "1",
        no_viewport=True,  # a página usa a janela inteira, como um Chrome normal
        args=["--start-maximized"],
        # sem "--no-sandbox": tira o aviso amarelo do Chrome (no Windows não precisa)
        ignore_default_args=["--enable-automation"] + ([] if os.environ.get("ROBO_CHROMIUM") else ["--no-sandbox"]),
        accept_downloads=True,
    )
    for canal in dict.fromkeys(canais):
        try:
            ctx = pw.chromium.launch_persistent_context(str(BASE / f"perfil-{canal}"), channel=canal, **opcoes)
            log(f"Navegador: {'Google Chrome' if canal == 'chrome' else 'Microsoft Edge' if canal == 'msedge' else canal}")
            return ctx
        except Exception as e:
            log(f"Não consegui abrir o {canal}: {str(e).splitlines()[0]}")
    log("Navegador: Chromium do robô")
    return pw.chromium.launch_persistent_context(
        str(BASE / "perfil-navegador"),
        executable_path=os.environ.get("ROBO_CHROMIUM") or None,
        **opcoes,
    )


def preencher_barras(fm, rev, lojas, i, codigo, nome):
    """Preenche o código de barras do card se estiver vazio. Devolve o texto para o relatório."""
    if not fm.tem_campo_barras(i):
        return ""  # tela antiga, sem o campo
    atual = fm.codigo_barras(i)
    if atual:
        log(f"   código de barras: já tinha ({atual})")
        return f"já tinha {atual}"
    # 1) catálogo público das lojas do grupo (do seu computador)
    if lojas:
        achado = lojas.buscar(codigo, nome)
        try:
            fm.page.bring_to_front()
        except Exception:
            pass
        ean = achado and achado.get("ean")
        if ean and 8 <= len(ean) <= 14 and fm.colocar_barras(i, ean):
            log(f"   código de barras: {ean} ({achado['loja']})")
            return ean
    # 2) botão Buscar do próprio card
    ean = fm.buscar_barras_no_app(i, espera=8)
    if ean:
        log(f"   código de barras: {ean} (busca do app)")
        return ean
    # 3) site de revendedores
    if rev and rev.aberto:
        try:
            ean = rev.buscar_ean(codigo)
        except Parado:
            raise
        except Exception:
            ean = None
        fm.page.bring_to_front()
        if ean and fm.colocar_barras(i, ean):
            log(f"   código de barras: {ean} (site de revendedores)")
            return ean
    log("   código de barras: não achei")
    return "não achou"


def ler_config():
    cfg = configparser.ConfigParser()
    cfg.BOOLEAN_STATES = {**cfg.BOOLEAN_STATES, "sim": True, "s": True, "nao": False, "não": False, "n": False}
    if not (BASE / "config.ini").exists() and (BASE / "config-padrao.ini").exists():
        (BASE / "config.ini").write_bytes((BASE / "config-padrao.ini").read_bytes())
    cfg.read(BASE / "config.ini", encoding="utf-8-sig")
    for sec in ("robo", "seletores"):
        if not cfg.has_section(sec):
            cfg.add_section(sec)
    return cfg


class Ouvinte:
    """O que a janela quer saber enquanto o robô trabalha. A versão de linha de comando não precisa de nada."""

    def lista(self, produtos):
        pass

    def produto(self, n, dados):
        pass

    def fim(self, resumo):
        pass


def rodar(cfg, nota=None, so_pasta=False, ouvinte=None, esperar_fim=None):
    """Faz o trabalho todo. esperar_fim() segura o navegador aberto até você conferir e salvar."""
    ouvinte = ouvinte or Ouvinte()
    pasta = PastaImagens(cfg.get("robo", "pasta_imagens", fallback=""), cfg.getint("robo", "similaridade_minima", fallback=90))
    pular = [normalizar(t) for t in cfg.get("robo", "pular_produtos", fallback=PULAR_PADRAO).split("|") if t.strip()]
    remover = cfg_bool(cfg, "remover_amostras", True)
    site_app = cfg.get("robo", "site_app", fallback="https://flowmaneger.com")
    site_rev = cfg.get("robo", "site_revendedor", fallback="https://revendedores.grupoboticario.com.br")
    usar_site = cfg_bool(cfg, "usar_site_revendedor", True) and not so_pasta
    usar_lojas = cfg_bool(cfg, "usar_lojas_publicas", True) and not so_pasta
    barras = cfg_bool(cfg, "preencher_codigo_barras", True)

    relatorio = []
    nome_rel = None
    with sync_playwright() as pw:
        ctx = abrir_navegador(pw, cfg)
        try:
            app_page = ctx.pages[0] if ctx.pages else ctx.new_page()
            fm = FlowManeger(app_page, site_app)
            fm.abrir()
            rev = Revendedores(ctx, site_rev, cfg) if usar_site else None
            lojas = LojasPublicas(ctx, cfg) if usar_lojas else None

            if nota:
                fm.enviar_nota(nota)
            fm.esperar_produtos(bool(nota))

            # amostras saem ANTES de tudo: tirar um card depois renumera os outros e pode bagunçar as fotos
            amostras = []
            if remover and any(eh_amostra(p["nome"], pular) for p in fm.produtos()):
                log("Tirando as amostras e flaconetes da tela...")
                amostras = fm.remover_amostras(pular)
                restam = [p for p in fm.produtos() if eh_amostra(p["nome"], pular)]
                if restam:
                    log(f"   ATENÇÃO: {len(restam)} amostra(s) continuam na tela; tire com o botão Remover antes de salvar.")
                srv = fm.estado_servidor()
                if srv is not None and any(eh_amostra(x["nome"], pular) for x in srv):
                    log("   ATENÇÃO: o site ainda tem amostra na lista; confira antes de salvar.")
                esperar(1)

            lista = fm.produtos()
            log(f"\n{len(lista)} produtos na tela.\n")
            ouvinte.lista([{"codigo": p["codigo"].strip(), "nome": p["nome"].strip()} for p in lista]
                          + [{"codigo": a["codigo"].strip(), "nome": a["nome"].strip()} for a in amostras])
            colocadas = {}  # index -> arquivo
            for n, p in enumerate(lista, 1):
                CONTROLE.checar()
                nome, codigo, i = p["nome"].strip(), p["codigo"].strip(), p["index"]
                log(f"[{n}/{len(lista)}] {codigo} - {nome}")
                linha = {"codigo": codigo, "nome": nome, "resultado": "", "arquivo": "", "origem": ""}
                ouvinte.produto(n, {**linha, "estado": "trabalhando"})

                if eh_amostra(nome, pular):
                    linha["resultado"] = "amostra: não saiu da tela (tire à mão)" if remover else "amostra: pulada"
                    linha["origem"] = "amostra"
                    log("   amostra/flaconete, pulando" + (" (vou tirar da tela no final)" if remover else ""))
                    relatorio.append(linha)
                    ouvinte.produto(n, {**linha, "estado": "amostra"})
                    continue

                if barras:
                    linha["codigo_barras"] = preencher_barras(fm, rev, lojas, i, codigo, nome)
                    app_page.bring_to_front()

                if p["tem_imagem"]:
                    linha["resultado"] = "já tinha imagem"
                    linha["origem"] = "ja"
                    log("   já tem imagem, pulando")
                    relatorio.append(linha)
                    ouvinte.produto(n, {**linha, "estado": "ok"})
                    continue

                arquivo, motivo = pasta.procurar(nome, codigo)
                if arquivo:
                    linha["origem"] = "pasta"
                if not arquivo and lojas:
                    achado = lojas.buscar(codigo, nome)
                    try:
                        fm.page.bring_to_front()
                    except Exception:
                        pass
                    if achado and achado.get("imagem"):
                        dados, ext = lojas.baixar(achado["imagem"])
                        if dados:
                            arquivo = pasta.salvar(nome, codigo, dados, ext)
                            motivo, linha["origem"] = f"loja {achado['loja']}", "loja"
                            log(f"   foto da loja {achado['loja']}: salva em {arquivo.name}")
                if not arquivo and rev:
                    log(f"   {motivo}; procurando no site de revendedores...")
                    try:
                        dados, ext, origem = rev.buscar_imagem(codigo, nome)
                    except Parado:
                        raise
                    except Exception as e:
                        dados, ext, origem = None, None, f"erro no site: {str(e).splitlines()[0]}"
                    if barras and linha.get("codigo_barras") == "não achou" and rev.aberto:
                        ean = ean_da_pagina(rev.page)
                        if ean and fm.colocar_barras(i, ean):
                            linha["codigo_barras"] = ean
                            log(f"   código de barras: {ean} (página do produto)")
                    app_page.bring_to_front()
                    if dados:
                        arquivo = pasta.salvar(nome, codigo, dados, ext)
                        motivo, linha["origem"] = origem, "site"
                        log(f"   salvo em {arquivo.name}")
                    else:
                        motivo = origem

                if arquivo:
                    ok = fm.colocar_imagem(i, arquivo)
                    colocadas[i] = (arquivo, nome, codigo, n)
                    linha["resultado"] = motivo if ok else f"{motivo} (não entrou no card)"
                    linha["arquivo"] = arquivo.name
                    log(f"   {'OK' if ok else 'FALHOU ao colocar no card'}: {motivo} -> {arquivo.name}")
                    estado = "ok" if ok else "erro"
                else:
                    linha["resultado"] = f"sem imagem: {motivo}"
                    linha["origem"] = "sem"
                    log(f"   sem imagem: {motivo}")
                    estado = "sem"
                relatorio.append(linha)
                ouvinte.produto(n, {**linha, "estado": estado})

            for k, a in enumerate(amostras, len(lista) + 1):
                relatorio.append({"codigo": a["codigo"].strip(), "nome": a["nome"].strip(), "resultado": "amostra: removida da tela",
                                  "arquivo": "", "origem": "amostra"})
                ouvinte.produto(k, {"estado": "amostra", "origem": "amostra", "resultado": "amostra: removida da tela"})

            # confere no site se cada foto chegou mesmo (a prévia do card pode aparecer sem o site ter recebido)
            if colocadas:
                log("\nConferindo no site se todas as fotos chegaram...")
                app_page.bring_to_front()
                for tentativa in range(3):
                    esperar(2)
                    srv = fm.estado_servidor()
                    if srv is None:
                        log("   não deu para conferir pelo site (tela diferente); confira as fotos antes de salvar")
                        break
                    por_index = {x["index"]: x for x in srv}
                    faltam = [(i, v) for i, v in colocadas.items()
                              if not por_index.get(i, {}).get("foto") or normalizar(por_index.get(i, {}).get("nome")) != normalizar(v[1])]
                    if not faltam:
                        log(f"   todas as {len(colocadas)} fotos chegaram no site")
                        break
                    for i, (arq, nome, codigo, n) in faltam:
                        alvo = next((x["index"] for x in srv if normalizar(x["nome"]) == normalizar(nome)
                                     and so_digitos(x["codigo"]) == so_digitos(codigo)), None)
                        if alvo is None:
                            continue
                        log(f"   foto de {codigo} não tinha chegado; mandando de novo")
                        fm.reenviar_imagem(alvo, arq)
                        if alvo != i:
                            colocadas[alvo] = colocadas.pop(i)
                else:
                    srv = fm.estado_servidor() or []
                    sem = [x for x in srv if not x["foto"] and any(normalizar(x["nome"]) == normalizar(v[1]) for v in colocadas.values())]
                    for x in sem:
                        log(f"   ATENÇÃO: a foto de {x['codigo']} {x['nome']} não chegou no site; coloque à mão antes de salvar")
                        for r in relatorio:
                            if r["codigo"] == x["codigo"] and r["nome"] == x["nome"]:
                                r["resultado"] += " (NÃO chegou no site)"

            nome_rel = BASE / "relatorios" / f"relatorio-{datetime.now():%Y-%m-%d_%H-%M}.csv"
            nome_rel.parent.mkdir(exist_ok=True)
            with open(nome_rel, "w", newline="", encoding="utf-8-sig") as f:
                w = csv.DictWriter(f, fieldnames=["codigo", "nome", "resultado", "arquivo", "codigo_barras"], delimiter=";",
                                   restval="", extrasaction="ignore")
                w.writeheader()
                w.writerows(relatorio)

            sem = [r for r in relatorio if r["resultado"].startswith("sem imagem")]
            sem_barras = [r for r in relatorio if r.get("codigo_barras", "").startswith("não achou")]
            log("\n================ RESUMO ================")
            log(f"Produtos: {len(relatorio)} | sem imagem: {len(sem)}" + (f" | sem código de barras: {len(sem_barras)}" if barras else ""))
            for r in sem:
                log(f"  - {r['codigo']} {r['nome']}")
            if sem_barras:
                log("Sem código de barras (digite ou leia com o leitor antes de salvar):")
                for r in sem_barras:
                    log(f"  - {r['codigo']} {r['nome']}")
            log(f"Relatório: {nome_rel.name}")
            log("\nConfira as imagens na tela do FlowManeger e clique em SALVAR.")
            ouvinte.fim({"relatorio": str(nome_rel), "produtos": relatorio})
            app_page.bring_to_front()
            if esperar_fim:
                esperar_fim(ctx)
        finally:
            try:
                ctx.close()
            except Exception:
                pass
    return relatorio, nome_rel


def main():
    ap = argparse.ArgumentParser(description="Robô de imagens do FlowManeger")
    ap.add_argument("--nota", help="PDF/CSV da nota para enviar automaticamente")
    ap.add_argument("--so-pasta", action="store_true", help="não usa o site de revendedores")
    ap.add_argument("--testar-pasta", nargs=2, metavar=("NOME", "CODIGO"), help="só testa a busca na pasta e sai")
    args = ap.parse_args()
    cfg = ler_config()
    if args.testar_pasta:
        pasta = PastaImagens(cfg.get("robo", "pasta_imagens"), cfg.getint("robo", "similaridade_minima", fallback=90))
        log(str(pasta.procurar(*args.testar_pasta)))
        return

    def segurar(ctx):
        log("Quando terminar, feche a janela do navegador ou aperte Enter aqui.")
        try:
            input()
        except EOFError:
            pass

    rodar(cfg, args.nota, args.so_pasta, esperar_fim=segurar)


if __name__ == "__main__":
    try:
        main()
    except (KeyboardInterrupt, Parado):
        log("\nRobô parado.")
        sys.exit(1)
