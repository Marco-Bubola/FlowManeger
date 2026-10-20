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

<section class="settings-products-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        <div class="s-pg-grid">
        <div class="s-col-main">
        <form wire:submit="save" style="display:flex;flex-direction:column;gap:1rem">

        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(234,88,12,.1);color:#ea580c"><i class="bi bi-tag" style="font-size:1rem"></i></div>
                    <div>
                        <p class="settings-card-title">Quando o custo de um produto mudar</p>
                        <p class="settings-card-desc">Vale para Novo produto e para a importação, quando chega o mesmo código e nome com outro custo</p>
                    </div>
                </div>
            </div>
            <div class="sp-opts">
                <label class="sp-opt {{ $cost_change === 'variation' ? 'on' : '' }}">
                    <input type="radio" wire:model.live="cost_change" value="variation" data-testid="cost-variation">
                    <span><b>Criar uma variação nova</b><small>O produto com o custo antigo fica como está e o novo entra como variação "R$ xx". Bom para revenda com lotes de preços diferentes (ex.: Boticário).</small></span>
                </label>
                <label class="sp-opt {{ $cost_change === 'update' ? 'on' : '' }}">
                    <input type="radio" wire:model.live="cost_change" value="update" data-testid="cost-update">
                    <span><b>Atualizar o custo do mesmo produto</b><small>Soma o estoque e troca o custo e a venda pelos novos. Bom para peças que você fabrica, onde o preço do material muda (ex.: móveis).</small></span>
                </label>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-file-earmark-pdf" style="font-size:1rem"></i></div>
                    <div>
                        <p class="settings-card-title">Importar pedido em PDF</p>
                        <p class="settings-card-desc">O atalho do PDF do fornecedor (Boticário, Eudora...) nos menus de Produtos</p>
                    </div>
                </div>
            </div>
            <div class="sp-opts">
                <label class="sp-opt {{ $pdf_upload === 'auto' ? 'on' : '' }}">
                    <input type="radio" wire:model.live="pdf_upload" value="auto">
                    <span><b>Automático</b><small>Aparece se a conta já importou algum PDF ou ainda não tem produtos.</small></span>
                </label>
                <label class="sp-opt {{ $pdf_upload === 'show' ? 'on' : '' }}">
                    <input type="radio" wire:model.live="pdf_upload" value="show">
                    <span><b>Mostrar sempre</b></span>
                </label>
                <label class="sp-opt {{ $pdf_upload === 'hide' ? 'on' : '' }}">
                    <input type="radio" wire:model.live="pdf_upload" value="hide">
                    <span><b>Esconder</b><small>No lugar dele aparece Importar planilha.</small></span>
                </label>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <button data-mobile-save type="submit" class="settings-save-btn"><i class="bi bi-check2"></i> Salvar</button>
            <x-action-message on="products-prefs-saved"><span style="display:inline-flex;align-items:center;gap:.35rem;color:#059669;font-size:.83rem;font-weight:600"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
        </div>
        </form>
        </div>

        <div class="s-col-side">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-lightning-charge" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Atalhos</p>
                            <p class="settings-card-desc">Cadastrar vários produtos de uma vez</p>
                        </div>
                    </div>
                </div>
                <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.6rem">
                    <a href="{{ route('products.import-sheet') }}" wire:navigate class="sp-link"><i class="bi bi-file-earmark-spreadsheet"></i> Importar planilha</a>
                    <a href="{{ route('products.create') }}" wire:navigate class="sp-link"><i class="bi bi-plus-circle"></i> Novo produto</a>
                </div>
            </div>
        </div>
        </div>
    </x-settings.layout>

    <style>
        .settings-products-page .sp-opts{padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.6rem}
        .settings-products-page .sp-opt{display:flex;gap:.7rem;align-items:flex-start;padding:.75rem .9rem;border-radius:.85rem;border:1.5px solid rgba(148,163,184,.35);cursor:pointer;transition:border-color .15s,background .15s}
        .settings-products-page .sp-opt.on{border-color:var(--s-accent);background:rgba(var(--s-accent-rgb),.06)}
        .settings-products-page .sp-opt input{margin-top:.2rem;accent-color:var(--s-accent);flex-shrink:0}
        .settings-products-page .sp-opt b{display:block;font-size:.88rem}
        .settings-products-page .sp-opt small{display:block;margin-top:.15rem;font-size:.76rem;line-height:1.4;color:#64748b}
        .dark .settings-products-page .sp-opt small{color:#94a3b8}
        .settings-products-page .sp-link{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border:1.5px solid rgba(var(--s-accent-rgb),.35);color:var(--s-accent);border-radius:.75rem;padding:.6rem 1rem;font-size:.85rem;font-weight:700}
    </style>
</section>
