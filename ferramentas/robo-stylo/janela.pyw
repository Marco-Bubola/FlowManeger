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
import subprocess
import sys
import tempfile
import threading
import traceback
import webbrowser
from pathlib import Path
from tkinter import filedialog, messagebox

import customtkinter as ctk

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

FUNDO = ("#f5f4fa", "#0e1117")
PAINEL = ("#ffffff", "#161b26")
CARD = ("#ffffff", "#1b2130")
CARD_H = ("#faf8ff", "#202739")
BORDA = ("#e6e3f0", "#2a3142")
SUAVE = ("#f3f1fa", "#20273a")
TEXTO = ("#1d1a2b", "#e9e7f2")
MUDO = ("#6b6880", "#9896ab")
ROXO_CLARO = ("#ede9fe", "#2c2450")
ROXO_TXT = ("#6d28d9", "#c4b5fd")
LATERAL = ("#1c1033", "#090612")

STATUS = {
    "pronta": ("Pronta", VERDE),
    "erro": ("Com erro", VERMELHO),
    "feita": ("Cadastrada", ROXO),
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
    vendas = [motor.numero(v.get("venda")) for v in f.variacoes if motor.numero(v.get("venda")) > 0]
    estoque = sum(int(motor.numero(v.get("estoque"))) for v in f.variacoes)
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
            if not ficha.pronta:
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
        if ficha.avisos and ficha.status == "pronta":
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

class App(ctk.CTk):
    def __init__(self):
        self.cfg = motor.ler_config()
        ctk.set_appearance_mode("dark" if self.cfg["robo"].get("tema") == "escuro" else "light")
        super().__init__()
        self.title("Robô de cadastro · Móveis Stylo")
        sw, sh = self.winfo_screenwidth(), self.winfo_screenheight()
        larg, alt = min(1320, sw - 40), min(820, sh - 90)
        self.geometry(f"{larg}x{alt}+{max((sw - larg) // 2, 0)}+{max((sh - alt) // 2 - 20, 0)}")
        self.minsize(1060, 660)
        self.configure(fg_color=FUNDO)
        icone = Path(__file__).with_name("icone.ico")
        if icone.exists() and sys.platform.startswith("win"):
            try:
                self.iconbitmap(str(icone))
            except Exception:
                pass

        self.fila = queue.Queue()      # robô → janela
        self.tarefas = queue.Queue()   # janela → robô (o navegador vive numa thread só)
        self.cartoes = []
        self.atual = None
        self.rodando = False
        self.ocupado_ml = False
        self.parar_flag = threading.Event()
        self.site = None
        self.ml = None
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
        self.recarregar()
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
        itens = [("inicio", "⌂   Início"), ("fichas", "☰   Fichas"), ("ml", "⌕   Mercado Livre"), ("concorrentes", "◎   Concorrentes"),
                 ("feitas", "✓   Cadastradas"), ("config", "⚙   Configurações")]
        for i, (chave, texto) in enumerate(itens, 1):
            b = ctk.CTkButton(lat, text=texto, anchor="w", height=42, corner_radius=11, fg_color="transparent",
                              hover_color="#2d1b52", text_color="#d6d0ea", font=F(14, "bold"),
                              command=lambda c=chave: self.ir(c))
            b.grid(row=i, column=0, sticky="ew", padx=12, pady=3)
            self.nav[chave] = b
        Botao(lat, "Copiar questionário p/ IA", "fantasma", text_color="#d6d0ea", border_color="#3b2a63",
              hover_color="#2d1b52", command=self.questionario).grid(row=9, column=0, sticky="ew", padx=14, pady=(0, 8))
        self.tema = ctk.CTkSwitch(lat, text="Modo escuro", text_color="#d6d0ea", progress_color=ROXO, font=F(13),
                                  command=self.trocar_tema)
        self.tema.grid(row=10, column=0, sticky="w", padx=18, pady=(4, 10))
        if ctk.get_appearance_mode() == "Dark":
            self.tema.select()
        self.chip_nav = Chip(lat, "Navegador fechado", "#3b2a63")
        self.chip_nav.grid(row=11, column=0, sticky="w", padx=16, pady=(0, 6))
        self.lbl_site = ctk.CTkLabel(lat, text="", text_color="#8b84a8", font=F(11), anchor="w", justify="left", wraplength=190)
        self.lbl_site.grid(row=12, column=0, sticky="ew", padx=18, pady=(0, 18))

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
        fichas = motor.listar_fichas(self.pasta())
        self.paginas["fichas"].carregar(fichas, selecionar)
        n = len(fichas)
        self.nav["fichas"].configure(text="☰   Fichas" + (f"   ·   {n}" if n else ""))
        if self.pagina is self.paginas["feitas"]:
            self.paginas["feitas"].carregar()
        if self.pagina is self.paginas.get("inicio"):
            self.paginas["inicio"].desenhar()

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
    def _ao_abrir(self):
        r = self.cfg["robo"]
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
                    if not (info and info.get("imagens")):
                        info = ml.extrair(link)
                    nomes = motor.salvar_fotos_ml(info, pasta, ml.baixar)
                    if not nomes:
                        falhas.append((f.nome, "o anúncio não tem fotos que dê para baixar"))
                        continue
                    d = json.loads(json.dumps(f.dados))
                    vs = recursos._vs(d)
                    if vs and not any(v.get("fotos") for v in vs):
                        vs[0]["fotos"] = nomes
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
        self.clipboard_clear()
        self.clipboard_append(arq.read_text(encoding="utf-8"))
        self.toast("Questionário copiado. Cole numa IA (Ctrl+V) com as fotos.")

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
    FILTROS = ["Todas", "Prontas", "Com erro", "Com aviso", "Sob encomenda"]
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
        self.lbl_cont.configure(text=f"{len(fichas)} na fila  ·  {prontas} prontas" + (f"  ·  {erros} com erro" if erros else ""))
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
            if c.check and c.ficha.pronta and c.ficha.status not in ("feita", "ja") and c.winfo_manager():
                c.var.set(valor)
        self.atualizar_botao()

    def alvos(self):
        """As fichas marcadas; se nenhuma, as que aparecem com o filtro atual."""
        marcadas = [c.ficha for c in self.cartoes if c.var.get()]
        return marcadas or [c.ficha for c in self.cartoes if c.winfo_manager()]

    def marcadas(self):
        return [c for c in self.cartoes if c.var.get() and c.ficha.pronta]

    def atualizar_botao(self):
        n = len(self.marcadas())
        self.bt_cad.configure(text=f"Cadastrar {n} {'produto' if n == 1 else 'produtos'} no site" if n else "Marque as fichas para cadastrar",
                              state="normal" if n and not self.app.rodando else "disabled")

    def cadastrar_marcadas(self, cartoes=None):
        cartoes = cartoes or self.marcadas()
        if not cartoes:
            return
        self.app.mapa = {id(c.ficha): c for c in cartoes}
        self.app.cadastrar([c.ficha for c in cartoes])

    def rodando(self, sim_):
        self.bt_parar.configure(state="normal" if sim_ else "disabled")
        for c in self.cartoes:
            if c.check:
                c.check.configure(state="disabled" if sim_ or not c.ficha.pronta or c.ficha.status in ("feita", "ja") else "normal")
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
        if f.avisos and f.status not in ("feita", "ja"):
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
        if f.mensagem and f.status in ("falhou", "feita", "ja"):
            faixa(("✓  " if f.status == "feita" else "•  ") + f.mensagem, ROXO_CLARO, ROXO_TXT)
            if f.status in ("feita", "ja"):
                por(Botao(self.det, "⟳  Atualizar no site com esta ficha", "suave", height=32, font=F(12, "bold"), state=trava,
                          command=lambda: app.atualizar_no_site([f])), sticky="w")
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
        ctk.CTkLabel(c2, text="").grid(row=13, column=0, pady=4)
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
        self.auto_imp.grid(row=7, column=0, columnspan=2, sticky="w", padx=20, pady=(12, 18))
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
        r["preco_de_pct"] = str(self.campos["preco_de_pct"].valor() or "")
        r["folga_caixa"] = decimal_txt(self.campos["folga_caixa"].valor() or 4).replace(",", ".")
        r["completar_ao_importar"] = "sim" if self.auto_imp.get() else "nao"
        r["prazo_padrao"] = str(self.campos["prazo_padrao"].valor() or 15)
        r["atualizar_sozinho"] = "sim" if self.auto_atu.get() else "nao"
        r["copia_diaria"] = "sim" if self.copia.get() else "nao"
        r["pasta_copias"] = self.campos["pasta_copias"].get().strip()
        motor.salvar_config(self.app.cfg)
        if self.app.site and not self.app.rodando:
            self.app.tarefas.put(("fechar_site", None))
        self.app.recarregar()
        self.app.toast("Configurações salvas")


# ------------------------------------------------------------------ editor de ficha

class Secao(ctk.CTkFrame):
    def __init__(self, master, titulo, sub=""):
        super().__init__(master, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
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
    COLS = [("Variação", 140), ("Código", 104), ("Custo R$", 84), ("Venda R$", 84), ("Preço “de” R$", 88), ("Estoque", 62), ("Margem", 56)]

    def __init__(self, master, editor, v):
        super().__init__(master, fg_color=SUAVE, corner_radius=12)
        self.editor = editor
        self.original = v
        emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
        med = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
        l1 = ctk.CTkFrame(self, fg_color="transparent")
        l1.pack(fill="x", padx=8, pady=(8, 2))
        self.valor = Entrada(l1, width=140, placeholder_text="ex.: 30 cm · Preto")
        self.valor.bind("<FocusOut>", lambda e: editor.atualizar_usos(), add="+")
        self.codigo = Entrada(l1, width=104, placeholder_text="automático")
        self.custo = Entrada(l1, "dinheiro", width=84, ao_mudar=self.calc)
        self.venda = Entrada(l1, "dinheiro", width=84, ao_mudar=self.calc)
        self.de = Entrada(l1, "dinheiro", width=88)
        self.estoque = Entrada(l1, "inteiro", width=62, placeholder_text="0")
        self.margem = ctk.CTkLabel(l1, text="—", width=56, font=F(13, "bold"), text_color=MUDO)
        for w in (self.valor, self.codigo, self.custo, self.venda, self.de, self.estoque, self.margem):
            w.pack(side="left", padx=3)
        Botao(l1, "✕", "perigo", width=34, height=32, command=lambda: editor.remover_variacao(self)).pack(side="left", padx=(6, 0))
        l2 = ctk.CTkFrame(self, fg_color="transparent")
        l2.pack(fill="x", padx=8, pady=(0, 8))

        def peq(rot, tipo, larg=58, ph=""):
            ctk.CTkLabel(l2, text=rot, font=F(11), text_color=MUDO).pack(side="left", padx=(6, 2))
            e = Entrada(l2, tipo, width=larg, height=28, font=F(12), placeholder_text=ph)
            e.pack(side="left", padx=1)
            return e
        ctk.CTkLabel(l2, text="Caixa:", font=F(11, "bold"), text_color=MUDO).pack(side="left", padx=(4, 0))
        self.peso = peq("peso kg", "decimal", 54)
        self.cc = peq("C", "decimal", 46, "cm")
        self.cl = peq("L", "decimal", 46, "cm")
        self.ca = peq("A", "decimal", 46, "cm")
        ctk.CTkLabel(l2, text="Peça:", font=F(11, "bold"), text_color=MUDO).pack(side="left", padx=(12, 0))
        self.pa = peq("alt.", "decimal", 46, "cm")
        self.pl = peq("larg.", "decimal", 46, "cm")
        self.pp = peq("prof.", "decimal", 46, "cm")
        self.pd = peq("Ø", "decimal", 46, "cm")

        self.valor.definir(v.get("valor"))
        self.codigo.definir(v.get("codigo"))
        self.custo.definir(v.get("custo"))
        self.venda.definir(v.get("venda"))
        self.de.definir(v.get("preco_tabela"))
        self.estoque.definir(v.get("estoque") if v.get("estoque") not in (None, "") else 0)
        self.peso.definir(emb.get("peso_kg"))
        self.cc.definir(emb.get("comprimento_cm"))
        self.cl.definir(emb.get("largura_cm"))
        self.ca.definir(emb.get("altura_cm"))
        self.pa.definir(med.get("altura"))
        self.pl.definir(med.get("largura"))
        self.pp.definir(med.get("profundidade"))
        self.pd.definir(med.get("diametro"))
        self.calc()

    def rotulo(self):
        return self.valor.get().strip() or f"variação {self.editor.linhas.index(self) + 1}"

    def calc(self):
        venda = self.venda.valor()
        custo = self.custo.valor()
        if custo is None:
            custo = self.editor.soma_custo()
        if venda and custo:
            m = (venda - custo) / venda * 100
            cor = VERDE if m >= 30 else AMBAR if m >= 10 else VERMELHO
            self.margem.configure(text=f"{m:.0f}%", text_color=cor)
        else:
            self.margem.configure(text="—", text_color=MUDO)
        if hasattr(self.editor, "lbl_conta"):
            self.editor._conta()

    def dados(self):
        v = dict(self.original)
        v["valor"] = self.valor.valor() or None
        v["codigo"] = self.codigo.valor()
        v["custo"] = self.custo.valor()
        v["venda"] = self.venda.valor()
        v["preco_tabela"] = self.de.valor()
        v["estoque"] = self.estoque.valor() or 0
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
        self.uso = uso          # "todas", "nao" ou a LinhaVariacao
        self.removida = False


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
        Botao(s.acoes, "＋  Material", "suave", width=110, command=lambda: self._linha_custo()).pack(side="right")
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
            b.configure(text=f"{nome}  ·  {n}" if n else nome, border_color=AMBAR if n else BORDA,
                        text_color=("#b45309", "#fcd34d") if n else TEXTO)

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
        s = Secao(self.corpo, "Variações", "Cada variação vira um produto no site, com o próprio código, preço e estoque.")
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
        gerar = ctk.CTkFrame(s, fg_color=SUAVE, corner_radius=12)
        gerar.grid(row=1, column=0, columnspan=4, sticky="ew", padx=20, pady=(6, 8))
        ctk.CTkLabel(gerar, text="Gerar:", font=F(12, "bold"), text_color=MUDO).pack(side="left", padx=(12, 6), pady=10)
        self.g_tam = Entrada(gerar, width=200, height=30, placeholder_text="tamanhos: 25 cm, 30 cm")
        self.g_tam.pack(side="left", padx=4)
        ctk.CTkLabel(gerar, text="×", font=F(14, "bold"), text_color=MUDO).pack(side="left", padx=4)
        self.g_cor = Entrada(gerar, width=220, height=30, placeholder_text="cores: Preto, Branco")
        self.g_cor.pack(side="left", padx=4)
        Botao(gerar, "Gerar variações", "suave", width=130, height=30, font=F(12, "bold"), command=self.gerar).pack(side="left", padx=8)
        cab = ctk.CTkFrame(s, fg_color="transparent")
        cab.grid(row=2, column=0, columnspan=4, sticky="ew", padx=28, pady=(4, 0))
        for rot, larg in LinhaVariacao.COLS:
            ctk.CTkLabel(cab, text=rot.upper(), width=larg, anchor="w", font=F(10, "bold"), text_color=MUDO).pack(side="left", padx=3)
        self.caixa_var = ctk.CTkFrame(s, fg_color="transparent")
        self.caixa_var.grid(row=3, column=0, columnspan=4, sticky="ew", padx=20)
        for v in d.get("variacoes") or []:
            if isinstance(v, dict):
                self.adicionar_variacao(v, redesenhar=False)
        self._desenhar_var()
        Botao(s, "＋  Adicionar variação", "suave", width=170, command=lambda: self.adicionar_variacao()).grid(
            row=4, column=0, columnspan=4, sticky="w", padx=20, pady=(8, 16))

    def _desenhar_var(self):
        for i, l in enumerate(self.linhas):
            l.pack_forget()
        for l in self.linhas:
            l.pack(fill="x", pady=4)

    def adicionar_variacao(self, v=None, redesenhar=True):
        if v is None:
            base = self.linhas[-1].dados() if self.linhas else {}
            v = {"valor": None, "codigo": "", "custo": base.get("custo"), "venda": base.get("venda"), "preco_tabela": base.get("preco_tabela"),
                 "estoque": 0, "embalagem": base.get("embalagem") or {}, "medidas_peca_cm": {}, "fotos": []}
        if len(self.linhas) >= 40:
            messagebox.showinfo("Variações", "O máximo é 40 variações.")
            return
        l = LinhaVariacao(self.caixa_var, self, v)
        self.linhas.append(l)
        if redesenhar:
            self._desenhar_var()
            self.atualizar_usos()
            if len(self.linhas) > 1 and self.var_por.get() == "Sem variação":
                self.var_por.set("Tamanho")
            l.valor.focus_set()

    def remover_variacao(self, linha):
        if len(self.linhas) <= 1:
            messagebox.showinfo("Variações", "A ficha precisa de pelo menos 1 item.")
            return
        self.linhas.remove(linha)
        for f in self.fotos:
            if f.uso is linha:
                f.uso = "todas"
        linha.destroy()
        self._desenhar_var()
        self.atualizar_usos()

    def gerar(self):
        tam = [t.strip() for t in self.g_tam.get().split(",") if t.strip()]
        cor = [c.strip() for c in self.g_cor.get().split(",") if c.strip()]
        if not tam and not cor:
            return
        valores = [f"{t} · {c}" for t in tam for c in cor] if tam and cor else (tam or cor)
        if len(valores) > 40:
            messagebox.showinfo("Variações", f"Isso daria {len(valores)} variações; o máximo é 40.")
            return
        existentes = {l.valor.get().strip().lower() for l in self.linhas}
        base = self.linhas[0].dados() if self.linhas else {}
        vazias = [l for l in self.linhas if not l.valor.get().strip()]
        for l in vazias:
            if len(self.linhas) > 1 or valores:
                self.linhas.remove(l)
                l.destroy()
        novos = 0
        for v in valores:
            if v.lower() in existentes:
                continue
            self.adicionar_variacao({"valor": v, "codigo": "", "custo": base.get("custo"), "venda": base.get("venda"),
                                     "preco_tabela": base.get("preco_tabela"), "estoque": base.get("estoque") or 0,
                                     "embalagem": base.get("embalagem") or {}, "medidas_peca_cm": {}, "fotos": []}, redesenhar=False)
            novos += 1
        self.var_por.set("Tamanho e cor" if tam and cor else "Tamanho" if tam else "Cor")
        self._desenhar_var()
        self.atualizar_usos()
        self.g_tam.definir(None)
        self.g_cor.definir(None)
        self.app.toast(f"{novos} variação(ões) criada(s)")

    # fotos
    def _fotos(self):
        s = Secao(self.corpo, "Fotos", "A primeira é a capa. Escolha se cada foto vale para todas as variações ou só para uma.")
        s.grid(row=4, column=0, sticky="ew", padx=10, pady=8)
        Botao(s.acoes, "✦  Deixar prontas", "fantasma", width=140, command=lambda: FotosProntas(self)).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "Colar imagem", "fantasma", width=120, command=self.colar_imagem).pack(side="right", padx=(6, 0))
        Botao(s.acoes, "＋  Adicionar fotos", "suave", width=150, command=self.escolher_fotos).pack(side="right")
        self.grade_fotos = ctk.CTkFrame(s, fg_color="transparent")
        self.grade_fotos.grid(row=1, column=0, columnspan=4, sticky="ew", padx=16, pady=(4, 16))
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
            elif em >= cheias or len(em) > 1:
                uso = "todas"
            else:
                i = next(iter(em))
                uso = self.linhas[i] if i < len(self.linhas) and len(cheias) > 1 else "todas"
            self.fotos.append(FotoEd(p, uso=uso))

    def _opcoes_uso(self):
        return ["Todas as variações"] + [f"Só: {l.rotulo()}" for l in self.linhas] + ["Não usar"]

    def _uso_txt(self, f):
        if f.uso == "todas":
            return "Todas as variações"
        if f.uso == "nao":
            return "Não usar"
        return f"Só: {f.uso.rotulo()}" if f.uso in self.linhas else "Todas as variações"

    def atualizar_usos(self):
        self.desenhar_fotos()

    def desenhar_fotos(self):
        for w in self.grade_fotos.winfo_children():
            w.destroy()
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
            m = ctk.CTkOptionMenu(cel, values=opcoes, width=150, height=26, font=F(11), dropdown_font=F(11), corner_radius=8,
                                  fg_color=PAINEL, button_color=ROXO_CLARO, button_hover_color=("#ddd6fe", "#3a2f68"), text_color=TEXTO,
                                  command=lambda v, f=f: self._mudar_uso(f, v))
            m.set(self._uso_txt(f))
            m.pack(padx=8, pady=(4, 2))
            bts = ctk.CTkFrame(cel, fg_color="transparent")
            bts.pack(pady=(2, 8))
            pb = dict(width=32, height=26, font=F(12))
            dica(Botao(bts, "◀", "fantasma", command=lambda f=f: self._mover(f, -1), **pb), "Mover para a esquerda").pack(side="left", padx=1)
            dica(Botao(bts, "★", "fantasma", command=lambda f=f: self._mover(f, -999), **pb), "Usar como capa").pack(side="left", padx=1)
            dica(Botao(bts, "▶", "fantasma", command=lambda f=f: self._mover(f, 1), **pb), "Mover para a direita").pack(side="left", padx=1)
            Botao(bts, "✕", "perigo", command=lambda f=f: self._remover_foto(f), **pb).pack(side="left", padx=(6, 1))

    def _mudar_uso(self, f, txt):
        if txt == "Todas as variações":
            f.uso = "todas"
        elif txt == "Não usar":
            f.uso = "nao"
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
            tem_propria = any(f.uso in self.linhas for f in vis)
            for l, v in zip(self.linhas, d["variacoes"]):
                # mantém a ordem da tela: a primeira foto da variação vira a capa dela
                v["fotos"] = [f.caminho.name for f in vis if f.uso == "todas" or f.uso is l] if tem_propria else []
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
            fotos = [(str(f.caminho), f.removida, f.uso if isinstance(f.uso, str) else self.linhas.index(f.uso)
                      if f.uso in self.linhas else -1) for f in self.fotos]
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
