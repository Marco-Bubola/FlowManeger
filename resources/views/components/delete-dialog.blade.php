@props([
    'show' => null,              // expressão Alpine (ex.: "showDeleteModal"); sem ela a janela aparece sempre (use dentro de @if)
    'confirm' => 'delete',       // ação Livewire do botão Excluir
    'cancel' => 'cancelDelete',  // ação Livewire de Cancelar / fechar
    'title' => 'Excluir?',
    'confirmLabel' => 'Excluir',
    'cancelLabel' => 'Cancelar',
    'icon' => 'bi-trash3',
])

@php
    $escJs = '$wire.' . $cancel . (str_contains($cancel, '(') ? '' : '()');
@endphp

{{-- Janela única de confirmar exclusão, usada em todo o app. --}}
<div @if($show) x-cloak x-show="{{ $show }}" x-transition.opacity @endif
     class="fm-dialog-backdrop" role="dialog" aria-modal="true"
     wire:click.self="{{ $cancel }}"
     x-on:keydown.escape.window="{{ $show ? "if ($show) " : '' }}{{ $escJs }}">
    <div class="fm-dialog" @if($show) x-show="{{ $show }}" x-transition.scale.95 @endif>
        <button type="button" class="fm-dialog-x" wire:click="{{ $cancel }}" title="Fechar"><i class="bi bi-x-lg"></i></button>
        <span class="fm-dialog-icon"><i class="bi {{ $icon }}"></i></span>
        <h3 class="fm-dialog-title">{{ $title }}</h3>
        <div class="fm-dialog-text">{{ $slot }}</div>
        <div class="fm-dialog-actions">
            <button type="button" class="fm-dialog-btn" wire:click="{{ $cancel }}">{{ $cancelLabel }}</button>
            <button type="button" class="fm-dialog-btn is-danger" wire:click="{{ $confirm }}" wire:loading.attr="disabled" wire:target="{{ \Illuminate\Support\Str::before($confirm, '(') }}">
                <span wire:loading.remove wire:target="{{ \Illuminate\Support\Str::before($confirm, '(') }}"><i class="bi bi-trash3"></i> {{ $confirmLabel }}</span>
                <span wire:loading wire:target="{{ \Illuminate\Support\Str::before($confirm, '(') }}"><i class="bi bi-arrow-repeat animate-spin"></i> Excluindo...</span>
            </button>
        </div>
    </div>
</div>

@once
<style>
    .fm-dialog-backdrop { position: fixed; inset: 0; z-index: 70; display: flex; align-items: center; justify-content: center; padding: 16px;
        background: rgb(15 23 42 / .45); backdrop-filter: blur(4px); }
    .fm-dialog { position: relative; width: 100%; max-width: 400px; padding: 26px 22px 20px; border-radius: 22px; text-align: center;
        background: #fff; border: 1px solid rgb(226 232 240); box-shadow: 0 30px 60px -20px rgb(15 23 42 / .45); }
    .dark .fm-dialog { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .fm-dialog-x { position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        color: rgb(100 116 139); background: rgb(241 245 249); }
    .fm-dialog-x:hover { background: rgb(226 232 240); }
    .dark .fm-dialog-x { background: rgb(30 41 59); color: rgb(148 163 184); }
    .fm-dialog-icon { width: 60px; height: 60px; margin: 0 auto 12px; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        font-size: 26px; color: rgb(225 29 72); background: rgb(255 228 230); box-shadow: 0 0 0 8px rgb(255 241 242); }
    .dark .fm-dialog-icon { color: rgb(253 164 175); background: rgb(136 19 55 / .45); box-shadow: 0 0 0 8px rgb(136 19 55 / .2); }
    .fm-dialog-title { font-size: 18px; font-weight: 800; color: rgb(15 23 42); }
    .dark .fm-dialog-title { color: #fff; }
    .fm-dialog-text { margin-top: 6px; font-size: 14px; line-height: 1.45; color: rgb(71 85 105); }
    .fm-dialog-text strong { color: rgb(15 23 42); }
    .dark .fm-dialog-text { color: rgb(148 163 184); }
    .dark .fm-dialog-text strong { color: rgb(241 245 249); }
    .fm-dialog-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
    .fm-dialog-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 11px 14px; border-radius: 14px; font-size: 14px; font-weight: 700;
        color: rgb(51 65 85); background: rgb(241 245 249); border: 1px solid rgb(226 232 240); transition: filter .15s, background .15s; }
    .fm-dialog-btn:hover { background: rgb(226 232 240); }
    .dark .fm-dialog-btn { color: rgb(226 232 240); background: rgb(30 41 59); border-color: rgb(51 65 85); }
    .fm-dialog-btn.is-danger { color: #fff; border-color: transparent; background: linear-gradient(90deg, rgb(225 29 72), rgb(220 38 38)); box-shadow: 0 10px 20px -12px rgb(225 29 72 / .8); }
    .fm-dialog-btn.is-danger:hover { filter: brightness(1.06); }
    .fm-dialog-btn:disabled { opacity: .6; }
</style>
@endonce
