@php
    $front = \App\Services\Portal\StorefrontService::class;
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $phBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-700/70';
    $iconBtn = 'inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-default';
    $primary = 'inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-fuchsia-500 hover:from-violet-600 hover:to-fuchsia-600 shadow-md shadow-violet-500/25 transition whitespace-nowrap';
    $label = 'block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1';
    $input = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:focus:ring-violet-900/30 focus:outline-none';
    $card = 'rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white dark:bg-slate-900 shadow-sm';
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
    ];
    $bState = function ($b) {
        $today = now()->startOfDay();
        return match (true) {
            ! $b->active => ['Desligado', 'slate'],
            $b->ends_at && $today->gt($b->ends_at) => ['Vencido', 'rose'],
            $b->starts_at && $today->lt($b->starts_at) => ['Agendado', 'sky'],
            default => ['No ar', 'emerald'],
        };
    };
    $sizes = [
        'hero'  => ['Topo (carrossel)', 'Aparece no alto da página inicial e passa sozinho. Imagem 1200 × 400 px; para o celular, 1080 × 608 px (opcional).', 'bi-images'],
        'strip' => ['Faixas entre as seções', 'Uma faixa depois das ofertas e outra depois de cada coleção. Imagem 1200 × 200 px; celular 900 × 300 px (opcional).', 'bi-distribute-vertical'],
        'side'  => ['Laterais', 'Ficam fixas dos lados do catálogo em monitores largos (Full HD e ultrawide). Imagem 160 × 600 px.', 'bi-layout-sidebar-inset'],
    ];
    $linkLabel = function ($b) use ($collections) {
        return match ($b->link_type) {
            'offers' => 'Leva para Ofertas',
            'collection' => 'Leva para a coleção ' . ($collections->firstWhere('slug', $b->link_value)?->name ?? $b->link_value),
            'category' => 'Leva para uma categoria',
            'product' => 'Leva para um produto',
            'url' => 'Leva para ' . parse_url((string) $b->link_value, PHP_URL_HOST),
            default => 'Sem link',
        };
    };
    $tabs = ['inicio' => ['Página inicial', 'bi-house'], 'banners' => ['Banners', 'bi-images'], 'destaques' => ['Destaques', 'bi-star'], 'colecoes' => ['Coleções', 'bi-collection'], 'aviso' => ['Aviso no topo', 'bi-megaphone']];
@endphp

<div class="w-full px-3 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-24 space-y-4" style="--ml-radius: 14px; --ml-blue: #3483fa">
    @include('portal.partials.storefront-styles')
    <x-page-header title="Vitrine do catálogo" icon="bi-shop-window" section="produtos" back-route="promotions.index"
                   subtitle="Tudo do seu catálogo online: aparência, vendas e clientes">
        <x-slot:actions>
            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" title="Abrir o catálogo" class="{{ $phBtn }}" data-testid="open-catalog"><i class="bi bi-box-arrow-up-right text-violet-500"></i><span class="hidden sm:inline">Ver catálogo</span></a>
        </x-slot:actions>
    </x-page-header>

    <x-portal-settings-nav :current="$tab" :wire="true" :info="$navInfo">

    {{-- ===================== Identidade da loja ===================== --}}
    @if($tab === 'geral')
        @php
            $all = collect($overview)->flatten(1);
            $req = $all->where(7, false);
            $doneReq = $req->where(4, true)->count();
            $pct = $req->count() ? (int) round($doneReq / $req->count() * 100) : 100;
            $next = $all->first(fn ($i) => ! $i[4] && ! $i[7]) ?? $all->first(fn ($i) => ! $i[4]);
            $go = fn ($i) => $i[6][0] === 'tab' ? 'wire:click="$set(\'tab\', \'' . $i[6][1] . '\')"' : 'href="' . e($i[6][1]) . '" wire:navigate';
            $ring = 2 * M_PI * 34;
        @endphp
        <div class="space-y-4" data-testid="overview">
            {{-- Topo: quanto falta e o link da loja --}}
            <section class="relative overflow-hidden rounded-3xl p-5 sm:p-6 text-white shadow-lg bg-gradient-to-br from-violet-600 via-fuchsia-600 to-rose-500">
                <div class="absolute -right-10 -top-14 w-56 h-56 rounded-full bg-white/10"></div>
                <div class="absolute right-24 -bottom-16 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="relative shrink-0 w-24 h-24">
                        <svg viewBox="0 0 80 80" class="w-24 h-24 -rotate-90"><circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="8"/><circle cx="40" cy="40" r="34" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-dasharray="{{ $ring }}" stroke-dashoffset="{{ $ring * (1 - $pct / 100) }}"/></svg>
                        <b class="absolute inset-0 flex items-center justify-center text-2xl font-black" data-testid="overview-pct">{{ $pct }}%</b>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-widest text-white/75">Sua loja online</p>
                        <h2 class="text-xl sm:text-2xl font-black leading-tight">{{ $pct >= 100 ? 'Tudo pronto para vender!' : ($pct >= 60 ? 'Falta pouco para ficar completa' : 'Vamos deixar a sua loja pronta') }}</h2>
                        <p class="text-sm text-white/85 mt-1">{{ $doneReq }} de {{ $req->count() }} passos principais prontos.@if($next) Próximo: <b>{{ $next[2] }}</b>.@endif</p>
                        <div class="flex flex-wrap gap-2 mt-3" x-data="{ copied: false }">
                            @if($next)<button type="button" {!! $go($next) !!} class="inline-flex items-center gap-1.5 rounded-xl bg-white text-violet-700 px-4 py-2 text-sm font-bold shadow hover:bg-violet-50" data-testid="overview-next"><i class="bi bi-arrow-right-circle-fill"></i>{{ $next[5] }}</button>@endif
                            <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/30 px-4 py-2 text-sm font-semibold"><i class="bi bi-box-arrow-up-right"></i>Abrir o site</a>
                            <button type="button" @click="navigator.clipboard?.writeText(@js($catalogUrl)); copied = true; setTimeout(() => copied = false, 1800)" class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/30 px-4 py-2 text-sm font-semibold" data-testid="overview-copy"><i class="bi" :class="copied ? 'bi-check2' : 'bi-link-45deg'"></i><span x-text="copied ? 'Link copiado' : 'Copiar link'">Copiar link</span></button>
                            <a href="https://wa.me/?text={{ rawurlencode('Conheça a minha loja: ' . $catalogUrl) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/30 px-4 py-2 text-sm font-semibold"><i class="bi bi-whatsapp"></i>Mandar no WhatsApp</a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Atalhos --}}
            <section class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2" data-testid="overview-quick">
                @foreach([
                    ['bi-palette', 'Trocar o tema', ['tab', 'inicio'], 'from-violet-500 to-fuchsia-500'],
                    ['bi-image', 'Novo banner', ['route', route('promotions.storefront.banner.create')], 'from-rose-500 to-orange-400'],
                    ['bi-star', 'Destaques', ['tab', 'destaques'], 'from-amber-500 to-yellow-400'],
                    ['bi-collection', 'Nova coleção', ['route', route('promotions.storefront.collection.create')], 'from-sky-500 to-cyan-400'],
                    ['bi-ticket-perforated', 'Novo cupom', ['route', route('promotions.coupons')], 'from-emerald-500 to-teal-400'],
                    ['bi-robot', 'Automações', ['tab', 'automacoes'], 'from-slate-700 to-slate-500'],
                ] as [$qi, $qn, $qt, $qg])
                    <a {!! $qt[0] === 'tab' ? 'href="#" wire:click.prevent="$set(\'tab\', \'' . $qt[1] . '\')"' : 'href="' . e($qt[1]) . '" wire:navigate' !!} class="{{ $card }} group p-3 flex items-center gap-2.5 hover:-translate-y-0.5 hover:shadow-md transition">
                        <span class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center text-white bg-gradient-to-br {{ $qg }} shadow-sm"><i class="bi {{ $qi }}"></i></span>
                        <b class="text-sm text-slate-700 dark:text-slate-200 leading-tight">{{ $qn }}</b>
                    </a>
                @endforeach
            </section>

            {{-- Passo a passo por grupo --}}
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach($overview as $gname => $items)
                    @php $gDone = collect($items)->where(4, true)->count(); @endphp
                    <section class="{{ $card }} p-4 sm:p-5" data-testid="overview-group">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $gname }}</h3>
                            <span class="shrink-0 text-xs font-semibold rounded-full px-2.5 py-1 {{ $gDone === count($items) ? $tones['emerald'] : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-300' }}">{{ $gDone }}/{{ count($items) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 mb-3 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width: {{ count($items) ? round($gDone / count($items) * 100) : 0 }}%"></div></div>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($items as [$ik, $iicon, $ititle, $itext, $idone, $ibtn, $itarget, $iopt])
                                <li class="flex items-center gap-3 py-2.5" data-testid="overview-item-{{ $ik }}">
                                    <span class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center {{ $idone ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}"><i class="bi {{ $idone ? 'bi-check-lg' : $iicon }}"></i></span>
                                    <span class="min-w-0 flex-1">
                                        <b class="block text-sm text-slate-800 dark:text-slate-100">{{ $ititle }}@if($iopt && ! $idone)<span class="ml-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">opcional</span>@endif</b>
                                        <small class="block text-xs text-slate-500 dark:text-slate-400">{{ $itext }}</small>
                                    </span>
                                    @if($itarget[0] === 'tab')
                                        <button type="button" wire:click="$set('tab', '{{ $itarget[1] }}')" class="shrink-0 rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $idone ? 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' : 'bg-violet-500 text-white hover:bg-violet-600 shadow-sm' }}">{{ $idone ? 'Mudar' : $ibtn }}</button>
                                    @else
                                        <a href="{{ $itarget[1] }}" wire:navigate class="shrink-0 rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $idone ? 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' : 'bg-violet-500 text-white hover:bg-violet-600 shadow-sm' }}">{{ $idone ? 'Mudar' : $ibtn }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        </div>
    @endif

    @if($tab === 'identidade')
        @php
            $SI = \App\Services\Portal\StoreIdentity::class;
            $logoUrl = ($identity['logo'] ?? null) ? asset('storage/' . $identity['logo']) : null;
            $fontCss = $SI::FONTS[$identity['font'] ?? 'padrao'][1] ?? $SI::FONTS['padrao'][1];
            $fontLinks = collect($SI::FONTS)->pluck(2)->filter()->implode('&family=');
        @endphp
        <link href="https://fonts.googleapis.com/css2?family={{ $fontLinks }}&display=swap" rel="stylesheet">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-start" data-testid="identity-panel">
            <div class="space-y-4 min-w-0">
                {{-- Logo e nome --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 text-lg"><i class="bi bi-person-badge"></i></div>
                        <div><h2 class="font-bold text-slate-900 dark:text-white">Logo e nome da loja</h2><p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Aparecem no topo de todas as páginas do catálogo e no rodapé.</p></div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 sm:items-center">
                        <div class="shrink-0 w-28 h-28 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden relative">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo" class="max-w-full max-h-full object-contain p-2">
                            @else
                                <i class="bi bi-image text-3xl text-slate-300"></i>
                            @endif
                            <div wire:loading.flex wire:target="logoUpload" class="absolute inset-0 bg-white/80 dark:bg-slate-900/80 items-center justify-center"><i class="bi bi-arrow-repeat animate-spin text-violet-500 text-xl"></i></div>
                        </div>
                        <div class="space-y-2 min-w-0">
                            <label class="{{ $phBtn }} cursor-pointer" data-testid="logo-upload"><i class="bi bi-upload text-violet-500"></i>{{ $logoUrl ? 'Trocar a logo' : 'Enviar a logo' }}
                                <input type="file" wire:model="logoUpload" accept="image/*" class="sr-only">
                            </label>
                            @if($logoUrl)<button type="button" wire:click="removeLogo" class="ml-1 text-xs font-semibold text-rose-600 hover:underline">Tirar a logo</button>@endif
                            <p class="text-xs text-slate-500">PNG com fundo transparente fica melhor. Até 3 MB.</p>
                            @error('logoUpload')<p class="text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="{{ $label }}" for="id-name">Nome da loja</label>
                            <input id="id-name" type="text" wire:model.blur="identity.name" maxlength="60" class="{{ $input }}" data-testid="id-name">
                            @error('identity.name')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="{{ $label }}" for="id-tag">Frase curta <span class="font-normal text-slate-400">(opcional)</span></label>
                            <input id="id-tag" type="text" wire:model.blur="identity.tagline" maxlength="80" placeholder="Perfumes e maquiagem com entrega rápida" class="{{ $input }}">
                        </div>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200 cursor-pointer">
                        <input type="checkbox" wire:model.live="identity.show_name" class="rounded text-violet-500 focus:ring-violet-400">
                        Mostrar o nome ao lado da logo no topo <small class="text-slate-400">(no celular aparece só a logo)</small>
                    </label>
                </section>

                {{-- Fonte --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-sky-50 dark:bg-sky-500/15 text-sky-600 text-lg"><i class="bi bi-fonts"></i></div>
                        <div><h2 class="font-bold text-slate-900 dark:text-white">Fonte dos títulos</h2><p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Muda o nome da loja e os títulos das seções. O texto dos produtos continua fácil de ler.</p></div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($SI::FONTS as $fk => [$fname, $fcss])
                            @php $fon = ($identity['font'] ?? 'padrao') === $fk; @endphp
                            <button type="button" wire:click="setFont('{{ $fk }}')" data-testid="font-{{ $fk }}"
                                    class="rounded-xl border p-3 text-left transition {{ $fon ? 'border-violet-400 bg-violet-50 dark:bg-violet-500/15 ring-1 ring-violet-300' : 'border-slate-200 dark:border-slate-700 hover:border-violet-300' }}">
                                <span class="block text-lg font-bold text-slate-900 dark:text-white truncate" style="font-family: {!! $fcss !!}">{{ \Illuminate\Support\Str::limit($identity['name'] ?? 'Sua loja', 14) }}</span>
                                <small class="block text-[11px] text-slate-500 truncate">{{ $fname }}</small>
                            </button>
                        @endforeach
                    </div>
                </section>

                {{-- Contatos --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 text-lg"><i class="bi bi-telephone"></i></div>
                        <div><h2 class="font-bold text-slate-900 dark:text-white">Contatos e redes</h2><p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ficam no rodapé do catálogo. Deixe em branco o que não quiser mostrar.</p></div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach([
                            'whatsapp' => ['WhatsApp', 'bi-whatsapp', '(16) 99999-9999', 'tel'],
                            'phone' => ['Telefone fixo', 'bi-telephone', '(16) 3333-3333', 'tel'],
                            'email' => ['E-mail', 'bi-envelope', 'contato@sualoja.com', 'email'],
                            'instagram' => ['Instagram', 'bi-instagram', '@sualoja', 'text'],
                            'facebook' => ['Facebook', 'bi-facebook', 'sualoja', 'text'],
                            'tiktok' => ['TikTok', 'bi-tiktok', '@sualoja', 'text'],
                        ] as $fk => [$fl, $fi, $fp, $ft])
                            <div>
                                <label class="{{ $label }}" for="id-{{ $fk }}"><i class="bi {{ $fi }} text-slate-400"></i> {{ $fl }}</label>
                                <input id="id-{{ $fk }}" type="{{ $ft }}" wire:model.blur="identity.{{ $fk }}" placeholder="{{ $fp }}" class="{{ $input }}" data-testid="id-{{ $fk }}">
                                @error('identity.' . $fk)<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}" for="id-address"><i class="bi bi-geo-alt text-slate-400"></i> Endereço <span class="font-normal text-slate-400">(abre no mapa)</span></label>
                            <input id="id-address" type="text" wire:model.blur="identity.address" placeholder="Rua, número, bairro, cidade" class="{{ $input }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}" for="id-hours"><i class="bi bi-clock text-slate-400"></i> Horário de atendimento</label>
                            <input id="id-hours" type="text" wire:model.blur="identity.hours" placeholder="Seg a sex 9h às 18h · Sáb 9h às 13h" class="{{ $input }}">
                        </div>
                    </div>
                    <div>
                        <span class="{{ $label }}">Formas de pagamento no rodapé</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($SI::PAYMENTS as $pk => $pn)
                                <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 cursor-pointer has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50 dark:has-[:checked]:bg-violet-500/15">
                                    <input type="checkbox" value="{{ $pk }}" wire:model="identity.payments" class="rounded text-violet-500 focus:ring-violet-400">{{ $pn }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Páginas --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-500/15 text-amber-600 text-lg"><i class="bi bi-file-earmark-text"></i></div>
                        <div><h2 class="font-bold text-slate-900 dark:text-white">Páginas da loja</h2><p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Cada texto preenchido vira uma página com link no rodapé. Em branco, a página não aparece.</p></div>
                    </div>
                    @foreach($SI::PAGES as $pk => [$pt, $pi, $ph])
                        <div x-data="{ open: {{ trim($pages[$pk] ?? '') !== '' ? 'true' : 'false' }} }" class="rounded-xl border border-slate-200 dark:border-slate-700">
                            <button type="button" @click="open = !open" class="w-full flex items-center gap-3 p-3 text-left">
                                <b class="flex-1 text-sm text-slate-800 dark:text-slate-100">{{ $pt }}</b>
                                @if(trim($pages[$pk] ?? '') !== '')
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones['emerald'] }}">No ar</span>
                                    <a href="{{ route('portal.store-page', ['userId' => auth()->id(), 'page' => $pk]) }}" target="_blank" rel="noopener" @click.stop class="text-xs font-semibold text-violet-600">Ver</a>
                                @else
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones['slate'] }}">Vazia</span>
                                @endif
                                <i class="bi bi-chevron-down text-slate-400 transition" :class="open && 'rotate-180'"></i>
                            </button>
                            <div x-show="open" x-cloak class="px-3 pb-3">
                                <textarea wire:model.blur="pages.{{ $pk }}" rows="5" maxlength="5000" placeholder="{{ $ph }}" class="{{ $input }}" data-testid="page-{{ $pk }}"></textarea>
                            </div>
                        </div>
                    @endforeach
                </section>

                <div class="flex justify-end sticky bottom-20 lg:bottom-4 z-10">
                    <button type="button" wire:click="saveIdentity" class="{{ $primary }}" data-testid="identity-save"><i class="bi bi-check2"></i><span wire:loading.remove wire:target="saveIdentity">Salvar identidade</span><span wire:loading wire:target="saveIdentity">Salvando…</span></button>
                </div>
            </div>

            {{-- Como fica: topo e rodapé --}}
            <aside class="{{ $card }} p-4 space-y-3 xl:sticky xl:top-4">
                <b class="block text-sm text-slate-900 dark:text-white">Como fica no catálogo</b>
                <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-2 px-3 h-14" style="background: linear-gradient(90deg, {{ $home_colors[0] ?? '#ec4899' }}, {{ $home_colors[1] ?? '#3b82f6' }})">
                        <span class="shrink-0 h-10 {{ $logoUrl ? 'px-1.5 min-w-10' : 'w-10' }} rounded-xl bg-white flex items-center justify-center shadow">
                            <img src="{{ $logoUrl ?: \Illuminate\Support\Facades\Vite::asset('resources/images/icons/logo-64.png') }}" alt="" class="{{ $logoUrl ? 'h-8 max-w-[90px]' : 'h-7 w-7' }} object-contain">
                        </span>
                        @if($identity['show_name'] ?? true)
                            <span class="min-w-0 text-white leading-tight">
                                <b class="block truncate text-[15px]" style="font-family: {!! $fontCss !!}">{{ $identity['name'] ?? '' }}</b>
                                @if(($identity['tagline'] ?? '') !== '')<small class="block truncate text-[10px] opacity-85">{{ $identity['tagline'] }}</small>@endif
                            </span>
                        @endif
                        <span class="ml-auto h-8 w-20 rounded-md bg-white/90"></span>
                    </div>
                    <div class="h-16 bg-slate-100 dark:bg-slate-800"></div>
                    <div class="bg-white dark:bg-slate-900 p-3 text-[11px] text-slate-600 dark:text-slate-300 space-y-1.5 border-t border-slate-100 dark:border-slate-800">
                        <b class="block text-sm text-slate-900 dark:text-white" style="font-family: {!! $fontCss !!}">{{ $identity['name'] ?? '' }}</b>
                        @foreach(['whatsapp' => 'bi-whatsapp', 'phone' => 'bi-telephone', 'email' => 'bi-envelope', 'address' => 'bi-geo-alt', 'hours' => 'bi-clock'] as $ck => $ci)
                            @if(trim((string) ($identity[$ck] ?? '')) !== '')<p class="truncate"><i class="bi {{ $ci }} text-violet-500"></i> {{ $identity[$ck] }}</p>@endif
                        @endforeach
                        @php $pv = collect($SI::PAGES)->filter(fn ($v, $k) => trim($pages[$k] ?? '') !== ''); @endphp
                        @if($pv->isNotEmpty())<p class="text-violet-600 font-semibold">{{ $pv->pluck(0)->implode(' · ') }}</p>@endif
                        @if(! empty($identity['payments']))<p>{{ collect($identity['payments'])->map(fn ($k) => $SI::PAYMENTS[$k] ?? null)->filter()->implode(' · ') }}</p>@endif
                    </div>
                </div>
                <a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="{{ $phBtn }} w-full justify-center"><i class="bi bi-box-arrow-up-right text-violet-500"></i>Abrir o catálogo</a>
            </aside>
        </div>
    @endif

    {{-- ===================== Página inicial ===================== --}}
    @if($tab === 'inicio')
        @php $home = \App\Services\Portal\HomeLayout::class; $cur = $themes[$home_theme] ?? null; @endphp
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] items-start" data-testid="home-builder">
            <div class="space-y-4 min-w-0" x-data="{ sec: 'tema', go(id) { this.sec = id; document.getElementById('pi-' + id)?.scrollIntoView({ behavior: 'smooth', block: 'start' }); } }">
                {{-- Atalhos para as partes da página (fica preso no alto ao rolar) --}}
                <div class="sticky top-2 z-20 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-sm p-1 grid grid-cols-4 gap-1" data-testid="home-subnav">
                    @foreach(['tema' => ['Tema', 'bi-palette'], 'cores' => ['Cores', 'bi-droplet-half'], 'estilo' => ['Estilo', 'bi-palette2'], 'blocos' => ['Blocos', 'bi-list-ol']] as $sk => [$sn, $si])
                        <button type="button" @click="go('{{ $sk }}')" :class="sec === '{{ $sk }}' ? 'bg-violet-500 text-white shadow' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'" class="rounded-xl py-2 text-xs sm:text-sm font-semibold transition inline-flex items-center justify-center gap-1.5"><i class="bi {{ $si }}"></i>{{ $sn }}</button>
                    @endforeach
                </div>
                {{-- 1. Tema --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="home-themes" id="pi-tema" style="scroll-margin-top: 80px">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-lg"><i class="bi bi-palette"></i></div>
                        <div class="min-w-0">
                            <h2 class="font-bold text-slate-900 dark:text-white">Tema da loja</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Escolha um estilo ou uma data e a página inteira muda de uma vez: cores do topo, cantos, ordem dos blocos, banner do evento e aviso. Depois você ajusta o que quiser.</p>
                        </div>
                        @if($cur)<span class="ml-auto shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tones['emerald'] }}" data-testid="theme-current">{{ $cur['emoji'] }} {{ $cur['name'] }}</span>@endif
                    </div>
                    @foreach(['estilo' => ['Estilos para qualquer época', 'Use o ano todo. Mudam cores, fundo, cards e o jeito do site.'], 'data' => ['Datas e campanhas', 'Para cada data, com banner e aviso prontos. Em Automações dá para trocar sozinho.']] as $grp => [$gName, $gHint])
                    <div class="pt-1">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $gName }}</p>
                        <p class="text-[11px] text-slate-400 mb-1.5">{{ $gHint }}</p>
                    </div>
                    <div class="grid grid-cols-2 min-[520px]:grid-cols-3 xl:grid-cols-4 gap-2" data-testid="themes-{{ $grp }}">
                        @foreach(collect($themes)->filter(fn ($t, $k) => ($grp === 'estilo') === ($k === 'padrao' || ($t['group'] ?? '') === 'estilo')) as $k => $t)
                            <button type="button" wire:click="pickTheme('{{ $k }}')" wire:key="th-{{ $k }}" data-testid="theme-{{ $k }}"
                                    class="relative text-left rounded-xl border p-2 transition {{ $pendingTheme === $k ? 'border-violet-500 ring-2 ring-violet-200 dark:ring-violet-500/30' : ($home_theme === $k ? 'border-emerald-400' : 'border-slate-200 dark:border-slate-700 hover:border-violet-300') }}">
                                <span class="block h-9 rounded-lg mb-1.5 relative overflow-hidden" style="background: linear-gradient(90deg, {{ $t['colors'][0] }}, {{ $t['colors'][1] }})">
                                    <span class="absolute right-1.5 bottom-1.5 w-4 h-4 rounded-full border-2 border-white" style="background: {{ $t['colors'][2] }}"></span>
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-lg">{{ $t['emoji'] }}</span>
                                </span>
                                @php $sf = $home::SURFACES[$k] ?? $home::SURFACES['padrao']; @endphp
                                <span class="flex gap-1 mb-1.5 rounded-md p-1" style="background: {{ $sf[0] }}" title="Fundo e cards">
                                    <span class="flex-1 h-3 rounded-sm" style="background: {{ $sf[1] }}"></span><span class="flex-1 h-3 rounded-sm" style="background: {{ $sf[1] }}"></span><span class="flex-1 h-3 rounded-sm" style="background: {{ $sf[1] }}"></span>
                                </span>
                                <b class="block text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $t['name'] }}</b>
                                <small class="block text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $t['when'] ? ($t['when']['now'] ? 'Agora, até ' . $t['when']['end']->format('d/m') : 'De ' . $t['when']['start']->format('d/m') . ' a ' . $t['when']['end']->format('d/m')) : ($k === 'padrao' ? 'O dia a dia' : ($t['desc'] ?? 'Quando quiser')) }}</small>
                                @if($home_theme === $k)<i class="bi bi-check-circle-fill absolute top-1 right-1 text-emerald-500 bg-white rounded-full"></i>@endif
                            </button>
                        @endforeach
                    </div>
                    @endforeach
                    @if($pendingTheme !== '' && isset($themes[$pendingTheme]))
                        @php $pt = $themes[$pendingTheme]; @endphp
                        <div class="rounded-xl bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/30 p-3 space-y-2" data-testid="theme-apply-box">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $pt['emoji'] }} Usar o tema {{ $pt['name'] }}?</p>
                            <p class="text-xs text-slate-600 dark:text-slate-300"><b>A prévia ao lado já mostra o site com este tema.</b> Troca as cores do topo, do fundo, dos cards e das áreas, os cantos e a ordem dos blocos ({{ collect($pt['order'])->map(fn ($k) => $home::SECTIONS[$k][0])->take(3)->join(', ') }}…).</p>
                            @if($pt['tpl'])
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model.live="withBanner" class="rounded text-violet-500 focus:ring-violet-400" data-testid="theme-with-banner"> Criar o banner do evento no começo do carrossel{{ $pt['when'] ? ' (sai do ar em ' . $pt['when']['end']->format('d/m') . ')' : ' (por 7 dias)' }}</label>
                            @endif
                            @if($pt['notice'])
                                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model.live="withNotice" class="rounded text-violet-500 focus:ring-violet-400"> Trocar o aviso do topo por “{{ $pt['notice'][$kind === 'moveis' ? 1 : 0] }}”</label>
                            @endif
                            <div class="flex gap-2 pt-1">
                                <button type="button" wire:click="applyTheme" wire:loading.attr="disabled" class="{{ $primary }}" data-testid="theme-apply"><i class="bi bi-magic"></i>Aplicar tema</button>
                                <button type="button" wire:click="pickTheme('{{ $pendingTheme }}')" class="{{ $phBtn }}">Cancelar</button>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- 2. Cores e cantos --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="home-colors" id="pi-cores" style="scroll-margin-top: 80px">
                    <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-droplet-half text-violet-500"></i>Cores e cantos</h2>
                    <div class="grid grid-cols-1 min-[480px]:grid-cols-3 gap-2">
                        @foreach(['Topo, começo', 'Topo, fim', 'Botões e links'] as $i => $cname)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-2.5 py-2 cursor-pointer">
                                <input type="color" wire:model.live.debounce.400ms="home_colors.{{ $i }}" class="shrink-0 w-9 h-9 rounded-lg border-0 bg-transparent p-0 cursor-pointer" data-testid="home-color-{{ $i }}">
                                <span class="min-w-0"><b class="block text-xs text-slate-700 dark:text-slate-200">{{ $cname }}</b><small class="block text-[11px] text-slate-400 uppercase">{{ $home_colors[$i] ?? '' }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="h-3 rounded-full" style="background: linear-gradient(90deg, {{ $home_colors[0] ?? '#ec4899' }}, {{ $home_colors[1] ?? '#3b82f6' }})"></div>
                    <div class="grid grid-cols-1 min-[480px]:grid-cols-2 gap-2">
                        @foreach(['Fundo da página', 'Cards e áreas'] as $i => $cname)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-2.5 py-2 cursor-pointer">
                                <input type="color" wire:model.live.debounce.400ms="home_surface.{{ $i }}" class="shrink-0 w-9 h-9 rounded-lg border-0 bg-transparent p-0 cursor-pointer" data-testid="home-surface-{{ $i }}">
                                <span class="min-w-0"><b class="block text-xs text-slate-700 dark:text-slate-200">{{ $cname }}</b><small class="block text-[11px] text-slate-400 uppercase">{{ $home_surface[$i] ?? '' }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">O texto fica claro sozinho quando os cards forem escuros.</p>
                    <div class="grid grid-cols-3 gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                        @foreach($home::STYLES as $k => [$n, $r, $ico])
                            <button type="button" wire:click="setStyle('{{ $k }}')" data-testid="style-{{ $k }}" class="rounded-lg py-2 text-xs sm:text-sm font-semibold {{ $home_style === $k ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}"><i class="bi {{ $ico }}"></i> {{ $n }}</button>
                        @endforeach
                    </div>
                </section>

                {{-- 2b. Estilo do site --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="home-look" id="pi-estilo" style="scroll-margin-top: 80px">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-palette2 text-violet-500"></i>Estilo do site</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">O jeito do topo, dos cards, dos botões e um desenho no fundo. A prévia mostra na hora.</p>
                        </div>
                        <button type="button" wire:click="resetLook" class="shrink-0 text-xs font-semibold text-violet-600 dark:text-violet-300 hover:underline" data-testid="look-reset">Voltar ao do tema</button>
                    </div>
                    @foreach($home::LOOKS as $part => [$pname, $choices])
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1.5">{{ $pname }}</p>
                            <div class="grid grid-cols-3 min-[520px]:grid-cols-4 2xl:grid-cols-5 gap-1.5">
                                @foreach($choices as $k => [$n, $ico])
                                    @php $on = ($home_look[$part] ?? null) === $k; @endphp
                                    <button type="button" wire:click="setLook('{{ $part }}', '{{ $k }}')" data-testid="look-{{ $part }}-{{ $k }}" aria-pressed="{{ $on ? 'true' : 'false' }}"
                                        class="relative text-left rounded-xl border p-1.5 transition {{ $on ? 'border-violet-500 ring-2 ring-violet-200 dark:ring-violet-500/30 bg-violet-50/60 dark:bg-violet-500/10' : 'border-slate-200 dark:border-slate-700 hover:border-violet-300 hover:-translate-y-0.5' }}">
                                        @include('livewire.promotions.partials.look-thumb', ['part' => $part, 'k' => $k, 'c' => $home_colors])
                                        <span class="block mt-1 text-[11px] font-semibold truncate {{ $on ? 'text-violet-700 dark:text-violet-200' : 'text-slate-600 dark:text-slate-300' }}">{{ $n }}</span>
                                        @if($on)<i class="bi bi-check-circle-fill absolute -top-1.5 -right-1.5 text-violet-500 bg-white dark:bg-slate-900 rounded-full"></i>@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </section>

                {{-- 3. Blocos --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-2" data-testid="home-sections" id="pi-blocos" style="scroll-margin-top: 80px">
                    <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-list-ol text-violet-500"></i>O que aparece e em que ordem</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">De cima para baixo, como o cliente vê. O aviso fica sempre no alto e a lista de todos os produtos sempre no fim.</p>
                    <ol class="space-y-1.5">
                        @foreach($home_order as $k)
                            @php [$sname, $sico, $sdesc] = $home::SECTIONS[$k]; $on = ! in_array($k, $home_off, true); [$sinfo, $stab] = $secInfo[$k] ?? ['', null]; @endphp
                            <li wire:key="sec-{{ $k }}" class="flex items-center gap-2 rounded-xl border px-2.5 py-2 {{ $on ? 'border-slate-200 dark:border-slate-700' : 'border-dashed border-slate-300 dark:border-slate-700 opacity-60' }}" data-testid="sec-{{ $k }}">
                                <span class="shrink-0 w-6 text-center text-xs font-bold text-slate-400">{{ $loop->iteration }}</span>
                                <span class="shrink-0 w-9 h-9 rounded-lg flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600"><i class="bi {{ $sico }}"></i></span>
                                <span class="min-w-0 flex-1">
                                    <b class="block text-sm text-slate-800 dark:text-slate-100 truncate">{{ $sname }}</b>
                                    <small class="block text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $sinfo ?: $sdesc }}@if($stab) · <a href="#" wire:click.prevent="$set('tab', '{{ $stab }}')" class="text-violet-600 font-semibold">mudar</a>@endif</small>
                                </span>
                                <button type="button" wire:click="moveSection('{{ $k }}', -1)" class="{{ $iconBtn }} !w-8 !h-8" title="Subir" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                <button type="button" wire:click="moveSection('{{ $k }}', 1)" class="{{ $iconBtn }} !w-8 !h-8" title="Descer" @disabled($loop->last) data-testid="down-{{ $k }}"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" wire:click="toggleSection('{{ $k }}')" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}" title="{{ $on ? 'Esconder' : 'Mostrar' }}" data-testid="toggle-{{ $k }}"
                                        class="shrink-0 relative w-10 h-6 rounded-full transition {{ $on ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"><span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all {{ $on ? 'left-[18px]' : 'left-0.5' }}"></span></button>
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>

            {{-- Prévia ao vivo do catálogo --}}
            @include('livewire.promotions.partials.storefront-preview', ['inline' => true])
        </div>
    @endif

    {{-- ===================== Banners ===================== --}}
    @if($tab === 'banners')
        <div class="space-y-4">
            <section class="{{ $card }} p-4 sm:p-5 overflow-hidden" data-testid="tpl-row">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-lg"><i class="bi bi-magic"></i></div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-slate-900 dark:text-white">Modelos prontos</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Toque em um modelo para criar o banner. Você muda textos, cores, fotos e arrumação, e usa no topo, na faixa ou na lateral. Os de data já entram e saem do ar sozinhos.</p>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 flex-wrap" data-testid="store-kind">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Minha loja é de</span>
                    @foreach(\App\Services\Promotions\StoreKind::KINDS as $k => [$n, $ico, $ex])
                        <button type="button" wire:click="setKind('{{ $k }}')" title="{{ $ex }}" data-testid="kind-{{ $k }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $kind === $k ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/15 text-violet-700 dark:text-violet-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300' }}"><i class="bi {{ $ico }}"></i>{{ $n }}</button>
                    @endforeach
                </div>
                <div class="mt-3 flex gap-1 overflow-x-auto [scrollbar-width:none]">
                    @foreach($front::TEMPLATE_GROUPS as $g => [$gn, $gi])
                        <button type="button" wire:click="$set('galleryGroup', '{{ $g }}')" data-testid="rowgroup-{{ $g }}"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $galleryGroup === $g ? 'bg-violet-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                            <i class="bi {{ $gi }}"></i>{{ $gn }} <span class="opacity-70">{{ $front::templates($kind)->where('group', $g)->count() }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="-mx-4 sm:-mx-5 px-4 sm:px-5 scroll-px-4 sm:scroll-px-5 mt-3 flex gap-3 overflow-x-auto pb-2 snap-x [scrollbar-width:thin] items-start">
                    @foreach($front::templatesIn($galleryGroup, $kind) as $key => $t)
                        @php
                            $tp = array_slice($t['src'] !== 'manual' ? ($autoPhotos[$t['src']] ?? []) : $samples, 0, $front::picsFor($key, $t['fmt'] ?? null));
                            $td = $t['date'] ? \App\Services\Promotions\CommercialCalendar::next($t['date']) : null;
                        @endphp
                        <a href="{{ route('promotions.storefront.banner.create', ['modelo' => $key]) }}" wire:key="tplrow-{{ $key }}" data-testid="tplrow-{{ $key }}"
                                class="group snap-start shrink-0 rounded-2xl p-1.5 text-left ring-1 ring-slate-200 dark:ring-slate-700 hover:ring-2 hover:ring-violet-400 transition">
                            @include('livewire.promotions.partials.storefront-mini', ['kind' => 'hero', 'scale' => 0.36, 'tpl' => $key, 'fmt' => $t['fmt'] ?? null, 'c1' => $t['c1'], 'c2' => $t['c2'], 'acc' => $t['acc'], 'ink' => $t['ink'],
                                'badge' => $t['badge'], 'title' => $t['title'], 'subtitle' => $t['subtitle'], 'button' => $t['button'], 'photos' => $tp])
                            <span class="flex items-center justify-between gap-2 px-1 pt-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $t['name'] }}<span class="text-violet-600 dark:text-violet-300 opacity-0 group-hover:opacity-100 transition"><i class="bi bi-plus-lg"></i> Usar</span></span>
                            @if($td)<small class="block px-1 text-[11px] text-slate-500">{{ $td['emoji'] }} {{ $td['now'] ? 'Agora, até ' . $td['end']->format('d/m') : \App\Services\Promotions\CommercialCalendar::label($td) }}</small>
                            @elseif($t['src'] !== 'manual')<small class="block px-1 text-[11px] text-emerald-600 dark:text-emerald-400"><i class="bi bi-arrow-repeat"></i> {{ $front::PHOTO_SOURCES[$t['src']][0] }}, atualiza sozinho</small>@endif
                        </a>
                    @endforeach
                </div>
            </section>
            @foreach($sizes as $place => [$title, $help, $ico])
                @php $list = $banners[$place] ?? collect(); @endphp
                <section class="{{ $card }} p-4 sm:p-5" data-testid="banners-{{ $place }}">
                    <div class="flex items-start gap-3 flex-wrap">
                        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-300 text-lg"><i class="bi {{ $ico }}"></i></div>
                        <div class="min-w-0 flex-1 self-center">
                            <h2 class="font-bold text-slate-900 dark:text-white">{{ $title }} <span class="text-sm font-normal text-slate-400">({{ $list->count() }})</span></h2>
                        </div>
                        <a href="{{ route('promotions.storefront.banner.create', ['lugar' => $place]) }}" class="{{ $phBtn }}" data-testid="banner-new-{{ $place }}"><i class="bi bi-plus-lg text-violet-500"></i>Adicionar</a>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 sm:pl-[52px]">{{ $help }}</p>

                    @if($list->isEmpty())
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 text-center">
                            @if($place === 'hero') Sem banner, o topo mostra o destaque das ofertas da semana. @else Nenhum banner aqui ainda. @endif
                        </p>
                    @else
                        <div class="mt-4 grid gap-3 grid-cols-1 md:grid-cols-2 2xl:grid-cols-3">
                            @foreach($list as $b)
                                @php [$st, $tone] = $bState($b); @endphp
                                <article wire:key="bn-{{ $b->id }}" class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden flex {{ $place === 'side' ? 'flex-row' : 'flex-col' }} {{ $b->active ? '' : 'opacity-70' }}">
                                    @if($b->isDesign())
                                        @php $dz = $b->design + ['c1' => null, 'c2' => null, 'acc' => null, 'ink' => 'light', 'badge' => null, 'lay' => 'esq'];
                                            $dArgs = ['tpl' => $dz['tpl'], 'c1' => $dz['c1'], 'c2' => $dz['c2'], 'acc' => $dz['acc'], 'ink' => $dz['ink'], 'badge' => $dz['badge'], 'lay' => $dz['lay'] ?? 'esq', 'anim' => $dz['anim'] ?? 'none', 'fmt' => $dz['fmt'] ?? null, 'ends' => $b->ends_at, 'title' => $b->title, 'subtitle' => $b->subtitle, 'button' => $b->button_text, 'photos' => $b->designPhotos()]; @endphp
                                        @if($place === 'side')
                                            <div class="shrink-0 bg-slate-50 dark:bg-slate-800/50 p-1.5">@include('livewire.promotions.partials.storefront-mini', $dArgs + ['kind' => 'side', 'scale' => 0.4])</div>
                                        @else
                                            <div class="sf-ban sf-{{ $place }} has-design !rounded-none">@include('portal.partials.storefront-design', $dArgs)</div>
                                        @endif
                                    @else
                                    <div class="relative {{ $place === 'side' ? 'w-16 shrink-0 aspect-[4/15]' : ($place === 'strip' ? 'aspect-[6/1] min-h-[56px]' : 'aspect-[3/1]') }} overflow-hidden" style="background: {{ $front::gradient($b->color) }}">
                                        @if($b->image)
                                            <img src="{{ $b->imageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                                        @endif
                                        @if($b->title && $place !== 'side')
                                            <div class="absolute inset-0 flex flex-col justify-center px-3 text-white {{ $b->image ? 'bg-gradient-to-r from-black/50 to-transparent' : '' }}">
                                                <b class="text-sm leading-tight line-clamp-2">{{ $b->title }}</b>
                                                @if($b->subtitle && $place !== 'strip')<span class="text-[11px] opacity-90 line-clamp-1">{{ $b->subtitle }}</span>@endif
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="p-3 flex flex-col gap-2 flex-1 min-w-0">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 min-w-0"><span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tones[$tone] }}">{{ $st }}</span><i class="bi bi-link-45deg"></i><span class="truncate">{{ $linkLabel($b) }}</span>@if($b->isDesign())<span class="ml-auto shrink-0 text-[11px] text-violet-600 dark:text-violet-300"><i class="bi bi-magic"></i> {{ $front::TEMPLATES[$b->design['tpl']]['name'] ?? 'Modelo' }}</span>@endif</p>
                                        @if($b->starts_at || $b->ends_at)
                                            <p class="text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-calendar3"></i> {{ $b->starts_at ? 'de ' . $b->starts_at->format('d/m') : '' }} {{ $b->ends_at ? 'até ' . $b->ends_at->format('d/m/Y') : '' }}</p>
                                        @endif
                                        <div class="flex items-center gap-1.5 mt-auto flex-wrap">
                                            <button type="button" wire:click="moveBanner({{ $b->id }}, -1)" class="{{ $iconBtn }}" title="Mover para antes" @disabled($loop->first)><i class="bi {{ $place === 'side' ? 'bi-arrow-up' : 'bi-arrow-left' }}"></i></button>
                                            <button type="button" wire:click="moveBanner({{ $b->id }}, 1)" class="{{ $iconBtn }}" title="Mover para depois" @disabled($loop->last)><i class="bi {{ $place === 'side' ? 'bi-arrow-down' : 'bi-arrow-right' }}"></i></button>
                                            <button type="button" wire:click="toggleBanner({{ $b->id }})" class="{{ $iconBtn }}" title="{{ $b->active ? 'Desligar' : 'Ligar' }}"><i class="bi {{ $b->active ? 'bi-toggle-on text-emerald-500' : 'bi-toggle-off' }} text-lg"></i></button>
                                            <a href="{{ route('promotions.storefront.banner.edit', $b->id) }}" class="{{ $iconBtn }} ml-auto" title="Editar" data-testid="banner-edit"><i class="bi bi-pencil text-indigo-500"></i></a>
                                            <button type="button" x-data @click="fmConfirm({ title: 'Excluir este banner?', message: 'Ele sai do catálogo na hora.', confirmText: 'Excluir' }).then(ok => ok && $wire.deleteBanner({{ $b->id }}))" class="{{ $iconBtn }}" title="Excluir"><i class="bi bi-trash3 text-rose-500"></i></button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif

    {{-- ===================== Destaques ===================== --}}
    @if($tab === 'destaques')
        @php $fmax = \App\Livewire\Promotions\PortalStorefront::FEATURED_MAX; $fcount = $featured->count(); @endphp
        <section class="{{ $card }} p-4 sm:p-5 space-y-4" data-testid="featured-panel">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-500/15 text-amber-500 text-lg"><i class="bi bi-star-fill"></i></div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 dark:text-white">Produtos em destaque</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Uma fileira na página inicial, na ordem que você montar (até {{ $fmax }}). Esgotado some sozinho e volta quando tiver estoque. Tudo que você muda aqui já fica salvo.</p>
                    </div>
                </div>
                <button type="button" wire:click="openPicker('featured')" class="{{ $primary }} w-full sm:w-auto" data-testid="featured-pick"><i class="bi bi-plus-lg"></i>Escolher produtos</button>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <label class="{{ $label }}" for="sf-ft">Título da fileira</label>
                    <input id="sf-ft" type="text" wire:model.blur="featured_title" maxlength="60" placeholder="Destaques da loja" class="{{ $input }}">
                    @error('featured_title')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 sm:pb-2.5">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 {{ $fcount >= $fmax ? $tones['amber'] : $tones['slate'] }}">{{ $fcount }} de {{ $fmax }}</span>
                    <span wire:loading.flex wire:target="pick,moveFeatured,removeFeatured,fillFeatured,clearFeatured,featured_title" class="items-center gap-1 text-violet-500"><i class="bi bi-arrow-repeat animate-spin"></i>Salvando</span>
                    <span wire:loading.remove wire:target="pick,moveFeatured,removeFeatured,fillFeatured,clearFeatured,featured_title" class="inline-flex items-center gap-1 text-emerald-600"><i class="bi bi-cloud-check"></i>Salvo</span>
                </div>
            </div>

            <div class="flex gap-2 overflow-x-auto -mx-1 px-1 pb-1 [scrollbar-width:none]">
                <span class="shrink-0 self-center text-xs font-semibold text-slate-500">Completar com:</span>
                @foreach(['vendidos' => ['Mais vendidos', 'bi-fire'], 'ofertas' => ['Em promoção', 'bi-percent'], 'novos' => ['Novidades', 'bi-stars']] as $src => [$sname, $sico])
                    <button type="button" wire:click="fillFeatured('{{ $src }}')" class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-violet-400 hover:text-violet-600" data-testid="featured-fill-{{ $src }}"><i class="bi {{ $sico }} text-violet-500"></i>{{ $sname }}</button>
                @endforeach
                @if($fcount)
                    <button type="button" wire:click="clearFeatured" wire:confirm="Tirar todos os produtos dos destaques?" class="shrink-0 ml-auto inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"><i class="bi bi-trash3"></i>Limpar</button>
                @endif
            </div>

            @if($featured->isEmpty())
                <button type="button" wire:click="openPicker('featured')" class="w-full rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-8 text-center hover:border-violet-300 transition">
                    <i class="bi bi-star text-3xl text-amber-400"></i>
                    <b class="block mt-2 text-slate-800 dark:text-slate-100">Nenhum produto em destaque</b>
                    <small class="block text-slate-500">Toque para escolher, ou use "Completar com" acima.</small>
                </button>
            @else
                <ol class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2.5 sm:gap-3" data-testid="featured-list">
                    @foreach($featured as $p)
                        @php $promo = $p->livePromotion(); @endphp
                        <li wire:key="ft-{{ $p->id }}" class="min-w-0 relative flex flex-col rounded-2xl border {{ $p->sf_available ? 'border-slate-200 dark:border-slate-700' : 'border-amber-300 dark:border-amber-500/40' }} bg-white dark:bg-slate-900 overflow-hidden" data-testid="featured-item">
                            <div class="relative aspect-square bg-slate-50 dark:bg-slate-800">
                                <img src="{{ $p->image_url }}" alt="" class="w-full h-full object-contain {{ $p->sf_available ? '' : 'opacity-50 grayscale' }}" loading="lazy">
                                <span class="absolute left-2 top-2 min-w-[26px] h-[26px] px-1.5 rounded-full bg-slate-900/80 text-white text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</span>
                                <button type="button" wire:click="removeFeatured({{ $p->id }})" class="absolute right-2 top-2 w-8 h-8 rounded-full bg-white/95 dark:bg-slate-900/90 shadow text-rose-500 hover:bg-rose-50" title="Tirar dos destaques" data-testid="featured-remove"><i class="bi bi-x-lg"></i></button>
                                @if($promo)
                                    <span class="absolute left-2 bottom-2 rounded-md bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5">PROMOÇÃO</span>
                                @endif
                                @unless($p->sf_available)
                                    <span class="absolute inset-x-2 bottom-2 rounded-md bg-amber-500 text-white text-[10px] font-bold px-1.5 py-1 text-center">Esgotado: não aparece agora</span>
                                @endunless
                            </div>
                            <div class="p-2.5 flex-1 flex flex-col">
                                <b class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-2 leading-snug">{{ $p->name }}</b>
                                <span class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                    @if($promo)<s class="text-[11px] font-normal text-slate-400 mr-1">{{ $money($p->price_sale) }}</s>{{ $money($promo->promo_price) }}@else{{ $money($p->price_sale) }}@endif
                                </span>
                                <span class="text-[11px] text-slate-400 truncate">{{ $p->product_code }}</span>
                                <div class="mt-auto pt-2 flex items-center gap-1">
                                    <button type="button" wire:click="moveFeatured({{ $loop->index }}, -1)" @disabled($loop->first) class="flex-1 h-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-25" title="Mover para antes"><i class="bi bi-arrow-left"></i></button>
                                    <button type="button" wire:click="moveFeatured({{ $loop->index }}, 1)" @disabled($loop->last) class="flex-1 h-8 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-25" title="Mover para depois"><i class="bi bi-arrow-right"></i></button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                    @if($fcount < $fmax)
                        <li class="min-w-0">
                            <button type="button" wire:click="openPicker('featured')" class="w-full h-full min-h-[180px] rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 text-slate-400 hover:border-violet-300 hover:text-violet-500 flex flex-col items-center justify-center gap-1 transition"><i class="bi bi-plus-circle text-2xl"></i><span class="text-xs font-semibold">Adicionar</span></button>
                        </li>
                    @endif
                </ol>
            @endif
        </section>
    @endif

    {{-- ===================== Coleções ===================== --}}
    @if($tab === 'colecoes')
        <div class="space-y-3">
            <div class="flex items-center gap-3 flex-wrap">
                <p class="text-sm text-slate-500 dark:text-slate-400 flex-1 min-w-[220px]">Grupos de produtos com banner próprio, como "Dia das Mães" ou "Kits para presente". Cada coleção vira uma fileira na página inicial e tem a sua página.</p>
                <a href="{{ route('promotions.storefront.collection.create') }}" class="{{ $primary }}" data-testid="collection-new"><i class="bi bi-plus-lg"></i>Nova coleção</a>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto [scrollbar-width:none]" data-testid="collection-idea-row">
                <span class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400"><i class="bi bi-lightbulb text-amber-500"></i> Ideias:</span>
                @foreach($front::COLLECTION_IDEAS[$kind] ?? [] as $i => [$iname, $idesc, $irule])
                    <a href="{{ route('promotions.storefront.collection.create', ['ideia' => $i]) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-violet-400"><i class="bi {{ $front::COLLECTION_RULES[$irule][1] }} text-violet-500"></i>{{ $iname }}</a>
                @endforeach
            </div>
            @if($missingCats->isNotEmpty())
                <div class="{{ $card }} p-3 sm:p-4 flex items-center gap-3 flex-wrap border-violet-200 dark:border-violet-500/30" data-testid="missing-cats">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50 dark:bg-violet-500/15 text-violet-600 text-lg"><i class="bi bi-tags"></i></div>
                    <p class="flex-1 min-w-[200px] text-sm text-slate-600 dark:text-slate-300"><b class="text-slate-900 dark:text-white">{{ $missingCats->count() === 1 ? '1 categoria ainda sem coleção' : $missingCats->count() . ' categorias ainda sem coleção' }}</b>
                        <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">{{ $missingCats->pluck('name')->take(6)->join(', ') }}{{ $missingCats->count() > 6 ? '…' : '' }}. Cada uma vira uma coleção automática com capa própria.</span></p>
                    <button type="button" wire:click="createCategoryCollections" wire:loading.attr="disabled" class="{{ $primary }}" data-testid="create-cat-collections"><i class="bi bi-magic"></i>Criar as coleções</button>
                </div>
            @endif
            @if($collections->isEmpty())
                <x-empty-state icon="bi-collection" tone="violet" title="Nenhuma coleção ainda"
                               text="Escolha os produtos ou use uma regra (categoria, em promoção, mais vendidos, faixa de preço) e a coleção se atualiza sozinha." />
            @else
                <div class="grid gap-3 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" data-testid="collection-list">
                    @foreach($collections as $c)
                        <article wire:key="col-{{ $c->id }}" class="{{ $card }} overflow-hidden flex flex-col {{ $c->active ? '' : 'opacity-70' }}">
                            @include('portal.partials.collection-head', ['c' => $c, 'href' => null, 'owner' => auth()->id()])
                            <div class="p-3 flex flex-col gap-2 flex-1">
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 flex-wrap">@php $rn = $front::COLLECTION_RULES[$c->rule ?? 'manual'] ?? $front::COLLECTION_RULES['manual']; @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $c->isAuto() ? $tones['emerald'] : $tones['slate'] }}"><i class="bi {{ $c->isAuto() ? 'bi-arrow-repeat' : $rn[1] }}"></i>{{ $c->isAuto() ? $rn[0] . ', automática' : 'Escolhidos à mão' }}</span>
                                    <span><i class="bi bi-box-seam"></i> {{ (int) ($counts[$c->id] ?? 0) }} à venda</span></p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" wire:click="toggleCollection({{ $c->id }}, 'active')" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->active ? $tones['emerald'] : $tones['slate'] }}"><i class="bi {{ $c->active ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i> {{ $c->active ? 'Ligada' : 'Desligada' }}</button>
                                    <button type="button" wire:click="toggleCollection({{ $c->id }}, 'show_on_home')" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->show_on_home ? $tones['sky'] : $tones['slate'] }}"><i class="bi {{ $c->show_on_home ? 'bi-house-check' : 'bi-house-slash' }}"></i> {{ $c->show_on_home ? 'Na página inicial' : 'Fora da página inicial' }}</button>
                                </div>
                                <div class="flex items-center gap-1.5 mt-auto pt-1">
                                    <button type="button" wire:click="moveCollection({{ $c->id }}, -1)" class="{{ $iconBtn }}" title="Mover para cima" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" wire:click="moveCollection({{ $c->id }}, 1)" class="{{ $iconBtn }}" title="Mover para baixo" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button>
                                    <a href="{{ route('portal.catalog', ['userId' => auth()->id(), 'colecao' => $c->slug]) }}" target="_blank" rel="noopener" class="{{ $iconBtn }}" title="Ver no catálogo"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a href="{{ route('promotions.storefront.collection.edit', $c->id) }}" class="{{ $phBtn }} !py-1.5 ml-auto" data-testid="collection-edit"><i class="bi bi-pencil text-indigo-500"></i>Editar</a>
                                    <button type="button" x-data @click="fmConfirm({ title: 'Excluir a coleção {{ addslashes($c->name) }}?', message: 'Os produtos continuam no catálogo; só o grupo some.', confirmText: 'Excluir' }).then(ok => ok && $wire.deleteCollection({{ $c->id }}))" class="{{ $iconBtn }}" title="Excluir"><i class="bi bi-trash3 text-rose-500"></i></button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ===================== Aviso no topo ===================== --}}
    @if($tab === 'aviso')
        <section class="{{ $card }} p-4 sm:p-5 space-y-4 max-w-2xl" data-testid="notice-panel">
            <div class="flex items-start gap-3">
                <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-500/15 text-amber-600 text-lg"><i class="bi bi-megaphone"></i></div>
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white">Aviso no topo do catálogo</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Uma linha curta que aparece em todas as páginas do catálogo. Ex.: "Entrega grátis no bairro acima de R$ 150" ou "Use o cupom BEMVINDA10".</p>
                </div>
            </div>
            <div>
                <label class="{{ $label }}" for="sf-nt">Texto do aviso</label>
                <input id="sf-nt" type="text" wire:model.live.debounce.400ms="notice_text" maxlength="160" placeholder="Entrega grátis no bairro acima de R$ 150" class="{{ $input }}">
                @error('notice_text')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $label }}" for="sf-nl">Link ao tocar <span class="font-normal text-slate-400">(opcional)</span></label>
                <input id="sf-nl" type="url" wire:model="notice_link" placeholder="https://" class="{{ $input }}">
                @error('notice_link')<p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 cursor-pointer">
                <input type="checkbox" wire:model="notice_active" class="mt-0.5 rounded text-violet-500 focus:ring-violet-400">
                <span class="text-sm text-slate-700 dark:text-slate-200"><b>Mostrar o aviso</b><small class="block text-xs text-slate-500">Desligado, o texto fica guardado aqui.</small></span>
            </label>
            @if(trim($notice_text) !== '')
                <div>
                    <span class="{{ $label }}">Como fica</span>
                    <p class="flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-3 py-2 text-sm font-semibold text-center"><i class="bi bi-megaphone-fill text-amber-500"></i>{{ $notice_text }}</p>
                </div>
            @endif
            <div class="flex justify-end">
                <button type="button" wire:click="saveNotice" class="{{ $primary }}" data-testid="notice-save"><i class="bi bi-check2"></i>Salvar aviso</button>
            </div>
        </section>
    @endif

    @if($tab === 'automacoes')
        @php
            $H = \App\Services\Portal\HomeLayout::class;
            $skip = (array) ($auto['skip'] ?? []);
            $nowDate = collect($dateThemes)->first(fn ($w, $k) => $w['now'] && ! in_array($H::THEMES[$k]['date'], $skip, true));
            $nowKey = $nowDate ? collect($dateThemes)->search($nowDate) : null;
            $nextKey = collect($dateThemes)->keys()->first(fn ($k) => ! $dateThemes[$k]['now'] && ! in_array($H::THEMES[$k]['date'], $skip, true));
            $sw = fn ($on) => 'relative inline-flex shrink-0 w-11 h-6 rounded-full transition ' . ($on ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600');
            $knob = fn ($on) => 'absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all ' . ($on ? 'left-[22px]' : 'left-0.5');
            $sel = 'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-violet-400 focus:border-violet-400';
        @endphp
        <div class="space-y-4" data-testid="auto-tab">
            <section class="{{ $card }} p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br from-emerald-500 to-teal-500 text-white text-lg"><i class="bi bi-robot"></i></div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-900 dark:text-white">O site se arruma sozinho</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ligue o que quiser. Tudo vale na hora em que o cliente abre o catálogo, sem você lembrar de nada. Quando a data acaba, o site volta para o seu tema.</p>
                    </div>
                </div>
            </section>

            {{-- 1. Tema das datas --}}
            <section class="{{ $card }} p-4 sm:p-5 space-y-3" data-testid="auto-theme">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-calendar-heart text-rose-500"></i>Trocar o tema nas datas</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Black Friday, Natal, Dia das Mães… o site veste o tema da data no período dela e depois volta sozinho.</p>
                    </div>
                    <button type="button" wire:click="setAuto('theme', {{ $auto['theme'] ? 'false' : 'true' }})" class="{{ $sw($auto['theme']) }}" role="switch" aria-checked="{{ $auto['theme'] ? 'true' : 'false' }}" data-testid="auto-theme-switch"><span class="{{ $knob($auto['theme']) }}"></span></button>
                </div>
                @if($auto['theme'])
                    <p class="rounded-xl px-3 py-2 text-sm {{ $nowKey ? $tones['emerald'] : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}" data-testid="auto-theme-status">
                        @if($nowKey)
                            {{ $H::THEMES[$nowKey]['emoji'] }} <b>Agora o site está com o tema {{ $H::THEMES[$nowKey]['name'] }}</b>, até {{ $dateThemes[$nowKey]['end']->format('d/m') }}.
                        @elseif($nextKey)
                            Próxima troca: {{ $H::THEMES[$nextKey]['emoji'] }} <b>{{ $H::THEMES[$nextKey]['name'] }}</b>, de {{ $dateThemes[$nextKey]['start']->format('d/m') }} a {{ $dateThemes[$nextKey]['end']->format('d/m') }}.
                        @else
                            Nenhuma data ligada.
                        @endif
                    </p>
                    <div class="grid sm:grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model.live="auto.banner" class="rounded text-violet-500 focus:ring-violet-400"> Mostrar o banner da data no começo do carrossel</label>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model.live="auto.notice" class="rounded text-violet-500 focus:ring-violet-400"> Trocar o aviso do topo pelo da data</label>
                    </div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Quais datas (toque para tirar)</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($dateThemes as $k => $w)
                            @php $off = in_array($H::THEMES[$k]['date'], $skip, true); @endphp
                            <button type="button" wire:click="toggleAutoDate('{{ $H::THEMES[$k]['date'] }}')" data-testid="auto-date-{{ $k }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $off ? 'border-slate-200 dark:border-slate-700 text-slate-400 line-through' : 'border-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' }}">
                                {{ $H::THEMES[$k]['emoji'] }} {{ $H::THEMES[$k]['name'] }} <span class="font-normal opacity-75">{{ $w['start']->format('d/m') }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="grid gap-4 xl:grid-cols-2">
                {{-- 2. Destaques --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-2" data-testid="auto-featured">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-stars text-amber-500"></i>Completar os destaques sozinho</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Os que você escolheu ficam primeiro; o resto da fileira (até 15) se enche e se atualiza ao longo do dia.</p>
                    <select wire:model.live="auto.featured" class="{{ $sel }}" data-testid="auto-featured-select">
                        @foreach($H::AUTO_FEATURED as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach
                    </select>
                </section>

                {{-- 3. Selo novidade --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-2" data-testid="auto-new">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-patch-check-fill text-violet-500"></i>Selo “Novidade” nos produtos novos</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Produto que você cadastrar aparece com o selo pelo tempo escolhido, depois o selo sai sozinho.</p>
                    <div class="grid grid-cols-4 gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 p-1">
                        @foreach($H::AUTO_NEW as $d => $n)
                            <button type="button" wire:click="setAuto('newbadge', {{ $d }})" data-testid="auto-new-{{ $d }}" class="rounded-lg py-2 text-xs font-semibold {{ (int) $auto['newbadge'] === $d ? 'bg-white dark:bg-slate-700 shadow text-violet-600 dark:text-violet-300' : 'text-slate-500' }}">{{ $n }}</button>
                        @endforeach
                    </div>
                </section>

                {{-- 4. Ordem --}}
                <section class="{{ $card }} p-4 sm:p-5 space-y-2" data-testid="auto-sort">
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-sort-down text-sky-500"></i>Ordem da lista de produtos</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Como a lista de todos os produtos abre para o cliente. Ele ainda pode trocar.</p>
                    <select wire:model.live="auto.sort" class="{{ $sel }}" data-testid="auto-sort-select">
                        @foreach($H::AUTO_SORT as $k => $n)<option value="{{ $k }}">{{ $n }}</option>@endforeach
                    </select>
                </section>

                {{-- 5. Coleções --}}
                <section class="{{ $card }} p-4 sm:p-5" data-testid="auto-cats">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-collection text-fuchsia-500"></i>Coleção para cada categoria nova</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Criou uma categoria e colocou produtos nela? A coleção com capa aparece sozinha no site.</p>
                        </div>
                        <button type="button" wire:click="setAuto('cats', {{ $auto['cats'] ? 'false' : 'true' }})" class="{{ $sw($auto['cats']) }}" role="switch" aria-checked="{{ $auto['cats'] ? 'true' : 'false' }}" data-testid="auto-cats-switch"><span class="{{ $knob($auto['cats']) }}"></span></button>
                    </div>
                </section>
            </div>

            <section class="{{ $card }} p-4 sm:p-5 space-y-1.5">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2"><i class="bi bi-check2-all text-emerald-500"></i>Já funciona sozinho, sem ligar nada</h3>
                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-1 list-disc pl-5">
                    <li>Produto sem estoque some do site e volta quando entra estoque.</li>
                    <li>Banner com data de fim sai do ar sozinho no dia.</li>
                    <li>Promoção agendada entra e sai na hora certa, com o preço de oferta e o “-%” no card.</li>
                    <li>Quem pediu “avise-me” recebe aviso quando o produto volta.</li>
                    <li>Coleções por regra (mais vendidos, ofertas, novos, faixa de preço) se atualizam sozinhas.</li>
                </ul>
            </section>
        </div>
    @endif

    </x-portal-settings-nav>

    @if(in_array($tab, ['geral', 'identidade', 'banners', 'destaques', 'colecoes', 'aviso', 'automacoes'], true))
        @include('livewire.promotions.partials.storefront-preview', ['inline' => false])
    @endif

    @include('livewire.promotions.partials.storefront-picker')
</div>
