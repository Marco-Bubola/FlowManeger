# -*- coding: utf-8 -*-
"""
Robô de cadastro · Móveis Stylo (janela).

Mostra as fichas da pasta de produtos, o que está pronto e o que tem erro,
as fotos de cada uma, e cadastra no FlowManeger com um clique.
"""
import os
import queue
import subprocess
import sys
import threading
import traceback
import webbrowser
from pathlib import Path
from tkinter import filedialog, messagebox

import customtkinter as ctk

try:
    from PIL import Image, ImageOps
except ImportError:
    Image = None

import motor

ROXO = "#6d28d9"
ROXO_ESCURO = "#5b21b6"
VERDE = "#059669"
VERMELHO = "#e11d48"
AMBAR = "#d97706"
CINZA = "#64748b"

STATUS = {
    "pronta": ("Pronta", VERDE),
    "erro": ("Com erro", VERMELHO),
    "feita": ("Cadastrada", ROXO),
    "ja": ("Já existia", CINZA),
    "falhou": ("Falhou", AMBAR),
    "rodando": ("Cadastrando...", ROXO),
}


def abrir_no_sistema(caminho):
    caminho = str(caminho)
    if sys.platform.startswith("win"):
        os.startfile(caminho)  # noqa
    elif sys.platform == "darwin":
        subprocess.Popen(["open", caminho])
    else:
        subprocess.Popen(["xdg-open", caminho])


def miniatura(caminho, lado):
    if Image is None:
        return None
    try:
        with Image.open(caminho) as im:
            im = ImageOps.exif_transpose(im).convert("RGB")
            im = ImageOps.fit(im, (lado * 2, lado * 2))
            return ctk.CTkImage(light_image=im, dark_image=im, size=(lado, lado))
    except Exception:
        return None


class Chip(ctk.CTkLabel):
    def __init__(self, master, texto, cor, **kw):
        super().__init__(master, text=f"  {texto}  ", fg_color=cor, text_color="white", corner_radius=10,
                         font=ctk.CTkFont(size=11, weight="bold"), height=22, **kw)


class LinhaFicha(ctk.CTkFrame):
    def __init__(self, master, app, ficha):
        super().__init__(master, corner_radius=12, fg_color=("white", "#1f2937"), border_width=2,
                         border_color=("#e2e8f0", "#334155"))
        self.app, self.ficha = app, ficha
        self.var = ctk.BooleanVar(value=ficha.pronta)
        self.grid_columnconfigure(2, weight=1)
        self.check = ctk.CTkCheckBox(self, text="", variable=self.var, width=24, command=app.atualizar_botao,
                                     fg_color=ROXO, hover_color=ROXO_ESCURO)
        self.check.grid(row=0, column=0, rowspan=2, padx=(10, 4), pady=10)
        if not ficha.pronta:
            self.check.configure(state="disabled")
        foto = ficha.fotos[0] if ficha.fotos else (ficha.pasta / ficha.sobrando[0] if ficha.sobrando else None)
        img = miniatura(foto, 44) if foto else None
        self.foto = ctk.CTkLabel(self, text="" if img else "sem\nfoto", image=img, width=44, height=44,
                                 fg_color=("#f1f5f9", "#111827"), corner_radius=10, font=ctk.CTkFont(size=9), text_color=CINZA)
        self.foto.grid(row=0, column=1, rowspan=2, padx=6, pady=8)
        self.titulo = ctk.CTkLabel(self, text=ficha.nome, anchor="w", font=ctk.CTkFont(size=14, weight="bold"))
        self.titulo.grid(row=0, column=2, sticky="ew", padx=6, pady=(8, 0))
        n = len(ficha.variacoes)
        extra = f"{n} {'variação' if n == 1 else 'variações'} · {len(ficha.fotos)} {'foto' if len(ficha.fotos) == 1 else 'fotos'}"
        if ficha.mensagem:
            extra += " · " + ficha.mensagem
        self.sub = ctk.CTkLabel(self, text=extra, anchor="w", text_color=CINZA, font=ctk.CTkFont(size=12))
        self.sub.grid(row=1, column=2, sticky="ew", padx=6, pady=(0, 8))
        texto, cor = STATUS.get(ficha.status, (ficha.status, CINZA))
        self.chip = Chip(self, texto, cor)
        self.chip.grid(row=0, column=3, rowspan=2, padx=10)
        for w in (self, self.titulo, self.sub, self.foto):
            w.bind("<Button-1>", lambda e: app.selecionar(self))

    def marcar(self, ativo):
        self.configure(border_color=(ROXO, "#a78bfa") if ativo else ("#e2e8f0", "#334155"))

    def mudar_status(self, status, mensagem=""):
        texto, cor = STATUS.get(status, (status, CINZA))
        self.chip.configure(text=f"  {texto}  ", fg_color=cor)
        if mensagem:
            base = self.sub.cget("text").split(" · ")[:2]
            self.sub.configure(text=" · ".join(base + [mensagem]))
        if status in ("feita", "ja"):
            self.var.set(False)
            self.check.configure(state="disabled")


class App(ctk.CTk):
    def __init__(self):
        super().__init__()
        ctk.set_appearance_mode("light")
        self.cfg = motor.ler_config()
        self.title("Robô de cadastro · Móveis Stylo")
        sw, sh = self.winfo_screenwidth(), self.winfo_screenheight()
        larg, alt = min(1180, sw - 40), min(760, sh - 90)
        self.geometry(f"{larg}x{alt}+{max((sw - larg) // 2, 0)}+{max((sh - alt) // 2 - 20, 0)}")
        self.minsize(940, 620)
        self.configure(fg_color=("#f5f3ff", "#0f172a"))
        icone = Path(__file__).with_name("icone.ico")
        if icone.exists() and sys.platform.startswith("win"):
            try:
                self.iconbitmap(str(icone))
            except Exception:
                pass

        self.fila = queue.Queue()      # robô → janela
        self.tarefas = queue.Queue()   # janela → robô (o navegador vive numa thread só)
        self.linhas = []
        self.atual = None
        self.rodando = False
        self.parar_flag = threading.Event()
        self.site = None
        self._fotos_ref = []

        self.grid_columnconfigure(0, weight=5)
        self.grid_columnconfigure(1, weight=6)
        self.grid_rowconfigure(1, weight=1)
        self._topo()
        self._lista()
        self._detalhe()
        self._rodape()
        self.recarregar()
        self.after(120, self._ler_fila)
        threading.Thread(target=self._trabalhador, daemon=True).start()
        self.protocol("WM_DELETE_WINDOW", self.fechar)

    # ---------------------------------------------------------------- montagem

    def _topo(self):
        topo = ctk.CTkFrame(self, corner_radius=0, fg_color=(ROXO, "#312e81"), height=74)
        topo.grid(row=0, column=0, columnspan=2, sticky="ew")
        topo.grid_columnconfigure(1, weight=1)
        ctk.CTkLabel(topo, text="MS", width=46, height=46, corner_radius=14, fg_color="white", text_color=ROXO,
                     font=ctk.CTkFont(size=17, weight="bold")).grid(row=0, column=0, rowspan=2, padx=(20, 12), pady=12)
        ctk.CTkLabel(topo, text="Robô de cadastro · Móveis Stylo", text_color="white",
                     font=ctk.CTkFont(size=20, weight="bold"), anchor="w").grid(row=0, column=1, sticky="w", pady=(12, 0))
        self.lbl_site = ctk.CTkLabel(topo, text="", text_color="#ddd6fe", font=ctk.CTkFont(size=12), anchor="w")
        self.lbl_site.grid(row=1, column=1, sticky="w", pady=(0, 12))
        self.lbl_nav = Chip(topo, "Navegador fechado", "#7c3aed")
        self.lbl_nav.grid(row=0, column=2, rowspan=2, padx=8)
        bt = dict(height=36, corner_radius=10, fg_color="white", text_color=ROXO, hover_color="#ede9fe",
                  font=ctk.CTkFont(size=13, weight="bold"))
        ctk.CTkButton(topo, text="📋  Questionário", width=140, command=self.questionario, **bt).grid(row=0, column=3, rowspan=2, padx=4)
        ctk.CTkButton(topo, text="⚙  Configurações", width=140, command=self.configuracoes, **bt).grid(row=0, column=4, rowspan=2, padx=(4, 20))

    def _lista(self):
        caixa = ctk.CTkFrame(self, corner_radius=16, fg_color=("white", "#111827"))
        caixa.grid(row=1, column=0, sticky="nsew", padx=(16, 8), pady=16)
        caixa.grid_rowconfigure(2, weight=1)
        caixa.grid_columnconfigure(0, weight=1)
        cab = ctk.CTkFrame(caixa, fg_color="transparent")
        cab.grid(row=0, column=0, sticky="ew", padx=14, pady=(14, 4))
        cab.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(cab, text="Fichas", font=ctk.CTkFont(size=17, weight="bold"), anchor="w").grid(row=0, column=0, sticky="w")
        self.lbl_contagem = ctk.CTkLabel(cab, text="", text_color=CINZA, anchor="w")
        self.lbl_contagem.grid(row=1, column=0, sticky="w")
        b = dict(height=32, corner_radius=9, fg_color=("#ede9fe", "#312e81"), text_color=(ROXO, "white"),
                 hover_color=("#ddd6fe", "#3730a3"), font=ctk.CTkFont(size=12, weight="bold"))
        ctk.CTkButton(cab, text="＋ Colar ficha da IA", width=150, command=self.colar_ficha, **b).grid(row=0, column=1, rowspan=2, padx=3)
        ctk.CTkButton(cab, text="📂 Pasta", width=84, command=lambda: abrir_no_sistema(self.pasta()), **b).grid(row=0, column=2, rowspan=2, padx=3)
        ctk.CTkButton(cab, text="⟳", width=38, command=self.recarregar, **b).grid(row=0, column=3, rowspan=2, padx=3)
        sel = ctk.CTkFrame(caixa, fg_color="transparent")
        sel.grid(row=1, column=0, sticky="ew", padx=14)
        ctk.CTkButton(sel, text="Marcar prontas", width=110, height=26, fg_color="transparent", text_color=ROXO, hover_color="#f5f3ff",
                      command=lambda: self.marcar_todas(True)).pack(side="left")
        ctk.CTkButton(sel, text="Desmarcar", width=90, height=26, fg_color="transparent", text_color=CINZA, hover_color="#f1f5f9",
                      command=lambda: self.marcar_todas(False)).pack(side="left")
        self.lista = ctk.CTkScrollableFrame(caixa, fg_color="transparent")
        self.lista.grid(row=2, column=0, sticky="nsew", padx=8, pady=(4, 10))
        self.lista.grid_columnconfigure(0, weight=1)

    def _detalhe(self):
        self.det = ctk.CTkScrollableFrame(self, corner_radius=16, fg_color=("white", "#111827"))
        self.det.grid(row=1, column=1, sticky="nsew", padx=(8, 16), pady=16)
        self.det.grid_columnconfigure(0, weight=1)

    def _rodape(self):
        rod = ctk.CTkFrame(self, corner_radius=0, fg_color=("white", "#111827"))
        rod.grid(row=2, column=0, columnspan=2, sticky="ew")
        rod.grid_columnconfigure(0, weight=1)
        self.lbl_status = ctk.CTkLabel(rod, text="Pronto.", anchor="w", font=ctk.CTkFont(size=13))
        self.lbl_status.grid(row=0, column=0, sticky="ew", padx=20, pady=(12, 2))
        self.barra = ctk.CTkProgressBar(rod, progress_color=ROXO, height=8)
        self.barra.grid(row=1, column=0, sticky="ew", padx=20, pady=(0, 6))
        self.barra.set(0)
        self.log = ctk.CTkTextbox(rod, height=74, font=ctk.CTkFont(family="Consolas", size=11), fg_color=("#f8fafc", "#0b1220"))
        self.log.grid(row=2, column=0, sticky="ew", padx=20, pady=(0, 12))
        self.log.configure(state="disabled")
        bot = ctk.CTkFrame(rod, fg_color="transparent")
        bot.grid(row=0, column=1, rowspan=3, padx=20, pady=12, sticky="e")
        self.bt_relatorio = ctk.CTkButton(bot, text="📄 Relatório", width=120, height=44, corner_radius=12,
                                          fg_color=("#f1f5f9", "#1f2937"), text_color=("#334155", "white"), hover_color=("#e2e8f0", "#334155"),
                                          command=self.abrir_relatorio)
        self.bt_relatorio.pack(side="left", padx=6)
        self.bt_parar = ctk.CTkButton(bot, text="■ Parar", width=100, height=44, corner_radius=12, fg_color=VERMELHO,
                                      hover_color="#be123c", command=self.parar, state="disabled")
        self.bt_parar.pack(side="left", padx=6)
        self.bt_cadastrar = ctk.CTkButton(bot, text="Cadastrar", width=230, height=44, corner_radius=12, fg_color=ROXO,
                                          hover_color=ROXO_ESCURO, font=ctk.CTkFont(size=15, weight="bold"), command=self.cadastrar)
        self.bt_cadastrar.pack(side="left", padx=6)

    # ---------------------------------------------------------------- lista

    def pasta(self):
        p = Path(self.cfg["robo"]["pasta_produtos"])
        p.mkdir(parents=True, exist_ok=True)
        return p

    def recarregar(self):
        if self.rodando:
            return
        self.lbl_site.configure(text=f"{self.cfg['robo']['site']}   ·   pasta: {self.pasta()}")
        for l in self.linhas:
            l.destroy()
        self.linhas = []
        fichas = motor.listar_fichas(self.pasta())
        for i, f in enumerate(fichas):
            l = LinhaFicha(self.lista, self, f)
            l.grid(row=i, column=0, sticky="ew", padx=4, pady=4)
            self.linhas.append(l)
        prontas = sum(f.pronta for f in fichas)
        erros = sum(f.status == "erro" for f in fichas)
        self.lbl_contagem.configure(text=f"{len(fichas)} na pasta · {prontas} prontas" + (f" · {erros} com erro" if erros else ""))
        if not fichas:
            vazio = ctk.CTkLabel(self.lista, text="Nenhuma ficha ainda.\n\nUse “＋ Colar ficha da IA” ou crie uma pasta por produto\n"
                                                 "com o ficha.md e as fotos dentro.", text_color=CINZA, justify="center")
            vazio.grid(row=0, column=0, pady=40)
            self.linhas.append(vazio)
        self.atualizar_botao()
        if fichas:
            self.selecionar(self.linhas[0])
        else:
            self.mostrar_vazio()

    def marcar_todas(self, valor):
        for l in self.linhas:
            if isinstance(l, LinhaFicha) and l.ficha.pronta and l.ficha.status not in ("feita", "ja"):
                l.var.set(valor)
        self.atualizar_botao()

    def marcadas(self):
        return [l for l in self.linhas if isinstance(l, LinhaFicha) and l.var.get() and l.ficha.pronta]

    def atualizar_botao(self):
        n = len(self.marcadas())
        self.bt_cadastrar.configure(text=f"Cadastrar {n} {'produto' if n == 1 else 'produtos'}" if n else "Cadastrar",
                                    state="normal" if n and not self.rodando else "disabled")

    # ---------------------------------------------------------------- detalhe

    def limpar_detalhe(self):
        for w in self.det.winfo_children():
            w.destroy()
        self._fotos_ref = []

    def mostrar_vazio(self):
        self.limpar_detalhe()
        ctk.CTkLabel(self.det, text="Como usar", font=ctk.CTkFont(size=18, weight="bold"), anchor="w").grid(sticky="w", padx=16, pady=(16, 6))
        passos = [
            "1. Toque em 📋 Questionário e cole numa IA (ChatGPT, Gemini…) junto com as fotos da peça.",
            "2. Responda o que a IA perguntar. No fim ela devolve a ficha com o bloco JSON.",
            "3. Toque em ＋ Colar ficha da IA, cole a resposta e escolha as fotos.",
            "4. Confira aqui se está tudo verde e toque em Cadastrar.",
            "5. Na primeira vez, entre na conta da Móveis Stylo na janela do navegador que abrir.",
            "6. Depois ligue o produto ao Mercado Livre no site, como na Boticário.",
        ]
        for p in passos:
            ctk.CTkLabel(self.det, text=p, anchor="w", justify="left", wraplength=420).grid(sticky="w", padx=16, pady=3)

    def selecionar(self, linha):
        if not isinstance(linha, LinhaFicha):
            return
        for l in self.linhas:
            if isinstance(l, LinhaFicha):
                l.marcar(l is linha)
        self.atual = linha
        self.mostrar_ficha(linha.ficha)

    def mostrar_ficha(self, f):
        self.limpar_detalhe()
        d = f.dados
        r = 0
        self.update_idletasks()
        try:
            escala = ctk.ScalingTracker.get_widget_scaling(self)
        except Exception:
            escala = 1
        larg = self.det.winfo_width() / (escala or 1)
        wrap = int(max(larg - 80, 300)) if larg > 200 else 420

        def linha(widget, **kw):
            nonlocal r
            widget.grid(row=r, column=0, sticky=kw.pop("sticky", "ew"), padx=kw.pop("padx", 16), pady=kw.pop("pady", 3), **kw)
            r += 1

        texto, cor = STATUS.get(f.status, (f.status, CINZA))
        topo = ctk.CTkFrame(self.det, fg_color="transparent")
        topo.grid_columnconfigure(0, weight=1)
        ctk.CTkLabel(topo, text=f.nome, font=ctk.CTkFont(size=19, weight="bold"), anchor="w", wraplength=wrap - 90, justify="left").grid(row=0, column=0, sticky="w")
        Chip(topo, texto, cor).grid(row=0, column=1, padx=4)
        linha(topo, pady=(16, 2))
        info = "  ·  ".join(x for x in [str(d.get("categoria") or "Geral"), str(d.get("marca") or ""),
                                      ("Sob encomenda" + (f" · {int(motor.numero(d.get('prazo_dias')))} dias" if d.get("prazo_dias") else "")) if motor.sim(d.get("sob_encomenda")) else "Pronta entrega",
                                      f"variação por {str(d.get('variacao_por')).lower()}" if d.get("variacao_por") else ""] if x)
        linha(ctk.CTkLabel(self.det, text=info, text_color=CINZA, anchor="w", justify="left", wraplength=wrap))
        linha(ctk.CTkLabel(self.det, text=f"Pasta: {f.pasta.name}", text_color=CINZA, anchor="w", font=ctk.CTkFont(size=11)))

        for e in f.erros:
            linha(self._aviso("✖  " + e, "#fff1f2", VERMELHO, wrap))
        for a in f.avisos:
            linha(self._aviso("⚠  " + a, "#fffbeb", "#b45309", wrap))
        if f.mensagem and f.status in ("falhou", "feita", "ja"):
            cores = {"falhou": ("#fffbeb", "#b45309"), "feita": ("#f5f3ff", ROXO), "ja": ("#f1f5f9", CINZA)}[f.status]
            linha(self._aviso(("✔  " if f.status == "feita" else "•  ") + f.mensagem, *cores, wrap))
        for res in f.resultado:
            rot = f"↗  {res['codigo']}  ·  {res['nome']}"
            maxc = max(wrap // 8, 30)
            bt = ctk.CTkButton(self.det, text=rot if len(rot) <= maxc else rot[:maxc - 1] + "…", anchor="w", height=30,
                               fg_color=("#f5f3ff", "#1e1b4b"), text_color=(ROXO, "#c4b5fd"), hover_color=("#ede9fe", "#312e81"),
                               command=lambda u=res["link"]: webbrowser.open(u))
            linha(bt)

        fotos = f.fotos or [f.pasta / n for n in f.sobrando]
        if fotos:
            linha(ctk.CTkLabel(self.det, text="Fotos", font=ctk.CTkFont(size=14, weight="bold"), anchor="w"), pady=(12, 2))
            grade = ctk.CTkFrame(self.det, fg_color="transparent")
            for i, p in enumerate(fotos[:12]):
                img = miniatura(p, 84)
                self._fotos_ref.append(img)
                cel = ctk.CTkFrame(grade, fg_color=("#f8fafc", "#1f2937"), corner_radius=10)
                cel.grid(row=i // 5, column=i % 5, padx=4, pady=4)
                ctk.CTkLabel(cel, text="" if img else "sem prévia", image=img, width=84, height=84).pack(padx=4, pady=(4, 0))
                ctk.CTkLabel(cel, text=p.name[:14], font=ctk.CTkFont(size=10), text_color=CINZA).pack(pady=(0, 4))
                if i == 0:
                    Chip(cel, "principal", ROXO).place(x=6, y=6)
            linha(grade, sticky="w")

        vars_ = f.variacoes
        if vars_:
            linha(ctk.CTkLabel(self.det, text="Variações", font=ctk.CTkFont(size=14, weight="bold"), anchor="w"), pady=(12, 2))
            tab = ctk.CTkFrame(self.det, fg_color=("#f8fafc", "#1f2937"), corner_radius=12)
            cab = ["Variação", "Código", "Custo", "Venda", "Estoque", "Fotos"]
            for c, t in enumerate(cab):
                ctk.CTkLabel(tab, text=t, font=ctk.CTkFont(size=11, weight="bold"), text_color=CINZA).grid(row=0, column=c, padx=8, pady=(8, 2), sticky="w")
            for i, v in enumerate(vars_, 1):
                vals = [str(v.get("valor") or "—"), str(v.get("codigo") or "automático"),
                        f"R$ {motor.numero(v.get('custo')):.2f}".replace(".", ",") if v.get("custo") not in (None, "") else "ficha",
                        f"R$ {motor.numero(v.get('venda')):.2f}".replace(".", ","),
                        str(int(motor.numero(v.get("estoque")))), str(len(v.get("fotos") or []))]
                for c, t in enumerate(vals):
                    ctk.CTkLabel(tab, text=t, font=ctk.CTkFont(size=12, weight="bold" if c == 3 else "normal"),
                                 text_color=VERDE if c == 3 else None).grid(row=i, column=c, padx=8, pady=2, sticky="w")
            linha(tab, pady=(2, 8))

        desc = str(d.get("descricao") or "").strip()
        if desc:
            linha(ctk.CTkLabel(self.det, text="Descrição", font=ctk.CTkFont(size=14, weight="bold"), anchor="w"), pady=(8, 2))
            linha(ctk.CTkLabel(self.det, text=desc[:700] + ("…" if len(desc) > 700 else ""), anchor="w", justify="left", wraplength=wrap))
        ml = d.get("mercado_livre") or {}
        if isinstance(ml, dict) and (ml.get("titulo_anuncio") or ml.get("link_referencia")):
            linha(ctk.CTkLabel(self.det, text="Mercado Livre", font=ctk.CTkFont(size=14, weight="bold"), anchor="w"), pady=(12, 2))
            if ml.get("titulo_anuncio"):
                linha(ctk.CTkLabel(self.det, text=f"Título: {ml['titulo_anuncio']}", anchor="w", wraplength=wrap, justify="left"))
            if ml.get("link_referencia"):
                linha(ctk.CTkButton(self.det, text="Abrir anúncio de referência", height=28, fg_color="#ffe600", text_color="#1e293b",
                                    hover_color="#facc15", command=lambda: webbrowser.open(ml["link_referencia"])), sticky="w")

        acoes = ctk.CTkFrame(self.det, fg_color="transparent")
        b = dict(height=30, corner_radius=9, fg_color=("#f1f5f9", "#1f2937"), text_color=("#334155", "white"), hover_color=("#e2e8f0", "#334155"))
        ctk.CTkButton(acoes, text="📂 Abrir pasta", width=120, command=lambda: abrir_no_sistema(f.pasta), **b).pack(side="left", padx=(0, 6))
        if f.arquivo:
            ctk.CTkButton(acoes, text="📝 Abrir ficha", width=120, command=lambda: abrir_no_sistema(f.arquivo), **b).pack(side="left", padx=6)
        linha(acoes, sticky="w", pady=(14, 16))

    def _aviso(self, texto, fundo, cor, wrap=440):
        return ctk.CTkLabel(self.det, text=texto, anchor="w", justify="left", wraplength=wrap - 30, fg_color=fundo,
                            text_color=cor, corner_radius=10, height=30, padx=10)

    # ---------------------------------------------------------------- ações

    def escrever(self, texto):
        self.log.configure(state="normal")
        self.log.insert("end", texto + "\n")
        self.log.see("end")
        self.log.configure(state="disabled")

    def cadastrar(self):
        linhas = self.marcadas()
        if not linhas or self.rodando:
            return
        self.rodando = True
        self.parar_flag.clear()
        self.bt_cadastrar.configure(state="disabled", text="Cadastrando...")
        self.bt_parar.configure(state="normal")
        self.barra.set(0)
        self.total = len(linhas)
        self.feitas_n = 0
        self.mapa = {id(l.ficha): l for l in linhas}
        for l in linhas:
            l.check.configure(state="disabled")
        self.escrever(f"— Cadastrando {len(linhas)} ficha(s) em {self.cfg['robo']['site']}")
        self.tarefas.put(("cadastrar", [l.ficha for l in linhas]))

    def _trabalhador(self):
        """Thread do robô: o Playwright só funciona na thread que abriu o navegador."""
        while True:
            tipo, dado = self.tarefas.get()
            if tipo == "cadastrar":
                self._trabalho(dado)
            elif tipo == "fechar_site":
                if self.site:
                    self.site.fechar()
                    self.site = None
                self.fila.put(("site", None, "fechado"))
            elif tipo == "sair":
                if self.site:
                    self.site.fechar()
                    self.site = None
                self.fila.put(("saiu", None, None))
                return

    def _trabalho(self, fichas):
        avisar = lambda ev, f, t: self.fila.put((ev, f, t))
        try:
            if self.site is None:
                r = self.cfg["robo"]
                self.site = motor.Site(r["site"], visivel=motor.sim(r["navegador_visivel"]),
                                       avisar=lambda t: self.fila.put(("site", None, t)), parar=self.parar_flag.is_set)
            else:
                self.site.parar = self.parar_flag.is_set
            feitas, falhas = motor.rodar(fichas, self.cfg, avisar, self.parar_flag.is_set, site=self.site)
            self.fila.put(("acabou", None, (feitas, falhas)))
        except motor.Parar:
            self.fila.put(("parado", None, None))
        except Exception as e:
            self.fila.put(("erro", None, str(e).splitlines()[0] if str(e) else repr(e)))
            with open(motor.BASE / "erros.log", "a", encoding="utf-8") as fl:
                fl.write(traceback.format_exc() + "\n")
            # Navegador pode ter sido fechado: abre um novo na próxima vez
            try:
                self.site.fechar()
            except Exception:
                pass
            self.site = None

    def _ler_fila(self):
        try:
            while True:
                ev, f, t = self.fila.get_nowait()
                self._evento(ev, f, t)
        except queue.Empty:
            pass
        self.after(120, self._ler_fila)

    def _evento(self, ev, f, t):
        if ev == "site":
            if t == "login":
                self.lbl_nav.configure(text="  Entre na conta no navegador  ", fg_color=AMBAR)
                self.lbl_status.configure(text="Entre na conta da Móveis Stylo na janela do navegador que abriu. O robô continua sozinho depois.")
                self.escrever("Esperando o login no navegador...")
            elif t == "conectado":
                self.lbl_nav.configure(text="  Conectado ao site  ", fg_color=VERDE)
            elif t == "fechado":
                self.lbl_nav.configure(text="  Navegador fechado  ", fg_color="#7c3aed")
            else:
                self.lbl_status.configure(text=self.lbl_status.cget("text").split("  ·  ")[0] + f"  ·  {t}")
        elif ev == "inicio":
            l = self.mapa.get(id(f))
            if l:
                l.mudar_status("rodando")
                self.selecionar(l)
            self.lbl_status.configure(text=t)
            self.escrever(t)
        elif ev == "fim":
            self.feitas_n += 1
            self.barra.set(self.feitas_n / max(self.total, 1))
            l = self.mapa.get(id(f))
            if l:
                l.mudar_status(f.status, f.mensagem)
                if self.atual is l:
                    self.mostrar_ficha(f)
            icone = {"feita": "✔", "ja": "•", "falhou": "✖"}.get(f.status, "•")
            self.escrever(f"{icone} {f.nome}: {t}")
        elif ev == "saiu":
            self._sair()
        elif ev in ("acabou", "parado", "erro"):
            self.rodando = False
            self.bt_parar.configure(state="disabled")
            if ev == "acabou":
                feitas, falhas = t
                msg = f"Pronto! {feitas} ficha(s) cadastrada(s)" + (f", {falhas} com falha (veja o motivo em cada uma)" if falhas else "") + "."
                self.barra.set(1)
            elif ev == "parado":
                msg = "Parado. O que já foi cadastrado ficou no site."
            else:
                msg = f"Parou com erro: {t}"
                self.lbl_nav.configure(text="  Navegador fechado  ", fg_color="#7c3aed")
                messagebox.showerror("Robô Móveis Stylo", msg)
            self.lbl_status.configure(text=msg)
            self.escrever(msg)
            for l in self.linhas:
                if isinstance(l, LinhaFicha) and l.ficha.pronta and l.ficha.status not in ("feita", "ja"):
                    l.check.configure(state="normal")
            self.atualizar_botao()

    def parar(self):
        if self.rodando:
            self.parar_flag.set()
            self.bt_parar.configure(state="disabled")
            self.lbl_status.configure(text="Parando depois do produto atual...")

    def abrir_relatorio(self):
        arq = motor.relatorio_path()
        if arq.exists():
            abrir_no_sistema(arq)
        else:
            abrir_no_sistema(arq.parent)

    def questionario(self):
        arq = Path(__file__).with_name("questionario-produto-stylo.md")
        if not arq.exists():
            messagebox.showwarning("Questionário", "Não achei o arquivo questionario-produto-stylo.md na pasta do robô.")
            return
        texto = arq.read_text(encoding="utf-8")
        self.clipboard_clear()
        self.clipboard_append(texto)
        if messagebox.askyesno("Questionário copiado",
                               "O questionário foi copiado. Cole numa IA (Ctrl+V) junto com as fotos da peça.\n\nQuer abrir o arquivo também?"):
            abrir_no_sistema(arq)

    def colar_ficha(self):
        Colar(self)

    def configuracoes(self):
        Config(self)

    def fechar(self):
        if self.rodando and not messagebox.askyesno("Fechar", "O robô está cadastrando. Fechar mesmo assim?"):
            return
        self.parar_flag.set()
        self.withdraw()
        self.tarefas.put(("sair", None))
        self.after(8000, self._sair)  # se o navegador demorar para fechar

    def _sair(self):
        try:
            self.destroy()
        except Exception:
            pass


class Colar(ctk.CTkToplevel):
    """Cola a resposta da IA, escolhe as fotos e cria a pasta do produto."""

    def __init__(self, app):
        super().__init__(app)
        self.app = app
        self.fotos = []
        self.title("Colar ficha da IA")
        self.configure(fg_color=("#faf5ff", "#111827"))
        self.geometry(f"720x620+{app.winfo_rootx() + 60}+{app.winfo_rooty() + 40}")
        self.transient(app)
        self.grab_set()
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=1)
        ctk.CTkLabel(self, text="Cole aqui a resposta da IA (com o bloco ```json```)", font=ctk.CTkFont(size=15, weight="bold"),
                     anchor="w").grid(row=0, column=0, sticky="ew", padx=16, pady=(16, 6))
        self.texto = ctk.CTkTextbox(self, font=ctk.CTkFont(family="Consolas", size=12))
        self.texto.grid(row=1, column=0, sticky="nsew", padx=16)
        try:
            clip = app.clipboard_get()
            if "{" in clip and "nome" in clip:
                self.texto.insert("1.0", clip)
        except Exception:
            pass
        linha = ctk.CTkFrame(self, fg_color="transparent")
        linha.grid(row=2, column=0, sticky="ew", padx=16, pady=10)
        linha.grid_columnconfigure(1, weight=1)
        ctk.CTkButton(linha, text="🖼  Escolher fotos", width=150, fg_color="#ede9fe", text_color=ROXO, hover_color="#ddd6fe",
                      command=self.escolher).grid(row=0, column=0)
        self.lbl = ctk.CTkLabel(linha, text="Nenhuma foto escolhida", text_color=CINZA, anchor="w")
        self.lbl.grid(row=0, column=1, sticky="ew", padx=10)
        self.msg = ctk.CTkLabel(self, text="", text_color=VERMELHO, anchor="w", wraplength=680, justify="left")
        self.msg.grid(row=3, column=0, sticky="ew", padx=16)
        bts = ctk.CTkFrame(self, fg_color="transparent")
        bts.grid(row=4, column=0, sticky="e", padx=16, pady=(6, 16))
        ctk.CTkButton(bts, text="Cancelar", width=110, fg_color="#f1f5f9", text_color="#334155", hover_color="#e2e8f0",
                      command=self.destroy).pack(side="left", padx=6)
        ctk.CTkButton(bts, text="Criar ficha", width=150, fg_color=ROXO, hover_color=ROXO_ESCURO,
                      font=ctk.CTkFont(weight="bold"), command=self.criar).pack(side="left")

    def escolher(self):
        arqs = filedialog.askopenfilenames(parent=self, title="Fotos do produto",
                                           filetypes=[("Fotos", "*.jpg *.jpeg *.png *.webp *.bmp *.gif *.jfif"), ("Todos", "*.*")])
        if arqs:
            self.fotos = list(arqs)
            nomes = ", ".join(Path(a).name for a in self.fotos[:4]) + (" …" if len(self.fotos) > 4 else "")
            self.lbl.configure(text=f"{len(self.fotos)} foto(s): {nomes}")

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
                                "\n\nVocê pode copiar essas fotos para a pasta depois.", parent=self)
        self.destroy()
        self.app.recarregar()
        for l in self.app.linhas:
            if isinstance(l, LinhaFicha) and l.ficha.pasta == pasta:
                self.app.selecionar(l)


class Config(ctk.CTkToplevel):
    def __init__(self, app):
        super().__init__(app)
        self.app = app
        r = app.cfg["robo"]
        self.title("Configurações")
        self.configure(fg_color=("#faf5ff", "#111827"))
        self.geometry(f"640x420+{app.winfo_rootx() + 120}+{app.winfo_rooty() + 80}")
        self.transient(app)
        self.grab_set()
        self.grid_columnconfigure(1, weight=1)
        self.campos = {}

        def campo(row, rotulo, chave, ajuda=""):
            ctk.CTkLabel(self, text=rotulo, anchor="w", font=ctk.CTkFont(weight="bold")).grid(row=row, column=0, sticky="w", padx=16, pady=(12, 0))
            e = ctk.CTkEntry(self)
            e.insert(0, r.get(chave, ""))
            e.grid(row=row, column=1, sticky="ew", padx=16, pady=(12, 0))
            self.campos[chave] = e
            if ajuda:
                ctk.CTkLabel(self, text=ajuda, text_color=CINZA, anchor="w", font=ctk.CTkFont(size=11)).grid(row=row + 1, column=1, sticky="w", padx=16)

        campo(0, "Site", "site", "Endereço do FlowManeger (ex.: https://flowmaneger.com)")
        campo(2, "Pasta dos produtos", "pasta_produtos", "Uma pasta por produto, com ficha.md e as fotos")
        ctk.CTkButton(self, text="Escolher...", width=100, fg_color="#ede9fe", text_color=ROXO, hover_color="#ddd6fe",
                      command=self.escolher_pasta).grid(row=3, column=1, sticky="e", padx=16)
        campo(4, "Lado máximo da foto (px)", "lado_maximo_foto", "Fotos maiores são reduzidas antes de subir")
        self.mover = ctk.CTkSwitch(self, text="Mover as fichas cadastradas para a pasta “feitos”", progress_color=ROXO)
        self.mover.grid(row=6, column=0, columnspan=2, sticky="w", padx=16, pady=(16, 0))
        (self.mover.select if motor.sim(r.get("mover_feitos")) else self.mover.deselect)()
        self.visivel = ctk.CTkSwitch(self, text="Mostrar o navegador enquanto cadastra", progress_color=ROXO)
        self.visivel.grid(row=7, column=0, columnspan=2, sticky="w", padx=16, pady=(10, 0))
        (self.visivel.select if motor.sim(r.get("navegador_visivel")) else self.visivel.deselect)()
        ctk.CTkButton(self, text="Salvar", width=140, fg_color=ROXO, hover_color=ROXO_ESCURO, command=self.salvar).grid(
            row=8, column=1, sticky="e", padx=16, pady=20)

    def escolher_pasta(self):
        p = filedialog.askdirectory(parent=self, title="Pasta dos produtos")
        if p:
            self.campos["pasta_produtos"].delete(0, "end")
            self.campos["pasta_produtos"].insert(0, p)

    def salvar(self):
        r = self.app.cfg["robo"]
        site = self.campos["site"].get().strip().rstrip("/")
        if site and not site.startswith("http"):
            site = "https://" + site
        r["site"] = site or motor.PADRAO["site"]
        r["pasta_produtos"] = self.campos["pasta_produtos"].get().strip() or motor.PADRAO["pasta_produtos"]
        lado = self.campos["lado_maximo_foto"].get().strip()
        r["lado_maximo_foto"] = lado if lado.isdigit() else "1600"
        r["mover_feitos"] = "sim" if self.mover.get() else "nao"
        r["navegador_visivel"] = "sim" if self.visivel.get() else "nao"
        motor.salvar_config(self.app.cfg)
        if self.app.site and not self.app.rodando:
            self.app.tarefas.put(("fechar_site", None))
        self.destroy()
        self.app.recarregar()


if __name__ == "__main__":
    os.chdir(motor.BASE)
    App().mainloop()
