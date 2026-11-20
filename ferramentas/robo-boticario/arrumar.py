"""Arrumar a pasta de imagens: fotos repetidas, nomes ruins, fotos pequenas e produtos sem foto.

Nada é apagado: as repetidas vão para a subpasta _repetidas, e renomear só acontece
no que você marcar na janela.
"""
import csv
import hashlib
import re
import threading
import time
from pathlib import Path

import robo

REPETIDAS = "_repetidas"
PEQUENA = 500  # lado maior abaixo disso fica feio no catálogo

GENERICO = re.compile(
    r"^(img|image|imagem|foto|photo|pic|dsc|dscn|screenshot|captura de tela|captura|print|download|baixados|"
    r"whatsapp image|whatsapp|unnamed|sem titulo|untitled)(?![a-z])",
    re.I)
SO_LIXO = re.compile(r"^[\W\d_]*$")  # só números/símbolos: 12345.jpg, ____.png
UUID_RE = re.compile(r"[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}", re.I)
COPIA = re.compile(r"(\s*\(\d+\)|\s*-\s*c[oó]pia(\s*\(\d+\))?|\s*-\s*copy(\s*\(\d+\))?|^c[oó]pia de\s+|^copy of\s+)", re.I)

ASSINATURAS = [  # (começo do arquivo, extensão certa)
    (b"\xff\xd8\xff", ".jpg"), (b"\x89PNG", ".png"), (b"GIF8", ".gif"), (b"BM", ".bmp"),
]


def _ext_real(cab):
    if cab[:4] == b"RIFF" and cab[8:12] == b"WEBP":
        return ".webp"
    for ini, ext in ASSINATURAS:
        if cab.startswith(ini):
            return ext
    return None


def _md5(p):
    h = hashlib.md5()
    with open(p, "rb") as f:
        for bloco in iter(lambda: f.read(1 << 20), b""):
            h.update(bloco)
    return h.hexdigest()


def _tamanho(p):
    try:
        from PIL import Image
        with Image.open(p) as im:
            return im.size
    except Exception:
        return None


def produtos_dos_relatorios(base):
    """{codigo: nome} de todas as notas já feitas (relatórios do robô)."""
    out = {}
    arqs = sorted(list((base / "relatorios").glob("relatorio-*.csv")) + list(base.glob("relatorio-*.csv")),
                  key=lambda p: p.stat().st_mtime)
    for a in arqs:
        try:
            with open(a, encoding="utf-8-sig", newline="") as f:
                for linha in csv.DictReader(f, delimiter=";"):
                    cod = robo.so_digitos(linha.get("codigo"))
                    nome = (linha.get("nome") or "").strip()
                    res = (linha.get("resultado") or "").lower()
                    if cod and nome and "amostra" not in res:
                        out[cod] = nome
        except Exception:
            continue
    return out


def _nome_sugerido(p, produtos):
    """Devolve (motivo, sugestão ou "") para um nome de arquivo ruim, ou None se o nome está bom."""
    stem, ext = p.stem, p.suffix
    limpo = re.sub(r"\s+", " ", COPIA.sub("", stem)).strip(" .-_")
    sem_uuid = re.sub(r"\s+", " ", UUID_RE.sub(" ", stem)).strip(" .-_")
    digitos = re.findall(r"\d{5,}", sem_uuid)
    pelo_codigo = next((produtos[d] for d in digitos if d in produtos), "")
    if GENERICO.match(stem) or SO_LIXO.match(stem) or (UUID_RE.search(stem) and len(sem_uuid) < 4):
        sug = robo.nome_limpo_para_arquivo(pelo_codigo) if pelo_codigo else ""
        return "nome não diz qual é o produto", (sug + ext.lower()) if sug else ""
    if COPIA.search(stem) and limpo:
        return "parece cópia (\"(1)\", \"Cópia de\")", limpo + ext
    if re.search(r"\s{2,}|^\s|\s$", stem) or stem.endswith("."):
        return "espaços sobrando", re.sub(r"\s+", " ", stem).strip().rstrip(".") + ext
    return None


def analisar(pasta, base, avisar=lambda t: None, minimo=90):
    pasta = Path(pasta)
    if not pasta.exists():
        raise FileNotFoundError(f"A pasta de imagens não existe: {pasta}")
    arquivos = [p for p in pasta.iterdir() if p.is_file() and p.suffix.lower() in robo.EXTS]
    produtos = produtos_dos_relatorios(base)
    total = len(arquivos)
    hashes, info = {}, {}
    nomes, pequenas, ext_errada = [], [], []
    for i, p in enumerate(arquivos, 1):
        if i % 25 == 0 or i == total:
            avisar(f"Olhando as fotos… {i} de {total}")
        try:
            st = p.stat()
            with open(p, "rb") as f:
                cab = f.read(16)
            h = _md5(p)
        except Exception:
            continue
        tam = _tamanho(p)
        info[p.name] = {"arq": p.name, "bytes": st.st_size, "mtime": st.st_mtime,
                        "w": tam[0] if tam else 0, "h": tam[1] if tam else 0}
        hashes.setdefault(h, []).append(p.name)
        real = _ext_real(cab)
        if real and real != (".jpg" if p.suffix.lower() == ".jpeg" else p.suffix.lower()):
            ext_errada.append({**info[p.name], "motivo": f"é {real[1:].upper()} com nome de {p.suffix[1:].upper()}",
                               "sugestao": p.stem + real})
        else:
            r = _nome_sugerido(p, produtos)
            if r:
                nomes.append({**info[p.name], "motivo": r[0], "sugestao": r[1]})
        if tam and max(tam) < PEQUENA:
            pequenas.append({**info[p.name], "motivo": f"{tam[0]}×{tam[1]} pixels"})

    def melhor(grupo):  # fica a maior; empate, a mais nova; nome "limpo" ganha de cópia
        return max(grupo, key=lambda n: (info[n]["w"] * info[n]["h"], not COPIA.search(Path(n).stem),
                                         info[n]["bytes"], info[n]["mtime"]))

    repetidas = []
    for grupo in hashes.values():
        if len(grupo) > 1:
            fica = melhor(grupo)
            repetidas.append({"tipo": "iguais", "fica": info[fica],
                              "sobram": [info[n] for n in grupo if n != fica]})
    # mesmo produto (mesmo nome depois de limpar cópias), fotos diferentes
    ja = {n for g in repetidas for n in [g["fica"]["arq"]] + [s["arq"] for s in g["sobram"]]}
    por_nome = {}
    for n in info:
        if n in ja:
            continue
        chave = robo.normalizar(COPIA.sub("", Path(n).stem))
        if len(chave) >= 6 and not GENERICO.match(chave):
            por_nome.setdefault(chave, []).append(n)
    for grupo in por_nome.values():
        if len(grupo) > 1:
            fica = melhor(grupo)
            repetidas.append({"tipo": "mesmo nome", "fica": info[fica],
                              "sobram": [info[n] for n in grupo if n != fica]})

    saem = {s["arq"] for g in repetidas for s in g["sobram"]}
    nomes = [x for x in nomes if x["arq"] not in saem]
    ext_errada = [x for x in ext_errada if x["arq"] not in saem]

    # produtos das notas que continuam sem foto na pasta
    busca = robo.PastaImagens(pasta, minimo)
    sem_foto = []
    for cod, nome in produtos.items():
        if robo.eh_amostra(nome, [t.strip() for t in robo.PULAR_PADRAO.split("|")]):
            continue
        achado, _ = busca.procurar(nome, cod)
        if not achado:
            sem_foto.append({"codigo": cod, "nome": nome})
    sem_foto.sort(key=lambda x: x["nome"])

    return {
        "pasta": str(pasta), "total": total, "quando": time.strftime("%d/%m %H:%M"),
        "repetidas": sorted(repetidas, key=lambda g: g["fica"]["arq"].lower()),
        "nomes": sorted(ext_errada + nomes, key=lambda x: x["arq"].lower()),
        "pequenas": sorted(pequenas, key=lambda x: x["arq"].lower()),
        "sem_foto": sem_foto,
    }


def _nome_ok(n):
    return bool(n) and not re.search(r'[\\/:*?"<>|]', n) and ".." not in n and n.strip() == n \
        and Path(n).suffix.lower() in robo.EXTS


def aplicar(pasta, mover=(), renomear=()):
    """mover: nomes para _repetidas. renomear: [{"de", "para"}]. Devolve o que deu e o que não deu."""
    pasta = Path(pasta)
    feitos, erros = [], []
    destino = pasta / REPETIDAS
    for n in mover:
        o = pasta / n
        if not _nome_ok(n) or not o.is_file():
            erros.append(f"{n}: não achei")
            continue
        destino.mkdir(exist_ok=True)
        alvo, k = destino / n, 2
        while alvo.exists():
            alvo = destino / f"{Path(n).stem} ({k}){Path(n).suffix}"
            k += 1
        try:
            o.rename(alvo)
            feitos.append(f"{n} → {REPETIDAS}")
        except Exception as e:
            erros.append(f"{n}: {e}")
    for r in renomear:
        de, para = (r.get("de") or "").strip(), (r.get("para") or "").strip()
        if not _nome_ok(de) or not (pasta / de).is_file():
            erros.append(f"{de}: não achei")
            continue
        if not _nome_ok(para):
            erros.append(f"{de}: o nome novo \"{para}\" não serve (precisa terminar em .jpg, .png ou .webp e não ter / \\ : * ? \" < > |)")
            continue
        if para == de:
            continue
        if (pasta / para).exists() and para.lower() != de.lower():
            erros.append(f"{de}: já existe uma foto chamada {para}")
            continue
        try:
            (pasta / de).rename(pasta / para)
            feitos.append(f"{de} → {para}")
        except Exception as e:
            erros.append(f"{de}: {e}")
    return {"feitos": feitos, "erros": erros}


class Trabalho:
    """A análise roda em segundo plano; a janela pergunta como está."""

    def __init__(self):
        self.lock = threading.Lock()
        self.estado = {"rodando": False, "msg": "", "resultado": None, "erro": ""}

    def comecar(self, pasta, base, minimo=90):
        with self.lock:
            if self.estado["rodando"]:
                return False
            self.estado = {"rodando": True, "msg": "Começando…", "resultado": None, "erro": ""}

        def ir():
            try:
                res = analisar(pasta, base, avisar=lambda t: self._msg(t), minimo=minimo)
                with self.lock:
                    self.estado.update(rodando=False, resultado=res, msg="")
            except Exception as e:
                with self.lock:
                    self.estado.update(rodando=False, erro=str(e), msg="")
        threading.Thread(target=ir, daemon=True).start()
        return True

    def _msg(self, t):
        with self.lock:
            self.estado["msg"] = t

    def json(self):
        with self.lock:
            return dict(self.estado)
