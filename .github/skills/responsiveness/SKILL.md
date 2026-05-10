---
name: responsiveness
description: "Use when: criando ou corrigindo responsividade de páginas, adicionando novos breakpoints, ajustando layout mobile/tablet/desktop, ou criando arquivos CSS responsivos por página no FlowManager."
---

# Responsividade — FlowManager

## Estrutura de Arquivos Responsivos

Cada página tem **6 arquivos CSS responsivos** dedicados em:
```
public/assets/css/responsive/<page-slug>-<breakpoint>.css
```

### Sufixos por breakpoint
| Sufixo | Dispositivo | Media Query |
|--------|-------------|-------------|
| `-mobile.css` | Mobile geral | `@media (max-width: 768px)` |
| `-iphone15.css` | iPhone/small phones | `@media (max-width: 450px)`, `@media (max-width: 400px)`, `@media (max-width: 393px)` |
| `-ipad-portrait.css` | iPad portrait | `@media (min-width: 768px) and (max-width: 1024px)` |
| `-ipad-landscape.css` | iPad landscape | `@media (min-width: 1025px) and (max-width: 1366px)` |
| `-notebook.css` | Notebooks | `@media (min-width: 1320px) and (max-width: 1400px)` |
| `-ultrawide.css` | Ultrawide | `@media (min-width: 2400px)` |

### Arquivos globais (não tocar sem motivo)
- `responsive-mobile.css` — regras globais ≤768px
- `responsive-iphone.css` — regras globais ≤450px, ≤400px, 393×852
- `responsive-ipad.css` — regras globais 768-1366px
- `responsive-notebook.css` — regras globais 1320-1400px
- `responsive-fullhd.css` — regras globais ≥1800px
- `responsive-ultrawide.css` — regras globais ≥2400px

## Como Carregar os CSS Responsivos numa Página

No template Blade da página, usar `@push('styles')` para carregar os 6 arquivos:

```blade
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/responsive/<page-slug>-mobile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/<page-slug>-iphone15.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/<page-slug>-ipad-portrait.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/<page-slug>-ipad-landscape.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/<page-slug>-notebook.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/<page-slug>-ultrawide.css') }}">
@endpush
```

## Classe Wrapper Padrão de Página

Toda página usa esse wrapper no elemento raiz:

```html
<div class="w-full h-screen min-h-screen app-viewport-fit <page-slug>-page">
```

- `app-viewport-fit` → definido em `flow-theme.css` (width:100%, min-height:100dvh, overflow-x:hidden)
- `<page-slug>-page` → classe de escopo para os CSS responsivos, ex: `dashboard-index-page`

## Regras de Escopo CSS

Sempre escopar regras dentro da classe da página para evitar colisão:

```css
/* ✅ CORRETO */
.dashboard-index-page .card { ... }

/* ❌ ERRADO — escopo global */
.card { ... }
```

## Sidebar e Conteúdo Principal

- Sidebar padrão: **280px** (desktop)
- Tablet (768-1366px): sidebar **240px**, `#mainContent` com `margin-left: 240px`
- Mobile: sidebar colapsada, `#mainContent` com `margin-left: 0`
- JS detecta `<=1024px` para colapsar sidebar automaticamente

## Variáveis CSS Responsivas Globais (flow-theme.css)

```css
--app-fluid-padding: clamp(0.65rem, 1.2vw, 1rem);
--app-fluid-gap:     clamp(0.5rem, 1vw, 0.9rem);
--app-density-font:  1;
```

## Tailwind Breakpoints Customizados (tailwind.config.js)

```js
screens: {
  '3xl': '1920px',    // Full HD
  '4xl': '2560px',    // 2K/QHD
  '5xl': '3440px',    // Ultrawide
  'ultrawind': '2498px',
}
```
Padrão Tailwind usado: `sm` (640px), `md` (768px), `lg` (1024px), `xl` (1280px), `2xl` (1536px).

## Template de Arquivo CSS Responsivo

```css
/* ═══════════════════════════════════════════
   FLOWMANAGER — <PAGE-SLUG> — <BREAKPOINT>
   <Descrição do dispositivo alvo>
   ═══════════════════════════════════════════ */

/* ──────────────── MOBILE (≤768px) ──────────────── */
@media (max-width: 768px) {
    .<page-slug>-page .elemento { }
}

/* ──────────────── SMALL MOBILE (≤450px) ──────────────── */
@media (max-width: 450px) {
    .<page-slug>-page .elemento { }
}
```

## Boas Práticas

- NUNCA adicionar `style=""` inline para responsividade; usar arquivos CSS dedicados.
- Usar `!important` com moderação — apenas quando herança de `produtos-extra.css` precisar ser sobrescrita.
- `produtos-extra.css` (legado, 4187 linhas) — evitar editar; criar overrides no arquivo responsivo da página.
- Para novo módulo: criar 6 arquivos responsivos desde o início.
- Verificar dark mode em CADA breakpoint: usar `dark:` Tailwind ou `.dark .elemento {}` no CSS.
