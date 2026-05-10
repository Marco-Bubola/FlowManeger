---
name: css-architecture
description: "Use when: criando novos arquivos CSS, adicionando estilos a páginas existentes, organizando estilos de componentes, definindo variáveis de cor/tema, ou trabalhando com dark mode no FlowManager."
---

# Arquitetura CSS — FlowManager

## Estrutura de Pastas

```
public/assets/css/
├── flow-theme.css              ← Variáveis globais CSS (base), NÃO adicionar regras de componente aqui
├── produtos-extra.css          ← Legado (4187 linhas) — não editar diretamente
├── produtos.css                ← Estilos específicos de produto-card
├── create-sale.css             ← Estilos página criar venda
├── edit-sale.css               ← Estilos página editar venda
├── settings.css                ← Design system das páginas de Settings
├── flatpickr-custom.css        ← Overrides do datepicker Flatpickr
├── icon-category.css           ← Ícones de categoria
├── icon-overrides.css          ← Overrides de ícones Bootstrap/FA
├── portal/                     ← CSS do portal do cliente
└── responsive/                 ← TODOS os arquivos responsivos (ver skill: responsiveness)
```

## Variáveis de Tema (flow-theme.css)

```css
:root {
    /* Cores de acento — definidas via JS no carregamento (localStorage) */
    --s-accent:       #9333ea;   /* cor primária do tema */
    --s-accent-dark:  #7c3aed;
    --s-accent-light: #d8b4fe;
    --s-accent-faint: #faf5ff;
    --s-accent-rgb:   147,51,234;

    /* Fluidos */
    --app-fluid-padding: clamp(0.65rem, 1.2vw, 1rem);
    --app-fluid-gap:     clamp(0.5rem, 1vw, 0.9rem);
    --app-density-font:  1;
}
```

**Temas disponíveis:** `purple`, `indigo`, `blue`, `teal`, `emerald`, `rose`, `orange`, `amber`

## Ordem de Carregamento (head.blade.php)

1. `@vite(['resources/css/app.css', 'resources/js/app.js'])` — Tailwind CSS
2. `@fluxAppearance` — Flux UI
3. `flow-theme.css` — variáveis globais
4. `@stack('styles')` — CSS específico da página (via `@push('styles')`)
5. Arquivos `responsive/*.css` — carregados automaticamente pelo `head.blade.php`

> **CRÍTICO:** CSS de página SEMPRE via `@push('styles')`. Nunca usar `<style>` inline em templates.

## Convenções de Nomenclatura

### Arquivos de Página
```
public/assets/css/<page-slug>.css
```
Exemplos: `create-sale.css`, `edit-sale.css`, `settings.css`

### Arquivos Responsivos por Página
```
public/assets/css/responsive/<page-slug>-<breakpoint>.css
```

### Classes de Escopo de Página
```css
/* Prefixo sempre igual ao slug da página */
.dashboard-index-page { }
.create-sale-page { }
.products-index-page { }
```

## Dark Mode

**Abordagem 1:** Classes Tailwind (preferida para componentes inline)
```html
<div class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
```

**Abordagem 2:** CSS dedicado (para arquivos `.css`)
```css
.minha-classe { background: #fff; color: #1e293b; }
.dark .minha-classe { background: #1e293b; color: #f1f5f9; }
```

> NUNCA usar `style="background:#fff"` em cards/tiles de settings — quebra dark mode. Usar classes em `settings.css`.

## Design System de Cores (Tailwind)

Paleta base do app:
```
slate-50/100/200/300/400 → backgrounds, borders, muted text
slate-600/700/800/900    → text, dark backgrounds
white / dark:slate-800   → card backgrounds
```

Gradientes padrão de ícones:
```
from-blue-500 to-indigo-600     (blue)
from-green-500 to-emerald-600   (green)
from-purple-500 to-pink-600     (purple)
from-orange-500 to-red-600      (orange)
from-cyan-500 to-blue-600       (cyan)
from-pink-500 to-rose-600       (pink)
```

## Padrão de Card Componente

```css
/* Card base */
.minha-page .card-item {
    background: #fff;
    border-radius: 1rem;       /* rounded-2xl */
    padding: 1.5rem;           /* p-6 */
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / .1);
    border: 1px solid #e2e8f0; /* border-slate-200 */
    transition: box-shadow .3s, transform .2s;
}
.dark .minha-page .card-item {
    background: #1e293b;       /* slate-800 */
    border-color: #334155;     /* slate-700 */
}
```

## Entradas de Formulário

```css
/* Input padrão */
.minha-page input,
.minha-page select,
.minha-page textarea {
    border-radius: .5rem;
    border: 1px solid #cbd5e1; /* slate-300 */
    padding: .5rem .75rem;
    transition: border-color .2s, box-shadow .2s;
}
.minha-page input:focus {
    outline: none;
    border-color: var(--s-accent);
    box-shadow: 0 0 0 3px rgba(var(--s-accent-rgb), .15);
}
.dark .minha-page input {
    background: #1e293b;
    border-color: #475569;
    color: #f1f5f9;
}
```

## Loading States (Livewire)

> **REGRA CRÍTICA:** NÃO usar `wire:loading` / `wire:loading.class` como seletor CSS estático.
> O Livewire controla visibilidade via `display:none` por JS.
> Usar SOMENTE classes via `wire:loading.class="classe-ativa"` nos templates.

```blade
{{-- ✅ CORRETO --}}
<div wire:loading.class="opacity-50 pointer-events-none">

{{-- ❌ ERRADO — CSS estático não respeita estado de loading --}}
<div wire:loading>
```

## Ícones Disponíveis

- **Bootstrap Icons** (`bi bi-*`) — uso geral
- **Font Awesome 6** (`fas fa-*`, `far fa-*`, `fab fa-*`) — uso geral
- Carregar via CDN já incluído no `head.blade.php`

## Criando CSS para Nova Página (Checklist)

- [ ] Criar `public/assets/css/<page-slug>.css` com comentário de cabeçalho
- [ ] Carregar via `@push('styles')` no template
- [ ] Criar 6 arquivos responsivos em `responsive/`
- [ ] Escopo todas as regras com `.<page-slug>-page`
- [ ] Adicionar variante dark mode para cada bloco principal
- [ ] Não duplicar variáveis — usar `var(--s-accent)` para cor de acento
