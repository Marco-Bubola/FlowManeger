# Responsividade Device Matrix

## Escopo concluido
- SaleIndex: condicoes de UI mobile aplicadas para esconder botoes/controles de quantidade, totais, pendentes e pagos abaixo de `md`.
- Publicacoes ML: header e controles reorganizados para quebrar linha corretamente em mobile/tablet.
- Base global reutilizavel criada em `public/assets/css/flow-theme.css`.

## Matriz de resolucoes implementada
- iPhone 15: `393x852`.
- iPad 11 a 13 (retrato): `834x1194`.
- iPad 11 a 13 (paisagem): `1194x834`.
- iPad 16 (retrato): `1024x1366`.
- iPad 16 (paisagem): `1366x1024`.
- Full HD: `1920x1080`.
- Ultra-wide: `2560x1080`.
- Notebook: `1360x760` com escala geral em `85%`.

## To-Do tecnico fechado (executado)
1. Mapear componentes de SaleIndex e Publicacoes ML.
2. Aplicar condicoes mobile com `hidden md:flex` e `hidden md:inline-flex` nos controles solicitados.
3. Padronizar wrappers de viewport com `w-full`, `min-h-screen`, `h-screen` e `app-viewport-fit`.
4. Criar tokens e regras globais de densidade/responsividade em `flow-theme.css`.
5. Ajustar grids para cada faixa de resolucao alvo.
6. Ajustar tipografia e espacamento por breakpoint para reduzir scroll desnecessario.
7. Registrar matriz de dispositivos para reaproveitamento futuro.

## Padrao para futuras paginas
1. Adicionar no container raiz: `w-full h-screen min-h-screen app-viewport-fit <nome-da-pagina>`.
2. Em grupos de controles pesados no header, usar:
- mobile: `hidden md:flex`.
- botoes individuais: `hidden md:inline-flex`.
3. Para grids principais, criar seletor por pagina em `flow-theme.css` e controlar colunas por media query.
4. Em telas `1360x760`, nao sobrescrever `zoom` localmente para preservar escala global de 85%.
5. Em overlays/modais, manter `max-height` com `dvh` para evitar overflow vertical.

## Pontos de validacao visual
- Sem scroll horizontal em `393x852`.
- Header sem quebra agressiva em iPad retrato/paisagem.
- Cards com leitura confortavel em `1920x1080` e `2560x1080`.
- Densidade consistente em notebook `1360x760`.

## Checklist atual (rodada de correcao)
- [x] Forcar ocultacao mobile no header de vendas para `Quantidade/Totais/Pendentes/Pagos`.
- [x] Ajustar proporcao e tipografia dos cards de venda para iPhone 15 (`393x852`).
- [x] Ajustar escala de cards de venda para iPad retrato/paisagem.
- [x] Revisar sidebar para tablet com largura reduzida e melhor equilibrio visual.
- [x] Aplicar padrao de container responsivo em `products-index`, `show-publication` e `show-sale`.
- [ ] Validacao final manual em dispositivo real (iPhone/iPad) com screenshots de confirmacao.
