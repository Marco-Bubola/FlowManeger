# Ficha de produto · Móveis Stylo

> **Para a IA que vai ler este arquivo:** você vai montar a ficha de **um produto** da Móveis Stylo (móveis e peças de ferro e madeira). Um robô lê a ficha e cadastra o produto no site FlowManeger. Siga estas regras:
>
> 1. Use as informações que o Marco te passar: fotos, medidas, preço, link de anúncio parecido no Mercado Livre e o que ele disser.
> 2. Se faltar alguma coisa, **pergunte ao Marco**. Faça as perguntas da Parte 1, uma por vez ou todas juntas. Não invente preço, custo, estoque, medida nem prazo.
> 3. Os textos de venda (título, descrição, para que serve e ficha técnica) você escreve. Use linguagem simples e verdadeira, sem exagero.
> 4. No fim, entregue **só** a ficha no formato da Parte 2: o bloco ```json``` completo e válido. O robô lê apenas esse bloco.
> 5. Dinheiro vai em reais com ponto decimal (`79.90`). Medidas vão em **cm** e peso em **kg**. Escreva "sim" ou "não" como `true` ou `false`.

---

## Parte 1 · Perguntas para o Marco

### A. O produto
1. **Que peça é?** Diga o nome curto, ex.: "Suporte para vaso redondo com rodinhas".
2. **Para que serve e onde se usa?** Ex.: sala, varanda, jardim, garagem, cozinha.
3. **De que é feita?** Ferro, madeira (qual: pinus, eucalipto, MDF), metalon, chapa. Qual o acabamento: pintura eletrostática, verniz, cera, natural.
4. **Categoria no site:** suportes de vaso, bicicleta, mão francesa, prateleiras, mesas, banquetas, decoração, outra?
5. **Vem montada ou desmontada?** Vem com parafusos ou buchas?
6. **Quanto peso aguenta?** Responda só se souber.
7. **O que vem na caixa?** Ex.: "1 suporte + 4 rodízios + parafusos" ou "Jogo com 10 argolas".

### B. Tamanhos, cores e variações
8. **Tem mais de um tamanho?** Quais? Ex.: 30 cm, 40 cm, 50 cm.
9. **Tem mais de uma cor ou acabamento?** Quais? Ex.: Preto, Branco, Ouro velho.
10. **Cada tamanho ou cor tem preço diferente?** Se tiver, passe o custo e o preço de venda de cada um.
11. **Medidas da peça montada**, por tamanho: altura, largura, profundidade ou diâmetro.

### C. Preço e estoque
12. **Custo** da peça. Se quiser, detalhe os materiais e as horas, que viram a ficha de custo. Ex.: tubo de ferro 3 × R$ 12,50; pintura R$ 15.
13. **Preço de venda** no site.
14. **Preço "de"**, o preço riscado de tabela. É opcional.
15. **Quantas tem prontas em estoque** de cada variação?
16. **É sob encomenda?** Ou seja, é feita depois da venda? Se for, **em quantos dias fica pronta?**

### D. Envio e frete
17. **Peso com a embalagem**, em kg.
18. **Medidas da caixa embalada**: comprimento × largura × altura, em cm.

### E. Mercado Livre
19. **Tem anúncio igual ou parecido no Mercado Livre?** Mande o link. Pode ser seu ou de outra loja, para usar de base.
20. **Quantas unidades quer no anúncio?** Para produto sob encomenda, o site já põe 10.
21. **Tipo de anúncio:** Clássico (taxa menor) ou Premium (mais exposição, com parcelamento)?
22. **Garantia** em meses. O padrão é 3.
23. **Código de barras (EAN)**, se tiver. Peça de fabricação própria normalmente não tem, e tudo bem.

### F. Fotos
24. **Fotos da peça:** fundo claro, a primeira de frente, a peça inteira. Mande de 3 a 8 fotos.
25. **Tem foto de cada cor ou tamanho?** Diga qual foto é de qual variação.
26. **Foto do produto em uso** (no ambiente) e **foto com as medidas**. São opcionais, mas ajudam muito no Mercado Livre.

---

## Parte 2 · Formato da ficha (o que a IA entrega)

Copie o modelo abaixo e preencha. Os campos marcados com `*` são obrigatórios. Se um campo não se aplica, deixe `null`. Não apague o campo.

```json
{
  "versao": 1,
  "nome": "Suporte para Vaso Redondo com Rodinhas",
  "codigo": "SV",
  "categoria": "Suportes de vaso",
  "marca": "Móveis Stylo",
  "modelo": null,
  "material": "Madeira pinus e rodízios de silicone",
  "acabamento": "Verniz natural",
  "uso": "Para mover vasos pesados na sala, varanda ou jardim sem arrastar.",
  "descricao": "Texto de venda com 3 a 6 parágrafos curtos: o que é, para que serve, material, medidas, o que vem na caixa e cuidados.",
  "o_que_vem_na_caixa": "1 suporte com 4 rodízios montados",
  "montagem": "Vem montado",
  "carga_maxima_kg": 50,
  "garantia_meses": 3,
  "condicao": "novo",
  "codigo_barras": null,

  "sob_encomenda": false,
  "prazo_dias": null,

  "ficha_tecnica": [
    { "nome": "Material", "valor": "Madeira pinus" },
    { "nome": "Formato", "valor": "Redondo" },
    { "nome": "Quantidade de rodízios", "valor": "4" }
  ],

  "ficha_de_custo": [
    { "item": "Madeira pinus", "quantidade": 1, "valor": 12.00 },
    { "item": "Rodízios", "quantidade": 4, "valor": 2.00 },
    { "item": "Mão de obra", "quantidade": 1, "valor": 2.00 }
  ],

  "variacao_por": "Tamanho",

  "variacoes": [
    {
      "valor": "30 cm",
      "codigo": "SV-30",
      "custo": 22.00,
      "venda": 48.90,
      "preco_tabela": null,
      "estoque": 10,
      "medidas_peca_cm": { "altura": 10, "largura": 30, "profundidade": 30, "diametro": 30 },
      "embalagem": { "peso_kg": 1.2, "comprimento_cm": 32, "largura_cm": 32, "altura_cm": 10 },
      "fotos": ["01-frente.jpg", "02-lado.jpg", "03-uso.jpg", "04-medidas.jpg"]
    },
    {
      "valor": "40 cm",
      "codigo": "SV-40",
      "custo": 30.00,
      "venda": 64.90,
      "preco_tabela": null,
      "estoque": 6,
      "medidas_peca_cm": { "altura": 10, "largura": 40, "profundidade": 40, "diametro": 40 },
      "embalagem": { "peso_kg": 1.8, "comprimento_cm": 42, "largura_cm": 42, "altura_cm": 10 },
      "fotos": ["05-40cm.jpg"]
    }
  ],

  "mercado_livre": {
    "link_referencia": "https://produto.mercadolivre.com.br/MLB-...",
    "titulo_anuncio": "Suporte Para Vaso Redondo Com Rodinhas Madeira 30 cm",
    "tipo_anuncio": "classico",
    "quantidade_anuncio": 10
  }
}
```

### Regras do formato
- **`nome`\***: o nome base, **sem** o tamanho e sem a cor. O site junta o nome com a variação, ex.: "Suporte para Vaso Redondo com Rodinhas 30 cm".
- **`variacoes`\***: sempre tem pelo menos 1 item. Um produto sem variação tem 1 item com `"valor": null`.
- **`variacao_por`**: use `"Tamanho"`, `"Cor"` ou `"Tamanho e cor"`. Para tamanho e cor, escreva o valor assim: `"30 cm · Preto"`. O separador é ponto do meio com um espaço de cada lado.
- **`codigo`** de cada variação deve ser único. Se ficar vazio, o site cria um.
- **`venda`\*** é obrigatório. **`custo`**, se ficar vazio, usa a soma da `ficha_de_custo`.
- **`sob_encomenda: true`** precisa de `prazo_dias`. O estoque pode ser 0: o produto continua aparecendo no site e no Mercado Livre.
- **`fotos`**: só o **nome do arquivo**. As fotos ficam na mesma pasta da ficha. A primeira foto da lista vira a foto principal; as outras vão para a galeria. Use JPG, PNG ou WEBP de até 2 MB, de preferência com 800 × 800 px ou mais.
- **`titulo_anuncio`**: até 60 caracteres. Comece pelo que a peça é, depois o material e a medida. Não use "promoção" nem "frete grátis".
- **`descricao`**: até 5000 caracteres, sem links, sem telefone, sem emojis.

---

## Parte 3 · Como entregar ao robô

**Jeito mais fácil:** no robô, toque em **Colar ficha da IA**, cole a resposta inteira da IA, escolha as fotos e toque em **Criar ficha**. O robô cria a pasta sozinho.

**À mão:** crie uma pasta por produto dentro de `produtos\` (ao lado do robô), com o código e o nome no nome da pasta:

```
C:\projetos\robo-stylo\produtos\
   SV - Suporte para vaso\
      ficha.md        ← esta ficha preenchida (com o bloco json)
      01-frente.jpg
      02-lado.jpg
      03-uso.jpg
      ...
```

O robô cadastra as pastas novas e depois move cada uma para `produtos\feitos\`.

**Sem robô:** a mesma ficha também pode ser enviada no site, em **Produtos › Importar ficha**, junto com as fotos.

---

## Exemplos prontos (dos prints que o Marco mandou)

| Produto | `variacao_por` | Variações | Sob encomenda |
|---|---|---|---|
| Suporte para vaso redondo com rodinhas | Tamanho | 30 cm, 40 cm | não |
| Jogo 10 argolas porta vaso | — (1 item, valor `null`) | — | não |
| Suporte de bicicleta chão e parede | Cor | Preto, Branco | não |
| Rack para bicicletas | — | — | sim, 10 dias |
| Mão francesa colonial | Tamanho e cor | 20 cm · Preto, 20 cm · Ouro velho, 30 cm · Preto… | sim, 7 dias |
