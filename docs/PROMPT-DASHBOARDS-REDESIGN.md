# 📊 PROMPT — Redesign Completo dos Dashboards (FlowManager)

> Documento operacional para reestruturar **todos** os dashboards do FlowManager: mais compactos, mais densos (mais conteúdo por linha), gráficos modernos, e perfeitamente adaptados a iPad e todos os dispositivos. Stack atual: **Laravel 12 · Livewire 3 · Tailwind 4 · ApexCharts (CDN) · Bootstrap Icons**.

---

## 🎯 Objetivo

Transformar os 6 dashboards de "cards grandes e espaçados" para um **cockpit denso, moderno e responsivo**, no estilo de produtos como Stripe Dashboard, Vercel Analytics e Linear:
- Mais informação visível sem scroll.
- Gráficos modernos (sparklines, área com gradiente, donut, heatmap, barras arredondadas).
- Densidade adaptativa por dispositivo (compacto no mobile, denso no desktop/ultrawide).
- Dark/light impecável.

### Dashboards no escopo
| # | Rota | Componente | Arquivo |
|---|------|-----------|---------|
| 1 | `dashboard.index` | DashboardIndex | `dashboard-index.blade.php` (1716 linhas) |
| 2 | `dashboard.cashbook` | DashboardCashbook | `dashboard-cashbook.blade.php` (Financeiro) |
| 3 | `dashboard.products` | DashboardProducts | `dashboard-products.blade.php` |
| 4 | `dashboard.sales` | DashboardSales | `dashboard-sales.blade.php` |
| 5 | `dashboard.clients` | DashboardClientes | `dashboard-clientes.blade.php` |
| 6 | `dashboard.banks` | DashboardBanks | `dashboard-banks.blade.php` |

---

## 🧱 FASE 0 — Design System de Dashboard (base para tudo)

### 0.1 Tokens de densidade (grid responsivo padrão)
Criar a classe utilitária `.dash-grid` que define a densidade por dispositivo. **Esta é a regra mais importante** — todos os blocos a usam.

`resources/css/dashboard.css` (ou bloco `<style>` compartilhado):
```css
/* KPIs — cards pequenos, muitos por linha */
.dash-kpis {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));   /* mobile: 2 */
    gap: 0.6rem;
}
@media (min-width: 480px)  { .dash-kpis { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 768px)  { .dash-kpis { grid-template-columns: repeat(4, 1fr); } }   /* iPad portrait */
@media (min-width: 1280px) { .dash-kpis { grid-template-columns: repeat(6, 1fr); } }   /* desktop */
@media (min-width: 1920px) { .dash-kpis { grid-template-columns: repeat(8, 1fr); gap: 0.75rem; } }

/* Cards de conteúdo (gráficos, listas) — 12 colunas fluidas */
.dash-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);             /* mobile: empilha */
    gap: 0.85rem;
}
@media (min-width: 768px)  { .dash-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }   /* iPad: 2 */
@media (min-width: 1280px) { .dash-grid { grid-template-columns: repeat(12, 1fr); } }            /* desktop: 12-col */

/* Helpers de span no desktop (col-span no grid de 12) */
@media (min-width: 1280px) {
    .dash-col-3  { grid-column: span 3; }
    .dash-col-4  { grid-column: span 4; }
    .dash-col-5  { grid-column: span 5; }
    .dash-col-6  { grid-column: span 6; }
    .dash-col-8  { grid-column: span 8; }
    .dash-col-12 { grid-column: span 12; }
}
```

### 0.2 Componentes Blade reutilizáveis (criar)
```
resources/views/components/dash/
├── kpi.blade.php            → mini-card de KPI (label, valor, delta %, sparkline)
├── card.blade.php           → wrapper de card (título, ícone, slot ações, slot corpo)
├── chart.blade.php          → container de gráfico ApexCharts (id + config via Alpine)
├── stat-pill.blade.php      → pílula de métrica inline
├── list-item.blade.php      → linha de lista (avatar/ícone, título, valor, trend)
└── empty.blade.php          → estado vazio padrão
```

**`<x-dash.kpi>`** — KPI compacto com sparkline e delta:
```blade
@props(['label', 'value', 'icon' => 'bi-graph-up', 'tone' => 'indigo', 'delta' => null, 'spark' => []])
<div class="dash-kpi dash-kpi-{{ $tone }} group">
    <div class="flex items-center justify-between">
        <span class="dash-kpi-ico"><i class="bi {{ $icon }}"></i></span>
        @if(!is_null($delta))
            <span class="dash-kpi-delta {{ $delta >= 0 ? 'up' : 'down' }}">
                <i class="bi bi-arrow-{{ $delta >= 0 ? 'up' : 'down' }}-right"></i>{{ abs($delta) }}%
            </span>
        @endif
    </div>
    <p class="dash-kpi-value">{{ $value }}</p>
    <p class="dash-kpi-label">{{ $label }}</p>
    @if(!empty($spark))
        <div class="dash-kpi-spark" data-spark='@json($spark)'></div>
    @endif
</div>
```

**`<x-dash.card>`** — wrapper padrão (dark/light pronto):
```blade
@props(['title', 'icon' => null, 'tone' => 'slate', 'span' => 'dash-col-6'])
<div {{ $attributes->merge(['class' => "dash-card {$span}"]) }}>
    <div class="dash-card-head">
        <div class="flex items-center gap-2.5 min-w-0">
            @if($icon)<span class="dash-card-ico dash-ico-{{ $tone }}"><i class="bi {{ $icon }}"></i></span>@endif
            <h3 class="dash-card-title">{{ $title }}</h3>
        </div>
        @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
    </div>
    <div class="dash-card-body">{{ $slot }}</div>
</div>
```

### 0.3 CSS base dos componentes (dark/light)
```css
.dash-card {
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(148,163,184,0.18);
    border-radius: 1.1rem;
    box-shadow: 0 8px 24px -12px rgba(15,23,42,0.18);
    padding: 0.9rem 1rem;
    backdrop-filter: blur(12px);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
.dash-card:hover { transform: translateY(-2px); border-color: rgba(139,92,246,0.35); }
.dark .dash-card {
    background: rgba(15,23,42,0.6);
    border-color: rgba(51,65,85,0.5);
}
.dash-card-head { display:flex; align-items:center; justify-content:space-between; gap:.5rem; margin-bottom:.7rem; }
.dash-card-title { font-size:.85rem; font-weight:800; color:#1e293b; }
.dark .dash-card-title { color:#f1f5f9; }
.dash-card-ico { width:1.9rem; height:1.9rem; border-radius:.6rem; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.8rem; }
.dash-ico-indigo  { background:linear-gradient(135deg,#6366f1,#4f46e5); }
.dash-ico-emerald { background:linear-gradient(135deg,#10b981,#059669); }
.dash-ico-amber   { background:linear-gradient(135deg,#f59e0b,#f97316); }
.dash-ico-rose    { background:linear-gradient(135deg,#f43f5e,#ec4899); }
.dash-ico-sky     { background:linear-gradient(135deg,#0ea5e9,#2563eb); }

/* KPI compacto */
.dash-kpi { border-radius:1rem; padding:.7rem .8rem; border:1px solid rgba(148,163,184,0.18); background:rgba(255,255,255,0.8); transition:transform .2s ease; }
.dash-kpi:hover { transform:translateY(-2px); }
.dark .dash-kpi { background:rgba(30,41,59,0.5); border-color:rgba(71,85,105,0.4); }
.dash-kpi-ico { width:1.8rem; height:1.8rem; border-radius:.55rem; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.8rem; }
.dash-kpi-value { font-size:1.15rem; font-weight:900; line-height:1.1; color:#0f172a; margin-top:.35rem; }
.dark .dash-kpi-value { color:#fff; }
.dash-kpi-label { font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#64748b; }
.dash-kpi-delta { font-size:.66rem; font-weight:800; padding:.1rem .35rem; border-radius:999px; }
.dash-kpi-delta.up   { color:#059669; background:rgba(16,185,129,.12); }
.dash-kpi-delta.down { color:#dc2626; background:rgba(239,68,68,.12); }
.dash-kpi-spark { height:32px; margin-top:.4rem; }
```

---

## 📈 FASE 1 — Sistema de Gráficos ApexCharts moderno

### 1.1 Helper único de montagem (`resources/js/dash-charts.js`)
Centralizar a criação de gráficos com tema dark/light automático e presets modernos:
```js
export function dashChart(selector, type, series, opts = {}) {
    const dark = document.documentElement.classList.contains('dark');
    const base = {
        chart: {
            type, height: opts.height ?? 220, fontFamily: 'inherit',
            toolbar: { show: false }, sparkline: { enabled: !!opts.spark },
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
            background: 'transparent',
        },
        theme: { mode: dark ? 'dark' : 'light' },
        grid: { borderColor: dark ? 'rgba(71,85,105,.25)' : 'rgba(148,163,184,.2)', strokeDashArray: 4 },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: type === 'line' ? 3 : 2 },
        colors: opts.colors ?? ['#6366f1','#10b981','#f59e0b','#f43f5e','#0ea5e9'],
        tooltip: { theme: dark ? 'dark' : 'light' },
        ...opts.extra,
    };
    if (type === 'area') {
        base.fill = { type:'gradient', gradient:{ shadeIntensity:1, opacityFrom:.45, opacityTo:.05, stops:[0,90] } };
    }
    if (type === 'bar') {
        base.plotOptions = { bar: { borderRadius: 6, columnWidth: '55%', borderRadiusApplication:'end' } };
    }
    if (type === 'donut') {
        base.plotOptions = { pie: { donut: { size: '72%', labels:{ show:true, total:{ show:true } } } } };
        base.legend = { position:'bottom', fontSize:'12px' };
    }
    const el = document.querySelector(selector);
    if (!el || typeof ApexCharts === 'undefined') return null;
    const chart = new ApexCharts(el, { ...base, series });
    chart.render();
    return chart;
}

// Re-render no toggle de tema
window.addEventListener('flowmanager:theme-changed', () => {
    // destruir e remontar charts registrados, OU usar chart.updateOptions({ theme })
});
```

### 1.2 Tipos de gráfico por contexto (presets modernos)
| Métrica | Gráfico | Preset |
|---------|---------|--------|
| Receita/fluxo ao longo do tempo | **Área com gradiente** | `area`, smooth, gradient fill |
| Comparação de categorias | **Barras arredondadas** | `bar`, borderRadius 6 |
| Distribuição (% por categoria/banco) | **Donut** | `donut`, size 72% |
| KPI trend (mini) | **Sparkline** | `area` + `sparkline:true` |
| Atividade por dia/hora | **Heatmap** | `heatmap` |
| Metas/progresso | **Radial bar** | `radialBar` |
| Comparativo 2 séries | **Linha dupla** | `line`, 2 cores |

### 1.3 Re-render no theme toggle
Os gráficos devem reagir ao evento `flowmanager:theme-changed` (já disparado pelo botão dark/light na sidebar). Guardar refs dos charts e chamar `chart.updateOptions({ theme:{ mode }, ... })`.

---

## 🗂️ FASE 2 — Layout de cada dashboard

> Regra geral: **cada dashboard começa com uma faixa de KPIs (`.dash-kpis`) e depois um grid de 12 colunas (`.dash-grid`)** com cards de gráfico/lista usando `.dash-col-*`.

### 2.1 Dashboard Geral (`dashboard-index`)
```
┌──────────────────────────────────────────────────────────┐
│ HEADER compacto (saudação + período + ações)             │
├──────────────────────────────────────────────────────────┤
│ KPIs (.dash-kpis): Receita · Vendas · Ticket · Pendente  │
│                    · Produtos · Clientes · Caixa · Lucro │  (8 no desktop)
├──────────────────────────────────────────────────────────┤
│ .dash-grid:                                              │
│  [Receita 30d — área gradiente]  dash-col-8              │
│  [Distribuição vendas — donut]   dash-col-4              │
│  [Top produtos — lista+barra]    dash-col-6              │
│  [Atividades recentes]           dash-col-6              │
│  [Metas/objetivos — radialBar]   dash-col-4              │
│  [Alertas]                       dash-col-4              │
│  [Calendário/agenda]             dash-col-4              │
└──────────────────────────────────────────────────────────┘
```

### 2.2 Financeiro (`dashboard-cashbook`)
- KPIs: Saldo · Entradas · Saídas · A receber · A pagar · Reservas.
- `[Fluxo de caixa mensal — área dupla (entrada vs saída)]` col-8
- `[Saúde do orçamento — radialBar]` col-4
- `[Despesas por categoria — donut]` col-6
- `[Últimos lançamentos — lista]` col-6
- `[Projeção 3 meses — linha]` col-12

### 2.3 Produtos (`dashboard-products`)
- KPIs: Total produtos · Em estoque · Estoque baixo · Sem estoque · Valor estoque · Mais vendido.
- `[Estoque por categoria — barras]` col-6
- `[Curva ABC / top giro — barras horizontais]` col-6
- `[Produtos parados — lista]` col-6
- `[Margem média por categoria — donut]` col-6

### 2.4 Vendas (`dashboard-sales`)
- KPIs: Vendas hoje · Semana · Mês · Ticket médio · Conversão · Cancelamentos.
- `[Vendas por dia — área]` col-8
- `[Por forma de pagamento — donut]` col-4
- `[Heatmap dia×hora]` col-6
- `[Ranking clientes — lista]` col-6

### 2.5 Clientes (`dashboard-clientes`)
- KPIs: Total · Novos no mês · Ativos · Inadimplentes · LTV médio · Recompra.
- `[Novos clientes por mês — barras]` col-8
- `[Segmentação — donut]` col-4
- `[Top clientes por receita — lista]` col-6
- `[Mapa por cidade/estado — barras]` col-6

### 2.6 Bancos (`dashboard-banks`)
- KPIs por banco (saldo) + total consolidado.
- `[Saldo consolidado — área]` col-8
- `[Distribuição por banco — donut]` col-4
- `[Faturas a vencer — lista]` col-6
- `[Gastos por cartão — barras]` col-6

---

## 📱 FASE 3 — Responsividade (iPad e todos os dispositivos)

### 3.1 Breakpoints e densidade
| Device | KPIs/linha | Cards de gráfico | Altura gráfico |
|--------|-----------|------------------|----------------|
| Mobile (<480) | 2 | 1 (empilha) | 180px |
| iPhone 15 | 2 | 1 | 180px |
| iPad portrait (768–1024) | 4 | 2 | 200px |
| iPad landscape | 6 | 12-col (2–3 cards/linha) | 220px |
| Notebook (1280–1535) | 6 | 12-col | 240px |
| Desktop (1536–1919) | 6–8 | 12-col | 260px |
| Ultrawide (≥1920) | 8 | 12-col (mais cards/linha) | 280px |

### 3.2 Arquivos CSS separados (convenção do projeto)
Criar, por dashboard, em `public/assets/css/responsive/`:
```
dashboard-index-{mobile,iphone15,ipad-portrait,ipad-landscape,notebook,ultrawide}.css
```
Cada um ajusta `.dash-kpis`, `.dash-grid`, `.dash-col-*` e alturas de gráfico para aquele device. **Reaproveitar o mesmo padrão dos `add-products-*.css`.**

### 3.3 iPad: regra-chave
No iPad portrait, **forçar 2 cards de gráfico por linha** (não 1), KPIs em 4 colunas, e reduzir a altura dos gráficos para 200px — para caber mais sem scroll.

---

## ⚡ FASE 4 — Performance e dados

### 4.1 Queries
- Cada KPI/gráfico deve vir de um **método computed cacheado** (`Cache::remember("dash:{user}:receita30d", 300, ...)`).
- Evitar N+1: usar `withSum`, `withCount`, agregações SQL (`selectRaw`).
- Para séries temporais, agrupar no banco: `selectRaw('DATE(created_at) d, SUM(total_price) t')->groupBy('d')`.

### 4.2 Loading states (skeleton)
- Enquanto Livewire carrega, mostrar **skeleton shimmer** nos cards (`wire:loading`).
- `wire:init` para carregar dados pesados após o primeiro paint.

### 4.3 Atualização
- Botão "Atualizar" + opção de auto-refresh (Livewire `wire:poll.60s` opcional por dashboard).

---

## 🔌 FASE 5 — APIs e Bibliotecas recomendadas

### Já no projeto (manter/expandir)
- **ApexCharts** — manter como engine principal (área, barras, donut, heatmap, radialBar).

### Adicionar (opcional, alto valor)
| Lib / API | Uso | Como |
|-----------|-----|------|
| **ApexCharts Heatmap/RadialBar** | atividade dia×hora, metas | já incluso no ApexCharts |
| **Day.js** | manipular datas no front (períodos) | `npm i dayjs` |
| **CountUp.js** | animar números dos KPIs subindo | `npm i countup.js` |
| **Tippy.js** | tooltips ricos nos gráficos/KPIs | `npm i tippy.js` |
| **maplibre-gl** ou **SVG BR map** | mapa de clientes por estado | dashboard de clientes |
| **Laravel Cache (Redis)** | cachear agregações pesadas | backend |
| **Brasil API** | enriquecer dados (CEP→região) | já citado no roadmap geral |
| **Intl.NumberFormat** | formatação de moeda no front | nativo (sem lib) |

### Animações de número (CountUp) — exemplo
```js
import { CountUp } from 'countup.js';
document.querySelectorAll('[data-countup]').forEach(el => {
    new CountUp(el, parseFloat(el.dataset.countup), { duration: 1.2, separator: '.', decimal: ',' }).start();
});
```

---

## 🎨 FASE 6 — Polimento visual

- **Glassmorphism** sutil nos cards (`backdrop-blur`), bordas finas, sombras suaves.
- **Gradientes** apenas nos ícones e barras de destaque (não no fundo dos cards — cansa).
- **Micro-animações**: entrada `fade-rise` escalonada, hover lift, sparkline animada.
- **Cores semânticas**: verde=positivo, vermelho=negativo, âmbar=atenção, indigo=neutro/destaque.
- **Tipografia**: valores em `font-black`, labels `uppercase text-xs`, tom `slate-500`.
- **Dark/light**: todo card com variante `dark:` testada; gráficos via `theme.mode`.
- **Acessibilidade**: contraste AA, `aria-label` nos gráficos, foco visível.

---

## ✅ Checklist de execução (por dashboard)
- [ ] Refatorar para usar `.dash-kpis` + `.dash-grid` + `<x-dash.*>`
- [ ] KPIs com sparkline + delta % vs período anterior
- [ ] Gráficos via `dashChart()` (tema automático)
- [ ] 6 arquivos CSS responsivos (mobile→ultrawide)
- [ ] iPad portrait: 4 KPIs/linha + 2 gráficos/linha
- [ ] Skeleton loading (`wire:loading`)
- [ ] Queries agregadas + cache 5min
- [ ] Re-render de gráficos no theme toggle
- [ ] Dark/light validado
- [ ] CountUp nos números principais
- [ ] Botão atualizar + (opcional) auto-refresh

---

## 🗺️ Ordem sugerida de implementação
1. **FASE 0 + 1** (design system + helper de charts) — base reutilizável.
2. **Dashboard Geral** (vitrine, valida o padrão).
3. **Financeiro** e **Vendas** (mais gráficos).
4. **Produtos**, **Clientes**, **Bancos**.
5. **FASE 3** (CSS responsivo por device) em paralelo a cada dashboard.
6. **FASE 4–6** (performance, libs, polimento) ao final.

---

**Meta final:** dashboards densos como um cockpit profissional, com gráficos modernos, carregando rápido, lindos no claro e no escuro, e perfeitos do iPhone ao ultrawide — reaproveitando ApexCharts (já no projeto) + componentes Blade `<x-dash.*>` + CSS responsivo no padrão já usado em `add-products-*.css`.
