---
name: blade-components
description: "Use when: criando novos componentes Blade, adicionando props a componentes existentes, construindo headers de página, cards, modais, filtros, ou qualquer elemento de UI reutilizável no FlowManager."
---

# Blade Components — FlowManager

## Localização dos Componentes

```
resources/views/components/
├── modern-header.blade.php      ← Header de página padrão
├── stat-card.blade.php          ← Card de estatística/KPI
├── section-header.blade.php     ← Cabeçalho de seção
├── confirmation-modal.blade.php ← Modal de confirmação
├── toast-notifications.blade.php
├── loading-overlay.blade.php
├── file-upload-zone.blade.php
├── currency-input.blade.php
├── quantity-input.blade.php
└── settings/                    ← Componentes exclusivos de settings
```

Usar via `<x-nome-do-componente />` nos templates.

## Estrutura de Arquivo de Componente

```blade
{{-- Comentário descritivo do componente --}}
@props([
    'propObrigatoria',
    'propComDefault' => 'valor-padrao',
    'propNullavel'   => null,
])

@php
    // Lógica PHP para computar classes dinâmicas
    $classesDinamicas = match($propObrigatoria) {
        'opcao1' => 'classe-a classe-b',
        default  => 'classe-c',
    };
@endphp

<div class="componente-wrapper {{ $classesDinamicas }}">
    {{ $slot }}
</div>
```

## Componente: Modern Header (`<x-modern-header>`)

Header padrão de todas as páginas internas. Props:

```blade
<x-modern-header
    icon="fas fa-chart-line"
    title="Título da Página"
    subtitle="Descrição curta"
    :breadcrumb="[
        ['icon' => 'fas fa-home', 'url' => route('dashboard'), 'label' => 'Início'],
        ['label' => 'Título Atual'],
    ]"
    gradient="from-indigo-500 via-purple-500 to-pink-500"
>
    {{-- Slot para botões de ação (canto direito) --}}
    <x-slot name="actions">
        <button class="btn-primary">Ação</button>
    </x-slot>
</x-modern-header>
```

**Props disponíveis:**
| Prop | Default | Descrição |
|------|---------|-----------|
| `icon` | `fas fa-chart-line` | Classe de ícone FA/Bootstrap |
| `title` | `'Título'` | Título principal |
| `subtitle` | `null` | Subtítulo (aceita HTML) |
| `breadcrumb` | `[]` | Array de itens: `['icon', 'url'?, 'label', 'iconColor'?]` |
| `gradient` | `from-indigo-500 via-purple-500 to-pink-500` | Gradiente do ícone e line |
| `bg` | bg branco/indigo claro | Gradiente do fundo do header |
| `iconBg` | igual ao `gradient` | Gradiente do fundo do ícone |
| `iconColor` | `text-white` | Cor do ícone |
| `ringColor` | `ring-white/50` | Cor do ring do ícone |

## Componente: Stat Card (`<x-stat-card>`)

```blade
<x-stat-card
    title="Total de Vendas"
    value="R$ 12.450,00"
    icon="bi bi-bag-check"
    color="blue"
    :trend="12.5"
    subtitle="vs. mês anterior"
>
    {{-- Conteúdo extra abaixo do valor (opcional) --}}
</x-stat-card>
```

**Cores disponíveis:** `blue`, `green`, `purple`, `orange`, `cyan`, `pink`

**Grade de stat cards:**
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <x-stat-card ... />
    <x-stat-card ... />
</div>
```

## Padrão de Layout de Página (Livewire + Blade)

```blade
{{-- resources/views/livewire/<modulo>/<page>.blade.php --}}
<div class="w-full min-h-screen app-viewport-fit <page-slug>-page">

    {{-- Header da página --}}
    <x-modern-header icon="..." title="..." :breadcrumb="[...]">
        <x-slot name="actions">...</x-slot>
    </x-modern-header>

    {{-- Conteúdo principal --}}
    <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Cards de stats (se aplicável) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            ...
        </div>

        {{-- Seção de conteúdo --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            ...
        </div>

    </div>
</div>
```

## Padrão de Modal de Confirmação

```blade
<x-confirmation-modal wire:model="showDeleteModal">
    <x-slot name="title">Confirmar Exclusão</x-slot>
    <x-slot name="content">
        Tem certeza que deseja excluir este item?
    </x-slot>
    <x-slot name="footer">
        <button wire:click="$set('showDeleteModal', false)" class="btn-secondary">
            Cancelar
        </button>
        <button wire:click="delete" class="btn-danger">
            Excluir
        </button>
    </x-slot>
</x-confirmation-modal>
```

## Classes de Botão Padrão (Tailwind)

```html
<!-- Primário -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium rounded-xl shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200">
    <i class="fas fa-plus"></i> Novo
</button>

<!-- Secundário -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 font-medium rounded-xl hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200">
    <i class="fas fa-filter"></i> Filtrar
</button>

<!-- Perigo -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white font-medium rounded-xl hover:bg-red-600 transition-all duration-200">
    <i class="fas fa-trash"></i> Excluir
</button>
```

## Padrão de Tabela

```blade
<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Coluna</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($items as $item)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->campo }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

## Padrão de Filtros / Busca

```blade
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    {{-- Busca --}}
    <div class="relative flex-1">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Buscar..."
            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 outline-none transition-all"
        >
    </div>

    {{-- Select de filtro --}}
    <select wire:model.live="filtro" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-white outline-none">
        <option value="">Todos</option>
        <option value="ativo">Ativo</option>
    </select>
</div>
```

## Boas Práticas

- Always usar `@props([])` no topo — nunca acessar `$attributes` diretamente sem declarar props
- Nomes de componentes em `kebab-case`: `<x-modern-header>`, `<x-stat-card>`
- Evitar lógica complexa no template — mover para `@php` ou para o Livewire component
- Dark mode obrigatório em todo componente novo: `dark:bg-*`, `dark:text-*`, `dark:border-*`
- Ícones: preferir Bootstrap Icons (`bi bi-*`) para ações, Font Awesome (`fas fa-*`) para decorativos
