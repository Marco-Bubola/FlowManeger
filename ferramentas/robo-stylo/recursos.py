# -*- coding: utf-8 -*-
"""
Recursos extras do robô da Móveis Stylo (sem navegador, menos a conferência de concorrentes):
preço do Mercado Livre, calculadora de custo e preço, concorrentes, título e descrição,
fotos prontas, atualização em lote, kits e etiquetas.
"""
import json
import os
import math
import re
import shutil
import statistics
import unicodedata
import urllib.error
import urllib.request
from datetime import datetime
from pathlib import Path

import motor

try:
    from PIL import Image, ImageDraw, ImageFont, ImageOps
except ImportError:
    Image = None


# ------------------------------------------------------------ preços e taxas

TAXAS = {"classico": 0.14, "premium": 0.19}


def custo_fixo_ml(preco):
    """Custo fixo por unidade que o Mercado Livre cobra abaixo de R$ 79 (o mesmo que o site usa)."""
    if preco <= 0 or preco >= 79:
        return 0.0
    if preco < 29:
        return 6.25
    if preco < 50:
        return 6.50
    return 6.75


def arredondar_90(v):
    """79,13 vira 79,90; 80,95 vira 81,90."""
    if v <= 0:
        return 0.0
    base = math.floor(v)
    return base + 0.9 if v <= base + 0.9 else base + 1.9


def conta_venda(preco, custo, tipo="classico", frete=0.0):
    """Quanto sobra de uma venda no Mercado Livre."""
    taxa = round(preco * TAXAS.get(tipo, 0.14), 2)
    fixo = custo_fixo_ml(preco)
    recebe = round(preco - taxa - fixo - (frete or 0), 2)
    lucro = round(recebe - (custo or 0), 2)
    return {"preco": preco, "taxa": taxa, "fixo": fixo, "frete": frete or 0, "recebe": recebe, "lucro": lucro,
            "margem": round(lucro / preco * 100, 1) if preco else 0.0}


def preco_sugerido(custo, margem_pct=30.0, tipo="classico", frete=0.0, redondo=True):
    """Preço para sobrar margem_pct% de lucro depois da taxa, do custo fixo e do frete."""
    taxa = TAXAS.get(tipo, 0.14)
    m = (margem_pct or 0) / 100.0
    if taxa + m >= 0.95:
        return None
    p = (custo + (frete or 0)) / (1 - taxa - m)
    for _ in range(4):
        p = (custo + (frete or 0) + custo_fixo_ml(p)) / (1 - taxa - m)
    p = arredondar_90(p) if redondo else round(p, 2)
    return p


def estatisticas(precos):
    """Menor, mediana e maior dos preços (sem os 10% mais baratos e mais caros quando tem bastante)."""
    v = sorted(float(p) for p in precos if p and float(p) > 0)
    if not v:
        return None
    corte = len(v) // 10 if len(v) >= 10 else 0
    meio = v[corte:len(v) - corte] or v
    return {"qtd": len(v), "menor": meio[0], "mediana": round(statistics.median(meio), 2), "maior": meio[-1],
            "minimo": v[0], "maximo": v[-1], "media": round(statistics.mean(meio), 2)}


# ------------------------------------------------------------ concorrentes

ARQ_CONCORRENTES = motor.BASE / "concorrentes.json"


def ler_concorrentes():
    try:
        dados = json.loads(ARQ_CONCORRENTES.read_text(encoding="utf-8"))
        return [c for c in dados if isinstance(c, dict) and c.get("link")]
    except Exception:
        return []


def salvar_concorrentes(lista):
    ARQ_CONCORRENTES.write_text(json.dumps(lista, ensure_ascii=False, indent=1), encoding="utf-8")


def _chave_link(link):
    m = re.search(r"MLB-?(\d+)", str(link), flags=re.I)
    return "MLB" + m.group(1) if m else str(link).split("?")[0].split("#")[0]


def vigiar(link, titulo="", preco=None, imagem="", ficha=""):
    """Põe um anúncio na lista de concorrentes (não repete)."""
    lista = ler_concorrentes()
    chave = _chave_link(link)
    for c in lista:
        if _chave_link(c["link"]) == chave:
            return False
    item = {"link": link, "titulo": titulo, "imagem": imagem, "ficha": ficha,
            "adicionado": datetime.now().isoformat(timespec="seconds"), "historico": []}
    if preco:
        item["historico"].append({"quando": item["adicionado"], "preco": float(preco)})
    lista.insert(0, item)
    salvar_concorrentes(lista)
    return True


def parar_de_vigiar(link):
    salvar_concorrentes([c for c in ler_concorrentes() if c["link"] != link])


def anotar_preco(link, preco, titulo=None):
    """Guarda o preço de hoje. Devolve (preço anterior, preço novo)."""
    lista = ler_concorrentes()
    anterior = None
    for c in lista:
        if c["link"] == link:
            if titulo:
                c["titulo"] = titulo
            hist = c.setdefault("historico", [])
            anterior = hist[-1]["preco"] if hist else None
            if preco is not None:
                hist.append({"quando": datetime.now().isoformat(timespec="seconds"), "preco": float(preco)})
                c["historico"] = hist[-60:]
            c["conferido"] = datetime.now().isoformat(timespec="seconds")
    salvar_concorrentes(lista)
    return anterior, preco


def ultima_conferencia():
    datas = [c.get("conferido") for c in ler_concorrentes() if c.get("conferido")]
    return max(datas) if datas else None


# ------------------------------------------------------------ título e descrição

def _medida_txt(v):
    med = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
    partes = []
    for k, rot in (("altura", "altura"), ("largura", "largura"), ("profundidade", "profundidade"), ("diametro", "diâmetro")):
        if med.get(k):
            partes.append(f"{rot} {motor.numero(med[k]):g} cm".replace(".", ","))
    return ", ".join(partes)


def titulo_modelo(d):
    nome = str(d.get("nome") or "").strip()
    extras = []
    mat = str(d.get("material") or "").strip()
    if mat and mat.split()[0].lower() not in nome.lower():
        extras.append(mat.split(",")[0])
    vs = [v for v in d.get("variacoes") or [] if isinstance(v, dict)]
    tamanhos = []
    for v in vs:
        t = str(v.get("valor") or "").split("·")[0].strip()
        if re.search(r"\d", t) and t not in tamanhos:
            tamanhos.append(t)
    if len(tamanhos) == 1:
        extras.append(tamanhos[0])
    titulo = nome
    for e in extras:
        if len(f"{titulo} {e}") <= 60:
            titulo = f"{titulo} {e}"
    if len(titulo) > 60:
        titulo = titulo[:60].rsplit(" ", 1)[0]
    return titulo.strip()


def descricao_modelo(d):
    """Descrição simples e verdadeira montada com os campos da ficha (sem IA)."""
    nome = str(d.get("nome") or "Produto").strip()
    p = []
    uso = str(d.get("uso") or "").strip()
    p.append(f"{nome}." + (f" {uso}" if uso else ""))
    mat = str(d.get("material") or "").strip()
    acab = str(d.get("acabamento") or "").strip()
    if mat or acab:
        frase = f"Feito em {mat[0].lower() + mat[1:]}" if mat else "Peça"
        if acab:
            frase += f", com acabamento {acab[0].lower() + acab[1:]}"
        p.append(frase + ". Fabricação própria da Móveis Stylo.")
    vs = [v for v in d.get("variacoes") or [] if isinstance(v, dict)]
    linhas = []
    for v in vs:
        med = _medida_txt(v)
        rot = str(v.get("valor") or "").strip()
        if rot or med:
            linhas.append("- " + " : ".join(x for x in [rot, med] if x))
    if linhas:
        por = {"Tamanho": "Tamanhos", "Cor": "Cores"}.get(str(d.get("variacao_por") or ""), "Opções")
        p.append(f"{por} disponíveis:\n" + "\n".join(dict.fromkeys(linhas)))
    if d.get("carga_maxima_kg"):
        p.append("Aguenta até " + f"{motor.numero(d['carga_maxima_kg']):g}".replace(".", ",") + " kg.")
    caixa = str(d.get("o_que_vem_na_caixa") or "").strip()
    mont = str(d.get("montagem") or "").strip()
    if caixa or mont:
        p.append(" ".join(x for x in [f"Vem na caixa: {caixa}." if caixa else "", f"{mont}." if mont else ""] if x))
    if motor.sim(d.get("sob_encomenda")):
        dias = int(motor.numero(d.get("prazo_dias")))
        p.append(f"Produto feito sob encomenda: fica pronto em {dias} dias úteis após a compra." if dias else
                 "Produto feito sob encomenda.")
    gar = int(motor.numero(d.get("garantia_meses")))
    if gar:
        p.append(f"Garantia de {gar} {'mês' if gar == 1 else 'meses'} contra defeitos de fabricação.")
    p.append("Cuidados: limpe com pano seco ou levemente úmido. Não use produtos abrasivos.")
    return "\n\n".join(p)[:5000]


def gerar_textos_ia(d, chave, modelo="gemini-2.5-flash", timeout=60):
    """Título e descrição pelo Gemini (precisa da chave nas Configurações). Devolve dict ou levanta erro."""
    resumo = {k: d.get(k) for k in ("nome", "categoria", "material", "acabamento", "uso", "o_que_vem_na_caixa",
                                     "montagem", "carga_maxima_kg", "garantia_meses", "sob_encomenda", "prazo_dias",
                                     "variacao_por", "ficha_tecnica")}
    resumo["variacoes"] = [{"valor": v.get("valor"), "medidas_peca_cm": v.get("medidas_peca_cm")}
                           for v in d.get("variacoes") or [] if isinstance(v, dict)][:20]
    pedido = (
        "Você escreve anúncios do Mercado Livre para a Móveis Stylo, que fabrica peças de ferro e madeira.\n"
        "Com os dados abaixo, escreva:\n"
        "1. titulo: até 60 caracteres. Comece pelo que a peça é, depois material e medida. Sem 'promoção', sem 'frete grátis', sem emojis.\n"
        "2. descricao: 4 a 7 parágrafos curtos em português do Brasil: o que é, para que serve, material e acabamento, "
        "medidas de cada opção, o que vem na caixa, prazo (se for sob encomenda), garantia e cuidados. "
        "Use só os dados fornecidos, não invente medidas nem números. Sem links, telefone ou emojis. Até 3000 caracteres.\n"
        "Responda só com JSON: {\"titulo\": \"...\", \"descricao\": \"...\"}\n\n"
        "Dados:\n" + json.dumps(resumo, ensure_ascii=False))
    url = f"https://generativelanguage.googleapis.com/v1beta/models/{modelo}:generateContent?key={chave}"
    corpo = {"contents": [{"parts": [{"text": pedido}]}],
             "generationConfig": {"responseMimeType": "application/json", "temperature": 0.6}}
    req = urllib.request.Request(url, data=json.dumps(corpo).encode("utf-8"), headers={"Content-Type": "application/json"})
    try:
        with urllib.request.urlopen(req, timeout=timeout) as r:
            resp = json.loads(r.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        detalhe = e.read().decode("utf-8", "replace")[:200]
        if e.code in (400, 401, 403):
            raise RuntimeError("A chave do Gemini não foi aceita. Confira em Configurações.") from None
        raise RuntimeError(f"O Gemini respondeu com erro {e.code}: {detalhe}") from None
    except urllib.error.URLError:
        raise RuntimeError("Sem conexão com o Gemini. Confira a internet.") from None
    try:
        texto = resp["candidates"][0]["content"]["parts"][0]["text"]
        j = json.loads(re.search(r"\{.*\}", texto, flags=re.S).group(0))
        titulo = str(j.get("titulo") or "").strip()
        if len(titulo) > 60:
            titulo = titulo[:60].rsplit(" ", 1)[0]
        return {"titulo": titulo, "descricao": str(j.get("descricao") or "").strip()[:5000]}
    except Exception:
        raise RuntimeError("O Gemini não devolveu o texto no formato esperado. Tente de novo.") from None


# ------------------------------------------------------------ imagens com IA

# (chave, rótulo, o que pedir). A ordem é a ordem das fotos no anúncio.
# ------------------------------------------------------------------ cores cadastradas (corano, ferragem, madeira)
# Ficam em cores.json, na pasta do robô (é do seu PC: a atualização não mexe nele).

ARQ_CORES = motor.BASE / "cores.json"

CORES_PADRAO = {"grupos": [
    {"chave": "ferragem", "nome": "Cor da ferragem", "prefixo": "", "canva": "estrutura de ferro pintada na cor {n}",
     "itens": [["Preto", "#1f1f1f"], ["Branco", "#f4f4f2"], ["Cinza", "#a3a7ab"], ["Bege", "#d8c7a8"], ["Grafite", "#3d4045"],
               ["Marrom", "#5a3e2b"], ["Bronze", "#6b4a2f"], ["Dourado", "#c9a24a"],
               ["Preto fosco", "#262626"], ["Preto brilhante", "#0d0d0d"], ["Branco fosco", "#ecebe6"], ["Cinza platina", "#c4c6c8"],
               ["Areia", "#cdb894"], ["Champagne", "#d6c29a"], ["Cobre", "#b0683c"], ["Rose gold", "#c99486"],
               ["Prata", "#b9bcc0"], ["Cromado", "#dfe3e6"], ["Ouro velho", "#9a7b3c"], ["Ferrugem", "#8a4b2a"],
               ["Verde militar", "#4b5a36"], ["Verde musgo", "#5f6b3a"], ["Azul marinho", "#1f2f55"], ["Vermelho", "#b3261e"],
               ["Amarelo", "#e8b923"], ["Martelado prata", "#9ea3a8"]]},
    {"chave": "corano", "nome": "Corano (couro sintético)", "prefixo": "corano", "canva": "assento estofado em corano (couro sintético) na cor {n}",
     "itens": [["Preto", "#1d1d1d"], ["Branco", "#f2f0ea"], ["Creme", "#e8d9a8"], ["Caramelo", "#d9a35a"], ["Fendi", "#a99a8a"],
               ["Marrom café", "#4e3428"], ["Amarelo", "#f2c500"], ["Laranja", "#f08a24"], ["Vermelho", "#d62a2a"],
               ["Rosa pink", "#e0417a"], ["Lilás", "#b9a6d6"], ["Roxo", "#6b3fa0"], ["Azul royal", "#2d4fb0"],
               ["Azul turquesa", "#1fb5c9"], ["Verde água", "#a7e3cf"], ["Verde", "#6aa53a"], ["Cinza claro", "#c9c9c9"],
               ["Cinza escuro", "#6e6e6e"],
               ["Off white", "#ece6da"], ["Nude", "#d9b8a0"], ["Areia", "#cbb79a"], ["Conhaque", "#9a5a2c"], ["Tabaco", "#6f4a2e"],
               ["Bordô", "#6d1a24"], ["Rosa bebê", "#f2c4cf"], ["Azul bebê", "#a9c8e8"], ["Azul marinho", "#1e2a4a"],
               ["Verde musgo", "#5d6b3a"], ["Verde bandeira", "#1f7a3a"], ["Mostarda", "#c9a227"], ["Telha", "#b5532e"],
               ["Grafite", "#3c3f44"], ["Dourado", "#c9a24a"], ["Prata", "#b9bcc0"]]},
    {"chave": "tecido", "nome": "Tecido (suede, linho, veludo...)", "prefixo": "tecido",
     "canva": "assento estofado em tecido {n}",
     "itens": [["Suede cinza", "#8d8f93"], ["Suede bege", "#cdb79a"], ["Suede marrom", "#6b4a33"], ["Suede azul", "#3d5a80"],
               ["Suede verde", "#5f7a4a"], ["Suede terracota", "#b5603c"], ["Linho cru", "#d9cfbd"], ["Linho cinza", "#a6a39c"],
               ["Linho areia", "#cbb998"], ["Veludo verde", "#2f5d46"], ["Veludo azul", "#28406b"], ["Veludo rosa", "#d79aa6"],
               ["Veludo mostarda", "#c29a2e"], ["Veludo cinza", "#7d7f84"], ["Bouclê off white", "#efe9dd"],
               ["Bouclê cinza", "#bdb9b2"], ["Chenille bege", "#cbb69a"], ["Chenille cinza", "#8f8f8f"],
               ["Jacquard cinza", "#9a9a9a"], ["Sarja preta", "#222222"], ["Impermeável cinza", "#7a7c80"]]},
    {"chave": "madeira", "nome": "Madeira / verniz", "prefixo": "madeira", "canva": "madeira no tom {n}",
     "itens": [["Natural", "#d8b27a"], ["Mel", "#c58b46"], ["Castanho", "#7a4b2a"], ["Imbuia", "#5b3a24"], ["Preto", "#222222"],
               ["Freijó", "#b98a57"], ["Carvalho", "#b48a5a"], ["Nogueira", "#6e4a30"], ["Cumaru", "#9a6a3c"],
               ["Rústico", "#8a6440"], ["Branco", "#efece6"]]},
]}


def _chaves_padrao_cores():
    return sorted({f"{g['chave']}:{n}" for g in CORES_PADRAO["grupos"] for n, _ in g["itens"]} |
                  {f"{g['chave']}:" for g in CORES_PADRAO["grupos"]})


def ler_cores():
    """Suas cores. Cores e grupos novos de uma versão nova entram sozinhos (as que você apagou não voltam)."""
    try:
        d = json.loads(ARQ_CORES.read_text(encoding="utf-8"))
        if isinstance(d, dict) and isinstance(d.get("grupos"), list):
            vistos = d.get("padrao_visto")
            if vistos is None:
                vistos = {f"{g.get('chave')}:{n}" for g in d["grupos"] for n, *_ in g.get("itens", [])} | \
                         {f"{g.get('chave')}:" for g in d["grupos"]}
            vistos = set(vistos)
            for gp in CORES_PADRAO["grupos"]:
                g = next((x for x in d["grupos"] if x.get("chave") == gp["chave"]), None)
                if g is None:
                    if f"{gp['chave']}:" in vistos:
                        continue
                    g = json.loads(json.dumps({**gp, "itens": []}))
                    pos = next((i for i, x in enumerate(d["grupos"]) if x.get("chave") == "madeira"), len(d["grupos"]))
                    d["grupos"].insert(pos, g)
                nomes = {str(n).lower() for n, *_ in g.get("itens", [])}
                for n, h in gp["itens"]:
                    if f"{gp['chave']}:{n}" not in vistos and n.lower() not in nomes:
                        g.setdefault("itens", []).append([n, h])
            return d
    except Exception:
        pass
    return json.loads(json.dumps(CORES_PADRAO))


def salvar_cores(d):
    d = dict(d)
    d["padrao_visto"] = _chaves_padrao_cores()
    tmp = ARQ_CORES.with_suffix(".tmp")
    tmp.write_text(json.dumps(d, ensure_ascii=False, indent=2), encoding="utf-8")
    os.replace(tmp, ARQ_CORES)


# ------------------------------------------------------------------ tabela de materiais e horas (custo)
# Fica em materiais.json, na pasta do robô (é do seu PC: a atualização não mexe nele).
# Os preços que vêm prontos são CHUTES pela internet (nov/2026): "est": true até você mudar o preço.

ARQ_MATERIAIS = motor.BASE / "materiais.json"
UNIDADES = ["m", "m²", "kg", "un", "h", "L", "barra 6 m", "pacote"]


def _tubos():
    itens = []
    for parede, chapa, precos in (("1,20 mm (chapa 18)", "18", {"5/8\"": 38, "3/4\"": 45, "7/8\"": 52, "1\"": 58, "1 1/4\"": 75, "1 1/2\"": 90}),
                                  ("0,90 mm (chapa 20)", "20", {"5/8\"": 30, "3/4\"": 35, "7/8\"": 40, "1\"": 45, "1 1/4\"": 58, "1 1/2\"": 70})):
        for bit, barra in precos.items():
            itens.append(["Tubos redondos", f"Tubo redondo {bit} parede {parede}", "m", round(barra / 6, 2), f"barra de 6 m ≈ R$ {barra},00"])
    for parede, precos in (("1,20 mm (chapa 18)", {"15×15": 35, "20×20": 45, "25×25": 55, "30×30": 65, "40×40": 88, "50×50": 110,
                                                   "30×20": 55, "40×20": 62, "50×30": 95}),
                           ("0,90 mm (chapa 20)", {"20×20": 36, "30×30": 52, "40×40": 70, "30×20": 44})):
        for bit, barra in precos.items():
            itens.append(["Tubos quadrados e retangulares (metalon)", f"Metalon {bit} mm parede {parede}", "m", round(barra / 6, 2),
                          f"barra de 6 m ≈ R$ {barra},00"])
    for nome, barra in (("Barra chata 3/4\" × 1/8\"", 30), ("Barra chata 1\" × 1/8\"", 38), ("Barra chata 1 1/2\" × 1/8\" (lâmina de encosto)", 55),
                        ("Barra chata 2\" × 3/16\"", 85), ("Cantoneira 1\" × 1/8\"", 48), ("Vergalhão / barra redonda 3/8\"", 32)):
        itens.append(["Barras, chapas e cantoneiras", nome, "m", round(barra / 6, 2), f"barra de 6 m ≈ R$ {barra},00"])
    itens.append(["Barras, chapas e cantoneiras", "Chapa de aço 1,20 mm (chapa 18)", "m²", 75.0, ""])
    itens.append(["Barras, chapas e cantoneiras", "Chapa de aço 2,00 mm (chapa 14)", "m²", 120.0, ""])
    return itens


def _ponteiras():
    itens = []
    for bit, pi, pe in (("5/8\"", 0.45, 0.70), ("3/4\"", 0.50, 0.80), ("7/8\"", 0.55, 0.90), ("1\"", 0.60, 1.00),
                        ("1 1/4\"", 0.90, 1.40), ("1 1/2\"", 1.20, 1.80)):
        itens.append(["Ponteiras", f"Ponteira interna tubo redondo {bit}", "un", pi, "vai dentro do tubo"])
        itens.append(["Ponteiras", f"Ponteira externa tubo redondo {bit}", "un", pe, "veste o tubo por fora"])
    for bit, pi, pe in (("15×15", 0.40, 0.70), ("20×20", 0.50, 0.80), ("25×25", 0.60, 0.95), ("30×30", 0.70, 1.10),
                        ("40×40", 1.00, 1.60), ("50×50", 1.40, 2.20), ("30×20", 0.65, 1.00), ("40×20", 0.80, 1.20),
                        ("50×30", 1.10, 1.70)):
        itens.append(["Ponteiras", f"Ponteira interna metalon {bit} mm", "un", pi, "vai dentro do tubo"])
        itens.append(["Ponteiras", f"Ponteira externa metalon {bit} mm", "un", pe, "veste o tubo por fora"])
    itens.append(["Ponteiras", "Ponteira de silicone (antirrisco)", "un", 1.20, ""])
    itens.append(["Ponteiras", "Deslizador de feltro adesivo", "un", 0.40, ""])
    return itens


PES_E_RODIZIOS = [
    ["Pés niveladores e sapatas", "Pé nivelador rosca 1/4\" (base 25 mm)", "un", 2.50, ""],
    ["Pés niveladores e sapatas", "Pé nivelador rosca 1/4\" (base 40 mm)", "un", 3.20, ""],
    ["Pés niveladores e sapatas", "Pé nivelador rosca 5/16\" (base 30 mm)", "un", 3.00, ""],
    ["Pés niveladores e sapatas", "Pé nivelador rosca 5/16\" (base 50 mm)", "un", 4.20, ""],
    ["Pés niveladores e sapatas", "Pé nivelador rosca 3/8\" (base 50 mm)", "un", 4.80, ""],
    ["Pés niveladores e sapatas", "Sapata roscada 1/4\" para tubo redondo (tampão com rosca)", "un", 1.20, "o nivelador rosqueia nela"],
    ["Pés niveladores e sapatas", "Sapata roscada 5/16\" para tubo redondo (tampão com rosca)", "un", 1.40, ""],
    ["Pés niveladores e sapatas", "Sapata roscada 1/4\" para metalon (tampão com rosca)", "un", 1.30, ""],
    ["Pés niveladores e sapatas", "Sapata roscada 5/16\" para metalon (tampão com rosca)", "un", 1.50, ""],
    ["Pés niveladores e sapatas", "Porca garra / rebite-porca 1/4\"", "un", 0.50, ""],
    ["Pés niveladores e sapatas", "Porca garra / rebite-porca 5/16\"", "un", 0.60, ""],
    ["Rodízios", "Rodízio de plástico 35 mm sem trava", "un", 3.50, ""],
    ["Rodízios", "Rodízio de plástico 35 mm com trava", "un", 5.50, ""],
    ["Rodízios", "Rodízio de nylon 50 mm sem trava", "un", 8.00, ""],
    ["Rodízios", "Rodízio de nylon 50 mm com trava", "un", 11.00, ""],
    ["Rodízios", "Rodízio de silicone (gel) 50 mm com trava", "un", 14.00, "não risca o piso"],
    ["Rodízios", "Rodízio de nylon 75 mm com trava", "un", 18.00, ""],
]


MATERIAIS_PADRAO = _tubos() + _ponteiras() + PES_E_RODIZIOS + [
    ["Madeira", "Compensado 15 mm", "m²", 70.0, "assento redondo de 33 cm ≈ 0,1 m²"],
    ["Madeira", "Compensado 10 mm", "m²", 52.0, ""],
    ["Madeira", "MDF 15 mm", "m²", 55.0, ""],
    ["Madeira", "MDF 6 mm", "m²", 28.0, ""],
    ["Madeira", "Tábua de pinus 30 cm × 2,5 cm", "m", 22.0, ""],
    ["Madeira", "Ripa de madeira maciça 5 × 2 cm (cumaru/itaúba)", "m", 18.0, ""],
    ["Madeira", "Ripa de eucalipto 5 × 2 cm", "m", 8.0, ""],
    ["Madeira", "Tábua de madeira maciça 2 cm (tábua de churrasco)", "m²", 160.0, ""],
    ["Madeira", "Verniz / seladora (900 ml)", "un", 45.0, ""],
    ["Espuma", "Espuma D23 5 cm", "m²", 45.0, "placa 1 × 1 m"],
    ["Espuma", "Espuma D28 3 cm", "m²", 38.0, ""],
    ["Espuma", "Espuma D28 5 cm", "m²", 60.0, "a mais usada em assento"],
    ["Espuma", "Espuma D33 5 cm", "m²", 75.0, ""],
    ["Espuma", "Espuma D33 10 cm", "m²", 140.0, ""],
    ["Tecido e estofado", "Corano (courino) largura 1,40 m", "m", 32.0, "1 m dá uns 12 assentos de 33 cm"],
    ["Tecido e estofado", "Corano náutico (área externa, sol e chuva) 1,40 m", "m", 48.0, "não racha no sol; bom para varanda e piscina"],
    ["Tecido e estofado", "Couro ecológico premium 1,40 m", "m", 58.0, "mais grosso e macio que o corano comum"],
    ["Tecido e estofado", "Corano matelassê 1,40 m", "m", 52.0, "já vem costurado em gomos"],
    ["Tecido e estofado", "Suede largura 1,40 m", "m", 35.0, ""],
    ["Tecido e estofado", "Suede amassado 1,40 m", "m", 38.0, ""],
    ["Tecido e estofado", "Veludo 1,40 m", "m", 48.0, "brilho bonito em foto; marca com o uso"],
    ["Tecido e estofado", "Linho largura 1,40 m", "m", 45.0, ""],
    ["Tecido e estofado", "Linhão (linho misto) 1,40 m", "m", 32.0, "visual de linho, mais barato"],
    ["Tecido e estofado", "Bouclê 1,40 m", "m", 65.0, "tendência; peças de valor maior"],
    ["Tecido e estofado", "Chenille 1,40 m", "m", 42.0, ""],
    ["Tecido e estofado", "Jacquard 1,40 m", "m", 40.0, "estampado em relevo"],
    ["Tecido e estofado", "Sarja 1,40 m", "m", 28.0, ""],
    ["Tecido e estofado", "Tecido impermeável (tipo Acquablock) 1,40 m", "m", 58.0, "não mancha com líquido"],
    ["Tecido e estofado", "Tela sling (área externa) 1,40 m", "m", 55.0, "cadeira de piscina, sem espuma"],
    ["Tecido e estofado", "Oxford 1,50 m", "m", 12.0, "forro"],
    ["Tecido e estofado", "Algodão cru 1,50 m", "m", 14.0, "forro por baixo do tecido"],
    ["Tecido e estofado", "Linha de costura para estofado (cone)", "un", 18.0, ""],
    ["Tecido e estofado", "Botão forrado para capitonê", "un", 0.8, ""],
    ["Tecido e estofado", "Manta acrílica", "m", 8.0, ""],
    ["Tecido e estofado", "TNT (fundo do assento)", "m", 2.5, ""],
    ["Tecido e estofado", "Grampos (caixa)", "un", 12.0, ""],
    ["Tecido e estofado", "Cola de contato 750 ml", "un", 38.0, ""],
    ["Tecido e estofado", "Corda náutica 6 mm", "m", 3.5, ""],
    ["Pintura", "Tinta pó eletrostática", "kg", 45.0, "1 kg pinta uns 8 a 10 m²"],
    ["Pintura", "Pintura eletrostática terceirizada (por peça)", "un", 25.0, ""],
    ["Pintura", "Fundo / primer 900 ml", "un", 32.0, ""],
    ["Pintura", "Esmalte sintético 900 ml", "un", 42.0, ""],
    ["Pintura", "Esmalte sintético galão 3,6 L", "un", 145.0, "pinta uns 40 m² por demão"],
    ["Pintura", "Fundo antiferrugem (zarcão) 900 ml", "un", 38.0, ""],
    ["Pintura", "Thinner 1 L", "L", 22.0, ""],
    ["Pintura", "Lixa d'água / ferro (folha)", "un", 2.5, ""],
    ["Pintura", "Desengraxante / desengordurante 1 L", "L", 18.0, "limpar o ferro antes de pintar"],
    ["Tinta spray", "Spray uso geral 400 ml (cores lisas)", "un", 22.0, "1 lata cobre uns 1,5 a 2 m² em 2 demãos"],
    ["Tinta spray", "Spray preto fosco 400 ml", "un", 22.0, "a mais usada na ferragem"],
    ["Tinta spray", "Spray preto brilhante 400 ml", "un", 22.0, ""],
    ["Tinta spray", "Spray preto acetinado (semibrilho) 400 ml", "un", 24.0, ""],
    ["Tinta spray", "Spray branco fosco 400 ml", "un", 22.0, ""],
    ["Tinta spray", "Spray branco brilhante 400 ml", "un", 22.0, ""],
    ["Tinta spray", "Spray cinza / grafite 400 ml", "un", 23.0, ""],
    ["Tinta spray", "Spray bege / areia 400 ml", "un", 24.0, ""],
    ["Tinta spray", "Spray colorido (vermelho, azul, verde, amarelo) 400 ml", "un", 23.0, ""],
    ["Tinta spray", "Spray metálico prata / alumínio 400 ml", "un", 28.0, ""],
    ["Tinta spray", "Spray metálico dourado 400 ml", "un", 32.0, ""],
    ["Tinta spray", "Spray metálico cobre / bronze / rose 400 ml", "un", 32.0, ""],
    ["Tinta spray", "Spray cromado (efeito espelho) 400 ml", "un", 38.0, ""],
    ["Tinta spray", "Spray efeito martelado 400 ml", "un", 34.0, ""],
    ["Tinta spray", "Spray efeito envelhecido / ferrugem 400 ml", "un", 36.0, "visual rústico/industrial"],
    ["Tinta spray", "Spray texturizado 400 ml", "un", 34.0, "esconde marca de solda"],
    ["Tinta spray", "Spray alta temperatura (até 600 °C) 400 ml", "un", 36.0, "churrasqueira, braseiro"],
    ["Tinta spray", "Spray primer / fundo cinza 400 ml", "un", 24.0, "passar antes da cor"],
    ["Tinta spray", "Spray fundo antiferrugem (zarcão) 400 ml", "un", 26.0, ""],
    ["Tinta spray", "Spray verniz incolor fosco ou brilho 400 ml", "un", 26.0, "proteger madeira ou ferro"],
    ["Tinta spray", "Spray premium secagem rápida 400 ml", "un", 30.0, "cobre mais, menos latas por peça"],
    ["Solda e corte", "Arame MIG 0,8 mm", "kg", 38.0, ""],
    ["Solda e corte", "Eletrodo 6013", "kg", 32.0, ""],
    ["Solda e corte", "Disco de corte", "un", 4.0, ""],
    ["Solda e corte", "Disco flap (lixa)", "un", 9.0, ""],
    ["Solda e corte", "Gás CO2 / mistura (por peça, média)", "un", 3.0, ""],
    ["Acessórios", "Parafuso + bucha", "un", 0.3, ""],
    ["Acessórios", "Rebite", "un", 0.1, ""],
    ["Acessórios", "Base giratória (lazy susan) 30 cm", "un", 45.0, ""],
    ["Embalagem", "Papelão ondulado", "m²", 4.0, ""],
    ["Embalagem", "Plástico bolha", "m", 3.0, ""],
    ["Embalagem", "Fita adesiva (rolo)", "un", 6.0, ""],
    ["Mão de obra", "Hora de serralheria (corte e solda)", "h", 25.0, ""],
    ["Mão de obra", "Hora de estofado", "h", 25.0, ""],
    ["Mão de obra", "Hora de marcenaria", "h", 25.0, ""],
    ["Mão de obra", "Hora de pintura e acabamento", "h", 20.0, ""],
    ["Mão de obra", "Hora de embalagem", "h", 15.0, ""],
]


def materiais_padrao():
    return [{"cat": c, "nome": n, "unid": u, "preco": p, "nota": nt, "est": True} for c, n, u, p, nt in MATERIAIS_PADRAO]


def _ler_local_materiais():
    """(itens, dados do arquivo) da cópia que fica no PC."""
    try:
        d = json.loads(ARQ_MATERIAIS.read_text(encoding="utf-8"))
        if isinstance(d, dict) and isinstance(d.get("itens"), list):
            itens = d["itens"]
            vistos = set(d.get("padrao_visto") or [i.get("nome") for i in itens])
            for novo in materiais_padrao():
                if novo["nome"] not in vistos:
                    pos = max((n for n, x in enumerate(itens) if x.get("cat") == novo["cat"]), default=len(itens) - 1) + 1
                    itens.insert(pos, novo)
            return itens, d
    except Exception:
        pass
    return materiais_padrao(), {}


def _gravar_local_materiais(itens, **extra):
    vistos = sorted({i["nome"] for i in materiais_padrao()} | {i.get("nome") for i in itens if i.get("nome")})
    tmp = ARQ_MATERIAIS.with_suffix(".tmp")
    tmp.write_text(json.dumps({"itens": itens, "padrao_visto": vistos, **extra}, ensure_ascii=False, indent=2), encoding="utf-8")
    os.replace(tmp, ARQ_MATERIAIS)


def _site_materiais(itens=None):
    """Tabela do site (Orçamentos › Materiais e horas). None = robô não conectado ou site sem essa parte.
    Com itens: manda a tabela do robô para o site e devolve como ficou lá."""
    conta = motor.ler_conta()
    if not conta or not conta.get("site"):
        return None
    try:
        n = motor.Nuvem(conta["site"], motor.BASE)
        if itens is None:
            r = n._req("GET", "/materiais", tempo=8)
        else:
            limpos = [{k: i.get(k) for k in ("id", "cat", "nome", "unid", "preco", "nota", "est")} for i in itens if i.get("nome")]
            r = n._req("POST", "/materiais", campos={"itens": json.dumps(limpos, ensure_ascii=False)}, tempo=25)
        lista = r.get("itens")
        return lista if isinstance(lista, list) else None
    except Exception:
        return None


_CACHE_MAT = {"t": 0.0, "itens": None}


def ler_materiais():
    """Sua tabela. Com o robô conectado ao site, a tabela é UMA SÓ: a do site (a mesma dos orçamentos).
    Sem internet, usa a cópia do PC. Itens novos de uma versão nova do robô entram sozinhos."""
    import time
    if _CACHE_MAT["itens"] is not None and time.time() - _CACHE_MAT["t"] < 60:
        return [dict(i) for i in _CACHE_MAT["itens"]]
    locais, d = _ler_local_materiais()
    if motor.ler_conta():
        # Primeira vez (ou mudança que não subiu): os seus preços do PC vão para o site.
        mexidos = any(not i.get("est", True) for i in locais)
        if d.get("pendente") or (not d.get("site_ok") and mexidos):
            site = _site_materiais(locais)
        else:
            site = _site_materiais()
        if site is not None:
            _gravar_local_materiais(site, site_ok=True)
            _CACHE_MAT.update(t=time.time(), itens=site)
            return [dict(i) for i in site]
    return locais


def salvar_materiais(itens):
    """Grava no PC e, se o robô estiver conectado, no site também (fica igual nos orçamentos)."""
    _CACHE_MAT["itens"] = None
    _gravar_local_materiais(itens, site_ok=True, pendente=bool(motor.ler_conta()))
    if motor.ler_conta():
        site = _site_materiais(itens)
        if site is not None:
            _gravar_local_materiais(site, site_ok=True)
            itens[:] = site


def texto_materiais(itens=None):
    """A tabela em texto, para a IA usar na ficha_de_custo."""
    itens = itens if itens is not None else ler_materiais()
    linhas, cat = [], None
    for i in itens:
        if i.get("cat") != cat:
            cat = i.get("cat")
            linhas.append(f"\n{cat}:")
        preco = f"{float(i.get('preco') or 0):.2f}".replace(".", ",")
        linhas.append(f"- {i['nome']}: R$ {preco} por {i.get('unid') or 'un'}")
    return "\n".join(linhas).strip()


def hex_da_cor(chave, nome, dados=None):
    g = next((g for g in (dados or ler_cores())["grupos"] if g["chave"] == chave), None)
    return next((h for n, h in (g or {}).get("itens", []) if n.lower() == str(nome).lower()), None)


def cores_do_nome(texto, dados=None):
    """Lê "Preto com corano Verde e madeira Mel" (ou só "Preto", "Corano Verde") -> {"ferragem": "Preto", "corano": "Verde", ...}.

    Só reconhece nomes que estão cadastrados; o que não reconhece fica de fora.
    """
    dados = dados or ler_cores()
    txt = str(texto or "").strip()
    if not txt:
        return {}
    grupos = dados["grupos"]
    pedacos = re.split(r"\s+com\s+|\s+e\s+(?=(?:%s)\b)" % "|".join(re.escape(g["prefixo"]) for g in grupos if g.get("prefixo")), txt,
                       flags=re.I)
    saida = {}
    for i, ped in enumerate(pedacos):
        ped = ped.strip()
        for g in grupos:
            pre = (g.get("prefixo") or "").lower()
            nome = ped
            if pre:
                if not ped.lower().startswith(pre + " "):
                    continue
                nome = ped[len(pre) + 1:].strip()
            elif i > 0 and any(ped.lower().startswith((x.get("prefixo") or "~").lower() + " ") for x in grupos):
                continue
            achou = next((n for n, _ in g["itens"] if n.lower() == nome.lower()), None)
            if achou and g["chave"] not in saida:
                saida[g["chave"]] = achou
                break
    return saida


def combinar_cores(escolhas):
    """escolhas: [(grupo, [nomes])] na ordem dos grupos. Devolve [(nome da variação, texto para o Canva)].

    Ex.: ferragem Preto + corano Verde -> ("Preto com corano Verde", "estrutura … Preto; assento … Verde").
    """
    import itertools
    listas = [[(g, n) for n in nomes] for g, nomes in escolhas if nomes]
    saida = []
    for combo in itertools.product(*listas) if listas else []:
        partes = [(f"{g.get('prefixo') or ''} {n}".strip()) for g, n in combo]
        nome = partes[0][:1].upper() + partes[0][1:]
        if len(partes) > 1:
            nome += " com " + " e ".join(partes[1:])
        canva = "; ".join((g.get("canva") or "{n}").replace("{n}", n) for g, n in combo)
        saida.append((nome.replace(",", " "), canva[:1].upper() + canva[1:] + "."))
    return saida


ESTILOS_IMAGEM = [
    ("capa", "Capa (fundo branco)",
     "Foto de CAPA do anúncio: o produto inteiro, de frente e levemente de cima, centralizado e ocupando cerca de 85% da imagem; "
     "fundo 100% branco puro (#FFFFFF) em toda a imagem, luz de estúdio suave e uniforme, sombra leve e natural só embaixo do produto. "
     "Nada além do produto: sem texto, sem logo, sem selo, sem moldura, sem objetos de decoração."),
    ("angulo", "Fundo branco em ângulo",
     "Foto em fundo 100% branco (#FFFFFF), produto inteiro girado em ângulo de 3/4 (cerca de 45 graus), mostrando a frente, "
     "a lateral e a espessura real da peça. Luz de estúdio suave, sombra leve embaixo. Sem texto e sem outros objetos."),
    ("ambiente", "No ambiente",
     "Foto lifestyle num ambiente real e bonito de casa brasileira onde esse produto é usado (cozinha, área gourmet, sala, varanda "
     "ou quarto, o que fizer mais sentido para ele). Luz natural de janela, cores quentes, decoração discreta e desfocada ao fundo. "
     "O produto é o protagonista, nítido, em primeiro plano e em escala real coerente com as medidas. Sem texto."),
    ("uso", "Em uso",
     "Foto do produto sendo usado do jeito que ele foi feito para ser usado, com os itens que ele serve, segura ou apoia "
     "(por exemplo comida, plantas, livros ou objetos), bem arrumados e apetitosos. Ambiente de casa, luz natural, "
     "pessoas só se aparecerem as mãos. Sem texto."),
    ("medidas", "Com as medidas",
     "Foto técnica em fundo 100% branco: o produto inteiro com linhas de cota finas cinza-escuro e as medidas escritas em "
     "centímetros ({medidas}), em fonte sem serifa limpa e legível, cada medida na direção certa. "
     "O único texto da imagem são essas medidas."),
    ("detalhe", "Detalhe do acabamento",
     "Foto macro em close do acabamento: textura e veios da madeira, a gravação, as bordas arredondadas, a solda e a pintura, "
     "com fundo bem desfocado e luz lateral suave que realça a textura. Mostra a qualidade do feito à mão. Sem texto."),
    ("kit", "Kit (todas as unidades)",
     "Foto em fundo 100% branco mostrando TODAS as unidades do kit juntas ({kit}), iguais entre si, bem organizadas "
     "(lado a lado ou levemente empilhadas), para o cliente ver exatamente quantas peças recebe. Sem texto."),
    ("escala", "Tamanho real",
     "Foto que deixa claro o tamanho real do produto: ele numa bancada ou mesa ao lado de objetos comuns do dia a dia "
     "(uma caneca, um celular ou uma mão adulta), tudo em escala correta para {medidas}. Luz natural. Sem texto."),
    ("presente", "Para presente",
     "Foto do produto como presente: sobre uma mesa clara de madeira, com papel kraft, fita e um pequeno cartão em branco ao lado, "
     "clima aconchegante, luz natural suave. O produto aparece inteiro e é o destaque. Sem texto escrito."),
]
ESTILOS_PADRAO = ("capa", "angulo", "ambiente", "uso", "medidas")


def medidas_texto(v):
    m = v.get("medidas_peca_cm") if isinstance(v, dict) and isinstance(v.get("medidas_peca_cm"), dict) else {}
    partes = []
    for k, rot in (("altura", "altura"), ("largura", "largura"), ("profundidade", "profundidade"), ("diametro", "diâmetro")):
        n = motor.numero(m.get(k))
        if n:
            partes.append(f"{rot} {str(round(n, 1)).replace('.0', '').replace('.', ',')} cm")
    return ", ".join(partes)


def kit_da_variacao(v):
    """Quantas unidades a variação tem (campo "kit" ou "Kit com N" no nome)."""
    if not isinstance(v, dict):
        return 1
    n = motor.numero(v.get("kit"))
    if not n:
        m = re.search(r"(?i)kit\D{0,8}(\d+)", str(v.get("valor") or ""))
        n = int(m.group(1)) if m else 1
    return max(int(n or 1), 1)


def prompt_imagem(d, estilo, variacao=None, extra="", ref=None):
    """O pedido completo que vai para a IA (Canva ou Gemini) junto com as fotos do produto.

    variacao = a opção para a qual a imagem é feita; ref = a opção que aparece nas fotos (para avisar o que muda)."""
    v = variacao or next((x for x in d.get("variacoes") or [] if isinstance(x, dict)), {}) or {}
    medidas = medidas_texto(v)
    kit = kit_da_variacao(v)
    kit_txt = f"kit com {kit} unidades" if kit > 1 else "1 unidade"
    texto = dict((k, t) for k, _, t in ESTILOS_IMAGEM)[estilo]
    texto = texto.replace("{medidas}", medidas or "as medidas da peça").replace("{kit}", kit_txt)
    nome = d.get("nome") or "produto"

    prod = [f"- Produto: {nome}"]
    if v.get("valor"):
        prod.append(f"- Opção desta imagem: {v.get('valor')}")
    if medidas:
        prod.append(f"- Medidas reais de UMA peça: {medidas}")
    for k, rot in (("material", "Material"), ("acabamento", "Acabamento"), ("uso", "Para que serve")):
        if d.get(k):
            prod.append(f"- {rot}: {d.get(k)}")
    for item in (d.get("ficha_tecnica") or [])[:12]:
        if isinstance(item, dict) and item.get("nome") and item.get("valor") and str(item["nome"]).lower() not in (
                "marca", "material", "uso", "pode ir à lava-louças", "requer montagem") and " ou " not in str(item["valor"]):
            prod.append(f"- {item['nome']}: {item['valor']}")
    prod.append(f"- Quantidade: {kit_txt}")

    muda = []
    ref = ref if isinstance(ref, dict) else None
    if ref is not None and ref is not v and (ref.get("valor") or "") != (v.get("valor") or ""):
        muda.append(f"As fotos enviadas mostram a opção “{ref.get('valor') or 'original'}”. Esta imagem é da opção “{v.get('valor')}”.")
        mr, mv = medidas_texto(ref), medidas_texto(v)
        if mr and mv and mr != mv:
            muda.append(f"O tamanho muda: nas fotos a peça tem {mr}; nesta opção ela tem {mv}. Mude só a escala, mantendo as "
                        "mesmas proporções, a mesma espessura e os mesmos detalhes.")
    if kit > 1:
        if estilo in ("capa", "kit"):
            muda.append(f"Esta opção é um kit: mostre as {kit} unidades iguais juntas na imagem.")
        else:
            muda.append(f"Esta opção é um kit com {kit} unidades; nesta imagem pode aparecer 1 ou todas, o que ficar mais claro.")
    if v.get("canva_extra"):
        muda.append(str(v.get("canva_extra")).strip())
    if extra and extra.strip() and extra.strip() != str(v.get("canva_extra") or "").strip():
        muda.append(extra.strip())

    partes = [
        "Crie UMA imagem para o anúncio no Mercado Livre de um produto real da Móveis Stylo, que faz peças de madeira e ferro à mão. "
        "As fotos que enviei são a referência exata do produto.",
        "TIPO DE IMAGEM\n" + texto,
        "PRODUTO\n" + "\n".join(prod),
    ]
    if muda:
        partes.append("O QUE MUDA EM RELAÇÃO ÀS FOTOS\n" + "\n".join(f"- {m}" for m in muda))
    partes.append(
        "REGRAS\n"
        "- Copie fielmente o produto das fotos: mesmo formato, cor, madeira e veios, ferro, espessura, bordas, divisórias, "
        "gravação e acabamento (só mude o que está em “O que muda”).\n"
        "- Não invente peças, pés, alças, gavetas, rodinhas, enfeites ou textos que o produto não tem.\n"
        "- Escala real e coerente com as medidas.\n"
        "- Aparência de foto profissional de catálogo, realista, nítida e bem iluminada, não de desenho ou 3D.\n"
        "- Imagem quadrada 1:1, alta resolução (pelo menos 1200 x 1200 px).\n"
        "- Sem marca d'água, sem logo de loja, sem preço, sem selo e sem bordas."
        + ("" if estilo == "medidas" else "\n- Sem nenhum texto na imagem."))
    return "\n\n".join(partes)


def _foto_base64(caminho, lado=1024):
    import base64
    if Image is None:
        return "image/jpeg", base64.b64encode(Path(caminho).read_bytes()).decode()
    with Image.open(caminho) as im:
        im = ImageOps.exif_transpose(im).convert("RGB")
        im.thumbnail((lado, lado))
        buf = __import__("io").BytesIO()
        im.save(buf, "JPEG", quality=90)
    return "image/jpeg", base64.b64encode(buf.getvalue()).decode()


def gerar_imagem_ia(fotos, prompt, chave, modelo="gemini-3.1-flash-image", timeout=180):
    """Manda as fotos do produto e o pedido para o Gemini e devolve os bytes da imagem criada."""
    import base64
    if not chave:
        raise RuntimeError("Falta a chave do Gemini em Configurações.")
    partes = [{"text": prompt}]
    for f in list(fotos)[:6]:
        mime, dado = _foto_base64(f)
        partes.append({"inline_data": {"mime_type": mime, "data": dado}})
    corpo = json.dumps({"contents": [{"role": "user", "parts": partes}],
                        "generationConfig": {"responseModalities": ["TEXT", "IMAGE"]}}).encode("utf-8")
    ultimo = None
    for versao in ("v1beta", "v1"):
        url = f"https://generativelanguage.googleapis.com/{versao}/models/{modelo}:generateContent"
        req = urllib.request.Request(url, data=corpo, headers={"Content-Type": "application/json", "x-goog-api-key": chave})
        try:
            with urllib.request.urlopen(req, timeout=timeout) as r:
                resp = json.loads(r.read().decode("utf-8"))
            break
        except urllib.error.HTTPError as e:
            detalhe = e.read().decode("utf-8", "replace")[:300]
            if e.code == 404:
                ultimo = f"O modelo {modelo} não foi encontrado. Confira o nome em Configurações."
                continue
            if e.code in (401, 403) or (e.code == 400 and "API key" in detalhe):
                raise RuntimeError("A chave do Gemini não foi aceita. Confira em Configurações.") from None
            if e.code == 429:
                raise RuntimeError("O limite de imagens da sua conta do Gemini acabou por agora. Tente mais tarde.") from None
            raise RuntimeError(f"O Gemini respondeu com erro {e.code}: {detalhe}") from None
        except urllib.error.URLError:
            raise RuntimeError("Sem conexão com o Gemini. Confira a internet.") from None
    else:
        raise RuntimeError(ultimo or "O Gemini não respondeu.")
    for c in resp.get("candidates") or []:
        for p in (c.get("content") or {}).get("parts") or []:
            dado = (p.get("inlineData") or p.get("inline_data") or {}).get("data")
            if dado:
                return base64.b64decode(dado)
    motivo = ""
    try:
        motivo = resp["candidates"][0].get("finishReason") or ""
        motivo = motivo or resp["candidates"][0]["content"]["parts"][0].get("text", "")[:160]
    except Exception:
        motivo = (resp.get("promptFeedback") or {}).get("blockReason", "")
    raise RuntimeError("O Gemini não devolveu imagem" + (f" ({motivo})" if motivo else "") + ". Tente de novo ou mude o pedido.")


def quadrar_imagem(dado, destino, lado=1200):
    """Salva a imagem da IA quadrada (fundo branco nas sobras) em JPG."""
    destino = Path(destino)
    if Image is None:
        destino.write_bytes(dado)
        return destino
    with Image.open(__import__("io").BytesIO(dado)) as im:
        im = im.convert("RGB")
        im.thumbnail((lado, lado))
        fundo = Image.new("RGB", (lado, lado), "white")
        fundo.paste(im, ((lado - im.width) // 2, (lado - im.height) // 2))
        fundo.save(destino, "JPEG", quality=92)
    return destino


# ------------------------------------------------------------ fotos prontas

def _fonte(tamanho, negrito=False):
    nomes = (["arialbd.ttf", "segoeuib.ttf", "DejaVuSans-Bold.ttf"] if negrito else ["arial.ttf", "segoeui.ttf", "DejaVuSans.ttf"])
    for n in nomes:
        try:
            return ImageFont.truetype(n, tamanho)
        except OSError:
            continue
    try:
        return ImageFont.load_default(tamanho)
    except TypeError:
        return ImageFont.load_default()


def tratar_foto(origem, destino, quadrada=True, fundo_branco=False, tolerancia=40, realcar=False,
                marca="", lado=1200):
    """Foto pronta para anúncio: fundo claro vira branco, quadrada, realce leve e marca d'água."""
    with Image.open(origem) as im:
        im = ImageOps.exif_transpose(im)
        if im.mode in ("RGBA", "LA", "P"):
            im = im.convert("RGBA")
            fundo = Image.new("RGB", im.size, (255, 255, 255))
            fundo.paste(im, mask=im.split()[-1])
            im = fundo
        else:
            im = im.convert("RGB")
    if fundo_branco:
        w, h = im.size
        pontos = [(0, 0), (w - 1, 0), (0, h - 1), (w - 1, h - 1), (w // 2, 0), (w // 2, h - 1), (0, h // 2), (w - 1, h // 2)]
        for xy in pontos:
            px = im.getpixel(xy)
            if sum(px) >= 3 * 170 and px != (255, 255, 255):  # só fundo claro
                ImageDraw.floodfill(im, xy, (255, 255, 255), thresh=tolerancia)
    if realcar:
        from PIL import ImageEnhance
        im = ImageOps.autocontrast(im, cutoff=0.3, preserve_tone=True)
        im = ImageEnhance.Sharpness(im).enhance(1.25)
        im = ImageEnhance.Color(im).enhance(1.06)
    if quadrada:
        lado_img = max(im.size)
        quad = Image.new("RGB", (lado_img, lado_img), (255, 255, 255))
        quad.paste(im, ((lado_img - im.size[0]) // 2, (lado_img - im.size[1]) // 2))
        im = quad
    if lado and max(im.size) != lado:
        esc = lado / max(im.size)
        im = im.resize((max(1, round(im.size[0] * esc)), max(1, round(im.size[1] * esc))), Image.LANCZOS)
    if marca:
        camada = Image.new("RGBA", im.size, (0, 0, 0, 0))
        d = ImageDraw.Draw(camada)
        fonte = _fonte(max(18, im.size[0] // 28), negrito=True)
        caixa = d.textbbox((0, 0), marca, font=fonte)
        tw, th = caixa[2] - caixa[0], caixa[3] - caixa[1]
        x, y = im.size[0] - tw - im.size[0] // 30, im.size[1] - th - im.size[1] // 30
        d.text((x, y), marca, font=fonte, fill=(40, 40, 40, 110))
        im = Image.alpha_composite(im.convert("RGBA"), camada).convert("RGB")
    destino = Path(destino).with_suffix(".jpg")
    im.save(destino, "JPEG", quality=90, optimize=True)
    return destino


# ------------------------------------------------------------ lote

CAMPOS_LOTE = {"Preço de venda": "venda", "Custo": "custo", "Preço “de”": "preco_tabela", "Estoque": "estoque",
               "Prazo (dias)": "prazo_dias"}
OPERACOES = ["Aumentar %", "Diminuir %", "Somar R$ / qtd", "Tirar R$ / qtd", "Definir valor"]


def _aplicar(atual, op, valor):
    a = motor.numero(atual) if atual not in (None, "") else 0.0
    if op == "Aumentar %":
        return a * (1 + valor / 100)
    if op == "Diminuir %":
        return a * (1 - valor / 100)
    if op == "Somar R$ / qtd":
        return a + valor
    if op == "Tirar R$ / qtd":
        return max(a - valor, 0)
    return valor


def atualizar_lote(fichas, campo, op, valor, redondo=False):
    """Muda um campo em todas as variações das fichas. Devolve quantas variações mudaram."""
    n = 0
    for f in fichas:
        d = json.loads(json.dumps(f.dados))
        if campo == "prazo_dias":
            d["prazo_dias"] = int(round(_aplicar(d.get("prazo_dias"), op, valor)))
            n += 1
        else:
            for v in d.get("variacoes") or []:
                if not isinstance(v, dict):
                    continue
                if op in ("Aumentar %", "Diminuir %", "Somar R$ / qtd", "Tirar R$ / qtd") and v.get(campo) in (None, ""):
                    continue
                novo = _aplicar(v.get(campo), op, valor)
                if campo == "estoque":
                    v[campo] = int(round(max(novo, 0)))
                else:
                    novo = round(max(novo, 0), 2)
                    v[campo] = arredondar_90(novo) if redondo and campo in ("venda", "preco_tabela") and novo > 0 else novo
                n += 1
        motor.salvar_ficha(f, d)
    return n


# ------------------------------------------------------------ kits

def criar_kits(ficha, pasta_produtos, quantidades, desconto_pct=0.0, redondo=True):
    """Uma ficha nova para cada kit (2, 4, 6...): preço, custo, peso e estoque multiplicados."""
    pastas = []
    for n in quantidades:
        n = int(n)
        if n < 2:
            continue
        d = json.loads(json.dumps(ficha.dados))
        d["nome"] = f"Kit {n} {ficha.nome}"
        if str(d.get("codigo") or "").strip():
            d["codigo"] = f"{d['codigo']}-K{n}"
        caixa = str(d.get("o_que_vem_na_caixa") or "").strip()
        d["o_que_vem_na_caixa"] = f"{n} unidades" + (f" ({caixa} cada)" if caixa else "")
        for v in d.get("variacoes") or []:
            if not isinstance(v, dict):
                continue
            if str(v.get("codigo") or "").strip():
                v["codigo"] = f"{v['codigo']}-K{n}"
            if v.get("venda") not in (None, ""):
                bruto = motor.numero(v["venda"]) * n * (1 - (desconto_pct or 0) / 100)
                v["venda"] = arredondar_90(bruto) if redondo else round(bruto, 2)
            if v.get("custo") not in (None, ""):
                v["custo"] = round(motor.numero(v["custo"]) * n, 2)
            if v.get("preco_tabela") not in (None, ""):
                v["preco_tabela"] = round(motor.numero(v["preco_tabela"]) * n, 2)
            v["estoque"] = int(motor.numero(v.get("estoque"))) // n
            emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
            if emb.get("peso_kg"):
                emb["peso_kg"] = round(motor.numero(emb["peso_kg"]) * n, 3)
                emb["_conferir_caixa"] = True
            v["embalagem"] = {k: x for k, x in emb.items() if not k.startswith("_")}
        if isinstance(d.get("ficha_de_custo"), list):
            d["ficha_de_custo"] = [dict(i, quantidade=(motor.numero(i.get("quantidade")) or 1) * n)
                                   for i in d["ficha_de_custo"] if isinstance(i, dict)]
        ml = d.get("mercado_livre") if isinstance(d.get("mercado_livre"), dict) else {}
        if ml.get("titulo_anuncio"):
            t = f"Kit {n} {ml['titulo_anuncio']}"
            ml["titulo_anuncio"] = t if len(t) <= 60 else t[:60].rsplit(" ", 1)[0]
        ml["link_referencia"] = ml.get("link_referencia")
        d["mercado_livre"] = ml
        destino = motor.nova_pasta(pasta_produtos, d)
        for p in ficha.todas_fotos:
            if p.exists():
                shutil.copy2(p, destino / p.name)
        (destino / "ficha.md").write_text(motor.texto_da_ficha(d), encoding="utf-8")
        pastas.append(destino)
    return pastas


# ------------------------------------------------------------ etiquetas

_C128 = ("212222 222122 222221 121223 121322 131222 122213 122312 132212 221213 221312 231212 112232 122132 122231 "
         "113222 123122 123221 223211 221132 221231 213212 223112 312131 311222 321122 321221 312212 322112 322211 "
         "212123 212321 232121 111323 131123 131321 112313 132113 132311 211313 231113 231311 112133 112331 132131 "
         "113123 113321 133121 313121 211331 231131 213113 213311 213131 311123 311321 331121 312113 312311 332111 "
         "314111 221411 431111 111224 111422 121124 121421 141122 141221 112214 112412 122114 122411 142112 142211 "
         "241211 221114 413111 241112 134111 111242 121142 121241 114212 124112 124211 411212 421112 421211 212141 "
         "214121 412121 111143 111341 131141 114113 114311 411113 411311 113141 114131 311141 411131 211412 211214 "
         "211232 2331112").split()


def _ascii(texto):
    t = unicodedata.normalize("NFKD", str(texto)).encode("ascii", "ignore").decode("ascii")
    return "".join(c for c in t if 32 <= ord(c) < 127)


def code128(texto):
    """Larguras das barras (Code 128 B) com início, verificação e fim."""
    t = _ascii(texto)
    valores = [104] + [ord(c) - 32 for c in t]
    soma = valores[0] + sum(i * v for i, v in enumerate(valores[1:], 1))
    valores.append(soma % 103)
    return "".join(_C128[v] for v in valores) + _C128[106]


def desenhar_barras(draw, x, y, larg_max, altura, texto):
    padrao = code128(texto)
    modulos = sum(int(c) for c in padrao) + 20  # 10 módulos de margem de cada lado
    m = max(1, int(larg_max // modulos))
    total = (modulos - 20) * m
    x0 = x + (larg_max - total) // 2
    preto = True
    for c in padrao:
        w = int(c) * m
        if preto:
            draw.rectangle((x0, y, x0 + w - 1, y + altura), fill=0)
        x0 += w
        preto = not preto


def _quebrar(draw, texto, fonte, larg, linhas=2):
    palavras, saida, atual = str(texto).split(), [], ""
    for p in palavras:
        teste = (atual + " " + p).strip()
        if draw.textlength(teste, font=fonte) <= larg:
            atual = teste
        else:
            if atual:
                saida.append(atual)
            atual = p
        if len(saida) == linhas:
            break
    if atual and len(saida) < linhas:
        saida.append(atual)
    if len(saida) == linhas and " ".join(saida) != " ".join(palavras):
        ult = saida[-1]
        while ult and draw.textlength(ult + "…", font=fonte) > larg:
            ult = ult[:-1]
        saida[-1] = ult.rstrip() + "…"
    return saida


def itens_etiqueta(fichas, por_estoque=True):
    itens = []
    for f in fichas:
        for v in f.variacoes:
            cod = str(v.get("codigo") or f.dados.get("codigo") or "").strip()
            qtd = int(motor.numero(v.get("estoque"))) if por_estoque else 1
            itens.append({"nome": f.nome, "variacao": str(v.get("valor") or "").strip(), "codigo": cod,
                          "medidas": _medida_txt(v), "preco": motor.numero(v.get("venda")), "qtd": max(qtd, 1 if not por_estoque else 0)})
    return [i for i in itens if i["qtd"] > 0]


def gerar_etiquetas(itens, destino, mostrar_preco=True, loja="Móveis Stylo"):
    """PDF A4 com 24 etiquetas por folha (3 × 8, 70 × 37 mm, padrão Pimaco A4356/A4256)."""
    dpi = 300
    W, H = 2480, 3508
    cols, rows = 3, 8
    lw, lh = W // cols, H // rows
    f_nome, f_var, f_peq, f_preco = _fonte(40, True), _fonte(34, True), _fonte(27), _fonte(44, True)
    paginas, pagina, n = [], None, 0
    etiquetas = [i for i in itens for _ in range(int(i.get("qtd") or 1))]
    for it in etiquetas:
        if n % (cols * rows) == 0:
            pagina = Image.new("L", (W, H), 255)
            paginas.append(pagina)
        d = ImageDraw.Draw(pagina)
        c, r = (n % (cols * rows)) % cols, (n % (cols * rows)) // cols
        x0, y0 = c * lw + 40, r * lh + 30
        larg = lw - 80
        y = y0
        for linha in _quebrar(d, it["nome"], f_nome, larg, 2):
            d.text((x0, y), linha, font=f_nome, fill=0)
            y += 46
        if it.get("variacao"):
            d.text((x0, y), it["variacao"], font=f_var, fill=0)
            y += 40
        if it.get("medidas"):
            d.text((x0, y), _quebrar(d, it["medidas"], f_peq, larg, 1)[0], font=f_peq, fill=0)
            y += 32
        base = r * lh + lh - 30
        if it.get("codigo"):
            alt = max(base - 50 - (y + 6), 50)
            desenhar_barras(d, x0, y + 6, larg, alt, it["codigo"])
            d.text((x0, base - 34), it["codigo"], font=f_var, fill=0)
        else:
            d.text((x0, base - 30), loja, font=f_peq, fill=90)
        if mostrar_preco and it.get("preco"):
            txt = "R$ " + f"{it['preco']:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")
            d.text((x0 + larg - d.textlength(txt, font=f_preco), base - 42), txt, font=f_preco, fill=0)
        n += 1
    if not paginas:
        raise ValueError("Nenhuma etiqueta para imprimir (estoque 0?). Desmarque “uma por peça em estoque”.")
    destino = Path(destino)
    paginas[0].save(destino, "PDF", resolution=dpi, save_all=True, append_images=paginas[1:])
    return destino, n


# ------------------------------------------------------------ completar a ficha sozinho

_PEQUENAS = {"de", "da", "do", "das", "dos", "para", "com", "e", "em", "a", "o", "p", "c", "kit"}

CATEGORIA_POR_PALAVRA = [
    (("vaso", "planta", "floreira", "xaxim", "orquídea", "orquidea"), "Suportes de vaso"),
    (("mão francesa", "mao francesa", "mãos francesas"), "Mão francesa"),
    (("prateleira", "nicho", "estante"), "Prateleiras"),
    (("bicicleta", "bike"), "Bicicleta"),
    (("mesa", "aparador", "escrivaninha", "bancada"), "Mesas"),
    (("banqueta", "banco", "cadeira", "puff"), "Banquetas"),
    (("organizador", "sapateira", "cabideiro", "arara", "porta "), "Organizadores"),
    (("jardim", "horta", "treliça", "trelica"), "Jardim"),
    (("espelho", "quadro", "luminária", "luminaria", "letreiro", "decor"), "Decoração"),
]


def _sem_acento(t):
    return unicodedata.normalize("NFKD", str(t)).encode("ascii", "ignore").decode()


def sigla(nome, n=4):
    """Mão Francesa Colonial de Ferro -> MFCF."""
    palavras = [p for p in re.findall(r"[A-Za-zÀ-ÿ]+", str(nome)) if p.lower() not in _PEQUENAS]
    return _sem_acento("".join(p[0] for p in palavras[:n])).upper() or "PROD"


def codigo_da_variacao(base, valor):
    """MFC + '30 cm · Preto' -> MFC-30-PRE; '25 x 25 cm' -> 25X25."""
    partes = [base]
    for pedaco in str(valor or "").split("·"):
        pedaco = pedaco.strip()
        if not pedaco:
            continue
        nums = re.findall(r"\d+(?:[.,]\d+)?", pedaco)
        if nums:
            partes.append("X".join(x.replace(",", "").replace(".", "") for x in nums))
        else:
            letras = [w for w in re.findall(r"[A-Za-zÀ-ÿ]+", pedaco) if w.lower() not in _PEQUENAS]
            if letras:
                partes.append(_sem_acento(letras[0][:3] + "".join(w[0] for w in letras[1:2])).upper())
    return "-".join(partes)


def categoria_pelo_nome(nome):
    t = str(nome).lower()
    for palavras, cat in CATEGORIA_POR_PALAVRA:
        if any(p in t for p in palavras):
            return cat
    return ""


def _vs(d):
    """As variações ligadas (as desligadas não sobem, então não entram nas pendências)."""
    return [v for v in d.get("variacoes") or [] if isinstance(v, dict) and v.get("ativa", True) is not False]


def codigos_das_fichas(pasta_produtos, exceto=None):
    """Códigos já usados nas outras fichas (inclusive as cadastradas), para não repetir."""
    usados = set()
    for f in motor.listar_fichas(pasta_produtos, incluir_feitas=True):
        if exceto and Path(f.pasta).resolve() == Path(exceto).resolve():
            continue
        for c in [f.dados.get("codigo")] + [v.get("codigo") for v in f.variacoes]:
            if str(c or "").strip():
                usados.add(str(c).strip().upper())
    return usados


def completar(d, cfg=None, campos=None, existentes=None):
    """Preenche o que dá para deduzir sem inventar. Devolve a lista do que mudou (o dict muda no lugar).
    existentes: códigos das outras fichas, para o código novo não repetir."""
    cfg = cfg or {}
    existentes = {str(c).upper() for c in existentes or ()}
    campos = set(campos or ("codigos", "categoria", "tecnica", "caixa", "preco_de", "venda", "textos"))
    feito = []
    vs = _vs(d)
    multi = len(vs) > 1 or any(str(v.get("valor") or "").strip() for v in vs)

    if "categoria" in campos and not str(d.get("categoria") or "").strip():
        cat = categoria_pelo_nome(d.get("nome"))
        if cat:
            d["categoria"] = cat
            feito.append(f"Categoria: {cat}")

    if "codigos" in campos and d.get("nome"):
        base = str(d.get("codigo") or "").strip()
        if not base:
            raiz, k = sigla(d["nome"]), 2
            base = raiz
            while any(c == base or c.startswith(base + "-") for c in existentes):
                base, k = f"{raiz}{k}", k + 1
            d["codigo"] = base
            feito.append(f"Código base: {base}")
        usados = existentes | {str(v.get("codigo") or "").strip().upper() for v in vs if str(v.get("codigo") or "").strip()}
        n = 0
        for v in vs:
            if str(v.get("codigo") or "").strip() or not multi:
                continue
            cod = codigo_da_variacao(base, v.get("valor"))
            k, final = 2, cod
            while final.upper() in usados:
                final, k = f"{cod}-{k}", k + 1
            v["codigo"] = final
            usados.add(final.upper())
            n += 1
        if n:
            feito.append(f"Código em {n} variação(ões) (ex.: {next(v['codigo'] for v in vs if v.get('codigo'))})")

    if "tecnica" in campos:
        tec = [i for i in d.get("ficha_tecnica") or [] if isinstance(i, dict)]
        tem = {str(i.get("nome") or "").strip().lower() for i in tec}
        novos = []
        for nome, chave, fmt in (("Material", "material", str), ("Acabamento", "acabamento", str), ("Montagem", "montagem", str),
                                 ("Carga máxima", "carga_maxima_kg", lambda x: f"{motor.numero(x):g} kg".replace(".", ",")),
                                 ("Marca", "marca", str)):
            val = d.get(chave)
            if val not in (None, "") and nome.lower() not in tem:
                novos.append({"nome": nome, "valor": fmt(val)})
        if novos:
            d["ficha_tecnica"] = tec + novos
            feito.append("Ficha técnica: " + ", ".join(i["nome"] for i in novos))

    if "caixa" in campos:
        folga = motor.numero(cfg.get("folga_caixa") or 4) or 4
        n = 0
        for v in vs:
            med = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
            emb = v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}
            diam = motor.numero(med.get("diametro"))
            larg = motor.numero(med.get("largura")) or diam
            prof = motor.numero(med.get("profundidade")) or diam
            alt = motor.numero(med.get("altura"))
            mudou = False
            for chave, val in (("comprimento_cm", larg), ("largura_cm", prof), ("altura_cm", alt)):
                if val > 0 and motor.numero(emb.get(chave)) <= 0:
                    emb[chave] = round(val + folga, 1)
                    mudou = True
            if mudou:
                v["embalagem"] = emb
                n += 1
        if n:
            feito.append(f"Medidas da caixa pela peça (+{folga:g} cm) em {n} variação(ões). O peso você confere.")

    soma = sum((motor.numero(i.get("quantidade")) or 1) * motor.numero(i.get("valor"))
               for i in d.get("ficha_de_custo") or [] if isinstance(i, dict))
    if "venda" in campos:
        margem = motor.numero(cfg.get("margem_padrao") or 30)
        frete = motor.numero(cfg.get("frete_estimado") or 0)
        tipo = "premium" if str((d.get("mercado_livre") or {}).get("tipo_anuncio") or "").startswith("prem") else "classico"
        n = 0
        for v in vs:
            custo = motor.numero(v.get("custo")) if v.get("custo") not in (None, "") else soma
            if motor.numero(v.get("venda")) <= 0 and custo > 0:
                p = preco_sugerido(custo, margem, tipo, frete)
                if p:
                    v["venda"] = p
                    n += 1
        if n:
            feito.append(f"Preço de venda sugerido em {n} variação(ões) ({margem:g}% de lucro)")

    if "preco_de" in campos:
        pct = motor.numero(cfg.get("preco_de_pct") or 0)
        if pct > 0:
            n = 0
            for v in vs:
                if motor.numero(v.get("venda")) > 0 and v.get("preco_tabela") in (None, "", 0):
                    v["preco_tabela"] = arredondar_90(motor.numero(v["venda"]) * (1 + pct / 100))
                    n += 1
            if n:
                feito.append(f"Preço “de” (+{pct:g}%) em {n} variação(ões)")

    if "textos" in campos and d.get("nome"):
        ml = d.get("mercado_livre") if isinstance(d.get("mercado_livre"), dict) else {}
        if not str(ml.get("titulo_anuncio") or "").strip():
            ml["titulo_anuncio"] = titulo_modelo(d)
            d["mercado_livre"] = ml
            feito.append("Título do anúncio")
        if not str(d.get("descricao") or "").strip():
            d["descricao"] = descricao_modelo(d)
            feito.append("Descrição")
    return feito


def problemas(d, n_fotos=None):
    """O que ainda falta na ficha (textos para a lista de conferência)."""
    return [p["texto"] for p in pendencias(d, n_fotos)]


def links_ml(texto):
    """Todos os links (ou MLB-123) do Mercado Livre num texto colado, sem repetir."""
    achados = re.findall(r"https?://[^\s<>\"']*mercadoli[bv]re\.com[^\s<>\"']*|\bMLB-?\d{6,}\b", str(texto), flags=re.I)
    vistos, out = set(), []
    for a in achados:
        a = a.rstrip(".,;)")
        k = _chave_link(a)
        if k not in vistos:
            vistos.add(k)
            out.append(a)
    return out


# ------------------------------------------------------------ resolver os avisos da ficha

CORES = ("preto", "preta", "branco", "branca", "ouro", "dourado", "dourada", "cobre", "bronze", "prata", "prateado", "cinza",
         "grafite", "marrom", "natural", "rosa", "verde", "azul", "vermelho", "amarelo", "bege", "champagne", "envelhecido",
         "rose", "fendi", "off", "nogueira", "carvalho", "freijo", "imbuia", "tabaco", "mel", "rustico", "madeira", "ferrugem")

# chave -> (seção do editor, rótulo do botão que resolve sozinho ou None = só você sabe)
AVISOS = {
    "nome": ("produto", None), "categoria": ("produto", "Pelo nome"), "codigo": ("produto", "Gerar códigos"),
    "venda": ("variacoes", "Sugerir pelo custo"), "custo": ("custo", "Estimar"), "peso": ("variacoes", "Estimar"),
    "prazo": ("precos", "Usar prazo padrão"), "variacao_por": ("variacoes", "Definir"),
    "titulo": ("ml", "Escrever"), "titulo_longo": ("ml", "Encurtar"), "descricao": ("produto", "Escrever"),
    "fotos": ("fotos", None), "fotos_faltando": ("fotos", "Tirar da ficha"), "fotos_nao_citadas": ("fotos", "Ligar às variações"),
}

_PREFIXOS = [("Sem custo", "custo"), ("Sem peso", "peso"), ("Sem código", "codigo"), ("Sob encomenda", "prazo"),
             ('Sem "variacao_por"', "variacao_por"), ("Fotos citadas", "fotos_faltando"), ("A ficha não cita", "fotos_nao_citadas"),
             ("Sem fotos", "fotos")]


def chave_do_aviso(texto):
    """Aviso que o motor escreve (ler_ficha) -> chave de AVISOS."""
    for pre, chave in _PREFIXOS:
        if str(texto).startswith(pre):
            return chave
    return None


def _soma_custo(d):
    return sum((motor.numero(i.get("quantidade")) or 1) * motor.numero(i.get("valor"))
               for i in d.get("ficha_de_custo") or [] if isinstance(i, dict))


def _rot(v, i, n):
    return str(v.get("valor") or "").strip() or (f"variação {i}" if n > 1 else "produto")


def _emb(v):
    return v.get("embalagem") if isinstance(v.get("embalagem"), dict) else {}


def _volume(v):
    """Tamanho da caixa para comparar pesos: (quantas medidas tem, produto delas)."""
    e = _emb(v)
    med = [motor.numero(e.get(k)) for k in ("comprimento_cm", "largura_cm", "altura_cm")]
    med = [x for x in med if x > 0]
    if not med:
        m = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
        med = [x for x in (motor.numero(m.get("largura")) or motor.numero(m.get("diametro")),
                           motor.numero(m.get("profundidade")) or motor.numero(m.get("diametro")), motor.numero(m.get("altura"))) if x > 0]
    return (len(med), math.prod(med)) if med else (0, 0.0)


def pendencias(d, n_fotos=None, cfg=None):
    """O que falta na ficha, com a seção do editor e o que o robô consegue resolver sozinho.
    Cada item: {chave, texto, secao, acao (rótulo do botão ou None), variacao (índice ou None)}."""
    cfg = cfg or {}
    out = []

    def add(chave, texto, var=None, acao="padrao"):
        sec, rot = AVISOS[chave]
        out.append({"chave": chave, "texto": texto, "secao": sec, "acao": rot if acao == "padrao" else acao, "variacao": var})

    if not str(d.get("nome") or "").strip():
        add("nome", "Falta o nome do produto.")
    vs = _vs(d)
    n = len(vs)
    soma = _soma_custo(d)
    com_custo = [v for v in vs if v.get("custo") not in (None, "") and motor.numero(v.get("venda")) > 0]
    com_peso = [v for v in vs if motor.numero(_emb(v).get("peso_kg")) > 0]
    for i, v in enumerate(vs, 1):
        rot = _rot(v, i, n)
        custo = motor.numero(v.get("custo")) if v.get("custo") not in (None, "") else soma
        if motor.numero(v.get("venda")) <= 0:
            add("venda", f"{rot}: sem preço de venda.", i - 1, "Sugerir pelo custo" if custo > 0 else None)
        if v.get("custo") in (None, "") and soma <= 0:
            add("custo", f"{rot}: sem custo.", i - 1, "Estimar" if com_custo and motor.numero(v.get("venda")) > 0 else None)
        if motor.numero(_emb(v).get("peso_kg")) <= 0:
            pode = com_peso or peso_pelo_tipo(d, v, motor.numero(cfg.get("folga_caixa") or 4) or 4) is not None
            add("peso", f"{rot}: sem peso da caixa (o frete precisa).", i - 1, "Estimar" if pode else "Informar")
    multi = n > 1 or any(str(v.get("valor") or "").strip() for v in vs)
    if str(d.get("nome") or "").strip() and not str(d.get("codigo") or "").strip() and not any(str(v.get("codigo") or "").strip() for v in vs):
        add("codigo", "Sem código (o site inventa um).")
    elif multi and any(not str(v.get("codigo") or "").strip() for v in vs):
        add("codigo", "Variação sem código.")
    if motor.sim(d.get("sob_encomenda")) and motor.numero(d.get("prazo_dias")) <= 0:
        add("prazo", "Sob encomenda sem prazo em dias.", acao=f"Usar {prazo_padrao(cfg)} dias")
    if multi and not str(d.get("variacao_por") or "").strip():
        add("variacao_por", "Não diz se varia por tamanho ou cor.", acao=f"Usar “{variacao_por_pelos_valores(d)}”")
    if not str(d.get("categoria") or "").strip():
        add("categoria", "Sem categoria.", acao="Pelo nome" if categoria_pelo_nome(d.get("nome")) else None)
    ml = d.get("mercado_livre") if isinstance(d.get("mercado_livre"), dict) else {}
    tit = str(ml.get("titulo_anuncio") or "").strip()
    if d.get("nome") and not tit:
        add("titulo", "Sem título do anúncio.")
    elif len(tit) > 60:
        add("titulo_longo", f"Título do anúncio com {len(tit)} letras (máx. 60).")
    if d.get("nome") and not str(d.get("descricao") or "").strip():
        add("descricao", "Sem descrição.")
    if n_fotos == 0:
        add("fotos", "Sem fotos.", acao="Baixar do anúncio" if ml.get("link_referencia") else None)
    return out


def prazo_padrao(cfg=None):
    return int(motor.numero((cfg or {}).get("prazo_padrao") or 15) or 15)


def variacao_por_pelos_valores(d):
    tem_num = tem_cor = False
    for v in _vs(d):
        for pedaco in str(v.get("valor") or "").replace("/", "·").split("·"):
            t = _sem_acento(pedaco).lower()
            if re.search(r"\d", t):
                tem_num = True
            elif any(w in CORES for w in re.findall(r"[a-z]+", t)):
                tem_cor = True
    if tem_num and tem_cor:
        return "Tamanho e cor"
    return "Cor" if tem_cor and not tem_num else "Tamanho"


def encurtar_titulo(t, limite=60):
    t = re.sub(r"\s+", " ", str(t)).strip()
    if len(t) <= limite:
        return t
    corte = t[:limite + 1].rsplit(" ", 1)[0]
    while corte and corte.split(" ")[-1].lower() in _PEQUENAS | {"-", "·", "|"}:
        corte = corte.rsplit(" ", 1)[0] if " " in corte else ""
    return corte.rstrip(" -,·|") or t[:limite]


def estimar_custos(d):
    """Custo das variações sem custo pela proporção custo/venda das que têm. Devolve quantas mudou."""
    vs = _vs(d)
    base = [(motor.numero(v["custo"]), motor.numero(v.get("venda"))) for v in vs
            if v.get("custo") not in (None, "") and motor.numero(v.get("venda")) > 0]
    if not base:
        return 0
    r = sum(c for c, _ in base) / sum(p for _, p in base)
    n = 0
    for v in vs:
        if v.get("custo") in (None, "") and motor.numero(v.get("venda")) > 0:
            v["custo"] = round(motor.numero(v["venda"]) * r, 2)
            n += 1
    return n


def estimar_pesos(d, peso=None, pelo_tipo=True, folga=4.0):
    """Peso das caixas sem peso: pelo tamanho da caixa comparado com as que têm peso.
    Sem nenhuma com peso, usa `peso` (se veio). Devolve quantas mudou."""
    vs = _vs(d)
    base = [(motor.numero(_emb(v).get("peso_kg")), _volume(v)) for v in vs if motor.numero(_emb(v).get("peso_kg")) > 0]
    n = 0
    for v in vs:
        e = _emb(v)
        if motor.numero(e.get("peso_kg")) > 0:
            continue
        nd, vol = _volume(v)
        par = [(p, x) for p, (k, x) in base if k == nd and x > 0]
        if par and vol > 0:
            novo = vol * sum(p for p, _ in par) / sum(x for _, x in par)
        elif base:
            novo = sum(p for p, _ in base) / len(base)
        elif peso:
            novo = motor.numero(peso)
        elif pelo_tipo and peso_pelo_tipo(d, v, folga) is not None:
            novo = peso_pelo_tipo(d, v, folga)
            v["_peso_tipo"] = True
        else:
            continue
        e["peso_kg"] = max(0.1, round(novo, 1))
        v["embalagem"] = e
        n += 1
    return n


def _fotos_da_pasta(pasta):
    return sorted((p for p in Path(pasta).iterdir() if p.is_file() and p.suffix.lower() in motor.FOTO_EXTS | motor.FOTO_CONVERTER),
                  key=lambda p: p.name.lower())


def _tokens(t):
    return set(re.findall(r"[a-z]+|\d+", _sem_acento(str(t)).lower())) - _PEQUENAS - {"cm", "mm", "x", "jpg", "jpeg", "png", "webp"}


def ligar_fotos(d, pasta):
    """Liga cada foto da pasta à variação certa pelo nome do arquivo (preto.jpg -> variação Preto).
    As que não batem com nenhuma valem para todas. Devolve (quantas próprias, quantas para todas)."""
    vs = _vs(d)
    fotos = _fotos_da_pasta(pasta)
    if not vs or not fotos:
        return 0, 0
    toks = [_tokens(v.get("valor")) for v in vs]
    proprias = {i: [] for i in range(len(vs))}
    gerais = []
    for p in fotos:
        tp = _tokens(p.stem)
        notas = [len(tp & t) for t in toks]
        melhor = max(notas)
        if len(vs) > 1 and melhor > 0 and notas.count(melhor) == 1:
            proprias[notas.index(melhor)].append(p.name)
        else:
            gerais.append(p.name)
    n_prop = sum(len(x) for x in proprias.values())
    if n_prop:
        for i, v in enumerate(vs):
            v["fotos"] = proprias[i] + gerais
    else:
        for i, v in enumerate(vs):
            v["fotos"] = gerais if i == 0 else []
    return n_prop, len(gerais)


def tirar_fotos_faltando(d, pasta):
    nomes = {p.name.lower() for p in _fotos_da_pasta(pasta)} | {p.stem.lower() for p in _fotos_da_pasta(pasta)}
    tirou = []
    for v in _vs(d):
        fica = []
        for nome in v.get("fotos") or []:
            n = str(nome).strip().replace("\\", "/").split("/")[-1]
            if n.lower() in nomes or Path(n).stem.lower() in nomes:
                fica.append(nome)
            elif n not in tirou:
                tirou.append(n)
        v["fotos"] = fica
    return tirou


def resolver(d, chaves=None, cfg=None, existentes=None, pasta=None, peso=None):
    """Resolve sozinho os avisos que dá (o dict muda no lugar). chaves=None tenta todos.
    Devolve a lista do que fez; valores estimados vêm marcados para conferir."""
    cfg = cfg or {}
    chaves = set(chaves or AVISOS)
    feito = []
    if "categoria" in chaves:
        feito += completar(d, cfg, ["categoria"])
    if "codigo" in chaves:
        feito += completar(d, cfg, ["codigos"], existentes)
    if "venda" in chaves:
        feito += completar(d, cfg, ["venda"])
    if "custo" in chaves and _soma_custo(d) <= 0:
        n = estimar_custos(d)
        if n:
            feito.append(f"Custo estimado em {n} variação(ões) pela proporção das outras (confira)")
    if "peso" in chaves:
        completar(d, cfg, ["caixa"])
        n = estimar_pesos(d, peso, folga=motor.numero(cfg.get("folga_caixa") or 4) or 4)
        tipo = sum(1 for v in _vs(d) if v.pop("_peso_tipo", None))
        if n - tipo > 0:
            feito.append(f"Peso da caixa em {n - tipo} variação(ões)" + (" (estimado pelas outras; confira)" if not peso else ""))
        if tipo:
            feito.append(f"Peso da caixa em {tipo} variação(ões) estimado pelo tipo, material e tamanho da peça (confira na balança)")
    if "prazo" in chaves and motor.sim(d.get("sob_encomenda")) and motor.numero(d.get("prazo_dias")) <= 0:
        d["prazo_dias"] = prazo_padrao(cfg)
        feito.append(f"Prazo de {d['prazo_dias']} dias (sob encomenda)")
    vs = _vs(d)
    multi = len(vs) > 1 or any(str(v.get("valor") or "").strip() for v in vs)
    if "variacao_por" in chaves and multi and not str(d.get("variacao_por") or "").strip():
        d["variacao_por"] = variacao_por_pelos_valores(d)
        feito.append(f"Varia por: {d['variacao_por']}")
    ml = d.get("mercado_livre") if isinstance(d.get("mercado_livre"), dict) else {}
    if "titulo_longo" in chaves and len(str(ml.get("titulo_anuncio") or "")) > 60:
        ml["titulo_anuncio"] = encurtar_titulo(ml["titulo_anuncio"])
        d["mercado_livre"] = ml
        feito.append(f"Título encurtado para {len(ml['titulo_anuncio'])} letras")
    if chaves & {"titulo", "descricao"}:
        feito += completar(d, cfg, ["textos"])
    if pasta is not None:
        if "fotos_faltando" in chaves:
            tirou = tirar_fotos_faltando(d, pasta)
            if tirou:
                feito.append("Tirei da ficha as fotos que não estão na pasta: " + ", ".join(tirou))
        if "fotos_nao_citadas" in chaves and not any(v.get("fotos") for v in vs):
            prop, ger = ligar_fotos(d, pasta)
            if prop or ger:
                feito.append(f"Fotos ligadas: {prop} na variação certa pelo nome do arquivo, {ger} para todas" if prop
                             else f"{ger} foto(s) ligadas a todas as variações, em ordem de nome")
    return feito


def faltam_numeros(d, n_fotos=None):
    """Variações que ainda precisam de custo ou peso que só o dono sabe: [(índice, rótulo, falta_custo, falta_peso)]."""
    vs = _vs(d)
    soma = _soma_custo(d)
    out = []
    for i, v in enumerate(vs, 1):
        fc = v.get("custo") in (None, "") and soma <= 0
        fp = motor.numero(_emb(v).get("peso_kg")) <= 0
        if fc or fp:
            out.append((i - 1, _rot(v, i, len(vs)), fc, fp))
    return out


# ------------------------------------------------------------ peso pelo tipo e tamanho (sem nenhuma referência)

# categoria -> (medida de referência em cm (as 2 maiores), peso da peça em kg) para peças de ferro
PESO_REFERENCIA = {
    "Mão francesa": ((25, 25), 0.5),
    "Suportes de vaso": ((60, 30), 1.5),
    "Prateleiras": ((60, 20), 2.5),
    "Bicicleta": ((50, 20), 1.5),
    "Mesas": ((120, 75), 22.0),
    "Banquetas": ((65, 35), 5.0),
    "Organizadores": ((80, 30), 4.0),
    "Jardim": ((50, 50), 2.0),
    "Decoração": ((40, 40), 1.0),
}
PESO_POR_CM2 = 0.0012          # quando a categoria não está na tabela (ferro)
MATERIAL_FATOR = [(("alumínio", "aluminio"), 0.45), (("mdf", "mdp"), 1.3), (("madeira", "pinus", "eucalipto"), 1.1),
                  (("vidro",), 1.4), (("inox", "aço", "aco", "ferro"), 1.0)]


def _duas_maiores(v, folga=4.0):
    m = v.get("medidas_peca_cm") if isinstance(v.get("medidas_peca_cm"), dict) else {}
    med = [motor.numero(m.get(k)) for k in ("altura", "largura", "profundidade", "diametro")]
    med = sorted((x for x in med if x > 0), reverse=True)
    if len(med) < 2:
        e = _emb(v)
        caixa = sorted((motor.numero(e.get(k)) - folga for k in ("comprimento_cm", "largura_cm", "altura_cm")
                        if motor.numero(e.get(k)) > folga), reverse=True)
        med = caixa if len(caixa) >= len(med) else med
    if not med:
        return None
    return med[0], (med[1] if len(med) > 1 else med[0] / 4)


def peso_pelo_tipo(d, v, folga=4.0):
    """Peso da caixa (kg) estimado pela categoria, material e tamanho da peça. None se não tem medida."""
    dm = _duas_maiores(v, folga)
    if not dm:
        return None
    area = dm[0] * dm[1]
    cat = str(d.get("categoria") or "").strip() or categoria_pelo_nome(d.get("nome"))
    if cat in PESO_REFERENCIA:
        (ra, rb), rp = PESO_REFERENCIA[cat]
        peca = rp * area / (ra * rb)
    else:
        peca = area * PESO_POR_CM2
    mat = _sem_acento(f"{d.get('material') or ''} {d.get('nome') or ''}").lower()
    fator = 1.0
    for palavras, f in MATERIAL_FATOR:
        if any(_sem_acento(p) in mat for p in palavras):
            fator = f
            break
    if "madeira" in mat and "ferro" in mat:
        fator = 1.15
    return max(0.2, round(peca * fator * 1.1 + 0.2, 1))   # +10% e 200 g da caixa e proteção


# ------------------------------------------------------------ atualizar o robô sozinho

VERSAO_ARQ = "versao.json"


def versao_local():
    try:
        return json.loads((motor.BASE / VERSAO_ARQ).read_text(encoding="utf-8"))
    except Exception:
        return {"versao": 6}      # a 6 foi a última sem versao.json


def _api(cfg, caminho, cru=False, timeout=20):
    repo = (cfg.get("atualizar_repo") or "Marco-Bubola/FlowManeger").strip()
    req = urllib.request.Request(f"https://api.github.com/repos/{repo}/{caminho}",
                                 headers={"Accept": "application/vnd.github.raw" if cru else "application/vnd.github+json",
                                          "User-Agent": "robo-stylo"})
    token = (cfg.get("atualizar_token") or "").strip()
    if token:
        req.add_header("Authorization", f"Bearer {token}")
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return r.read()


def checar_atualizacao(cfg):
    """Procura versão nova no GitHub. Devolve o versao.json novo (com 'sha') ou None se já está em dia."""
    cfg = cfg or {}
    ref = (cfg.get("atualizar_ref") or "claude/project-thread-l778hs").strip()
    pasta = (cfg.get("atualizar_pasta") or "ferramentas/robo-stylo").strip().strip("/")
    commits = json.loads(_api(cfg, f"commits?sha={urllib.request.quote(ref, safe='')}&path={pasta}&per_page=1"))
    if not commits:
        return None
    sha = commits[0]["sha"]
    nova = json.loads(_api(cfg, f"contents/{pasta}/{VERSAO_ARQ}?ref={sha}", cru=True))
    if int(nova.get("versao") or 0) <= int(versao_local().get("versao") or 0):
        return None
    nova["sha"], nova["pasta"] = sha, pasta
    return nova


def instalar_atualizacao(cfg, nova, avisar=lambda t: None):
    """Baixa os arquivos da versão nova, confere e troca (guarda a atual em _versao-anterior). Devolve se precisa do pip."""
    import ast
    import tempfile
    cfg = cfg or {}
    arquivos = [a for a in nova.get("arquivos") or [] if re.fullmatch(r"[\w .\-]+", a) and not a.startswith(".")]
    proibidos = {"config.ini", "concorrentes.json", "historico-ml.json"}
    arquivos = [a for a in arquivos if a.lower() not in proibidos]
    if not arquivos:
        raise ValueError("A versão nova não lista arquivos.")
    tmp = Path(tempfile.mkdtemp(prefix="robo-stylo-atualizar-"))
    for i, a in enumerate(arquivos, 1):
        avisar(f"Baixando {a} ({i}/{len(arquivos)})…")
        dados = _api(cfg, f"contents/{nova['pasta']}/{urllib.request.quote(a)}?ref={nova['sha']}", cru=True, timeout=60)
        if a.endswith((".py", ".pyw")):
            ast.parse(dados.decode("utf-8"))      # não troca nada se o arquivo veio quebrado
        (tmp / a).write_bytes(dados)
    (tmp / VERSAO_ARQ).write_text(json.dumps({k: v for k, v in nova.items() if k != "pasta"}, ensure_ascii=False, indent=2), encoding="utf-8")
    atual = versao_local().get("versao") or "antiga"
    copia = motor.BASE / "_versao-anterior" / f"v{atual}"
    copia.mkdir(parents=True, exist_ok=True)
    req_antes = (motor.BASE / "requirements.txt").read_bytes() if (motor.BASE / "requirements.txt").exists() else b""
    for a in arquivos + [VERSAO_ARQ]:
        if (motor.BASE / a).exists():
            shutil.copy2(motor.BASE / a, copia / a)
    for a in arquivos + [VERSAO_ARQ]:
        shutil.copy2(tmp / a, motor.BASE / a)
    shutil.rmtree(tmp, ignore_errors=True)
    return "requirements.txt" in arquivos and (motor.BASE / "requirements.txt").read_bytes() != req_antes
