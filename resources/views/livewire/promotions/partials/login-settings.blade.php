{{-- Vitrine › Tela de login: como o cliente vê o entrar / criar conta do portal. Tudo salva na hora. --}}
@php
    $LL = \App\Services\Portal\LoginLook::class;
    $lg = $login;
    $logoUrl = ($identity['logo'] ?? null) ? asset('storage/' . $identity['logo']) : null;
    $loginSrc = route('portal.login', ['loja' => auth()->id(), 'previa' => $previewKey]);
    $opt = fn (bool $on) => $on
        ? 'border-violet-400 bg-violet-50/70 dark:bg-violet-500/10 ring-2 ring-violet-200 dark:ring-violet-500/30'
        : 'border-slate-200 dark:border-slate-700 hover:border-violet-300 bg-white dark:bg-slate-900';
    $secTitle = fn ($icon, $tone, $title, $sub) => '<div class="flex items-start gap-3"><div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-lg ' . $tone . '"><i class="bi ' . $icon . '"></i></div><div class="min-w-0"><h2 class="font-bold text-slate-900 dark:text-white">' . e($title) . '</h2><p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">' . e($sub) . '</p></div></div>';
    $switches = [
        'google' => ['Entrar com Google', 'O botão branco do Google embaixo do Entrar.', 'bi-google'],
        'register' => ['Botão "Criar minha conta"', 'Para quem compra pela primeira vez.', 'bi-person-plus'],
        'show_name' => ['Nome da loja embaixo da logo', 'Desligue se o nome já está escrito na logo.', 'bi-fonts'],
    ];
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_420px] 2xl:grid-cols-[minmax(0,1fr)_480px] items-start" data-testid="login-panel">
    <div class="space-y-4 min-w-0">

        {{-- Logo --}}
        <section class="{{ $card }} p-4 sm:p-5 space-y-4">
            {!! $secTitle('bi-image', 'bg-violet-50 dark:bg-violet-500/15 text-violet-600', 'Logo da loja', 'A tela de login, a aba do navegador e o ícone na tela de início do celular usam a logo de Informações da loja.') !!}
            <div class="flex flex-col sm:flex-row gap-4 sm:items-center">
                <div class="shrink-0 w-20 h-20 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white flex items-center justify-center overflow-hidden">
                    @if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo" class="max-w-full max-h-full object-contain p-1.5">@else<i class="bi bi-image text-2xl text-slate-300"></i>@endif
                </div>
                <div class="min-w-0 flex-1 space-y-2">
                    @if($logoUrl)
                        <p class="text-sm text-slate-700 dark:text-slate-200"><i class="bi bi-check-circle-fill text-emerald-500"></i> Sua logo já aparece no login e no ícone.</p>
                    @else
                        <p class="text-sm text-amber-700 dark:text-amber-300"><i class="bi bi-exclamation-circle"></i> Sem logo ainda: o login mostra o ícone do FlowManager.</p>
                    @endif
                    <button type="button" wire:click="$set('tab', 'identidade')" class="{{ $phBtn }}"><i class="bi bi-upload text-violet-500"></i>{{ $logoUrl ? 'Trocar a logo' : 'Enviar a logo' }}</button>
                </div>
            </div>
            @if($logoUrl)
                <div>
                    <span class="{{ $label }}">Como a logo aparece</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($LL::LOGO_BOX as $k => [$n, $d])
                            <button type="button" wire:click="setLogin('logo_box', '{{ $k }}')" class="rounded-xl border p-3 text-left transition {{ $opt($lg['logo_box'] === $k) }}" data-testid="login-logo-{{ $k }}">
                                <span class="flex items-center gap-2">
                                    <span class="w-9 h-9 shrink-0 flex items-center justify-center {{ $k === 'caixa' ? 'bg-white rounded-lg shadow ring-1 ring-slate-200' : '' }}"><img src="{{ $logoUrl }}" alt="" class="max-w-full max-h-full object-contain {{ $k === 'caixa' ? 'p-1' : '' }}"></span>
                                    <b class="text-sm text-slate-800 dark:text-slate-100">{{ $n }}</b>
                                </span>
                                <small class="block mt-1 text-xs text-slate-500">{{ $d }}</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- O que aparece --}}
        <section class="{{ $card }} p-4 sm:p-5 space-y-3">
            {!! $secTitle('bi-toggles', 'bg-sky-50 dark:bg-sky-500/15 text-sky-600', 'O que aparece', 'Ligue ou desligue as partes da tela.') !!}
            @foreach($switches as $k => [$n, $d, $ic])
                <button type="button" wire:click="setLogin('{{ $k }}', {{ $lg[$k] ? 'false' : 'true' }})" role="switch" aria-checked="{{ $lg[$k] ? 'true' : 'false' }}"
                        class="w-full flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/60 transition" data-testid="login-switch-{{ $k }}">
                    <i class="bi {{ $ic }} text-lg text-slate-400 shrink-0"></i>
                    <span class="min-w-0 flex-1"><b class="block text-sm text-slate-800 dark:text-slate-100">{{ $n }}</b><small class="block text-xs text-slate-500">{{ $d }}</small></span>
                    <span class="shrink-0 relative w-11 h-6 rounded-full transition {{ $lg[$k] ? 'bg-violet-500' : 'bg-slate-300 dark:bg-slate-600' }}"><span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all {{ $lg[$k] ? 'left-[22px]' : 'left-0.5' }}"></span></span>
                </button>
            @endforeach
        </section>

        {{-- Cores --}}
        <section class="{{ $card }} p-4 sm:p-5 space-y-4">
            {!! $secTitle('bi-palette', 'bg-fuchsia-50 dark:bg-fuchsia-500/15 text-fuchsia-600', 'Cores', 'Fundo da tela e cor dos botões.') !!}
            <div>
                <span class="{{ $label }}">Fundo</span>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($LL::MODES as $k => [$n, $d])
                        @php $bg = match ($k) { 'claro' => '#f6f7fb', 'cor' => 'linear-gradient(135deg,' . $lg['c1'] . ',' . $lg['c2'] . ')', default => '#0b0b12' }; @endphp
                        <button type="button" wire:click="setLogin('mode', '{{ $k }}')" class="rounded-xl border p-2 text-left transition {{ $opt($lg['mode'] === $k) }}" data-testid="login-mode-{{ $k }}">
                            <span class="block h-14 rounded-lg relative overflow-hidden" style="background: {{ $bg }}">
                                <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-9 rounded {{ $k === 'escuro' ? 'bg-white/10 ring-1 ring-white/15' : 'bg-white shadow' }}"></span>
                            </span>
                            <b class="block mt-1.5 text-xs sm:text-sm text-slate-800 dark:text-slate-100">{{ $n }}</b>
                            <small class="hidden sm:block text-[11px] text-slate-500 leading-tight">{{ $d }}</small>
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <span class="{{ $label }}">Cores prontas</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($LL::PALETTES as $k => [$n, $a, $b])
                        <button type="button" wire:click="setLoginPalette('{{ $k }}')" title="{{ $n }}"
                                class="inline-flex items-center gap-2 rounded-full border pl-1 pr-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 transition {{ $opt($lg['c1'] === $a && $lg['c2'] === $b) }}" data-testid="login-palette-{{ $k }}">
                            <span class="w-6 h-6 rounded-full" style="background: linear-gradient(135deg, {{ $a }}, {{ $b }})"></span>{{ $n }}
                        </button>
                    @endforeach
                    <button type="button" wire:click="useSiteColorsOnLogin" class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-slate-300 dark:border-slate-600 px-3 py-1 text-xs font-semibold text-violet-600 dark:text-violet-300 hover:bg-violet-50 dark:hover:bg-violet-500/10"><i class="bi bi-magic"></i>Igual ao site</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['c1' => 'Cor principal', 'c2' => 'Segunda cor'] as $k => $n)
                    <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 p-2.5 cursor-pointer">
                        <input type="color" wire:model.live.debounce.500ms="login.{{ $k }}" class="w-10 h-10 rounded-lg border-0 bg-transparent cursor-pointer p-0" data-testid="login-{{ $k }}">
                        <span class="min-w-0"><b class="block text-sm text-slate-800 dark:text-slate-100">{{ $n }}</b><small class="block text-xs text-slate-500 uppercase">{{ $lg[$k] }}</small></span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Formato --}}
        <section class="{{ $card }} p-4 sm:p-5 space-y-4">
            {!! $secTitle('bi-bounding-box-circles', 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600', 'Formato', 'Cantos dos campos e botões, estilo do botão e a arrumação da tela.') !!}
            <div>
                <span class="{{ $label }}">Cantos</span>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($LL::CORNERS as $k => [$n, $r])
                        <button type="button" wire:click="setLogin('corners', '{{ $k }}')" class="rounded-xl border p-3 transition {{ $opt($lg['corners'] === $k) }}" data-testid="login-corners-{{ $k }}">
                            <span class="block h-8 text-white text-[11px] font-bold leading-8 text-center" style="border-radius: {{ min($r, 999) }}px; background: linear-gradient(90deg, {{ $lg['c1'] }}, {{ $lg['c2'] }})">Entrar</span>
                            <b class="block mt-1.5 text-xs sm:text-sm text-slate-800 dark:text-slate-100 text-center">{{ $n }}</b>
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <span class="{{ $label }}">Botão</span>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($LL::BUTTONS as $k => $n)
                        <button type="button" wire:click="setLogin('button', '{{ $k }}')" class="rounded-xl border p-3 transition {{ $opt($lg['button'] === $k) }}" data-testid="login-button-{{ $k }}">
                            <span class="block h-8 rounded-lg text-white text-[11px] font-bold leading-8 text-center" style="background: {{ $k === 'solido' ? $lg['c1'] : 'linear-gradient(90deg,' . $lg['c1'] . ',' . $lg['c2'] . ')' }}">Entrar</span>
                            <b class="block mt-1.5 text-sm text-slate-800 dark:text-slate-100 text-center">{{ $n }}</b>
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <span class="{{ $label }}">Arrumação da tela</span>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($LL::LAYOUTS as $k => [$n, $d])
                        <button type="button" wire:click="setLogin('layout', '{{ $k }}')" class="rounded-xl border p-2.5 text-left transition {{ $opt($lg['layout'] === $k) }}" data-testid="login-layout-{{ $k }}">
                            <span class="flex h-14 rounded-lg bg-slate-100 dark:bg-slate-800 p-1.5 gap-1.5 {{ $k === 'centro' ? 'flex-col items-center justify-center' : 'items-center' }}">
                                @if($k === 'centro')
                                    <span class="w-4 h-4 rounded bg-slate-300 dark:bg-slate-600"></span><span class="w-12 h-5 rounded bg-white dark:bg-slate-600 shadow-sm"></span>
                                @else
                                    <span class="flex-1 flex flex-col gap-1 pl-1"><span class="w-5 h-5 rounded bg-slate-300 dark:bg-slate-600"></span><span class="w-10 h-1.5 rounded bg-slate-300 dark:bg-slate-600"></span></span><span class="w-12 h-10 rounded bg-white dark:bg-slate-600 shadow-sm"></span>
                                @endif
                            </span>
                            <b class="block mt-1.5 text-sm text-slate-800 dark:text-slate-100">{{ $n }}</b>
                            <small class="block text-xs text-slate-500 leading-tight">{{ $d }}</small>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Textos --}}
        <section class="{{ $card }} p-4 sm:p-5 space-y-3">
            {!! $secTitle('bi-chat-quote', 'bg-amber-50 dark:bg-amber-500/15 text-amber-600', 'Textos', 'Deixe em branco para usar o texto padrão.') !!}
            <div>
                <label class="{{ $label }}" for="lg-heading">Título</label>
                <input id="lg-heading" type="text" wire:model.live.debounce.600ms="login.heading" maxlength="60" placeholder="Bem-vindo de volta 👋" class="{{ $input }}" data-testid="login-heading">
            </div>
            <div>
                <label class="{{ $label }}" for="lg-sub">Frase embaixo</label>
                <input id="lg-sub" type="text" wire:model.live.debounce.600ms="login.subtitle" maxlength="140" placeholder="Entre com seu e-mail ou com o login enviado pela loja." class="{{ $input }}" data-testid="login-subtitle">
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <button type="button" wire:click="resetLogin" wire:confirm="Voltar a tela de login ao padrão?" class="text-sm font-semibold text-slate-500 hover:text-rose-600"><i class="bi bi-arrow-counterclockwise"></i> Voltar ao padrão</button>
            <a href="{{ route('portal.login', ['loja' => auth()->id()]) }}" target="_blank" rel="noopener" class="{{ $phBtn }}"><i class="bi bi-box-arrow-up-right text-violet-500"></i>Abrir o login</a>
        </div>
    </div>

    {{-- Prévia ao vivo --}}
    <aside class="{{ $card }} p-3 xl:sticky xl:top-4 min-w-0" data-testid="login-preview"
           x-data="{ dev: 'cel', w: 0, sizes: { cel: [390, 760], pc: [1280, 800] },
                     sc() { const [iw] = this.sizes[this.dev]; return Math.min(1, this.w / iw); } }"
           x-init="w = $refs.box.clientWidth; new ResizeObserver(() => w = $refs.box.clientWidth).observe($refs.box)">
        <div class="flex items-center gap-2 pb-2">
            <b class="text-sm text-slate-800 dark:text-slate-100"><i class="bi bi-eye text-violet-500"></i> Como o cliente vê</b>
            <span wire:loading class="text-xs text-slate-400"><i class="bi bi-arrow-repeat animate-spin"></i></span>
            <div class="ml-auto inline-flex rounded-lg bg-slate-100 dark:bg-slate-800 p-0.5 text-xs font-semibold">
                <button type="button" @click="dev = 'cel'" :class="dev === 'cel' ? 'bg-white dark:bg-slate-700 text-violet-700 dark:text-violet-200 shadow-sm' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-phone"></i> Celular</button>
                <button type="button" @click="dev = 'pc'" :class="dev === 'pc' ? 'bg-white dark:bg-slate-700 text-violet-700 dark:text-violet-200 shadow-sm' : 'text-slate-500'" class="rounded-md px-2.5 py-1"><i class="bi bi-display"></i> Computador</button>
            </div>
        </div>
        <div x-ref="box" class="relative w-full overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
             :style="`height:${Math.round(sizes[dev][1] * sc())}px`">
            <iframe src="{{ $loginSrc }}" wire:key="login-pv-{{ $previewKey }}" title="Prévia da tela de login" loading="lazy"
                    class="absolute top-0 border-0 origin-top-left bg-white"
                    :style="`width:${sizes[dev][0]}px;height:${sizes[dev][1]}px;transform:scale(${sc()});left:${Math.max(0, (w - sizes[dev][0] * sc()) / 2)}px`"
                    data-testid="login-preview-frame"></iframe>
        </div>
    </aside>
</div>
