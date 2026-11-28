# -*- coding: utf-8 -*-
"""Converte as fichas de conjunto (CBR, CMK, CNT, CPN) para "conjunto" montado com as peças do site
e liga as fichas de tamanhos diferentes do mesmo produto ("fotos_de").
Roda uma vez só (trava _conjuntos-pecas-feito.txt). Sem --aplicar só mostra o que faria.
Backup: ficha.md.bak-<data> na mesma pasta."""
import json, re, sys, shutil
from datetime import datetime
from pathlib import Path

RAIZ = Path(sys.argv[1]) if len(sys.argv) > 1 and not sys.argv[1].startswith("--") else Path(r"C:\projetos\robo-stylo\produtos")
APLICAR = "--aplicar" in sys.argv
TRAVA = RAIZ.parent / "_conjuntos-pecas-feito.txt"
CARIMBO = datetime.now().strftime("%Y%m%d-%H%M")
log = []


def say(t):
    print(t)
    log.append(t)


def pastas():
    for lugar in (RAIZ, RAIZ / "feitos"):
        if lugar.is_dir():
            for p in sorted(lugar.iterdir()):
                if p.is_dir() and not p.name.startswith("_") and p.name.lower() != "feitos":
                    yield p


def codigo_da_pasta(p):
    m = re.match(r"^([A-Za-z0-9]+)", p.name)
    return m.group(1).upper() if m else ""


def achar(cod):
    return next((p for p in pastas() if codigo_da_pasta(p) == cod.upper()), None)


def ler(pasta):
    arq = pasta / "ficha.md"
    txt = arq.read_text(encoding="utf-8")
    blocos = list(re.finditer(r"```json\s*(.*?)```", txt, re.S))
    if not blocos:
        raise ValueError("sem bloco json")
    b = blocos[-1]
    return txt, b, json.loads(b.group(1))


def gravar(pasta, txt, b, d):
    novo = txt[:b.start()] + "```json\n" + json.dumps(d, ensure_ascii=False, indent=2) + "\n```" + txt[b.end():]
    if APLICAR:
        shutil.copy2(pasta / "ficha.md", pasta / f"ficha.md.bak-{CARIMBO}")
        (pasta / "ficha.md").write_text(novo, encoding="utf-8")


CONJ = {
    "CBR": ({"pecas": [{"codigo": "MBR", "quantidade": 1, "so": ["com tampo"], "rotulo": "mesa"},
                       {"codigo": "BQT54", "quantidade": 2, "rotulo": "banquetas"}], "combinar": "cor"}, True),
    "CMK": ({"pecas": [{"codigo": "MMC", "quantidade": 1, "rotulo": "mesa"},
                       {"codigo": "CSM", "quantidade": 2, "rotulo": "cadeiras"}], "combinar": "cor"}, True),
    # CNT tem preço de conjunto (1790/1880, menor que a soma): fica o preço da ficha
    "CNT": ({"pecas": [{"codigo": "NTU", "quantidade": 1, "excluir": ["cor à escolha"], "rotulo": "namoradeira"},
                       {"codigo": "PTU", "quantidade": 2, "excluir": ["cor à escolha"], "rotulo": "poltronas"}], "combinar": "igual"}, False),
    "CPN": ({"pecas": [{"codigo": "NAR", "quantidade": 1, "excluir": ["cor à escolha"], "rotulo": "namoradeira"},
                       {"codigo": "PAR", "quantidade": 2, "excluir": ["cor à escolha"], "rotulo": "poltronas"}], "combinar": "igual"}, True),
}


def main():
    if TRAVA.exists() and APLICAR:
        print(f"Já rodou antes ({TRAVA}). Nada feito.")
        return
    for cod, (conj, preco_das_pecas) in CONJ.items():
        pasta = achar(cod)
        if not pasta:
            say(f"[conjunto] {cod}: pasta não encontrada, pulei")
            continue
        txt, b, d = ler(pasta)
        d["conjunto"] = conj
        d["tipo"] = "kit"
        n = 0
        for v in d.get("variacoes") or []:
            if isinstance(v, dict) and v.get("componentes"):
                v.pop("estoque", None)
                v.pop("custo", None)
                if preco_das_pecas:
                    v.pop("venda", None)
                n += 1
        gravar(pasta, txt, b, d)
        say(f"[conjunto] {cod}: monta com {' + '.join(str(p['quantidade']) + '× ' + p['codigo'] for p in conj['pecas'])}"
            f" ({conj['combinar']}); {n} opções da ficha ficam com nome e fotos"
            + ("; preço = soma das peças" if preco_das_pecas else "; preço da ficha mantido"))

    # fichas do mesmo produto em tamanhos diferentes: ligadas pelas fotos
    grupos = {}
    for p in pastas():
        c = codigo_da_pasta(p)
        m = re.match(r"^([A-Z]+)(\d+)$", c)
        base = m.group(1) if m else c
        grupos.setdefault(base, []).append((c, p))
    for base, itens in sorted(grupos.items()):
        codigos = [c for c, _ in itens]
        if len(itens) < 2 or base not in codigos or len(set(codigos)) != len(codigos):
            continue
        for c, p in itens:
            try:
                txt, b, d = ler(p)
            except Exception as e:
                say(f"[ligar] {c}: {e}, pulei")
                continue
            outros = [x for x in codigos if x != c]
            d["fotos_de"] = outros
            gravar(p, txt, b, d)
        say(f"[ligar] {', '.join(codigos)}: fotos ligadas entre si")

    if APLICAR:
        TRAVA.write_text("\n".join(log), encoding="utf-8")
    print("\nAPLICADO" if APLICAR else "\nSÓ TESTE (use --aplicar)")


main()
