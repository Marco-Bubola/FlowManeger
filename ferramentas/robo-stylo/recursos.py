# -*- coding: utf-8 -*-
"""
Recursos extras do robô da Móveis Stylo (sem navegador, menos a conferência de concorrentes):
preço do Mercado Livre, calculadora de custo e preço, concorrentes, título e descrição,
fotos prontas, atualização em lote, kits e etiquetas.
"""
import json
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
