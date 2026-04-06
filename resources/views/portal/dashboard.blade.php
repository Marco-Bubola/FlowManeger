<x-portal-layout title="Início">

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/portal-dashboard.css') }}">
@endpush

<div class="portal-dashboard-page">
    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        <div class="pph-row1">
            <div class="pph-icon">
                <i class="fas fa-house-chimney"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <i class="fas fa-store text-[8px]"></i>
                    <span>Portal do Cliente</span>
                </div>
                <h1 class="pph-title">Olá, {{ explode(' ', $client->name)[0] }}!</h1>
            </div>
            <div class="hidden sm:flex flex-wrap items-center gap-2 ml-auto">
                <span class="pph-badge info">
                    <i class="fas fa-bag-shopping text-[8px]"></i>
                    {{ $totalSales }} compra{{ $totalSales !== 1 ? 's' : '' }}
                </span>
                <span class="pph-badge success">
                    <i class="fas fa-circle-dollar-sign text-[8px]"></i>
                    R$ {{ number_format($totalPaid, 2, ',', '.') }}
                </span>
                @if($pendingSales > 0)
                <span class="pph-badge warning">
                    <i class="fas fa-hourglass-half text-[8px]"></i>
                    {{ $pendingSales }} pendente{{ $pendingSales !== 1 ? 's' : '' }}
                </span>
                @endif
            </div>
            <a href="{{ route('portal.quotes.create') }}" class="pph-btn">
                <i class="fas fa-circle-plus text-xs"></i>
                Solicitar Orçamento
            </a>
        </div>

        <div class="pph-row2">
            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                Último acesso:
            </span>
            <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400">
                <i class="fas fa-clock text-sky-400 mr-1"></i>
                {{ $client->portal_last_login_at?->format('d/m/Y H:i') ?? 'Primeiro acesso' }}
            </span>
            @if($recentQuotes->count() > 0)
            <span class="pph-badge violet">
                <i class="fas fa-file-invoice text-[8px]"></i>
                {{ $recentQuotes->count() }} orçamento{{ $recentQuotes->count() !== 1 ? 's' : '' }}
            </span>
            @endif
        </div>
    </div>

    {{-- KPI tiles --}}
    @php
        $tiles = [
            ['icon'=>'bag-shopping',   'label'=>'Total de Compras', 'value'=>$totalSales,
             'sub'=>Str::plural('pedido',$totalSales), 'color'=>'sky'],
            ['icon'=>'circle-dollar-sign','label'=>'Total Pago',   'value'=>'R$ '.number_format($totalPaid,2,',','.'),
             'sub'=>'acumulado', 'color'=>'emerald'],
            ['icon'=>'hourglass-half', 'label'=>'Aguardando Pgto.','value'=>$pendingSales,
             'sub'=>Str::plural('pendente',$pendingSales), 'color'=>'amber'],
            ['icon'=>'file-invoice',   'label'=>'Orçamentos',      'value'=>$recentQuotes->count(),
             'sub'=>'últimos registros', 'color'=>'violet'],
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        @foreach($tiles as $t)
        <div class="kpi-tile">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1 truncate">{{ $t['label'] }}</p>
                    <p class="text-xl font-black text-gray-900 dark:text-slate-100 leading-none mb-0.5">{{ $t['value'] }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $t['sub'] }}</p>
                </div>
                <div class="w-9 h-9 bg-{{ $t['color'] }}-100 dark:bg-{{ $t['color'] }}-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-{{ $t['icon'] }} text-{{ $t['color'] }}-600 dark:text-{{ $t['color'] }}-400 text-sm"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Quick actions + content --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-5">

        {{-- Recent Sales (col-span-3) --}}
        <div class="portal-card overflow-hidden lg:col-span-3">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-slate-700">
                <h2 class="font-bold text-sm text-gray-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fas fa-bag-shopping text-sky-500 text-xs"></i>
                    Últimas Compras
                </h2>
                <a href="{{ route('portal.sales') }}" class="text-xs text-sky-600 dark:text-sky-400 font-semibold hover:underline">Ver todas →</a>
            </div>
            @forelse($recentSales as $sale)
            @php
                $sm = ['pending'=>['Pendente','amber'],'paid'=>['Pago','emerald'],'cancelled'=>['Cancelado','red'],'partial'=>['Parcial','blue']];
                [$lbl,$col] = $sm[$sale->status] ?? [$sale->status,'gray'];
            @endphp
            <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors border-b border-gray-50/80 dark:border-slate-700/50 last:border-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-7 h-7 bg-sky-100 dark:bg-sky-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-box text-sky-600 dark:text-sky-400 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-900 dark:text-slate-200">Pedido #{{ $sale->id }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $sale->created_at->format('d/m/Y') }} · {{ $sale->saleItems->count() }} {{ Str::plural('item',$sale->saleItems->count()) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300">{{ $lbl }}</span>
                    <span class="text-xs font-black text-gray-800 dark:text-slate-200">R$ {{ number_format($sale->total_price,2,',','.') }}</span>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <i class="fas fa-bag-shopping text-2xl text-gray-200 dark:text-slate-600 mb-2"></i>
                <p class="text-xs text-gray-400 dark:text-slate-500">Nenhuma compra registrada.</p>
            </div>
            @endforelse
        </div>

        {{-- Recent Quotes (col-span-2) --}}
        <div class="portal-card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-slate-700">
                <h2 class="font-bold text-sm text-gray-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-violet-500 text-xs"></i>
                    Orçamentos
                </h2>
                <a href="{{ route('portal.quotes.create') }}" class="text-xs text-violet-600 dark:text-violet-400 font-semibold hover:underline">+ Novo</a>
            </div>
            @forelse($recentQuotes as $quote)
            @php $col = $quote->status_color; @endphp
            <a href="{{ route('portal.quotes.show', $quote) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors border-b border-gray-50 dark:border-slate-700/50 last:border-0 group">
                <div>
                    <p class="text-xs font-bold text-gray-900 dark:text-slate-200">Orç. #{{ $quote->id }}</p>
                    <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $quote->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="status-badge bg-{{ $col }}-100 dark:bg-{{ $col }}-900/40 text-{{ $col }}-700 dark:text-{{ $col }}-300">{{ $quote->status_label }}</span>
                    <i class="fas fa-chevron-right text-gray-300 dark:text-slate-600 text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>
            @empty
            <div class="px-5 py-10 text-center">
                <i class="fas fa-file-invoice text-2xl text-gray-200 dark:text-slate-600 mb-2"></i>
                <p class="text-xs text-gray-400 dark:text-slate-500 mb-3">Nenhum orçamento ainda.</p>
                <a href="{{ route('portal.quotes.create') }}" class="inline-flex items-center gap-1 text-xs text-violet-600 dark:text-violet-400 font-semibold hover:underline">
                    <i class="fas fa-plus-circle text-[10px]"></i> Solicitar
                </a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Quick access row --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <a href="{{ route('portal.products') }}" class="portal-card p-4 flex flex-col items-center gap-2 text-center hover:border-sky-200 dark:hover:border-sky-700 transition-all group">
            <div class="w-10 h-10 bg-sky-100 dark:bg-sky-900/40 rounded-xl flex items-center justify-center group-hover:bg-sky-500 transition-colors">
                <i class="fas fa-boxes-stacked text-sky-600 dark:text-sky-400 group-hover:text-white transition-colors"></i>
            </div>
            <p class="text-xs font-bold text-gray-700 dark:text-slate-300">Ver Catálogo</p>
        </a>
        <a href="{{ route('portal.quotes.create') }}" class="portal-card p-4 flex flex-col items-center gap-2 text-center hover:border-indigo-200 dark:hover:border-indigo-700 transition-all group">
            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center group-hover:bg-indigo-500 transition-colors">
                <i class="fas fa-circle-plus text-indigo-600 dark:text-indigo-400 group-hover:text-white transition-colors"></i>
            </div>
            <p class="text-xs font-bold text-gray-700 dark:text-slate-300">Novo Pedido</p>
        </a>
        <a href="{{ route('portal.profile') }}" class="portal-card p-4 flex flex-col items-center gap-2 text-center hover:border-violet-200 dark:hover:border-violet-700 transition-all group">
            <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/40 rounded-xl flex items-center justify-center group-hover:bg-violet-500 transition-colors">
                <i class="fas fa-circle-user text-violet-600 dark:text-violet-400 group-hover:text-white transition-colors"></i>
            </div>
            <p class="text-xs font-bold text-gray-700 dark:text-slate-300">Meu Perfil</p>
        </a>
    </div>

    {{-- Profile banner --}}
    @if(! $client->cpf_cnpj || ! $client->phone)
    <div class="flex items-center gap-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-2xl">
        <div class="w-9 h-9 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user-pen text-amber-600 dark:text-amber-400 text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Complete seu perfil</p>
            <p class="text-xs text-amber-600 dark:text-amber-400/80 mt-0.5">Adicione seus dados para agilizar futuros pedidos.</p>
        </div>
        <a href="{{ route('portal.profile') }}" class="flex-shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
            Completar
        </a>
    </div>
    @endif

</div>{{-- /portal-dashboard-page --}}
</x-portal-layout>
