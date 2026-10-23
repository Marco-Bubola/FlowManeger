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

        self.paginas["fichas"] = PaginaFichas(self.area, self)
        self.paginas["ml"] = PaginaML(self.area, self)
        self.paginas["feitas"] = PaginaFeitas(self.area, self)
        self.paginas["config"] = PaginaConfig(self.area, self)
        self.ir("fichas")
        self.recarregar()
        self.after(120, self._ler_fila)
        threading.Thread(target=self._trabalhador, daemon=True).start()
        self.protocol("WM_DELETE_WINDOW", self.fechar)

    # ---------------------------------------------------------- montagem

    def _lateral(self):
        lat = ctk.CTkFrame(self, width=226, corner_radius=0, fg_color=LATERAL)
        lat.grid(row=0, column=0, rowspan=2, sticky="nsw")
        lat.grid_propagate(False)
        lat.grid_columnconfigure(0, weight=1)
        lat.grid_rowconfigure(7, weight=1)
        logo = ctk.CTkFrame(lat, fg_color="transparent")
        logo.grid(row=0, column=0, sticky="ew", padx=18, pady=(22, 26))
        ctk.CTkLabel(logo, text="MS", width=44, height=44, corner_radius=13, fg_color=ROXO, text_color="white",
                     font=F(17, "bold")).pack(side="left")
        txt = ctk.CTkFrame(logo, fg_color="transparent")
        txt.pack(side="left", padx=10)
        ctk.CTkLabel(txt, text="Móveis Stylo", text_color="white", font=F(15, "bold"), anchor="w").pack(anchor="w")
        ctk.CTkLabel(txt, text="Robô de cadastro", text_color="#a79fc4", font=F(12), anchor="w").pack(anchor="w")
        self.nav = {}
        itens = [("fichas", "☰   Fichas"), ("ml", "⌕   Mercado Livre"), ("feitas", "✓   Cadastradas"), ("config", "⚙   Configurações")]
        for i, (chave, texto) in enumerate(itens, 1):
            b = ctk.CTkButton(lat, text=texto, anchor="w", height=42, corner_radius=11, fg_color="transparent",
                              hover_color="#2d1b52", text_color="#d6d0ea", font=F(14, "bold"),
                              command=lambda c=chave: self.ir(c))
            b.grid(row=i, column=0, sticky="ew", padx=12, pady=3)
            self.nav[chave] = b
        Botao(lat, "Copiar questionário p/ IA", "fantasma", text_color="#d6d0ea", border_color="#3b2a63",
              hover_color="#2d1b52", command=self.questionario).grid(row=8, column=0, sticky="ew", padx=14, pady=(0, 8))
        self.tema = ctk.CTkSwitch(lat, text="Modo escuro", text_color="#d6d0ea", progress_color=ROXO, font=F(13),
                                  command=self.trocar_tema)
        self.tema.grid(row=9, column=0, sticky="w", padx=18, pady=(4, 10))
        if ctk.get_appearance_mode() == "Dark":
            self.tema.select()
        self.chip_nav = Chip(lat, "Navegador fechado", "#3b2a63")
        self.chip_nav.grid(row=10, column=0, sticky="w", padx=16, pady=(0, 6))
        self.lbl_site = ctk.CTkLabel(lat, text="", text_color="#8b84a8", font=F(11), anchor="w", justify="left", wraplength=190)
        self.lbl_site.grid(row=11, column=0, sticky="ew", padx=18, pady=(0, 18))

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

    def selecionar(self, cartao):
        self.paginas["fichas"].selecionar(cartao)

    def atualizar_botao(self):
        self.paginas["fichas"].atualizar_botao()

    def editar(self, ficha=None):
        if self.rodando:
            messagebox.showinfo("Robô", "Espere o cadastro terminar para editar.")
            return
        if ficha is not None and ficha.status == "feita":
            if not messagebox.askyesno("Ficha já cadastrada", "Esta ficha já foi cadastrada no site. Mudar a ficha não muda o produto no site.\n\nQuer criar uma cópia para editar?"):
                return
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
        self.ir("fichas")
        if ed:
            ed.destroy()
        self.recarregar(selecionar=pasta)

    def duplicar(self, ficha):
        destino = motor.duplicar_ficha(ficha, self.pasta())
        self.recarregar(selecionar=destino)
        self.ir("fichas")
        self.toast("Cópia criada")

    def excluir(self, ficha):
        if self.rodando:
            return
        if not messagebox.askyesno("Excluir ficha", f"Excluir a ficha “{ficha.nome}”?\n\nA pasta vai para produtos\\_lixeira (dá para recuperar)."):
            return
        motor.excluir_ficha(ficha, self.pasta())
        self.recarregar()
        self.toast("Ficha movida para a lixeira", VERMELHO)

    # ---------------------------------------------------------- robô (thread)

    def cadastrar(self, fichas):
        if not fichas or self.rodando or self.ocupado_ml:
            return
        self.rodando = True
        self.parar_flag.clear()
        self.barra.set(0)
        self.total = len(fichas)
        self.feitas_n = 0
        self.escrever(f"— Cadastrando {len(fichas)} ficha(s) em {self.cfg['robo']['site']}")
        self.paginas["fichas"].rodando(True)
        self.tarefas.put(("cadastrar", fichas))

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
                    self._trabalho(dado)
                elif tipo == "ml_buscar":
                    self._ml_buscar(*dado)
                elif tipo == "ml_mais":
                    self._ml_buscar(*dado, mais=True)
                elif tipo == "ml_importar":
                    self._ml_importar(dado)
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

    def _trabalho(self, fichas):
        avisar = lambda ev, f, t: self.fila.put((ev, f, t))
        try:
            site = self._garantir_site()
            feitas, falhas = motor.rodar(fichas, self.cfg, avisar, self.parar_flag.is_set, site=site)
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

    def _ml_importar(self, link):
        try:
            ml = self._garantir_ml()
            self.fila.put(("ml_status", None, "Lendo o anúncio no Mercado Livre…"))
            info = ml.extrair(link)
            self.fila.put(("ml_status", None, f"Baixando {len(info.get('imagens') or [])} foto(s)…"))
            pasta = motor.ficha_do_ml(info, self.pasta(), baixar=ml.baixar)
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
            if ev == "acabou":
                feitas, falhas = t
                ja = sum(1 for c in self.mapa.values() if c.ficha.status == "ja")
                msg = f"Pronto! {feitas} ficha(s) cadastrada(s)" + (f", {ja} já existia(m) no site" if ja else "") + \
                      (f", {falhas} com falha (veja o motivo em cada uma)" if falhas else "") + "."
                self.barra.set(1)
                self.toast(msg if len(msg) < 70 else "Cadastro terminado", VERDE if not falhas else AMBAR)
            elif ev == "parado":
                msg = "Parado. O que já foi cadastrado ficou no site."
            else:
                msg = f"Parou com erro: {t}"
                self.chip_nav.mudar("Navegador fechado", "#3b2a63")
                messagebox.showerror("Robô Móveis Stylo", msg)
            self.status(msg)
            self.escrever(msg)
            pf.rodando(False)
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
        try:
            self.destroy()
        except Exception:
            pass


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
        Botao(cab.acoes, "＋  Nova ficha", "primario", width=130, command=lambda: app.editar(None)).pack(side="left", padx=4)
        Botao(cab.acoes, "Colar ficha da IA", "suave", width=140, command=lambda: Colar(app)).pack(side="left", padx=4)
        Botao(cab.acoes, "Importar do Mercado Livre", "ml", width=200, command=lambda: app.ir("ml")).pack(side="left", padx=4)
        Botao(cab.acoes, "Pasta", "fantasma", width=70, command=lambda: abrir_no_sistema(app.pasta())).pack(side="left", padx=4)
        Botao(cab.acoes, "⟳", "fantasma", width=40, command=app.recarregar).pack(side="left", padx=(4, 0))

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
        Botao(acoes, "⧉  Duplicar", "suave", width=104, command=lambda: app.duplicar(f), state=trava).pack(side="left", padx=6)
        if f.pronta and f.status not in ("feita", "ja"):
            Botao(acoes, "Cadastrar esta", "fantasma", width=118, state=trava,
                  command=lambda: self.cadastrar_marcadas([self.atual])).pack(side="left", padx=6)
        Botao(acoes, "✕  Excluir", "perigo", width=92, command=lambda: app.excluir(f), state=trava).pack(side="left", padx=6)
        por(acoes, sticky="w", pady=(10, 8))

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
        for a in f.avisos:
            faixa("⚠  " + a, ("#fffbeb", "#33270f"), ("#b45309", "#fcd34d"))
        if f.mensagem and f.status in ("falhou", "feita", "ja"):
            faixa(("✓  " if f.status == "feita" else "•  ") + f.mensagem, ROXO_CLARO, ROXO_TXT)
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

        self.msg = ctk.CTkLabel(self, text="", anchor="w", justify="left", font=F(13), corner_radius=10, height=0)
        self.msg.grid(row=3, column=0, sticky="ew", padx=24, pady=(4, 4))
        self.msg.grid_remove()

        self.grade = ctk.CTkScrollableFrame(self, fg_color="transparent")
        self.grade.grid(row=4, column=0, sticky="nsew", padx=18, pady=(4, 12))
        self.dica = ctk.CTkLabel(self.grade, text="Os resultados aparecem aqui.\n\nNa primeira busca o navegador do robô abre o Mercado Livre.\n"
                                                   "Se o Mercado Livre pedir para confirmar que você não é um robô ou para entrar,\n"
                                                   "resolva na janela do navegador e o robô continua sozinho.",
                                 text_color=MUDO, font=F(13), justify="center")
        self.dica.grid(row=0, column=0, columnspan=4, pady=60, padx=40)

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
            Botao(bts, "Ver", "fantasma", width=54, height=30, font=F(12), command=lambda u=it["link"]: webbrowser.open(u)).pack(side="left")
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
            row=0, column=0, sticky="ew", padx=24, pady=(20, 12))
        card = ctk.CTkFrame(self, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
        card.grid(row=1, column=0, sticky="new", padx=24)
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
        bts = ctk.CTkFrame(card, fg_color="transparent")
        bts.grid(row=10, column=0, columnspan=2, sticky="e", padx=20, pady=20)
        Botao(bts, "Fechar o navegador do robô", "fantasma", width=200, command=lambda: app.tarefas.put(("fechar_site", None))).pack(side="left", padx=6)
        Botao(bts, "Salvar", "primario", width=140, command=self.salvar).pack(side="left")

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
        motor.salvar_config(self.app.cfg)
        if self.app.site and not self.app.rodando:
            self.app.tarefas.put(("fechar_site", None))
        self.app.recarregar()
        self.app.toast("Configurações salvas")


# ------------------------------------------------------------------ editor de ficha

class Secao(ctk.CTkFrame):
    def __init__(self, master, titulo, sub=""):
        super().__init__(master, fg_color=PAINEL, corner_radius=18, border_width=1, border_color=BORDA)
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
    COLS = [("Variação", 150), ("Código", 110), ("Custo R$", 92), ("Venda R$", 92), ("Preço “de” R$", 100), ("Estoque", 74), ("Margem", 66)]

    def __init__(self, master, editor, v):
        super().__init__(master, fg_color=SUAVE, corner_radius=12)
        self.editor = editor
        self.original = v
        emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
        med = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
        l1 = ctk.CTkFrame(self, fg_color="transparent")
        l1.pack(fill="x", padx=8, pady=(8, 2))
        self.valor = Entrada(l1, width=150, placeholder_text="ex.: 30 cm · Preto")
        self.valor.bind("<FocusOut>", lambda e: editor.atualizar_usos(), add="+")
        self.codigo = Entrada(l1, width=110, placeholder_text="automático")
        self.custo = Entrada(l1, "dinheiro", width=92, ao_mudar=self.calc)
        self.venda = Entrada(l1, "dinheiro", width=92, ao_mudar=self.calc)
        self.de = Entrada(l1, "dinheiro", width=100)
        self.estoque = Entrada(l1, "inteiro", width=74, placeholder_text="0")
        self.margem = ctk.CTkLabel(l1, text="—", width=66, font=F(13, "bold"), text_color=MUDO)
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
        self.peso = peq("peso kg", "decimal")
        self.cc = peq("C", "decimal", 50, "cm")
        self.cl = peq("L", "decimal", 50, "cm")
        self.ca = peq("A", "decimal", 50, "cm")
        ctk.CTkLabel(l2, text="Peça:", font=F(11, "bold"), text_color=MUDO).pack(side="left", padx=(14, 0))
        self.pa = peq("alt.", "decimal", 50, "cm")
        self.pl = peq("larg.", "decimal", 50, "cm")
        self.pp = peq("prof.", "decimal", 50, "cm")
        self.pd = peq("Ø", "decimal", 50, "cm")

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
        self.grid_rowconfigure(1, weight=1)

        cab = ctk.CTkFrame(self, fg_color="transparent")
        cab.grid(row=0, column=0, sticky="ew", padx=24, pady=(18, 10))
        cab.grid_columnconfigure(1, weight=1)
        Botao(cab, "◀  Voltar", "fantasma", width=96, command=self.cancelar).grid(row=0, column=0, rowspan=2, padx=(0, 14))
        ctk.CTkLabel(cab, text="Nova ficha" if ficha is None else "Editar ficha", font=F(24, "bold"), text_color=TEXTO, anchor="w").grid(row=0, column=1, sticky="w")
        self.lbl_sub = ctk.CTkLabel(cab, text=ficha.pasta.name if ficha else "A pasta é criada quando você salvar.", font=F(12), text_color=MUDO, anchor="w")
        self.lbl_sub.grid(row=1, column=1, sticky="w")
        self.lbl_erro = ctk.CTkLabel(cab, text="", font=F(12, "bold"), text_color=VERMELHO)
        self.lbl_erro.grid(row=0, column=2, rowspan=2, padx=10)
        Botao(cab, "Cancelar", "fantasma", width=96, command=self.cancelar).grid(row=0, column=3, rowspan=2, padx=4)
        Botao(cab, "Salvar ficha", "primario", width=140, height=40, font=F(14, "bold"), command=self.salvar).grid(row=0, column=4, rowspan=2, padx=(4, 0))

        self.corpo = ctk.CTkScrollableFrame(self, fg_color="transparent")
        self.corpo.grid(row=1, column=0, sticky="nsew", padx=14, pady=(0, 10))
        self.corpo.grid_columnconfigure(0, weight=1)
        self._produto()
        self._precos()
        self._variacoes()
        self._fotos()
        self._tecnica()
        self._mercado_livre()

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

    def _variacoes(self):
        d = self.dados
        s = Secao(self.corpo, "Variações", "Cada variação vira um produto no site, com o próprio código, preço e estoque.")
        s.grid(row=2, column=0, sticky="ew", padx=10, pady=8)
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

    def soma_custo(self):
        return sum((motor.numero(i.get("quantidade")) or 1) * motor.numero(i.get("valor"))
                   for i in self.dados.get("ficha_de_custo") or [] if isinstance(i, dict))

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
        s.grid(row=3, column=0, sticky="ew", padx=10, pady=8)
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
            Botao(bts, "◀", "fantasma", command=lambda f=f: self._mover(f, -1), **pb).pack(side="left", padx=1)
            Botao(bts, "★", "fantasma", command=lambda f=f: self._mover(f, -999), **pb).pack(side="left", padx=1)
            Botao(bts, "▶", "fantasma", command=lambda f=f: self._mover(f, 1), **pb).pack(side="left", padx=1)
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
        s.grid(row=4, column=0, sticky="ew", padx=10, pady=8)
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
        s.grid(row=5, column=0, sticky="ew", padx=10, pady=(8, 20))
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
        self.app.fechar_editor(pasta)

    def cancelar(self):
        if messagebox.askyesno("Sair sem salvar", "Sair do editor sem salvar as mudanças?"):
            self.app.fechar_editor(self.ficha.pasta if self.ficha else None)

    def destroy(self):
        import shutil
        shutil.rmtree(self.tmp, ignore_errors=True)
        super().destroy()


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
