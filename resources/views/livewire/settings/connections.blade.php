<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public array $stats = [];

    public function mount(): void
    {
        $uid = Auth::id();

        $mlToken = DB::table('mercadolivre_tokens')
            ->where('user_id', $uid)
            ->where('is_active', 1)
            ->latest('updated_at')
            ->first();

        $shopeeToken = DB::table('shopee_tokens')
            ->where('user_id', $uid)
            ->where('is_active', 1)
            ->latest('updated_at')
            ->first();

        $googleUser = Auth::user()->google_id ?? null;

        $this->stats = [
            'ml_connected'     => !is_null($mlToken),
            'ml_expires'       => $mlToken?->expires_at,
            'shopee_connected'  => !is_null($shopeeToken),
            'shopee_shop_id'    => $shopeeToken?->shop_id ?? null,
            'google_connected'  => !is_null($googleUser),
        ];
    }
}; ?>

<section class="settings-connections-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

        <div class="s-pg-grid">
        <div class="s-col-main">

        {{-- CARD: Resumo --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Integrações conectadas</p>
                        <p class="settings-card-desc">Status das suas conexões com plataformas externas</p>
                    </div>
                </div>
                @php
                    $connCount = collect([$stats['ml_connected'], $stats['shopee_connected'], $stats['google_connected']])->filter()->count();
                @endphp
                <span class="s-badge {{ $connCount > 0 ? 's-badge-success' : '' }}">{{ $connCount }} / 3 ativas</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;padding:0 1.25rem 1.25rem">
                @foreach([
                    ['Mercado Livre', $stats['ml_connected'], 'rgba(255,233,0,.15)', '#FFD700'],
                    ['Shopee', $stats['shopee_connected'], 'rgba(238,77,45,.12)', '#EE4D2D'],
                    ['Google', $stats['google_connected'], 'rgba(66,133,244,.12)', '#4285F4'],
                ] as [$n,$c,$bg,$col])
                <div class="settings-info-tile" style="text-align:center;background:{{ $bg }};border-color:transparent">
                    <div style="width:2rem;height:2rem;border-radius:50%;background:{{ $c ? '#10b98122' : '#f1f5f9' }};color:{{ $c ? '#10b981' : '#94a3b8' }};display:flex;align-items:center;justify-content:center;margin:0 auto .5rem">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:.85rem;height:.85rem">
                            @if($c)
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            @endif
                        </svg>
                    </div>
                    <p class="settings-info-tile-label">{{ $n }}</p>
                    <p class="settings-info-tile-val" style="font-size:.78rem;color:{{ $c ? '#10b981' : '#94a3b8' }}">{{ $c ? 'Conectado' : 'Desconectado' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- GRID: cards de integração --}}
        <div class="s-conns-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem">

            {{-- Mercado Livre --}}
            <div class="s-conn-card {{ $stats['ml_connected'] ? 'connected' : '' }}">
                <div class="s-conn-top">
                    <div class="s-conn-logo" style="background:linear-gradient(135deg,#FFD700,#FFA500)">
                        <svg viewBox="0 0 40 40" fill="none" style="width:1.5rem;height:1.5rem"><ellipse cx="20" cy="20" rx="20" ry="20" fill="#FFD700"/><path d="M10 20 Q15 14 20 20 Q25 26 30 20" stroke="#3483FA" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>
                    </div>
                    <div class="s-conn-info" style="flex:1">
                        <h3>Mercado Livre</h3>
                        <p>Marketplace líder na América Latina</p>
                    </div>
                    @if($stats['ml_connected'])
                        <span class="s-badge s-badge-success">Conectado</span>
                    @else
                        <span class="s-badge">Desconectado</span>
                    @endif
                </div>
                <div class="s-conn-body">
                    <div class="s-conn-meta">
                        @if($stats['ml_connected'])
                            <span class="s-conn-chip s-conn-chip--success">
                                <svg viewBox="0 0 6 6" fill="currentColor" style="width:.4rem;height:.4rem"><circle cx="3" cy="3" r="3"/></svg>
                                Token ativo
                            </span>
                            @if($stats['ml_expires'])
                            <span class="s-conn-chip">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.7rem;height:.7rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Expira {{ \Carbon\Carbon::parse($stats['ml_expires'])->diffForHumans() }}
                            </span>
                            @endif
                        @else
                            <span class="s-conn-chip s-conn-chip--warning">Não conectado</span>
                        @endif
                        <span class="s-conn-chip">Vendas · Publicações · Pedidos</span>
                    </div>
                    <div class="s-conn-actions">
                        @if($stats['ml_connected'])
                            <a href="{{ route('mercadolivre.settings') }}" class="s-conn-btn s-conn-btn--primary">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.8rem;height:.8rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                Configurar
                            </a>
                            <a href="{{ route('mercadolivre.publications') }}" class="s-conn-btn">
                                Ver publicações
                            </a>
                        @else
                            <a href="{{ route('mercadolivre.auth.connect') }}" class="s-conn-btn s-conn-btn--primary">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.8rem;height:.8rem"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                                Conectar
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Shopee --}}
            <div class="s-conn-card {{ $stats['shopee_connected'] ? 'connected' : '' }}">
                <div class="s-conn-top">
                    <div class="s-conn-logo" style="background:linear-gradient(135deg,#EE4D2D,#ff7043)">
                        <svg viewBox="0 0 40 40" fill="none" style="width:1.5rem;height:1.5rem"><circle cx="20" cy="20" r="20" fill="#EE4D2D"/><path d="M14 16h12M16 22h8M13 13l2 10h10l2-10" stroke="white" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </div>
                    <div class="s-conn-info" style="flex:1">
                        <h3>Shopee</h3>
                        <p>Marketplace de comércio eletrônico</p>
                    </div>
                    @if($stats['shopee_connected'])
                        <span class="s-badge s-badge-success">Conectado</span>
                    @else
                        <span class="s-badge">Desconectado</span>
                    @endif
                </div>
                <div class="s-conn-body">
                    <div class="s-conn-meta">
                        @if($stats['shopee_connected'])
                            <span class="s-conn-chip s-conn-chip--success">
                                <svg viewBox="0 0 6 6" fill="currentColor" style="width:.4rem;height:.4rem"><circle cx="3" cy="3" r="3"/></svg>
                                Loja ativa
                            </span>
                            @if($stats['shopee_shop_id'])
                            <span class="s-conn-chip">Shop ID: {{ $stats['shopee_shop_id'] }}</span>
                            @endif
                        @else
                            <span class="s-conn-chip s-conn-chip--warning">Não conectado</span>
                        @endif
                        <span class="s-conn-chip">Produtos · Publicações</span>
                    </div>
                    <div class="s-conn-actions">
                        @if($stats['shopee_connected'])
                            <a href="{{ route('shopee.settings') }}" class="s-conn-btn s-conn-btn--orange">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.8rem;height:.8rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                Configurar
                            </a>
                            <a href="{{ route('shopee.publications') }}" class="s-conn-btn">
                                Ver publicações
                            </a>
                        @else
                            <a href="{{ route('shopee.auth.connect') }}" class="s-conn-btn s-conn-btn--orange">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.8rem;height:.8rem"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                                Conectar
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Google --}}
            <div class="s-conn-card {{ $stats['google_connected'] ? 'connected' : '' }}">
                <div class="s-conn-top">
                    <div class="s-conn-logo" style="background:#f1f5f9">
                        <svg viewBox="0 0 24 24" style="width:1.4rem;height:1.4rem" fill="none"><path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 1 1 0-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0 0 12.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013Z" fill="#4285F4"/></svg>
                    </div>
                    <div class="s-conn-info" style="flex:1">
                        <h3>Google</h3>
                        <p>Login único com sua conta Google</p>
                    </div>
                    @if($stats['google_connected'])
                        <span class="s-badge s-badge-success">Vinculado</span>
                    @else
                        <span class="s-badge">Não vinculado</span>
                    @endif
                </div>
                <div class="s-conn-body">
                    <div class="s-conn-meta">
                        @if($stats['google_connected'])
                            <span class="s-conn-chip" style="background:#eff6ff;color:#1e40af;border-color:#bfdbfe">Login Google ativo</span>
                        @else
                            <span class="s-conn-chip">Vincule para login rápido</span>
                        @endif
                        <span class="s-conn-chip">Autenticação · OAuth 2.0</span>
                    </div>
                    <div class="s-conn-actions">
                        @if($stats['google_connected'])
                            <button type="button" class="s-conn-btn" style="opacity:.55;cursor:not-allowed" disabled>
                                Conta vinculada
                            </button>
                        @else
                            <a href="{{ route('login') }}?provider=google" class="s-conn-btn s-conn-btn--blue">
                                <svg viewBox="0 0 24 24" style="width:.8rem;height:.8rem" fill="currentColor"><path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 1 1 0-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0 0 12.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013Z"/></svg>
                                Vincular Google
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Webhook / API (em breve) --}}
            @foreach([
                ['API Pública','Integre o FlowManager com seus sistemas','Breve','rgba(99,102,241,.08)','#6366f1','Chave de API · REST · JSON'],
                ['Webhooks','Receba notificações em tempo real no seu servidor','Breve','rgba(16,185,129,.08)','#10b981','Events · POST · Payload'],
                ['Zapier','Automatize fluxos de trabalho com 5000+ apps','Breve','rgba(245,158,11,.08)','#f59e0b','Automação · Triggers'],
            ] as [$nm,$desc,$badge,$bg,$col,$tags])
            <div class="s-conn-card" style="border-color:#e2e8f0;background:{{ $bg }};opacity:.8">
                <div class="s-conn-top" style="border-color:#f1f5f9">
                    <div class="s-conn-logo" style="background:{{ str_replace('.08','.15',$bg) }};color:{{ $col }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.1rem;height:1.1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                    </div>
                    <div class="s-conn-info" style="flex:1">
                        <h3>{{ $nm }}</h3>
                        <p>{{ $desc }}</p>
                    </div>
                    <span class="s-badge" style="background:{{ str_replace('.08','.12',$bg) }};color:{{ $col }};border-color:{{ str_replace('.08','.25',$bg) }}">{{ $badge }}</span>
                </div>
                <div class="s-conn-body">
                    <div class="s-conn-meta">
                        <span class="s-conn-chip">{{ $tags }}</span>
                    </div>
                    <button type="button" class="s-conn-btn" style="opacity:.5;cursor:not-allowed" disabled>
                        Em desenvolvimento
                    </button>
                </div>
            </div>
            @endforeach

        </div>

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

        @php
            $syncItems = [
                ['Catálogo', $stats['ml_connected'] || $stats['shopee_connected'], 'Produtos e estoque sincronizados com marketplaces', '#10b981'],
                ['Pedidos', $stats['ml_connected'], 'Importação de pedidos e atualizações de status', '#6366f1'],
                ['Login social', $stats['google_connected'], 'Entrada rápida e validação OAuth com Google', '#f59e0b'],
            ];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1rem;margin-top:1rem">
            <div class="settings-card" style="padding:1.25rem;background:linear-gradient(135deg,rgba(99,102,241,.08),transparent)">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(99,102,241,.14);color:#6366f1">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Centro de API e automação</p>
                            <p class="settings-card-desc">O que já pode ser conectado e o que entra em breve</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.65rem">
                    @foreach([
                        ['API REST privada', 'Use chaves para sincronizar ERP, BI ou integrações internas.', 'Breve', '#6366f1'],
                        ['Webhooks de eventos', 'Receba pedidos, estoque e publicações em tempo real.', 'Breve', '#10b981'],
                        ['Automação low-code', 'Fluxos com Zapier, Make e conectores simplificados.', 'Planejado', '#f59e0b'],
                    ] as [$title,$desc,$badge,$color])
                    <div style="padding:.8rem .9rem;border-radius:.8rem;background:{{ $color }}0F;border:1px solid {{ $color }}22">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.2rem">
                            <p style="font-size:.8rem;font-weight:700;color:{{ $color }};margin:0">{{ $title }}</p>
                            <span class="s-badge" style="background:{{ $color }}1A;color:{{ $color }}">{{ $badge }}</span>
                        </div>
                        <p style="font-size:.74rem;color:#64748b;margin:0">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="settings-card" style="padding:1.25rem">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.12);color:#10b981">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Saúde da sincronização</p>
                            <p class="settings-card-desc">Indicadores de estabilidade das integrações principais</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.65rem">
                    @foreach($syncItems as [$name,$ok,$desc,$color])
                    <div style="padding:.8rem .9rem;border-radius:.8rem;background:{{ $ok ? $color . '0F' : 'rgba(148,163,184,.08)' }};border:1px solid {{ $ok ? $color . '22' : 'rgba(148,163,184,.16)' }}">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.2rem">
                            <p style="font-size:.8rem;font-weight:700;color:{{ $ok ? $color : '#64748b' }};margin:0">{{ $name }}</p>
                            <span class="s-badge {{ $ok ? 's-badge-success' : '' }}">{{ $ok ? 'Saudável' : 'Pendente' }}</span>
                        </div>
                        <p style="font-size:.74rem;color:#64748b;margin:0">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        </div>{{-- /s-col-side --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>

