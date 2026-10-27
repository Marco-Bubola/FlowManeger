<?php

use App\Services\Products\ProductPreferences;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $cost_change = 'variation';
    public string $pdf_upload = 'auto';

    public function mount(): void
    {
        $prefs = ProductPreferences::get(Auth::user());
        $this->cost_change = $prefs[ProductPreferences::COST_CHANGE];
        $this->pdf_upload = $prefs[ProductPreferences::PDF_UPLOAD];
    }

    // Salva sozinho a cada escolha
    public function updatedCostChange(): void { $this->save(); }
    public function updatedPdfUpload(): void { $this->save(); }

    public function save(): void
    {
        $this->validate([
            'cost_change' => 'required|in:variation,update',
            'pdf_upload' => 'required|in:auto,show,hide',
        ]);
        ProductPreferences::save(Auth::user(), [
            ProductPreferences::COST_CHANGE => $this->cost_change,
            ProductPreferences::PDF_UPLOAD => $this->pdf_upload,
        ]);
        $this->dispatch('products-prefs-saved');
    }
}; ?>

@php
    // Resumo do catálogo para o topo
    $uid = auth()->id();
    $pc = ['produtos' => 0, 'variacoes' => 0, 'kits' => 0, 'pdfs' => 0];
    try {
        $base = \App\Models\Product::withoutGlobalScope('team_visibility')->where('user_id', $uid);
        $pc['produtos'] = (clone $base)->count();
        $pc['kits'] = (clone $base)->where('tipo', 'kit')->count();
        $pc['variacoes'] = (clone $base)->whereNotNull('parent_id')->count();
        $pc['pdfs'] = \App\Models\ProductUploadHistory::where('user_id', $uid)->count();
    } catch (\Throwable $e) {
    }
    $pdfShown = \App\Services\Products\ProductPreferences::showPdfUpload(auth()->user());
@endphp

<section class="settings-products-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="sp-page" x-data="{ saved: false }" @products-prefs-saved.window="saved = true; clearTimeout($el._t); $el._t = setTimeout(() => saved = false, 1800)">

        <div class="sp-hero bg-gradient-to-br from-lime-500 via-green-600 to-emerald-700">
            <span class="sp-glow"></span>
            <div class="sp-hero-ico"><i class="bi bi-box-seam"></i></div>
            <div class="sp-hero-txt">
                <span class="sp-kicker"><i class="bi bi-sliders"></i> Como seus produtos funcionam</span>
                <h2>{{ number_format($pc['produtos'], 0, ',', '.') }} {{ $pc['produtos'] === 1 ? 'produto' : 'produtos' }} no catálogo</h2>
                <div class="sp-chips">
                    <span><i class="bi bi-diagram-3"></i> {{ $pc['variacoes'] }} {{ $pc['variacoes'] === 1 ? 'variação' : 'variações' }}</span>
                    <span><i class="bi bi-boxes"></i> {{ $pc['kits'] }} {{ $pc['kits'] === 1 ? 'kit' : 'kits' }}</span>
                    <span><i class="bi bi-file-earmark-pdf"></i> {{ $pc['pdfs'] }} {{ $pc['pdfs'] === 1 ? 'PDF importado' : 'PDFs importados' }}</span>
                </div>
            </div>
            <span class="sp-saved" x-show="saved" x-transition.opacity x-cloak><i class="bi bi-cloud-check"></i> Salvo</span>
        </div>

        <div class="sp-grid">
            <div class="sp-main">
                {{-- Custo mudou --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-orange-500 to-amber-500"><i class="bi bi-tag"></i></span>
                        <div class="min-w-0">
                            <h2>Quando o custo de um produto mudar</h2>
                            <p>Vale para Novo produto e para a importação, quando chega o mesmo código e nome com outro custo.</p>
                        </div>
                    </div>
                    <div class="sp-choices">
                        <label class="sp-choice {{ $cost_change === 'variation' ? 'is-on' : '' }}">
                            <input type="radio" wire:model.live="cost_change" value="variation" data-testid="cost-variation">
                            <span class="sp-art">
                                <span class="sp-mini"><em>R$ 40</em><i>12 un.</i></span>
                                <i class="bi bi-plus-lg sp-art-op"></i>
                                <span class="sp-mini is-new"><em>R$ 45</em><i>6 un.</i></span>
                            </span>
                            <b>Criar uma variação nova</b>
                            <small>O produto com o custo antigo fica como está e o novo entra como variação "R$ xx". Bom para revenda com lotes de preços diferentes (ex.: Boticário).</small>
                            <span class="sp-check"><i class="bi bi-check-lg"></i></span>
                        </label>
                        <label class="sp-choice {{ $cost_change === 'update' ? 'is-on' : '' }}">
                            <input type="radio" wire:model.live="cost_change" value="update" data-testid="cost-update">
                            <span class="sp-art">
                                <span class="sp-mini is-old"><em><s>R$ 40</s></em><i>12 un.</i></span>
                                <i class="bi bi-arrow-right sp-art-op"></i>
                                <span class="sp-mini is-new"><em>R$ 45</em><i>18 un.</i></span>
                            </span>
                            <b>Atualizar o custo do mesmo produto</b>
                            <small>Soma o estoque e troca o custo e a venda pelos novos. Bom para peças que você fabrica, onde o preço do material muda (ex.: móveis).</small>
                            <span class="sp-check"><i class="bi bi-check-lg"></i></span>
                        </label>
                    </div>
                </div>

                {{-- PDF --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-indigo-500 to-violet-600"><i class="bi bi-file-earmark-pdf"></i></span>
                        <div class="min-w-0">
                            <h2>Importar pedido em PDF</h2>
                            <p>O atalho do PDF do fornecedor (Boticário, Eudora...) nos menus de Produtos. Hoje ele está <b>{{ $pdfShown ? 'aparecendo' : 'escondido' }}</b>.</p>
                        </div>
                    </div>
                    <div class="sp-seg">
                        @foreach([['auto', 'Automático', 'bi-magic', 'Aparece se você já importou algum PDF ou ainda não tem produtos'], ['show', 'Mostrar sempre', 'bi-eye', 'O atalho fica sempre nos menus'], ['hide', 'Esconder', 'bi-eye-slash', 'No lugar dele aparece Importar planilha']] as [$val, $label, $icon, $hint])
                            <label class="sp-seg-opt {{ $pdf_upload === $val ? 'is-on' : '' }}">
                                <input type="radio" wire:model.live="pdf_upload" value="{{ $val }}">
                                <i class="bi {{ $icon }}"></i>
                                <b>{{ $label }}</b>
                                <small>{{ $hint }}</small>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="sp-side">
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-lightning-charge"></i></span>
                        <div class="min-w-0">
                            <h2>Atalhos</h2>
                            <p>Cadastrar e arrumar produtos.</p>
                        </div>
                    </div>
                    <div class="sp-links">
                        @foreach([['products.create', 'Novo produto', 'bi-plus-circle', 'from-indigo-500 to-purple-600'], ['products.import-sheet', 'Importar planilha', 'bi-file-earmark-spreadsheet', 'from-green-500 to-emerald-600'], ['products.import-ficha', 'Importar ficha', 'bi-clipboard-data', 'from-sky-500 to-blue-600'], ['products.variation-stock', 'Estoque das variações', 'bi-diagram-3', 'from-violet-500 to-fuchsia-600']] as [$r, $label, $icon, $grad])
                            @if(\Illuminate\Support\Facades\Route::has($r))
                                <a href="{{ route($r) }}" wire:navigate class="sp-link"><span class="bg-gradient-to-br {{ $grad }}"><i class="bi {{ $icon }}"></i></span>{{ $label }}<i class="bi bi-chevron-right"></i></a>
                            @endif
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('settings.appearance') }}" wire:navigate class="sp-promo">
                    <span class="sp-promo-cards"><span></span><span></span></span>
                    <span class="min-w-0">
                        <b>Cores dos cards de produto</b>
                        <small>Escolha em Aparência: acompanhar o tema, branco, grafite ou suas cores.</small>
                    </span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </aside>
        </div>
    </div>

    <style>
        .sp-page { display: flex; flex-direction: column; gap: 1rem; }
        .sp-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; box-shadow: 0 24px 50px -28px rgba(15, 23, 42, .6); }
        .sp-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .sp-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.7rem; }
        .sp-hero-txt { position: relative; flex: 1; min-width: 0; }
        .sp-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .sp-hero-txt h2 { font-size: 1.45rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .sp-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .45rem; }
        .sp-chips span { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .6rem; border-radius: 999px; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .25); font-size: .72rem; font-weight: 700; }
        .sp-saved { position: absolute; top: .9rem; right: 1rem; display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 800; background: rgba(255, 255, 255, .95); color: #059669; border-radius: 999px; padding: .25rem .6rem; }
        .sp-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1rem; align-items: start; }
        .sp-main, .sp-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .sp-side { position: sticky; top: 1rem; }
        .sp-choices { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .7rem; }
        .sp-choice { position: relative; display: flex; flex-direction: column; gap: .3rem; padding: .8rem; border-radius: 1.1rem; border: 2px solid #e2e8f0; background: #f8fafc; cursor: pointer; transition: border-color .15s, background .15s, transform .15s; }
        .sp-choice:hover { transform: translateY(-2px); }
        .sp-choice.is-on { border-color: var(--color-indigo-600); background: #fff; box-shadow: 0 0 0 4px color-mix(in oklab, var(--color-indigo-500) 16%, transparent); }
        .dark .sp-choice { background: #111827; border-color: #1f2937; }
        .dark .sp-choice.is-on { background: #1e293b; border-color: var(--color-indigo-600); }
        .sp-choice input, .sp-seg-opt input { position: absolute; opacity: 0; pointer-events: none; }
        .sp-art { display: flex; align-items: center; justify-content: center; gap: .6rem; padding: .8rem; margin-bottom: .3rem; border-radius: .8rem; background: linear-gradient(135deg, var(--color-indigo-50), #fff); border: 1px dashed #e2e8f0; }
        .dark .sp-art { background: #0b1220; border-color: #1e293b; }
        .sp-mini { display: flex; flex-direction: column; align-items: center; gap: .1rem; width: 4.6rem; padding: .45rem .3rem; border-radius: .7rem; background: var(--card-bg, #e6e6fa); border: 2px solid var(--card-border, #b39ddb); }
        .sp-mini em { font-style: normal; font-size: .78rem; font-weight: 900; color: var(--card-title, #1e1b4b); }
        .sp-mini i { font-style: normal; font-size: .62rem; font-weight: 700; color: #64748b; }
        .sp-mini.is-new { box-shadow: 0 0 0 3px color-mix(in oklab, #10b981 30%, transparent); }
        .sp-mini.is-old { opacity: .55; }
        .sp-art-op { color: #94a3b8; font-size: 1.1rem; }
        .sp-choice b { font-size: .9rem; color: #0f172a; }
        .sp-choice small { font-size: .74rem; color: #64748b; line-height: 1.4; }
        .dark .sp-choice b { color: #f1f5f9; }
        .dark .sp-choice small { color: #94a3b8; }
        .sp-check { position: absolute; top: .7rem; right: .7rem; display: none; place-items: center; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: var(--color-indigo-600); color: #fff; font-size: .85rem; }
        .sp-choice.is-on .sp-check { display: grid; }
        .sp-seg { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .55rem; }
        .sp-seg-opt { position: relative; display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border-radius: 1rem; border: 2px solid #e2e8f0; background: #f8fafc; cursor: pointer; transition: border-color .15s; }
        .sp-seg-opt > i { font-size: 1.15rem; color: #94a3b8; }
        .sp-seg-opt b { font-size: .84rem; color: #0f172a; }
        .sp-seg-opt small { font-size: .7rem; color: #64748b; line-height: 1.35; }
        .sp-seg-opt.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .sp-seg-opt.is-on > i { color: var(--color-indigo-600); }
        .dark .sp-seg-opt { background: #111827; border-color: #1f2937; }
        .dark .sp-seg-opt.is-on { background: #1e293b; border-color: var(--color-indigo-600); }
        .dark .sp-seg-opt b { color: #f1f5f9; }
        .sp-links { display: flex; flex-direction: column; gap: .45rem; }
        .sp-link { display: flex; align-items: center; gap: .6rem; padding: .55rem .7rem; border-radius: .9rem; background: #f8fafc; border: 1px solid #eef2f7; font-size: .84rem; font-weight: 700; color: #334155; transition: background .15s; }
        .sp-link:hover { background: #fff; border-color: var(--color-indigo-200); }
        .sp-link > span { flex: none; display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: .65rem; color: #fff; font-size: .9rem; }
        .sp-link .bi-chevron-right { margin-left: auto; color: #cbd5e1; }
        .dark .sp-link { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .sp-promo { display: flex; align-items: center; align-self: start; gap: .8rem; padding: .9rem; border-radius: 1.25rem; background: linear-gradient(135deg, var(--color-indigo-50), var(--color-pink-50, #fdf2f8)); border: 1px solid var(--color-indigo-100); color: #0f172a; }
        .dark .sp-promo { background: rgba(99, 102, 241, .1); border-color: rgba(99, 102, 241, .3); color: #f1f5f9; }
        .sp-promo-cards { position: relative; flex: none; width: 3.3rem; height: 3rem; }
        .sp-promo-cards span { position: absolute; width: 2.1rem; height: 2.7rem; border-radius: .5rem; background: var(--card-bg, #e6e6fa); border: 2px solid var(--card-border, #b39ddb); }
        .sp-promo-cards span:first-child { left: 0; top: .3rem; transform: rotate(-8deg); }
        .sp-promo-cards span:last-child { right: 0; top: 0; transform: rotate(6deg); }
        .sp-promo b { display: block; font-size: .85rem; }
        .sp-promo small { display: block; font-size: .72rem; color: #64748b; line-height: 1.35; }
        .sp-promo > .bi-chevron-right { margin-left: auto; color: #94a3b8; }
        @media (max-width: 1279px) { .sp-grid { grid-template-columns: minmax(0, 1fr) 280px; } .sp-seg { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1023px) { .sp-grid { grid-template-columns: minmax(0, 1fr); } .sp-side { position: static; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } .sp-seg { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .sp-hero { flex-wrap: wrap; padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .sp-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.35rem; }
            .sp-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .sp-hero-txt h2 { font-size: 1.12rem; }
            .sp-choices, .sp-seg { grid-template-columns: minmax(0, 1fr); }
            .sp-side { display: flex; }
        }
    </style>
    </x-settings.layout>
</section>
