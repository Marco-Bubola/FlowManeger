# -*- coding: utf-8 -*-
"""
Janela do Robô Boticário.

Abre uma janela própria (Chrome ou Edge em modo aplicativo, sem barra de endereço) com a tela do robô.
Não abre terminal: o atalho chama o pythonw. O robô em si continua no robo.py.
"""
import base64
import json
import os
import re
import shutil
import socket
import subprocess
import sys
import tempfile
import threading
import time
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlparse

BASE = Path(__file__).resolve().parent
UI = BASE / "ui"
os.chdir(BASE)
sys.path.insert(0, str(BASE))

# Antes de tudo: versão nova no GitHub? Instala e abre de novo já com ela.
if "--reaberto" not in sys.argv and os.environ.get("ROBO_SEM_ATUALIZAR") != "1":
    try:
        import atualizar  # noqa: E402
        if atualizar.ao_abrir():
            subprocess.Popen([sys.executable, str(Path(__file__).resolve()), "--reaberto",
                              *[a for a in sys.argv[1:] if a != "--reaberto"]], cwd=str(BASE))
            os._exit(0)
    except Exception:
        pass

import robo  # noqa: E402
import arrumar  # noqa: E402

ARRUMAR = arrumar.Trabalho()


def _versao():
    try:
        return int(json.loads((BASE / "versao.json").read_text(encoding="utf-8")).get("versao") or 2)
    except Exception:
        return 2


VERSAO = _versao()
CAMPOS = {  # chave do config.ini -> (seção, tipo)
    "site_app": ("robo", "texto"),
    "site_revendedor": ("robo", "texto"),
    "pagina_busca": ("robo", "texto"),
    "pasta_imagens": ("robo", "texto"),
    "usar_site_revendedor": ("robo", "sim"),
    "usar_lojas_publicas": ("robo", "sim"),
    "modo_assistido": ("robo", "sim"),
    "navegador": ("robo", "texto"),
    "pular_produtos": ("robo", "texto"),
    "remover_amostras": ("robo", "sim"),
    "preencher_codigo_barras": ("robo", "sim"),
    "similaridade_minima": ("robo", "numero"),
    "pausa_entre_buscas": ("robo", "numero"),
    "tema": ("janela", "texto"),
}
PADRAO = {
    "site_app": "https://flowmaneger.com",
    "site_revendedor": "https://revendedores.grupoboticario.com.br",
    "pagina_busca": "https://revendedores.grupoboticario.com.br/departamentos",
    "pasta_imagens": r"E:\eudora\Produtos Eudora",
    "usar_site_revendedor": "sim",
    "usar_lojas_publicas": "sim",
    "modo_assistido": "sim",
    "navegador": "chrome",
    "pular_produtos": robo.PULAR_PADRAO,
    "remover_amostras": "sim",
    "preencher_codigo_barras": "sim",
    "similaridade_minima": "90",
    "pausa_entre_buscas": "3",
    "tema": "aurora",
}


# ------------------------------------------------------------- config.ini

def ler_valores():
    cfg = robo.ler_config()
    out = {}
    for k, (sec, _tipo) in CAMPOS.items():
        out[k] = cfg.get(sec, k, fallback=PADRAO[k]) if cfg.has_section(sec) else PADRAO[k]
    return out


def gravar_valores(novos):
    """Troca só as linhas mudadas do config.ini (mantém os comentários)."""
    arq = BASE / "config.ini"
    linhas = arq.read_text(encoding="utf-8-sig").splitlines() if arq.exists() else []
    for chave, valor in novos.items():
        if chave not in CAMPOS:
            continue
        sec, tipo = CAMPOS[chave]
        if tipo == "sim":
            valor = "sim" if valor in (True, "sim", "true", "1", 1) else "nao"
        valor = str(valor).replace("\n", " ").strip()
        atual, feito, fim_secao = None, False, None
        for i, l in enumerate(linhas):
            m = re.match(r"\s*\[(.+?)\]", l)
            if m:
                if atual == sec and fim_secao is None:
                    fim_secao = i
                atual = m.group(1).strip()
                continue
            if atual == sec and re.match(rf"\s*{re.escape(chave)}\s*=", l):
                linhas[i] = f"{chave} = {valor}"
                feito = True
                break
        if not feito:
            if not any(re.match(rf"\s*\[{re.escape(sec)}\]", l) for l in linhas):
                linhas += ["", f"[{sec}]"]
                fim_secao = None
            if fim_secao is None:
                # fim da seção = antes da próxima seção depois dela, ou fim do arquivo
                dentro, fim_secao = False, len(linhas)
                for i, l in enumerate(linhas):
                    m = re.match(r"\s*\[(.+?)\]", l)
                    if m:
                        if dentro:
                            fim_secao = i
                            break
                        dentro = m.group(1).strip() == sec
                while fim_secao > 0 and not linhas[fim_secao - 1].strip():
                    fim_secao -= 1
            linhas.insert(fim_secao, f"{chave} = {valor}")
    arq.write_text("\n".join(linhas) + "\n", encoding="utf-8")


# ------------------------------------------------------------------ estado

class Estado:
    def __init__(self):
        self.lock = threading.Lock()
        self.fase = "pronto"        # pronto | rodando | conferir | erro | parado
        self.mensagem = ""
        self.produtos = []
        self.atual = 0
        self.logs = []              # [(seq, texto)]
        self.seq = 0
        self.relatorio = ""
        self.terminei = threading.Event()
        self.thread = None
        self.ultimo_ping = time.time()
        self.inicio = None
        self.nota_inicial = ""
        self.fila = [0, 0, ""]
        self.novidades = None       # {"versao", "itens"} quando acabou de se atualizar      # nota atual, total de notas, nome da nota

    def log(self, msg):
        with self.lock:
            for linha in str(msg).splitlines() or [""]:
                self.seq += 1
                self.logs.append((self.seq, linha))
            del self.logs[:-600]

    def json(self, desde):
        with self.lock:
            return {
                "fase": self.fase,
                "mensagem": self.mensagem,
                "produtos": self.produtos,
                "atual": self.atual,
                "logs": [{"n": n, "t": t} for n, t in self.logs if n > desde],
                "seq": self.seq,
                "acao": robo.CONTROLE.acao,
                "relatorio": Path(self.relatorio).name if self.relatorio else "",
                "inicio": self.inicio,
                "nota_inicial": self.nota_inicial,
                "fila": self.fila,
                "novidades": self.novidades,
                "versao": VERSAO,
            }


E = Estado()
robo.CONTROLE.ouvintes.append(E.log)


class OuvinteJanela(robo.Ouvinte):
    def lista(self, produtos):
        with E.lock:
            E.produtos = [{**p, "estado": "fila", "resultado": "", "arquivo": "", "origem": "", "codigo_barras": ""} for p in produtos]

    def produto(self, n, dados):
        with E.lock:
            E.atual = n
            if 0 < n <= len(E.produtos):
                E.produtos[n - 1].update({k: v for k, v in dados.items() if k in (
                    "estado", "resultado", "arquivo", "origem", "codigo_barras")})

    def fim(self, resumo):
        with E.lock:
            E.relatorio = resumo.get("relatorio", "")
            E.fase = "conferir"


def trabalhar(notas, so_pasta):
    """Faz uma nota depois da outra. Cada nota precisa ser salva no site antes da próxima,
    porque a tela de upload do FlowManeger guarda uma nota por vez."""
    notas = list(notas) or [None]
    total = len(notas)
    for k, nota in enumerate(notas, 1):
        with E.lock:
            E.fila = [k, total, nota.name if nota else ""]
            if k > 1:
                E.fase, E.mensagem, E.produtos, E.atual, E.relatorio = "rodando", "", [], 0, ""
        if total > 1:
            E.log("")
            E.log(f"==== Nota {k} de {total}: {nota.name if nota else ''} ====")
        if not trabalhar_uma(nota, so_pasta, k, total):
            break
    with E.lock:
        if E.fase == "pronto" and total > 1:
            E.mensagem = f"As {total} notas foram feitas. Pode enviar outras."


def trabalhar_uma(nota, so_pasta, k=1, total=1):
    """Devolve True se pode seguir para a próxima nota."""
    robo.CONTROLE.parar.clear()
    robo.CONTROLE.limpar()
    E.terminei.clear()
    falta = total - k

    def segurar(ctx):
        depois = (f" Depois toque em Próxima nota (ainda faltam {falta})." if falta
                  else " Depois toque em Terminei aqui.")
        robo.CONTROLE.pedir("conferir", "Confira e clique em Salvar" + (f" (nota {k} de {total})" if total > 1 else ""),
                            "As fotos e os códigos de barras já estão nos cards, na janela do Chrome do robô. "
                            "Confira e clique em SALVAR no site." + depois, ["Próxima nota" if falta else "Terminei"])
        while not E.terminei.is_set() and not robo.CONTROLE.parar.is_set():
            if robo.CONTROLE.pegar_resposta() in ("Terminei", "Próxima nota"):
                break
            try:
                if not ctx.pages:
                    break
                ctx.pages[0].title()  # mantém o navegador respondendo
            except Exception:
                break
            time.sleep(0.5)
        robo.CONTROLE.limpar()

    try:
        robo.rodar(robo.ler_config(), nota, so_pasta, OuvinteJanela(), esperar_fim=segurar)
        with E.lock:
            E.fase = "pronto" if E.fase != "erro" else E.fase
            E.mensagem = "Terminado. Pode enviar outra nota."
        return not robo.CONTROLE.parar.is_set()
    except robo.Parado:
        E.log("Robô parado.")
        with E.lock:
            E.fase, E.mensagem = "parado", "Você parou o robô."
        return False
    except Exception as e:  # mostra na janela em vez de sumir
        primeira = (str(e).splitlines() or [repr(e)])[0]
        E.log(f"ERRO: {primeira}" + (f" (na nota {k} de {total}; as outras ficaram para depois)" if total > 1 else ""))
        with E.lock:
            E.fase, E.mensagem = "erro", primeira
        return False
    finally:
        robo.CONTROLE.limpar()


def iniciar(dados):
    with E.lock:
        if E.thread and E.thread.is_alive():
            return {"ok": False, "erro": "O robô já está trabalhando."}
        E.fase, E.mensagem, E.produtos, E.atual, E.relatorio = "rodando", "", [], 0, ""
        E.inicio = time.time()
    notas = []
    for n in (dados.get("notas") or ([dados["nota"]] if dados.get("nota") else [])):
        if n.get("b64"):
            pasta = BASE / "notas"
            pasta.mkdir(exist_ok=True)
            nome = re.sub(r'[\\/:*?"<>|]', "_", n.get("nome") or "nota.pdf")
            nota = pasta / nome
            nota.write_bytes(base64.b64decode(n["b64"].split(",")[-1]))
            notas.append(nota)
        elif n.get("caminho") and Path(n["caminho"]).exists():
            notas.append(Path(n["caminho"]))
    E.log("")
    if len(notas) > 1:
        E.log(f"==== {time.strftime('%d/%m %H:%M')} começando {len(notas)} notas: " + ", ".join(x.name for x in notas) + " ====")
    else:
        E.log(f"==== {time.strftime('%d/%m %H:%M')} começando" + (f" com {notas[0].name}" if notas else "") + " ====")
    E.fila = [1, max(len(notas), 1), notas[0].name if notas else ""]
    E.thread = threading.Thread(target=trabalhar, args=(notas, bool(dados.get("so_pasta"))), daemon=True)
    E.thread.start()
    return {"ok": True}


# -------------------------------------------------------------------- ajudas

def abrir_no_sistema(caminho):
    caminho = str(caminho)
    try:
        if sys.platform.startswith("win"):
            os.startfile(caminho)  # noqa
        elif sys.platform == "darwin":
            subprocess.Popen(["open", caminho])
        else:
            subprocess.Popen(["xdg-open", caminho])
        return True
    except Exception:
        return False


def escolher_pasta(atual):
    """Janela de escolher pasta do Windows (num processo à parte, para não travar o servidor)."""
    codigo = ("import tkinter as tk, sys\nfrom tkinter import filedialog\nr=tk.Tk();r.withdraw();r.attributes('-topmost',True)\n"
              "p=filedialog.askdirectory(initialdir=sys.argv[1] or None,title='Pasta das imagens')\nprint(p or '')")
    exe = sys.executable.replace("pythonw.exe", "python.exe")
    try:
        flags = 0x08000000 if sys.platform.startswith("win") else 0  # CREATE_NO_WINDOW
        r = subprocess.run([exe, "-c", codigo, atual or ""], capture_output=True, text=True, timeout=600, creationflags=flags)
        p = r.stdout.strip()
        return p.replace("/", "\\") if sys.platform.startswith("win") else p
    except Exception:
        return ""


def achar_navegador():
    candidatos = []
    if sys.platform.startswith("win"):
        for base in filter(None, [os.environ.get("PROGRAMFILES"), os.environ.get("PROGRAMFILES(X86)"), os.environ.get("LOCALAPPDATA")]):
            candidatos += [Path(base) / "Google/Chrome/Application/chrome.exe", Path(base) / "Microsoft/Edge/Application/msedge.exe"]
    else:
        for n in ("google-chrome", "chromium", "chromium-browser", "microsoft-edge"):
            w = shutil.which(n)
            if w:
                candidatos.append(Path(w))
    if os.environ.get("ROBO_CHROMIUM"):
        candidatos.insert(0, Path(os.environ["ROBO_CHROMIUM"]))
    return next((c for c in candidatos if c.exists()), None)


def abrir_janela(url):
    exe = achar_navegador()
    if not exe:
        import webbrowser
        webbrowser.open(url)
        return None
    perfil = BASE / "perfil-janela"
    args = [str(exe), f"--app={url}", f"--user-data-dir={perfil}", "--no-first-run", "--no-default-browser-check",
            "--disable-features=Translate,MediaRouter", "--window-size=1440,920", "--force-dark-mode"]
    if os.environ.get("ROBO_HEADLESS") == "1":
        return None
    return subprocess.Popen(args)


# ---------------------------------------------------------------- servidor

TIPOS = {".html": "text/html; charset=utf-8", ".css": "text/css", ".js": "application/javascript", ".svg": "image/svg+xml",
         ".png": "image/png", ".ico": "image/x-icon", ".jpg": "image/jpeg", ".jpeg": "image/jpeg", ".webp": "image/webp",
         ".gif": "image/gif", ".bmp": "image/bmp", ".woff2": "font/woff2"}


class Handler(BaseHTTPRequestHandler):
    def log_message(self, *a):
        pass

    def _json(self, obj, code=200):
        dados = json.dumps(obj, ensure_ascii=False).encode()
        self.send_response(code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Cache-Control", "no-store")
        self.send_header("Content-Length", str(len(dados)))
        self.end_headers()
        self.wfile.write(dados)

    def _arquivo(self, caminho):
        if not caminho.is_file():
            self.send_error(404)
            return
        dados = caminho.read_bytes()
        self.send_response(200)
        self.send_header("Content-Type", TIPOS.get(caminho.suffix.lower(), "application/octet-stream"))
        self.send_header("Cache-Control", "no-store" if caminho.suffix == ".html" else "max-age=60")
        self.send_header("Content-Length", str(len(dados)))
        self.end_headers()
        self.wfile.write(dados)

    def _corpo(self):
        n = int(self.headers.get("Content-Length") or 0)
        if not n:
            return {}
        try:
            return json.loads(self.rfile.read(n).decode("utf-8"))
        except Exception:
            return {}

    def _local(self):
        # só aceita pedidos da própria janela (nada de outro site chamando o robô)
        origem = self.headers.get("Origin") or ""
        return not origem or origem.startswith(f"http://127.0.0.1:{PORTA}")

    def do_GET(self):
        u = urlparse(self.path)
        q = parse_qs(u.query)
        E.ultimo_ping = time.time()
        if u.path in ("/", "/index.html"):
            return self._arquivo(UI / "index.html")
        if u.path.startswith("/ui/"):
            alvo = (UI / u.path[4:]).resolve()
            if UI.resolve() in alvo.parents:
                return self._arquivo(alvo)
            return self.send_error(404)
        if u.path == "/api/estado":
            return self._json(E.json(int((q.get("desde") or ["0"])[0] or 0)))
        if u.path == "/api/config":
            return self._json(ler_valores())
        if u.path == "/api/foto":
            nome = (q.get("arq") or [""])[0]
            if not nome or "/" in nome or "\\" in nome or ".." in nome:
                return self.send_error(400)
            pasta = Path(ler_valores()["pasta_imagens"])
            return self._arquivo(pasta / nome)
        if u.path == "/api/arrumar":
            return self._json(ARRUMAR.json())
        if u.path == "/api/ping":
            return self._json({"ok": True, "app": "robo-boticario"})
        self.send_error(404)

    def do_POST(self):
        if not self._local():
            return self.send_error(403)
        u = urlparse(self.path)
        d = self._corpo()
        E.ultimo_ping = time.time()
        if u.path == "/api/iniciar":
            return self._json(iniciar(d))
        if u.path == "/api/parar":
            robo.CONTROLE.parar.set()
            E.terminei.set()
            return self._json({"ok": True})
        if u.path == "/api/responder":
            robo.CONTROLE.responder(d.get("valor"))
            if d.get("valor") == "Terminei":
                E.terminei.set()
            return self._json({"ok": True})
        if u.path == "/api/config":
            gravar_valores(d)
            return self._json({"ok": True, "valores": ler_valores()})
        if u.path == "/api/escolher_pasta":
            return self._json({"pasta": escolher_pasta(d.get("atual", ""))})
        if u.path == "/api/abrir":
            o = d.get("o")
            alvo = {"pasta": Path(ler_valores()["pasta_imagens"]), "relatorios": BASE / "relatorios",
                    "relatorio": Path(E.relatorio) if E.relatorio else None, "robo": BASE}.get(o)
            return self._json({"ok": bool(alvo) and abrir_no_sistema(alvo)})
        if u.path in ("/api/arrumar/analisar", "/api/arrumar/aplicar"):
            v = ler_valores()
            pasta = Path(v["pasta_imagens"])
            try:
                minimo = int(float(v.get("similaridade_minima") or 90))
            except Exception:
                minimo = 90
            res = {"ok": True}
            if u.path.endswith("aplicar"):
                res = arrumar.aplicar(pasta, d.get("mover") or [], d.get("renomear") or [])
                for t in res["feitos"]:
                    E.log(f"Pasta arrumada: {t}")
                for t in res["erros"]:
                    E.log(f"Pasta: não deu ({t})")
                res["ok"] = True
            ARRUMAR.comecar(pasta, BASE, minimo)
            return self._json(res)
        if u.path == "/api/sair":
            threading.Thread(target=sair, daemon=True).start()
            return self._json({"ok": True})
        self.send_error(404)


PORTA = 0


def sair():
    time.sleep(0.4)
    robo.CONTROLE.parar.set()
    E.terminei.set()
    if E.thread and E.thread.is_alive():
        E.thread.join(timeout=15)
    os._exit(0)


def porta_livre(preferida=8765):
    for p in [preferida] + list(range(8766, 8800)):
        with socket.socket() as s:
            try:
                s.bind(("127.0.0.1", p))
                return p
            except OSError:
                continue
    return 0


def ja_aberto():
    """Se o robô já está aberto, só mostra a janela dele de novo."""
    arq = BASE / ".porta"
    try:
        p = int(arq.read_text().strip())
        import urllib.request
        with urllib.request.urlopen(f"http://127.0.0.1:{p}/api/ping", timeout=1.5) as r:
            if json.loads(r.read().decode()).get("app") == "robo-boticario":
                return p
    except Exception:
        pass
    return None


def main():
    global PORTA
    nota = next((a for a in sys.argv[1:] if a.lower().endswith((".pdf", ".csv")) and Path(a).exists()), "")
    outra = ja_aberto()
    if outra:
        abrir_janela(f"http://127.0.0.1:{outra}/")
        return
    PORTA = porta_livre()
    srv = ThreadingHTTPServer(("127.0.0.1", PORTA), Handler)
    (BASE / ".porta").write_text(str(PORTA))
    E.nota_inicial = nota
    url = f"http://127.0.0.1:{PORTA}/"
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    E.log(f"Robô Boticário versão {VERSAO} aberto ({url})")
    try:
        nova = json.loads((BASE / ".atualizado").read_text(encoding="utf-8"))
        (BASE / ".atualizado").unlink()
        E.novidades = {"versao": nova.get("versao"), "itens": nova.get("novidades") or []}
        E.log(f"Atualizei sozinho para a versão {nova.get('versao')}. Novidades:")
        for t in nova.get("novidades") or []:
            E.log(f"   • {t}")
    except Exception:
        pass
    proc = abrir_janela(url)
    # fecha sozinho quando a janela fecha (a janela pergunta o estado a cada segundo)
    E.ultimo_ping = time.time() + 40
    while True:
        time.sleep(2)
        rodando = E.thread is not None and E.thread.is_alive()
        if proc is not None and proc.poll() is not None and time.time() - E.ultimo_ping > 8 and not rodando:
            break
        if time.time() - E.ultimo_ping > 25 and not rodando and os.environ.get("ROBO_HEADLESS") != "1":
            break
    try:
        (BASE / ".porta").unlink()
    except Exception:
        pass
    os._exit(0)


if __name__ == "__main__":
    main()
