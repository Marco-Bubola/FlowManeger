"""Atualiza o Robô Boticário sozinho quando ele abre.

Procura no GitHub (repositório do FlowManeger) um versao.json com número maior que o daqui.
Se tiver, baixa os arquivos listados, confere se nenhum veio quebrado, guarda os atuais em
_versao-anterior/vN e troca. Sem internet ou com qualquer erro, o robô abre do jeito que está.
Para desligar: em config.ini, seção [atualizar], coloque  desligado = sim
"""
import ast
import configparser
import json
import re
import shutil
import subprocess
import sys
import tempfile
import urllib.parse
import urllib.request
from pathlib import Path

BASE = Path(__file__).resolve().parent
ARQ = "versao.json"
PADRAO = {
    "repo": "Marco-Bubola/FlowManeger",
    "ref": "claude/project-thread-c4qmsx",
    "pasta": "ferramentas/robo-boticario",
}
NUNCA = {"config.ini", ".porta"}  # o que é do Marco nunca é trocado


def versao_local():
    try:
        return json.loads((BASE / ARQ).read_text(encoding="utf-8"))
    except Exception:
        return {"versao": 2}  # a 2 foi a última sem versao.json


def _cfg():
    c = configparser.ConfigParser()
    try:
        c.read(BASE / "config.ini", encoding="utf-8-sig")
    except Exception:
        pass
    sec = c["atualizar"] if c.has_section("atualizar") else {}
    out = {k: (sec.get(k) or v).strip() for k, v in PADRAO.items()}
    out["desligado"] = (sec.get("desligado") or "").strip().lower() in ("sim", "s", "1", "true", "yes")
    out["token"] = (sec.get("token") or "").strip()
    out["base_url"] = (sec.get("base_url") or "https://api.github.com").rstrip("/")  # só para teste
    return out


def _api(cfg, caminho, cru=False, timeout=8):
    req = urllib.request.Request(
        f"{cfg['base_url']}/repos/{cfg['repo']}/{caminho}",
        headers={"Accept": "application/vnd.github.raw" if cru else "application/vnd.github+json",
                 "User-Agent": "robo-boticario"})
    if cfg.get("token"):
        req.add_header("Authorization", f"Bearer {cfg['token']}")
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return r.read()


def _seguro(nome):
    return bool(re.fullmatch(r"[\w .\-]+(/[\w .\-]+)*", nome)) and ".." not in nome \
        and not nome.startswith(("/", ".")) and nome.lower() not in NUNCA


def checar(cfg=None):
    """Devolve o versao.json novo (com 'sha') ou None se já está em dia."""
    cfg = cfg or _cfg()
    if cfg["desligado"]:
        return None
    ref = urllib.parse.quote(cfg["ref"], safe="")
    commits = json.loads(_api(cfg, f"commits?sha={ref}&path={cfg['pasta']}&per_page=1"))
    if not commits:
        return None
    sha = commits[0]["sha"]
    nova = json.loads(_api(cfg, f"contents/{cfg['pasta']}/{ARQ}?ref={sha}", cru=True))
    if int(nova.get("versao") or 0) <= int(versao_local().get("versao") or 0):
        return None
    nova["sha"] = sha
    return nova


def instalar(nova, cfg=None, avisar=lambda t: None):
    """Baixa, confere e troca os arquivos. Não troca nada se algum vier quebrado."""
    cfg = cfg or _cfg()
    arquivos = [a for a in nova.get("arquivos") or [] if _seguro(a)]
    if not arquivos:
        raise ValueError("a versão nova não lista arquivos")
    tmp = Path(tempfile.mkdtemp(prefix="robo-boticario-"))
    try:
        for i, a in enumerate(arquivos, 1):
            avisar(f"baixando {a} ({i}/{len(arquivos)})")
            dados = _api(cfg, f"contents/{cfg['pasta']}/{urllib.parse.quote(a)}?ref={nova['sha']}", cru=True, timeout=60)
            if a.endswith((".py", ".pyw")):
                ast.parse(dados.decode("utf-8"))
            if not dados:
                raise ValueError(f"{a} veio vazio")
            (tmp / a).parent.mkdir(parents=True, exist_ok=True)
            (tmp / a).write_bytes(dados)
        guardar = {k: v for k, v in nova.items()}
        (tmp / ARQ).write_text(json.dumps(guardar, ensure_ascii=False, indent=2), encoding="utf-8")
        atual = versao_local().get("versao") or "antiga"
        copia = BASE / "_versao-anterior" / f"v{atual}"
        req_antes = (BASE / "requirements.txt").read_bytes() if (BASE / "requirements.txt").exists() else b""
        for a in arquivos + [ARQ]:
            if (BASE / a).exists():
                (copia / a).parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(BASE / a, copia / a)
        for a in arquivos + [ARQ]:
            (BASE / a).parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(tmp / a, BASE / a)
    finally:
        shutil.rmtree(tmp, ignore_errors=True)
    if "requirements.txt" in arquivos and (BASE / "requirements.txt").read_bytes() != req_antes:
        py = Path(sys.executable)
        if py.name.lower() == "pythonw.exe" and (py.parent / "python.exe").exists():
            py = py.parent / "python.exe"
        avisar("instalando o que a versão nova precisa")
        subprocess.run([str(py), "-m", "pip", "install", "-q", "-r", str(BASE / "requirements.txt")],
                       cwd=str(BASE), creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0), timeout=600)
    return True


def ao_abrir():
    """Chamado pelo app.pyw antes de tudo. Devolve a versão instalada (dict) se trocou algo, senão None."""
    try:
        cfg = _cfg()
        nova = checar(cfg)
        if not nova:
            return None
        instalar(nova, cfg)
        (BASE / ".atualizado").write_text(json.dumps(nova, ensure_ascii=False), encoding="utf-8")
        return nova
    except Exception as e:  # sem internet, GitHub fora etc.: abre do jeito que está
        try:
            (BASE / "atualizar-erro.txt").write_text(f"{type(e).__name__}: {e}", encoding="utf-8")
        except Exception:
            pass
        return None
