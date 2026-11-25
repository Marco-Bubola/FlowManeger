# -*- coding: utf-8 -*-
"""
Robô de cadastro · Móveis Stylo (janela).

Fichas: lista com busca, filtros e ordem; editar (com máscaras de dinheiro e
números), duplicar, excluir e cadastrar no FlowManeger.
Mercado Livre: busca um produto, lê o anúncio e cria uma ficha nova com as fotos.
"""
import io
import json
import os
import queue
import re
import shutil
import subprocess
import sys
import tempfile
import threading
import time
import traceback
import webbrowser
from datetime import datetime
from pathlib import Path
from tkinter import filedialog, messagebox

import customtkinter as ctk

ICONE = Path(__file__).with_name("icone.ico")
if sys.platform.startswith("win"):
    # Sem isto o Windows mostra o ícone do Python na barra de tarefas e o do customtkinter nas janelas.
    try:
        import ctypes
        ctypes.windll.shell32.SetCurrentProcessExplicitAppUserModelID("MoveisStylo.RoboCadastro")
    except Exception:
        pass

    def _icone_stylo(janela):
        try:
            if ICONE.exists():
                janela.iconbitmap(str(ICONE))
        except Exception:
            pass

    for _cls in (ctk.CTk, ctk.CTkToplevel):
        if hasattr(_cls, "_windows_set_titlebar_icon"):
            _cls._windows_set_titlebar_icon = _icone_stylo

try:
    from PIL import Image, ImageDraw, ImageOps
except ImportError:
    Image = None

import motor
import recursos

# ------------------------------------------------------------------ visual

FAMILIA = "Segoe UI" if sys.platform.startswith("win") else None

ROXO = "#6d28d9"
ROXO_H = "#5b21b6"
VERDE = "#059669"
VERMELHO = "#e11d48"
AMBAR = "#d97706"
AMARELO_ML = "#ffe600"

FUNDO = ("#eceefb", "#0b0f1a")
PAINEL = ("#ffffff", "#141a28")
CARD = ("#ffffff", "#1b2130")
CARD_H = ("#faf8ff", "#202739")
BORDA = ("#e6e3f0", "#2a3142")
SUAVE = ("#f3f1fa", "#20273a")
TEXTO = ("#1d1a2b", "#e9e7f2")
MUDO = ("#6b6880", "#9896ab")
ROXO_CLARO = ("#ede9fe", "#2c2450")
ROXO_TXT = ("#6d28d9", "#c4b5fd")
LATERAL = ("#1c1033", "#090612")
# "vidro": cartões claros e translúcidos sobre o fundo lilás, com borda branca
VIDRO = ("#f4f5ff", "#1a2236")
VIDRO_BORDA = ("#ffffff", "#2c3650")

STATUS = {
    "pronta": ("Pronta", VERDE),
    "erro": ("Com erro", VERMELHO),
    "feita": ("Cadastrada", ROXO),
    "editada": ("Editada · subir", AMBAR),
    "ja": ("Já existia", "#64748b"),
    "falhou": ("Falhou", AMBAR),
    "rodando": ("Cadastrando…", ROXO),
}

CATEGORIAS = ["Suportes de vaso", "Mão francesa", "Prateleiras", "Bicicleta", "Mesas", "Banquetas",
              "Decoração", "Organizadores", "Jardim", "Outros"]

_fontes = {}


def F(tam=13, peso="normal"):
    k = (tam, peso)
    if k not in _fontes:
        _fontes[k] = ctk.CTkFont(family=FAMILIA, size=tam, weight=peso) if FAMILIA else ctk.CTkFont(size=tam, weight=peso)
    return _fontes[k]


def reais(v):
    if v in (None, ""):
        return ""
    s = f"{float(v):,.2f}"
    return s.replace(",", "X").replace(".", ",").replace("X", ".")


def decimal_txt(v):
    if v in (None, ""):
        return ""
    v = float(v)
    return (str(int(v)) if v == int(v) else f"{v:.3f}".rstrip("0").rstrip(".")).replace(".", ",")


def abrir_no_sistema(caminho):
    caminho = str(caminho)
    try:
        if sys.platform.startswith("win"):
            os.startfile(caminho)  # noqa
        elif sys.platform == "darwin":
            subprocess.Popen(["open", caminho])
        else:
            subprocess.Popen(["xdg-open", caminho])
    except Exception:
        pass


_minis = {}


def _arredondar(im, raio):
    mask = Image.new("L", im.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, im.size[0] - 1, im.size[1] - 1), raio, fill=255)
    fundo = Image.new("RGBA", im.size, (0, 0, 0, 0))
    fundo.paste(im, mask=mask)
    return fundo


def miniatura(origem, lado, raio=12):
    """CTkImage quadrada e arredondada de um arquivo (ou bytes). Fica guardada em cache."""
    if Image is None or not origem:
        return None
    if isinstance(origem, (bytes, bytearray)):
        chave = (hash(bytes(origem[:4096])), len(origem), lado)
    else:
        try:
            chave = (str(origem), Path(origem).stat().st_mtime, lado)
        except OSError:
            return None
    if chave in _minis:
        return _minis[chave]
    try:
        fonte = io.BytesIO(origem) if isinstance(origem, (bytes, bytearray)) else origem
        with Image.open(fonte) as im:
            im = ImageOps.exif_transpose(im).convert("RGB")
            im = ImageOps.pad(im, (lado * 2, lado * 2), color=(255, 255, 255))
            im = _arredondar(im, raio * 2)
            img = ctk.CTkImage(light_image=im, dark_image=im, size=(lado, lado))
    except Exception:
        img = None
    if len(_minis) > 600:
        _minis.clear()
    _minis[chave] = img
    return img


def Botao(master, texto, tipo="suave", **kw):
    estilos = {
        "primario": dict(fg_color=ROXO, hover_color=ROXO_H, text_color="white"),
        "suave": dict(fg_color=ROXO_CLARO, hover_color=("#ddd6fe", "#3a2f68"), text_color=ROXO_TXT),
        "fantasma": dict(fg_color="transparent", hover_color=SUAVE, text_color=TEXTO, border_width=1, border_color=BORDA),
        "perigo": dict(fg_color=("#fff1f2", "#3a1420"), hover_color=("#ffe4e6", "#4c1a2a"), text_color=("#be123c", "#fda4af")),
        "vermelho": dict(fg_color=VERMELHO, hover_color="#be123c", text_color="white"),
        "ml": dict(fg_color=AMARELO_ML, hover_color="#facc15", text_color="#1e293b"),
    }
    opts = dict(height=34, corner_radius=10, font=F(13, "bold"))
    opts.update(estilos[tipo])
    opts.update(kw)
    return ctk.CTkButton(master, text=texto, **opts)


class Chip(ctk.CTkLabel):
    def __init__(self, master, texto, cor, **kw):
        super().__init__(master, text=f"  {texto}  ", fg_color=cor, text_color="white", corner_radius=9,
                         font=F(11, "bold"), height=22, **kw)

    def mudar(self, texto, cor):
        self.configure(text=f"  {texto}  ", fg_color=cor)


class Entrada(ctk.CTkEntry):
    """Campo com máscara: dinheiro (79,00), inteiro, decimal (1,5), digitos ou texto."""

    def __init__(self, master, tipo="texto", ao_mudar=None, **kw):
        kw.setdefault("height", 34)
        kw.setdefault("corner_radius", 9)
        kw.setdefault("border_color", BORDA)
        kw.setdefault("fg_color", PAINEL)
        kw.setdefault("font", F(13))
        if tipo == "dinheiro":
            kw.setdefault("placeholder_text", "0,00")
            kw.setdefault("justify", "right")
        elif tipo in ("inteiro", "decimal"):
            kw.setdefault("justify", "right")
        super().__init__(master, **kw)
        self.tipo = tipo
        self.ao_mudar = ao_mudar
        if tipo != "texto":
            self.bind("<KeyRelease>", self._mascara)
            self.bind("<FocusOut>", self._mascara, add="+")
        elif ao_mudar:
            self.bind("<KeyRelease>", lambda e: ao_mudar())

    def _mascara(self, e=None):
        if e is not None and getattr(e, "keysym", "") in ("Left", "Right", "Tab", "Shift_L", "Shift_R", "Home", "End",
                                                           "Control_L", "Control_R", "ISO_Left_Tab"):
            return
        t = self.get()
        if self.tipo == "dinheiro":
            dig = re.sub(r"\D", "", t).lstrip("0")
            novo = reais(int(dig) / 100) if dig else ""
        elif self.tipo == "inteiro":
            dig = re.sub(r"\D", "", t)
            novo = (dig.lstrip("0") or "0") if dig else ""
        elif self.tipo == "digitos":
            novo = re.sub(r"\D", "", t)
        else:  # decimal
            novo = re.sub(r"[^\d,]", "", t.replace(".", ","))
            if novo.count(",") > 1:
                i = novo.index(",")
                novo = novo[:i + 1] + novo[i + 1:].replace(",", "")
        if novo != t:
            self.delete(0, "end")
            if novo:
                self.insert(0, novo)
        if self.ao_mudar:
            self.ao_mudar()

    def valor(self):
        t = self.get().strip()
        if self.tipo == "texto":
            return t
        if not t:
            return None
        if self.tipo == "inteiro":
            return int(re.sub(r"\D", "", t) or 0)
        if self.tipo == "digitos":
            return t
        n = motor.numero(t)
        return round(n, 2 if self.tipo == "dinheiro" else 3)

    def definir(self, v):
        self.delete(0, "end")
        if v in (None, ""):
            return
        if self.tipo == "dinheiro":
            t = reais(motor.numero(v))
        elif self.tipo == "inteiro":
            t = str(int(motor.numero(v)))
        elif self.tipo == "decimal":
            t = decimal_txt(motor.numero(v))
        else:
            t = str(v)
        self.insert(0, t)


def resumo(f):
    vendas = [motor.numero(v.get("venda")) for v in f.ativas if motor.numero(v.get("venda")) > 0]
    estoque = sum(int(motor.numero(v.get("estoque"))) for v in f.ativas)
    if not vendas:
        preco = "sem preço"
    elif min(vendas) == max(vendas):
        preco = "R$ " + reais(vendas[0])
    else:
        preco = f"R$ {reais(min(vendas))} a {reais(max(vendas))}"
    return vendas, estoque, preco


def foto_capa(f):
    fotos = f.todas_fotos
    return fotos[0] if fotos else None


# ------------------------------------------------------------------ cartão da lista

def cortar(texto, n):
    texto = str(texto)
    return texto if len(texto) <= n else texto[:max(n - 1, 4)].rstrip() + "…"


def pode_subir(f):
    """Ficha nova pronta, ou cadastrada que foi editada depois (sobe de novo atualizando)."""
    return f.pronta or (f.status == "editada" and not f.erros)


class CartaoFicha(ctk.CTkFrame):
    def __init__(self, master, app, ficha, marcavel=True, letras=40):
        super().__init__(master, corner_radius=14, fg_color=CARD, border_width=1, border_color=BORDA)
        self.app, self.ficha = app, ficha
        self.ativo = False
        self.var = ctk.BooleanVar(value=False)
        self.grid_columnconfigure(2, weight=1)
        col = 0
        if marcavel:
            self.check = ctk.CTkCheckBox(self, text="", variable=self.var, width=22, checkbox_width=20, checkbox_height=20,
                                         corner_radius=6, fg_color=ROXO, hover_color=ROXO_H, border_color=BORDA,
                                         command=app.atualizar_botao)
            self.check.grid(row=0, column=0, rowspan=2, padx=(12, 2), pady=10)
            if not pode_subir(ficha):
                self.check.configure(state="disabled")
        else:
            self.check = None
        img = miniatura(foto_capa(ficha), 52)
        self.foto = ctk.CTkLabel(self, text="" if img else "sem\nfoto", image=img, width=52, height=52,
                                 fg_color=SUAVE if not img else "transparent", corner_radius=12, font=F(10), text_color=MUDO)
        self.foto.grid(row=0, column=1, rowspan=2, padx=8, pady=10)
        self.titulo = ctk.CTkLabel(self, text=cortar(ficha.nome, letras), anchor="w", font=F(14, "bold"), text_color=TEXTO)
        self.titulo.grid(row=0, column=2, sticky="ew", padx=(4, 6), pady=(11, 0))
        vendas, estoque, preco = resumo(ficha)
        n = len(ficha.variacoes)
        partes = [str(ficha.dados.get("codigo") or "").strip(), preco,
                  f"{n} {'variação' if n == 1 else 'variações'}" if n > 1 else "",
                  "sob encomenda" if motor.sim(ficha.dados.get("sob_encomenda")) else f"estoque {estoque}"]
        self.sub = ctk.CTkLabel(self, text=cortar("  ·  ".join(p for p in partes if p), int(letras * 1.25)), anchor="w", text_color=MUDO, font=F(12))
        self.sub.grid(row=1, column=2, sticky="ew", padx=(4, 6), pady=(0, 11))
        lado = ctk.CTkFrame(self, fg_color="transparent")
        lado.grid(row=0, column=3, rowspan=2, padx=(4, 12))
        texto, cor = STATUS.get(ficha.status, (ficha.status, "#64748b"))
        self.chip = Chip(lado, texto, cor)
        self.chip.pack(anchor="e")
        if ficha.status in ("feita", "editada") and ficha.resultado:
            ctk.CTkLabel(lado, text="no site", text_color=ROXO_TXT, font=F(11, "bold")).pack(anchor="e", pady=(2, 0))
        elif ficha.avisos and ficha.status == "pronta":
            ctk.CTkLabel(lado, text=f"⚠ {len(ficha.avisos)} aviso{'s' if len(ficha.avisos) > 1 else ''}", text_color=AMBAR,
                         font=F(11, "bold")).pack(anchor="e", pady=(2, 0))
        for w in (self, self.titulo, self.sub, self.foto, lado):
            w.bind("<Button-1>", lambda e=None: app.selecionar(self))
            w.bind("<Double-Button-1>", lambda e=None: app.editar(self.ficha))
            w.bind("<Enter>", lambda e=None: self._hover(True))
            w.bind("<Leave>", lambda e=None: self._hover(False))

    def _hover(self, dentro):
        if not self.ativo:
            self.configure(fg_color=CARD_H if dentro else CARD)

    def marcar(self, ativo):
        self.ativo = ativo
        self.configure(border_color=ROXO if ativo else BORDA, border_width=2 if ativo else 1,
                       fg_color=CARD_H if ativo else CARD)

    def mudar_status(self, status, mensagem=""):
        texto, cor = STATUS.get(status, (status, "#64748b"))
        self.chip.mudar(texto, cor)
        if mensagem:
            self.sub.configure(text=mensagem[:90])
        if status in ("feita", "ja") and self.check:
            self.var.set(False)
            self.check.configure(state="disabled")


# ------------------------------------------------------------------ app

TAMANHOS_TELA = {"Automático": "auto", "Compacto": "compacto", "Normal": "normal", "Grande": "grande"}


def escala_da_tela(escolha, sw, sh):
    """Tamanho das letras e botões: automático pelo monitor, ou o que a pessoa escolheu em Configurações."""
    fixo = {"compacto": 0.9, "normal": 1.0, "grande": 1.15}
    if escolha in fixo:
        return fixo[escolha]
    if sw <= 1440 or sh <= 820:      # notebook 1366
        return 0.92
    if sw >= 2500 and sh >= 1300:    # ultrawide / 2K
        return 1.12
    return 1.0


class App(ctk.CTk):
    def __init__(self):
        self.cfg = motor.ler_config()
        ctk.set_appearance_mode("dark" if self.cfg["robo"].get("tema") == "escuro" else "light")
        super().__init__()
        self.title("Robô de cadastro · Móveis Stylo")
        sw, sh = self.winfo_screenwidth(), self.winfo_screenheight()
        self.escala = escala_da_tela(self.cfg["robo"].get("tamanho_tela"), sw, sh)
        ctk.set_widget_scaling(self.escala)
        ctk.set_window_scaling(self.escala)
        if sw <= 1440 or sh <= 820:      # notebook: ocupa a tela toda
            larg, alt = sw - 20, sh - 80
        else:                            # monitor grande / ultrawide: janela confortável no meio
            larg, alt = min(int(1560 * self.escala), sw - 80), min(int(940 * self.escala), sh - 110)
        larg, alt = int(larg / self.escala), int(alt / self.escala)
        self.geometry(f"{larg}x{alt}+{max((sw - int(larg * self.escala)) // 2, 0)}+{max((sh - int(alt * self.escala)) // 2 - 20, 0)}")
        self.minsize(int(1000 / self.escala), int(620 / self.escala))
        if (sw <= 1440 or sh <= 820) and sys.platform.startswith("win"):
            self.after(60, lambda: self.state("zoomed"))
        self.configure(fg_color=FUNDO)
        if ICONE.exists() and sys.platform.startswith("win"):
            try:
                self.iconbitmap(str(ICONE), default=str(ICONE))
            except Exception:
                pass
            self.after(2500, self._conferir_atalho)

        self.fila = queue.Queue()      # robô → janela
        self.tarefas = queue.Queue()   # janela → robô (o navegador vive numa thread só)
        self.cartoes = []
        self.atual = None
        self.rodando = False
        self.ocupado_ml = False
        self.parar_flag = threading.Event()
        self.site = None
        self.ml = None
        self._sync_id = None
        self._sync_rodando = False
        self._sync_de_novo = False
        self._sync_quieto = False
        self.mapa = {}
        self.paginas = {}
        self.pagina = None
        self._refs = []

        self.grid_columnconfigure(1, weight=1)
        self.grid_rowconfigure(0, weight=1)
        self._lateral()
        self.area = ctk.CTkFrame(self, fg_color="transparent")
        self.area.grid(row=0, column=1, sticky="nsew")
        self.area.grid_columnconfigure(0, weight=1)
        self.area.grid_rowconfigure(0, weight=1)
        self._rodape()

        self.paginas["inicio"] = PaginaInicio(self.area, self)
        self.paginas["fichas"] = PaginaFichas(self.area, self)
        self.paginas["ml"] = PaginaML(self.area, self)
        self.paginas["feitas"] = PaginaFeitas(self.area, self)
        self.paginas["config"] = PaginaConfig(self.area, self)
        self.paginas["concorrentes"] = PaginaConcorrentes(self.area, self)
        self.paginas["materiais"] = PaginaMateriais(self.area, self)
        self.recarregar()
        if motor.ler_conta():
            self.bt_nuvem.configure(text="☁  FlowManeger conectado")
        else:
            self._mostrar_nuvem(None)
        self.ir("inicio")
        self._atalhos()
        self.after(120, self._ler_fila)
        self._clip = None
        self.after(1500, self._olhar_area_transf)
        self._reabrir = False
        self.after(4000, self._ao_abrir)
        threading.Thread(target=self._trabalhador, daemon=True).start()
        self.protocol("WM_DELETE_WINDOW", self.fechar)

    # ---------------------------------------------------------- montagem

    def _lateral(self):
        lat = ctk.CTkFrame(self, width=226, corner_radius=0, fg_color=LATERAL)
        lat.grid(row=0, column=0, rowspan=2, sticky="nsw")
        lat.grid_propagate(False)
        lat.grid_columnconfigure(0, weight=1)
        lat.grid_rowconfigure(8, weight=1)
        logo = ctk.CTkFrame(lat, fg_color="transparent")
        logo.grid(row=0, column=0, sticky="ew", padx=18, pady=(22, 26))
        ctk.CTkLabel(logo, text="MS", width=44, height=44, corner_radius=13, fg_color=ROXO, text_color="white",
                     font=F(17, "bold")).pack(side="left")
        txt = ctk.CTkFrame(logo, fg_color="transparent")
        txt.pack(side="left", padx=10)
        ctk.CTkLabel(txt, text="Móveis Stylo", text_color="white", font=F(15, "bold"), anchor="w").pack(anchor="w")
        ctk.CTkLabel(txt, text="Robô de cadastro", text_color="#a79fc4", font=F(12), anchor="w").pack(anchor="w")
        self.nav = {}
        menu = ctk.CTkFrame(lat, fg_color="transparent")
        menu.grid(row=1, column=0, sticky="new")
        menu.grid_columnconfigure(0, weight=1)
        grupos = [("PRODUTOS", [("inicio", "⌂", "Início"), ("fichas", "☰", "Fichas"), ("feitas", "✓", "Cadastradas")]),
                  ("MERCADO LIVRE", [("ml", "⌕", "Buscar no ML"), ("concorrentes", "◎", "Concorrentes")]),
                  ("AJUSTES", [("materiais", "▦", "Materiais e custos"), ("cores", "🎨", "Minhas cores"), ("config", "⚙", "Configurações")])]
        r = 0
        for titulo, itens in grupos:
            ctk.CTkLabel(menu, text=titulo, text_color="#7d7499", font=F(10, "bold"), anchor="w").grid(
                row=r, column=0, sticky="w", padx=24, pady=(10 if r else 0, 2))
            r += 1
            for chave, icone, texto in itens:
                b = ctk.CTkButton(menu, text=f"{icone}   {texto}", anchor="w", height=38, corner_radius=11, fg_color="transparent",
                                  hover_color="#2d1b52", text_color="#d6d0ea", font=F(14, "bold"),
                                  command=(lambda: EscolherCores(self)) if chave == "cores" else (lambda c=chave: self.ir(c)))
                b.grid(row=r, column=0, sticky="ew", padx=12, pady=2)
                if chave != "cores":
                    self.nav[chave] = b
                r += 1
        Botao(lat, "Copiar questionário p/ IA", "fantasma", text_color="#d6d0ea", border_color="#3b2a63",
              hover_color="#2d1b52", command=self.questionario).grid(row=9, column=0, sticky="ew", padx=14, pady=(0, 8))
        self.tema = ctk.CTkSwitch(lat, text="Modo escuro", text_color="#d6d0ea", progress_color=ROXO, font=F(13),
                                  command=self.trocar_tema)
        self.tema.grid(row=10, column=0, sticky="w", padx=18, pady=(4, 10))
        if ctk.get_appearance_mode() == "Dark":
            self.tema.select()
        self.chip_nav = Chip(lat, "Navegador fechado", "#3b2a63")
        self.chip_nav.grid(row=11, column=0, sticky="w", padx=16, pady=(0, 6))
        self.bt_nuvem = ctk.CTkButton(lat, text="☁  Fichas só neste PC", anchor="w", height=26, corner_radius=8, fg_color="#2a1d47",
                                      hover_color="#3b2a63", text_color="#d6d0ea", font=F(11, "bold"), command=self.clique_nuvem)
        self.bt_nuvem.grid(row=12, column=0, sticky="ew", padx=14, pady=(0, 6))
        self.lbl_site = ctk.CTkLabel(lat, text="", text_color="#8b84a8", font=F(11), anchor="w", justify="left", wraplength=190)
        self.lbl_site.grid(row=13, column=0, sticky="ew", padx=18, pady=(0, 18))

    def _rodape(self):
        rod = ctk.CTkFrame(self, corner_radius=0, fg_color=PAINEL, border_width=0, height=46)
        rod.grid(row=1, column=1, sticky="ew")
        rod.grid_columnconfigure(1, weight=1)
        self.lbl_status = ctk.CTkLabel(rod, text="Pronto.", anchor="w", font=F(13), text_color=TEXTO)
        self.lbl_status.grid(row=0, column=0, sticky="w", padx=(20, 12), pady=10)
        self.barra = ctk.CTkProgressBar(rod, progress_color=ROXO, height=6, fg_color=SUAVE)
        self.barra.grid(row=0, column=1, sticky="ew", padx=8)
        self.barra.set(0)
        Botao(rod, "Log", "fantasma", width=60, height=30, font=F(12), command=self.mostrar_log).grid(row=0, column=2, padx=4)
        Botao(rod, "Relatório", "fantasma", width=90, height=30, font=F(12), command=self.abrir_relatorio).grid(row=0, column=3, padx=(4, 16))
        self.log_txt = []

    def ir(self, chave):
        for c, b in self.nav.items():
            b.configure(fg_color=ROXO if c == chave else "transparent", text_color="white" if c == chave else "#d6d0ea")
        if self.pagina:
            self.pagina.grid_remove()
        self.pagina = self.paginas[chave]
        self.pagina.grid(row=0, column=0, sticky="nsew")
        if hasattr(self.pagina, "ao_mostrar"):
            self.pagina.ao_mostrar()

    def _atalhos(self):
        def no_editor():
            ed = self.paginas.get("editor")
            return ed if ed is not None and self.pagina is ed else None

        def tecla(seq, acao):
            self.bind_all(seq, lambda e: (acao(), "break")[1])
        tecla("<Control-n>", lambda: self.rodando or self.editar(None))
        tecla("<Control-l>", lambda: ImportarLinks(self))
        tecla("<F5>", lambda: no_editor() or (self.recarregar(), self.toast("Lista atualizada", ROXO)))

        def buscar():
            if no_editor():
                return
            if self.pagina is self.paginas["ml"]:
                self.paginas["ml"].termo.focus_set()
            else:
                self.ir("fichas")
                self.paginas["fichas"].busca.focus_set()
        tecla("<Control-f>", buscar)
        tecla("<Control-s>", lambda: no_editor() and no_editor().salvar())

        def esc():
            ed = no_editor()
            if ed and not any(isinstance(w, ctk.CTkToplevel) and w.winfo_viewable() for w in self.winfo_children()):
                ed.cancelar()
        self.bind_all("<Escape>", lambda e: esc())

    def trocar_tema(self):
        escuro = bool(self.tema.get())
        ctk.set_appearance_mode("dark" if escuro else "light")
        self.cfg["robo"]["tema"] = "escuro" if escuro else "claro"
        motor.salvar_config(self.cfg)

    def toast(self, texto, cor=VERDE, ms=3200):
        try:
            self._toast.destroy()
        except Exception:
            pass
        self._toast = t = ctk.CTkLabel(self, text=f"   {texto}   ", fg_color=cor, text_color="white", corner_radius=12, font=F(13, "bold"), height=40)
        t.place(relx=1.0, rely=1.0, x=-24, y=-64, anchor="se")
        t.lift()
        self.after(ms, lambda: t.winfo_exists() and t.destroy())

    def status(self, texto):
        self.lbl_status.configure(text=texto)

    def escrever(self, texto):
        self.log_txt.append(texto)
        self.log_txt = self.log_txt[-400:]

    def mostrar_log(self):
        j = ctk.CTkToplevel(self)
        j.title("Log do robô")
        j.geometry(f"760x420+{self.winfo_rootx() + 200}+{self.winfo_rooty() + 120}")
        j.configure(fg_color=FUNDO)
        caixa = ctk.CTkTextbox(j, font=ctk.CTkFont(family="Consolas", size=12), fg_color=PAINEL)
        caixa.pack(fill="both", expand=True, padx=14, pady=14)
        caixa.insert("1.0", "\n".join(self.log_txt) or "Nada ainda.")
        caixa.see("end")
        caixa.configure(state="disabled")
        j.after(200, j.lift)

    # ---------------------------------------------------------- fichas

    def pasta(self):
        p = Path(self.cfg["robo"]["pasta_produtos"])
        p.mkdir(parents=True, exist_ok=True)
        return p

    def recarregar(self, selecionar=None):
        if self.rodando:
            return
        self.lbl_site.configure(text=f"{self.cfg['robo']['site']}\n{self.pasta()}")
        fichas = motor.listar_fichas(self.pasta(), incluir_feitas=True)
        self.paginas["fichas"].carregar(fichas, selecionar)
        n = sum(1 for f in fichas if f.status not in ("feita", "ja"))
        self.nav["fichas"].configure(text="☰   Fichas" + (f"   ·   {n}" if n else ""))
        if self.pagina is self.paginas["feitas"]:
            self.paginas["feitas"].carregar()
        if self.pagina is self.paginas.get("inicio"):
            self.paginas["inicio"].desenhar()
        if self._sync_quieto:
            self._sync_quieto = False
        else:
            self.agendar_sync(2500)

    # ---------------------------------------------------------- fichas no banco do FlowManeger

    def nuvem(self):
        r = self.cfg["robo"]
        ed = self.paginas.get("editor")
        abertas = lambda: {str(ed.ficha.pasta)} if ed is not None and ed.winfo_exists() and getattr(ed, "ficha", None) else set()
        return motor.Nuvem(r["site"], self.pasta(), avisar=lambda t: self.fila.put(("nuvem_aviso", None, t)), ocupadas=abertas)

    def agendar_sync(self, ms=2500):
        if self._sync_id:
            try:
                self.after_cancel(self._sync_id)
            except Exception:
                pass
        self._sync_id = self.after(ms, self.sincronizar)

    def sincronizar(self, avisar=False):
        self._sync_id = None
        if not motor.ler_conta():
            self._mostrar_nuvem(None)
            if avisar:
                self.conectar_nuvem()
            return
        if self._sync_rodando:
            self._sync_de_novo = True
            return
        if self.rodando:          # o cadastro mexe nas pastas: sincroniza quando acabar
            self.agendar_sync(15000)
            return
        nv = self.nuvem()
        self._sync_rodando = True
        self._mostrar_nuvem("rodando")

        def rodar():
            try:
                res = nv.sincronizar()
                self.fila.put(("nuvem_ok", None, (res, avisar)))
            except Exception as e:
                self.fila.put(("nuvem_erro", None, (str(e), avisar, nv.conectada)))
        threading.Thread(target=rodar, daemon=True).start()

    def _mostrar_nuvem(self, estado, texto=None):
        if estado is None:
            self.bt_nuvem.configure(text="☁  Fichas só neste PC · conectar", fg_color="#2a1d47", text_color="#d6d0ea")
        elif estado == "rodando":
            self.bt_nuvem.configure(text="☁  Sincronizando…", fg_color="#2a1d47", text_color="#d6d0ea")
        elif estado == "ok":
            self.bt_nuvem.configure(text=f"☁  Salvas no site · {datetime.now():%H:%M}", fg_color="#1f3b2c", text_color="#bff0cf")
        else:
            self.bt_nuvem.configure(text="☁  " + (texto or "Não sincronizou"), fg_color="#4a3413", text_color="#ffd99a")
        pc = self.paginas.get("config")
        if pc is not None:
            pc.mostrar_nuvem()

    def clique_nuvem(self):
        if motor.ler_conta():
            self.sincronizar(avisar=True)
        else:
            self.conectar_nuvem()

    def conectar_nuvem(self):
        if self.rodando or self.ocupado_ml:
            messagebox.showinfo("Robô", "Espere o robô terminar o que está fazendo.")
            return
        if not messagebox.askyesno("Salvar as fichas no FlowManeger",
                                   "O robô vai abrir o navegador dele no site para você entrar na conta (só na primeira vez).\n\n"
                                   "Depois disso, todas as fichas e fotos ficam guardadas no banco do FlowManeger. Este PC "
                                   "continua com uma cópia para trabalhar, e em outro PC o robô traz as fichas sozinho.\n\nConectar agora?"):
            return
        self.status("Conectando ao FlowManeger… se aparecer o login no navegador, entre na sua conta.")
        self._mostrar_nuvem("rodando")
        self.tarefas.put(("conectar_nuvem", None))

    def _conectar_nuvem(self):
        try:
            site = self._garantir_site()
            if not site.visivel:
                site.fechar()
                site.visivel = True
            chave, usuario = site.pegar_chave(nome_pc=os.environ.get("COMPUTERNAME") or "")
            motor.salvar_conta(self.cfg["robo"]["site"], chave, usuario)
            self.fila.put(("nuvem_conectada", None, usuario))
        except Exception as e:
            self.fila.put(("nuvem_erro", None, (str(e), True, False)))

    def desconectar_nuvem(self):
        if messagebox.askyesno("Desconectar", "Parar de salvar as fichas no FlowManeger neste PC?\n\n"
                               "Nada é apagado: as fichas continuam no site e neste PC."):
            motor.desconectar_conta()
            self._mostrar_nuvem(None)

    def selecionar(self, cartao):
        self.paginas["fichas"].selecionar(cartao)

    def atualizar_botao(self):
        self.paginas["fichas"].atualizar_botao()

    def _oferecer_atualizar(self, fichas):
        nomes = "\n".join("• " + f.nome for f in fichas[:8]) + ("\n…" if len(fichas) > 8 else "")
        if messagebox.askyesno("Já estavam no site", f"Estas fichas já estavam cadastradas no site:\n\n{nomes}\n\n"
                               "Quer atualizar os produtos do site com o que está nelas agora? "
                               "(preços, textos, medidas, prazo e fotos; o estoque não muda)"):
            self.cadastrar(fichas, atualizar=True)

    def editar(self, ficha=None):
        if self.rodando:
            messagebox.showinfo("Robô", "Espere o cadastro terminar para editar.")
            return
        if ficha is not None and ficha.status == "feita":
            r = messagebox.askyesnocancel(
                "Ficha já cadastrada",
                "Esta ficha já foi cadastrada no site.\n\n"
                "Sim = editar esta ficha e depois atualizar o produto no site\n"
                "Não = criar uma cópia para um produto novo\n"
                "Cancelar = voltar")
            if r is None:
                return
            if r is False:
                destino = motor.duplicar_ficha(ficha, self.pasta())
                ficha = motor.ler_ficha(destino)
        if "editor" in self.paginas:
            self.paginas["editor"].destroy()
        ed = Editor(self.area, self, ficha)
        self.paginas["editor"] = ed
        if self.pagina:
            self.pagina.grid_remove()
        self.pagina = ed
        ed.grid(row=0, column=0, sticky="nsew")
        for b in self.nav.values():
            b.configure(fg_color="transparent", text_color="#d6d0ea")

    def fechar_editor(self, pasta=None):
        ed = self.paginas.pop("editor", None)
        self.ir("feitas" if pasta is not None and Path(pasta).parent.name.lower() == "feitos" else "fichas")
        if ed:
            ed.destroy()
        self.recarregar(selecionar=pasta)

    def duplicar(self, ficha):
        destino = motor.duplicar_ficha(ficha, self.pasta())
        self.recarregar(selecionar=destino)
        self.ir("fichas")
        self.toast("Cópia criada")

    def completar_fichas(self, fichas):
        fichas = [f for f in fichas if f.status != "feita" and f.dados]
        if not fichas or self.rodando:
            return
        if not messagebox.askyesno("Completar fichas", f"Completar {len(fichas)} ficha(s)?\n\nO robô preenche só o que está vazio "
                                   "(código, categoria, ficha técnica, caixa, preço, prazo, título e descrição) e resolve os avisos que dá. "
                                   "Nada que você já preencheu muda. No fim ele pergunta o custo e o peso que só você sabe."):
            return
        self.resolver_avisos(fichas, completar=True)

    def resolver_avisos(self, fichas, chaves=None, completar=False):
        """Resolve os avisos das fichas (salva cada uma) e no fim pergunta o custo e o peso que faltam."""
        fichas = [f for f in fichas if f.status not in ("feita", "ja") and f.dados]
        if not fichas or self.rodando:
            return
        cfg = dict(self.cfg["robo"])
        n, faltam, linhas = 0, [], []
        for f in fichas:
            d = json.loads(json.dumps(f.dados))
            existentes = recursos.codigos_das_fichas(self.pasta(), exceto=f.pasta)
            feito = recursos.completar(d, cfg, existentes=existentes) if completar else []
            feito += recursos.resolver(d, chaves, cfg, existentes, pasta=f.pasta)
            if feito:
                motor.salvar_ficha(f, d)
                n += 1
                linhas.append(f"{f.nome}: " + "; ".join(feito))
            for idx, rot, fc, fp in recursos.faltam_numeros(d):
                fc = fc and (chaves is None or "custo" in chaves)
                fp = fp and (chaves is None or "peso" in chaves)
                if fc or fp:
                    faltam.append((f.pasta, f.nome, idx, rot, fc, fp))
        self.recarregar(selecionar=fichas[0].pasta if len(fichas) == 1 else None)
        if linhas:
            self.status(" | ".join(linhas)[:400])
        if chaves is None or "fotos" in chaves:
            sem_foto = [f.pasta for f in motor.listar_fichas(self.pasta()) if any(a.startswith("Sem fotos") for a in f.avisos)
                        and isinstance(f.dados.get("mercado_livre"), dict) and f.dados["mercado_livre"].get("link_referencia")
                        and any(Path(f.pasta) == Path(x.pasta) for x in fichas)]
            if sem_foto:
                self.baixar_fotos_ml(sem_foto)
        if faltam:
            Faltando(self, faltam, n)
        else:
            self.toast(f"✦ {n} ficha(s) arrumada(s)" if n else "Nada que o robô consiga resolver sozinho", VERDE if n else AMBAR)

    def baixar_fotos_ml(self, pastas):
        pastas = [Path(p) for p in pastas if p]
        if not pastas:
            return
        if self.rodando or self.ocupado_ml:
            self.toast("Espere o robô terminar o que está fazendo", AMBAR)
            return
        self.ocupado_ml = True
        self.parar_flag.clear()
        self.status(f"Baixando as fotos do anúncio de referência ({len(pastas)} ficha(s))…")
        self.tarefas.put(("ml_fotos", pastas))

    # ---------------------------------------------------------- atualização e cópia das fichas
    def _sync_periodico(self):
        if motor.ler_conta():
            self.sincronizar()
        self.after(180000, self._sync_periodico)

    def _ao_abrir(self):
        r = self.cfg["robo"]
        self._sync_periodico()
        if motor.sim(r.get("copia_diaria", "sim")):
            def copiar():
                try:
                    arq = motor.copia_das_fichas(self.pasta(), r.get("pasta_copias"), r.get("copias_guardar") or 14)
                    if arq:
                        self.fila.put(("copia_ok", None, str(arq)))
                except Exception as e:
                    self.fila.put(("copia_erro", None, str(e)))
            threading.Thread(target=copiar, daemon=True).start()
        if motor.sim(r.get("atualizar_sozinho", "sim")):
            self.procurar_atualizacao(silencioso=True)

    def procurar_atualizacao(self, silencioso=False):
        cfg = dict(self.cfg["robo"])
        if not silencioso:
            self.status("Procurando versão nova do robô…")

        def rodar():
            try:
                nova = recursos.checar_atualizacao(cfg)
                self.fila.put(("atualizacao", None, (nova, silencioso)))
            except Exception as e:
                self.fila.put(("atualizacao_erro", None, (str(e), silencioso)))
        threading.Thread(target=rodar, daemon=True).start()

    def _aviso_versao(self, nova):
        try:
            self._banner_v.destroy()
        except Exception:
            pass
        b = self._banner_v = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=14, border_width=2, border_color=ROXO)
        ctk.CTkLabel(b, text="⟳", width=36, height=36, corner_radius=10, fg_color=ROXO, text_color="white",
                     font=F(16, "bold")).pack(side="left", padx=(10, 8), pady=10)
        ctk.CTkLabel(b, text=f"Versão {nova.get('versao')} do robô disponível", font=F(13, "bold"), text_color=TEXTO).pack(side="left", padx=(0, 12))
        Botao(b, "Ver e atualizar", "primario", width=130, height=32,
              command=lambda: (b.destroy(), Atualizar(self, nova))).pack(side="left", padx=4)
        Botao(b, "✕", "fantasma", width=32, height=32, command=b.destroy).pack(side="left", padx=(2, 10))
        b.place(relx=1.0, rely=1.0, x=-24, y=-118, anchor="se")
        b.lift()

    def instalar_versao(self, nova, janela=None):
        if self.rodando or self.ocupado_ml:
            self.toast("Espere o robô terminar o que está fazendo", AMBAR)
            return
        cfg = dict(self.cfg["robo"])

        def avisar(t):
            self.fila.put(("atualizando", None, t))

        def rodar():
            try:
                pip = recursos.instalar_atualizacao(cfg, nova, avisar)
                if pip:
                    avisar("Instalando o que a versão nova precisa…")
                    subprocess.run([sys.executable.replace("pythonw", "python"), "-m", "pip", "install", "-q", "-r",
                                    str(motor.BASE / "requirements.txt")], cwd=str(motor.BASE),
                                   creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0))
                self.fila.put(("atualizado", None, nova))
            except Exception as e:
                with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                    fl.write(traceback.format_exc() + "\n")
                self.fila.put(("atualizacao_erro", None, (str(e), False)))
        threading.Thread(target=rodar, daemon=True).start()

    def reabrir(self):
        self._reabrir = True
        self.fechar()

    def _olhar_area_transf(self):
        """Avisa quando um link do Mercado Livre é copiado (Ctrl+C) em qualquer lugar."""
        try:
            t = self.clipboard_get()
        except Exception:
            t = ""
        if t != self._clip:
            self._clip = t
            links = recursos.links_ml(t[:20000]) if t else []
            if links and not self.rodando and self.pagina is not self.paginas.get("editor"):
                self._aviso_link(links)
        self.after(1500, self._olhar_area_transf)

    def _aviso_link(self, links):
        try:
            self._banner.destroy()
        except Exception:
            pass
        b = self._banner = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=14, border_width=2, border_color=AMARELO_ML)
        ctk.CTkLabel(b, text="ML", width=36, height=36, corner_radius=10, fg_color=AMARELO_ML, text_color="#1e293b",
                     font=F(12, "bold")).pack(side="left", padx=(10, 8), pady=10)
        txt = "Link do Mercado Livre copiado" if len(links) == 1 else f"{len(links)} links do Mercado Livre copiados"
        ctk.CTkLabel(b, text=txt, font=F(13, "bold"), text_color=TEXTO).pack(side="left", padx=(0, 12))
        Botao(b, "Importar" if len(links) == 1 else "Importar todos", "primario", width=110, height=32,
              command=lambda: (b.destroy(), self.importar_links(links))).pack(side="left", padx=4)
        Botao(b, "✕", "fantasma", width=32, height=32, command=b.destroy).pack(side="left", padx=(2, 10))
        b.place(relx=1.0, rely=1.0, x=-24, y=-118, anchor="se")
        b.lift()
        self.after(20000, lambda: b.winfo_exists() and b.destroy())

    def importar_links(self, links):
        if not links:
            return
        if self.rodando or self.ocupado_ml:
            self.toast("Espere o robô terminar o que está fazendo", AMBAR)
            return
        self.ocupado_ml = True
        self.parar_flag.clear()
        self.status(f"Importando {len(links)} anúncio(s) do Mercado Livre…")
        self.paginas["ml"].aviso(f"Importando {len(links)} anúncio(s)…", carregando=True)
        self.tarefas.put(("ml_importar_lote", links))

    def excluir(self, ficha):
        if self.rodando:
            return
        if not messagebox.askyesno("Excluir ficha", f"Excluir a ficha “{ficha.nome}”?\n\nA pasta vai para produtos\\_lixeira (dá para recuperar)."):
            return
        motor.excluir_ficha(ficha, self.pasta())
        self.recarregar()
        self.toast("Ficha movida para a lixeira", VERMELHO)

    # ---------------------------------------------------------- robô (thread)

    def cadastrar(self, fichas, atualizar=False):
        if not fichas or self.rodando or self.ocupado_ml:
            return
        self.rodando = True
        self.modo_atualizar = atualizar
        self.parar_flag.clear()
        self.barra.set(0)
        self.total = len(fichas)
        self.feitas_n = 0
        self.escrever(f"— {'Atualizando' if atualizar else 'Cadastrando'} {len(fichas)} ficha(s) em {self.cfg['robo']['site']}")
        if atualizar:
            self.status(f"Atualizando {len(fichas)} ficha(s) no site…")
        self.paginas["fichas"].rodando(True)
        self.tarefas.put(("cadastrar", (fichas, atualizar)))

    def atualizar_no_site(self, fichas):
        """Manda de novo fichas já cadastradas: o site atualiza os produtos (o estoque não muda)."""
        fichas = [f for f in fichas if f.dados and not f.erros]
        if not fichas or self.rodando or self.ocupado_ml:
            if self.rodando or self.ocupado_ml:
                messagebox.showinfo("Robô", "Espere o robô terminar o que está fazendo.")
            return
        nomes = "\n".join("• " + f.nome for f in fichas[:8]) + ("\n…" if len(fichas) > 8 else "")
        if not messagebox.askyesno("Atualizar no site", f"Atualizar no site com o que está na ficha?\n\n{nomes}\n\n"
                                   "Mudam preços, textos, medidas, prazo e as fotos. O estoque do site não muda. "
                                   "Variação nova na ficha entra como produto novo no mesmo grupo."):
            return
        self.cadastrar(fichas, atualizar=True)

    def _garantir_site(self):
        if self.site is None:
            r = self.cfg["robo"]
            self.site = motor.Site(r["site"], visivel=motor.sim(r["navegador_visivel"]),
                                   avisar=lambda t: self.fila.put(("site", None, t)), parar=self.parar_flag.is_set)
        self.site.parar = self.parar_flag.is_set
        return self.site

    def _garantir_ml(self):
        site = self._garantir_site()
        if not site.visivel:
            # O Mercado Livre às vezes pede verificação: o navegador precisa aparecer
            site.fechar()
            site.visivel = True
        if self.ml is None:
            self.ml = motor.MercadoLivre(site, avisar=lambda t: self.fila.put(("ml_aviso", None, t)), parar=self.parar_flag.is_set)
        return self.ml

    def _trabalhador(self):
        """Thread do robô: o Playwright só funciona na thread que abriu o navegador."""
        while True:
            tipo, dado = self.tarefas.get()
            try:
                if tipo == "cadastrar":
                    self._trabalho(*dado)
                elif tipo == "ml_buscar":
                    self._ml_buscar(*dado)
                elif tipo == "ml_mais":
                    self._ml_buscar(*dado, mais=True)
                elif tipo == "ml_precos":
                    self._ml_precos(*dado)
                elif tipo == "concorrentes":
                    self._conferir_concorrentes(dado)
                elif tipo == "ml_importar":
                    self._ml_importar(dado)
                elif tipo == "ml_importar_lote":
                    self._ml_importar_lote(dado)
                elif tipo == "ml_fotos":
                    self._ml_fotos(dado)
                elif tipo == "conectar_nuvem":
                    self._conectar_nuvem()
                elif tipo == "fechar_site":
                    self._fechar_site()
                    self.fila.put(("site", None, "fechado"))
                elif tipo == "sair":
                    self._fechar_site()
                    self.fila.put(("saiu", None, None))
                    return
            except Exception:
                with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                    fl.write(traceback.format_exc() + "\n")

    def _fechar_site(self):
        if self.site:
            self.site.fechar()
        self.site = None
        self.ml = None

    def _trabalho(self, fichas, atualizar=False):
        avisar = lambda ev, f, t: self.fila.put((ev, f, t))
        try:
            site = self._garantir_site()
            feitas, falhas = motor.rodar(fichas, self.cfg, avisar, self.parar_flag.is_set, site=site, atualizar=atualizar)
            self.fila.put(("acabou", None, (feitas, falhas)))
        except motor.Parar:
            self.fila.put(("parado", None, None))
        except Exception as e:
            self.fila.put(("erro", None, str(e).splitlines()[0] if str(e) else repr(e)))
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")
            self._fechar_site()

    def _ml_buscar(self, termo, busca_id, pagina=1, paginas=2, mais=False):
        """Busca {paginas} páginas seguidas (cerca de 50 anúncios cada) e baixa as miniaturas."""
        try:
            ml = self._garantir_ml()
            if motor.eh_link_ml(termo):
                self.fila.put(("ml_status", None, "Lendo o anúncio…"))
                self._ml_importar(termo)
                return
            for n in range(pagina, pagina + paginas):
                itens = ml.buscar(termo, n)
                acabou = len(itens) < 30
                self.fila.put(("ml_resultados", None, (busca_id, itens, mais or n > pagina, n, acabou)))
                for it in itens:
                    if not self.tarefas.empty():
                        return
                    if it.get("imagem"):
                        corpo = ml.baixar(it["imagem"], timeout=15000)
                        if corpo:
                            self.fila.put(("ml_thumb", None, (busca_id, it["link"], corpo)))
                if acabou or not self.tarefas.empty():
                    break
        except motor.Parar:
            self.fila.put(("ml_erro", None, "Parado."))
        except Exception as e:
            self.fila.put(("ml_erro", None, str(e).splitlines()[0] if str(e) else repr(e)))
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")
            if "closed" in str(e).lower() or "Target" in str(e):
                self._fechar_site()

    def _ml_precos(self, termo, cid):
        """Preços do Mercado Livre para o editor (2 páginas da busca)."""
        try:
            ml = self._garantir_ml()
            itens = []
            for n in (1, 2):
                lote = ml.buscar(termo, n)
                itens += lote
                if len(lote) < 30:
                    break
            self.fila.put(("ml_precos", None, (cid, termo, itens)))
        except Exception as e:
            self.fila.put(("ml_precos_erro", None, (cid, str(e).splitlines()[0] if str(e) else repr(e))))
            if "closed" in str(e).lower() or "Target" in str(e):
                self._fechar_site()

    def _conferir_concorrentes(self, links):
        mudaram = 0
        try:
            ml = self._garantir_ml()
            for i, link in enumerate(links, 1):
                if self.parar_flag.is_set():
                    break
                self.fila.put(("ml_status_conc", None, f"Conferindo {i} de {len(links)}…"))
                try:
                    titulo, preco = ml.preco_atual(link)
                except Exception as e:
                    if "closed" in str(e).lower() or "Target" in str(e):
                        raise
                    titulo, preco = None, None
                anterior, novo = recursos.anotar_preco(link, preco, titulo)
                if anterior is not None and novo is not None and abs(anterior - novo) >= 0.01:
                    mudaram += 1
                self.fila.put(("conc_item", None, (link, anterior, novo)))
        except Exception as e:
            self.fila.put(("ml_erro", None, str(e).splitlines()[0] if str(e) else repr(e)))
            self._fechar_site()
        self.fila.put(("conc_fim", None, mudaram))

    def _completar_importada(self, pasta):
        if not motor.sim(self.cfg["robo"].get("completar_ao_importar", "sim")):
            return
        try:
            f = motor.ler_ficha(pasta)
            if f.dados:
                d = json.loads(json.dumps(f.dados))
                # o preço do anúncio é do concorrente: não sugere venda nem preço "de" em cima dele
                if recursos.completar(d, dict(self.cfg["robo"]), campos=("codigos", "categoria", "tecnica", "caixa"),
                                      existentes=recursos.codigos_das_fichas(self.pasta(), exceto=pasta)):
                    motor.salvar_ficha(f, d)
        except Exception:
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")

    def _ml_importar_lote(self, links):
        pastas, falhas = [], []
        try:
            ml = self._garantir_ml()
            for i, link in enumerate(links, 1):
                if self.parar_flag.is_set():
                    break
                self.fila.put(("ml_status", None, f"Importando {i} de {len(links)}: lendo o anúncio…"))
                try:
                    info = ml.extrair(link)
                    pasta = motor.ficha_do_ml(info, self.pasta(), baixar=ml.baixar)
                    self._completar_importada(pasta)
                    pastas.append(pasta)
                except motor.Parar:
                    break
                except Exception as e:
                    if "closed" in str(e).lower() or "Target" in str(e):
                        raise
                    falhas.append((link, str(e).splitlines()[0] if str(e) else repr(e)))
                    with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                        fl.write(traceback.format_exc() + "\n")
        except Exception as e:
            falhas.append(("", str(e).splitlines()[0] if str(e) else repr(e)))
            self._fechar_site()
        self.fila.put(("ml_lote_fim", None, (pastas, falhas)))

    def _ml_fotos(self, pastas):
        """Baixa as fotos do anúncio de referência das fichas que estão sem foto."""
        feitas, falhas = [], []
        try:
            ml = None
            for i, pasta in enumerate(pastas, 1):
                if self.parar_flag.is_set():
                    break
                f = motor.ler_ficha(pasta)
                link = (f.dados.get("mercado_livre") or {}).get("link_referencia") if isinstance(f.dados.get("mercado_livre"), dict) else None
                if not link:
                    continue
                self.fila.put(("ml_status", None, f"Fotos {i} de {len(pastas)}: lendo o anúncio de {f.nome}…"))
                try:
                    ml = ml or self._garantir_ml()
                    info = None
                    origem = Path(pasta) / "origem-ml.json"
                    if origem.exists():
                        try:
                            info = json.loads(origem.read_text(encoding="utf-8"))
                        except Exception:
                            info = None
                    varias = len(f.variacoes) > 1
                    if not (info and info.get("imagens")) or (varias and not any(p.get("detalhes") for p in info.get("variacoes") or [])):
                        info = ml.extrair(link)
                    d = json.loads(json.dumps(f.dados))
                    vs = recursos._vs(d)
                    nomes = []
                    if varias and vs and not any(v.get("fotos") for v in vs):
                        # cada variação com as fotos da cor (ou do tamanho) dela no anúncio
                        por_opcao = motor.salvar_fotos_variacoes(info, pasta, ml.baixar)
                        for v in vs:
                            partes = [x.strip().lower() for x in str(v.get("valor") or "").split("·")]
                            for (tipo, opcao), lista in por_opcao.items():
                                if opcao.strip().lower() in partes:
                                    v["fotos"] = lista
                                    nomes += [n for n in lista if n not in nomes]
                                    break
                    if not nomes:
                        nomes = motor.salvar_fotos_ml(info, pasta, ml.baixar)
                        if vs and not any(v.get("fotos") for v in vs):
                            vs[0]["fotos"] = nomes
                    if not nomes:
                        falhas.append((f.nome, "o anúncio não tem fotos que dê para baixar"))
                        continue
                    motor.salvar_ficha(f, d)
                    feitas.append((Path(pasta), nomes))
                except motor.Parar:
                    break
                except Exception as e:
                    if "closed" in str(e).lower() or "Target" in str(e):
                        raise
                    falhas.append((f.nome, str(e).splitlines()[0] if str(e) else repr(e)))
                    with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                        fl.write(traceback.format_exc() + "\n")
        except Exception as e:
            falhas.append(("", str(e).splitlines()[0] if str(e) else repr(e)))
            self._fechar_site()
        self.fila.put(("ml_fotos_fim", None, (feitas, falhas)))

    def _ml_importar(self, link):
        try:
            ml = self._garantir_ml()
            self.fila.put(("ml_status", None, "Lendo o anúncio no Mercado Livre…"))
            info = ml.extrair(link)
            self.fila.put(("ml_status", None, f"Baixando {len(info.get('imagens') or [])} foto(s)…"))
            pasta = motor.ficha_do_ml(info, self.pasta(), baixar=ml.baixar)
            self._completar_importada(pasta)
            self.fila.put(("ml_importado", None, pasta))
        except motor.Parar:
            self.fila.put(("ml_erro", None, "Parado."))
        except Exception as e:
            self.fila.put(("ml_erro", None, str(e).splitlines()[0] if str(e) else repr(e)))
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")
            if "closed" in str(e).lower() or "Target" in str(e):
                self._fechar_site()

    def _ler_fila(self):
        try:
            while True:
                ev, f, t = self.fila.get_nowait()
                try:
                    self._evento(ev, f, t)
                except Exception:
                    with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                        fl.write(traceback.format_exc() + "\n")
        except queue.Empty:
            pass
        self.after(120, self._ler_fila)

    def _evento(self, ev, f, t):
        pf = self.paginas["fichas"]
        if ev == "nuvem_aviso":
            self.escrever(t)
            return
        if ev == "nuvem_conectada":
            self.toast(f"Conectado ao FlowManeger{(' como ' + t) if t else ''}. Salvando as fichas no site…", VERDE)
            self.status("Pronto.")
            self.sincronizar(avisar=True)
            return
        if ev in ("nuvem_ok", "nuvem_erro"):
            self._sync_rodando = False
            if ev == "nuvem_ok":
                res, avisar = t
                if res["erros"]:
                    self._mostrar_nuvem("erro", f"{len(res['erros'])} ficha(s) não sincronizaram")
                    for e in res["erros"]:
                        self.escrever("Site: " + e)
                    if avisar:
                        self.status("Não sincronizou: " + " | ".join(res["erros"])[:300])
                else:
                    self._mostrar_nuvem("ok")
                partes = []
                if res["subiram"]:
                    partes.append(f"{res['subiram']} salva(s) no site")
                if res["desceram"]:
                    partes.append(f"{res['desceram']} trazida(s) do site")
                if res["excluidas"]:
                    partes.append(f"{res['excluidas']} foram para a lixeira")
                if partes:
                    self.escrever("Fichas no FlowManeger: " + ", ".join(partes))
                if res["desceram"] or res["excluidas"]:
                    self._sync_quieto = True
                    self.recarregar()
                    self.toast("Fichas atualizadas pelo FlowManeger: " + ", ".join(partes), VERDE)
                elif avisar:
                    self.toast("Fichas salvas no FlowManeger" + (": " + ", ".join(partes) if partes else ", tudo em dia"), VERDE)
            else:
                msg, avisar, conectada = t
                if not conectada and not motor.ler_conta():
                    self._mostrar_nuvem(None)
                else:
                    self._mostrar_nuvem("erro", "Sem conexão com o site" if "conexão" in msg.lower() else "Não sincronizou")
                self.escrever("Site: " + msg)
                if avisar:
                    self.status(msg[:300])
                    messagebox.showwarning("FlowManeger", msg)
            if self._sync_de_novo:
                self._sync_de_novo = False
                self.agendar_sync(1000)
            return
        if ev == "ml_fotos_fim":
            self.ocupado_ml = False
            feitas, falhas = t
            n = sum(len(x[1]) for x in feitas)
            ed = self.paginas.get("editor")
            for pasta, nomes in feitas:
                if ed is not None and ed.winfo_exists() and ed.ficha and Path(ed.ficha.pasta) == pasta:
                    ed.fotos_baixadas(nomes)
            self.recarregar(selecionar=feitas[0][0] if len(feitas) == 1 and ed is None else None)
            self.status("Pronto.")
            if falhas:
                self.status("Fotos que não vieram: " + " | ".join(f"{a}: {b}" for a, b in falhas)[:300])
            self.toast(f"{n} foto(s) baixada(s) do anúncio" if n else "Não deu para baixar as fotos do anúncio", VERDE if n else AMBAR)
            return
        if ev == "copia_ok":
            self.escrever(f"Cópia das fichas de hoje: {t}")
            return
        if ev == "copia_erro":
            self.escrever(f"Não deu para fazer a cópia das fichas: {t}")
            return
        if ev == "atualizacao":
            nova, silencioso = t
            cfgp = self.paginas.get("config")
            if cfgp is not None:
                cfgp.mostrar_versao(nova)
            if nova:
                self._aviso_versao(nova)
            elif not silencioso:
                self.toast(f"O robô já está na versão mais nova ({recursos.versao_local().get('versao')})")
                self.status("Pronto.")
            return
        if ev == "atualizacao_erro":
            msg, silencioso = t
            self.escrever(f"Atualização: {msg}")
            if not silencioso:
                self.toast("Não deu para atualizar agora. Confira a internet e tente de novo.", VERMELHO)
                self.status(f"Atualização: {msg}"[:300])
            return
        if ev == "atualizando":
            self.status(t)
            return
        if ev == "atualizado":
            self.status(f"Robô atualizado para a versão {t.get('versao')}.")
            if messagebox.askyesno("Robô atualizado", f"Pronto! O robô está na versão {t.get('versao')}.\n\n"
                                   "Reabrir agora para usar a versão nova?\n(A anterior ficou guardada em _versao-anterior.)"):
                self.reabrir()
            return
        if ev == "site":
            if t == "login":
                self.chip_nav.mudar("Entre na conta", AMBAR)
                self.status("Entre na conta da Móveis Stylo na janela do navegador. O robô continua sozinho depois.")
                self.escrever("Esperando o login no navegador…")
            elif t == "conectado":
                self.chip_nav.mudar("Conectado ao site", VERDE)
            elif t == "fechado":
                self.chip_nav.mudar("Navegador fechado", "#3b2a63")
            else:
                self.status(self.lbl_status.cget("text").split("  ·  ")[0] + f"  ·  {t}")
        elif ev == "inicio":
            c = self.mapa.get(id(f))
            if c:
                c.mudar_status("rodando")
                pf.selecionar(c)
            self.status(t)
            self.escrever(t)
        elif ev == "fim":
            self.feitas_n += 1
            self.barra.set(self.feitas_n / max(self.total, 1))
            c = self.mapa.get(id(f))
            if c:
                c.mudar_status(f.status, f.mensagem)
                if pf.atual is c:
                    pf.mostrar(f)
            icone = {"feita": "✓", "ja": "•", "falhou": "✕"}.get(f.status, "•")
            self.escrever(f"{icone} {f.nome}: {t}")
        elif ev == "saiu":
            self._sair()
        elif ev in ("acabou", "parado", "erro"):
            self.rodando = False
            self.trazer_para_frente()
            atualizou = getattr(self, "modo_atualizar", False)
            ja_fichas = []
            if ev == "acabou":
                feitas, falhas = t
                ja_fichas = [c.ficha for c in self.mapa.values() if c.ficha.status == "ja"] if not atualizou else []
                ja = len(ja_fichas)
                msg = f"Pronto! {feitas} ficha(s) {'atualizada(s) no site' if atualizou else 'cadastrada(s)'}" + \
                      (f", {ja} já existia(m) no site" if ja else "") + \
                      (f", {falhas} com falha (veja o motivo em cada uma)" if falhas else "") + "."
                self.barra.set(1)
                self.toast(msg if len(msg) < 70 else ("Atualização terminada" if atualizou else "Cadastro terminado"),
                           VERDE if not falhas else AMBAR)
            elif ev == "parado":
                msg = "Parado. O que já foi cadastrado ficou no site."
            else:
                msg = f"Parou com erro: {t}"
                self.chip_nav.mudar("Navegador fechado", "#3b2a63")
                messagebox.showerror("Robô Móveis Stylo", msg)
            self.status(msg)
            self.escrever(msg)
            pf.rodando(False)
            if self.pagina is self.paginas.get("feitas"):
                self.paginas["feitas"].carregar()
            if ja_fichas:
                self.after(300, lambda: self._oferecer_atualizar(ja_fichas))
        elif ev == "ml_aviso":
            if t == "ml_verificar":
                self.status("O Mercado Livre pediu uma verificação. Resolva na janela do navegador e o robô continua.")
                self.chip_nav.mudar("Verifique no navegador", AMBAR)
                self.paginas["ml"].aviso("O Mercado Livre pediu uma verificação (ou login). Resolva na janela do navegador; o robô continua sozinho.")
        elif ev == "ml_status":
            self.status(t)
            self.paginas["ml"].aviso(t, carregando=True)
        elif ev == "ml_resultados":
            self.ocupado_ml = False
            self.trazer_para_frente()
            self.chip_nav.mudar("Navegador aberto", VERDE)
            self.paginas["ml"].resultados(*t)
            self.status(f"{len(self.paginas['ml'].itens)} anúncio(s) encontrado(s).")
        elif ev == "ml_thumb":
            self.paginas["ml"].thumb(*t)
        elif ev == "ml_erro":
            self.ocupado_ml = False
            self.paginas["ml"].aviso(t, erro=True)
            self.status(t)
        elif ev == "ml_precos":
            self.ocupado_ml = False
            self.trazer_para_frente()
            ed = self.paginas.get("editor")
            if ed:
                ed.precos_ml(*t)
        elif ev == "ml_precos_erro":
            self.ocupado_ml = False
            ed = self.paginas.get("editor")
            if ed:
                ed.precos_ml_erro(*t)
        elif ev == "ml_status_conc":
            self.status(t)
        elif ev == "conc_item":
            self.paginas["concorrentes"].carregar()
        elif ev == "conc_fim":
            self.ocupado_ml = False
            self.trazer_para_frente()
            self.paginas["concorrentes"].fim(t)
        elif ev == "ia_imagem":
            dlg = t[0]
            try:
                if dlg.winfo_exists():
                    dlg.recebeu(*t[1:])
            except Exception:
                pass
        elif ev in ("canva_aviso", "canva_fim"):
            dlg = t[0]
            try:
                if dlg.winfo_exists():
                    (dlg.lbl_rod.configure(text=t[1]) if ev == "canva_aviso" else dlg.canva_fim(t[1]))
            except Exception:
                pass
        elif ev == "ia_textos":
            ed = self.paginas.get("editor")
            if ed:
                ed.textos_prontos(t)
        elif ev == "ml_lote_fim":
            pastas, falhas = t
            self.ocupado_ml = False
            self.trazer_para_frente()
            self.paginas["ml"].aviso("")
            if len(pastas) == 1 and not falhas:
                self._evento("ml_importado", None, pastas[0])
                return
            self.recarregar(selecionar=pastas[0] if pastas else None)
            self.ir("fichas")
            msg = f"{len(pastas)} ficha(s) criada(s) do Mercado Livre" + (f", {len(falhas)} com falha" if falhas else "")
            self.status(msg + ". Confira o custo, o estoque e o peso de cada uma.")
            self.toast(msg, VERDE if not falhas else AMBAR)
            if falhas:
                messagebox.showwarning("Importar do Mercado Livre", "Não deu para importar:\n\n" +
                                       "\n".join(f"• {cortar(l or 'navegador', 60)}: {m}" for l, m in falhas[:8]))
        elif ev == "ml_importado":
            self.ocupado_ml = False
            self.trazer_para_frente()
            self.paginas["ml"].aviso("")
            self.status("Ficha criada a partir do Mercado Livre. Confira o custo, o estoque e as medidas.")
            self.recarregar(selecionar=t)
            self.editar(motor.ler_ficha(t))
            self.toast("Ficha criada do Mercado Livre")

    def trazer_para_frente(self):
        try:
            self.deiconify()
            self.lift()
            self.attributes("-topmost", True)
            self.after(400, lambda: self.attributes("-topmost", False))
            self.focus_force()
        except Exception:
            pass

    def parar(self):
        if self.rodando:
            self.parar_flag.set()
            self.status("Parando depois do produto atual…")

    def abrir_relatorio(self):
        arq = motor.relatorio_path()
        abrir_no_sistema(arq if arq.exists() else arq.parent)

    def questionario(self):
        arq = Path(__file__).with_name("questionario-produto-stylo.md")
        if not arq.exists():
            messagebox.showwarning("Questionário", "Não achei o arquivo questionario-produto-stylo.md na pasta do robô.")
            return
        texto = arq.read_text(encoding="utf-8")
        try:
            texto += ("\n\n## Minha tabela de materiais e horas (use para montar a ficha_de_custo)\n\n"
                      "Monte a `ficha_de_custo` com estes itens e preços: estime a quantidade de cada um para a peça "
                      "(metros de tubo, m² de espuma e madeira, metros de corano, kg de tinta, horas). "
                      "Use o nome igual ao da tabela.\n\n" + recursos.texto_materiais() + "\n")
        except Exception:
            pass
        self.clipboard_clear()
        self.clipboard_append(texto)
        self.toast("Questionário copiado. Cole numa IA (Ctrl+V) com as fotos.")

    def _conferir_atalho(self):
        """Cria (uma vez por versão) os atalhos com o ícone na Área de Trabalho e no menu Iniciar."""
        pasta = Path(__file__).parent
        marca = pasta / ".atalho-ok"
        versao = str((recursos.versao_local() or {}).get("versao", ""))
        try:
            if marca.exists() and marca.read_text(encoding="utf-8").strip() == versao:
                return
        except Exception:
            pass
        script = pasta / "criar-atalho.ps1"
        if not script.exists():
            return

        def roda():
            try:
                subprocess.run(["powershell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-File", str(script)],
                               cwd=str(pasta), timeout=60, creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0))
                marca.write_text(versao, encoding="utf-8")
            except Exception:
                pass
        threading.Thread(target=roda, daemon=True).start()

    def fechar(self):
        if self.rodando and not messagebox.askyesno("Fechar", "O robô está cadastrando. Fechar mesmo assim?"):
            return
        self.parar_flag.set()
        self.withdraw()
        self.tarefas.put(("sair", None))
        self.after(8000, self._sair)

    def _sair(self):
        if getattr(self, "_reabrir", False):
            self._reabrir = False
            try:
                subprocess.Popen([sys.executable, str(motor.BASE / "janela.pyw")], cwd=str(motor.BASE))
            except Exception:
                pass
        try:
            self.destroy()
        except Exception:
            pass


# ------------------------------------------------------------------ dicas (tooltip)

class Dica:
    """Balãozinho com explicação quando o mouse para em cima de um botão."""

    def __init__(self, w, texto):
        self.w, self.texto, self.janela, self.job = w, texto, None, None
        try:
            w.bind("<Enter>", self._entrar, add="+")
            w.bind("<Leave>", self._sair, add="+")
            w.bind("<Button-1>", self._sair, add="+")
        except Exception:
            pass

    def _entrar(self, e=None):
        self._cancelar()
        self.job = self.w.after(450, self._mostrar)

    def _cancelar(self):
        if self.job:
            try:
                self.w.after_cancel(self.job)
            except Exception:
                pass
            self.job = None

    def _mostrar(self):
        self.job = None
        try:
            x, y = self.w.winfo_pointerx() + 14, self.w.winfo_pointery() + 18
            j = self.janela = ctk.CTkToplevel(self.w)
            j.wm_overrideredirect(True)
            j.attributes("-topmost", True)
            j.configure(fg_color="#1d1a2b")
            ctk.CTkLabel(j, text=self.texto, text_color="white", fg_color="#1d1a2b", font=F(12), padx=10, pady=5,
                         wraplength=320, justify="left").pack()
            j.geometry(f"+{x}+{y}")
        except Exception:
            self.janela = None

    def _sair(self, e=None):
        self._cancelar()
        if self.janela:
            try:
                self.janela.destroy()
            except Exception:
                pass
            self.janela = None


def dica(w, texto):
    Dica(w, texto)
    return w


# ------------------------------------------------------------------ página Início

class PaginaInicio(ctk.CTkFrame):
    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=1)
        from datetime import datetime
        dias = ["segunda", "terça", "quarta", "quinta", "sexta", "sábado", "domingo"]
        hoje = datetime.now()
        hora = hoje.hour
        saud = "Bom dia" if hora < 12 else "Boa tarde" if hora < 18 else "Boa noite"
        self.cab = Cabecalho(self, f"{saud}, Marco!", f"Hoje é {dias[hoje.weekday()]}, {hoje:%d/%m}. O que vamos fazer?")
        self.cab.grid(row=0, column=0, sticky="ew", padx=24, pady=(20, 8))
        self.corpo = ctk.CTkScrollableFrame(self, fg_color="transparent")
        self.corpo.grid(row=1, column=0, sticky="nsew", padx=14, pady=(0, 10))
        self.corpo.grid_columnconfigure(0, weight=1)

    def ao_mostrar(self):
        self.desenhar()

    def _ir_fichas(self, filtro="Todas", marcar=False, selecionar=None):
        app = self.app
        pf = app.paginas["fichas"]
        app.ir("fichas")
        if selecionar is not None:
            app.recarregar(selecionar=selecionar)
        pf.filtro.set(filtro)
        pf.filtrar()
        if marcar:
            pf.marcar_todas(True)

    def _conferir(self):
        self.app.ir("concorrentes")
        self.app.paginas["concorrentes"].conferir()

    def desenhar(self):
        app = self.app
        for w in self.corpo.winfo_children():
            w.destroy()
        fichas = motor.listar_fichas(app.pasta())
        feitos = app.pasta() / "feitos"
        n_feitas = sum(1 for p in feitos.iterdir() if p.is_dir()) if feitos.is_dir() else 0
        prontas = [f for f in fichas if f.pronta and f.status not in ("feita", "ja")]
        erro = [f for f in fichas if f.status == "erro"]
        conc = recursos.ler_concorrentes()
        mudaram = []
        for c in conc:
            h = [x["preco"] for x in c.get("historico") or []]
            if len(h) > 1 and abs(h[-1] - h[-2]) >= 0.01:
                mudaram.append((c, h[-2], h[-1]))

        # números
        kpis = ctk.CTkFrame(self.corpo, fg_color="transparent")
        kpis.grid(row=0, column=0, sticky="ew", padx=(10, 18), pady=(4, 12))
        dados = [("Na fila", len(fichas), TEXTO, lambda: self._ir_fichas()),
                 ("Prontas", len(prontas), VERDE, lambda: self._ir_fichas("Prontas")),
                 ("Com erro", len(erro), VERMELHO if erro else TEXTO, lambda: self._ir_fichas("Com erro")),
                 ("Cadastradas", n_feitas, ROXO_TXT, lambda: app.ir("feitas")),
                 ("Concorrentes", len(conc), TEXTO, lambda: app.ir("concorrentes"))]
        for i, (rot, n, cor, acao) in enumerate(dados):
            kpis.grid_columnconfigure(i, weight=1, uniform="k")
            k = ctk.CTkFrame(kpis, fg_color=PAINEL, corner_radius=16, border_width=1, border_color=BORDA, cursor="hand2")
            k.grid(row=0, column=i, sticky="ew", padx=5)
            a = ctk.CTkLabel(k, text=rot.upper(), font=F(11, "bold"), text_color=MUDO, anchor="w")
            a.pack(anchor="w", padx=16, pady=(12, 0))
            b = ctk.CTkLabel(k, text=str(n), font=F(28, "bold"), text_color=cor, anchor="w")
            b.pack(anchor="w", padx=16, pady=(0, 10))
            for w in (k, a, b):
                w.bind("<Button-1>", lambda e, f=acao: f())

        # ações
        ctk.CTkLabel(self.corpo, text="Atalhos", font=F(16, "bold"), text_color=TEXTO, anchor="w").grid(
            row=1, column=0, sticky="w", padx=16, pady=(6, 4))
        grade = ctk.CTkFrame(self.corpo, fg_color="transparent")
        grade.grid(row=2, column=0, sticky="ew", padx=(10, 18))
        acoes = [("＋", "Nova ficha", "Preencher do zero (Ctrl+N)", ROXO, lambda: app.editar(None)),
                 ("⤓", "Importar link do ML", "Cole o link de um anúncio (Ctrl+L)", "#ca8a04", lambda: ImportarLinks(app)),
                 ("⌕", "Buscar no Mercado Livre", "Ideias e preços dos concorrentes", "#ca8a04", lambda: app.ir("ml")),
                 ("✎", "Colar ficha da IA", "Resposta do questionário", ROXO, lambda: Colar(app)),
                 ("◎", "Conferir concorrentes", f"{len(conc)} anúncio(s) vigiado(s)", AMBAR, self._conferir),
                 ("⇧", "Cadastrar prontas", f"{len(prontas)} pronta(s) para subir ao site", VERDE,
                  lambda: self._ir_fichas("Prontas", marcar=True))]
        for i, (ico, tit, sub, cor, acao) in enumerate(acoes):
            grade.grid_columnconfigure(i % 3, weight=1, uniform="a")
            c = ctk.CTkFrame(grade, fg_color=PAINEL, corner_radius=16, border_width=1, border_color=BORDA, cursor="hand2")
            c.grid(row=i // 3, column=i % 3, sticky="ew", padx=5, pady=5)
            ic = ctk.CTkLabel(c, text=ico, width=44, height=44, corner_radius=12, fg_color=cor, text_color="white", font=F(20, "bold"))
            ic.pack(side="left", padx=(14, 12), pady=14)
            tx = ctk.CTkFrame(c, fg_color="transparent")
            tx.pack(side="left", fill="x", expand=True)
            t1 = ctk.CTkLabel(tx, text=tit, font=F(14, "bold"), text_color=TEXTO, anchor="w")
            t1.pack(anchor="w")
            t2 = ctk.CTkLabel(tx, text=sub, font=F(12), text_color=MUDO, anchor="w")
            t2.pack(anchor="w")

            def hover(dentro, c=c):
                c.configure(border_color=ROXO if dentro else BORDA)
            for w in (c, ic, tx, t1, t2):
                w.bind("<Button-1>", lambda e, f=acao: f())
                w.bind("<Enter>", lambda e, h=hover: h(True))
                w.bind("<Leave>", lambda e, h=hover: h(False))

        # atenção
        ctk.CTkLabel(self.corpo, text="Precisa de atenção", font=F(16, "bold"), text_color=TEXTO, anchor="w").grid(
            row=3, column=0, sticky="w", padx=16, pady=(16, 4))
        lista = ctk.CTkFrame(self.corpo, fg_color=PAINEL, corner_radius=16, border_width=1, border_color=BORDA)
        lista.grid(row=4, column=0, sticky="ew", padx=15, pady=(0, 16))
        itens = []
        for f in erro:
            itens.append(("✕", VERMELHO, f.nome, f.erros[0] if f.erros else "Com erro", lambda f=f: self._ir_fichas(selecionar=f.pasta)))
        for c, antes, agora in mudaram:
            seta = "▼" if agora < antes else "▲"
            itens.append((seta, AMBAR, c.get("titulo") or c["link"], f"Concorrente mudou de R$ {reais(antes)} para R$ {reais(agora)}",
                          lambda: app.ir("concorrentes")))
        com_aviso = [f for f in prontas if f.avisos]
        if com_aviso:
            n_av = sum(len(f.avisos) for f in com_aviso)
            itens.insert(0, ("✦", ROXO, f"{len(com_aviso)} ficha(s) com {n_av} aviso(s)",
                             "toque para o robô resolver o que der e perguntar o resto", lambda: app.resolver_avisos(com_aviso)))
        for f in com_aviso[:5]:
            itens.append(("⚠", AMBAR, f.nome, f.avisos[0], lambda f=f: self._ir_fichas(selecionar=f.pasta)))
        if not itens:
            ctk.CTkLabel(lista, text="✓  Tudo em dia. Nenhuma ficha com erro e nenhum concorrente mudou de preço.",
                         font=F(13), text_color=VERDE, anchor="w").pack(fill="x", padx=16, pady=16)
        for ico, cor, tit, sub, acao in itens[:10]:
            l = ctk.CTkFrame(lista, fg_color="transparent", cursor="hand2")
            l.pack(fill="x", padx=8, pady=2)
            a = ctk.CTkLabel(l, text=ico, width=30, height=30, corner_radius=9, fg_color=cor, text_color="white", font=F(13, "bold"))
            a.pack(side="left", padx=(8, 10), pady=6)
            b = ctk.CTkLabel(l, text=cortar(tit, 60), font=F(13, "bold"), text_color=TEXTO, anchor="w")
            b.pack(side="left")
            c2 = ctk.CTkLabel(l, text="  ·  " + cortar(sub, 70), font=F(12), text_color=MUDO, anchor="w")
            c2.pack(side="left")
            for w in (l, a, b, c2):
                w.bind("<Button-1>", lambda e, f=acao: f())
        if len(itens) > 10:
            ctk.CTkLabel(lista, text=f"e mais {len(itens) - 10}…", font=F(12), text_color=MUDO).pack(anchor="w", padx=18, pady=(0, 10))
        ctk.CTkLabel(self.corpo, text="Atalhos do teclado:  Ctrl+N nova ficha  ·  Ctrl+L importar link  ·  Ctrl+F buscar  ·  "
                                      "F5 atualizar  ·  no editor: Ctrl+S salvar, Esc voltar",
                     font=F(11), text_color=MUDO, anchor="w").grid(row=5, column=0, sticky="w", padx=16, pady=(0, 10))


# ------------------------------------------------------------------ página Fichas

class Cabecalho(ctk.CTkFrame):
    def __init__(self, master, titulo, sub=""):
        super().__init__(master, fg_color="transparent")
        self.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(self, text=titulo, font=F(24, "bold"), text_color=TEXTO, anchor="w").grid(row=0, column=0, sticky="w")
        self.sub = ctk.CTkLabel(self, text=sub, font=F(13), text_color=MUDO, anchor="w")
        self.sub.grid(row=1, column=0, sticky="w")
        self.acoes = ctk.CTkFrame(self, fg_color="transparent", width=1, height=1)
        self.acoes.grid(row=0, column=1, rowspan=2, sticky="e")


class PaginaFichas(ctk.CTkFrame):
    FILTROS = ["Todas", "Na fila", "Para atualizar", "Cadastradas", "Com erro", "Com aviso"]
    ORDENS = ["Mais recentes", "Nome A–Z", "Maior preço", "Menor preço", "Código"]

    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.cartoes = []
        self.atual = None
        self.grid_columnconfigure(0, weight=5, uniform="c")
        self.grid_columnconfigure(1, weight=6, uniform="c")
        self.grid_rowconfigure(2, weight=1)

        cab = Cabecalho(self, "Fichas", "")
        cab.grid(row=0, column=0, columnspan=2, sticky="ew", padx=24, pady=(20, 10))
        self.lbl_cont = cab.sub
        dica(Botao(cab.acoes, "＋  Nova ficha", "primario", width=130, command=lambda: app.editar(None)), "Ficha em branco (Ctrl+N)").pack(side="left", padx=4)
        Botao(cab.acoes, "Colar ficha da IA", "suave", width=140, command=lambda: Colar(app)).pack(side="left", padx=4)
        dica(Botao(cab.acoes, "🔗  Importar link do ML", "ml", width=190, command=lambda: ImportarLinks(app)),
             "Cole o link de um ou mais anúncios do Mercado Livre (Ctrl+L)").pack(side="left", padx=4)
        dica(Botao(cab.acoes, "Pasta", "fantasma", width=70, command=lambda: abrir_no_sistema(app.pasta())),
             "Abrir a pasta das fichas no Windows").pack(side="left", padx=4)
        dica(Botao(cab.acoes, "⟳", "fantasma", width=40, command=app.recarregar), "Atualizar a lista (F5)").pack(side="left", padx=(4, 0))

        barra = ctk.CTkFrame(self, fg_color="transparent")
        barra.grid(row=1, column=0, columnspan=2, sticky="ew", padx=24, pady=(0, 10))
        barra.grid_columnconfigure(0, weight=1)
        self.busca = Entrada(barra, placeholder_text="⌕   Buscar por nome, código, categoria ou material", height=38, ao_mudar=self.filtrar)
        self.busca.grid(row=0, column=0, sticky="ew", padx=(0, 10))
        self.filtro = ctk.CTkSegmentedButton(barra, values=self.FILTROS, command=lambda v: self.filtrar(), height=36,
                                             selected_color=ROXO, selected_hover_color=ROXO_H, font=F(12, "bold"),
                                             unselected_color=PAINEL, unselected_hover_color=SUAVE, fg_color=PAINEL,
                                             text_color=TEXTO)
        self.filtro.set("Todas")
        self.filtro.grid(row=0, column=1, padx=(0, 10))
        self.ordem = ctk.CTkOptionMenu(barra, values=self.ORDENS, command=lambda v: self.filtrar(), height=36, width=150,
                                       fg_color=PAINEL, button_color=ROXO_CLARO, button_hover_color=("#ddd6fe", "#3a2f68"),
                                       text_color=TEXTO, font=F(12), dropdown_font=F(12), corner_radius=10)
        self.ordem.set("Mais recentes")
        self.ordem.grid(row=0, column=2)

        esq = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        esq.grid(row=2, column=0, sticky="nsew", padx=(24, 8), pady=(0, 16))
        esq.grid_rowconfigure(1, weight=1)
        esq.grid_columnconfigure(0, weight=1)
        sel = ctk.CTkFrame(esq, fg_color="transparent")
        sel.grid(row=0, column=0, sticky="ew", padx=12, pady=(10, 0))
        ctk.CTkButton(sel, text="Marcar prontas", width=110, height=26, fg_color="transparent", text_color=ROXO_TXT,
                      hover_color=SUAVE, font=F(12, "bold"), command=lambda: self.marcar_todas(True)).pack(side="left")
        ctk.CTkButton(sel, text="Desmarcar", width=90, height=26, fg_color="transparent", text_color=MUDO,
                      hover_color=SUAVE, font=F(12), command=lambda: self.marcar_todas(False)).pack(side="left")
        ctk.CTkButton(sel, text="Etiquetas", width=80, height=26, fg_color="transparent", text_color=ROXO_TXT,
                      hover_color=SUAVE, font=F(12, "bold"), command=lambda: Etiquetas(app, self.alvos())).pack(side="right")
        ctk.CTkButton(sel, text="Lote", width=50, height=26, fg_color="transparent", text_color=ROXO_TXT,
                      hover_color=SUAVE, font=F(12, "bold"), command=lambda: Lote(app, self.alvos())).pack(side="right")
        dica(ctk.CTkButton(sel, text="✦ Completar", width=90, height=26, fg_color="transparent", text_color=ROXO_TXT,
                           hover_color=SUAVE, font=F(12, "bold"), command=lambda: app.completar_fichas(self.alvos())),
             "Preenche o que está vazio e resolve os avisos das fichas marcadas (ou da aberta)").pack(side="right")
        self.lista = ctk.CTkScrollableFrame(esq, fg_color="transparent")
        self.lista.grid(row=1, column=0, sticky="nsew", padx=6, pady=(4, 6))
        self.lista.grid_columnconfigure(0, weight=1)
        self.vazio = ctk.CTkLabel(self.lista, text="", text_color=MUDO, justify="center", font=F(13))
        acoes = ctk.CTkFrame(esq, fg_color="transparent")
        acoes.grid(row=2, column=0, sticky="ew", padx=12, pady=(0, 12))
        acoes.grid_columnconfigure(0, weight=1)
        self.bt_parar = Botao(acoes, "■  Parar", "vermelho", width=100, height=42, command=app.parar, state="disabled")
        self.bt_parar.grid(row=0, column=1, padx=6)
        self.bt_cad = Botao(acoes, "Cadastrar", "primario", height=42, font=F(15, "bold"), command=self.cadastrar_marcadas)
        self.bt_cad.grid(row=0, column=0, sticky="ew")

        self.det = ctk.CTkScrollableFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        self.det.grid(row=2, column=1, sticky="nsew", padx=(8, 24), pady=(0, 16))
        self.det.grid_columnconfigure(0, weight=1)

    # lista
    def carregar(self, fichas, selecionar=None):
        for c in self.cartoes:
            c.destroy()
        self.update_idletasks()
        try:
            escala = ctk.ScalingTracker.get_widget_scaling(self)
        except Exception:
            escala = 1
        larg = self.lista.winfo_width() / (escala or 1)
        letras = int(max(larg - 190, 160) / 8.6) if larg > 100 else 34
        self.cartoes = [CartaoFicha(self.lista, self.app, f, letras=letras) for f in fichas]
        self.app.mapa = {}
        prontas = sum(f.pronta for f in fichas)
        erros = sum(f.status == "erro" for f in fichas)
        feitas = sum(f.status in ("feita", "editada") for f in fichas)
        editadas = sum(f.status == "editada" for f in fichas)
        fila = len(fichas) - feitas
        self.lbl_cont.configure(text=f"{fila} na fila  ·  {prontas} prontas" + (f"  ·  {erros} com erro" if erros else "")
                                + (f"  ·  {feitas} cadastradas" if feitas else "") + (f"  ·  {editadas} para atualizar" if editadas else ""))
        self.atual = None
        self.filtrar()
        alvo = None
        if selecionar:
            alvo = next((c for c in self.cartoes if c.ficha.pasta == Path(selecionar)), None)
        visiveis = [c for c in self.cartoes if c.winfo_manager()]
        alvo = alvo or (visiveis[0] if visiveis else None)
        if alvo:
            self.selecionar(alvo)
        else:
            self.mostrar_vazio()
        self.atualizar_botao()

    def _bate(self, c):
        f = c.ficha
        termo = self.busca.get().strip().lower()
        if termo:
            d = f.dados
            alvo = " ".join(str(x) for x in [f.nome, d.get("codigo"), d.get("categoria"), d.get("material"), f.pasta.name]
                            + [v.get("codigo") for v in f.variacoes] + [v.get("valor") for v in f.variacoes]).lower()
            if not all(p in alvo for p in termo.split()):
                return False
        filtro = self.filtro.get()
        if filtro == "Prontas":
            return f.pronta
        if filtro == "Na fila":
            return f.status not in ("feita", "editada", "ja")
        if filtro == "Para atualizar":
            return f.status == "editada"
        if filtro == "Cadastradas":
            return f.status in ("feita", "editada", "ja")
        if filtro == "Com erro":
            return f.status == "erro"
        if filtro == "Com aviso":
            return bool(f.avisos)
        if filtro == "Sob encomenda":
            return motor.sim(f.dados.get("sob_encomenda"))
        return True

    def filtrar(self):
        ordem = self.ordem.get()
        chave = {
            "Mais recentes": lambda c: -c.ficha.modificada,
            "Nome A–Z": lambda c: c.ficha.nome.lower(),
            "Maior preço": lambda c: -max(resumo(c.ficha)[0] or [0]),
            "Menor preço": lambda c: min(resumo(c.ficha)[0] or [10 ** 9]),
            "Código": lambda c: str(c.ficha.dados.get("codigo") or "~").lower(),
        }[ordem]
        r = 0
        for c in sorted(self.cartoes, key=chave):
            if self._bate(c):
                c.grid(row=r, column=0, sticky="ew", padx=4, pady=4)
                r += 1
            else:
                c.grid_remove()
        if r == 0:
            txt = ("Nenhuma ficha ainda.\n\nToque em “＋ Nova ficha”, “Colar ficha da IA”\nou importe um produto do Mercado Livre."
                   if not self.cartoes else "Nenhuma ficha com esse filtro.")
            self.vazio.configure(text=txt)
            self.vazio.grid(row=0, column=0, pady=50)
        else:
            self.vazio.grid_remove()

    def marcar_todas(self, valor):
        for c in self.cartoes:
            if c.check and pode_subir(c.ficha) and c.winfo_manager():
                c.var.set(valor)
        self.atualizar_botao()

    def alvos(self):
        """As fichas marcadas; se nenhuma, as que aparecem com o filtro atual."""
        marcadas = [c.ficha for c in self.cartoes if c.var.get()]
        return marcadas or [c.ficha for c in self.cartoes if c.winfo_manager()]

    def marcadas(self):
        return [c for c in self.cartoes if c.var.get() and pode_subir(c.ficha)]

    def atualizar_botao(self):
        m = self.marcadas()
        novas = sum(c.ficha.status != "editada" for c in m)
        edit = len(m) - novas
        if not m:
            txt = "Marque as fichas para cadastrar"
        elif not edit:
            txt = f"Cadastrar {novas} {'produto' if novas == 1 else 'produtos'} no site"
        elif not novas:
            txt = f"Atualizar {edit} no site"
        else:
            txt = f"Cadastrar {novas} e atualizar {edit} no site"
        self.bt_cad.configure(text=txt, state="normal" if m and not self.app.rodando else "disabled")

    def cadastrar_marcadas(self, cartoes=None):
        cartoes = cartoes or self.marcadas()
        if not cartoes:
            return
        self.app.mapa = {id(c.ficha): c for c in cartoes}
        self.app.cadastrar([c.ficha for c in cartoes], atualizar=any(c.ficha.status == "editada" for c in cartoes))

    def rodando(self, sim_):
        self.bt_parar.configure(state="normal" if sim_ else "disabled")
        for c in self.cartoes:
            if c.check:
                c.check.configure(state="disabled" if sim_ or not pode_subir(c.ficha) else "normal")
        if sim_:
            self.bt_cad.configure(state="disabled", text="Cadastrando…")
        else:
            self.atualizar_botao()
        if self.atual:
            self.mostrar(self.atual.ficha)

    # detalhe
    def limpar(self):
        for w in self.det.winfo_children():
            w.destroy()
        self.app._refs = []

    def mostrar_vazio(self):
        self.limpar()
        ctk.CTkLabel(self.det, text="Como usar", font=F(18, "bold"), anchor="w", text_color=TEXTO).grid(sticky="w", padx=20, pady=(20, 8))
        passos = [
            ("1", "Crie a ficha: “＋ Nova ficha”, “Colar ficha da IA” (com o questionário) ou “Importar do Mercado Livre”."),
            ("2", "Confira e edite custo, preço, estoque, medidas e fotos. Duplo clique abre o editor."),
            ("3", "Marque as fichas prontas e toque em Cadastrar."),
            ("4", "Na primeira vez, entre na conta da Móveis Stylo na janela do navegador."),
            ("5", "Depois ligue o produto ao anúncio do Mercado Livre no site, como na Boticário."),
        ]
        for n, t in passos:
            l = ctk.CTkFrame(self.det, fg_color=SUAVE, corner_radius=12)
            l.grid(sticky="ew", padx=20, pady=4)
            l.grid_columnconfigure(1, weight=1)
            ctk.CTkLabel(l, text=n, width=28, height=28, corner_radius=14, fg_color=ROXO, text_color="white", font=F(13, "bold")).grid(row=0, column=0, padx=10, pady=10)
            ctk.CTkLabel(l, text=t, anchor="w", justify="left", wraplength=380, text_color=TEXTO, font=F(13)).grid(row=0, column=1, sticky="w", padx=(0, 10))

    def selecionar(self, cartao):
        for c in self.cartoes:
            c.marcar(c is cartao)
        self.atual = cartao
        self.mostrar(cartao.ficha)

    def _largura(self):
        self.update_idletasks()
        try:
            escala = ctk.ScalingTracker.get_widget_scaling(self)
        except Exception:
            escala = 1
        larg = self.det.winfo_width() / (escala or 1)
        return int(max(larg - 70, 320)) if larg > 200 else 440

    def mostrar(self, f):
        self.limpar()
        app = self.app
        d = f.dados
        wrap = self._largura()
        r = 0

        def por(w, **kw):
            nonlocal r
            w.grid(row=r, column=0, sticky=kw.pop("sticky", "ew"), padx=kw.pop("padx", 20), pady=kw.pop("pady", 4), **kw)
            r += 1

        topo = ctk.CTkFrame(self.det, fg_color="transparent")
        topo.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(topo, text=f.nome, font=F(20, "bold"), anchor="w", justify="left", wraplength=wrap - 120,
                     text_color=TEXTO).grid(row=0, column=0, sticky="w")
        texto, cor = STATUS.get(f.status, (f.status, "#64748b"))
        Chip(topo, texto, cor).grid(row=0, column=1, padx=4, sticky="ne")
        por(topo, pady=(20, 2))
        info = "  ·  ".join(x for x in [
            str(d.get("codigo") or ""), str(d.get("categoria") or "Sem categoria"),
            ("Sob encomenda" + (f" ({int(motor.numero(d.get('prazo_dias')))} dias)" if d.get("prazo_dias") else "")) if motor.sim(d.get("sob_encomenda")) else "Pronta entrega",
            f"por {str(d.get('variacao_por')).lower()}" if d.get("variacao_por") and len(f.variacoes) > 1 else ""] if x)
        por(ctk.CTkLabel(self.det, text=info, text_color=MUDO, anchor="w", justify="left", wraplength=wrap, font=F(13)))

        acoes = ctk.CTkFrame(self.det, fg_color="transparent")
        trava = "disabled" if app.rodando else "normal"
        Botao(acoes, "✎  Editar", "primario", width=96, command=lambda: app.editar(f), state=trava).pack(side="left", padx=(0, 6))
        Botao(acoes, "Kits", "suave", width=60, command=lambda: Kits(app, f), state=trava).pack(side="left", padx=6)
        Botao(acoes, "⧉  Duplicar", "suave", width=104, command=lambda: app.duplicar(f), state=trava).pack(side="left", padx=6)
        if f.pronta and f.status not in ("feita", "ja"):
            Botao(acoes, "Cadastrar esta", "fantasma", width=118, state=trava,
                  command=lambda: self.cadastrar_marcadas([self.atual])).pack(side="left", padx=6)
        elif f.status == "editada" and not f.erros:
            Botao(acoes, "⟳  Subir edições", "primario", width=130, state=trava,
                  command=lambda: self.cadastrar_marcadas([self.atual])).pack(side="left", padx=6)
        por(acoes, sticky="w", pady=(10, 2))
        acoes2 = ctk.CTkFrame(self.det, fg_color="transparent")
        Botao(acoes2, "Etiquetas", "fantasma", width=90, command=lambda: Etiquetas(app, [f])).pack(side="left", padx=(0, 6))
        Botao(acoes2, "✕  Excluir", "perigo", width=92, command=lambda: app.excluir(f), state=trava).pack(side="left", padx=6)
        por(acoes2, sticky="w", pady=(4, 8))

        vendas, estoque, preco = resumo(f)
        custos = [motor.numero(v.get("custo")) for v in f.variacoes if v.get("custo") not in (None, "")]
        kpis = ctk.CTkFrame(self.det, fg_color="transparent")
        for i in range(4):
            kpis.grid_columnconfigure(i, weight=1, uniform="k")
        margem = ""
        if vendas and custos:
            m = (vendas[0] - custos[0]) / vendas[0] * 100 if vendas[0] else 0
            margem = f"{m:.0f}%"
        dados_k = [("Venda", preco.replace(" a ", " – ")), ("Custo", ("R$ " + reais(min(custos))) if custos else "—"),
                   ("Margem", margem or "—"), ("Estoque", "sob encomenda" if motor.sim(d.get("sob_encomenda")) and not estoque else str(estoque))]
        for i, (rot, val) in enumerate(dados_k):
            k = ctk.CTkFrame(kpis, fg_color=SUAVE, corner_radius=12)
            k.grid(row=0, column=i, sticky="ew", padx=(0 if i == 0 else 4, 0 if i == 3 else 4))
            ctk.CTkLabel(k, text=rot.upper(), font=F(10, "bold"), text_color=MUDO, anchor="w").pack(anchor="w", padx=12, pady=(8, 0))
            ctk.CTkLabel(k, text=val, font=F(15 if len(val) <= 11 else 12, "bold"), text_color=VERDE if rot == "Venda" else TEXTO, anchor="w").pack(anchor="w", padx=12, pady=(0, 8))
        por(kpis, pady=(2, 8))

        def faixa(t, fundo, cor):
            por(ctk.CTkLabel(self.det, text=t, anchor="w", justify="left", wraplength=wrap - 30, fg_color=fundo,
                             text_color=cor, corner_radius=10, height=32, padx=12, font=F(12)))
        for e in f.erros:
            faixa("✕  " + e, ("#fff1f2", "#3a1420"), ("#be123c", "#fda4af"))
        if f.avisos and f.status not in ("feita", "ja", "editada"):
            acoes_pend = {}
            for p in recursos.pendencias(f.dados, cfg=dict(app.cfg["robo"])):
                acoes_pend.setdefault(p["chave"], p["acao"])
            av = ctk.CTkFrame(self.det, fg_color=("#fffbeb", "#2a2110"), corner_radius=12)
            av.grid_columnconfigure(0, weight=1)
            topo_av = ctk.CTkFrame(av, fg_color="transparent")
            topo_av.grid(row=0, column=0, columnspan=2, sticky="ew", padx=12, pady=(10, 4))
            ctk.CTkLabel(topo_av, text=f"⚠  {len(f.avisos)} aviso(s)", font=F(13, "bold"), text_color=("#b45309", "#fcd34d")).pack(side="left")
            dica(Botao(topo_av, "✦  Resolver avisos", "primario", width=150, height=28, font=F(12, "bold"), state=trava,
                       command=lambda: app.resolver_avisos([f])), "O robô resolve o que dá e pergunta o resto (custo e peso)").pack(side="right")
            for i, a in enumerate(f.avisos, 1):
                chave = recursos.chave_do_aviso(a)
                acao = acoes_pend.get(chave) or recursos.AVISOS.get(chave, (None, None))[1] if chave else None
                if chave in ("custo", "peso") and not acoes_pend.get(chave):
                    acao = None
                if chave == "fotos":
                    acao = acoes_pend.get("fotos")
                ctk.CTkLabel(av, text="•  " + a, anchor="w", justify="left", wraplength=wrap - 190, text_color=("#92400e", "#fcd34d"),
                             font=F(12)).grid(row=i, column=0, sticky="w", padx=(14, 6), pady=3)
                if acao:
                    rot = acao if acao != "Informar" else "Informar…"
                    cmd = (lambda: app.baixar_fotos_ml([f.pasta])) if chave == "fotos" else (lambda k=chave: app.resolver_avisos([f], {k}))
                    Botao(av, rot, "suave", height=26, width=40, font=F(11, "bold"), state=trava,
                          command=cmd).grid(row=i, column=1, sticky="e", padx=(0, 10), pady=3)
                elif chave == "fotos":
                    Botao(av, "Editar", "fantasma", height=26, width=40, font=F(11), state=trava,
                          command=lambda: app.editar(f)).grid(row=i, column=1, sticky="e", padx=(0, 10), pady=3)
            ctk.CTkLabel(av, text="").grid(row=len(f.avisos) + 1, column=0, pady=1)
            por(av, pady=(4, 6))
        if f.mensagem and f.status == "editada":
            faixa("✎  " + f.mensagem, ("#fffbeb", "#2a2110"), ("#b45309", "#fcd34d"))
        if f.mensagem and f.status in ("falhou", "feita", "ja"):
            faixa(("✓  " if f.status == "feita" else "•  ") + f.mensagem, ROXO_CLARO, ROXO_TXT)
            if f.status in ("feita", "ja"):
                por(Botao(self.det, "⟳  Atualizar no site com esta ficha", "suave", height=32, font=F(12, "bold"), state=trava,
                          command=lambda: app.atualizar_no_site([f])), sticky="w")
        if f.status in ("feita", "editada", "ja"):
            por(PainelEstoque(self.det, app, f, wrap), pady=(8, 6))
        for res in f.resultado:
            rot = f"↗  {res.get('codigo') or ''}  ·  {res.get('nome') or ''}"
            maxc = max(wrap // 8, 30)
            por(Botao(self.det, rot if len(rot) <= maxc else rot[:maxc - 1] + "…", "suave", anchor="w", height=30, font=F(12),
                      command=lambda u=res.get("link"): u and webbrowser.open(u)))

        fotos = f.todas_fotos
        if fotos:
            por(ctk.CTkLabel(self.det, text=f"Fotos ({len(fotos)})", font=F(14, "bold"), anchor="w", text_color=TEXTO), pady=(12, 2))
            grade = ctk.CTkFrame(self.det, fg_color="transparent")
            cols = max(3, min(6, wrap // 96))
            for i, p in enumerate(fotos[:12]):
                img = miniatura(p, 82)
                app._refs.append(img)
                cel = ctk.CTkLabel(grade, text="" if img else "sem prévia", image=img, width=82, height=82, fg_color=SUAVE if not img else "transparent",
                                   corner_radius=12, text_color=MUDO, font=F(10))
                cel.grid(row=i // cols, column=i % cols, padx=4, pady=4)
                cel.bind("<Button-1>", lambda e, q=p: abrir_no_sistema(q))
                if i == 0:
                    Chip(grade, "capa", ROXO).grid(row=0, column=0, sticky="nw", padx=8, pady=8)
            por(grade, sticky="w")

        if f.variacoes:
            por(ctk.CTkLabel(self.det, text="Variações", font=F(14, "bold"), anchor="w", text_color=TEXTO), pady=(12, 2))
            tab = ctk.CTkFrame(self.det, fg_color=SUAVE, corner_radius=12)
            cab = ["Variação", "Código", "Custo", "Venda", "Estoque", "Peso"]
            for c, t in enumerate(cab):
                ctk.CTkLabel(tab, text=t.upper(), font=F(10, "bold"), text_color=MUDO).grid(row=0, column=c, padx=10, pady=(8, 2), sticky="w")
            for i, v in enumerate(f.variacoes[:40], 1):
                emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
                vals = [str(v.get("valor") or "—"), str(v.get("codigo") or "automático"),
                        ("R$ " + reais(motor.numero(v.get("custo")))) if v.get("custo") not in (None, "") else "ficha",
                        "R$ " + reais(motor.numero(v.get("venda"))),
                        str(int(motor.numero(v.get("estoque")))),
                        (decimal_txt(motor.numero(emb.get("peso_kg"))) + " kg") if emb.get("peso_kg") else "—"]
                for c, t in enumerate(vals):
                    ctk.CTkLabel(tab, text=t, font=F(12, "bold" if c == 3 else "normal"),
                                 text_color=VERDE if c == 3 else TEXTO).grid(row=i, column=c, padx=10, pady=1, sticky="w")
            ctk.CTkLabel(tab, text="").grid(row=len(f.variacoes) + 1, column=0, pady=2)
            por(tab, pady=(2, 8))

        desc = str(d.get("descricao") or "").strip()
        if desc:
            por(ctk.CTkLabel(self.det, text="Descrição", font=F(14, "bold"), anchor="w", text_color=TEXTO), pady=(8, 2))
            por(ctk.CTkLabel(self.det, text=desc[:600] + ("…" if len(desc) > 600 else ""), anchor="w", justify="left",
                             wraplength=wrap, text_color=MUDO, font=F(12)))
        ml = d.get("mercado_livre") or {}
        if isinstance(ml, dict) and ml.get("link_referencia"):
            por(Botao(self.det, "Abrir anúncio de referência no Mercado Livre", "ml", height=30, font=F(12, "bold"),
                      command=lambda: webbrowser.open(ml["link_referencia"])), sticky="w", pady=(12, 4))
        lp = ctk.CTkLabel(self.det, text=f"▤  Abrir pasta: {f.pasta}", text_color=ROXO_TXT, anchor="w", font=F(11), wraplength=wrap,
                          justify="left", cursor="hand2")
        lp.bind("<Button-1>", lambda e=None: abrir_no_sistema(f.pasta))
        por(lp, pady=(12, 20))


class PainelEstoque(ctk.CTkFrame):
    """Estoque no site de cada variação de uma ficha cadastrada, e quantas subir de cada."""

    def __init__(self, master, app, ficha, wrap=440):
        super().__init__(master, fg_color=SUAVE, corner_radius=12)
        self.app, self.ficha, self.wrap = app, ficha, wrap
        self.linhas = motor.codigos_no_site(ficha)
        self.campos = {}
        self.grid_columnconfigure(0, weight=1)
        topo = ctk.CTkFrame(self, fg_color="transparent")
        topo.grid(row=0, column=0, sticky="ew", padx=12, pady=(10, 2))
        ctk.CTkLabel(topo, text="Estoque no site", font=F(14, "bold"), text_color=TEXTO).pack(side="left")
        dica(Botao(topo, "⟳", "fantasma", width=34, height=26, command=lambda: self.buscar(forcar=True)),
             "Ler de novo o estoque do site").pack(side="right")
        self.corpo = ctk.CTkFrame(self, fg_color="transparent")
        self.corpo.grid(row=1, column=0, sticky="ew", padx=12, pady=(2, 10))
        self.corpo.grid_columnconfigure(0, weight=1)
        cache = getattr(app, "_estoque_cache", {}).get(str(ficha.pasta))
        if cache and time.time() - cache[0] < 90:
            self.desenhar(cache[1])
        else:
            self.buscar()

    def _aviso(self, texto, cor=MUDO):
        for w in self.corpo.winfo_children():
            w.destroy()
        ctk.CTkLabel(self.corpo, text=texto, text_color=cor, font=F(12), anchor="w", justify="left",
                     wraplength=self.wrap - 40).grid(row=0, column=0, sticky="w", pady=4)

    def _em_fundo(self, tarefa, pronto):
        """Roda tarefa() numa thread e chama pronto(resultado, erro) na janela."""
        caixa = {}

        def rodar():
            try:
                caixa["r"] = tarefa()
            except Exception as e:
                caixa["e"] = str(e).splitlines()[0] if str(e) else repr(e)

        th = threading.Thread(target=rodar, daemon=True)
        th.start()

        def conferir():
            if not self.winfo_exists():
                return
            if th.is_alive():
                self.after(150, conferir)
            else:
                pronto(caixa.get("r"), caixa.get("e"))
        self.after(150, conferir)

    def buscar(self, forcar=False):
        if not motor.ler_conta():
            self._aviso("Conecte o robô ao site (Configurações › Fichas no FlowManeger) para ver o estoque.")
            return
        cods = [c for _, c in self.linhas if c]
        if not cods:
            self._aviso("As variações não têm código: abra o produto no site para ver o estoque.")
            return
        self._aviso("Lendo o estoque do site…")
        nv = self.app.nuvem()
        self._em_fundo(lambda: nv.estoque(cods), self._lido)

    def _lido(self, dados, erro):
        if erro:
            self._aviso("Não deu para ler o estoque: " + erro, VERMELHO)
            return
        if not hasattr(self.app, "_estoque_cache"):
            self.app._estoque_cache = {}
        self.app._estoque_cache[str(self.ficha.pasta)] = (time.time(), dados or {})
        self.desenhar(dados or {})

    def desenhar(self, dados):
        for w in self.corpo.winfo_children():
            w.destroy()
        self.campos = {}
        tab = ctk.CTkFrame(self.corpo, fg_color="transparent")
        tab.grid(row=0, column=0, sticky="ew")
        tab.grid_columnconfigure(0, weight=1)
        for c, t in enumerate(["Variação", "Código", "No site", "Subir"]):
            ctk.CTkLabel(tab, text=t.upper(), font=F(10, "bold"), text_color=MUDO).grid(row=0, column=c, padx=(0, 10), sticky="w")
        letras = max(int((self.wrap - 260) / 8), 14)
        total = 0
        for i, (v, cod) in enumerate(self.linhas, 1):
            info = dados.get((cod or "").lower()) if cod else None
            ctk.CTkLabel(tab, text=cortar(str(v.get("valor") or self.ficha.nome), letras), font=F(12), text_color=TEXTO,
                         anchor="w").grid(row=i, column=0, sticky="w", padx=(0, 10), pady=2)
            ctk.CTkLabel(tab, text=cod or "—", font=F(11), text_color=MUDO).grid(row=i, column=1, sticky="w", padx=(0, 10))
            if not info:
                ctk.CTkLabel(tab, text="não está no site", font=F(11), text_color=AMBAR).grid(row=i, column=2, columnspan=2, sticky="w")
                continue
            n = int(info.get("estoque") or 0)
            total += n
            txt = str(n) + (" (kit)" if info.get("kit") else "") + (" · encomenda" if info.get("encomenda") and not n else "")
            ctk.CTkLabel(tab, text=txt, font=F(13, "bold"), text_color=VERDE if n else TEXTO).grid(row=i, column=2, sticky="w", padx=(0, 10))
            if info.get("kit"):
                ctk.CTkLabel(tab, text="pelos itens", font=F(11), text_color=MUDO).grid(row=i, column=3, sticky="w")
                continue
            e = ctk.CTkEntry(tab, width=64, height=28, placeholder_text="+0", font=F(12), justify="center")
            e.grid(row=i, column=3, sticky="w", pady=2)
            e.bind("<Return>", lambda ev=None: self.subir())
            self.campos[cod] = (e, n, v)
        rod = ctk.CTkFrame(self.corpo, fg_color="transparent")
        rod.grid(row=1, column=0, sticky="ew", pady=(8, 0))
        ctk.CTkLabel(rod, text=f"Total no site: {total}", font=F(12, "bold"), text_color=TEXTO).pack(side="left")
        if self.campos:
            Botao(rod, "Subir estoque", "primario", width=130, height=30, font=F(12, "bold"),
                  state="disabled" if self.app.rodando else "normal", command=self.subir).pack(side="right")
            ctk.CTkLabel(rod, text="Número negativo tira.", font=F(11), text_color=MUDO).pack(side="right", padx=8)

    def subir(self):
        itens, linhas = [], []
        for cod, (e, n, v) in self.campos.items():
            t = e.get().strip().replace("+", "")
            if not t:
                continue
            try:
                q = int(t)
            except ValueError:
                messagebox.showwarning("Estoque", f"“{e.get()}” não é um número ({cod}).")
                return
            if q:
                q = max(q, -n)
                itens.append((cod, q))
                linhas.append(f"• {v.get('valor') or cod}: {n} → {n + q}")
        if not itens:
            messagebox.showinfo("Estoque", "Digite em “Subir” quantas peças quer somar em cada variação.")
            return
        if not messagebox.askyesno("Subir estoque", "Mudar o estoque no site?\n\n" + "\n".join(linhas[:20])
                                   + ("\n…" if len(linhas) > 20 else "") + "\n\nO Mercado Livre ligado ao produto acompanha."):
            return
        self._aviso("Mandando para o site…")
        nv = self.app.nuvem()
        self._em_fundo(lambda: nv.somar_estoque(itens), self._somado)

    def _somado(self, dados, erro):
        if erro:
            self._aviso("Não deu para mudar o estoque: " + erro, VERMELHO)
            return
        falhas = [f"{x.get('codigo')}: {x.get('erro')}" for x in (dados or {}).values() if x.get("erro")]
        self.app.toast("Estoque atualizado no site" if not falhas else "Parte não mudou: " + "; ".join(falhas)[:120],
                       VERDE if not falhas else AMBAR)
        cache = getattr(self.app, "_estoque_cache", {}).get(str(self.ficha.pasta), (0, {}))[1]
        novo = dict(cache)
        novo.update({k: x for k, x in (dados or {}).items() if not x.get("erro") or x.get("id")})
        self.app._estoque_cache[str(self.ficha.pasta)] = (time.time(), novo)
        self.desenhar(novo)


# ------------------------------------------------------------------ página Cadastradas

class PaginaFeitas(ctk.CTkFrame):
    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.itens = []
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(2, weight=1)
        cab = Cabecalho(self, "Cadastradas", "Fichas que o robô já subiu para o site (pasta produtos\\feitos).")
        cab.grid(row=0, column=0, sticky="ew", padx=24, pady=(20, 10))
        self.lbl = cab.sub
        Botao(cab.acoes, "Abrir pasta feitos", "fantasma", width=140,
              command=lambda: abrir_no_sistema(app.pasta() / "feitos" if (app.pasta() / "feitos").exists() else app.pasta())).pack(side="left")
        self.busca = Entrada(self, placeholder_text="⌕   Buscar nas cadastradas", height=38, ao_mudar=self.filtrar)
        self.busca.grid(row=1, column=0, sticky="ew", padx=24, pady=(0, 10))
        self.lista = ctk.CTkScrollableFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        self.lista.grid(row=2, column=0, sticky="nsew", padx=24, pady=(0, 16))
        self.lista.grid_columnconfigure(0, weight=1)
        self.vazio = ctk.CTkLabel(self.lista, text="Nenhuma ficha cadastrada ainda.", text_color=MUDO, font=F(13))

    def ao_mostrar(self):
        self.carregar()

    def carregar(self):
        for w in self.itens:
            w.destroy()
        self.itens = []
        fichas = [f for f in motor.listar_fichas(self.app.pasta(), incluir_feitas=True) if f.status == "feita"]
        self.lbl.configure(text=f"{len(fichas)} ficha(s) já cadastrada(s) no site.")
        for f in fichas:
            self.itens.append(self._linha(f))
        self.filtrar()

    def _linha(self, f):
        l = ctk.CTkFrame(self.lista, fg_color=CARD, corner_radius=14, border_width=1, border_color=BORDA)
        l.ficha = f
        l.grid_columnconfigure(1, weight=1)
        img = miniatura(foto_capa(f), 48)
        self.app._refs.append(img)
        ctk.CTkLabel(l, text="" if img else "—", image=img, width=48, height=48, fg_color="transparent" if img else SUAVE,
                     corner_radius=12).grid(row=0, column=0, rowspan=2, padx=12, pady=10)
        ctk.CTkLabel(l, text=f.nome, font=F(14, "bold"), anchor="w", text_color=TEXTO).grid(row=0, column=1, sticky="ew", pady=(10, 0))
        quando = ""
        try:
            from datetime import datetime
            quando = datetime.fromtimestamp((f.pasta / "resultado.json").stat().st_mtime).strftime("%d/%m/%Y %H:%M")
        except OSError:
            pass
        cods = ", ".join(str(x.get("codigo") or "") for x in f.resultado[:6]) + (" …" if len(f.resultado) > 6 else "")
        ctk.CTkLabel(l, text="  ·  ".join(x for x in [quando, f.mensagem, cods] if x), font=F(12), text_color=MUDO, anchor="w").grid(
            row=1, column=1, sticky="ew", pady=(0, 10))
        bts = ctk.CTkFrame(l, fg_color="transparent")
        bts.grid(row=0, column=2, rowspan=2, padx=12)
        if f.resultado and f.resultado[0].get("link"):
            Botao(bts, "↗  Ver no site", "suave", width=110, height=30, font=F(12, "bold"),
                  command=lambda: webbrowser.open(f.resultado[0]["link"])).pack(side="left", padx=3)
        Botao(bts, "⟳  Atualizar no site", "suave", width=150, height=30, font=F(12, "bold"),
              command=lambda: self.app.atualizar_no_site([f])).pack(side="left", padx=3)
        Botao(bts, "✎  Editar", "fantasma", width=80, height=30, font=F(12),
              command=lambda: self.app.editar(f)).pack(side="left", padx=3)
        Botao(bts, "⧉  Nova a partir desta", "fantasma", width=160, height=30, font=F(12),
              command=lambda: self.app.duplicar(f)).pack(side="left", padx=3)
        Botao(bts, "Pasta", "fantasma", width=60, height=30, font=F(12), command=lambda: abrir_no_sistema(f.pasta)).pack(side="left", padx=3)
        return l

    def filtrar(self):
        termo = self.busca.get().strip().lower()
        r = 0
        for l in self.itens:
            alvo = (l.ficha.nome + " " + str(l.ficha.dados.get("codigo") or "") + " " +
                    " ".join(str(x.get("codigo") or "") for x in l.ficha.resultado)).lower()
            if all(p in alvo for p in termo.split()):
                l.grid(row=r, column=0, sticky="ew", padx=6, pady=4)
                r += 1
            else:
                l.grid_remove()
        if r:
            self.vazio.grid_remove()
        else:
            self.vazio.grid(row=0, column=0, pady=50)


# ------------------------------------------------------------------ página Mercado Livre

class PaginaML(ctk.CTkFrame):
    ORDENS = ["Relevância", "Menor preço", "Maior preço"]

    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.busca_id = 0
        self.itens = []
        self.cartoes = []
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(4, weight=1)
        cab = Cabecalho(self, "Importar do Mercado Livre",
                        "Busque um produto parecido ou cole o link do anúncio. O robô lê o anúncio e cria uma ficha nova com as fotos.")
        cab.grid(row=0, column=0, sticky="ew", padx=24, pady=(20, 12))

        caixa = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        caixa.grid(row=1, column=0, sticky="ew", padx=24)
        caixa.grid_columnconfigure(0, weight=1)
        self.termo = Entrada(caixa, placeholder_text="Ex.: mão francesa ferro 30 cm   ou cole o link do anúncio", height=46, font=F(15))
        self.termo.grid(row=0, column=0, sticky="ew", padx=(14, 8), pady=14)
        self.termo.bind("<Return>", lambda e: self.buscar())
        self.bt_buscar = Botao(caixa, "⌕  Buscar", "ml", width=130, height=46, font=F(15, "bold"), command=self.buscar)
        self.bt_buscar.grid(row=0, column=1, padx=(0, 8), pady=14)
        Botao(caixa, "✦  Pesquisas rápidas", "suave", width=170, height=46, font=F(14, "bold"),
              command=lambda: PesquisasRapidas(app, self)).grid(row=0, column=2, padx=(0, 14), pady=14)
        self.pagina_ml = 1
        self.fim_ml = False
        self.termo_atual = ""

        filtros = ctk.CTkFrame(self, fg_color="transparent")
        filtros.grid(row=2, column=0, sticky="ew", padx=24, pady=(10, 4))
        filtros.grid_columnconfigure(5, weight=1)
        ctk.CTkLabel(filtros, text="Filtrar resultados:", text_color=MUDO, font=F(12)).grid(row=0, column=0, padx=(2, 8))
        self.filtro_txt = Entrada(filtros, placeholder_text="palavra no título", width=180, height=32, ao_mudar=self.desenhar)
        self.filtro_txt.grid(row=0, column=1, padx=4)
        ctk.CTkLabel(filtros, text="R$ de", text_color=MUDO, font=F(12)).grid(row=0, column=2, padx=(10, 4))
        self.pmin = Entrada(filtros, "dinheiro", width=90, height=32, ao_mudar=self.desenhar)
        self.pmin.grid(row=0, column=3)
        ctk.CTkLabel(filtros, text="até", text_color=MUDO, font=F(12)).grid(row=0, column=4, padx=4)
        self.pmax = Entrada(filtros, "dinheiro", width=90, height=32, ao_mudar=self.desenhar)
        self.pmax.grid(row=0, column=5, sticky="w")
        self.ordem = ctk.CTkSegmentedButton(filtros, values=self.ORDENS, command=lambda v: self.desenhar(), height=32,
                                            selected_color=ROXO, selected_hover_color=ROXO_H, font=F(12, "bold"),
                                            unselected_color=PAINEL, unselected_hover_color=SUAVE, fg_color=PAINEL, text_color=TEXTO)
        self.ordem.set("Relevância")
        self.ordem.grid(row=0, column=6, sticky="e")
        self.lbl_stats = ctk.CTkLabel(filtros, text="", text_color=MUDO, font=F(12, "bold"), anchor="w")
        self.lbl_stats.grid(row=1, column=0, columnspan=7, sticky="w", padx=2, pady=(6, 0))

        self.msg = ctk.CTkLabel(self, text="", anchor="w", justify="left", font=F(13), corner_radius=10, height=0)
        self.msg.grid(row=3, column=0, sticky="ew", padx=24, pady=(4, 4))
        self.msg.grid_remove()

        self.grade = ctk.CTkScrollableFrame(self, fg_color="transparent")
        self.grade.grid(row=4, column=0, sticky="nsew", padx=18, pady=(4, 12))
        self.dica = ctk.CTkFrame(self.grade, fg_color="transparent")
        self.dica.grid(row=0, column=0, columnspan=4, sticky="ew", pady=30, padx=20)
        self._vazio()

    def ao_mostrar(self):
        if not self.itens:
            self._vazio()

    def _vazio(self):
        for w in self.dica.winfo_children():
            w.destroy()
        ctk.CTkLabel(self.dica, text="Comece por uma busca", font=F(18, "bold"), text_color=TEXTO).pack(pady=(10, 2))
        ctk.CTkLabel(self.dica, text="Na primeira busca o navegador do robô abre o Mercado Livre. Se ele pedir para confirmar que você "
                                     "não é um robô, resolva lá e o robô continua sozinho.",
                     font=F(12), text_color=MUDO, wraplength=620, justify="center").pack(pady=(0, 14))
        grupos = []
        hist = ler_historico()[:8]
        if hist:
            grupos.append(("Buscadas por você", hist))
        grupos.append(("Sugestões", PESQUISAS[0][2][:8]))
        for titulo, termos in grupos:
            ctk.CTkLabel(self.dica, text=titulo.upper(), font=F(11, "bold"), text_color=MUDO).pack(pady=(10, 4))
            linha = ctk.CTkFrame(self.dica, fg_color="transparent")
            linha.pack()
            for i, t in enumerate(termos):
                Botao(linha, t, "suave", height=30, font=F(12, "bold"), command=lambda t=t: self.buscar_termo(t)).grid(
                    row=i // 4, column=i % 4, padx=4, pady=3, sticky="ew")
        Botao(self.dica, "✦  Ver todas as pesquisas rápidas", "fantasma", height=34,
              command=lambda: PesquisasRapidas(self.app, self)).pack(pady=(16, 0))

    def aviso(self, texto, erro=False, carregando=False):
        if not texto:
            self.msg.grid_remove()
            self.bt_buscar.configure(state="normal", text="⌕  Buscar")
            return
        fundo, cor = ((("#fff1f2", "#3a1420"), ("#be123c", "#fda4af")) if erro else
                      (ROXO_CLARO, ROXO_TXT))
        self.msg.configure(text=("✕  " if erro else "•  ") + texto, fg_color=fundo, text_color=cor, height=38, padx=12)
        self.msg.grid()
        if erro:
            self.bt_buscar.configure(state="normal", text="⌕  Buscar")
        if carregando:
            self.bt_buscar.configure(state="disabled", text="Aguarde…")

    def buscar(self):
        termo = self.termo.get().strip()
        if not termo or self.app.ocupado_ml:
            return
        if self.app.rodando:
            self.aviso("Espere o cadastro terminar para usar o Mercado Livre.", erro=True)
            return
        self.app.ocupado_ml = True
        self.busca_id += 1
        self.app.parar_flag.clear()
        importar = motor.eh_link_ml(termo)
        self.aviso("Lendo o anúncio…" if importar else f"Buscando “{termo}” no Mercado Livre…", carregando=True)
        self.app.status("Abrindo o Mercado Livre no navegador do robô…")
        self.pagina_ml, self.fim_ml, self.termo_atual = 2, False, termo
        if not importar:
            salvar_historico(termo)
        self.app.tarefas.put(("ml_buscar", (termo, self.busca_id)))

    def buscar_termo(self, termo):
        self.termo.definir(termo)
        self.app.ir("ml")
        self.buscar()

    def carregar_mais(self):
        if self.app.ocupado_ml or self.fim_ml or not self.termo_atual:
            return
        self.app.ocupado_ml = True
        self.aviso("Trazendo mais anúncios…", carregando=True)
        self.app.tarefas.put(("ml_mais", (self.termo_atual, self.busca_id, self.pagina_ml + 1, 2)))

    def vigiar(self, item):
        novo = recursos.vigiar(item["link"], item["titulo"], item.get("preco"), item.get("imagem") or "")
        if item.get("_thumb"):
            try:
                FOTOS_CONC.mkdir(exist_ok=True)
                (FOTOS_CONC / (recursos._chave_link(item["link"]) + ".jpg")).write_bytes(item["_thumb"])
            except OSError:
                pass
        self.app.toast("Anúncio na lista de concorrentes" if novo else "Esse anúncio já está na lista", VERDE if novo else ROXO)

    def importar(self, item):
        if self.app.ocupado_ml or self.app.rodando:
            return
        self.app.ocupado_ml = True
        self.app.parar_flag.clear()
        self.aviso(f"Lendo o anúncio “{item['titulo'][:60]}”…", carregando=True)
        self.app.tarefas.put(("ml_importar", item["link"]))

    def resultados(self, busca_id, itens, juntar=False, pagina=1, acabou=False):
        if busca_id != self.busca_id:
            return
        if not juntar:
            self.itens = []
        vistos = {it["link"] for it in self.itens}
        novos = [dict(it, _thumb=None) for it in itens if it["link"] not in vistos]
        self.itens += novos
        self.pagina_ml = max(self.pagina_ml, pagina)
        self.fim_ml = acabou
        self.aviso("")
        if not self.itens:
            self.aviso("Nada encontrado. Tente outras palavras ou cole o link do anúncio.", erro=True)
        self.desenhar()

    def thumb(self, busca_id, link, corpo):
        if busca_id != self.busca_id:
            return
        item = next((it for it in self.itens if it["link"] == link), None)
        if item is None:
            return
        item["_thumb"] = corpo
        c = next((c for c in self.cartoes if c.item is item), None)
        if c:
            img = miniatura(corpo, 150, 14)
            if img:
                c.foto.configure(image=img, text="")

    def desenhar(self):
        for c in self.cartoes:
            c.destroy()
        self.cartoes = []
        if not self.itens:
            return
        self.dica.grid_remove()
        termo = self.filtro_txt.get().strip().lower()
        pmin, pmax = self.pmin.valor(), self.pmax.valor()
        itens = [it for it in self.itens
                 if (not termo or all(p in it["titulo"].lower() for p in termo.split()))
                 and (pmin is None or (it.get("preco") or 0) >= pmin)
                 and (pmax is None or (it.get("preco") or 0) <= pmax)]
        est = recursos.estatisticas([it.get("preco") for it in itens])
        self.lbl_stats.configure(text=(f"{len(itens)} anúncios   ·   menor R$ {reais(est['minimo'])}   ·   mais comum (mediana) R$ {reais(est['mediana'])}"
                                       f"   ·   maior R$ {reais(est['maximo'])}") if est else "")
        o = self.ordem.get()
        if o == "Menor preço":
            itens.sort(key=lambda x: x.get("preco") or 10 ** 9)
        elif o == "Maior preço":
            itens.sort(key=lambda x: -(x.get("preco") or 0))
        self.update_idletasks()
        try:
            escala = ctk.ScalingTracker.get_widget_scaling(self)
        except Exception:
            escala = 1
        cols = max(2, min(6, int(self.grade.winfo_width() / (escala or 1) // 226)))
        for c in range(6):
            self.grade.grid_columnconfigure(c, weight=1 if c < cols else 0, uniform="g" if c < cols else "")
        for n, it in enumerate(itens):
            card = ctk.CTkFrame(self.grade, fg_color=CARD, corner_radius=16, border_width=1, border_color=BORDA)
            card.item = it
            card.grid(row=n // cols, column=n % cols, sticky="nsew", padx=6, pady=6)
            img = miniatura(it["_thumb"], 150, 14) if it.get("_thumb") else None
            card.foto = ctk.CTkLabel(card, text="" if img else "carregando foto…", image=img, width=150, height=150,
                                     fg_color="transparent" if img else SUAVE, corner_radius=14, text_color=MUDO, font=F(11))
            card.foto.pack(padx=10, pady=(10, 6))
            titulo = it["titulo"] if len(it["titulo"]) <= 70 else it["titulo"][:68] + "…"
            ctk.CTkLabel(card, text=titulo, wraplength=190, justify="left", anchor="w", font=F(12), text_color=TEXTO,
                         height=48).pack(fill="x", padx=12)
            ctk.CTkLabel(card, text=("R$ " + reais(it["preco"])) if it.get("preco") else "—", font=F(17, "bold"),
                         text_color=TEXTO, anchor="w").pack(fill="x", padx=12)
            if it.get("vendedor"):
                ctk.CTkLabel(card, text=it["vendedor"][:34], font=F(11), text_color=MUDO, anchor="w").pack(fill="x", padx=12)
            bts = ctk.CTkFrame(card, fg_color="transparent")
            bts.pack(fill="x", padx=10, pady=(8, 12))
            dica(Botao(bts, "Ver", "fantasma", width=44, height=30, font=F(12), command=lambda u=it["link"]: webbrowser.open(u)),
                 "Abrir o anúncio no navegador").pack(side="left")
            dica(Botao(bts, "◎", "fantasma", width=34, height=30, font=F(13), command=lambda x=it: self.vigiar(x)),
                 "Vigiar o preço deste anúncio (vai para Concorrentes)").pack(side="left", padx=(4, 0))
            Botao(bts, "Importar ficha", "primario", height=30, font=F(12, "bold"),
                  command=lambda x=it: self.importar(x)).pack(side="left", padx=(6, 0), fill="x", expand=True)
            self.cartoes.append(card)
        if not itens:
            l = ctk.CTkLabel(self.grade, text="Nenhum resultado com esses filtros.", text_color=MUDO, font=F(13))
            l.grid(row=0, column=0, columnspan=cols, pady=40)
            l.item = None
            l.foto = None
            self.cartoes.append(l)
        elif not self.fim_ml and self.termo_atual:
            b = Botao(self.grade, f"Carregar mais anúncios  ·  {len(self.itens)} até agora", "suave", height=42, font=F(13, "bold"),
                      command=self.carregar_mais)
            b.grid(row=(len(itens) - 1) // cols + 1, column=0, columnspan=cols, sticky="ew", padx=6, pady=(10, 16))
            b.item = None
            b.foto = None
            self.cartoes.append(b)


# ------------------------------------------------------------------ pesquisas rápidas

HISTORICO_ML = motor.BASE / "historico-ml.json"

PESQUISAS = [
    ("Seus produtos", "As peças dos prints que você mandou", [
        "suporte vaso redondo com rodinhas", "suporte vaso rodinhas madeira", "jogo 10 argolas porta vaso",
        "argola porta vaso parede ferro", "suporte bicicleta chão e parede", "rack bicicleta estacionamento chão",
        "mão francesa colonial ferro", "mão francesa ferro prateleira", "mão francesa industrial", "kit mão francesa com parafusos"]),
    ("Prateleiras e parede", "Ferro com tábua de madeira", [
        "prateleira industrial ferro madeira", "nicho ferro madeira parede", "estante industrial ferro madeira",
        "prateleira banheiro ferro madeira", "porta temperos ferro madeira", "cabideiro parede ferro madeira",
        "porta chaves madeira ferro", "adega de parede ferro", "suporte tv industrial", "prateleira flutuante cantoneira"]),
    ("Móveis", "Peças maiores que vendem bem no estilo industrial", [
        "mesa lateral industrial", "mesa de centro ferro madeira", "banqueta industrial ferro", "aparador industrial ferro madeira",
        "rack tv industrial", "sapateira industrial", "mesa dobrável de parede", "escrivaninha industrial",
        "arara de roupas industrial", "carrinho bar ferro", "cabeceira ferro", "bancada industrial"]),
    ("Jardim e plantas", "Para quem já compra suporte de vaso", [
        "suporte plantas escada ferro", "floreira ferro", "cachepô ferro", "suporte vaso tripé ferro",
        "suporte vaso parede ferro", "treliça ferro jardim", "mesa jardim ferro", "suporte xaxim ferro"]),
    ("Bicicleta e garagem", "Ferro puro, fácil de produzir", [
        "suporte bicicleta parede vertical", "suporte bicicleta teto", "suporte capacete parede",
        "suporte botijão gás rodinhas", "porta lenha ferro", "suporte pneu parede", "organizador ferramentas parede"]),
    ("Casa e decoração", "Peças pequenas, boas para kit", [
        "suporte papel toalha ferro", "porta toalha banheiro ferro", "porta revista ferro", "porta vinho ferro",
        "espelho moldura ferro", "luminária industrial ferro", "suporte rede parede", "suporte microondas parede",
        "gancho parede ferro", "porta casaco de pé ferro"]),
    ("Pet", "Ferro e madeira para cães e gatos", [
        "comedouro elevado pet madeira", "cama pet ferro", "arranhador gato madeira", "porta guia coleira parede"]),
]


def ler_historico():
    try:
        return [t for t in json.loads(HISTORICO_ML.read_text(encoding="utf-8")) if isinstance(t, str)][:16]
    except Exception:
        return []


def salvar_historico(termo):
    termo = termo.strip()
    if not termo:
        return
    lista = [termo] + [t for t in ler_historico() if t.lower() != termo.lower()]
    try:
        HISTORICO_ML.write_text(json.dumps(lista[:16], ensure_ascii=False), encoding="utf-8")
    except OSError:
        pass


class PesquisasRapidas(ctk.CTkToplevel):
    """Janela com buscas prontas: as peças da loja e ideias em ferro e madeira."""

    def __init__(self, app, pagina):
        super().__init__(app)
        self.app, self.pagina = app, pagina
        self.title("Pesquisas rápidas")
        self.configure(fg_color=FUNDO)
        larg, alt = 900, min(700, app.winfo_height() - 40)
        self.geometry(f"{larg}x{alt}+{app.winfo_rootx() + 160}+{app.winfo_rooty() + 30}")
        self.transient(app)
        self.after(150, self.grab_set)
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(2, weight=1)
        cab = ctk.CTkFrame(self, fg_color="transparent")
        cab.grid(row=0, column=0, sticky="ew", padx=22, pady=(18, 6))
        cab.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(cab, text="Pesquisas rápidas", font=F(22, "bold"), text_color=TEXTO, anchor="w").grid(row=0, column=0, sticky="w")
        ctk.CTkLabel(cab, text="Toque numa ideia para buscar no Mercado Livre. Use os botões para juntar o material.",
                     font=F(12), text_color=MUDO, anchor="w").grid(row=1, column=0, sticky="w")
        Botao(cab, "Fechar", "fantasma", width=80, command=self.destroy).grid(row=0, column=1, rowspan=2)
        barra = ctk.CTkFrame(self, fg_color="transparent")
        barra.grid(row=1, column=0, sticky="ew", padx=22, pady=(4, 8))
        barra.grid_columnconfigure(0, weight=1)
        self.filtro = Entrada(barra, placeholder_text="⌕   Filtrar ideias (ex.: vaso, mesa, parede)", height=38, ao_mudar=self.desenhar)
        self.filtro.grid(row=0, column=0, sticky="ew", padx=(0, 10))
        self.extra = ctk.CTkSegmentedButton(barra, values=["Como está", "+ ferro", "+ madeira", "+ ferro e madeira"], height=36,
                                            selected_color=ROXO, selected_hover_color=ROXO_H, font=F(12, "bold"),
                                            unselected_color=PAINEL, unselected_hover_color=SUAVE, fg_color=PAINEL, text_color=TEXTO)
        self.extra.set("Como está")
        self.extra.grid(row=0, column=1)
        self.corpo = ctk.CTkScrollableFrame(self, fg_color="transparent")
        self.corpo.grid(row=2, column=0, sticky="nsew", padx=14, pady=(0, 14))
        self.corpo.grid_columnconfigure(0, weight=1)
        self.desenhar()
        self.after(200, self.filtro.focus_set)

    def _termo(self, t):
        extra = {"+ ferro": " ferro", "+ madeira": " madeira", "+ ferro e madeira": " ferro madeira"}.get(self.extra.get(), "")
        palavras = t.lower().split()
        return t + "".join(" " + e for e in extra.split() if e not in palavras)

    def escolher(self, t):
        termo = self._termo(t)
        self.destroy()
        self.pagina.buscar_termo(termo)

    def desenhar(self):
        for w in self.corpo.winfo_children():
            w.destroy()
        filtro = self.filtro.get().strip().lower()
        grupos = []
        hist = ler_historico()
        if hist:
            grupos.append(("Buscadas por você", "As últimas pesquisas", hist))
        grupos += PESQUISAS
        livre = self.filtro.get().strip()
        r = 0
        if livre:
            b = Botao(self.corpo, f"⌕  Buscar “{self._termo(livre)}”", "ml", height=40, font=F(13, "bold"), anchor="w",
                      command=lambda: self.escolher(livre))
            b.grid(row=r, column=0, sticky="ew", padx=8, pady=(4, 8))
            r += 1
        for titulo, sub, termos in grupos:
            termos = [t for t in termos if not filtro or all(p in t.lower() for p in filtro.split())]
            if not termos:
                continue
            sec = ctk.CTkFrame(self.corpo, fg_color=PAINEL, corner_radius=16, border_width=1, border_color=BORDA)
            sec.grid(row=r, column=0, sticky="ew", padx=8, pady=6)
            r += 1
            ctk.CTkLabel(sec, text=titulo, font=F(15, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", padx=16, pady=(12, 0))
            ctk.CTkLabel(sec, text=sub, font=F(12), text_color=MUDO, anchor="w").pack(fill="x", padx=16)
            chips = ctk.CTkFrame(sec, fg_color="transparent")
            chips.pack(fill="x", padx=12, pady=(8, 12))
            cols = 3
            for c in range(cols):
                chips.grid_columnconfigure(c, weight=1, uniform="p")
            for i, t in enumerate(termos):
                Botao(chips, t, "suave", height=32, font=F(12, "bold"), anchor="w",
                      command=lambda t=t: self.escolher(t)).grid(row=i // cols, column=i % cols, sticky="ew", padx=4, pady=3)
        if r == 0:
            ctk.CTkLabel(self.corpo, text="Nenhuma ideia com esse filtro.", text_color=MUDO, font=F(13)).grid(row=0, column=0, pady=40)


# ------------------------------------------------------------------ página Configurações

class PaginaConfig(ctk.CTkFrame):
    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.grid_columnconfigure(0, weight=1)
        Cabecalho(self, "Configurações", "Onde ficam as fichas, qual site usar e como o navegador trabalha.").grid(
            row=0, column=0, columnspan=2, sticky="ew", padx=24, pady=(20, 12))
        self.grid_rowconfigure(1, weight=1)
        raiz = ctk.CTkScrollableFrame(self, fg_color="transparent")
        raiz.grid(row=1, column=0, columnspan=2, sticky="nsew", padx=(0, 6), pady=(0, 10))
        raiz.grid_columnconfigure((0, 1), weight=1, uniform="cfg")
        nv = ctk.CTkFrame(raiz, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        nv.grid(row=0, column=0, columnspan=2, sticky="new", padx=18, pady=(0, 16))
        nv.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(nv, text="☁  Fichas no FlowManeger", font=F(15, "bold"), text_color=TEXTO, anchor="w").grid(
            row=0, column=0, sticky="w", padx=20, pady=(16, 0))
        self.lbl_nuvem = ctk.CTkLabel(nv, text="", font=F(12), text_color=MUDO, anchor="w", justify="left", wraplength=760)
        self.lbl_nuvem.grid(row=1, column=0, sticky="w", padx=20, pady=(4, 16))
        bn = ctk.CTkFrame(nv, fg_color="transparent")
        bn.grid(row=0, column=1, rowspan=2, sticky="e", padx=20)
        self.bt_conectar = Botao(bn, "Conectar", "primario", width=150, command=app.conectar_nuvem)
        self.bt_sync = Botao(bn, "⟳  Sincronizar agora", "suave", width=170, command=lambda: app.sincronizar(avisar=True))
        self.bt_desc = Botao(bn, "Desconectar", "fantasma", width=120, command=app.desconectar_nuvem)
        self.mostrar_nuvem()
        card = ctk.CTkFrame(raiz, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        card.grid(row=1, column=0, sticky="new", padx=(18, 8))
        card.grid_columnconfigure(1, weight=1)
        self.campos = {}
        r = app.cfg["robo"]

        def campo(row, rotulo, chave, ajuda="", tipo="texto"):
            ctk.CTkLabel(card, text=rotulo, anchor="w", font=F(13, "bold"), text_color=TEXTO).grid(row=row, column=0, sticky="w", padx=20, pady=(16, 0))
            e = Entrada(card, tipo)
            e.definir(r.get(chave, ""))
            e.grid(row=row, column=1, sticky="ew", padx=20, pady=(16, 0))
            self.campos[chave] = e
            if ajuda:
                ctk.CTkLabel(card, text=ajuda, text_color=MUDO, anchor="w", font=F(11)).grid(row=row + 1, column=1, sticky="w", padx=20)

        campo(0, "Site", "site", "Endereço do FlowManeger (ex.: https://flowmaneger.com)")
        campo(2, "Pasta dos produtos", "pasta_produtos", "Uma pasta por produto, com ficha.md e as fotos")
        Botao(card, "Escolher pasta…", "suave", width=130, height=28, font=F(12), command=self.escolher_pasta).grid(row=3, column=1, sticky="e", padx=20)
        campo(4, "Lado máximo da foto (px)", "lado_maximo_foto", "Fotos maiores são reduzidas antes de subir", "inteiro")
        campo(6, "Tamanho máximo da foto (KB)", "tamanho_maximo_foto_kb", "", "inteiro")
        self.mover = ctk.CTkSwitch(card, text="Mover as fichas cadastradas para a pasta “feitos”", progress_color=ROXO, font=F(13), text_color=TEXTO)
        self.mover.grid(row=8, column=0, columnspan=2, sticky="w", padx=20, pady=(18, 0))
        (self.mover.select if motor.sim(r.get("mover_feitos")) else self.mover.deselect)()
        self.visivel = ctk.CTkSwitch(card, text="Mostrar o navegador enquanto cadastra", progress_color=ROXO, font=F(13), text_color=TEXTO)
        self.visivel.grid(row=9, column=0, columnspan=2, sticky="w", padx=20, pady=(10, 0))
        (self.visivel.select if motor.sim(r.get("navegador_visivel")) else self.visivel.deselect)()
        self.grid_columnconfigure(1, weight=1)
        card.grid(row=1, column=0, sticky="new", padx=(18, 8))
        c2 = ctk.CTkFrame(raiz, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        c2.grid(row=1, column=1, sticky="new", padx=(8, 18))
        c2.grid_columnconfigure(1, weight=1)
        ctk.CTkLabel(c2, text="Preço, textos e fotos", font=F(15, "bold"), text_color=TEXTO, anchor="w").grid(
            row=0, column=0, columnspan=2, sticky="w", padx=20, pady=(16, 0))

        def campo2(row, rotulo, chave, ajuda="", tipo="texto", **kw):
            ctk.CTkLabel(c2, text=rotulo, anchor="w", font=F(13, "bold"), text_color=TEXTO).grid(row=row, column=0, sticky="w", padx=20, pady=(14, 0))
            e = Entrada(c2, tipo, **kw)
            e.definir(r.get(chave, ""))
            e.grid(row=row, column=1, sticky="ew", padx=20, pady=(14, 0))
            self.campos[chave] = e
            if ajuda:
                ctk.CTkLabel(c2, text=ajuda, text_color=MUDO, anchor="w", font=F(11), justify="left", wraplength=430).grid(
                    row=row + 1, column=0, columnspan=2, sticky="w", padx=20)

        campo2(1, "Margem de lucro (%)", "margem_padrao", "Usada na calculadora de preço do editor", "inteiro")
        campo2(3, "Sua hora de trabalho (R$)", "valor_hora", "Entra na ficha de custo como mão de obra", "dinheiro")
        campo2(5, "Frete que você paga (R$)", "frete_estimado", "Média por venda acima de R$ 79 (opcional)", "dinheiro")
        campo2(7, "Marca d'água das fotos", "marca_dagua", "Texto no canto das fotos prontas (vazio = sem marca)")
        campo2(9, "Chave do Gemini (IA)", "gemini_chave", "Opcional. Grátis em aistudio.google.com › Get API key. Sem chave, o robô "
               "monta o título e a descrição com os dados da ficha.", show="•")
        campo2(11, "Modelo do Gemini", "gemini_modelo", "")
        campo2(12, "Modelo das imagens (IA)", "gemini_modelo_imagem", "Usado em Fotos › Criar com IA. A conta do Gemini precisa "
               "estar com cobrança ativa para criar imagens.")
        campo2(14, "Link do Canva IA", "canva_link", "Abre em Fotos › Criar no Canva, no seu navegador (entre no Canva lá uma vez).")
        campo2(16, "Pasta de downloads", "pasta_downloads", "Onde o navegador salva o que você baixa. Vazio = Downloads do Windows.")
        ctk.CTkLabel(c2, text="").grid(row=18, column=0, pady=4)
        c3 = ctk.CTkFrame(raiz, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        c3.grid(row=2, column=0, sticky="new", padx=(18, 8), pady=(16, 10))
        c3.grid_columnconfigure(1, weight=1)
        ctk.CTkLabel(c3, text="Automático", font=F(15, "bold"), text_color=TEXTO, anchor="w").grid(
            row=0, column=0, columnspan=2, sticky="w", padx=20, pady=(16, 0))
        for row, rot, chave, tipo, ajuda in ((1, "Preço “de” automático (%)", "preco_de_pct", "inteiro",
                                              "Ex.: 20 = o preço riscado fica 20% acima da venda. Vazio = não preenche."),
                                             (3, "Folga da caixa (cm)", "folga_caixa", "decimal",
                                              "Medida da caixa = medida da peça + essa folga."),
                                             (5, "Prazo padrão sob encomenda (dias)", "prazo_padrao", "inteiro",
                                              "Usado quando a ficha é sob encomenda e não diz o prazo.")):
            ctk.CTkLabel(c3, text=rot, anchor="w", font=F(13, "bold"), text_color=TEXTO).grid(row=row, column=0, sticky="w", padx=20, pady=(12, 0))
            e = Entrada(c3, tipo, width=90)
            e.definir(r.get(chave, ""))
            e.grid(row=row, column=1, sticky="w", padx=20, pady=(12, 0))
            self.campos[chave] = e
            ctk.CTkLabel(c3, text=ajuda, text_color=MUDO, anchor="w", font=F(11)).grid(row=row + 1, column=0, columnspan=2, sticky="w", padx=20)
        self.auto_imp = ctk.CTkSwitch(c3, text="Completar a ficha sozinho ao importar do Mercado Livre", progress_color=ROXO, font=F(13), text_color=TEXTO)
        self.auto_imp.grid(row=7, column=0, columnspan=2, sticky="w", padx=20, pady=(12, 0))
        Botao(c3, "🎨  Minhas cores (ferragem, corano, madeira)", "suave", width=300,
              command=lambda: EscolherCores(app)).grid(row=8, column=0, columnspan=2, sticky="w", padx=20, pady=(14, 0))
        ctk.CTkLabel(c3, text="Tamanho da tela", anchor="w", font=F(13, "bold"), text_color=TEXTO).grid(row=9, column=0, sticky="w", padx=20, pady=(14, 0))
        self.tam_tela = _seg(c3, list(TAMANHOS_TELA))
        atual = next((k for k, v in TAMANHOS_TELA.items() if v == r.get("tamanho_tela")), "Automático")
        self.tam_tela.set(atual)
        self.tam_tela.grid(row=9, column=1, sticky="w", padx=20, pady=(14, 0))
        ctk.CTkLabel(c3, text="Letras e botões maiores ou menores. Automático = pelo seu monitor. Vale ao abrir o robô de novo.",
                     text_color=MUDO, anchor="w", font=F(11), justify="left", wraplength=420).grid(row=10, column=0, columnspan=2, sticky="w", padx=20, pady=(2, 18))
        (self.auto_imp.select if motor.sim(r.get("completar_ao_importar", "sim")) else self.auto_imp.deselect)()
        c4 = ctk.CTkFrame(raiz, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        c4.grid(row=2, column=1, sticky="new", padx=(8, 18), pady=(16, 10))
        c4.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(c4, text="Atualização e cópias", font=F(15, "bold"), text_color=TEXTO, anchor="w").grid(
            row=0, column=0, columnspan=2, sticky="w", padx=20, pady=(16, 0))
        self.lbl_versao = ctk.CTkLabel(c4, text=f"Versão {recursos.versao_local().get('versao') or '—'} instalada", font=F(13),
                                       text_color=MUDO, anchor="w")
        self.lbl_versao.grid(row=1, column=0, sticky="w", padx=20, pady=(8, 0))
        self.bt_versao = Botao(c4, "⟳  Atualizar robô", "primario", width=160, command=lambda: app.procurar_atualizacao())
        self.bt_versao.grid(row=1, column=1, sticky="e", padx=20, pady=(8, 0))
        self.auto_atu = ctk.CTkSwitch(c4, text="Procurar versão nova sozinho ao abrir", progress_color=ROXO, font=F(13), text_color=TEXTO)
        self.auto_atu.grid(row=2, column=0, columnspan=2, sticky="w", padx=20, pady=(12, 0))
        (self.auto_atu.select if motor.sim(r.get("atualizar_sozinho", "sim")) else self.auto_atu.deselect)()
        self.copia = ctk.CTkSwitch(c4, text="Fazer cópia das fichas todo dia", progress_color=ROXO, font=F(13), text_color=TEXTO)
        self.copia.grid(row=3, column=0, columnspan=2, sticky="w", padx=20, pady=(12, 0))
        (self.copia.select if motor.sim(r.get("copia_diaria", "sim")) else self.copia.deselect)()
        ctk.CTkLabel(c4, text="Pasta das cópias", anchor="w", font=F(13, "bold"), text_color=TEXTO).grid(row=4, column=0, sticky="w", padx=20, pady=(12, 0))
        lc = ctk.CTkFrame(c4, fg_color="transparent")
        lc.grid(row=5, column=0, columnspan=2, sticky="ew", padx=20)
        lc.grid_columnconfigure(0, weight=1)
        self.campos["pasta_copias"] = Entrada(lc, placeholder_text=str(motor.BASE / "copias"))
        self.campos["pasta_copias"].definir(r.get("pasta_copias") or None)
        self.campos["pasta_copias"].grid(row=0, column=0, sticky="ew")
        Botao(lc, "Abrir", "fantasma", width=70, command=lambda: abrir_no_sistema(Path(self.campos["pasta_copias"].get().strip() or motor.BASE / "copias"))).grid(row=0, column=1, padx=(6, 0))
        ctk.CTkLabel(c4, text="Guarda as últimas 14 cópias. Dica: escolha uma pasta do OneDrive ou Google Drive para ter cópia fora do PC.",
                     text_color=MUDO, anchor="w", justify="left", wraplength=420, font=F(11)).grid(row=6, column=0, columnspan=2, sticky="w", padx=20, pady=(4, 18))
        bts = ctk.CTkFrame(card, fg_color="transparent")
        bts.grid(row=10, column=0, columnspan=2, sticky="e", padx=20, pady=20)
        Botao(bts, "Fechar o navegador do robô", "fantasma", width=200, command=lambda: app.tarefas.put(("fechar_site", None))).pack(side="left", padx=6)
        Botao(bts, "Salvar", "primario", width=140, command=self.salvar).pack(side="left")

    def mostrar_nuvem(self):
        conta = motor.ler_conta()
        for b in (self.bt_conectar, self.bt_sync, self.bt_desc):
            b.pack_forget()
        if conta:
            quem = f" na conta de {conta['usuario']}" if conta.get("usuario") else ""
            self.lbl_nuvem.configure(text=f"Conectado{quem} ({conta.get('site')}). Cada ficha e as fotos dela ficam salvas no banco do site. "
                                          "O robô sincroniza sozinho quando você salva e a cada 3 minutos; em outro PC, conecte e ele traz tudo. "
                                          "Nada é apagado: o que sai vai para a lixeira.")
            self.bt_sync.pack(side="left", padx=(0, 6))
            self.bt_desc.pack(side="left")
        else:
            self.lbl_nuvem.configure(text="As fichas estão só neste PC. Conecte para guardar todas as fichas e fotos no banco do FlowManeger "
                                          "(sem tela nova no site). O robô continua aqui e traz as fichas de lá em qualquer PC.")
            self.bt_conectar.pack(side="left")

    def mostrar_versao(self, nova):
        atual = recursos.versao_local().get("versao") or "—"
        self.lbl_versao.configure(text=f"Versão {atual} instalada" + (f"  ·  versão {nova.get('versao')} disponível" if nova else "  ·  em dia"),
                                  text_color=ROXO_TXT if nova else MUDO)

    def escolher_pasta(self):
        p = filedialog.askdirectory(parent=self, title="Pasta dos produtos")
        if p:
            self.campos["pasta_produtos"].definir(p)

    def salvar(self):
        r = self.app.cfg["robo"]
        site = self.campos["site"].get().strip().rstrip("/")
        if site and not site.startswith("http"):
            site = "https://" + site
        r["site"] = site or motor.PADRAO["site"]
        r["pasta_produtos"] = self.campos["pasta_produtos"].get().strip() or motor.PADRAO["pasta_produtos"]
        r["lado_maximo_foto"] = str(self.campos["lado_maximo_foto"].valor() or 1600)
        r["tamanho_maximo_foto_kb"] = str(self.campos["tamanho_maximo_foto_kb"].valor() or 1500)
        r["mover_feitos"] = "sim" if self.mover.get() else "nao"
        r["navegador_visivel"] = "sim" if self.visivel.get() else "nao"
        r["margem_padrao"] = str(self.campos["margem_padrao"].valor() or 30)
        for chave in ("valor_hora", "frete_estimado"):
            v = self.campos[chave].valor()
            r[chave] = f"{v:.2f}" if v else ""
        r["marca_dagua"] = self.campos["marca_dagua"].get().strip()
        r["gemini_chave"] = self.campos["gemini_chave"].get().strip()
        r["gemini_modelo"] = self.campos["gemini_modelo"].get().strip() or "gemini-2.5-flash"
        r["gemini_modelo_imagem"] = self.campos["gemini_modelo_imagem"].get().strip() or "gemini-3.1-flash-image"
        r["canva_link"] = self.campos["canva_link"].get().strip() or "https://www.canva.com/ai"
        r["pasta_downloads"] = self.campos["pasta_downloads"].get().strip()
        r["preco_de_pct"] = str(self.campos["preco_de_pct"].valor() or "")
        r["folga_caixa"] = decimal_txt(self.campos["folga_caixa"].valor() or 4).replace(",", ".")
        r["completar_ao_importar"] = "sim" if self.auto_imp.get() else "nao"
        r["prazo_padrao"] = str(self.campos["prazo_padrao"].valor() or 15)
        r["atualizar_sozinho"] = "sim" if self.auto_atu.get() else "nao"
        r["copia_diaria"] = "sim" if self.copia.get() else "nao"
        r["pasta_copias"] = self.campos["pasta_copias"].get().strip()
        r["tamanho_tela"] = TAMANHOS_TELA.get(self.tam_tela.get(), "auto")
        motor.salvar_config(self.app.cfg)
        if self.app.site and not self.app.rodando:
            self.app.tarefas.put(("fechar_site", None))
        self.app.recarregar()
        self.app.toast("Configurações salvas")


# ------------------------------------------------------------------ editor de ficha

class Secao(ctk.CTkFrame):
    def __init__(self, master, titulo, sub=""):
        super().__init__(master, fg_color=PAINEL, corner_radius=22, border_width=1, border_color=VIDRO_BORDA)
        self.titulo = titulo
        self.grid_columnconfigure((1, 3), weight=1)
        cab = ctk.CTkFrame(self, fg_color="transparent")
        cab.grid(row=0, column=0, columnspan=4, sticky="ew", padx=20, pady=(16, 6))
        cab.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(cab, text=titulo, font=F(16, "bold"), text_color=TEXTO, anchor="w").grid(row=0, column=0, sticky="w")
        if sub:
            ctk.CTkLabel(cab, text=sub, font=F(12), text_color=MUDO, anchor="w").grid(row=1, column=0, sticky="w")
        self.acoes = ctk.CTkFrame(cab, fg_color="transparent", width=1, height=1)
        self.acoes.grid(row=0, column=1, rowspan=2, sticky="e")
        self.linha = 1

    def campo(self, rotulo, widget, col=0, span=1, ajuda=None):
        """Coloca rótulo + campo. col 0 = esquerda, col 2 = direita."""
        ctk.CTkLabel(self, text=rotulo, font=F(12, "bold"), text_color=MUDO, anchor="w").grid(
            row=self.linha, column=col, sticky="w", padx=(20, 8), pady=(8, 0))
        widget.grid(row=self.linha, column=col + 1, columnspan=span, sticky="ew", padx=(0, 20), pady=(8, 0))
        if ajuda is not None:
            ajuda.grid(row=self.linha + 1, column=col + 1, columnspan=span, sticky="w", padx=(0, 20))
        if col + 1 + span >= 4 or span > 1:
            self.linha += 2 if ajuda is not None else 1
        return widget

    def fim_linha(self):
        self.linha += 1


class LinhaVariacao(ctk.CTkFrame):
    """Uma variação no editor, como um cartão: foto, nome, preços, estoque com − e +, kit, medidas e o pedido do Canva."""

    def __init__(self, master, editor, v):
        super().__init__(master, fg_color=VIDRO, corner_radius=18, border_width=1, border_color=VIDRO_BORDA)
        self.editor = editor
        self.original = v
        self.aberta = False
        emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
        med = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
        self.grid_columnconfigure(1, weight=1)
        self.ativa = v.get("ativa", True) is not False

        # foto da variação
        self.foto = ctk.CTkLabel(self, text="sem\nfoto", width=64, height=64, corner_radius=14, fg_color=SUAVE,
                                 text_color=MUDO, font=F(10))
        self.foto.grid(row=0, column=0, rowspan=2, padx=(12, 10), pady=12, sticky="n")

        # linha 1: nome, código, selos e ações
        l1 = ctk.CTkFrame(self, fg_color="transparent")
        l1.grid(row=0, column=1, sticky="ew", padx=(0, 12), pady=(12, 0))
        l1.grid_columnconfigure(0, weight=0)
        l1.grid_columnconfigure(1, weight=1)
        self.marca = ctk.CTkCheckBox(l1, text="", width=24, checkbox_width=22, checkbox_height=22, fg_color=ROXO, hover_color=ROXO_H,
                                     command=lambda: editor._contar_marcadas())
        self.marca.grid(row=0, column=0, padx=(0, 6))
        self.valor = Entrada(l1, height=34, font=F(14, "bold"), placeholder_text="nome da variação (ex.: 30 cm · Preto · Kit com 2)",
                             fg_color=PAINEL, border_color=VIDRO_BORDA)
        self.valor.grid(row=0, column=1, sticky="ew")
        self.valor.bind("<FocusOut>", lambda e: (self._selos(), editor.atualizar_usos()), add="+")
        self.liga = ctk.CTkSwitch(self, text="Sobe", width=60, switch_width=36, switch_height=20, progress_color=VERDE,
                                  font=F(11, "bold"), text_color=MUDO, command=self._ligou)
        self.liga.grid(row=2, column=0, sticky="nw", padx=(14, 0), pady=(0, 10))
        self.codigo = Entrada(l1, width=124, height=34, font=F(12), placeholder_text="código automático", fg_color=PAINEL,
                              border_color=VIDRO_BORDA)
        self.codigo.grid(row=0, column=2, padx=(6, 0))
        self.selo_kit = ctk.CTkLabel(l1, text="", font=F(11, "bold"), corner_radius=10, height=24, fg_color=ROXO_CLARO,
                                     text_color=ROXO_TXT)
        self.margem = ctk.CTkLabel(l1, text="—", width=62, height=28, corner_radius=14, font=F(12, "bold"), fg_color=SUAVE,
                                   text_color=MUDO)
        self.margem.grid(row=0, column=4, padx=(8, 0))
        pb = dict(width=34, height=32, font=F(13))
        dica(Botao(l1, "✦", "suave", command=self.canva, **pb), "Criar as imagens desta variação no Canva").grid(row=0, column=5, padx=(8, 0))
        dica(Botao(l1, "⧉", "fantasma", command=lambda: editor.duplicar_variacao(self), **pb), "Duplicar").grid(row=0, column=6, padx=(4, 0))
        dica(Botao(l1, "✕", "perigo", command=lambda: editor.remover_variacao(self), **pb), "Remover").grid(row=0, column=7, padx=(4, 0))

        # linha 2: preços, estoque, kit e o botão de detalhes
        l2 = ctk.CTkFrame(self, fg_color="transparent")
        l2.grid(row=1, column=1, sticky="ew", padx=(0, 12), pady=(8, 4))

        def campo(rot, w, padx=(0, 10)):
            caixa = ctk.CTkFrame(l2, fg_color="transparent")
            caixa.pack(side="left", padx=padx)
            ctk.CTkLabel(caixa, text=rot, font=F(10, "bold"), text_color=MUDO, anchor="w", height=14).pack(anchor="w")
            return caixa
        c = campo("CUSTO R$", None)
        cc = ctk.CTkFrame(c, fg_color="transparent")
        cc.pack()
        self.custo = Entrada(cc, "dinheiro", width=86, ao_mudar=self.calc)
        self.custo.pack(side="left")
        self.itens_custo = [dict(i) for i in v.get("ficha_de_custo") or [] if isinstance(i, dict)]
        self.bt_custo = dica(Botao(cc, "▦", "suave", width=30, height=30, font=F(12, "bold"), command=self.montar_custo),
                             "Montar o custo só desta variação pela tabela de materiais")
        self.bt_custo.pack(side="left", padx=(3, 0))
        c = campo("VENDA R$", None)
        self.venda = Entrada(c, "dinheiro", width=86, ao_mudar=self.calc, font=F(13, "bold"))
        self.venda.pack()
        self.lbl_un = ctk.CTkLabel(c, text="", font=F(10), text_color=MUDO, height=12)
        c = campo("PREÇO “DE”", None)
        self.de = Entrada(c, "dinheiro", width=86)
        self.de.pack()
        c = campo("ESTOQUE", None)
        ctrl = ctk.CTkFrame(c, fg_color=PAINEL, corner_radius=10, border_width=1, border_color=VIDRO_BORDA)
        ctrl.pack()
        Botao(ctrl, "−", "fantasma", width=30, height=30, border_width=0, command=lambda: self.somar(-1)).pack(side="left", padx=(2, 0), pady=2)
        self.estoque = Entrada(ctrl, "inteiro", width=48, height=30, border_width=0, justify="center", placeholder_text="0")
        self.estoque.pack(side="left")
        Botao(ctrl, "+", "fantasma", width=30, height=30, border_width=0, command=lambda: self.somar(1)).pack(side="left", padx=(0, 2), pady=2)
        c = campo("KIT (UN.)", None)
        kk = ctk.CTkFrame(c, fg_color="transparent")
        kk.pack()
        self.kit = Entrada(kk, "inteiro", width=60, placeholder_text="1")
        self.kit.pack(side="left")
        self.kit.bind("<FocusOut>", lambda e: (self._selos(), self.calc()), add="+")
        self.bt_kit = dica(Botao(kk, "× kit", "suave", width=50, height=30, font=F(11, "bold"), command=self.calcular_kit),
                           "Calcula custo, preço e peso do kit pelo preço de 1 unidade (a variação igual sem kit)")

        # linha 3: detalhes + cores desta variação
        l3x = ctk.CTkFrame(self, fg_color="transparent")
        l3x.grid(row=2, column=1, sticky="ew", padx=(0, 12), pady=(0, 10))
        self.bt_det = Botao(l3x, "▸  Medidas, caixa e pedido do Canva", "fantasma", height=26, font=F(12), border_width=0,
                            text_color=ROXO_TXT, anchor="w", command=self.alternar)
        self.bt_det.pack(side="left")
        self.caixa_cores = ctk.CTkFrame(l3x, fg_color="transparent")
        self.caixa_cores.pack(side="right")

        # detalhes (abre e fecha)
        self.det = ctk.CTkFrame(self, fg_color=SUAVE, corner_radius=14)
        l3 = ctk.CTkFrame(self.det, fg_color="transparent")
        l3.pack(fill="x", padx=10, pady=(10, 4))

        def peq(pai, rot, tipo="decimal", larg=58, ph="cm"):
            ctk.CTkLabel(pai, text=rot, font=F(11), text_color=MUDO).pack(side="left", padx=(6, 3))
            e = Entrada(pai, tipo, width=larg, height=30, font=F(12), placeholder_text=ph)
            e.pack(side="left")
            return e
        ctk.CTkLabel(l3, text="Caixa", font=F(12, "bold"), text_color=TEXTO).pack(side="left")
        self.peso = peq(l3, "peso", larg=60, ph="kg")
        self.cc = peq(l3, "comp.")
        self.cl = peq(l3, "larg.")
        self.ca = peq(l3, "alt.")
        l3b = ctk.CTkFrame(self.det, fg_color="transparent")
        l3b.pack(fill="x", padx=10, pady=(4, 4))
        ctk.CTkLabel(l3b, text="Peça ", font=F(12, "bold"), text_color=TEXTO).pack(side="left")
        l3 = l3b
        self.pa = peq(l3, "alt.")
        self.pl = peq(l3, "larg.")
        self.pp = peq(l3, "prof.")
        self.pd = peq(l3, "Ø")
        l4 = ctk.CTkFrame(self.det, fg_color="transparent")
        l4.pack(fill="x", padx=10, pady=(4, 10))
        ctk.CTkLabel(l4, text="Pedido para o Canva", font=F(12, "bold"), text_color=TEXTO).pack(side="left")
        self.canva_extra = Entrada(l4, height=30, font=F(12),
                                   placeholder_text="o que muda nesta variação (ex.: troque o desenho do boi por um porco)")
        self.canva_extra.pack(side="left", fill="x", expand=True, padx=(8, 0))

        self.valor.definir(v.get("valor"))
        self.codigo.definir(v.get("codigo"))
        self.custo.definir(v.get("custo"))
        self.venda.definir(v.get("venda"))
        self.de.definir(v.get("preco_tabela"))
        self.estoque.definir(v.get("estoque") if v.get("estoque") not in (None, "") else 0)
        self.kit.definir(v.get("kit") if (motor.numero(v.get("kit")) or 0) > 1 else None)
        self.canva_extra.definir(v.get("canva_extra"))
        self.peso.definir(emb.get("peso_kg"))
        self.cc.definir(emb.get("comprimento_cm"))
        self.cl.definir(emb.get("largura_cm"))
        self.ca.definir(emb.get("altura_cm"))
        self.pa.definir(med.get("altura"))
        self.pl.definir(med.get("largura"))
        self.pp.definir(med.get("profundidade"))
        self.pd.definir(med.get("diametro"))
        for w in (self.peso, self.cc, self.cl, self.ca, self.pa, self.pl, self.pp, self.pd, self.canva_extra):
            w.bind("<FocusIn>", lambda e: self.abrir(), add="+")
        if self.ativa:
            self.liga.select()
        self.cores = dict(v.get("cores") or {}) if isinstance(v.get("cores"), dict) else {}
        if not self.cores:
            self.cores = recursos.cores_do_nome(self._parte_cor() or "")
        self._desenhar_cores()
        self._pintar_custo()
        self._selos()
        self._pintar()
        self.calc()

    # ações
    def _ligou(self):
        self.ativa = bool(self.liga.get())
        self._pintar()
        self.editor._resumo_var()

    def ligar(self, sim):
        (self.liga.select if sim else self.liga.deselect)()
        self._ligou()

    def _pintar(self):
        """Desligada: cartão apagado, para ver na hora o que não vai subir."""
        if self.ativa:
            self.configure(fg_color=VIDRO, border_color=VIDRO_BORDA)
            self.valor.configure(text_color=TEXTO)
            self.liga.configure(text="Sobe", text_color=VERDE)
        else:
            self.configure(fg_color=("#eceef4", "#121726"), border_color=BORDA)
            self.valor.configure(text_color=MUDO)
            self.liga.configure(text="Não sobe", text_color=MUDO)

    def rotulo(self):
        return self.valor.get().strip() or f"variação {self.editor.linhas.index(self) + 1}"

    def somar(self, n):
        self.estoque.definir(max(0, (self.estoque.valor() or 0) + n))
        self.editor._resumo_var()

    def abrir(self):
        if not self.aberta:
            self.alternar()

    def alternar(self, abrir=None):
        self.aberta = (not self.aberta) if abrir is None else abrir
        if self.aberta:
            self.det.grid(row=3, column=0, columnspan=2, sticky="ew", padx=12, pady=(0, 12))
            self.bt_det.configure(text="▾  Medidas, caixa e pedido do Canva")
        else:
            self.det.grid_forget()
            self.bt_det.configure(text="▸  Medidas, caixa e pedido do Canva")

    def canva(self):
        ImagensIA(self.editor, "canva", linha=self)

    def _selos(self):
        n = self.kit.valor() or 1
        if n > 1:
            self.selo_kit.configure(text=f"  kit {n} un.  ")
            self.selo_kit.grid(row=0, column=3, padx=(8, 0))
            self.bt_kit.pack(side="left", padx=(4, 0))
        else:
            self.selo_kit.grid_forget()
            self.bt_kit.pack_forget()

    # cores (ferragem, corano, madeira) desta variação
    def _parte_cor(self):
        partes = [x.strip() for x in self.valor.get().split("·") if x.strip()]
        partes = [x for x in partes if not re.match(r"(?i)kit\b", x)]
        if len(partes) > 1:
            return partes[-1]
        if len(partes) == 1 and recursos.cores_do_nome(partes[0]):
            return partes[0]
        return None

    def _desenhar_cores(self):
        for w in self.caixa_cores.winfo_children():
            w.destroy()
        dados = recursos.ler_cores()
        nomes = {g["chave"]: g for g in dados["grupos"]}
        for chave, nome in self.cores.items():
            g = nomes.get(chave)
            chip = ctk.CTkFrame(self.caixa_cores, fg_color=PAINEL, corner_radius=12, border_width=1, border_color=VIDRO_BORDA)
            chip.pack(side="left", padx=(0, 6))
            ctk.CTkFrame(chip, width=14, height=14, corner_radius=7, fg_color=recursos.hex_da_cor(chave, nome, dados) or "#888888",
                         border_width=1, border_color=BORDA).pack(side="left", padx=(8, 5), pady=5)
            rot = ((g or {}).get("prefixo") or "ferragem").capitalize() if g else chave
            ctk.CTkLabel(chip, text=f"{rot}: {nome}", font=F(11), text_color=TEXTO).pack(side="left", padx=(0, 9))
        dica(Botao(self.caixa_cores, "🎨  Cores" if not self.cores else "🎨", "suave", width=34 if self.cores else 80, height=26,
                   font=F(11, "bold"), command=self.escolher_cores), "Escolher a ferragem, o corano ou a madeira desta variação").pack(side="left")

    def escolher_cores(self):
        def usar(comb, marc):
            self.cores = {k: v[0] for k, v in marc.items() if v}
            nome, canva = comb[0]
            partes = [x.strip() for x in self.valor.get().split("·") if x.strip()]
            kits = [x for x in partes if re.match(r"(?i)kit\b", x)]
            resto = [x for x in partes if x not in kits]
            antiga = self._parte_cor()
            if antiga and antiga in resto:
                resto[resto.index(antiga)] = nome
            else:
                resto.append(nome)
            self.valor.definir(" · ".join(resto + kits))
            ant_canva = self.canva_extra.valor() or ""
            if not ant_canva or ant_canva.startswith(("Estrutura de ferro", "Assento estofado", "Madeira no tom")):
                self.canva_extra.definir(canva)
            self._desenhar_cores()
            self.editor.atualizar_usos()
            if self.editor.var_por.get() in ("Sem variação", "Tamanho") and len(resto) > 1:
                self.editor.var_por.set("Tamanho e cor")
        EscolherCores(self.editor.app, usar, marcadas={k: [v] for k, v in self.cores.items()}, unica=True)

    def montar_custo(self):
        CustoVariacao(self)

    def _pintar_custo(self):
        n = len(self.itens_custo)
        self.bt_custo.configure(text=f"▦ {n}" if n else "▦", width=44 if n else 30)

    def calcular_kit(self):
        k = self.kit.valor() or 1
        if k < 2:
            self.editor.app.toast("Ponha a quantidade em KIT (UN.) primeiro", AMBAR)
            return
        base_nome = re.sub(r"(?i)\s*·\s*kit\b.*$", "", self.valor.get().strip()).strip().lower()
        base = next((l for l in self.editor.linhas if l is not self and (l.kit.valor() or 1) == 1
                     and l.valor.get().strip().lower() == base_nome), None)
        base = base or next((l for l in self.editor.linhas if l is not self and (l.kit.valor() or 1) == 1 and l.venda.valor()), None)
        if base is None or not base.venda.valor():
            self.editor.app.toast("Não achei a variação de 1 unidade com preço", AMBAR)
            return
        desc = min(max(self.editor.g_desc.valor() or 0, 0), 60) / 100
        self.venda.definir(round(base.venda.valor() * k * (1 - desc), 2))
        if base.custo.valor():
            self.custo.definir(round(base.custo.valor() * k, 2))
        if base.peso.valor():
            self.peso.definir(round(base.peso.valor() * k, 2))
        self.calc()
        self.editor.app.toast(f"Kit de {k} calculado pelo preço de “{base.rotulo()}”" + (f" com {desc * 100:.0f}% de desconto" if desc else ""))

    def mostrar_foto(self, caminho):
        img = miniatura(caminho, 64, 14) if caminho else None
        if img:
            self.editor.app._refs.append(img)
            self.foto.configure(image=img, text="", fg_color="transparent")
        else:
            self.foto.configure(image=None, text="sem\nfoto", fg_color=SUAVE)

    def calc(self):
        venda = self.venda.valor()
        custo = self.custo.valor()
        k = self.kit.valor() or 1
        if k > 1 and venda:
            self.lbl_un.configure(text=f"R$ {reais(venda / k)} por un.")
            self.lbl_un.pack(anchor="w")
        else:
            self.lbl_un.pack_forget()
        if custo is None:
            custo = self.editor.soma_custo()
        if venda and custo:
            m = (venda - custo) / venda * 100
            cor = VERDE if m >= 30 else AMBAR if m >= 10 else VERMELHO
            self.margem.configure(text=f"{m:.0f}%", text_color="white", fg_color=cor)
        else:
            self.margem.configure(text="—", text_color=MUDO, fg_color=SUAVE)
        if hasattr(self.editor, "lbl_conta"):
            self.editor._conta()
        if hasattr(self.editor, "lbl_resumo_var"):
            self.editor._resumo_var()

    def dados(self):
        v = dict(self.original)
        v["valor"] = self.valor.valor() or None
        v["codigo"] = self.codigo.valor()
        v["custo"] = self.custo.valor()
        v["venda"] = self.venda.valor()
        v["preco_tabela"] = self.de.valor()
        v["estoque"] = self.estoque.valor() or 0
        if self.ativa:
            v.pop("ativa", None)
        else:
            v["ativa"] = False
        if self.itens_custo:
            v["ficha_de_custo"] = [dict(i) for i in self.itens_custo]
        else:
            v.pop("ficha_de_custo", None)
        if self.cores:
            v["cores"] = dict(self.cores)
        else:
            v.pop("cores", None)
        kit = self.kit.valor() or 1
        if kit > 1:
            v["kit"] = kit
        else:
            v.pop("kit", None)
        extra = self.canva_extra.valor()
        if extra:
            v["canva_extra"] = extra
        else:
            v.pop("canva_extra", None)
        emb = dict(self.original.get("embalagem") or {}) if isinstance(self.original.get("embalagem"), dict) else {}
        emb.update({"peso_kg": self.peso.valor(), "comprimento_cm": self.cc.valor(), "largura_cm": self.cl.valor(), "altura_cm": self.ca.valor()})
        v["embalagem"] = {k: x for k, x in emb.items() if x is not None}
        med = dict(self.original.get("medidas_peca_cm") or {}) if isinstance(self.original.get("medidas_peca_cm"), dict) else {}
        med.update({"altura": self.pa.valor(), "largura": self.pl.valor(), "profundidade": self.pp.valor(), "diametro": self.pd.valor()})
        v["medidas_peca_cm"] = {k: x for k, x in med.items() if x is not None}
        return v


class FotoEd:
    """Uma foto no editor: arquivo da pasta ou escolhido agora."""

    def __init__(self, caminho, nova=False, uso="todas"):
        self.caminho = Path(caminho)
        self.nova = nova
        self.uso = uso          # "todas", "nao", "cor:X", a LinhaVariacao ou uma tupla de LinhaVariacao (várias)
        self.removida = False

    def de(self, linha):
        """A foto vale para esta variação (sozinha ou junto com outras escolhidas)?"""
        return self.uso is linha or (isinstance(self.uso, tuple) and linha in self.uso)


class Editor(ctk.CTkFrame):
    VARIACAO_POR = ["Sem variação", "Tamanho", "Cor", "Tamanho e cor"]
    CHAVE_SECAO = {"Produto": "produto", "Preço, estoque e prazo": "precos", "Custo e preço sugerido": "custo", "Variações": "variacoes",
                   "Fotos": "fotos", "Ficha técnica": "tecnica", "Mercado Livre": "ml"}
    CURTOS = {"Preço, estoque e prazo": "Preço", "Custo e preço sugerido": "Custo", "Ficha técnica": "Técnica", "Mercado Livre": "Anúncio"}

    def __init__(self, master, app, ficha=None):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.ficha = ficha
        self.dados = json.loads(json.dumps(ficha.dados if ficha else motor.MODELO_VAZIO))
        if not self.dados.get("variacoes"):
            self.dados["variacoes"] = json.loads(json.dumps(motor.MODELO_VAZIO["variacoes"]))
        self.linhas = []
        self.fotos = []
        self.tmp = Path(tempfile.mkdtemp(prefix="robo-stylo-ed-"))
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(2, weight=1)

        cab = ctk.CTkFrame(self, fg_color="transparent")
        cab.grid(row=0, column=0, columnspan=2, sticky="ew", padx=24, pady=(18, 10))
        cab.grid_columnconfigure(1, weight=1)
        Botao(cab, "◀  Voltar", "fantasma", width=96, command=self.cancelar).grid(row=0, column=0, rowspan=2, padx=(0, 14))
        tit = ctk.CTkFrame(cab, fg_color="transparent")
        tit.grid(row=0, column=1, sticky="w")
        ctk.CTkLabel(tit, text="Nova ficha" if ficha is None else "Editar ficha", font=F(24, "bold"), text_color=TEXTO, anchor="w").pack(side="left")
        self.chip_salvo = ctk.CTkLabel(tit, text="", font=F(11, "bold"), corner_radius=10, height=24, padx=10)
        self.chip_salvo.pack(side="left", padx=12)
        self.lbl_sub = ctk.CTkLabel(cab, text=ficha.pasta.name if ficha else "A pasta é criada quando você salvar.", font=F(12), text_color=MUDO, anchor="w")
        self.lbl_sub.grid(row=1, column=1, sticky="w")
        self.lbl_erro = ctk.CTkLabel(cab, text="", font=F(12, "bold"), text_color=VERMELHO)
        self.lbl_erro.grid(row=0, column=2, rowspan=2, padx=10)
        dica(Botao(cab, "✦  Completar tudo", "suave", width=150, command=self.completar_tudo),
             "Preenche o que está vazio e resolve os avisos que dá: código, categoria, caixa, peso, preço, prazo, título e descrição").grid(
            row=0, column=3, rowspan=2, padx=4)
        dica(Botao(cab, "Salvar ficha", "primario", width=140, height=40, font=F(14, "bold"), command=self.salvar),
             "Ctrl+S").grid(row=0, column=6, rowspan=2, padx=(4, 0))

        self.corpo = ctk.CTkScrollableFrame(self, fg_color="transparent")
        self.corpo.grid(row=2, column=0, sticky="nsew", padx=(14, 0), pady=(0, 10))
        self.corpo.grid_columnconfigure(0, weight=1)
        self.grid_columnconfigure(1, weight=0)
        self.painel = ctk.CTkScrollableFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA, width=262)
        self.painel.grid(row=1, column=1, rowspan=2, sticky="nsew", padx=(6, 22), pady=(0, 16))
        self.painel.grid_columnconfigure(0, weight=1)
        self._produto()
        self._precos()
        self._custo()
        self._variacoes()
        self._fotos()
        self._tecnica()
        self._mercado_livre()
        nav = ctk.CTkFrame(self, fg_color="transparent")
        nav.grid(row=1, column=0, sticky="ew", padx=24, pady=(0, 8))
        secoes = sorted((w for w in self.corpo.winfo_children() if isinstance(w, Secao)), key=lambda w: int(w.grid_info()["row"]))
        self.secoes, self.chips = {}, {}
        for sec in secoes:
            chave = self.CHAVE_SECAO.get(sec.titulo, sec.titulo)
            self.secoes[chave] = sec
            b = ctk.CTkButton(nav, text=self.CURTOS.get(sec.titulo, sec.titulo), height=28, width=40, corner_radius=14, fg_color=PAINEL,
                              border_width=1, border_color=BORDA, hover_color=ROXO_CLARO, text_color=TEXTO, font=F(12, "bold"),
                              command=lambda s=sec: self.ir_secao(s))
            b.pack(side="left", padx=(0, 6))
            self.chips[chave] = b
        self._inicial = None
        self._ultima = None
        self.after(400, self._comecar)

    # seções
    def _produto(self):
        d = self.dados
        s = Secao(self.corpo, "Produto", "Nome sem o tamanho e sem a cor: o site junta com a variação.")
        s.grid(row=0, column=0, sticky="ew", padx=10, pady=8)
        self.nome = s.campo("Nome *", Entrada(s, placeholder_text="ex.: Mão Francesa Colonial de Ferro"), 0, 3)
        self.nome.definir(d.get("nome"))
        self.codigo = s.campo("Código base", Entrada(s, placeholder_text="ex.: MFC"), 0)
        self.codigo.definir(d.get("codigo"))
        self.categoria = s.campo("Categoria", ctk.CTkComboBox(s, values=CATEGORIAS, height=34, corner_radius=9, border_color=BORDA,
                                                            fg_color=PAINEL, button_color=ROXO_CLARO, text_color=TEXTO,
                                                            font=F(13), dropdown_font=F(12)), 2)
        self.categoria.set(str(d.get("categoria") or ""))
        s.fim_linha()
        self.marca = s.campo("Marca", Entrada(s), 0)
        self.marca.definir(d.get("marca") or "Móveis Stylo")
        self.modelo = s.campo("Modelo", Entrada(s, placeholder_text="opcional"), 2)
        self.modelo.definir(d.get("modelo"))
        s.fim_linha()
        self.material = s.campo("Material", Entrada(s, placeholder_text="ex.: Ferro"), 0)
        self.material.definir(d.get("material"))
        self.acabamento = s.campo("Acabamento", Entrada(s, placeholder_text="ex.: Pintura eletrostática"), 2)
        self.acabamento.definir(d.get("acabamento"))
        s.fim_linha()
        self.montagem = s.campo("Montagem", ctk.CTkComboBox(s, values=["Vem montado", "Vem desmontado", "Fácil montagem"], height=34,
                                                          corner_radius=9, border_color=BORDA, fg_color=PAINEL, button_color=ROXO_CLARO,
                                                          text_color=TEXTO, font=F(13), dropdown_font=F(12)), 0)
        self.montagem.set(str(d.get("montagem") or ""))
        self.caixa = s.campo("Vem na caixa", Entrada(s, placeholder_text="ex.: 1 mão francesa"), 2)
        self.caixa.definir(d.get("o_que_vem_na_caixa"))
        s.fim_linha()
        self.carga = s.campo("Aguenta (kg)", Entrada(s, "decimal", placeholder_text="opcional"), 0)
        self.carga.definir(d.get("carga_maxima_kg"))
        self.garantia = s.campo("Garantia (meses)", Entrada(s, "inteiro"), 2)
        self.garantia.definir(d.get("garantia_meses") if d.get("garantia_meses") not in (None, "") else 3)
        s.fim_linha()
        self.ean = s.campo("Cód. de barras", Entrada(s, "digitos", placeholder_text="opcional (EAN)"), 0)
        self.ean.definir(d.get("codigo_barras"))
        self.condicao = ctk.CTkSegmentedButton(s, values=["Novo", "Usado"], selected_color=ROXO, selected_hover_color=ROXO_H,
                                               font=F(12, "bold"), height=32)
        self.condicao.set("Usado" if str(d.get("condicao") or "").lower().startswith("us") else "Novo")
        s.campo("Condição", self.condicao, 2)
        s.fim_linha()
        self.uso = s.campo("Para que serve", Entrada(s, placeholder_text="uma frase: onde e como se usa"), 0, 3)
        self.uso.definir(d.get("uso"))
        ctk.CTkLabel(s, text="Descrição", font=F(12, "bold"), text_color=MUDO, anchor="w").grid(row=s.linha, column=0, sticky="nw", padx=(20, 8), pady=(10, 0))
        self.desc = ctk.CTkTextbox(s, height=150, corner_radius=9, border_width=2, border_color=BORDA, fg_color=PAINEL, font=F(13), wrap="word")
        self.desc.grid(row=s.linha, column=1, columnspan=3, sticky="ew", padx=(0, 20), pady=(10, 0))
        self.desc.insert("1.0", str(d.get("descricao") or ""))
        self.lbl_desc = ctk.CTkLabel(s, text="", font=F(11), text_color=MUDO)
        self.lbl_desc.grid(row=s.linha + 1, column=3, sticky="e", padx=20, pady=(0, 14))
        self.desc.bind("<KeyRelease>", lambda e: self._contar())
        self._contar()

    def _contar(self):
        n = len(self.desc.get("1.0", "end").strip())
        self.lbl_desc.configure(text=f"{n} / 5000", text_color=VERMELHO if n > 5000 else MUDO)

    def _precos(self):
        d = self.dados
        s = Secao(self.corpo, "Preço, estoque e prazo", "Para mudar tudo de uma vez, preencha aqui e toque em Aplicar a todas.")
        s.grid(row=1, column=0, sticky="ew", padx=10, pady=8)
        linha = ctk.CTkFrame(s, fg_color="transparent")
        linha.grid(row=1, column=0, columnspan=4, sticky="ew", padx=20, pady=(6, 4))
        self.sob = ctk.CTkSwitch(linha, text="Sob encomenda (feito depois da venda)", progress_color=ROXO, font=F(13), text_color=TEXTO,
                                 command=self._sob)
        self.sob.pack(side="left")
        if motor.sim(d.get("sob_encomenda")):
            self.sob.select()
        ctk.CTkLabel(linha, text="Fica pronto em", font=F(12), text_color=MUDO).pack(side="left", padx=(18, 6))
        self.prazo = Entrada(linha, "inteiro", width=70, placeholder_text="dias")
        self.prazo.pack(side="left")
        ctk.CTkLabel(linha, text="dias", font=F(12), text_color=MUDO).pack(side="left", padx=6)
        self.prazo.definir(d.get("prazo_dias"))
        self._sob()
        ap = ctk.CTkFrame(s, fg_color=SUAVE, corner_radius=12)
        ap.grid(row=2, column=0, columnspan=4, sticky="ew", padx=20, pady=(8, 16))
        self.ap = {}
        for rot, chave, tipo in [("Custo R$", "custo", "dinheiro"), ("Venda R$", "venda", "dinheiro"), ("Preço “de” R$", "de", "dinheiro"),
                                 ("Estoque", "estoque", "inteiro"), ("Peso caixa kg", "peso", "decimal")]:
            ctk.CTkLabel(ap, text=rot, font=F(11, "bold"), text_color=MUDO).pack(side="left", padx=(12, 4), pady=10)
            e = Entrada(ap, tipo, width=86, height=30)
            e.pack(side="left")
            self.ap[chave] = e
        Botao(ap, "Aplicar a todas", "primario", width=130, height=30, font=F(12, "bold"), command=self.aplicar_todas).pack(side="right", padx=12)

    def _sob(self):
        self.prazo.configure(state="normal" if self.sob.get() else "disabled")

    def aplicar_todas(self):
        mudou = False
        for chave, e in self.ap.items():
            if not e.get().strip():
                continue
            mudou = True
            for l in self.linhas:
                alvo = {"custo": l.custo, "venda": l.venda, "de": l.de, "estoque": l.estoque, "peso": l.peso}[chave]
                alvo.definir(e.valor())
                l.calc()
        if mudou:
            for e in self.ap.values():
                e.definir(None)
            self.app.toast("Aplicado a todas as variações")

    # custo e preço sugerido
    def _custo(self):
        cfg = self.app.cfg["robo"]
        s = Secao(self.corpo, "Custo e preço sugerido",
                  "Monte o custo pelos materiais e horas, veja quanto sobra no Mercado Livre e compare com os concorrentes.")
        s.grid(row=2, column=0, sticky="ew", padx=10, pady=8)
        Botao(s.acoes, "＋ Mão de obra", "fantasma", width=120, command=self._mao_de_obra).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "＋  Material", "fantasma", width=110, command=lambda: self._linha_custo()).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "▦  Da tabela de materiais", "suave", width=190, command=lambda: EscolherMaterial(self)).pack(side="right")
        cab = ctk.CTkFrame(s, fg_color="transparent")
        cab.grid(row=1, column=0, columnspan=4, sticky="ew", padx=24, pady=(4, 0))
        for rot, larg in [("Item", 260), ("Qtd. / horas", 96), ("Valor R$", 96), ("Total", 90)]:
            ctk.CTkLabel(cab, text=rot.upper(), width=larg, anchor="w", font=F(10, "bold"), text_color=MUDO).pack(side="left", padx=3)
        self.caixa_custo = ctk.CTkFrame(s, fg_color="transparent")
        self.caixa_custo.grid(row=2, column=0, columnspan=4, sticky="ew", padx=20)
        self.custos = []
        for i in self.dados.get("ficha_de_custo") or []:
            if isinstance(i, dict):
                self._linha_custo(i.get("item"), i.get("quantidade"), i.get("valor"), recalc=False)
        tot = ctk.CTkFrame(s, fg_color="transparent")
        tot.grid(row=3, column=0, columnspan=4, sticky="ew", padx=20, pady=(6, 4))
        self.lbl_total = ctk.CTkLabel(tot, text="", font=F(14, "bold"), text_color=TEXTO, anchor="w")
        self.lbl_total.pack(side="left", padx=4)
        Botao(tot, "Usar como custo de todas", "suave", width=190, height=30, font=F(12, "bold"),
              command=self._usar_custo).pack(side="right")

        calc = ctk.CTkFrame(s, fg_color=SUAVE, corner_radius=12)
        calc.grid(row=4, column=0, columnspan=4, sticky="ew", padx=20, pady=(8, 6))
        ctk.CTkLabel(calc, text="Quero lucrar", font=F(12, "bold"), text_color=MUDO).pack(side="left", padx=(12, 4), pady=10)
        self.c_margem = Entrada(calc, "decimal", width=60, height=30, ao_mudar=self._conta)
        self.c_margem.definir(cfg.get("margem_padrao") or "30")
        self.c_margem.pack(side="left")
        ctk.CTkLabel(calc, text="%   frete por minha conta R$", font=F(12, "bold"), text_color=MUDO).pack(side="left", padx=4)
        self.c_frete = Entrada(calc, "dinheiro", width=80, height=30, ao_mudar=self._conta)
        self.c_frete.definir(cfg.get("frete_estimado") or None)
        self.c_frete.pack(side="left")
        self.c_tipo = _seg(calc, ["Clássico", "Premium"], command=lambda v: self._conta())
        self.c_tipo.set("Premium" if str((self.dados.get("mercado_livre") or {}).get("tipo_anuncio") or "").lower().startswith("prem") else "Clássico")
        self.c_tipo.pack(side="left", padx=10)
        Botao(calc, "Sugerir preço de venda", "primario", width=180, height=30, font=F(12, "bold"),
              command=self._sugerir).pack(side="right", padx=12)
        self.lbl_conta = ctk.CTkLabel(s, text="", font=F(12), text_color=MUDO, anchor="w", justify="left")
        self.lbl_conta.grid(row=5, column=0, columnspan=4, sticky="ew", padx=26, pady=(0, 6))

        pesq = ctk.CTkFrame(s, fg_color="transparent")
        pesq.grid(row=6, column=0, columnspan=4, sticky="ew", padx=20, pady=(4, 16))
        pesq.grid_columnconfigure(0, weight=1)
        self.p_termo = Entrada(pesq, placeholder_text="o que buscar no Mercado Livre (ex.: mão francesa ferro 30 cm)", height=34)
        self.p_termo.grid(row=0, column=0, sticky="ew", padx=(0, 8))
        self.bt_pesq = Botao(pesq, "⌕  Ver preços no Mercado Livre", "ml", width=230, command=self.pesquisar_precos)
        self.bt_pesq.grid(row=0, column=1)
        self.res_pesq = ctk.CTkFrame(pesq, fg_color="transparent", width=1, height=1)
        self.res_pesq.grid(row=1, column=0, columnspan=2, sticky="ew")
        self.cid_precos = None
        self.mediana_ml = None
        self._total_custo()

    def _linha_custo(self, item=None, qtd=None, valor=None, recalc=True):
        fr = ctk.CTkFrame(self.caixa_custo, fg_color="transparent")
        fr.pack(fill="x", pady=2)
        a = Entrada(fr, width=260, height=30, placeholder_text="ex.: Tubo de ferro 20 × 20")
        b = Entrada(fr, "decimal", width=96, height=30, ao_mudar=self._total_custo, placeholder_text="1")
        c = Entrada(fr, "dinheiro", width=96, height=30, ao_mudar=self._total_custo)
        t = ctk.CTkLabel(fr, text="", width=90, anchor="w", font=F(12, "bold"), text_color=TEXTO)
        for w in (a, b, c, t):
            w.pack(side="left", padx=3)
        a.definir(item)
        b.definir(qtd)
        c.definir(valor)
        linha = (fr, a, b, c, t)
        Botao(fr, "✕", "perigo", width=30, height=28,
              command=lambda: (self.custos.remove(linha), fr.destroy(), self._total_custo())).pack(side="left", padx=(6, 0))
        self.custos.append(linha)
        if recalc:
            self._total_custo()
            a.focus_set()
        return linha

    def _mao_de_obra(self):
        hora = motor.numero(self.app.cfg["robo"].get("valor_hora") or 0)
        linha = self._linha_custo("Mão de obra (horas)", 1, hora or None)
        linha[2].focus_set()
        if not hora:
            self.app.toast("Dica: ponha o valor da sua hora em Configurações", ROXO)

    def soma_custo(self):
        if hasattr(self, "custos"):
            return round(sum((b.valor() if b.valor() is not None else 1) * (c.valor() or 0) for _, a, b, c, t in self.custos), 2)
        return sum((motor.numero(i.get("quantidade")) or 1) * motor.numero(i.get("valor"))
                   for i in self.dados.get("ficha_de_custo") or [] if isinstance(i, dict))

    def _total_custo(self):
        for _, a, b, c, t in self.custos:
            v = (b.valor() if b.valor() is not None else 1) * (c.valor() or 0)
            t.configure(text=("R$ " + reais(v)) if c.valor() else "")
        tot = self.soma_custo()
        self.lbl_total.configure(text=f"Custo da peça: R$ {reais(tot)}" if self.custos else "Sem ficha de custo: use o custo de cada variação.")
        for l in getattr(self, "linhas", []):
            l.calc()
        self._conta()

    def _usar_custo(self):
        tot = self.soma_custo()
        if tot <= 0:
            self.app.toast("A ficha de custo está vazia", AMBAR)
            return
        for l in self.linhas:
            l.custo.definir(tot)
            l.calc()
        self._conta()
        self.app.toast(f"Custo R$ {reais(tot)} em todas as variações")

    def _parametros(self):
        tipo = "premium" if self.c_tipo.get() == "Premium" else "classico"
        return (self.c_margem.valor() if self.c_margem.valor() is not None else 30.0), (self.c_frete.valor() or 0.0), tipo

    def _custo_da(self, l):
        c = l.custo.valor()
        return c if c is not None else self.soma_custo()

    def _conta(self):
        if not hasattr(self, "lbl_conta") or not getattr(self, "linhas", None):
            return
        margem, frete, tipo = self._parametros()
        l = self.linhas[0]
        custo = self._custo_da(l)
        venda = l.venda.valor()
        partes = []
        if venda:
            c = recursos.conta_venda(venda, custo, tipo, frete)
            rot = f" ({l.valor.get().strip()})" if l.valor.get().strip() else ""
            partes.append(f"Vendendo{rot} a R$ {reais(venda)}: taxa R$ {reais(c['taxa'])}" +
                          (f" + custo fixo R$ {reais(c['fixo'])}" if c["fixo"] else "") +
                          (f" + frete R$ {reais(c['frete'])}" if c["frete"] else "") +
                          f"  →  você recebe R$ {reais(c['recebe'])} e lucra R$ {reais(c['lucro'])} ({c['margem']:.0f}%)")
        if custo:
            p = recursos.preco_sugerido(custo, margem, tipo, frete)
            if p:
                partes.append(f"Para sobrar {decimal_txt(margem)}% do preço como lucro (custo R$ {reais(custo)}, já tirando as taxas), venda a R$ {reais(p)}.")
            else:
                partes.append("Essa margem não fecha com a taxa do Mercado Livre. Diminua a margem.")
        if self.mediana_ml and custo:
            c = recursos.conta_venda(self.mediana_ml, custo, tipo, frete)
            partes.append(f"No preço do meio dos concorrentes (R$ {reais(self.mediana_ml)}) você lucraria R$ {reais(c['lucro'])} ({c['margem']:.0f}%).")
        self.lbl_conta.configure(text="\n".join(partes) or "Preencha o custo (ou a ficha de custo) e a venda para ver a conta.")

    def _sugerir(self):
        margem, frete, tipo = self._parametros()
        feitas = 0
        for l in self.linhas:
            custo = self._custo_da(l)
            if not custo:
                continue
            p = recursos.preco_sugerido(custo, margem, tipo, frete)
            if p:
                l.venda.definir(p)
                l.calc()
                feitas += 1
        self._conta()
        if feitas:
            self.app.toast(f"Preço sugerido em {feitas} variação(ões)")
        else:
            self.app.toast("Falta o custo para sugerir o preço", AMBAR)

    def pesquisar_precos(self):
        termo = self.p_termo.get().strip() or " ".join(x for x in [self.nome.valor(), self.material.valor()] if x)
        if not termo:
            self.app.toast("Digite o que buscar", AMBAR)
            return
        if self.app.ocupado_ml or self.app.rodando:
            self.app.toast("Espere o robô terminar o que está fazendo", AMBAR)
            return
        self.p_termo.definir(termo)
        self.app.ocupado_ml = True
        self.app.parar_flag.clear()
        self.cid_precos = f"p{id(self)}-{termo}"
        self.bt_pesq.configure(state="disabled", text="Buscando…")
        self.app.status("Buscando preços no Mercado Livre…")
        self.app.tarefas.put(("ml_precos", (termo, self.cid_precos)))

    def precos_ml_erro(self, cid, msg):
        if cid != self.cid_precos:
            return
        self.bt_pesq.configure(state="normal", text="⌕  Ver preços no Mercado Livre")
        self.app.status(msg)
        self.app.toast("Não deu para buscar os preços", VERMELHO)

    def precos_ml(self, cid, termo, itens):
        if cid != self.cid_precos or not self.winfo_exists():
            return
        self.bt_pesq.configure(state="normal", text="⌕  Ver preços no Mercado Livre")
        for w in self.res_pesq.winfo_children():
            w.destroy()
        est = recursos.estatisticas([i.get("preco") for i in itens])
        if not est:
            ctk.CTkLabel(self.res_pesq, text="Nenhum preço encontrado. Tente outras palavras.", text_color=MUDO, font=F(12)).pack(anchor="w", pady=6)
            return
        self.mediana_ml = est["mediana"]
        self.app.status(f"{est['qtd']} anúncios de “{termo}” no Mercado Livre.")
        caixa = ctk.CTkFrame(self.res_pesq, fg_color=SUAVE, corner_radius=12)
        caixa.pack(fill="x", pady=(8, 0))
        for i in range(4):
            caixa.grid_columnconfigure(i, weight=1, uniform="m")
        for i, (rot, val) in enumerate([("Anúncios", str(est["qtd"])), ("Mais barato", "R$ " + reais(est["menor"])),
                                         ("Preço do meio", "R$ " + reais(est["mediana"])), ("Mais caro", "R$ " + reais(est["maior"]))]):
            k = ctk.CTkFrame(caixa, fg_color="transparent")
            k.grid(row=0, column=i, sticky="w", padx=14, pady=8)
            ctk.CTkLabel(k, text=rot.upper(), font=F(10, "bold"), text_color=MUDO).pack(anchor="w")
            ctk.CTkLabel(k, text=val, font=F(16, "bold"), text_color=VERDE if i == 2 else TEXTO).pack(anchor="w")
        bts = ctk.CTkFrame(caixa, fg_color="transparent")
        bts.grid(row=1, column=0, columnspan=4, sticky="ew", padx=10, pady=(0, 10))
        Botao(bts, f"Usar R$ {reais(recursos.arredondar_90(est['mediana']))} como venda", "suave", height=30, font=F(12, "bold"),
              command=lambda: self._usar_venda(recursos.arredondar_90(est["mediana"]))).pack(side="left", padx=4)
        Botao(bts, "Ver os anúncios", "fantasma", height=30, font=F(12),
              command=lambda: (self.app.paginas["ml"].termo.definir(termo), self.app.toast("Busca pronta na página Mercado Livre", ROXO))
              ).pack(side="left", padx=4)
        ctk.CTkLabel(bts, text="Os 10% mais baratos e mais caros ficam de fora.", font=F(11), text_color=MUDO).pack(side="right", padx=6)
        self._conta()

    def _usar_venda(self, valor):
        for l in self.linhas:
            l.venda.definir(valor)
            l.calc()
        self._conta()
        self.app.toast(f"Venda R$ {reais(valor)} em todas as variações")

    # completar e conferir
    def _existentes(self):
        return recursos.codigos_das_fichas(self.app.pasta(), exceto=self.ficha.pasta if self.ficha else None)

    def _n_fotos(self):
        return len([f for f in self.fotos if not f.removida])

    def completar_tudo(self):
        d = self.montar()
        cfg = dict(self.app.cfg["robo"])
        existentes = self._existentes()
        feito = recursos.completar(d, cfg, existentes=existentes)
        feito += recursos.resolver(d, cfg=cfg, existentes=existentes)
        self._preencher(d)
        self.conferir(feito)

    def conferir(self, feito=None):
        d = self.montar()
        ConferirFicha(self.app, feito, recursos.problemas(d, self._n_fotos()))

    def _preencher(self, d):
        """Põe na tela o que o robô preencheu no dict (só muda os campos que mudaram)."""
        def por(e, v):
            if e.valor() != (v if v not in ("", None) else None):
                e.definir(v)
        por(self.codigo, d.get("codigo"))
        if d.get("categoria") and self.categoria.get() != d["categoria"]:
            self.categoria.set(d["categoria"])
        if d.get("prazo_dias") and self.sob.get():
            self.prazo.configure(state="normal")
            por(self.prazo, d.get("prazo_dias"))
        if d.get("variacao_por") and d["variacao_por"] in self.VARIACAO_POR and self.var_por.get() != d["variacao_por"]:
            self.var_por.set(d["variacao_por"])
        for l, v in zip(self.linhas, d["variacoes"]):
            emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
            for e, x in ((l.codigo, v.get("codigo")), (l.custo, v.get("custo")), (l.venda, v.get("venda")), (l.de, v.get("preco_tabela")),
                         (l.peso, emb.get("peso_kg")), (l.cc, emb.get("comprimento_cm")), (l.cl, emb.get("largura_cm")),
                         (l.ca, emb.get("altura_cm"))):
                por(e, x)
            l.calc()
        tec = [(i.get("nome"), i.get("valor")) for i in d.get("ficha_tecnica") or [] if isinstance(i, dict)]
        if tec != [(a.valor(), b.valor()) for _, a, b in self.tec if a.valor() and b.valor()]:
            for fr, a, b in list(self.tec):
                fr.destroy()
            self.tec = []
            for nome, valor in tec:
                self._linha_tec(nome, valor)
        tit = (d.get("mercado_livre") or {}).get("titulo_anuncio")
        if tit and tit != self.ml_titulo.get().strip():
            self.ml_titulo.definir(tit)
            self._contar_titulo()
        if not self.desc.get("1.0", "end").strip() and d.get("descricao"):
            self.desc.insert("1.0", d["descricao"])
            self._contar()

    # painel ao lado: resumo, progresso e pendências com botão de resolver
    def _comecar(self):
        if not self.winfo_exists():
            return
        self._inicial = self._assinatura()
        self._vigiar()

    def _vigiar(self):
        if not self.winfo_exists():
            return
        try:
            sig = self._assinatura()
            if sig != self._ultima:
                self._ultima = sig
                self._desenhar_painel()
            mudou = self._inicial is not None and sig != self._inicial
            self.chip_salvo.configure(text="● não salvo" if mudou else "✓ salvo" if self.ficha else "",
                                      fg_color=("#fffbeb", "#33270f") if mudou else (("#ecfdf5", "#0f2a22") if self.ficha else "transparent"),
                                      text_color=("#b45309", "#fcd34d") if mudou else ("#047857", "#6ee7b7"))
        except Exception:
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")
        self.after(900, self._vigiar)

    def _progresso(self, d, pend):
        vs = recursos._vs(d)
        chaves = {p["chave"] for p in pend}
        itens = [("Nome", "nome" not in chaves), ("Categoria", "categoria" not in chaves), ("Código", "codigo" not in chaves),
                 ("Preço de venda", "venda" not in chaves), ("Custo", "custo" not in chaves), ("Peso da caixa", "peso" not in chaves),
                 ("Fotos", "fotos" not in chaves), ("Descrição", "descricao" not in chaves),
                 ("Título do anúncio", not chaves & {"titulo", "titulo_longo"}),
                 ("Ficha técnica", bool(d.get("ficha_tecnica")))]
        if motor.sim(d.get("sob_encomenda")):
            itens.append(("Prazo", "prazo" not in chaves))
        if len(vs) > 1:
            itens.append(("Varia por", "variacao_por" not in chaves))
        return itens

    def _desenhar_painel(self):
        d = json.loads(json.dumps(self.montar(), default=str))
        cfg = dict(self.app.cfg["robo"])
        pend = recursos.pendencias(d, self._n_fotos(), cfg)
        for w in self.painel.winfo_children():
            w.destroy()
        r = 0

        def por(w, **kw):
            nonlocal r
            w.grid(row=r, column=0, sticky=kw.pop("sticky", "ew"), padx=kw.pop("padx", 14), pady=kw.pop("pady", 3), **kw)
            r += 1

        vis = [f for f in self.fotos if not f.removida]
        img = miniatura(vis[0].caminho, 72) if vis else None
        topo = ctk.CTkFrame(self.painel, fg_color="transparent")
        topo.grid_columnconfigure(1, weight=1)
        if img:
            self.app._refs.append(img)
        cap = ctk.CTkLabel(topo, text="" if img else "sem\nfoto", image=img, width=72, height=72, fg_color=SUAVE if not img else "transparent",
                           corner_radius=12, text_color=MUDO, font=F(10), cursor="hand2")
        cap.grid(row=0, column=0, rowspan=2, padx=(0, 10))
        cap.bind("<Button-1>", lambda e: self.ir_secao(self.secoes["fotos"]))
        ctk.CTkLabel(topo, text=cortar(d.get("nome") or "Sem nome", 44), font=F(14, "bold"), text_color=TEXTO, anchor="w",
                     justify="left", wraplength=150).grid(row=0, column=1, sticky="sw")
        ctk.CTkLabel(topo, text="  ·  ".join(x for x in [d.get("codigo") or "", d.get("categoria") or ""] if x) or "sem código",
                     font=F(11), text_color=MUDO, anchor="w", wraplength=150, justify="left").grid(row=1, column=1, sticky="nw")
        por(topo, pady=(14, 6))

        itens = self._progresso(d, pend)
        ok = sum(1 for _, b in itens if b)
        pct = ok / len(itens)
        cor = VERDE if pct >= 0.99 else ROXO if pct >= 0.6 else AMBAR
        linha = ctk.CTkFrame(self.painel, fg_color="transparent")
        ctk.CTkLabel(linha, text="Ficha pronta" if pct >= 0.99 else f"Ficha {pct * 100:.0f}% pronta", font=F(12, "bold"),
                     text_color=cor).pack(side="left")
        ctk.CTkLabel(linha, text=f"{ok} de {len(itens)}", font=F(11), text_color=MUDO).pack(side="right")
        por(linha, pady=(6, 0))
        barra = ctk.CTkProgressBar(self.painel, height=8, corner_radius=4, progress_color=cor, fg_color=SUAVE)
        barra.set(pct)
        por(barra, pady=(2, 8))

        vendas = [motor.numero(v.get("venda")) for v in recursos._vs(d) if motor.numero(v.get("venda")) > 0]
        if vendas and self.linhas:
            l = self.linhas[0]
            margem, frete, tipo = self._parametros()
            custo = self._custo_da(l)
            k = ctk.CTkFrame(self.painel, fg_color=SUAVE, corner_radius=12)
            k.grid_columnconfigure((0, 1), weight=1)
            faixa = f"R$ {reais(min(vendas))}" + (f" a {reais(max(vendas))}" if max(vendas) > min(vendas) else "")
            ctk.CTkLabel(k, text="VENDA", font=F(10, "bold"), text_color=MUDO, anchor="w").grid(row=0, column=0, sticky="w", padx=10, pady=(8, 0))
            ctk.CTkLabel(k, text=faixa, font=F(13, "bold"), text_color=VERDE, anchor="w").grid(row=1, column=0, columnspan=2, sticky="w", padx=10)
            if l.venda.valor() and custo:
                c = recursos.conta_venda(l.venda.valor(), custo, tipo, frete)
                cor_m = VERDE if c["margem"] >= 25 else AMBAR if c["margem"] >= 10 else VERMELHO
                ctk.CTkLabel(k, text=f"No ML você lucra R$ {reais(c['lucro'])} ({c['margem']:.0f}%)", font=F(11, "bold"),
                             text_color=cor_m, anchor="w", wraplength=220, justify="left").grid(row=2, column=0, columnspan=2, sticky="w", padx=10, pady=(0, 8))
            else:
                ctk.CTkLabel(k, text="Ponha o custo para ver o lucro", font=F(11), text_color=MUDO, anchor="w").grid(
                    row=2, column=0, columnspan=2, sticky="w", padx=10, pady=(0, 8))
            por(k, pady=(0, 8))

        cab = ctk.CTkFrame(self.painel, fg_color="transparent")
        n_pend = len({(p["chave"]) for p in pend})
        ctk.CTkLabel(cab, text="Falta resolver" if pend else "Tudo certo", font=F(14, "bold"), text_color=TEXTO).pack(side="left")
        if pend:
            ctk.CTkLabel(cab, text=str(n_pend), width=24, height=22, corner_radius=11, fg_color=AMBAR, text_color="white",
                         font=F(11, "bold")).pack(side="left", padx=6)
        por(cab, pady=(6, 2))
        autos = [p for p in pend if p["acao"] and p["acao"] != "Informar"]
        if autos:
            por(dica(Botao(self.painel, "✦  Resolver o que der", "primario", height=34, font=F(12, "bold"),
                           command=lambda: self.resolver({p["chave"] for p in autos})),
                     "O robô resolve o que dá para deduzir. O que for estimado fica marcado para você conferir."), pady=(2, 6))
        if not pend:
            ctk.CTkLabel(self.painel, text="✓  A ficha tem tudo o que o site e o Mercado Livre precisam. É só salvar.",
                         font=F(12), text_color=VERDE, anchor="w", justify="left", wraplength=220).grid(row=r, column=0, sticky="ew", padx=14, pady=4)
            r += 1
        # o mesmo aviso em várias variações vira um cartão só
        juntos = []
        for p in pend:
            ig = next((q for q in juntos if q["chave"] == p["chave"] and p["variacao"] is not None), None)
            if ig:
                ig["vars"].append(p["texto"].split(":")[0])
            else:
                q = dict(p)
                q["vars"] = [p["texto"].split(":")[0]] if p["variacao"] is not None else []
                juntos.append(q)
        for q in juntos:
            if len(q["vars"]) > 1:
                base = {"venda": "Sem preço de venda", "custo": "Sem custo", "peso": "Sem peso da caixa (o frete precisa)"}.get(q["chave"], q["texto"])
                q["texto"] = f"{base} em {len(q['vars'])} variações: " + cortar(", ".join(q["vars"]), 70)
        pend = juntos
        for p in pend[:14]:
            c = ctk.CTkFrame(self.painel, fg_color=("#fffbeb", "#2a2110"), corner_radius=10)
            c.grid_columnconfigure(0, weight=1)
            ctk.CTkLabel(c, text=p["texto"], font=F(11), text_color=("#92400e", "#fcd34d"), anchor="w", justify="left",
                         wraplength=220).grid(row=0, column=0, columnspan=2, sticky="w", padx=10, pady=(7, 2))
            bts = ctk.CTkFrame(c, fg_color="transparent")
            bts.grid(row=1, column=0, sticky="w", padx=6, pady=(0, 6))
            if p["acao"] and p["acao"] != "Informar":
                Botao(bts, "✦ " + p["acao"], "suave", height=24, width=40, font=F(11, "bold"),
                      command=lambda k=p["chave"]: self.resolver({k})).pack(side="left", padx=(0, 4))
            if p["chave"] == "fotos" and not p["acao"]:
                Botao(bts, "＋ Adicionar fotos", "suave", height=24, width=40, font=F(11, "bold"),
                      command=self.escolher_fotos).pack(side="left", padx=(0, 4))
            ir = "Preencher" if p["acao"] in (None, "Informar") and p["chave"] != "fotos" else "Ver"
            Botao(bts, ir + " ›", "fantasma", height=24, width=40, font=F(11),
                  command=lambda q=p: self.ir_pendencia(q)).pack(side="left")
            por(c, pady=2)
        if len(pend) > 14:
            ctk.CTkLabel(self.painel, text=f"e mais {len(pend) - 14}…", font=F(11), text_color=MUDO).grid(row=r, column=0, sticky="w", padx=16)
            r += 1
        feitos = [t for t, b in itens if b]
        if feitos:
            ctk.CTkLabel(self.painel, text="✓ " + "  ✓ ".join(feitos), font=F(10), text_color=MUDO, anchor="w", justify="left",
                         wraplength=226).grid(row=r, column=0, sticky="ew", padx=14, pady=(10, 14))

        # bolinha de status em cada aba de seção
        por_secao = {}
        for p in pend:
            por_secao[p["secao"]] = por_secao.get(p["secao"], 0) + 1
        for chave, b in self.chips.items():
            sec = self.secoes[chave]
            nome = self.CURTOS.get(sec.titulo, sec.titulo)
            n = por_secao.get(chave, 0)
            b.configure(text=f"!  {nome}  ·  {n}" if n else f"✓  {nome}", border_color=AMBAR if n else ("#a7f3d0", "#14532d"),
                        fg_color=("#fffbeb", "#2a2110") if n else ("#ecfdf5", "#0f2a1f"),
                        text_color=("#b45309", "#fcd34d") if n else ("#047857", "#6ee7b7"))

    def resolver(self, chaves):
        chaves = set(chaves)
        if "fotos" in chaves:
            chaves.discard("fotos")
            if self.ficha is None:
                self.app.toast("Salve a ficha uma vez para o robô baixar as fotos", AMBAR)
            elif self._n_fotos() == 0:
                if self.ml_link.valor() and self.ml_link.valor() != (self.ficha.dados.get("mercado_livre") or {}).get("link_referencia"):
                    self.app.toast("Salve a ficha com o link novo antes de baixar as fotos", AMBAR)
                else:
                    self.app.baixar_fotos_ml([self.ficha.pasta])
            if not chaves:
                return
        d = self.montar()
        cfg = dict(self.app.cfg["robo"])
        feito = recursos.resolver(d, chaves, cfg, self._existentes())
        self._preencher(d)
        self._ultima = None
        if feito:
            self.app.toast("✦ " + feito[0] + (f"  (+{len(feito) - 1})" if len(feito) > 1 else ""))
            self.app.status(" · ".join(feito))
        else:
            self.app.toast("Nada que o robô consiga deduzir sozinho aqui", AMBAR)

    def fotos_baixadas(self, nomes):
        for n in nomes:
            p = self.ficha.pasta / n
            if p.exists() and not any(f.caminho == p for f in self.fotos):
                self.fotos.append(FotoEd(p, uso="todas"))
        self.desenhar_fotos()
        self._ultima = None

    def _campo_da(self, p):
        i = p.get("variacao")
        l = self.linhas[i] if i is not None and i < len(self.linhas) else (self.linhas[0] if self.linhas else None)
        return {"nome": self.nome, "categoria": self.categoria, "codigo": self.codigo, "venda": l and l.venda,
                "custo": (l and l.custo) if self.custos else None, "peso": l and l.peso, "prazo": self.prazo,
                "titulo": self.ml_titulo, "titulo_longo": self.ml_titulo, "descricao": self.desc}.get(p["chave"])

    def ir_pendencia(self, p):
        w = self._campo_da(p)
        if w is None and p["chave"] == "custo" and self.linhas:
            i = p.get("variacao") or 0
            w = self.linhas[min(i, len(self.linhas) - 1)].custo
        if w is None:
            self.ir_secao(self.secoes.get(p["secao"]) or self.secoes["produto"])
            return
        self._ir_widget(w)
        try:
            w.focus_set()
            antes = w.cget("border_color")
            w.configure(border_color=AMBAR)
            self.after(1800, lambda: w.winfo_exists() and w.configure(border_color=antes))
        except Exception:
            pass

    def _ir_widget(self, w):
        self.update_idletasks()
        alto = max(self.corpo.winfo_height(), 1)
        y = w.winfo_rooty() - self.corpo.winfo_rooty()
        self.corpo._parent_canvas.yview_moveto(max(y - 140, 0) / alto)

    # título e descrição
    def gerar_textos(self):
        d = json.loads(json.dumps(self.montar()))
        if not d.get("nome"):
            self.app.toast("Preencha o nome do produto primeiro", AMBAR)
            return
        chave = (self.app.cfg["robo"].get("gemini_chave") or "").strip()
        modelo = (self.app.cfg["robo"].get("gemini_modelo") or "gemini-2.5-flash").strip()
        self.bt_textos.configure(state="disabled", text="Escrevendo…")

        def rodar():
            erro = None
            if chave:
                try:
                    r = recursos.gerar_textos_ia(d, chave, modelo)
                    r["fonte"] = "ia"
                    self.app.fila.put(("ia_textos", None, r))
                    return
                except Exception as e:
                    erro = str(e)
            self.app.fila.put(("ia_textos", None, {"titulo": recursos.titulo_modelo(d), "descricao": recursos.descricao_modelo(d),
                                                   "fonte": "modelo", "erro": erro}))
        threading.Thread(target=rodar, daemon=True).start()

    def textos_prontos(self, t):
        if not self.winfo_exists():
            return
        self.bt_textos.configure(state="normal", text="✦  Escrever título e descrição")
        atual = self.desc.get("1.0", "end").strip()
        if atual and not messagebox.askyesno("Título e descrição", "Trocar o título e a descrição que já estão na ficha pelos novos?"):
            return
        if t.get("titulo"):
            self.ml_titulo.definir(t["titulo"])
            self._contar_titulo()
        if t.get("descricao"):
            self.desc.delete("1.0", "end")
            self.desc.insert("1.0", t["descricao"])
            self._contar()
        if t.get("fonte") == "ia":
            self.app.toast("Texto escrito pela IA. Confira antes de salvar.")
        elif t.get("erro"):
            self.app.toast("A IA falhou; usei o modelo pronto", AMBAR)
            self.app.status(t["erro"])
        else:
            self.app.toast("Texto pelo modelo pronto. Com a chave do Gemini fica melhor.", ROXO)

    def _variacoes(self):
        d = self.dados
        s = Secao(self.corpo, "Variações", "Cada uma vira um produto no site.")
        s.grid(row=3, column=0, sticky="ew", padx=10, pady=8)
        self.secao_var = s
        valores = list(self.VARIACAO_POR)
        atual = d.get("variacao_por") or ("Tamanho" if len(d.get("variacoes") or []) > 1 else "Sem variação")
        if atual not in valores:
            valores.append(atual)
        self.var_por = ctk.CTkSegmentedButton(s.acoes, values=valores, selected_color=ROXO, selected_hover_color=ROXO_H,
                                              font=F(12, "bold"), height=32)
        self.var_por.set(atual)
        self.var_por.pack(side="right")

        # gerador: tamanhos × modelos/cores × kits
        gerar = ctk.CTkFrame(s, fg_color=VIDRO, corner_radius=16, border_width=1, border_color=VIDRO_BORDA)
        gerar.grid(row=1, column=0, columnspan=4, sticky="ew", padx=20, pady=(6, 10))
        ctk.CTkLabel(gerar, text="✦  Criar várias de uma vez", font=F(13, "bold"), text_color=TEXTO, anchor="w").grid(
            row=0, column=0, columnspan=4, sticky="w", padx=14, pady=(12, 2))
        ctk.CTkLabel(gerar, text="Separe por vírgula. O robô cria todas as combinações e calcula o kit (custo, preço e peso × unidades).",
                     font=F(11), text_color=MUDO, anchor="w", justify="left", wraplength=560).grid(row=1, column=0, columnspan=4, sticky="w", padx=14)

        def campo(col, rot, larg, ph, tipo="texto"):
            ctk.CTkLabel(gerar, text=rot, font=F(10, "bold"), text_color=MUDO, anchor="w").grid(row=2, column=col, sticky="w",
                                                                                                padx=(14 if col == 0 else 6, 0), pady=(8, 0))
            e = Entrada(gerar, tipo, width=larg, height=32, placeholder_text=ph)
            e.grid(row=3, column=col, sticky="ew", padx=(14 if col == 0 else 6, 14 if col == 3 else 0), pady=(2, 8))
            gerar.grid_columnconfigure(col, weight=larg)
            return e
        self.g_tam = campo(0, "TAMANHOS", 150, "25 cm, 30 cm, 35 cm")
        self.g_cor = campo(1, "MODELOS OU CORES", 190, "Logo, Desenho, Sem gravura")
        self.g_kit = campo(2, "KITS (UNIDADES)", 90, "1, 2, 4")
        self.g_desc = campo(3, "DESCONTO KIT %", 80, "5", "decimal")
        bts = ctk.CTkFrame(gerar, fg_color="transparent")
        bts.grid(row=4, column=0, columnspan=4, sticky="ew", padx=14, pady=(2, 14))
        Botao(bts, "Criar variações", "primario", width=150, height=32, font=F(12, "bold"), command=self.gerar).pack(side="left")
        Botao(bts, "🎨  Escolher cores cadastradas", "suave", width=220, height=32, font=F(12, "bold"),
              command=self.escolher_cores).pack(side="left", padx=8)
        self.lbl_cores = ctk.CTkLabel(bts, text="", font=F(11), text_color=MUDO, anchor="w")
        self.lbl_cores.pack(side="left", padx=4)
        self._canva_cores, self._marc_cores = {}, (d.get("cores_marcadas") or {})

        barra = ctk.CTkFrame(s, fg_color="transparent")
        barra.grid(row=2, column=0, columnspan=4, sticky="ew", padx=20, pady=(0, 4))
        self.lbl_resumo_var = ctk.CTkLabel(barra, text="", font=F(12, "bold"), text_color=MUDO, anchor="w")
        self.lbl_resumo_var.pack(side="left")
        Botao(barra, "Fechar", "fantasma", width=64, height=28, font=F(12),
              command=lambda: [l.alternar(False) for l in self.linhas]).pack(side="right")
        Botao(barra, "Desligar todas", "fantasma", width=104, height=28, font=F(12),
              command=lambda: [l.ligar(False) for l in self.linhas]).pack(side="right", padx=(0, 12))
        Botao(barra, "Ligar todas", "fantasma", width=90, height=28, font=F(12),
              command=lambda: [l.ligar(True) for l in self.linhas]).pack(side="right", padx=(0, 6))
        Botao(barra, "Abrir", "fantasma", width=60, height=28, font=F(12),
              command=lambda: [l.alternar(True) for l in self.linhas]).pack(side="right", padx=6)
        # várias de uma vez: marcar e aplicar custo / preço
        lote = ctk.CTkFrame(s, fg_color=VIDRO, corner_radius=16, border_width=1, border_color=VIDRO_BORDA)
        lote.grid(row=3, column=0, columnspan=4, sticky="ew", padx=20, pady=(2, 8))
        l_a = ctk.CTkFrame(lote, fg_color="transparent")
        l_a.pack(fill="x", padx=14, pady=(10, 4))
        ctk.CTkLabel(l_a, text="☑  Marcar", font=F(13, "bold"), text_color=TEXTO).pack(side="left")
        self.lote_filtro = Entrada(l_a, width=210, height=30, placeholder_text="as que têm… (ex.: 75 cm, corano Verde)")
        self.lote_filtro.pack(side="left", padx=8)
        self.lote_filtro.bind("<Return>", lambda e: self.marcar_por_texto())
        Botao(l_a, "Marcar", "suave", width=70, height=30, font=F(12, "bold"), command=self.marcar_por_texto).pack(side="left")
        Botao(l_a, "Todas", "fantasma", width=60, height=30, font=F(12), command=lambda: self.marcar_todas_var(True)).pack(side="left", padx=(8, 0))
        Botao(l_a, "Nenhuma", "fantasma", width=70, height=30, font=F(12), command=lambda: self.marcar_todas_var(False)).pack(side="left", padx=4)
        self.lbl_marcadas = ctk.CTkLabel(l_a, text="", font=F(12, "bold"), text_color=ROXO_TXT)
        self.lbl_marcadas.pack(side="left", padx=8)
        l_b = ctk.CTkFrame(lote, fg_color="transparent")
        l_b.pack(fill="x", padx=14, pady=(2, 12))
        ctk.CTkLabel(l_b, text="Nas marcadas:", font=F(12, "bold"), text_color=MUDO).pack(side="left")
        self.lote = {}
        for chave, rot in (("custo", "custo R$"), ("venda", "venda R$"), ("de", "preço “de” R$")):
            ctk.CTkLabel(l_b, text=rot, font=F(11), text_color=MUDO).pack(side="left", padx=(10, 3))
            self.lote[chave] = Entrada(l_b, "dinheiro", width=86, height=30)
            self.lote[chave].pack(side="left")
        Botao(l_b, "Aplicar", "primario", width=80, height=30, font=F(12, "bold"), command=self.aplicar_marcadas).pack(side="left", padx=(10, 0))
        dica(Botao(l_b, "▦ Montar custo", "suave", width=120, height=30, font=F(12, "bold"), command=self.custo_marcadas),
             "Monta o custo pela tabela de materiais e põe em todas as marcadas").pack(side="left", padx=(8, 0))
        self.caixa_var = ctk.CTkFrame(s, fg_color="transparent")
        self.caixa_var.grid(row=4, column=0, columnspan=4, sticky="ew", padx=20)
        for v in d.get("variacoes") or []:
            if isinstance(v, dict):
                self.adicionar_variacao(v, redesenhar=False)
        self._desenhar_var()
        Botao(s, "＋  Adicionar variação", "suave", width=170, command=lambda: self.adicionar_variacao()).grid(
            row=5, column=0, columnspan=4, sticky="w", padx=20, pady=(8, 16))
        self._contar_marcadas()

    def marcadas_var(self):
        return [l for l in self.linhas if l.marca.get()]

    def _contar_marcadas(self):
        if hasattr(self, "lbl_marcadas"):
            n = len(self.marcadas_var())
            self.lbl_marcadas.configure(text=f"{n} marcada(s)" if n else "")
        try:
            self.bt_fotos_marc.configure(text=f"☑  Fotos das marcadas ({len(self.marcadas_var())})")
        except Exception:
            pass

    def marcar_todas_var(self, sim):
        for l in self.linhas:
            l.marca.select() if sim else l.marca.deselect()
        self._contar_marcadas()

    def marcar_por_texto(self):
        """Marca (somando às já marcadas) as variações cujo nome tem todos os pedaços digitados."""
        pedacos = [p.strip().lower() for p in re.split(r"[,;]", self.lote_filtro.get()) if p.strip()]
        if not pedacos:
            return
        n = 0
        for l in self.linhas:
            nome = l.rotulo().lower()
            if all(p in nome for p in pedacos):
                l.marca.select()
                n += 1
        self._contar_marcadas()
        self.app.toast(f"{n} variação(ões) marcada(s)" if n else "Nenhuma variação com esse nome", VERDE if n else AMBAR)

    def aplicar_marcadas(self):
        alvos = self.marcadas_var()
        if not alvos:
            self.app.toast("Marque as variações primeiro (caixinha no cartão ou “Marcar”)", AMBAR)
            return
        vals = {k: e.valor() for k, e in self.lote.items()}
        if all(v is None for v in vals.values()):
            self.app.toast("Ponha o custo, a venda ou o preço “de”", AMBAR)
            return
        for l in alvos:
            if vals["custo"] is not None:
                l.custo.definir(vals["custo"])
            if vals["venda"] is not None:
                l.venda.definir(vals["venda"])
            if vals["de"] is not None:
                l.de.definir(vals["de"])
            l.calc()
        for e in self.lote.values():
            e.definir(None)
        self.app.toast(f"Aplicado em {len(alvos)} variação(ões)")

    def custo_marcadas(self):
        alvos = self.marcadas_var()
        if not alvos:
            self.app.toast("Marque as variações primeiro", AMBAR)
            return
        CustoVariacao(alvos[0], alvos[1:])

    def _resumo_var(self):
        if not getattr(self, "lbl_resumo_var", None) or not self.linhas:
            return
        n = len(self.linhas)
        lig = [l for l in self.linhas if l.ativa]
        est = sum((l.estoque.valor() or 0) for l in lig)
        sem_preco = sum(1 for l in lig if not l.venda.valor())
        t = f"{len(lig)} de {n} ligada(s) para subir · {est} em estoque" if len(lig) < n else f"{n} variação(ões) · {est} em estoque"
        if sem_preco:
            t += f" · {sem_preco} sem preço de venda"
        self.lbl_resumo_var.configure(text=t, text_color=AMBAR if sem_preco else MUDO)

    def _fotos_var(self):
        """Mostra no cartão de cada variação a foto que vai ser a capa dela."""
        vis = [f for f in self.fotos if not f.removida]
        for l in self.linhas:
            cor = self._cor_da_linha(l)
            propria = next((f for f in vis if f.de(l) or (isinstance(f.uso, str) and cor and f.uso == f"cor:{cor}")), None)
            geral = next((f for f in vis if f.uso == "todas"), None)
            l.mostrar_foto((propria or geral).caminho if (propria or geral) else None)

    def _desenhar_var(self):
        for l in self.linhas:
            l.pack_forget()
        for l in self.linhas:
            l.pack(fill="x", pady=5)
        self._resumo_var()

    def _nova_base(self):
        base = self.linhas[-1].dados() if self.linhas else {}
        return {"valor": None, "codigo": "", "custo": base.get("custo"), "venda": base.get("venda"), "preco_tabela": base.get("preco_tabela"),
                "estoque": 0, "embalagem": base.get("embalagem") or {}, "medidas_peca_cm": base.get("medidas_peca_cm") or {}, "fotos": []}

    def adicionar_variacao(self, v=None, redesenhar=True):
        if v is None:
            v = self._nova_base()
        if len(self.linhas) >= 40:
            messagebox.showinfo("Variações", "O máximo é 40 variações.")
            return None
        l = LinhaVariacao(self.caixa_var, self, v)
        self.linhas.append(l)
        if redesenhar:
            self._desenhar_var()
            self.atualizar_usos()
            if len(self.linhas) > 1 and self.var_por.get() == "Sem variação":
                self.var_por.set("Tamanho")
            l.valor.focus_set()
        return l

    def duplicar_variacao(self, linha):
        v = json.loads(json.dumps(linha.dados(), default=str))
        v["codigo"], v["fotos"], v["estoque"] = "", [], 0
        v["valor"] = (v.get("valor") or "") + " (cópia)" if v.get("valor") else None
        l = self.adicionar_variacao(v, redesenhar=False)
        if l is None:
            return
        self.linhas.remove(l)
        self.linhas.insert(self.linhas.index(linha) + 1, l)
        self._desenhar_var()
        self.atualizar_usos()
        if self.var_por.get() == "Sem variação":
            self.var_por.set("Tamanho")
        l.valor.focus_set()
        l.valor.select_range(0, "end")

    def remover_variacao(self, linha):
        if len(self.linhas) <= 1:
            messagebox.showinfo("Variações", "A ficha precisa de pelo menos 1 item.")
            return
        self.linhas.remove(linha)
        for f in self.fotos:
            if f.uso is linha:
                f.uso = "nao"        # as fotos só dela ficam guardadas como "Não usar"
            elif isinstance(f.uso, tuple) and linha in f.uso:
                resto = tuple(x for x in f.uso if x is not linha)
                f.uso = resto[0] if len(resto) == 1 else (resto or "nao")
        linha.destroy()
        self._desenhar_var()
        self.atualizar_usos()

    def escolher_cores(self):
        tam = [t for t in self.g_tam.get().split(",") if t.strip()]
        kits = {int(k) for k in re.findall(r"\d+", self.g_kit.get()) if 0 < int(k) <= 50} or {1}

        def usar(comb, marc):
            self._canva_cores = {n.lower(): c for n, c in comb}
            self._marc_cores = marc
            self.g_cor.definir(", ".join(n for n, _ in comb))
            self.lbl_cores.configure(text=f"{len(comb)} cor(es) prontas · aperte Criar variações")
        marc = self._marc_cores
        if not marc:      # ficha sem marcação: começa com as cores que as variações já têm
            marc = {}
            for l in self.linhas:
                for k, v in l.cores.items():
                    marc.setdefault(k, set()).add(v)
        EscolherCores(self.app, usar, multiplicar=max(len(tam), 1) * len(kits), marcadas=marc)

    def gerar(self):
        tam = [t.strip() for t in self.g_tam.get().split(",") if t.strip()]
        cor = [c.strip() for c in self.g_cor.get().split(",") if c.strip()]
        kits = sorted({int(k) for k in re.findall(r"\d+", self.g_kit.get()) if 0 < int(k) <= 50}) or [1]
        desc = min(max(self.g_desc.valor() or 0, 0), 60) / 100
        if not tam and not cor and kits == [1]:
            return
        if not tam and not cor:      # só kits: em cima das variações que já existem
            bases = [(l.valor.get().strip(), l.dados()) for l in self.linhas]
        else:
            nomes = [f"{t} · {c}" for t in tam for c in cor] if tam and cor else (tam or cor)
            base = self.linhas[0].dados() if self.linhas else {}
            bases = [(n, base) for n in nomes]
        valores = []
        for nome, base in bases:
            for k in kits:
                valores.append((" · ".join(x for x in (nome, f"Kit com {k}" if k > 1 else "") if x), base, k))
        existentes = {l.valor.get().strip().lower() for l in self.linhas}
        ficam = [l for l in self.linhas if l.valor.get().strip()] if (tam or cor) else self.linhas   # as vazias saem
        if len({v[0].lower() for v in valores} - existentes) + max(len(ficam), 0 if (tam or cor) else 1) > 40:
            messagebox.showinfo("Variações", f"Isso passaria de 40 variações (o máximo do site).")
            return
        if tam or cor:
            for l in [l for l in self.linhas if not l.valor.get().strip()]:
                if len(self.linhas) > 1 or valores:
                    self.linhas.remove(l)
                    l.destroy()
        novos = 0
        for nome, base, k in valores:
            if nome.lower() in existentes:
                continue
            emb = dict(base.get("embalagem") or {})
            custo, venda, de = base.get("custo"), base.get("venda"), base.get("preco_tabela")
            if k > 1:
                custo = round(custo * k, 2) if custo else custo
                venda = round(venda * k * (1 - desc), 2) if venda else venda
                de = round(de * k, 2) if de else (round(base.get("venda") * k, 2) if base.get("venda") and desc else None)
                if emb.get("peso_kg"):
                    emb["peso_kg"] = round(emb["peso_kg"] * k, 2)
                if emb.get("altura_cm"):
                    emb["altura_cm"] = round(emb["altura_cm"] * k, 1)
            v = {"valor": nome, "codigo": "", "custo": custo, "venda": venda, "preco_tabela": de,
                 "estoque": 0 if k > 1 else (base.get("estoque") or 0), "embalagem": emb,
                 "medidas_peca_cm": base.get("medidas_peca_cm") or {}, "fotos": []}
            if k > 1:
                v["kit"] = k
            if base.get("canva_extra") and not (tam or cor):
                v["canva_extra"] = base["canva_extra"]
            cor_txt = self._canva_cores.get(nome.split(" · Kit com ")[0].split(" · ")[-1].strip().lower())
            if cor_txt:
                v["canva_extra"] = cor_txt
            self.adicionar_variacao(v, redesenhar=False)
            novos += 1
        if tam and cor:
            self.var_por.set("Tamanho e cor")
        elif tam or cor:
            self.var_por.set("Tamanho" if tam else "Cor")
        elif self.var_por.get() == "Sem variação":
            self.var_por.set("Tamanho")
        self._desenhar_var()
        self.atualizar_usos()
        for e in (self.g_tam, self.g_cor, self.g_kit):
            e.definir(None)
        self.lbl_cores.configure(text="")
        self.app.toast(f"{novos} variação(ões) criada(s)")

    # fotos
    def _fotos(self):
        s = Secao(self.corpo, "Fotos", "Adicione as fotos e diga em quais variações cada uma entra. A primeira de cada variação é a capa dela.")
        s.grid(row=4, column=0, sticky="ew", padx=10, pady=8)
        Botao(s.acoes, "✦  Deixar prontas", "fantasma", width=140, command=lambda: FotosProntas(self)).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "Gemini", "fantasma", width=80, command=lambda: ImagensIA(self)).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "✦  Criar no Canva", "suave", width=140, command=lambda: ImagensIA(self, "canva")).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "Colar imagem", "fantasma", width=120, command=self.colar_imagem).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "＋  Adicionar fotos", "suave", width=150, command=self.escolher_fotos).pack(side="right")
        modo = ctk.CTkFrame(s, fg_color="transparent")
        modo.grid(row=1, column=0, columnspan=4, sticky="ew", padx=20, pady=(0, 6))
        self.modo_fotos = _seg(modo, ["Por variação", "Todas as fotos"], command=lambda v: self.desenhar_fotos())
        self.modo_fotos.pack(side="left")
        self.lbl_modo = ctk.CTkLabel(modo, text="", font=F(11), text_color=MUDO, anchor="w", justify="left", wraplength=520)
        self.lbl_modo.pack(side="left", padx=12)
        self.grade_fotos = ctk.CTkFrame(s, fg_color="transparent")
        self.grade_fotos.grid(row=2, column=0, columnspan=4, sticky="ew", padx=16, pady=(4, 16))
        self._mini_cache = {}
        self._carregar_fotos()
        self.desenhar_fotos()

    def _carregar_fotos(self):
        if not self.ficha:
            return
        f = self.ficha
        na_pasta = {}
        for p in f.pasta.iterdir():
            if p.is_file() and p.suffix.lower() in motor.FOTO_EXTS | motor.FOTO_CONVERTER:
                na_pasta.setdefault(p.name.lower(), p)
                na_pasta.setdefault(p.stem.lower(), p)
        listas = []
        for v in f.variacoes:
            lst = []
            for n in v.get("fotos") or []:
                n = str(n).strip().replace("\\", "/").split("/")[-1]
                p = na_pasta.get(n.lower()) or na_pasta.get(Path(n).stem.lower())
                if p and p not in lst:
                    lst.append(p)
            listas.append(lst)
        ordem = []
        for lst in listas:
            for p in lst:
                if p not in ordem:
                    ordem.append(p)
        citou = bool(ordem)
        for p in sorted(set(na_pasta.values()), key=lambda x: x.name.lower()):
            if p not in ordem:
                ordem.append(p)
        cheias = {i for i, lst in enumerate(listas) if lst}
        for p in ordem:
            em = {i for i, lst in enumerate(listas) if p in lst}
            if not em:
                uso = "nao" if citou else "todas"
            elif em >= cheias:
                uso = "todas"
            elif len(em) > 1:
                cores = {self._cor_da_linha(self.linhas[i]) for i in em if i < len(self.linhas)}
                cor = next(iter(cores)) if len(cores) == 1 else None
                mesmas = {i for i, l in enumerate(self.linhas) if cor and self._cor_da_linha(l) == cor}
                uso = f"cor:{cor}" if cor and em == mesmas else tuple(self.linhas[i] for i in sorted(em) if i < len(self.linhas))
            else:
                i = next(iter(em))
                uso = self.linhas[i] if i < len(self.linhas) and len(cheias) > 1 else "todas"
            self.fotos.append(FotoEd(p, uso=uso))

    @staticmethod
    def _cor_da_linha(l):
        """Em "Tamanho e cor" a variação se chama "25 cm · Preto": devolve "Preto"."""
        partes = [x.strip() for x in l.rotulo().split("·") if x.strip() and not re.match(r"(?i)kit\b", x.strip())]
        return partes[-1] if len(partes) > 1 else None

    def _cores(self):
        return list(dict.fromkeys(c for c in (self._cor_da_linha(l) for l in self.linhas) if c))

    def _opcoes_uso(self):
        return (["Todas as variações"] + [f"Só a cor: {c}" for c in self._cores()]
                + [f"Só: {l.rotulo()}" for l in self.linhas] + ["Escolher variações…", "Não usar"])

    def _uso_txt(self, f):
        if f.uso == "todas":
            return "Todas as variações"
        if f.uso == "nao":
            return "Não usar"
        if isinstance(f.uso, str) and f.uso.startswith("cor:"):
            return f"Só a cor: {f.uso[4:]}" if f.uso[4:] in self._cores() else "Todas as variações"
        if isinstance(f.uso, tuple):
            n = len([l for l in f.uso if l in self.linhas])
            return f"{n} variações" if n else "Todas as variações"
        return f"Só: {f.uso.rotulo()}" if f.uso in self.linhas else "Todas as variações"

    def atualizar_usos(self):
        self.desenhar_fotos()

    def desenhar_fotos(self):
        self._desenhar_fotos()
        if self.linhas:
            self._fotos_var()

    # ---- quais variações usam cada foto
    def _mini(self, caminho, lado):
        k = (str(caminho), lado)
        if k not in self._mini_cache:
            self._mini_cache[k] = miniatura(caminho, lado, 10)
            if self._mini_cache[k]:
                self.app._refs.append(self._mini_cache[k])
        return self._mini_cache[k]

    def linhas_da_foto(self, f):
        if f.removida or f.uso == "nao":
            return []
        if f.uso == "todas":
            return list(self.linhas)
        if isinstance(f.uso, str) and f.uso.startswith("cor:"):
            return [l for l in self.linhas if self._cor_da_linha(l) == f.uso[4:]] or list(self.linhas)
        return [l for l in self.linhas if f.de(l)]

    def definir_linhas(self, f, linhas):
        sel = [l for l in self.linhas if l in linhas]
        if not sel:
            f.uso = "nao"
        elif len(sel) == len(self.linhas):
            f.uso = "todas"
        else:
            f.uso = sel[0] if len(sel) == 1 else tuple(sel)

    def fotos_da_linha(self, l):
        return [f for f in self.fotos if not f.removida and l in self.linhas_da_foto(f)]

    def _desenhar_fotos(self):
        for w in self.grade_fotos.winfo_children():
            w.destroy()
        vis = [f for f in self.fotos if not f.removida]
        multi = len(self.linhas) > 1
        if hasattr(self, "modo_fotos"):
            if multi:
                self.modo_fotos.pack(side="left")
            else:
                self.modo_fotos.pack_forget()
            if multi and self.modo_fotos.get() == "Por variação" and vis:
                self.lbl_modo.configure(text="Cada linha é uma variação: toque em “Escolher fotos” e marque as fotos dela. "
                                             "Para várias de uma vez, marque as variações (caixinha) e use “Fotos das marcadas”.")
                return self._desenhar_por_variacao(vis)
            self.lbl_modo.configure(text="Arraste a ordem com ◀ ▶ (★ = capa). Em cada foto, “Variações” diz onde ela entra." if multi
                                    else "A primeira foto é a capa. Use ◀ ▶ para mudar a ordem.")
        vis = [f for f in self.fotos if not f.removida]
        if not vis:
            ctk.CTkLabel(self.grade_fotos, text="Nenhuma foto. Toque em “＋ Adicionar fotos” (pode escolher uma por vez ou várias).",
                         text_color=MUDO, font=F(13)).grid(row=0, column=0, pady=20, padx=8)
            return
        try:
            escala = ctk.ScalingTracker.get_widget_scaling(self)
        except Exception:
            escala = 1
        self.update_idletasks()
        cols = max(3, min(8, int(self.corpo.winfo_width() / (escala or 1) - 80) // 178))
        opcoes = self._opcoes_uso() if len(self.linhas) > 1 else ["Todas as variações", "Não usar"]
        for i, f in enumerate(vis):
            cel = ctk.CTkFrame(self.grade_fotos, fg_color=SUAVE, corner_radius=14, border_width=2 if i == 0 else 0, border_color=ROXO)
            cel.grid(row=i // cols, column=i % cols, padx=5, pady=5, sticky="n")
            img = miniatura(f.caminho, 132)
            self.app._refs.append(img)
            ctk.CTkLabel(cel, text="" if img else "sem prévia", image=img, width=132, height=132, text_color=MUDO).pack(padx=8, pady=(8, 2))
            if i == 0:
                Chip(cel, "capa", ROXO).place(x=12, y=12)
            if f.nova:
                Chip(cel, "nova", VERDE).place(x=12 if i else 62, y=12)
            ctk.CTkLabel(cel, text=f.caminho.name[:22], font=F(10), text_color=MUDO).pack()
            n = len(self.linhas_da_foto(f))
            if multi:
                txt = "Em todas" if f.uso == "todas" else "Não usada" if not n else (
                    f"Só: {cortar(self.linhas_da_foto(f)[0].rotulo(), 18)}" if n == 1 else f"Em {n} variações")
                Botao(cel, f"☑  {txt}", "suave" if n else "fantasma", width=150, height=28, font=F(11, "bold"),
                      text_color=ROXO_TXT if n else MUDO, command=lambda f=f: EscolherVariacoes(self, f)).pack(padx=8, pady=(4, 2))
            else:
                usada = f.uso != "nao"
                Botao(cel, "✓  Usada" if usada else "Não usada", "suave" if usada else "fantasma", width=150, height=28, font=F(11, "bold"),
                      command=lambda f=f: (setattr(f, "uso", "nao" if f.uso != "nao" else "todas"), self.desenhar_fotos())).pack(padx=8, pady=(4, 2))
            bts = ctk.CTkFrame(cel, fg_color="transparent")
            bts.pack(pady=(2, 8))
            pb = dict(width=32, height=26, font=F(12))
            dica(Botao(bts, "◀", "fantasma", command=lambda f=f: self._mover(f, -1), **pb), "Mover para a esquerda").pack(side="left", padx=1)
            dica(Botao(bts, "★", "fantasma", command=lambda f=f: self._mover(f, -999), **pb), "Usar como capa").pack(side="left", padx=1)
            dica(Botao(bts, "▶", "fantasma", command=lambda f=f: self._mover(f, 1), **pb), "Mover para a direita").pack(side="left", padx=1)
            Botao(bts, "✕", "perigo", command=lambda f=f: self._remover_foto(f), **pb).pack(side="left", padx=(6, 1))

    def _desenhar_por_variacao(self, vis):
        g = self.grade_fotos
        g.grid_columnconfigure(0, weight=1)
        bar = ctk.CTkFrame(g, fg_color="transparent")
        bar.grid(row=0, column=0, sticky="ew", pady=(0, 6))
        marc = self.marcadas_var() if hasattr(self, "lote") else []
        self.bt_fotos_marc = Botao(bar, f"☑  Fotos das marcadas ({len(marc)})", "suave", width=200, height=32, font=F(12, "bold"),
                                   command=lambda: EscolherFotos(self, self.marcadas_var()) if self.marcadas_var()
                                   else self.app.toast("Marque as variações em Variações (caixinha ou “Marcar”)", AMBAR))
        self.bt_fotos_marc.pack(side="left")
        Botao(bar, "Todas as fotos em todas", "fantasma", width=180, height=32, font=F(12),
              command=lambda: ([setattr(f, "uso", "todas") for f in vis], self.desenhar_fotos())).pack(side="left", padx=8)
        sem = [l for l in self.linhas if not self.fotos_da_linha(l)]
        if sem:
            ctk.CTkLabel(bar, text=f"{len(sem)} sem foto", font=F(12, "bold"), text_color=AMBAR).pack(side="left", padx=8)
        for r, l in enumerate(self.linhas, 1):
            suas = self.fotos_da_linha(l)
            fr = ctk.CTkFrame(g, fg_color=VIDRO, corner_radius=14, border_width=1, border_color=VIDRO_BORDA if suas else AMBAR)
            fr.grid(row=r, column=0, sticky="ew", pady=3)
            fr.grid_columnconfigure(1, weight=1)
            nome = ctk.CTkFrame(fr, fg_color="transparent", width=250)
            nome.grid(row=0, column=0, sticky="w", padx=(12, 8), pady=8)
            ctk.CTkLabel(nome, text=cortar(l.rotulo(), 34), font=F(13, "bold"), text_color=TEXTO if l.ativa else MUDO, anchor="w",
                         width=250).pack(anchor="w")
            ctk.CTkLabel(nome, text=(f"{len(suas)} foto(s)" if suas else "sem foto") + ("" if l.ativa else " · não sobe"), font=F(11),
                         text_color=MUDO if suas else AMBAR, anchor="w").pack(anchor="w")
            tira = ctk.CTkFrame(fr, fg_color="transparent")
            tira.grid(row=0, column=1, sticky="w", pady=6)
            for i, f in enumerate(suas[:9]):
                img = self._mini(f.caminho, 52)
                ctk.CTkLabel(tira, text="" if img else "foto", image=img, width=52, height=52, corner_radius=10,
                             fg_color=ROXO if i == 0 else "transparent").pack(side="left", padx=2)
            if len(suas) > 9:
                ctk.CTkLabel(tira, text=f"+{len(suas) - 9}", font=F(12, "bold"), text_color=MUDO).pack(side="left", padx=4)
            Botao(fr, "Escolher fotos", "suave", width=120, height=32, font=F(12, "bold"),
                  command=lambda l=l: EscolherFotos(self, [l])).grid(row=0, column=2, padx=12)

    def _mudar_uso(self, f, txt):
        if txt == "Todas as variações":
            f.uso = "todas"
        elif txt == "Não usar":
            f.uso = "nao"
        elif txt.startswith("Só a cor: "):
            f.uso = "cor:" + txt[len("Só a cor: "):]
        elif txt == "Escolher variações…":
            EscolherVariacoes(self, f)
        else:
            rot = txt[len("Só: "):]
            f.uso = next((l for l in self.linhas if l.rotulo() == rot), "todas")

    def _mover(self, f, passo):
        vis = [x for x in self.fotos if not x.removida]
        i = vis.index(f)
        j = 0 if passo == -999 else max(0, min(len(vis) - 1, i + passo))
        vis.insert(j, vis.pop(i))
        self.fotos = vis + [x for x in self.fotos if x.removida]
        self.desenhar_fotos()

    def _remover_foto(self, f):
        f.removida = True
        self.desenhar_fotos()

    def escolher_fotos(self):
        arqs = filedialog.askopenfilenames(parent=self, title="Fotos do produto (pode escolher várias)",
                                           filetypes=[("Fotos", "*.jpg *.jpeg *.png *.webp *.bmp *.gif *.jfif"), ("Todos", "*.*")])
        self._adicionar(arqs)

    def _adicionar(self, arqs):
        ja = {str(f.caminho.resolve()).lower() for f in self.fotos if not f.removida}
        novos = 0
        for a in arqs or []:
            p = Path(a)
            if not p.is_file() or p.suffix.lower() not in motor.FOTO_EXTS | motor.FOTO_CONVERTER:
                continue
            if str(p.resolve()).lower() in ja:
                continue
            ja.add(str(p.resolve()).lower())
            self.fotos.append(FotoEd(p, nova=True))
            novos += 1
        if novos:
            self.desenhar_fotos()
            self.app.toast(f"{novos} foto(s) adicionada(s)")

    def colar_imagem(self):
        try:
            from PIL import ImageGrab
            dado = ImageGrab.grabclipboard()
        except Exception:
            dado = None
        if isinstance(dado, list):
            self._adicionar(dado)
            return
        if dado is None or not hasattr(dado, "save"):
            messagebox.showinfo("Colar imagem", "Não tem imagem copiada. Copie uma foto (Ctrl+C) e tente de novo.")
            return
        n = len(list(self.tmp.glob("colada-*.jpg"))) + 1
        destino = self.tmp / f"colada-{n:02d}.jpg"
        dado.convert("RGB").save(destino, "JPEG", quality=92)
        self._adicionar([destino])

    # ficha técnica
    def _tecnica(self):
        s = Secao(self.corpo, "Ficha técnica", "Características que vão para o site e para o anúncio (ex.: Formato, Quantidade de furos).")
        s.grid(row=5, column=0, sticky="ew", padx=10, pady=8)
        Botao(s.acoes, "＋  Adicionar linha", "suave", width=150, command=lambda: self._linha_tec()).pack(side="right")
        self.caixa_tec = ctk.CTkFrame(s, fg_color="transparent")
        self.caixa_tec.grid(row=1, column=0, columnspan=4, sticky="ew", padx=20, pady=(4, 16))
        self.caixa_tec.grid_columnconfigure((0, 1), weight=1)
        self.tec = []
        for item in self.dados.get("ficha_tecnica") or []:
            if isinstance(item, dict):
                self._linha_tec(item.get("nome"), item.get("valor"))

    def _linha_tec(self, nome=None, valor=None):
        fr = ctk.CTkFrame(self.caixa_tec, fg_color="transparent")
        fr.pack(fill="x", pady=2)
        fr.grid_columnconfigure((0, 1), weight=1)
        a = Entrada(fr, placeholder_text="Nome (ex.: Formato)")
        a.grid(row=0, column=0, sticky="ew", padx=(0, 6))
        b = Entrada(fr, placeholder_text="Valor (ex.: Colonial)")
        b.grid(row=0, column=1, sticky="ew", padx=(0, 6))
        a.definir(nome)
        b.definir(valor)
        par = (fr, a, b)
        Botao(fr, "✕", "perigo", width=34, command=lambda: (self.tec.remove(par), fr.destroy())).grid(row=0, column=2)
        self.tec.append(par)

    def _mercado_livre(self):
        ml = self.dados.get("mercado_livre") if isinstance(self.dados.get("mercado_livre"), dict) else {}
        s = Secao(self.corpo, "Mercado Livre", "Só referência: o robô não publica nada no Mercado Livre.")
        s.grid(row=6, column=0, sticky="ew", padx=10, pady=(8, 20))
        self.bt_textos = Botao(s.acoes, "✦  Escrever título e descrição", "suave", width=230, command=self.gerar_textos)
        self.bt_textos.pack(side="right")
        self.ml_titulo = s.campo("Título do anúncio", Entrada(s, ao_mudar=self._contar_titulo, placeholder_text="até 60 letras"), 0, 3)
        self.ml_titulo.definir(ml.get("titulo_anuncio"))
        self.lbl_tit = ctk.CTkLabel(s, text="", font=F(11), text_color=MUDO)
        self.lbl_tit.grid(row=s.linha, column=3, sticky="e", padx=20)
        s.fim_linha()
        self.ml_link = s.campo("Anúncio de referência", Entrada(s, placeholder_text="https://produto.mercadolivre.com.br/MLB-…"), 0, 3)
        self.ml_link.definir(ml.get("link_referencia"))
        self.ml_tipo = ctk.CTkSegmentedButton(s, values=["Clássico", "Premium"], selected_color=ROXO, selected_hover_color=ROXO_H,
                                              font=F(12, "bold"), height=32)
        self.ml_tipo.set("Premium" if str(ml.get("tipo_anuncio") or "").lower().startswith("prem") else "Clássico")
        s.campo("Tipo", self.ml_tipo, 0)
        self.ml_qtd = s.campo("Qtd. no anúncio", Entrada(s, "inteiro"), 2)
        self.ml_qtd.definir(ml.get("quantidade_anuncio") if ml.get("quantidade_anuncio") not in (None, "") else 10)
        s.fim_linha()
        ctk.CTkLabel(s, text="").grid(row=s.linha, column=0, pady=4)
        self._contar_titulo()

    def _contar_titulo(self):
        n = len(self.ml_titulo.get().strip())
        self.lbl_tit.configure(text=f"{n} / 60", text_color=VERMELHO if n > 60 else MUDO)

    # salvar
    def montar(self):
        d = self.dados
        d["nome"] = self.nome.valor()
        d["codigo"] = self.codigo.valor()
        d["categoria"] = self.categoria.get().strip()
        d["marca"] = self.marca.valor() or "Móveis Stylo"
        d["modelo"] = self.modelo.valor() or None
        d["material"] = self.material.valor()
        d["acabamento"] = self.acabamento.valor()
        d["montagem"] = self.montagem.get().strip()
        d["o_que_vem_na_caixa"] = self.caixa.valor()
        d["carga_maxima_kg"] = self.carga.valor()
        d["garantia_meses"] = self.garantia.valor()
        d["codigo_barras"] = self.ean.valor() or None
        d["condicao"] = "usado" if self.condicao.get() == "Usado" else "novo"
        d["uso"] = self.uso.valor()
        d["descricao"] = self.desc.get("1.0", "end").strip()
        d["sob_encomenda"] = bool(self.sob.get())
        d["prazo_dias"] = self.prazo.valor() if self.sob.get() else None
        por = self.var_por.get()
        d["variacao_por"] = None if por == "Sem variação" else por
        d["ficha_tecnica"] = [{"nome": a.valor(), "valor": b.valor()} for _, a, b in self.tec if a.valor() and b.valor()]
        d["ficha_de_custo"] = [{"item": a.valor(), "quantidade": b.valor() if b.valor() is not None else 1, "valor": c.valor()}
                               for _, a, b, c, t in self.custos if a.valor() or c.valor()]
        ml = dict(d.get("mercado_livre") or {}) if isinstance(d.get("mercado_livre"), dict) else {}
        ml.update({"titulo_anuncio": self.ml_titulo.valor(), "link_referencia": self.ml_link.valor() or None,
                   "tipo_anuncio": "premium" if self.ml_tipo.get() == "Premium" else "classico",
                   "quantidade_anuncio": self.ml_qtd.valor() or 10})
        d["mercado_livre"] = ml
        d["variacoes"] = [l.dados() for l in self.linhas]
        if getattr(self, "_marc_cores", None):
            d["cores_marcadas"] = self._marc_cores      # só para lembrar o que foi marcado em Escolher cores
        else:
            d.pop("cores_marcadas", None)
        return d

    def salvar(self):
        d = self.montar()
        if not d["nome"]:
            self.lbl_erro.configure(text="Falta o nome do produto.")
            self.nome.focus_set()
            return
        if len(self.linhas) == 1 and d["variacao_por"] is None:
            d["variacoes"][0]["valor"] = None
        try:
            pasta = self.ficha.pasta if self.ficha else motor.nova_pasta(self.app.pasta(), d)
            # fotos: copia as novas, tira as removidas, monta a lista de cada variação
            for f in self.fotos:
                if f.removida and not f.nova:
                    motor.remover_foto(pasta, f.caminho.name)
            vis = [f for f in self.fotos if not f.removida]
            for f in vis:
                if f.nova:
                    nome = motor.adicionar_fotos(pasta, [f.caminho])
                    if nome:
                        f.caminho, f.nova = pasta / nome[0], False
            gerais = [f.caminho.name for f in vis if f.uso == "todas"]
            cores = self._cores()
            de_cor = lambda f: isinstance(f.uso, str) and f.uso.startswith("cor:") and f.uso[4:] in cores
            tem_propria = any(f.uso in self.linhas or isinstance(f.uso, tuple) or de_cor(f) for f in vis)
            for l, v in zip(self.linhas, d["variacoes"]):
                # mantém a ordem da tela: a primeira foto da variação vira a capa dela
                v["fotos"] = [f.caminho.name for f in vis if f.uso == "todas" or f.de(l)
                              or (de_cor(f) and f.uso[4:] == self._cor_da_linha(l))] if tem_propria else []
            if not tem_propria and d["variacoes"]:
                d["variacoes"][0]["fotos"] = gerais
            ficha = self.ficha or motor.Ficha(pasta=pasta)
            motor.salvar_ficha(ficha, d)
        except Exception as e:
            self.lbl_erro.configure(text=f"Não salvou: {e}")
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")
            return
        self.app.toast("Ficha salva")
        ja_no_site = self.ficha is not None and self.ficha.status == "feita"
        self.app.fechar_editor(pasta)
        if ja_no_site:
            self.app.atualizar_no_site([motor.ler_feita(pasta)])

    def ir_secao(self, sec):
        self.update_idletasks()
        alto = max(self.corpo.winfo_height(), 1)
        self.corpo._parent_canvas.yview_moveto(max(sec.winfo_y() - 8, 0) / alto)
        try:
            sec.configure(border_color=ROXO)
            self.after(1200, lambda: sec.winfo_exists() and sec.configure(border_color=BORDA))
        except Exception:
            pass

    def _assinatura(self):
        try:
            d = json.loads(json.dumps(self.montar(), default=str))
            fotos = [(str(f.caminho), f.removida, f.uso if isinstance(f.uso, str) else
                      [self.linhas.index(x) for x in f.uso if x in self.linhas] if isinstance(f.uso, tuple) else
                      self.linhas.index(f.uso) if f.uso in self.linhas else -1) for f in self.fotos]
            return json.dumps([d, fotos], sort_keys=True, default=str)
        except Exception:
            return None

    def cancelar(self):
        pasta = self.ficha.pasta if self.ficha else None
        if self._inicial is not None and self._assinatura() == self._inicial:
            self.app.fechar_editor(pasta)
            return
        r = messagebox.askyesnocancel("Salvar as mudanças?", "Você mudou a ficha. Quer salvar antes de sair?\n\n"
                                      "Sim = salvar  ·  Não = sair sem salvar  ·  Cancelar = continuar editando")
        if r is True:
            self.salvar()
        elif r is False:
            self.app.fechar_editor(pasta)

    def destroy(self):
        import shutil
        shutil.rmtree(self.tmp, ignore_errors=True)
        super().destroy()


# ------------------------------------------------------------------ janelas pequenas (lote, kits, etiquetas, fotos)

FOTOS_CONC = motor.BASE / "concorrentes-fotos"


def foto_concorrente(link):
    p = FOTOS_CONC / (recursos._chave_link(link) + ".jpg")
    return p if p.exists() else None


class Dialogo(ctk.CTkToplevel):
    """Janela por cima da principal, com título, subtítulo e botões no rodapé."""

    def __init__(self, app, titulo, sub="", larg=620, alt=520):
        super().__init__(app)
        self.app = app
        self.title(titulo)
        self.configure(fg_color=FUNDO)
        alt = min(alt, app.winfo_height() - 40) if app.winfo_height() > 200 else alt
        self.geometry(f"{larg}x{alt}+{app.winfo_rootx() + max((app.winfo_width() - larg) // 2, 20)}+{app.winfo_rooty() + 40}")
        self.transient(app)
        self.after(150, self._prender)
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=1)
        cab = ctk.CTkFrame(self, fg_color="transparent")
        cab.grid(row=0, column=0, sticky="ew", padx=22, pady=(18, 8))
        cab.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(cab, text=titulo, font=F(21, "bold"), text_color=TEXTO, anchor="w").grid(row=0, column=0, sticky="w")
        if sub:
            ctk.CTkLabel(cab, text=sub, font=F(12), text_color=MUDO, anchor="w", justify="left", wraplength=larg - 60).grid(
                row=1, column=0, sticky="w")
        self.corpo = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        self.corpo.grid(row=1, column=0, sticky="nsew", padx=22)
        self.corpo.grid_columnconfigure(1, weight=1)
        self.rod = ctk.CTkFrame(self, fg_color="transparent")
        self.rod.grid(row=2, column=0, sticky="ew", padx=22, pady=14)
        self.rod.grid_columnconfigure(0, weight=1)
        self.lbl_rod = ctk.CTkLabel(self.rod, text="", font=F(12), text_color=MUDO, anchor="w", justify="left", wraplength=larg - 300)
        self.lbl_rod.grid(row=0, column=0, sticky="w")
        Botao(self.rod, "Cancelar", "fantasma", width=96, command=self.destroy).grid(row=0, column=1, padx=6)
        self.linha = 0

    def _prender(self):
        try:
            self.grab_set()
            self.lift()
        except Exception:
            pass

    def botao_ok(self, texto, comando):
        self.bt_ok = Botao(self.rod, texto, "primario", width=170, height=38, command=comando)
        self.bt_ok.grid(row=0, column=2)
        return self.bt_ok

    def campo(self, rotulo, widget, ajuda=""):
        ctk.CTkLabel(self.corpo, text=rotulo, font=F(12, "bold"), text_color=MUDO, anchor="w").grid(
            row=self.linha, column=0, sticky="nw", padx=(18, 10), pady=(14 if self.linha == 0 else 8, 0))
        widget.grid(row=self.linha, column=1, sticky="w", padx=(0, 18), pady=(14 if self.linha == 0 else 8, 0))
        self.linha += 1
        if ajuda:
            ctk.CTkLabel(self.corpo, text=ajuda, font=F(11), text_color=MUDO, anchor="w", justify="left").grid(
                row=self.linha, column=1, sticky="w", padx=(0, 18))
            self.linha += 1
        return widget

    def caixa_info(self):
        lbl = ctk.CTkLabel(self.corpo, text="", font=F(13), text_color=ROXO_TXT, fg_color=ROXO_CLARO, corner_radius=12,
                           anchor="w", justify="left", padx=14, pady=10)
        lbl.grid(row=self.linha, column=0, columnspan=2, sticky="ew", padx=18, pady=(14, 16))
        self.linha += 1
        return lbl


def _seg(master, valores, **kw):
    s = ctk.CTkSegmentedButton(master, values=valores, height=32, selected_color=ROXO, selected_hover_color=ROXO_H,
                               font=F(12, "bold"), unselected_color=SUAVE, unselected_hover_color=CARD_H, fg_color=SUAVE,
                               text_color=TEXTO, **kw)
    s.set(valores[0])
    return s


def _lista_nomes(fichas, n=4):
    nomes = [f.nome for f in fichas[:n]]
    return ", ".join(cortar(x, 40) for x in nomes) + (f" e mais {len(fichas) - n}" if len(fichas) > n else "")


class Lote(Dialogo):
    """Muda preço, custo, estoque ou prazo de várias fichas de uma vez."""

    def __init__(self, app, fichas):
        fichas = [f for f in fichas if f.status != "feita"]
        super().__init__(app, "Atualizar em lote",
                         f"{len(fichas)} ficha(s): {_lista_nomes(fichas)}.\nMuda só as fichas do robô; no site use Produtos › Edição em massa.",
                         larg=640, alt=470)
        self.fichas = fichas
        self.campo_m = self.campo("O que mudar", _seg(self.corpo, list(recursos.CAMPOS_LOTE), command=lambda v: self.previa()))
        self.op = self.campo("Como", ctk.CTkOptionMenu(self.corpo, values=recursos.OPERACOES, width=200, height=32, font=F(12),
                                                       fg_color=SUAVE, button_color=ROXO_CLARO, text_color=TEXTO,
                                                       command=lambda v: self.previa()))
        self.op.set("Aumentar %")
        self.valor = self.campo("Valor", Entrada(self.corpo, "decimal", width=120, ao_mudar=self.previa, placeholder_text="ex.: 10"),
                                "% para aumentar ou diminuir; reais ou peças para somar, tirar ou definir.")
        self.redondo = ctk.CTkSwitch(self.corpo, text="Terminar o preço em ,90 (ex.: 52,90)", progress_color=ROXO, font=F(12),
                                     text_color=TEXTO, command=self.previa)
        self.redondo.select()
        self.campo("", self.redondo)
        self.info = self.caixa_info()
        self.botao_ok("Aplicar", self.aplicar)
        if not fichas:
            self.bt_ok.configure(state="disabled")
        self.previa()

    def _ler(self):
        campo = recursos.CAMPOS_LOTE[self.campo_m.get()]
        return campo, self.op.get(), self.valor.valor(), bool(self.redondo.get())

    def previa(self, *_):
        campo, op, valor, redondo = self._ler()
        if valor is None:
            self.info.configure(text="Digite o valor para ver como fica.")
            return
        exemplos = []
        for f in self.fichas[:3]:
            if campo == "prazo_dias":
                antes = f.dados.get("prazo_dias")
                depois = int(round(recursos._aplicar(antes, op, valor)))
                exemplos.append(f"{cortar(f.nome, 34)}: {antes or 0} → {depois} dias")
                continue
            v = next((x for x in f.variacoes if x.get(campo) not in (None, "")), None)
            if v is None:
                continue
            novo = recursos._aplicar(v.get(campo), op, valor)
            if campo == "estoque":
                a, b = str(int(motor.numero(v.get(campo)))), str(int(round(max(novo, 0))))
            else:
                novo = round(max(novo, 0), 2)
                if redondo and campo in ("venda", "preco_tabela") and novo > 0:
                    novo = recursos.arredondar_90(novo)
                a, b = "R$ " + reais(motor.numero(v.get(campo))), "R$ " + reais(novo)
            rot = f"{cortar(f.nome, 30)}" + (f" {v.get('valor')}" if v.get("valor") else "")
            exemplos.append(f"{rot}: {a} → {b}")
        self.info.configure(text="Exemplo:\n" + "\n".join(exemplos) if exemplos else "Essas fichas não têm esse campo preenchido.")

    def aplicar(self):
        campo, op, valor, redondo = self._ler()
        if valor is None:
            self.valor.focus_set()
            return
        if not messagebox.askyesno("Atualizar em lote", f"Mudar {self.campo_m.get().lower()} de {len(self.fichas)} ficha(s)?", parent=self):
            return
        n = recursos.atualizar_lote(self.fichas, campo, op, valor, redondo)
        self.destroy()
        self.app.recarregar()
        self.app.toast(f"{n} item(ns) atualizado(s)")


class Kits(Dialogo):
    """Cria fichas de kit (2, 3, 4 unidades) a partir de uma ficha."""

    def __init__(self, app, ficha):
        super().__init__(app, "Criar kits", f"A partir de “{cortar(ficha.nome, 60)}”. Cada kit vira uma ficha nova, com código -K2, -K3…",
                         larg=620, alt=470)
        self.ficha = ficha
        self.qtds = self.campo("Quantidades", Entrada(self.corpo, width=200, ao_mudar=self.previa, placeholder_text="ex.: 2, 3, 4"),
                               "Separe por vírgula. Um kit de cada quantidade.")
        self.qtds.insert(0, "2, 4")
        self.desc = self.campo("Desconto do kit %", Entrada(self.corpo, "decimal", width=90, ao_mudar=self.previa, placeholder_text="0"),
                               "Ex.: 5 = o kit sai 5% mais barato que comprar as peças separadas.")
        self.desc.insert(0, "5")
        self.redondo = ctk.CTkSwitch(self.corpo, text="Terminar o preço em ,90", progress_color=ROXO, font=F(12), text_color=TEXTO,
                                     command=self.previa)
        self.redondo.select()
        self.campo("", self.redondo)
        self.info = self.caixa_info()
        self.botao_ok("Criar kits", self.criar)
        self.previa()

    def _qtds(self):
        return sorted({int(x) for x in re.findall(r"\d+", self.qtds.get()) if 2 <= int(x) <= 50})

    def previa(self, *_):
        v = next((x for x in self.ficha.variacoes if motor.numero(x.get("venda")) > 0), None)
        qtds = self._qtds()
        if not qtds:
            self.info.configure(text="Digite pelo menos uma quantidade de 2 para cima.")
            return
        if v is None:
            self.info.configure(text="A ficha não tem preço de venda.")
            return
        unit = motor.numero(v.get("venda"))
        d = self.desc.valor() or 0
        linhas = []
        for n in qtds:
            bruto = unit * n * (1 - d / 100)
            p = recursos.arredondar_90(bruto) if self.redondo.get() else round(bruto, 2)
            est = int(motor.numero(v.get("estoque"))) // n
            linhas.append(f"Kit {n}:  R$ {reais(p)}   (cada peça sai R$ {reais(p / n)}, estoque {est})")
        rot = f" ({v.get('valor')})" if v.get("valor") else ""
        self.info.configure(text=f"Peça avulsa{rot}: R$ {reais(unit)}\n" + "\n".join(linhas) +
                                 ("\nO mesmo vale para as outras variações." if len(self.ficha.variacoes) > 1 else ""))

    def criar(self):
        qtds = self._qtds()
        if not qtds:
            return
        pastas = recursos.criar_kits(self.ficha, self.app.pasta(), qtds, self.desc.valor() or 0, bool(self.redondo.get()))
        self.destroy()
        self.app.recarregar(selecionar=pastas[0] if pastas else None)
        self.app.toast(f"{len(pastas)} kit(s) criado(s). Confira o peso e a caixa.")


class Etiquetas(Dialogo):
    """PDF de etiquetas (A4, 24 por folha) com nome, medida, código de barras e preço."""

    def __init__(self, app, fichas):
        super().__init__(app, "Etiquetas", f"{len(fichas)} ficha(s): {_lista_nomes(fichas)}.\n"
                                           "Folha A4 com 24 etiquetas (3 × 8, 70 × 37 mm, tipo Pimaco A4356).", larg=620, alt=440)
        self.fichas = fichas
        self.por_estoque = ctk.CTkSwitch(self.corpo, text="Uma etiqueta para cada peça em estoque", progress_color=ROXO, font=F(12),
                                         text_color=TEXTO, command=self.previa)
        self.por_estoque.select()
        self.campo("Quantidade", self.por_estoque, "Desligado: uma etiqueta de cada variação.")
        self.preco = ctk.CTkSwitch(self.corpo, text="Mostrar o preço de venda", progress_color=ROXO, font=F(12), text_color=TEXTO)
        self.preco.select()
        self.campo("Preço", self.preco)
        self.info = self.caixa_info()
        self.botao_ok("Gerar PDF", self.gerar)
        self.previa()

    def previa(self):
        itens = recursos.itens_etiqueta(self.fichas, bool(self.por_estoque.get()))
        n = sum(i["qtd"] for i in itens)
        folhas = (n + 23) // 24
        sem_cod = sum(1 for i in itens if not i["codigo"])
        txt = f"{n} etiqueta(s) em {folhas} folha(s)." if n else "Nenhuma etiqueta: o estoque está 0. Desligue a primeira opção."
        if sem_cod:
            txt += f"\n{sem_cod} item(ns) sem código: saem sem código de barras."
        self.info.configure(text=txt)
        if hasattr(self, "bt_ok"):
            self.bt_ok.configure(state="normal" if n else "disabled")

    def gerar(self):
        from datetime import datetime
        itens = recursos.itens_etiqueta(self.fichas, bool(self.por_estoque.get()))
        destino = motor.relatorio_path().parent / f"etiquetas-{datetime.now():%Y-%m-%d-%H%M%S}.pdf"
        try:
            arq, n = recursos.gerar_etiquetas(itens, destino, bool(self.preco.get()))
        except Exception as e:
            messagebox.showerror("Etiquetas", str(e), parent=self)
            return
        self.destroy()
        abrir_no_sistema(arq)
        self.app.toast(f"{n} etiqueta(s) prontas no PDF")


class FotosProntas(Dialogo):
    """Deixa as fotos da ficha prontas para anúncio (fundo branco, quadradas, realce, marca d'água)."""

    def __init__(self, editor):
        app = editor.app
        super().__init__(app, "Deixar fotos prontas", "As originais não se perdem: ao salvar, vão para a subpasta _removidas da ficha.",
                         larg=960, alt=600)
        self.editor = editor
        self.fotos = [f for f in editor.fotos if not f.removida]
        esq = ctk.CTkFrame(self.corpo, fg_color="transparent")
        esq.grid(row=0, column=0, sticky="nw", padx=18, pady=16)
        sw = dict(progress_color=ROXO, font=F(13), text_color=TEXTO)
        self.quadrada = ctk.CTkSwitch(esq, text="Quadrada (1200 × 1200)", command=self.previa, **sw)
        self.quadrada.select()
        self.fundo = ctk.CTkSwitch(esq, text="Fundo claro vira branco", command=self.previa, **sw)
        self.fundo.select()
        self.realce = ctk.CTkSwitch(esq, text="Realçar (contraste e nitidez)", command=self.previa, **sw)
        self.marca_on = ctk.CTkSwitch(esq, text="Marca d'água", command=self.previa, **sw)
        for w in (self.quadrada, self.fundo, self.realce, self.marca_on):
            w.pack(anchor="w", pady=6)
        self.marca = Entrada(esq, width=220, placeholder_text="Móveis Stylo")
        self.marca.insert(0, app.cfg["robo"].get("marca_dagua") or "Móveis Stylo")
        self.marca.bind("<FocusOut>", lambda e: self.previa(), add="+")
        self.marca.pack(anchor="w", padx=(46, 0), pady=(0, 8))
        ctk.CTkLabel(esq, text="Tolerância do fundo", font=F(12, "bold"), text_color=MUDO).pack(anchor="w", pady=(10, 0))
        self.tol = ctk.CTkSlider(esq, from_=10, to=90, number_of_steps=16, width=220, button_color=ROXO, progress_color=ROXO,
                                 command=lambda v: self._agendar())
        self.tol.set(40)
        self.tol.pack(anchor="w", pady=4)
        ctk.CTkLabel(esq, text="Mais alto = apaga mais do fundo.\nFunciona melhor com foto em fundo liso e claro.",
                     font=F(11), text_color=MUDO, justify="left").pack(anchor="w")
        ctk.CTkLabel(esq, text=f"Vale para as {len(self.fotos)} foto(s) da ficha.", font=F(12, "bold"), text_color=ROXO_TXT).pack(anchor="w", pady=(16, 0))
        dir_ = ctk.CTkFrame(self.corpo, fg_color="transparent")
        dir_.grid(row=0, column=1, sticky="n", padx=(0, 18), pady=16)
        ctk.CTkLabel(dir_, text="ANTES", font=F(10, "bold"), text_color=MUDO).grid(row=0, column=0)
        ctk.CTkLabel(dir_, text="DEPOIS", font=F(10, "bold"), text_color=MUDO).grid(row=0, column=1)
        self.antes = ctk.CTkLabel(dir_, text="", width=230, height=230, fg_color=SUAVE, corner_radius=14)
        self.antes.grid(row=1, column=0, padx=6, pady=4)
        self.depois = ctk.CTkLabel(dir_, text="", width=230, height=230, fg_color=SUAVE, corner_radius=14)
        self.depois.grid(row=1, column=1, padx=6, pady=4)
        nav = ctk.CTkFrame(dir_, fg_color="transparent")
        nav.grid(row=2, column=0, columnspan=2, pady=6)
        self.i = 0
        Botao(nav, "◀", "fantasma", width=36, height=28, command=lambda: self._ir(-1)).pack(side="left", padx=4)
        self.lbl_i = ctk.CTkLabel(nav, text="", font=F(12), text_color=MUDO, width=90)
        self.lbl_i.pack(side="left")
        Botao(nav, "▶", "fantasma", width=36, height=28, command=lambda: self._ir(1)).pack(side="left", padx=4)
        self.botao_ok(f"Aplicar em {len(self.fotos)} foto(s)", self.aplicar)
        if not self.fotos or Image is None:
            self.bt_ok.configure(state="disabled")
        self._job = None
        self.after(250, self.previa)

    def _ir(self, passo):
        if self.fotos:
            self.i = (self.i + passo) % len(self.fotos)
            self.previa()

    def _agendar(self):
        if self._job:
            self.after_cancel(self._job)
        self._job = self.after(250, self.previa)

    def _opcoes(self):
        return dict(quadrada=bool(self.quadrada.get()), fundo_branco=bool(self.fundo.get()), tolerancia=int(self.tol.get()),
                    realcar=bool(self.realce.get()), marca=self.marca.get().strip() if self.marca_on.get() else "")

    def previa(self):
        self._job = None
        if not self.fotos or Image is None:
            return
        f = self.fotos[self.i]
        self.lbl_i.configure(text=f"foto {self.i + 1} de {len(self.fotos)}")
        img = miniatura(f.caminho, 230, 14)
        self.antes.configure(image=img, text="" if img else "sem prévia")
        self.app._refs.append(img)
        destino = self.editor.tmp / f"_previa-{self.i}.jpg"
        try:
            out = recursos.tratar_foto(f.caminho, destino, lado=700, **self._opcoes())
            img2 = miniatura(out.read_bytes(), 230, 14)
        except Exception:
            img2 = None
        self.depois.configure(image=img2, text="" if img2 else "não deu para tratar")
        self.app._refs.append(img2)

    def aplicar(self):
        op = self._opcoes()
        self.bt_ok.configure(state="disabled", text="Tratando…")
        self.update()
        novas, falhas = [], 0
        for n, f in enumerate(self.fotos, 1):
            destino = self.editor.tmp / f"{f.caminho.stem}-pronta.jpg"
            try:
                out = recursos.tratar_foto(f.caminho, destino, **op)
            except Exception:
                falhas += 1
                novas.append(f)
                continue
            f.removida = True
            novas.append(FotoEd(out, nova=True, uso=f.uso))
        resto = [f for f in self.editor.fotos if f.removida]
        self.editor.fotos = novas + [f for f in resto if f not in novas]
        self.editor.desenhar_fotos()
        self.destroy()
        self.app.toast("Fotos prontas. Toque em Salvar ficha para guardar." if not falhas else f"{falhas} foto(s) não deu para tratar",
                       VERDE if not falhas else AMBAR)


class PaginaMateriais(ctk.CTkFrame):
    """Tabela de materiais e horas: preço por metro, m², kg, unidade ou hora. Usada na ficha de custo."""

    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.itens = recursos.ler_materiais()
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(2, weight=1)
        Cabecalho(self, "Materiais e custos", "Seus preços de material e de hora. Os que vieram prontos são estimativas da internet: "
                  "confira com o seu fornecedor e mude o preço.").grid(row=0, column=0, sticky="ew", padx=24, pady=(20, 8))
        bar = ctk.CTkFrame(self, fg_color="transparent")
        bar.grid(row=1, column=0, sticky="ew", padx=24, pady=(0, 8))
        bar.grid_columnconfigure(0, weight=1)
        self.busca = Entrada(bar, height=36, placeholder_text="⌕  Buscar material (ex.: 7/8, espuma, corano)")
        self.busca.grid(row=0, column=0, sticky="ew")
        self.busca.bind("<KeyRelease>", lambda e: self._agendar())
        self.cat = ctk.CTkOptionMenu(bar, values=["Todas"], width=230, height=36, font=F(12), dropdown_font=F(12), fg_color=PAINEL,
                                     button_color=ROXO_CLARO, button_hover_color=("#ddd6fe", "#3a2f68"), text_color=TEXTO,
                                     command=lambda v: self.desenhar())
        self.cat.grid(row=0, column=1, padx=8)
        Botao(bar, "＋  Novo material", "primario", width=150, height=36, command=self.novo).grid(row=0, column=2)
        self.lbl = ctk.CTkLabel(bar, text="", font=F(11), text_color=MUDO, anchor="w")
        self.lbl.grid(row=1, column=0, columnspan=3, sticky="w", pady=(4, 0))
        self.lista = ctk.CTkScrollableFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        self.lista.grid(row=2, column=0, sticky="nsew", padx=24, pady=(0, 14))
        self.lista.grid_columnconfigure(0, weight=1)
        self._id = None
        self.ok = False

    def ao_mostrar(self):
        if not self.ok:
            self.ok = True
            self.desenhar()

    def _agendar(self):
        if self._id:
            self.after_cancel(self._id)
        self._id = self.after(250, self.desenhar)

    def _cats(self):
        return list(dict.fromkeys(i.get("cat") or "Outros" for i in self.itens))

    def salvar(self):
        try:
            recursos.salvar_materiais(self.itens)
        except Exception as e:
            messagebox.showerror("Materiais", f"Não deu para salvar: {e}")

    def desenhar(self, foco=None):
        for w in self.lista.winfo_children():
            w.destroy()
        cats = self._cats()
        self.cat.configure(values=["Todas"] + cats)
        termo = self.busca.get().strip()
        filtro = self.cat.get()
        r, mostrados = 0, 0
        for cat in cats:
            if filtro != "Todas" and cat != filtro:
                continue
            itens = [i for i in self.itens if (i.get("cat") or "Outros") == cat]
            if termo:
                itens = [i for i in itens if all(p in (i["nome"] + " " + cat).lower() for p in self.busca.get().lower().split())]
            if not itens:
                continue
            cab = ctk.CTkFrame(self.lista, fg_color="transparent")
            cab.grid(row=r, column=0, sticky="ew", padx=14, pady=(14, 4))
            ctk.CTkLabel(cab, text=cat, font=F(15, "bold"), text_color=TEXTO).pack(side="left")
            ctk.CTkLabel(cab, text=f"  {len(itens)}", font=F(12), text_color=MUDO).pack(side="left")
            Botao(cab, "＋ aqui", "fantasma", width=70, height=26, font=F(11), command=lambda c=cat: self.novo(c)).pack(side="right")
            r += 1
            for i in itens:
                self._linha(r, i, foco is i)
                r += 1
                mostrados += 1
        est = sum(1 for i in self.itens if i.get("est"))
        self.lbl.configure(text=f"{len(self.itens)} itens · {est} ainda com preço estimado (marcados “estimado”) · "
                                "as mudanças já ficam salvas")
        if not mostrados:
            ctk.CTkLabel(self.lista, text="Nada encontrado.", text_color=MUDO, font=F(13)).grid(row=0, column=0, pady=30)

    def _linha(self, r, i, foco=False):
        fr = ctk.CTkFrame(self.lista, fg_color=VIDRO, corner_radius=12)
        fr.grid(row=r, column=0, sticky="ew", padx=10, pady=2)
        fr.grid_columnconfigure(0, weight=1)
        nome = Entrada(fr, height=32, font=F(13), fg_color=PAINEL, border_color=VIDRO_BORDA)
        nome.definir(i["nome"])
        nome.grid(row=0, column=0, sticky="ew", padx=(8, 6), pady=6)
        ctk.CTkLabel(fr, text="R$", font=F(12, "bold"), text_color=MUDO).grid(row=0, column=1)
        preco = Entrada(fr, "dinheiro", width=92, height=32, font=F(13, "bold"), fg_color=PAINEL, border_color=VIDRO_BORDA)
        preco.definir(i.get("preco"))
        preco.grid(row=0, column=2, padx=(4, 4))
        ctk.CTkLabel(fr, text="por", font=F(12), text_color=MUDO).grid(row=0, column=3)
        unid = ctk.CTkOptionMenu(fr, values=recursos.UNIDADES, width=104, height=32, font=F(12), dropdown_font=F(12), fg_color=PAINEL,
                                 button_color=ROXO_CLARO, button_hover_color=("#ddd6fe", "#3a2f68"), text_color=TEXTO)
        unid.set(i.get("unid") or "un")
        unid.grid(row=0, column=4, padx=(4, 6))
        nota = Entrada(fr, width=220, height=32, font=F(11), placeholder_text="observação (ex.: barra de 6 m)", fg_color=PAINEL,
                       border_color=VIDRO_BORDA)
        nota.definir(i.get("nota") or None)
        nota.grid(row=0, column=5, padx=(0, 6))
        chip = ctk.CTkLabel(fr, text="estimado" if i.get("est") else "", font=F(10, "bold"), width=70, corner_radius=8,
                            fg_color=("#fef3c7", "#3a2a0a") if i.get("est") else "transparent", text_color=("#b45309", "#fcd34d"))
        chip.grid(row=0, column=6, padx=(0, 4))
        Botao(fr, "✕", "perigo", width=30, height=30, command=lambda: self.excluir(i)).grid(row=0, column=7, padx=(0, 8))

        def mudou(e=None):
            novo_preco = preco.valor()
            mudou_preco = novo_preco is not None and abs(float(novo_preco) - float(i.get("preco") or 0)) > 0.004
            antes = (i["nome"], i.get("unid"), i.get("nota") or "", i.get("preco"))
            i["nome"] = nome.get().strip() or i["nome"]
            i["unid"] = unid.get()
            i["nota"] = nota.get().strip()
            if novo_preco is not None:
                i["preco"] = round(float(novo_preco), 2)
            if mudou_preco and i.get("est"):
                i["est"] = False
                chip.configure(text="", fg_color="transparent")
            if antes != (i["nome"], i.get("unid"), i.get("nota") or "", i.get("preco")):
                self.salvar()
        for w in (nome, preco, nota):
            w.bind("<FocusOut>", mudou, add="+")
            w.bind("<Return>", mudou, add="+")
        unid.configure(command=lambda v: mudou())
        if foco:
            nome.focus_set()
            nome.select_range(0, "end")

    def novo(self, cat=None):
        cat = cat if isinstance(cat, str) else (self.cat.get() if self.cat.get() != "Todas" else "Outros")
        i = {"cat": cat, "nome": "Material novo", "unid": "un", "preco": 0.0, "nota": "", "est": False}
        pos = max((n for n, x in enumerate(self.itens) if x.get("cat") == cat), default=len(self.itens) - 1) + 1
        self.itens.insert(pos, i)
        self.salvar()
        self.busca.definir(None)
        self.cat.set(cat)          # mostra só o grupo dele, com o novo no fim
        self.desenhar(foco=i)
        self.after(120, lambda: self.lista._parent_canvas.yview_moveto(1.0))

    def excluir(self, i):
        if not messagebox.askyesno("Materiais", f"Excluir “{i['nome']}” da tabela?", parent=self):
            return
        self.itens.remove(i)
        self.salvar()
        self.desenhar()


class CustoVariacao(Dialogo):
    """Ficha de custo de UMA variação (ex.: a banqueta de 75 cm gasta mais tubo que a de 54 cm)."""

    def __init__(self, linha, outras=None):
        self.lv, self.editor = linha, linha.editor
        self.alvos = [linha] + [l for l in (outras or []) if l is not linha]
        titulo = f"Custo de: {linha.rotulo()}" if len(self.alvos) == 1 else f"Custo de {len(self.alvos)} variações marcadas"
        super().__init__(linha.editor.app, titulo, "Monte o custo. O total vira o custo " +
                         ("desta variação." if len(self.alvos) == 1 else "de todas as variações marcadas."), larg=800, alt=600)
        self.corpo.grid_rowconfigure(2, weight=1)
        bar = ctk.CTkFrame(self.corpo, fg_color="transparent")
        bar.grid(row=0, column=0, columnspan=2, sticky="ew", padx=16, pady=(14, 6))
        Botao(bar, "▦  Da tabela de materiais", "suave", width=190, command=lambda: EscolherMaterial(self.editor, self.add)).pack(side="left")
        Botao(bar, "＋ Linha", "fantasma", width=80, command=lambda: self.add()).pack(side="left", padx=6)
        Botao(bar, "Copiar da ficha de custo geral", "fantasma", width=210, command=self.copiar_geral).pack(side="left")
        cab = ctk.CTkFrame(self.corpo, fg_color="transparent")
        cab.grid(row=1, column=0, columnspan=2, sticky="ew", padx=20)
        for rot, larg in [("Item", 330), ("Qtd.", 80), ("Valor R$", 90), ("Total", 90)]:
            ctk.CTkLabel(cab, text=rot.upper(), width=larg, anchor="w", font=F(10, "bold"), text_color=MUDO).pack(side="left", padx=3)
        self.caixa = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        self.caixa.grid(row=2, column=0, columnspan=2, sticky="nsew", padx=10, pady=(0, 8))
        self.linhas = []
        for i in linha.itens_custo:
            self.add(i.get("item"), i.get("quantidade"), i.get("valor"))
        self.botao_ok("Usar este custo", self.ok)
        self.total()

    def add(self, item=None, qtd=None, valor=None):
        fr = ctk.CTkFrame(self.caixa, fg_color="transparent")
        fr.pack(fill="x", pady=2)
        a = Entrada(fr, width=330, height=30, placeholder_text="ex.: Tubo redondo 7/8 (m)")
        b = Entrada(fr, "decimal", width=80, height=30, ao_mudar=self.total, placeholder_text="1")
        c = Entrada(fr, "dinheiro", width=90, height=30, ao_mudar=self.total)
        t = ctk.CTkLabel(fr, text="", width=90, anchor="w", font=F(12, "bold"), text_color=TEXTO)
        for w in (a, b, c, t):
            w.pack(side="left", padx=3)
        a.definir(item)
        b.definir(qtd)
        c.definir(valor)
        lin = (fr, a, b, c, t)
        Botao(fr, "✕", "perigo", width=30, height=28, command=lambda: (self.linhas.remove(lin), fr.destroy(), self.total())).pack(side="left", padx=4)
        self.linhas.append(lin)
        self.total()

    def copiar_geral(self):
        for _, a, b, c, t in self.editor.custos:
            if a.valor() or c.valor():
                self.add(a.valor(), b.valor(), c.valor())

    def soma(self):
        return round(sum((b.valor() if b.valor() is not None else 1) * (c.valor() or 0) for _, a, b, c, t in self.linhas), 2)

    def total(self):
        for _, a, b, c, t in self.linhas:
            v = (b.valor() if b.valor() is not None else 1) * (c.valor() or 0)
            t.configure(text=("R$ " + reais(v)) if c.valor() else "")
        venda = self.lv.venda.valor()
        tot = self.soma()
        txt = (f"Custo desta variação: R$ {reais(tot)}" if len(self.alvos) == 1 else
               f"Custo de cada uma das {len(self.alvos)}: R$ {reais(tot)}")
        if venda and tot:
            txt += f"  ·  venda R$ {reais(venda)}  ·  margem {(venda - tot) / venda * 100:.0f}%"
        self.lbl_rod.configure(text=txt, text_color=TEXTO)

    def ok(self):
        itens = [{"item": a.valor(), "quantidade": b.valor() if b.valor() is not None else 1, "valor": c.valor()}
                 for _, a, b, c, t in self.linhas if a.valor() or c.valor()]
        tot = self.soma()
        for l in self.alvos:
            l.itens_custo = [dict(x) for x in itens]
            if itens:
                l.custo.definir(tot)
            l._pintar_custo()
            l.calc()
        self.destroy()
        self.editor.app.toast(f"Custo R$ {reais(tot)} em {len(self.alvos)} variação(ões)" if len(self.alvos) > 1
                              else f"Custo de “{self.lv.rotulo()}”: R$ {reais(tot)}")


class EscolherMaterial(Dialogo):
    """Escolhe itens da tabela de materiais para a ficha de custo da peça."""

    def __init__(self, editor, ao_escolher=None):
        self.ao_escolher = ao_escolher
        super().__init__(editor.app, "Da tabela de materiais", "Toque no material e diga quanto vai na peça. Ele entra na ficha de custo "
                         "com o preço da tabela.", larg=720, alt=640)
        self.editor = editor
        self.itens = recursos.ler_materiais()
        self.corpo.grid_rowconfigure(1, weight=1)
        topo = ctk.CTkFrame(self.corpo, fg_color="transparent")
        topo.grid(row=0, column=0, columnspan=2, sticky="ew", padx=14, pady=(12, 6))
        topo.grid_columnconfigure(0, weight=1)
        self.busca = Entrada(topo, height=34, placeholder_text="⌕  Buscar (ex.: 7/8, espuma D28, corano, hora)")
        self.busca.grid(row=0, column=0, sticky="ew")
        self.busca.bind("<KeyRelease>", lambda e: self.desenhar())
        ctk.CTkLabel(topo, text="Quantidade", font=F(12, "bold"), text_color=MUDO).grid(row=0, column=1, padx=(10, 4))
        self.qtd = Entrada(topo, "decimal", width=70, height=34, placeholder_text="1")
        self.qtd.grid(row=0, column=2)
        self.lista = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        self.lista.grid(row=1, column=0, columnspan=2, sticky="nsew", padx=8, pady=(0, 8))
        self.lista.grid_columnconfigure(0, weight=1)
        self.n = 0
        self.lbl_rod.configure(text="Ponha a quantidade (metros, m², kg, horas…) antes de tocar no material.")
        Botao(self.rod, "Pronto", "primario", width=120, command=self.destroy).grid(row=0, column=2)
        self.after(200, self.busca.focus_set)
        self.desenhar()

    def desenhar(self):
        for w in self.lista.winfo_children():
            w.destroy()
        termos = self.busca.get().lower().split()
        r, cat = 0, None
        for i in self.itens:
            alvo = (i["nome"] + " " + (i.get("cat") or "")).lower()
            if termos and not all(t in alvo for t in termos):
                continue
            if i.get("cat") != cat:
                cat = i.get("cat")
                ctk.CTkLabel(self.lista, text=cat, font=F(12, "bold"), text_color=MUDO, anchor="w").grid(
                    row=r, column=0, sticky="w", padx=8, pady=(10, 2))
                r += 1
            b = ctk.CTkButton(self.lista, text=f"{i['nome']}      R$ {reais(i.get('preco'))} / {i.get('unid') or 'un'}"
                              + ("   · estimado" if i.get("est") else ""), anchor="w", height=34, corner_radius=10,
                              fg_color=VIDRO, hover_color=ROXO_CLARO, text_color=TEXTO, font=F(12),
                              command=lambda i=i: self.usar(i))
            b.grid(row=r, column=0, sticky="ew", padx=6, pady=2)
            r += 1
            if r > 160:
                break

    def usar(self, i):
        qtd = self.qtd.valor() or 1
        (self.ao_escolher or self.editor._linha_custo)(f"{i['nome']} ({i.get('unid') or 'un'})", qtd, i.get("preco"))
        self.n += 1
        self.lbl_rod.configure(text=f"{self.n} adicionado(s). Último: {i['nome']} × {decimal_txt(qtd)}", text_color=VERDE)
        self.qtd.definir(None)
        self.qtd.focus_set()


class EscolherCores(Dialogo):
    """Cores cadastradas (ferragem, corano, madeira): marque as que quer e o robô monta as combinações.

    Sem `ao_usar` a janela só serve para cadastrar e apagar cores (aberta em Configurações).
    """

    def __init__(self, app, ao_usar=None, multiplicar=1, marcadas=None, unica=False):
        super().__init__(app, "Minhas cores", "Escolha uma cor de cada grupo para esta variação." if unica else "Marque as cores desta peça. Se marcar ferragem e corano, o robô cria cada "
                         "combinação (ex.: Preto com corano Verde)." if ao_usar else
                         "As cores que você usa. Elas aparecem para marcar em Variações › Escolher cores.", larg=760, alt=680)
        self.ao_usar, self.multiplicar, self.unica = ao_usar, max(multiplicar, 1), unica
        self.dados = recursos.ler_cores()
        self.marcadas = {k: set(v) for k, v in (marcadas or {}).items()}
        self.editando = not ao_usar
        self.corpo.grid_rowconfigure(0, weight=1)
        self.rolagem = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        self.rolagem.grid(row=0, column=0, columnspan=2, sticky="nsew", padx=6, pady=6)
        self.rolagem.grid_columnconfigure(0, weight=1)
        if ao_usar:
            for w in self.rod.grid_slaves(row=0, column=1):
                w.grid(row=0, column=3, padx=(6, 0))     # Cancelar vai para o fim
            self.bt_editar = Botao(self.rod, "Cadastrar / apagar", "fantasma", width=150, command=self._alternar_edicao)
            self.bt_editar.grid(row=0, column=1, padx=6)
            self.botao_ok("Usar estas cores", self._usar)
        self.desenhar()

    def _alternar_edicao(self):
        self.editando = not self.editando
        self.bt_editar.configure(text="Pronto" if self.editando else "Cadastrar / apagar")
        self.desenhar()

    def _grupo(self, chave):
        return next(g for g in self.dados["grupos"] if g["chave"] == chave)

    def desenhar(self):
        for w in self.rolagem.winfo_children():
            w.destroy()
        for gi, g in enumerate(self.dados["grupos"]):
            marc = self.marcadas.setdefault(g["chave"], set())
            bloco = ctk.CTkFrame(self.rolagem, fg_color=VIDRO, corner_radius=16, border_width=1, border_color=VIDRO_BORDA)
            bloco.grid(row=gi, column=0, sticky="ew", padx=6, pady=6)
            for c in range(4):
                bloco.grid_columnconfigure(c, weight=1, uniform=f"cor{gi}")
            topo = ctk.CTkFrame(bloco, fg_color="transparent")
            topo.grid(row=0, column=0, columnspan=4, sticky="ew", padx=14, pady=(10, 4))
            ctk.CTkLabel(topo, text=g["nome"], font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(side="left")
            n = len(marc)
            ctk.CTkLabel(topo, text=f"  {n} marcada(s)" if n else "", font=F(12), text_color=ROXO_TXT).pack(side="left")
            if self.ao_usar and not self.editando and not self.unica:
                Botao(topo, "Limpar", "fantasma", width=60, height=24, font=F(11),
                      command=lambda k=g["chave"]: (self.marcadas[k].clear(), self.desenhar())).pack(side="right")
                Botao(topo, "Todas", "fantasma", width=56, height=24, font=F(11),
                      command=lambda g=g: (self.marcadas[g["chave"]].update(n for n, _ in g["itens"]), self.desenhar())).pack(side="right")
            for i, (nome, cor) in enumerate(g["itens"]):
                on = nome in marc
                chip = ctk.CTkFrame(bloco, fg_color=ROXO_CLARO if on else PAINEL, corner_radius=14, border_width=2,
                                    border_color=ROXO if on else BORDA, height=40)
                chip.grid(row=1 + i // 4, column=i % 4, sticky="ew", padx=(14 if i % 4 == 0 else 4, 14 if i % 4 == 3 else 4), pady=4)
                bola = ctk.CTkFrame(chip, width=22, height=22, corner_radius=11, fg_color=cor, border_width=1, border_color=BORDA)
                bola.pack(side="left", padx=(10, 8), pady=8)
                lbl = ctk.CTkLabel(chip, text=nome, font=F(12, "bold" if on else "normal"), text_color=ROXO_TXT if on else TEXTO, anchor="w")
                lbl.pack(side="left", fill="x", expand=True)
                if self.editando:
                    Botao(chip, "✕", "perigo", width=26, height=24, font=F(11),
                          command=lambda g=g, nome=nome: self._apagar(g, nome)).pack(side="right", padx=6)
                elif self.ao_usar:
                    if on:
                        ctk.CTkLabel(chip, text="✓", font=F(14, "bold"), text_color=ROXO_TXT).pack(side="right", padx=10)
                    for w in (chip, bola, lbl):
                        w.bind("<Button-1>", lambda e, k=g["chave"], nome=nome: self._marcar(k, nome))
                        w.configure(cursor="hand2")
            if self.editando:
                linha = ctk.CTkFrame(bloco, fg_color="transparent")
                linha.grid(row=2 + (len(g["itens"]) - 1) // 4, column=0, columnspan=4, sticky="ew", padx=14, pady=(4, 12))
                nova = Entrada(linha, width=200, height=30, placeholder_text="Nome da cor nova")
                nova.pack(side="left")
                amostra = ctk.CTkFrame(linha, width=30, height=30, corner_radius=15, fg_color="#888888", border_width=1, border_color=BORDA)
                amostra.pack(side="left", padx=8)
                amostra.cor = "#888888"

                def tom(a=amostra):
                    from tkinter import colorchooser
                    r = colorchooser.askcolor(color=a.cor, parent=self, title="Escolha o tom")
                    if r and r[1]:
                        a.cor = r[1]
                        a.configure(fg_color=r[1])
                Botao(linha, "Escolher o tom", "suave", width=120, height=30, font=F(12), command=tom).pack(side="left")
                Botao(linha, "＋ Cadastrar", "primario", width=110, height=30, font=F(12),
                      command=lambda g=g, e=nova, a=amostra: self._cadastrar(g, e.get(), a.cor)).pack(side="left", padx=8)
                nova.bind("<Return>", lambda ev, g=g, e=nova, a=amostra: self._cadastrar(g, e.get(), a.cor))
            else:
                ctk.CTkLabel(bloco, text="").grid(row=2 + (len(g["itens"]) - 1) // 4, column=0, pady=2)
        self._contar()

    def _marcar(self, chave, nome):
        m = self.marcadas.setdefault(chave, set())
        if self.unica and nome not in m:
            m.clear()
        m.symmetric_difference_update({nome})
        self.desenhar()

    def _cadastrar(self, g, nome, cor):
        nome = re.sub(r"[,·]", " ", nome or "").strip()
        nome = re.sub(r"\s+", " ", nome)[:30]
        if not nome:
            return
        if nome.lower() in {n.lower() for n, _ in g["itens"]}:
            self.app.toast(f"{nome} já está cadastrada", AMBAR)
            return
        g["itens"].append([nome[:1].upper() + nome[1:], cor or "#888888"])
        self._salvar()
        self.desenhar()

    def _apagar(self, g, nome):
        g["itens"] = [i for i in g["itens"] if i[0] != nome]
        self.marcadas.get(g["chave"], set()).discard(nome)
        self._salvar()
        self.desenhar()

    def _salvar(self):
        try:
            recursos.salvar_cores(self.dados)
        except Exception as e:
            messagebox.showerror("Cores", f"Não deu para salvar as cores: {e}", parent=self)

    def combinacoes(self):
        esc = [(g, [n for n, _ in g["itens"] if n in self.marcadas.get(g["chave"], set())]) for g in self.dados["grupos"]]
        return recursos.combinar_cores(esc)

    def _contar(self):
        if not self.ao_usar:
            self.lbl_rod.configure(text="As mudanças já ficam salvas.")
            return
        n = len(self.combinacoes())
        total = n * self.multiplicar
        t = f"{n} cor(es)" + (f" × {self.multiplicar} = {total} variações" if self.multiplicar > 1 and n else "")
        if total > 40:
            t += " · passa de 40 (o máximo do site): marque menos"
        self.lbl_rod.configure(text=t, text_color=VERMELHO if total > 40 else MUDO)

    def _usar(self):
        comb = self.combinacoes()
        if not comb:
            self.lbl_rod.configure(text="Marque pelo menos uma cor.", text_color=AMBAR)
            return
        if len(comb) * self.multiplicar > 40:
            return
        marc = {k: sorted(v) for k, v in self.marcadas.items() if v}
        self.destroy()
        self.ao_usar(comb, marc)


class EscolherFotos(Dialogo):
    """Marca quais fotos entram numa variação (ou em várias marcadas de uma vez)."""

    def __init__(self, editor, linhas):
        self.editor, self.alvos = editor, list(linhas)
        titulo = f"Fotos de: {cortar(linhas[0].rotulo(), 40)}" if len(linhas) == 1 else f"Fotos das {len(linhas)} variações marcadas"
        super().__init__(editor.app, titulo, "Toque nas fotos para marcar ou desmarcar. A primeira marcada (na ordem das fotos) vira a capa.",
                         larg=820, alt=640)
        self.corpo.grid_rowconfigure(0, weight=1)
        grade = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        grade.grid(row=0, column=0, columnspan=2, sticky="nsew", padx=8, pady=8)
        self.sel = {}
        vis = [f for f in editor.fotos if not f.removida]
        for i, f in enumerate(vis):
            em = editor.linhas_da_foto(f)
            marcada = all(l in em for l in self.alvos)
            parcial = not marcada and any(l in em for l in self.alvos)
            self.sel[f] = marcada
            cel = ctk.CTkFrame(grade, fg_color=ROXO_CLARO if marcada else SUAVE, corner_radius=14, border_width=3,
                               border_color=ROXO if marcada else BORDA)
            cel.grid(row=i // 5, column=i % 5, padx=5, pady=5)
            img = editor._mini(f.caminho, 120)
            foto = ctk.CTkLabel(cel, text="" if img else "foto", image=img, width=120, height=120)
            foto.pack(padx=6, pady=(6, 2))
            rot = ctk.CTkLabel(cel, text=("✓ entra" if marcada else "em algumas" if parcial else "não entra"), font=F(11, "bold"),
                               text_color=ROXO_TXT if marcada else MUDO)
            rot.pack(pady=(0, 6))
            for w in (cel, foto, rot):
                w.bind("<Button-1>", lambda e, f=f, cel=cel, rot=rot: self._tocar(f, cel, rot))
                w.configure(cursor="hand2")
        self.botao_ok("Usar estas fotos", self.ok)
        self._contar()

    def _tocar(self, f, cel, rot):
        self.sel[f] = not self.sel[f]
        on = self.sel[f]
        cel.configure(fg_color=ROXO_CLARO if on else SUAVE, border_color=ROXO if on else BORDA)
        rot.configure(text="✓ entra" if on else "não entra", text_color=ROXO_TXT if on else MUDO)
        self._contar()

    def _contar(self):
        n = sum(1 for v in self.sel.values() if v)
        self.lbl_rod.configure(text=f"{n} foto(s) marcada(s)" + ("" if n else " · sem foto, o produto fica sem imagem"))

    def ok(self):
        for f, on in self.sel.items():
            atual = self.editor.linhas_da_foto(f)
            novo = [l for l in self.editor.linhas if (l in atual and l not in self.alvos) or (on and l in self.alvos)]
            self.editor.definir_linhas(f, novo)
        self.destroy()
        self.editor.desenhar_fotos()
        self.editor.app.toast("Fotos escolhidas")


class EscolherVariacoes(Dialogo):
    """Marca em quais variações uma foto entra (ex.: a foto da mesa na Mesa, Mesa + 2 e Mesa + 4, e não na Cadeira)."""

    def __init__(self, editor, foto):
        super().__init__(editor.app, "Esta foto entra em quais variações?", foto.caminho.name, larg=560, alt=620)
        self.editor, self.foto = editor, foto
        img = miniatura(foto.caminho, 96)
        editor.app._refs.append(img)
        topo = ctk.CTkFrame(self.corpo, fg_color="transparent")
        topo.grid(row=0, column=0, columnspan=2, sticky="ew", padx=16, pady=(12, 4))
        ctk.CTkLabel(topo, text="" if img else "foto", image=img, width=96, height=96).pack(side="left")
        bts = ctk.CTkFrame(topo, fg_color="transparent")
        bts.pack(side="left", padx=14)
        Botao(bts, "Marcar todas", "fantasma", width=120, height=28, font=F(12), command=lambda: self._todas(True)).pack(anchor="w")
        Botao(bts, "Desmarcar todas", "fantasma", width=120, height=28, font=F(12), command=lambda: self._todas(False)).pack(anchor="w", pady=4)
        filtro = ctk.CTkFrame(topo, fg_color="transparent")
        filtro.pack(side="left", padx=10)
        ctk.CTkLabel(filtro, text="Marcar as que têm:", font=F(11, "bold"), text_color=MUDO).pack(anchor="w")
        self.filtro = Entrada(filtro, width=170, height=30, placeholder_text="ex.: 75 cm, Mesa")
        self.filtro.pack(anchor="w")
        self.filtro.bind("<Return>", lambda e: self._por_nome())
        Botao(filtro, "Marcar", "suave", width=70, height=26, font=F(11, "bold"), command=self._por_nome).pack(anchor="w", pady=(4, 0))
        self.corpo.grid_rowconfigure(1, weight=1)
        lista = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        lista.grid(row=1, column=0, columnspan=2, sticky="nsew", padx=10, pady=(0, 10))
        self.caixas = []
        for l in editor.linhas:
            cb = ctk.CTkCheckBox(lista, text=l.rotulo(), font=F(13), text_color=TEXTO, fg_color=ROXO, hover_color=ROXO_H,
                                 command=self._contar)
            cb.pack(anchor="w", padx=8, pady=5)
            if foto.uso == "todas" or foto.de(l) or (isinstance(foto.uso, str) and foto.uso.startswith("cor:")
                                                     and editor._cor_da_linha(l) == foto.uso[4:]):
                cb.select()
            self.caixas.append((cb, l))
        self.botao_ok("Usar nestas", self._ok)
        self._contar()

    def _por_nome(self):
        pedacos = [p.strip().lower() for p in re.split(r"[,;]", self.filtro.get()) if p.strip()]
        for cb, l in self.caixas:
            if pedacos and all(p in l.rotulo().lower() for p in pedacos):
                cb.select()
        self._contar()

    def _todas(self, sim):
        for cb, _ in self.caixas:
            cb.select() if sim else cb.deselect()
        self._contar()

    def _contar(self):
        n = sum(1 for cb, _ in self.caixas if cb.get())
        self.lbl_rod.configure(text=f"{n} de {len(self.caixas)} marcada(s)" + (" · nenhuma = não usar" if not n else ""))

    def _ok(self):
        sel = tuple(l for cb, l in self.caixas if cb.get())
        if not sel:
            self.foto.uso = "nao"
        elif len(sel) == len(self.caixas):
            self.foto.uso = "todas"
        else:
            self.foto.uso = sel[0] if len(sel) == 1 else sel
        self.destroy()
        self.editor.desenhar_fotos()


class ImagensIA(Dialogo):
    """Imagens de anúncio a partir das fotos do produto.

    modo "gemini": o robô manda as fotos e o pedido para a IA do Gemini (chave em Configurações).
    modo "canva": o robô abre o Canva IA no navegador da pessoa, copia o pedido e separa as fotos;
    as imagens que ela baixar do Canva entram aqui sozinhas (vigia a pasta Downloads)."""

    def __init__(self, editor, modo="gemini", linha=None):
        app = editor.app
        self.modo = modo
        if modo == "canva":
            titulo, sub = "Criar imagens no Canva", ("Toque em “Robô faz tudo”: ele abre o Canva IA, manda as fotos marcadas e cada pedido "
                                                     "marcado, um por vez, espera ficar pronto e baixa. As imagens aparecem aqui sozinhas.")
        else:
            titulo, sub = "Criar imagens com o Gemini", ("Escolha as fotos do produto e os tipos de imagem. A IA cria no estilo do "
                                                         "Mercado Livre; confira se a peça ficou igual antes de usar.")
        super().__init__(app, titulo, sub, larg=1080, alt=720)
        self.editor = editor
        self.aberto = False
        self.d = json.loads(json.dumps(editor.montar(), default=str))
        self.chave = (app.cfg["robo"].get("gemini_chave") or "").strip()
        self.modelo = (app.cfg["robo"].get("gemini_modelo_imagem") or "gemini-3.1-flash-image").strip()
        self.fotos = [f for f in editor.fotos if not f.removida]
        self.criadas = []          # [{"estilo", "rotulo", "dado", "var", "check"}]
        self.rodando = False
        sw = dict(font=F(13), text_color=TEXTO, fg_color=ROXO, hover_color=ROXO, border_color=BORDA, checkbox_width=20, checkbox_height=20)

        esq = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent", width=360)
        esq.grid(row=0, column=0, sticky="nsew", padx=(12, 4), pady=12)
        self.corpo.grid_rowconfigure(0, weight=1)

        ctk.CTkLabel(esq, text="Fotos do produto", font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x")
        ctk.CTkLabel(esq, text="A IA olha estas fotos para copiar a peça (até 6).", font=F(11), text_color=MUDO, anchor="w").pack(fill="x")
        grade = ctk.CTkFrame(esq, fg_color="transparent")
        grade.pack(fill="x", pady=(6, 10))
        self.marcadas = []
        for i, f in enumerate(self.fotos[:12]):
            cel = ctk.CTkFrame(grade, fg_color="transparent")
            cel.grid(row=i // 4, column=i % 4, padx=3, pady=3)
            img = miniatura(f.caminho, 70)
            app._refs.append(img)
            ctk.CTkLabel(cel, text="" if img else "—", image=img, width=70, height=70, fg_color=SUAVE if not img else "transparent",
                         corner_radius=10).pack()
            cb = ctk.CTkCheckBox(cel, text="usar", width=20, command=self._contar, **{**sw, "font": F(11)})
            if i < 3:
                cb.select()
            cb.pack(pady=(2, 0))
            self.marcadas.append((cb, f))
        if not self.fotos:
            ctk.CTkLabel(grade, text="A ficha ainda não tem foto. Adicione pelo menos uma.", font=F(12), text_color=AMBAR).grid(row=0, column=0)

        self.vars = [v for v in self.d.get("variacoes") or [] if isinstance(v, dict)]
        self.nomes_var = [str(v.get("valor") or f"Opção {i + 1}") for i, v in enumerate(self.vars)] or ["Produto"]
        nomes = (["Todas as variações"] + self.nomes_var) if len(self.vars) > 1 else self.nomes_var
        vbox = ctk.CTkFrame(esq, fg_color=VIDRO, corner_radius=14, border_width=1, border_color=VIDRO_BORDA)
        vbox.pack(fill="x", pady=(2, 10))
        ctk.CTkLabel(vbox, text="Para qual variação?", font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", padx=12, pady=(10, 0))
        lin = ctk.CTkFrame(vbox, fg_color="transparent")
        lin.pack(fill="x", padx=12, pady=(6, 2))
        Botao(lin, "◀", "fantasma", width=32, height=32, command=lambda: self._andar(-1)).pack(side="left")
        self.var_menu = ctk.CTkOptionMenu(lin, values=nomes, width=232, font=F(13), fg_color=PAINEL, button_color=ROXO,
                                          text_color=TEXTO, dropdown_font=F(13), command=lambda _: self._trocou_var())
        self.var_menu.pack(side="left", padx=4)
        Botao(lin, "▶", "fantasma", width=32, height=32, command=lambda: self._andar(1)).pack(side="left")
        self.lbl_var = ctk.CTkLabel(vbox, text="", font=F(11), text_color=MUDO, anchor="w", justify="left", wraplength=320)
        self.lbl_var.pack(fill="x", padx=12, pady=(2, 4))
        self.bt_prox = Botao(vbox, "Próxima variação sem imagem  ▶", "suave", height=30, font=F(12, "bold"), command=self._proxima_sem)
        if len(self.vars) > 1:
            self.bt_prox.pack(fill="x", padx=12, pady=(0, 10))
        # as fotos reais são de qual variação?
        self.banner_ref = None
        self.reais = [f for f in self.fotos if f.uso != "nao"]
        if len(editor.linhas) > 1 and self.reais and all(f.uso == "todas" for f in self.reais):
            self.banner_ref = ctk.CTkFrame(esq, fg_color=("#fff7ed", "#3a2a12"), corner_radius=12)
            self.banner_ref.pack(fill="x", pady=(0, 10))
            ctk.CTkLabel(self.banner_ref, text="As fotos que você tirou são de qual variação?", font=F(12, "bold"), text_color=TEXTO,
                         anchor="w").pack(fill="x", padx=12, pady=(10, 0))
            ctk.CTkLabel(self.banner_ref, text="Assim elas não aparecem nas outras e o pedido diz o que muda de uma para a outra.",
                         font=F(11), text_color=MUDO, anchor="w", justify="left", wraplength=320).pack(fill="x", padx=12)
            self.ref_menu = ctk.CTkOptionMenu(self.banner_ref, values=["Todas as variações"] + [l.rotulo() for l in editor.linhas],
                                              width=300, font=F(12), fg_color=PAINEL, button_color=AMBAR, text_color=TEXTO,
                                              dropdown_font=F(12), command=self._fotos_da)
            self.ref_menu.pack(anchor="w", padx=12, pady=(6, 10))
            dona = next((i for i, v in enumerate(self.vars) if v.get("fotos")), None)
            if dona is not None and sum(1 for v in self.vars if v.get("fotos")) == 1 and dona < len(editor.linhas):
                self.ref_menu.set(editor.linhas[dona].rotulo())
                for f in self.reais:
                    f.uso = editor.linhas[dona]
                editor.desenhar_fotos()

        ctk.CTkLabel(esq, text="Imagens para criar", font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", pady=(12, 2))
        self.estilos = []
        for chave, rot, _ in recursos.ESTILOS_IMAGEM:
            cb = ctk.CTkCheckBox(esq, text=rot, command=self._contar, **sw)
            if chave in recursos.ESTILOS_PADRAO:
                cb.select()
            cb.pack(anchor="w", pady=3)
            self.estilos.append((cb, chave, rot))
        ctk.CTkLabel(esq, text="O que muda nesta variação", font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", pady=(12, 0))
        ctk.CTkLabel(esq, text="Fica guardado na variação e entra em todos os pedidos dela.", font=F(11), text_color=MUDO,
                     anchor="w").pack(fill="x")
        self.extra = ctk.CTkTextbox(esq, width=320, height=74, corner_radius=9, border_width=2, border_color=BORDA, fg_color=PAINEL,
                                    font=F(12), wrap="word")
        self.extra.pack(anchor="w", pady=(4, 0))
        self.extra.bind("<FocusOut>", lambda e: self._guardar_extra(), add="+")

        dir_ = ctk.CTkFrame(self.corpo, fg_color="transparent")
        dir_.grid(row=0, column=1, sticky="nsew", padx=(4, 12), pady=12)
        dir_.grid_columnconfigure(0, weight=1)
        dir_.grid_rowconfigure(3, weight=1)
        if modo == "canva":
            passos = ctk.CTkFrame(dir_, fg_color=SUAVE, corner_radius=14)
            passos.grid(row=0, column=0, sticky="ew", pady=(0, 8))
            for i, t in enumerate(("1. Marque as fotos, a variação e as imagens para criar. Toque em “⚡ Robô faz tudo no Canva”.",
                                   "2. Na primeira vez, entre na sua conta do Canva na janela que o robô abre (depois ele lembra).",
                                   "3. O robô cola as fotos e o primeiro pedido, espera, baixa, e segue para o próximo. Não mexa na janela do Canva enquanto ele trabalha.")):
                ctk.CTkLabel(passos, text=t, font=F(12), text_color=TEXTO, anchor="w", justify="left", wraplength=500).grid(
                    row=i, column=0, sticky="w", padx=14, pady=(10 if i == 0 else 2, 10 if i == 2 else 2))
            self.pedidos = ctk.CTkFrame(dir_, fg_color="transparent")
            self.pedidos.grid(row=1, column=0, sticky="ew")
            self.capa_sw = ctk.CTkSwitch(dir_, text="A primeira imagem marcada vira a capa", progress_color=ROXO, font=F(12), text_color=TEXTO)
            self.capa_sw.select()
        ctk.CTkLabel(dir_, text="Imagens baixadas do Canva" if modo == "canva" else "Imagens criadas", font=F(14, "bold"),
                     text_color=TEXTO, anchor="w").grid(row=2, column=0, sticky="ew", pady=(4, 0))
        self.res = ctk.CTkScrollableFrame(dir_, fg_color=CARD, corner_radius=14, border_width=1, border_color=BORDA)
        self.res.grid(row=3, column=0, sticky="nsew", pady=(6, 0))
        self.vazio = ctk.CTkLabel(self.res, text=("Esperando você baixar as imagens do Canva…\n(o robô olha a pasta Downloads)"
                                                  if modo == "canva" else "As imagens aparecem aqui.\nMarque as que quer usar e toque em Adicionar."),
                                  font=F(12), text_color=MUDO, justify="center")
        self.vazio.pack(pady=60)
        if modo == "canva":
            self.capa_sw.grid(row=4, column=0, sticky="w", pady=(8, 0))

        self.bt_add = Botao(self.rod, "Adicionar às fotos", "suave", width=160, height=38, command=self.adicionar, state="disabled")
        self.botao_ok("⚡  Robô faz tudo no Canva" if modo == "canva" else "✦  Criar imagens",
                      self.robo_canva if modo == "canva" else self.gerar)
        self.bt_ok.configure(width=210)
        self._parar_canva = False
        if modo == "canva":
            self.bt_manual = Botao(self.rod, "Eu faço no Canva", "suave", width=140, height=38, command=self.abrir_canva)
            self.bt_manual.grid(row=0, column=2, padx=(6, 0))
        self.bt_ok.grid(row=0, column=3, padx=6)
        self.bt_add.grid(row=0, column=4)
        if modo == "canva":
            import time as _t
            self.inicio = _t.time() - 2
            self.vistos = {}
            pasta = (app.cfg["robo"].get("pasta_downloads") or "").strip()
            self.downloads = Path(os.path.expandvars(os.path.expanduser(pasta or "~/Downloads")))
            self.after(1500, self._vigiar_downloads)
        if linha is not None and linha in editor.linhas and len(self.vars) > 1:
            self.var_menu.set(self.nomes_var[editor.linhas.index(linha)])
        elif modo == "canva" and len(self.vars) > 1:
            self.var_menu.set(self.nomes_var[self._indice_sem_imagem(0)])
        self._trocou_var()
        self._contar()
        if modo != "canva" and not self.chave:
            self.lbl_rod.configure(text="Falta a chave do Gemini em Configurações (a mesma do título e descrição).")
            self.bt_ok.configure(state="disabled")

    # ---- variação escolhida
    def _linha(self, idx):
        ls = self.editor.linhas
        return ls[idx] if idx is not None and idx < len(ls) else None

    def _n_imagens(self, idx):
        l = self._linha(idx)
        return sum(1 for f in self.editor.fotos if not f.removida and l is not None and f.de(l))

    def _indice_sem_imagem(self, de):
        n = len(self.vars)
        for k in range(n):
            i = (de + k) % n
            l = self._linha(i)
            if not self._n_imagens(i) and (l is None or l.ativa):
                return i
        return de % n if n else 0

    def _andar(self, passo):
        if len(self.vars) <= 1:
            return
        idx, _ = self._var_atual()
        idx = 0 if idx is None else (idx + passo) % len(self.vars)
        self._guardar_extra()
        self.var_menu.set(self.nomes_var[idx])
        self._trocou_var()

    def _proxima_sem(self):
        idx, _ = self._var_atual()
        self._guardar_extra()
        self.var_menu.set(self.nomes_var[self._indice_sem_imagem(0 if idx is None else idx + 1)])
        self._trocou_var()

    def _guardar_extra(self):
        idx, _ = self._var_atual()
        l = self._linha(idx)
        t = self.extra.get("1.0", "end").strip()
        if l is not None:
            l.canva_extra.definir(t or None)
            if idx < len(self.vars):
                self.vars[idx]["canva_extra"] = t or None

    def _fotos_da(self, txt):
        ed = self.editor
        alvo = next((l for l in ed.linhas if l.rotulo() == txt), None)
        for f in self.reais:
            f.uso = alvo if alvo is not None else "todas"
        ed.desenhar_fotos()
        self._trocou_var()

    def _trocou_var(self):
        idx, var = self._var_atual()
        l = self._linha(idx) if len(self.vars) > 1 else None
        self.var_idx = idx if len(self.vars) > 1 else None
        self.extra.delete("1.0", "end")
        if var.get("canva_extra") or (l is not None and l.canva_extra.valor()):
            self.extra.insert("1.0", (l.canva_extra.valor() if l is not None else None) or var.get("canva_extra") or "")
        # marca as fotos desta variação (as dela e as que valem para todas)
        cor = self.editor._cor_da_linha(l) if l is not None else None
        for cb, f in self.marcadas:
            dela = f.uso == "todas" or (l is not None and (f.de(l) or (isinstance(f.uso, str) and cor and f.uso == f"cor:{cor}")))
            if l is None:
                dela = f.uso != "nao"
            if dela:
                cb.select()
            else:
                cb.deselect()
        if not self._escolhidas():          # variação sem foto real: usa as fotos reais do produto como referência
            for cb, f in self.marcadas[:3]:
                cb.select()
        kit = recursos.kit_da_variacao(var)
        for cb, k, _ in self.estilos:
            if k == "kit":
                (cb.select if kit > 1 else cb.deselect)()
        com = sum(1 for i in range(len(self.vars)) if self._n_imagens(i))
        if len(self.vars) > 1:
            if idx is None:
                t = "As imagens vão valer para todas as variações."
            else:
                n = self._n_imagens(idx)
                t = (f"Variação {idx + 1} de {len(self.vars)} · {'já tem ' + str(n) + ' imagem(ns)' if n else 'ainda sem imagem própria'}"
                     f" · {com} de {len(self.vars)} variações com imagem")
            self.lbl_var.configure(text=t)
        if self.aberto:
            self._montar_pedidos(copiar=False)
        self._contar()

    def _referencia(self, idx):
        """A variação que aparece nas fotos marcadas (para o pedido dizer o que muda)."""
        donas = {f.uso for cb, f in self.marcadas if cb.get()}
        ls = self.editor.linhas
        if len(donas) == 1:
            u = next(iter(donas))
            if u in ls:
                return self.vars[ls.index(u)] if ls.index(u) < len(self.vars) else None
        com = [v for v in self.vars if v.get("fotos")]
        if len(com) == 1 and len(self.vars) > 1:
            return com[0]
        return None

    def _escolhidas(self):
        return [f.caminho for cb, f in self.marcadas if cb.get()][:6]

    def _contar(self):
        if self.modo == "canva":
            if self.rodando:
                return
            n = sum(1 for cb, *_ in self.estilos if cb.get())
            self.bt_ok.configure(state="normal" if n and self._escolhidas() else "disabled",
                                 text=f"⚡  Robô cria {n} no Canva" if n else "⚡  Robô faz tudo no Canva")
            self.bt_manual.configure(state="normal" if n and self._escolhidas() else "disabled")
            if not self.criadas:
                self.lbl_rod.configure(text="" if self._escolhidas() else "Marque pelo menos uma foto do produto.")
            return
        if self.rodando or not self.chave:
            return
        n = sum(1 for cb, *_ in self.estilos if cb.get())
        ok = n and self._escolhidas()
        self.bt_ok.configure(state="normal" if ok else "disabled", text=f"✦  Criar {n} imagem(ns)" if n else "✦  Criar imagens")
        self.lbl_rod.configure(text="" if self._escolhidas() else "Marque pelo menos uma foto do produto.")

    def _var_atual(self):
        nome = self.var_menu.get()
        for i, v in enumerate(self.vars):
            if str(v.get("valor") or f"Opção {i + 1}") == nome:
                return i, v
        return None, (self.vars[0] if self.vars else {})

    # ---- Canva assistido
    def _copiar(self, texto, rot):
        self.clipboard_clear()
        self.clipboard_append(texto)
        self.lbl_rod.configure(text=f"Pedido “{rot}” copiado. No Canva, cole com Ctrl+V.")

    def _pedido(self, k, idx, var):
        v = dict(var)
        t = self.extra.get("1.0", "end").strip()
        v["canva_extra"] = t or None
        return recursos.prompt_imagem(self.d, k, v, "", ref=self._referencia(idx))

    def _montar_pedidos(self, copiar=True):
        estilos = [(k, r) for cb, k, r in self.estilos if cb.get()]
        if not estilos:
            return
        idx, var = self._var_atual()
        for w in self.pedidos.winfo_children():
            w.destroy()
        self.pedidos_txt = []
        for i, (k, rot) in enumerate(estilos):
            texto = self._pedido(k, idx, var)
            self.pedidos_txt.append((k, rot, texto))
            Botao(self.pedidos, f"⧉  {rot}", "suave" if i else "primario", height=30, font=F(12, "bold"),
                  command=lambda t=texto, r=rot: self._copiar(t, r)).grid(row=i // 3, column=i % 3, padx=3, pady=3, sticky="ew")
        for c in range(3):
            self.pedidos.grid_columnconfigure(c, weight=1)
        if copiar:
            k, rot, texto = self.pedidos_txt[0]
            self._copiar(texto, rot)
        else:
            nome = self.var_menu.get()
            self.lbl_rod.configure(text=f"Pedidos refeitos para “{nome}”. Toque em cada um para copiar.")

    def abrir_canva(self):
        estilos = [(k, r) for cb, k, r in self.estilos if cb.get()]
        fotos = self._escolhidas()
        if not estilos or not fotos:
            return
        self._guardar_extra()
        idx, var = self._var_atual()
        # fotos numa pasta só, prontas para arrastar
        pasta = self.editor.tmp / "para-o-canva"
        shutil.rmtree(pasta, ignore_errors=True)
        pasta.mkdir(parents=True, exist_ok=True)
        for i, f in enumerate(fotos, 1):
            try:
                recursos.tratar_foto(f, pasta / f"foto-{i}.jpg", quadrada=False, lado=1600)
            except Exception:
                shutil.copy2(f, pasta / f"foto-{i}{Path(f).suffix}")
        self.aberto = True
        self._montar_pedidos()
        link = (self.app.cfg["robo"].get("canva_link") or "").strip() or "https://www.canva.com/ai"
        webbrowser.open(link)
        abrir_no_sistema(pasta)
        self.bt_manual.configure(text="Abrir de novo")
        k, rot, _ = self.pedidos_txt[0]
        self.lbl_rod.configure(text=f"Pedido “{rot}” copiado. Para os outros tipos, toque no botão de cada um acima.")

    def _fotos_para_canva(self):
        pasta = self.editor.tmp / "para-o-canva"
        shutil.rmtree(pasta, ignore_errors=True)
        pasta.mkdir(parents=True, exist_ok=True)
        saida = []
        for i, f in enumerate(self._escolhidas(), 1):
            try:
                recursos.tratar_foto(f, pasta / f"foto-{i}.jpg", quadrada=False, lado=1600)
                saida.append(pasta / f"foto-{i}.jpg")
            except Exception:
                shutil.copy2(f, pasta / f"foto-{i}{Path(f).suffix}")
                saida.append(pasta / f"foto-{i}{Path(f).suffix}")
        return saida

    def robo_canva(self):
        """O robô faz o processo todo no Canva IA: fotos + um pedido por vez, espera, baixa."""
        if self.rodando:
            self._parar_canva = True
            self.bt_ok.configure(state="disabled", text="Parando…")
            return
        estilos = [(k, r) for cb, k, r in self.estilos if cb.get()]
        if not estilos or not self._escolhidas():
            return
        self._guardar_extra()
        idx, var = self._var_atual()
        pedidos = [(k, r, self._pedido(k, idx, var)) for k, r in estilos]
        fotos = self._fotos_para_canva()
        self.rodando = True
        self._parar_canva = False
        self.bt_ok.configure(state="normal", text="■  Parar")
        self.bt_manual.configure(state="disabled")
        self.vazio.pack_forget()
        self.lbl_rod.configure(text="Abrindo o Canva IA…")
        link = (self.app.cfg["robo"].get("canva_link") or "").strip() or "https://www.canva.com/ai"
        fila, total = self.app.fila, len(pedidos)
        pasta_robo = Path(motor.__file__).resolve().parent

        def aviso(t):
            if t == "login":
                fila.put(("canva_aviso", None, (self, "Entre na sua conta do Canva na janela que abriu. Só precisa da primeira vez; "
                                                      "o robô continua sozinho quando você entrar.")))
            elif t == "conectado":
                fila.put(("canva_aviso", None, (self, "Canva aberto. Mandando as fotos…")))
            elif t == "humano":
                fila.put(("canva_aviso", None, (self, "O Canva pediu “Confirme que é humano”. Marque o quadradinho na janela do "
                                                      "Canva; o robô espera e continua sozinho.")))
            elif t == "continuando":
                fila.put(("canva_aviso", None, (self, "Confirmado. Continuando…")))
            elif t == "fotos_manual":
                try:
                    abrir_no_sistema(fotos[0].parent)
                except Exception:
                    pass
                fila.put(("canva_aviso", None, (self, "Não consegui colocar as fotos sozinho. Arraste as fotos da pasta que abri "
                                                      "para a caixa do Canva. O robô continua sozinho quando elas aparecerem.")))
            elif t.startswith("pedido:"):
                _, n, tot, rot = t.split(":", 3)
                fila.put(("canva_aviso", None, (self, f"Pedido {n} de {tot}: “{rot}”. Esperando o Canva criar e baixando… "
                                                      "(não mexa na janela do Canva)")))

        def receber(k, rot, n, tot, dados, erro):
            if not dados:
                fila.put(("ia_imagem", None, (self, k, rot, idx, None, erro or "sem imagem", n, tot)))
                return
            for j, d in enumerate(dados):
                ultimo = j == len(dados) - 1
                nome = rot if len(dados) == 1 else f"{rot} ({j + 1})"
                fila.put(("ia_imagem", None, (self, k, nome, idx, d, None, n if ultimo else n - 1 if n > 1 else 0, tot, j == 0)))

        antigo = getattr(self, "_fechar_canva", None)
        if antigo is not None:
            antigo.set()                      # fecha o navegador da tentativa anterior
        fechar = self._fechar_canva = threading.Event()

        def rodar():
            import traceback
            cv = motor.CanvaIA(link, avisar=aviso, parar=lambda: self._parar_canva, pasta_erro=pasta_robo)
            deixar_aberto = False
            try:
                cv.criar(fotos, pedidos, receber)
                fila.put(("canva_fim", None, (self, None)))
            except motor.Parar:
                fila.put(("canva_fim", None, (self, "Parado. As imagens que já vieram ficaram aqui.")))
            except Exception as e:
                try:
                    (pasta_robo / "canva-erro.txt").write_text(traceback.format_exc(), encoding="utf-8")
                except Exception:
                    pass
                msg = str(e).splitlines()[0] if str(e) else repr(e)
                if "Executable doesn't exist" in msg or "playwright" in msg.lower() and "install" in msg.lower():
                    msg = "O navegador do robô não está instalado. Rode o instalar.bat de novo."
                deixar_aberto = True
                fila.put(("canva_fim", None, (self, f"O robô parou: {msg}. Deixei a janela do Canva aberta para você ver. "
                                                     "Mande um print dela e do robô para eu ajustar.")))
            finally:
                if deixar_aberto:             # o navegador fica aberto até fechar esta janela ou tentar de novo
                    while not fechar.is_set():
                        try:
                            if cv.page is None or cv.page.is_closed():
                                break
                            cv.page.wait_for_timeout(1000)
                        except Exception:
                            break
                cv.fechar()
        threading.Thread(target=rodar, daemon=True).start()

    def canva_fim(self, msg):
        self.rodando = False
        self._contar()
        boas = sum(1 for c in self.criadas if c["dado"])
        if msg:
            self.lbl_rod.configure(text=msg)
        else:
            self.lbl_rod.configure(text=f"Pronto: {boas} imagem(ns) do Canva. Marque as que gostou e toque em Adicionar. "
                                        "Confira se a peça ficou igual à de verdade.")
        self.bt_ok.configure(state="normal", text="⚡  Robô faz de novo")
        self.bt_manual.configure(state="normal")
        self._botao_add()

    def destroy(self):
        self._parar_canva = True
        if getattr(self, "_fechar_canva", None) is not None:
            self._fechar_canva.set()
        super().destroy()

    def _vigiar_downloads(self):
        if not self.winfo_exists():
            return
        try:
            arqs = [p for p in self.downloads.iterdir()
                    if p.is_file() and p.suffix.lower() in (".png", ".jpg", ".jpeg", ".webp") and p.stat().st_mtime >= self.inicio]
        except OSError:
            arqs = []
        for p in sorted(arqs, key=lambda x: x.stat().st_mtime):
            try:
                tam = p.stat().st_size
            except OSError:
                continue
            antes = self.vistos.get(p)
            if antes == "ok":
                continue
            if antes != tam:          # ainda baixando: espera o tamanho parar
                self.vistos[p] = tam
                continue
            self.vistos[p] = "ok"
            try:
                dado = p.read_bytes()
            except OSError:
                continue
            self.vazio.pack_forget()
            self.recebeu("canva", p.name if len(p.name) < 26 else p.name[:23] + "…", getattr(self, "var_idx", None), dado, None, 1, 1)
        self.after(1500, self._vigiar_downloads)

    def gerar(self):
        estilos = [(k, r) for cb, k, r in self.estilos if cb.get()]
        fotos = self._escolhidas()
        if not estilos or not fotos or self.rodando:
            return
        self.rodando = True
        self.bt_ok.configure(state="disabled", text="Criando…")
        self._guardar_extra()
        idx, var = self._var_atual()
        pedidos = {k: self._pedido(k, idx, var) for k, _ in estilos}
        chave, modelo, fila = self.chave, self.modelo, self.app.fila
        self.vazio.pack_forget()
        self.lbl_rod.configure(text=f"Criando 1 de {len(estilos)}… cada imagem leva uns 10 a 30 segundos.")

        def rodar():
            for n, (k, rot) in enumerate(estilos, 1):
                try:
                    dado = recursos.gerar_imagem_ia(fotos, pedidos[k], chave, modelo)
                    fila.put(("ia_imagem", None, (self, k, rot, idx, dado, None, n, len(estilos))))
                except Exception as e:
                    fila.put(("ia_imagem", None, (self, k, rot, idx, None, str(e).splitlines()[0] if str(e) else repr(e), n, len(estilos))))
                    if "chave" in str(e).lower() or "limite" in str(e).lower():
                        fila.put(("ia_imagem", None, (self, None, None, None, None, None, len(estilos), len(estilos))))
                        return
        threading.Thread(target=rodar, daemon=True).start()

    def recebeu(self, estilo, rot, idx, dado, erro, n, total, marcar=True):
        if estilo is not None:
            card = ctk.CTkFrame(self.res, fg_color="transparent")
            i = len(self.criadas)
            card.grid(row=i // 3, column=i % 3, padx=6, pady=6, sticky="n")
            if dado:
                img = miniatura(dado, 120, 14)
                self.app._refs.append(img)
                lbl = ctk.CTkLabel(card, text="" if img else "sem prévia", image=img, width=120, height=120, corner_radius=14,
                                   fg_color="transparent" if img else SUAVE)
                lbl.pack()
                if idx is not None and len(self.vars) > 1 and idx < len(self.nomes_var):
                    ctk.CTkLabel(card, text=self.nomes_var[idx][:24], font=F(10, "bold"), text_color=ROXO_TXT).pack()
                cb = ctk.CTkCheckBox(card, text=rot, font=F(12), text_color=TEXTO, fg_color=ROXO, hover_color=ROXO,
                                     checkbox_width=18, checkbox_height=18, command=self._botao_add)
                if marcar:
                    cb.select()
                cb.pack(pady=(4, 0))
                lbl.bind("<Button-1>", lambda e, c=cb: (c.toggle()))
                self.criadas.append({"estilo": estilo, "rotulo": rot, "dado": dado, "var": idx, "check": cb})
            else:
                ctk.CTkLabel(card, text=f"{rot}\nnão saiu:\n{erro}", width=140, height=140, corner_radius=14, fg_color=SUAVE,
                             text_color=MUDO, font=F(11), wraplength=128, justify="center").pack()
                self.criadas.append({"estilo": estilo, "rotulo": rot, "dado": None, "var": idx, "check": None})
        if n >= total:
            self.rodando = False
            self._contar()
            boas = sum(1 for c in self.criadas if c["dado"])
            self.lbl_rod.configure(text=f"{boas} imagem(ns) {'baixada(s)' if self.modo == 'canva' else 'criada(s)'}. "
                                   "Confira se a peça ficou igual à de verdade."
                                   if boas else "Nenhuma imagem saiu. Veja o motivo em cada uma.")
            if self.modo != "canva" and self.chave:
                self.bt_ok.configure(text="✦  Criar de novo")
            if self.modo == "canva":
                self.bt_ok.configure(state="normal", text="⚡  Robô faz de novo")
                self.bt_manual.configure(state="normal")
        else:
            self.lbl_rod.configure(text=f"Criando {n + 1} de {total}…")
        self._botao_add()

    def _botao_add(self):
        n = sum(1 for c in self.criadas if c["dado"] and c["check"] and c["check"].get())
        self.bt_add.configure(state="normal" if n else "disabled", text=f"Adicionar {n} às fotos" if n else "Adicionar às fotos")

    def adicionar(self):
        ed = self.editor
        novas_capa, novas = [], []
        capa_livre = self.modo == "canva" and bool(self.capa_sw.get())
        for n, c in enumerate(self.criadas):
            if not (c["dado"] and c["check"] and c["check"].get()):
                continue
            if capa_livre:
                c["estilo"], capa_livre = "capa", False
            import time as _t
            destino = ed.tmp / f"ia-{c['estilo']}-{_t.strftime('%d%m-%H%M%S')}-{n + 1}.jpg"
            try:
                recursos.quadrar_imagem(c["dado"], destino)
            except Exception:
                continue
            uso = "todas"
            if c["var"] is not None and len(ed.linhas) > 1 and c["var"] < len(ed.linhas):
                uso = ed.linhas[c["var"]]
            (novas_capa if c["estilo"] == "capa" else novas).append(FotoEd(destino, nova=True, uso=uso))
        if not novas_capa and not novas:
            return
        ed.fotos = novas_capa + ed.fotos + novas
        ed.desenhar_fotos()
        total = len(novas_capa) + len(novas)
        if self.modo == "canva" and len(self.vars) > 1:
            # continua aberto para a próxima variação
            for w in self.res.winfo_children():
                if w is not self.vazio:
                    w.destroy()
            self.criadas = []
            self.vazio.pack(pady=60)
            self.fotos = [f for f in ed.fotos if not f.removida]
            self.app.toast(f"{total} imagem(ns) na variação. Agora a próxima.", VERDE)
            self._proxima_sem()
            self._botao_add()
            return
        self.destroy()
        self.app.toast(f"{total} imagem(ns) adicionada(s). Toque em Salvar ficha para guardar.", VERDE)


class ConferirFicha(Dialogo):
    def __init__(self, app, feito, falta):
        titulo = "Ficha completada" if feito is not None else "Conferir ficha"
        super().__init__(app, titulo, "O robô preenche só o que estava vazio. Confira e toque em Salvar ficha.", larg=640, alt=560)
        caixa = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        caixa.grid(row=0, column=0, columnspan=2, sticky="nsew", padx=10, pady=10)
        self.corpo.grid_rowconfigure(0, weight=1)
        self.corpo.grid_columnconfigure(0, weight=1)

        def linha(t, cor, fundo):
            ctk.CTkLabel(caixa, text=t, anchor="w", justify="left", wraplength=540, fg_color=fundo, text_color=cor,
                         corner_radius=10, padx=12, pady=6, font=F(12)).pack(fill="x", pady=2)
        if feito is not None:
            ctk.CTkLabel(caixa, text="Preenchido agora", font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", pady=(2, 4))
            for t in feito or ["Nada para completar: o que dava para deduzir já estava preenchido."]:
                linha("✓  " + t, ("#047857", "#6ee7b7"), ("#ecfdf5", "#0f2a22"))
        ctk.CTkLabel(caixa, text="Ainda falta (só você sabe)" if falta else "Tudo certo",
                     font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", pady=(12, 4))
        for t in falta:
            linha("⚠  " + t, ("#b45309", "#fcd34d"), ("#fffbeb", "#33270f"))
        if not falta:
            linha("✓  A ficha tem tudo o que o site e o Mercado Livre precisam.", ("#047857", "#6ee7b7"), ("#ecfdf5", "#0f2a22"))
        self.botao_ok("Entendi", self.destroy)


class Faltando(Dialogo):
    """Custo e peso que só o Marco sabe, de todas as fichas de uma vez."""

    def __init__(self, app, itens, arrumadas=0):
        txt = (f"O robô arrumou {arrumadas} ficha(s). " if arrumadas else "") + \
              "Falta o que só você sabe. Preencha o que tiver (pode deixar em branco) e toque em Salvar."
        super().__init__(app, "Custo e peso que faltam", txt, larg=720, alt=560)
        self.itens = itens[:80]
        caixa = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        caixa.grid(row=0, column=0, columnspan=2, sticky="nsew", padx=12, pady=(10, 4))
        self.corpo.grid_rowconfigure(0, weight=1)
        self.corpo.grid_columnconfigure(0, weight=1)
        caixa.grid_columnconfigure(0, weight=1)
        for c, (t, w) in enumerate((("PRODUTO", 0), ("CUSTO R$", 96), ("PESO CAIXA KG", 110))):
            ctk.CTkLabel(caixa, text=t, font=F(10, "bold"), text_color=MUDO, anchor="w", width=w).grid(row=0, column=c, sticky="w", padx=6)
        self.campos = []
        ultimo = None
        for i, (pasta, nome, idx, rot, fc, fp) in enumerate(self.itens, 1):
            tit = cortar(nome, 46) if pasta != ultimo else ""
            ultimo = pasta
            ctk.CTkLabel(caixa, text=(tit + "\n" if tit else "") + "   " + rot, font=F(12), text_color=TEXTO, anchor="w",
                         justify="left").grid(row=i, column=0, sticky="w", padx=6, pady=3)
            ec = Entrada(caixa, "dinheiro", width=96, height=30) if fc else None
            ep = Entrada(caixa, "decimal", width=110, height=30) if fp else None
            for c, e in ((1, ec), (2, ep)):
                if e:
                    e.grid(row=i, column=c, padx=6, pady=3, sticky="w")
                else:
                    ctk.CTkLabel(caixa, text="✓", text_color=VERDE, width=40).grid(row=i, column=c, padx=6)
            self.campos.append((pasta, idx, ec, ep))
        if len(itens) > 80:
            ctk.CTkLabel(caixa, text=f"e mais {len(itens) - 80}: rode de novo depois de salvar.", font=F(11), text_color=MUDO).grid(
                row=len(self.itens) + 1, column=0, sticky="w", padx=6, pady=6)
        dica_t = ctk.CTkLabel(self.corpo, text="Dica: ponha o peso de uma variação e rode “Resolver avisos” de novo: o robô estima o das "
                                                "outras pelo tamanho da caixa.", font=F(11), text_color=MUDO, anchor="w", justify="left",
                              wraplength=660)
        dica_t.grid(row=1, column=0, columnspan=2, sticky="w", padx=18, pady=(0, 10))
        self.botao_ok("Salvar", self.salvar)

    def salvar(self):
        por_pasta = {}
        for pasta, idx, ec, ep in self.campos:
            c = ec.valor() if ec else None
            p = ep.valor() if ep else None
            if c is not None or p:
                por_pasta.setdefault(pasta, []).append((idx, c, p))
        n = 0
        for pasta, mudas in por_pasta.items():
            f = motor.ler_ficha(pasta)
            d = json.loads(json.dumps(f.dados))
            vs = recursos._vs(d)
            for idx, c, p in mudas:
                if idx >= len(vs):
                    continue
                if c is not None:
                    vs[idx]["custo"] = c
                if p:
                    emb = vs[idx].get("embalagem") if isinstance(vs[idx].get("embalagem"), dict) else {}
                    emb["peso_kg"] = p
                    vs[idx]["embalagem"] = emb
                n += 1
            motor.salvar_ficha(f, d)
        self.destroy()
        self.app.recarregar()
        self.app.toast(f"{n} variação(ões) atualizada(s)" if n else "Nada preenchido", VERDE if n else ROXO)


class Atualizar(Dialogo):
    """Mostra o que mudou na versão nova e instala com um clique."""

    def __init__(self, app, nova):
        atual = recursos.versao_local().get("versao") or "—"
        super().__init__(app, f"Versão {nova.get('versao')} do robô",
                         f"Você está na versão {atual}. Suas fichas, login e configurações não mudam.", larg=600, alt=460)
        self.nova = nova
        caixa = ctk.CTkScrollableFrame(self.corpo, fg_color="transparent")
        caixa.grid(row=0, column=0, columnspan=2, sticky="nsew", padx=12, pady=(10, 6))
        self.corpo.grid_rowconfigure(0, weight=1)
        self.corpo.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(caixa, text="O que tem de novo", font=F(14, "bold"), text_color=TEXTO, anchor="w").pack(fill="x", pady=(2, 4))
        for t in nova.get("novidades") or ["Melhorias e correções."]:
            ctk.CTkLabel(caixa, text="✦  " + t, anchor="w", justify="left", wraplength=500, fg_color=ROXO_CLARO, text_color=ROXO_TXT,
                         corner_radius=10, padx=12, pady=6, font=F(12)).pack(fill="x", pady=2)
        self.info = ctk.CTkLabel(self.corpo, text="", font=F(12), text_color=MUDO, anchor="w")
        self.info.grid(row=1, column=0, columnspan=2, sticky="w", padx=18, pady=(0, 10))
        self.botao_ok("Atualizar agora", self.atualizar)

    def atualizar(self):
        self.bt_ok.configure(state="disabled", text="Atualizando…")
        self.info.configure(text="Baixando a versão nova. O robô reabre no fim.")
        self.app.instalar_versao(self.nova, self)
        self.after(600, self.destroy)


class ImportarLinks(Dialogo):
    """Cola um ou vários links de anúncios do Mercado Livre e o robô cria uma ficha de cada."""

    def __init__(self, app, texto=""):
        super().__init__(app, "Importar link do Mercado Livre",
                         "Cole o link do anúncio (pode colar vários, um por linha). O robô abre cada um e cria a ficha com fotos, "
                         "medidas e características.", larg=680, alt=480)
        if not texto:
            try:
                texto = app.clipboard_get()
            except Exception:
                texto = ""
            texto = "\n".join(recursos.links_ml(texto[:20000]))
        self.caixa = ctk.CTkTextbox(self.corpo, height=200, corner_radius=10, border_width=2, border_color=BORDA, fg_color=PAINEL,
                                    font=F(12), wrap="none")
        self.caixa.grid(row=0, column=0, columnspan=2, sticky="nsew", padx=16, pady=(16, 6))
        self.corpo.grid_rowconfigure(0, weight=1)
        self.corpo.grid_columnconfigure(0, weight=1)
        if texto:
            self.caixa.insert("1.0", texto)
        self.caixa.bind("<KeyRelease>", lambda e: self.contar())
        self.caixa.bind("<<Paste>>", lambda e: self.after(50, self.contar), add="+")
        self.info = ctk.CTkLabel(self.corpo, text="", font=F(12), text_color=MUDO, anchor="w")
        self.info.grid(row=1, column=0, columnspan=2, sticky="w", padx=18, pady=(0, 14))
        self.botao_ok("Importar", self.importar)
        self.contar()
        self.after(200, self.caixa.focus_set)

    def links(self):
        return recursos.links_ml(self.caixa.get("1.0", "end"))

    def contar(self):
        n = len(self.links())
        self.info.configure(text=f"{n} anúncio(s) encontrado(s)." if n else "Nenhum link do Mercado Livre ainda. Cole com Ctrl+V.")
        self.bt_ok.configure(text=f"Importar {n}" if n > 1 else "Importar", state="normal" if n else "disabled")

    def importar(self):
        links = self.links()
        if not links:
            return
        self.destroy()
        self.app.importar_links(links)


# ------------------------------------------------------------------ página Concorrentes

class PaginaConcorrentes(ctk.CTkFrame):
    def __init__(self, master, app):
        super().__init__(master, fg_color="transparent")
        self.app = app
        self.linhas = []
        self.ultimos = {}
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(2, weight=1)
        cab = Cabecalho(self, "Concorrentes", "Anúncios do Mercado Livre que você vigia. O robô confere o preço e avisa quem mudou.")
        cab.grid(row=0, column=0, sticky="ew", padx=24, pady=(20, 10))
        self.lbl = cab.sub
        self.bt = Botao(cab.acoes, "⟳  Conferir preços agora", "primario", width=200, height=38, command=self.conferir)
        self.bt.pack(side="left")
        add = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=16, border_width=1, border_color=BORDA)
        add.grid(row=1, column=0, sticky="ew", padx=24, pady=(0, 10))
        add.grid_columnconfigure(0, weight=1)
        self.link = Entrada(add, placeholder_text="Cole o link de um anúncio do Mercado Livre para vigiar", height=38)
        self.link.grid(row=0, column=0, sticky="ew", padx=(12, 8), pady=12)
        self.link.bind("<Return>", lambda e: self.adicionar())
        Botao(add, "◎  Vigiar", "ml", width=110, height=38, command=self.adicionar).grid(row=0, column=1, padx=(0, 8))
        self.filtro = Entrada(add, placeholder_text="⌕  Filtrar", width=170, height=38, ao_mudar=self.carregar)
        self.filtro.grid(row=0, column=2, padx=(0, 12))
        self.lista = ctk.CTkScrollableFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        self.lista.grid(row=2, column=0, sticky="nsew", padx=24, pady=(0, 16))
        self.lista.grid_columnconfigure(0, weight=1)

    def ao_mostrar(self):
        self.carregar()

    def _quando(self, iso):
        from datetime import datetime
        try:
            return datetime.fromisoformat(iso).strftime("%d/%m %H:%M")
        except Exception:
            return ""

    def carregar(self):
        for w in self.lista.winfo_children():
            w.destroy()
        todos = recursos.ler_concorrentes()
        termo = self.filtro.get().strip().lower()
        lista = [c for c in todos if all(p in (c.get("titulo") or c["link"]).lower() for p in termo.split())]
        ult = recursos.ultima_conferencia()
        self.lbl.configure(text=f"{len(todos)} anúncio(s) vigiado(s)" + (f"  ·  última conferência {self._quando(ult)}" if ult else
                                                                       "  ·  ainda não conferido"))
        if not lista:
            ctk.CTkLabel(self.lista, text="Nenhum anúncio vigiado ainda.\n\nNa página Mercado Livre, toque em ◎ num anúncio, ou cole o link acima.",
                         text_color=MUDO, font=F(13), justify="center").grid(row=0, column=0, pady=60)
            return
        for r, c in enumerate(lista):
            self._linha(c).grid(row=r, column=0, sticky="ew", padx=6, pady=4)

    def _linha(self, c):
        l = ctk.CTkFrame(self.lista, fg_color=CARD, corner_radius=14, border_width=1, border_color=BORDA)
        l.grid_columnconfigure(1, weight=1)
        foto = foto_concorrente(c["link"])
        img = miniatura(foto, 54) if foto else None
        self.app._refs.append(img)
        ctk.CTkLabel(l, text="" if img else "ML", image=img, width=54, height=54, fg_color="transparent" if img else AMARELO_ML,
                     text_color="#1e293b", corner_radius=12, font=F(13, "bold")).grid(row=0, column=0, rowspan=2, padx=12, pady=10)
        ctk.CTkLabel(l, text=cortar(c.get("titulo") or c["link"], 80), font=F(14, "bold"), anchor="w", text_color=TEXTO).grid(
            row=0, column=1, sticky="ew", pady=(10, 0))
        hist = c.get("historico") or []
        precos = [h["preco"] for h in hist]
        partes = []
        if len(precos) > 1:
            partes.append("histórico: " + " → ".join("R$ " + reais(p) for p in precos[-4:]))
        if precos:
            partes.append(f"menor já visto R$ {reais(min(precos))}")
        if c.get("conferido"):
            partes.append(f"conferido {self._quando(c['conferido'])}")
        elif c.get("adicionado"):
            partes.append(f"vigiando desde {self._quando(c['adicionado'])}")
        ctk.CTkLabel(l, text="  ·  ".join(partes), font=F(12), text_color=MUDO, anchor="w").grid(row=1, column=1, sticky="ew", pady=(0, 10))
        dir_ = ctk.CTkFrame(l, fg_color="transparent")
        dir_.grid(row=0, column=2, rowspan=2, padx=8)
        atual = precos[-1] if precos else None
        ctk.CTkLabel(dir_, text=("R$ " + reais(atual)) if atual else "sem preço", font=F(17, "bold"),
                     text_color=VERDE if atual else MUDO).pack(anchor="e")
        if len(precos) > 1 and abs(precos[-1] - precos[-2]) >= 0.01:
            dif = precos[-1] - precos[-2]
            pct = dif / precos[-2] * 100 if precos[-2] else 0
            Chip(dir_, f"{'▲' if dif > 0 else '▼'} R$ {reais(abs(dif))} ({abs(pct):.0f}%)", AMBAR if dif < 0 else "#64748b").pack(anchor="e", pady=(2, 0))
        elif c.get("conferido") and not atual:
            Chip(dir_, "pausado ou sumiu", "#64748b").pack(anchor="e", pady=(2, 0))
        bts = ctk.CTkFrame(l, fg_color="transparent")
        bts.grid(row=0, column=3, rowspan=2, padx=(4, 12))
        dica(Botao(bts, "↗", "suave", width=36, height=30, command=lambda: webbrowser.open(c["link"])), "Abrir o anúncio").pack(side="left", padx=2)
        dica(Botao(bts, "✕", "perigo", width=36, height=30, command=lambda: self.remover(c)), "Parar de vigiar").pack(side="left", padx=2)
        return l

    def adicionar(self):
        link = self.link.get().strip()
        if not motor.eh_link_ml(link):
            self.app.toast("Cole um link de anúncio do Mercado Livre", AMBAR)
            return
        novo = recursos.vigiar(link)
        self.link.definir(None)
        self.carregar()
        self.app.toast("Vigiando. Toque em Conferir preços para buscar o preço." if novo else "Esse anúncio já está na lista",
                       VERDE if novo else ROXO)

    def remover(self, c):
        if messagebox.askyesno("Parar de vigiar", f"Parar de vigiar “{cortar(c.get('titulo') or c['link'], 60)}”?"):
            recursos.parar_de_vigiar(c["link"])
            self.carregar()

    def conferir(self):
        links = [c["link"] for c in recursos.ler_concorrentes()]
        if not links:
            self.app.toast("Nenhum anúncio na lista", AMBAR)
            return
        if self.app.ocupado_ml or self.app.rodando:
            self.app.toast("Espere o robô terminar o que está fazendo", AMBAR)
            return
        self.app.ocupado_ml = True
        self.app.parar_flag.clear()
        self.bt.configure(state="disabled", text="Conferindo…")
        self.app.status("Abrindo o Mercado Livre no navegador do robô…")
        self.app.tarefas.put(("concorrentes", links))

    def fim(self, mudaram):
        self.bt.configure(state="normal", text="⟳  Conferir preços agora")
        self.carregar()
        msg = f"{mudaram} concorrente(s) mudaram de preço" if mudaram else "Nenhum concorrente mudou de preço"
        self.app.status(msg + ".")
        self.app.toast(msg, AMBAR if mudaram else VERDE)


# ------------------------------------------------------------------ colar ficha da IA

class Colar(ctk.CTkToplevel):
    """Cola a resposta da IA, junta as fotos (uma por vez ou várias) e cria a pasta do produto."""

    def __init__(self, app):
        super().__init__(app)
        self.app = app
        self.fotos = []
        self.title("Colar ficha da IA")
        self.configure(fg_color=FUNDO)
        self.geometry(f"820x680+{app.winfo_rootx() + 120}+{app.winfo_rooty() + 40}")
        self.transient(app)
        self.after(150, self.grab_set)
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=1)
        ctk.CTkLabel(self, text="Cole a resposta da IA (com o bloco ```json```)", font=F(17, "bold"), text_color=TEXTO,
                     anchor="w").grid(row=0, column=0, sticky="ew", padx=20, pady=(18, 8))
        self.texto = ctk.CTkTextbox(self, font=ctk.CTkFont(family="Consolas", size=12), fg_color=PAINEL, corner_radius=12,
                                    border_width=1, border_color=BORDA)
        self.texto.grid(row=1, column=0, sticky="nsew", padx=20)
        try:
            clip = app.clipboard_get()
            if "{" in clip and "nome" in clip:
                self.texto.insert("1.0", clip)
        except Exception:
            pass
        caixa = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=12, border_width=1, border_color=BORDA)
        caixa.grid(row=2, column=0, sticky="ew", padx=20, pady=12)
        caixa.grid_columnconfigure(0, weight=1)
        topo = ctk.CTkFrame(caixa, fg_color="transparent")
        topo.grid(row=0, column=0, sticky="ew", padx=12, pady=(10, 4))
        self.lbl = ctk.CTkLabel(topo, text="Fotos: nenhuma ainda", font=F(13, "bold"), text_color=TEXTO)
        self.lbl.pack(side="left")
        Botao(topo, "Limpar", "fantasma", width=70, height=28, font=F(12), command=self.limpar).pack(side="right", padx=(6, 0))
        Botao(topo, "＋  Adicionar fotos", "suave", width=150, height=28, font=F(12, "bold"), command=self.escolher).pack(side="right")
        self.tira = ctk.CTkScrollableFrame(caixa, height=96, orientation="horizontal", fg_color="transparent")
        self.tira.grid(row=1, column=0, sticky="ew", padx=8, pady=(0, 8))
        self.msg = ctk.CTkLabel(self, text="", text_color=VERMELHO, anchor="w", wraplength=760, justify="left", font=F(12))
        self.msg.grid(row=3, column=0, sticky="ew", padx=20)
        bts = ctk.CTkFrame(self, fg_color="transparent")
        bts.grid(row=4, column=0, sticky="e", padx=20, pady=(6, 18))
        Botao(bts, "Cancelar", "fantasma", width=110, command=self.destroy).pack(side="left", padx=6)
        Botao(bts, "Criar ficha", "primario", width=150, command=self.criar).pack(side="left")
        self.desenhar()

    def escolher(self):
        arqs = filedialog.askopenfilenames(parent=self, title="Fotos do produto (pode escolher uma ou várias)",
                                           filetypes=[("Fotos", "*.jpg *.jpeg *.png *.webp *.bmp *.gif *.jfif"), ("Todos", "*.*")])
        ja = {str(Path(a).resolve()).lower() for a in self.fotos}
        for a in arqs or []:
            if str(Path(a).resolve()).lower() not in ja:
                self.fotos.append(a)   # junta com as que já estavam
                ja.add(str(Path(a).resolve()).lower())
        self.desenhar()
        self.lift()

    def limpar(self):
        self.fotos = []
        self.desenhar()

    def desenhar(self):
        for w in self.tira.winfo_children():
            w.destroy()
        self.lbl.configure(text=f"Fotos: {len(self.fotos)}" if self.fotos else "Fotos: nenhuma ainda (pode adicionar uma por vez)")
        for i, a in enumerate(self.fotos):
            cel = ctk.CTkFrame(self.tira, fg_color=SUAVE, corner_radius=10)
            cel.grid(row=0, column=i, padx=4)
            img = miniatura(a, 64, 10)
            self.app._refs.append(img)
            ctk.CTkLabel(cel, text="" if img else Path(a).name[:8], image=img, width=64, height=64).pack(padx=4, pady=(4, 0))
            Botao(cel, "✕", "perigo", width=24, height=20, font=F(10), command=lambda a=a: (self.fotos.remove(a), self.desenhar())).pack(pady=(2, 4))

    def criar(self):
        texto = self.texto.get("1.0", "end").strip()
        if not texto:
            self.msg.configure(text="Cole a resposta da IA primeiro.")
            return
        try:
            pasta = motor.criar_pasta_da_ficha(self.app.pasta(), texto, self.fotos)
        except Exception as e:
            self.msg.configure(text=str(e))
            return
        f = motor.ler_ficha(pasta)
        if f.faltando:
            messagebox.showinfo("Fotos", "A ficha cita fotos que não foram escolhidas:\n\n" + "\n".join(f.faltando) +
                                "\n\nVocê pode adicionar depois no editor.", parent=self)
        self.destroy()
        self.app.ir("fichas")
        self.app.recarregar(selecionar=pasta)
        self.app.toast("Ficha criada")


if __name__ == "__main__":
    os.chdir(motor.BASE)
    App().mainloop()
