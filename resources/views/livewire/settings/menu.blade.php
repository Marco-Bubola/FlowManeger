<?php

use App\Support\AppMenu;
use Livewire\Volt\Component;

new class extends Component {
    /** Áreas desligadas e páginas escondidas (preferences.menu). */
    public array $areasOff = [];
    public array $hidden = [];

    public function mount(): void
    {
        $prefs = AppMenu::prefs();
        $this->areasOff = $prefs['areas_off'];
        $this->hidden = $prefs['hidden'];
    }

    public function toggleArea(string $area): void
    {
        if (! array_key_exists($area, AppMenu::areas())) {
            return;
        }
        $this->areasOff = in_array($area, $this->areasOff, true)
            ? array_values(array_diff($this->areasOff, [$area]))
            : array_values(array_merge($this->areasOff, [$area]));
        $this->persist();
    }

    public function toggleItem(string $route): void
    {
        $all = collect(AppMenu::areas())->flatMap(fn ($a) => array_column($a[5], 0))->all();
        if (! in_array($route, $all, true)) {
            return;
        }
        $this->hidden = in_array($route, $this->hidden, true)
            ? array_values(array_diff($this->hidden, [$route]))
            : array_values(array_merge($this->hidden, [$route]));
        $this->persist();
    }

    public function showAll(): void
    {
        $this->areasOff = [];
        $this->hidden = [];
        $this->persist();
    }

    protected function persist(): void
    {
        $user = auth()->user();
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $prefs['menu'] = ['areas_off' => $this->areasOff, 'hidden' => $this->hidden];
        $user->update(['preferences' => $prefs]);
        // Atualiza o menu desta tela na hora, sem recarregar
        $this->dispatch('menu-saved', css: AppMenu::css($user->fresh()));
    }
}; ?>

@php
    $areas = \App\Support\AppMenu::areas();
    $visibleAreas = count(array_diff(array_keys($areas), $areasOff));
    $hiddenCount = \App\Support\AppMenu::hiddenCount();
@endphp

<section class="settings-menu-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="mn-page" x-data="{ saved: false }"
         @menu-saved.window="saved = true; clearTimeout($el._t); $el._t = setTimeout(() => saved = false, 1800);
            const st = document.getElementById('fm-menu-prefs'); if (st) st.textContent = $event.detail.css; window.fmTidyMenu && window.fmTidyMenu()">

        <div class="mn-hero bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600">
            <span class="mn-glow"></span>
            <div class="mn-hero-ico"><i class="bi bi-layout-sidebar-inset"></i></div>
            <div class="mn-hero-txt">
                <span class="mn-kicker"><i class="bi bi-sliders"></i> Seu menu</span>
                <h2>{{ $visibleAreas }} de {{ count($areas) }} áreas no menu</h2>
                <p>Desligue o que você não usa. Some do menu lateral, do "Mais" e do botão Criar no celular. Nada é apagado: é só ligar de novo.</p>
            </div>
            <span class="mn-saved" x-show="saved" x-transition.opacity x-cloak><i class="bi bi-cloud-check"></i> Salvo</span>
        </div>

        <div class="mn-grid">
            <div class="mn-list">
                @foreach($areas as $key => [$name, $icon, $grad, $class, $prefixes, $items])
                    @php $areaOn = ! in_array($key, $areasOff, true); @endphp
                    <div class="mn-area {{ $areaOn ? 'is-on' : '' }}" wire:key="area-{{ $key }}">
                        <button type="button" class="mn-area-head" wire:click="toggleArea('{{ $key }}')" aria-pressed="{{ $areaOn ? 'true' : 'false' }}">
                            <span class="mn-ico bg-gradient-to-br {{ $grad }}"><i class="bi {{ $icon }}"></i></span>
                            <span class="mn-txt">
                                <b>{{ $name }}</b>
                                <small>
                                    @if(! $areaOn)
                                        Fora do menu
                                    @elseif($items)
                                        @php $shown = count(array_filter($items, fn ($i) => ! in_array($i[0], $hidden, true))); @endphp
                                        {{ $shown }} de {{ count($items) }} páginas no menu
                                    @else
                                        Área inteira no menu
                                    @endif
                                </small>
                            </span>
                            <span class="mn-switch"><span></span></span>
                        </button>
                        @if($areaOn && count($items) > 1)
                            <div class="mn-items">
                                @foreach($items as [$route, $label, $iIcon])
                                    @php $itemOn = ! in_array($route, $hidden, true); @endphp
                                    <button type="button" class="mn-item {{ $itemOn ? 'is-on' : '' }}" wire:click="toggleItem('{{ $route }}')" wire:key="it-{{ $route }}" aria-pressed="{{ $itemOn ? 'true' : 'false' }}">
                                        <i class="bi {{ $iIcon }}"></i>
                                        <span>{{ $label }}</span>
                                        <em><i class="bi {{ $itemOn ? 'bi-eye' : 'bi-eye-slash' }}"></i></em>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <aside class="mn-side">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-eye"></i></span>
                        <div class="min-w-0">
                            <h2>Como fica o menu</h2>
                            <p>As áreas na ordem em que aparecem.</p>
                        </div>
                    </div>
                    <div class="mn-preview">
                        <span class="mn-pv-row is-fixed"><i class="bi bi-house"></i> Início</span>
                        @foreach($areas as $key => [$name, $icon, $grad])
                            @if(! in_array($key, $areasOff, true))
                                <span class="mn-pv-row"><span class="mn-pv-dot bg-gradient-to-br {{ $grad }}"><i class="bi {{ $icon }}"></i></span> {{ $name }}</span>
                            @endif
                        @endforeach
                        <span class="mn-pv-row is-fixed"><i class="bi bi-gear"></i> Conta e Plano</span>
                    </div>
                    <p class="mn-note"><i class="bi bi-info-circle"></i> Início e Conta e Plano ficam sempre, para você nunca ficar sem caminho até aqui.</p>
                </div>
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-slate-500 to-slate-700"><i class="bi bi-arrow-counterclockwise"></i></span>
                        <div class="min-w-0">
                            <h2>{{ $hiddenCount ? $hiddenCount . ' ' . ($hiddenCount === 1 ? 'página escondida' : 'páginas escondidas') : 'Tudo aparecendo' }}</h2>
                            <p>As páginas escondidas continuam funcionando pelos atalhos e links das outras telas.</p>
                        </div>
                    </div>
                    <button type="button" class="ap-btn-ghost w-full justify-center" wire:click="showAll" @disabled(! $hiddenCount)><i class="bi bi-eye"></i> Mostrar tudo de novo</button>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .mn-page { display: flex; flex-direction: column; gap: 1rem; }
        .mn-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .mn-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .mn-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.7rem; }
        .mn-hero-txt { position: relative; flex: 1; min-width: 0; }
        .mn-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .mn-hero-txt h2 { font-size: 1.45rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .mn-hero-txt p { font-size: .82rem; opacity: .92; max-width: 46rem; }
        .mn-saved { position: absolute; top: .9rem; right: 1rem; display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 800; background: rgba(255, 255, 255, .95); color: #059669; border-radius: 999px; padding: .25rem .6rem; }
        .mn-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1rem; align-items: start; }
        .mn-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; align-items: start; }
        .mn-side { position: sticky; top: 1rem; display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .mn-area { border-radius: 1.2rem; border: 1px solid #e2e8f0; background: #fff; overflow: hidden; transition: border-color .15s, box-shadow .15s; }
        .mn-area.is-on { border-color: var(--color-indigo-200); box-shadow: 0 10px 26px -20px var(--color-indigo-600); }
        .dark .mn-area { background: #0f172a; border-color: #1e293b; }
        .dark .mn-area.is-on { border-color: rgba(99, 102, 241, .35); box-shadow: none; }
        .mn-area-head { width: 100%; display: flex; align-items: center; gap: .75rem; padding: .85rem .9rem; text-align: left; }
        .mn-ico { flex: none; display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: .85rem; color: #fff; font-size: 1.05rem; transition: filter .15s, opacity .15s; }
        .mn-area:not(.is-on) .mn-ico { filter: grayscale(1); opacity: .5; }
        .mn-txt { display: block; flex: 1 1 auto; min-width: 0; }
        .mn-txt b { display: block; font-size: .9rem; color: #0f172a; }
        .mn-txt small { display: block; font-size: .73rem; color: #64748b; }
        .dark .mn-txt b { color: #f1f5f9; }
        .mn-area:not(.is-on) .mn-txt b { color: #94a3b8; }
        .mn-switch { flex: none; position: relative; width: 2.4rem; height: 1.4rem; border-radius: 999px; background: #cbd5e1; transition: background .2s; }
        .mn-switch span { position: absolute; top: .2rem; left: .2rem; width: 1rem; height: 1rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .2s; }
        .mn-area.is-on .mn-switch { background: var(--color-indigo-600); }
        .mn-area.is-on .mn-switch span { transform: translateX(1rem); }
        .dark .mn-switch { background: #334155; }
        .mn-items { display: flex; flex-wrap: wrap; gap: .4rem; padding: .7rem .9rem .9rem; border-top: 1px dashed #e2e8f0; background: #f8fafc; }
        .dark .mn-items { background: #0b1220; border-color: #1e293b; }
        .mn-item { display: inline-flex; align-items: center; gap: .4rem; padding: .42rem .55rem .42rem .65rem; border-radius: .75rem; border: 1px solid #e2e8f0; background: #fff; font-size: .76rem; font-weight: 700; color: #94a3b8; text-decoration: line-through; transition: all .15s; }
        .mn-item em { font-style: normal; display: grid; place-items: center; width: 1.4rem; height: 1.4rem; border-radius: .5rem; background: #f1f5f9; font-size: .78rem; }
        .mn-item.is-on { color: #1e293b; text-decoration: none; border-color: var(--color-indigo-200); }
        .mn-item.is-on em { background: var(--color-indigo-50); color: var(--color-indigo-600); }
        .dark .mn-item { background: #111827; border-color: #1f2937; color: #64748b; }
        .dark .mn-item.is-on { color: #e2e8f0; border-color: rgba(99, 102, 241, .35); }
        .dark .mn-item em { background: #1f2937; }
        .dark .mn-item.is-on em { background: rgba(99, 102, 241, .18); color: #a5b4fc; }
        .mn-preview { display: flex; flex-direction: column; gap: .3rem; padding: .55rem; border-radius: 1rem; background: #f1f5f9; }
        .dark .mn-preview { background: #020617; }
        .mn-pv-row { display: flex; align-items: center; gap: .5rem; padding: .4rem .5rem; border-radius: .65rem; background: #fff; font-size: .78rem; font-weight: 700; color: #334155; }
        .mn-pv-row.is-fixed { color: #64748b; background: transparent; border: 1px dashed #cbd5e1; }
        .dark .mn-pv-row { background: #0f172a; color: #e2e8f0; }
        .dark .mn-pv-row.is-fixed { background: transparent; border-color: #334155; color: #94a3b8; }
        .mn-pv-dot { display: grid; place-items: center; width: 1.45rem; height: 1.45rem; border-radius: .45rem; color: #fff; font-size: .72rem; }
        .mn-note { margin-top: .7rem; display: flex; gap: .4rem; font-size: .72rem; color: #64748b; line-height: 1.35; }
        .ap-btn-ghost:disabled { opacity: .5; cursor: default; }
        @media (max-width: 1535px) { .mn-list { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1279px) { .mn-grid { grid-template-columns: minmax(0, 1fr) 280px; } }
        @media (max-width: 1023px) { .mn-grid { grid-template-columns: minmax(0, 1fr); } .mn-side { position: static; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } .mn-list { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .mn-hero { flex-wrap: wrap; padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .mn-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.35rem; }
            .mn-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .mn-hero-txt h2 { font-size: 1.12rem; }
            .mn-list { grid-template-columns: minmax(0, 1fr); }
            .mn-side { display: flex; }
        }
    </style>
    </x-settings.layout>
</section>
