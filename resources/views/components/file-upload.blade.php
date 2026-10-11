@props([
    'name' => 'file',
    'id' => 'file',
    'wireModel' => 'file',
    'acceptedTypes' => '.pdf,.csv',
    'maxSize' => '10MB',
    'supportedFormats' => 'PDF, CSV',
    'title' => 'Arraste o arquivo aqui',
    'description' => 'ou clique para escolher no aparelho',
    'existingFile' => null,
    'newFile' => null,
    'width' => 'w-full',
    'height' => null,
])

{{-- Área de envio de arquivo usada nas importações (produtos, fatura e extrato).
     Aceita clique e arrastar-e-soltar; com arquivo escolhido mostra nome, tipo e tamanho. --}}
@php
    $formats = is_array($supportedFormats) ? $supportedFormats : explode(',', $supportedFormats);
    $fileName = $newFile ? $newFile->getClientOriginalName() : null;
    $extension = $fileName ? strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) : null;
    $isPdf = $extension === 'pdf';
@endphp

<div class="{{ $width }}"
     x-data="{ over: false }"
     x-on:dragover.prevent="over = true"
     x-on:dragleave.prevent="over = false"
     x-on:drop.prevent="over = false; if ($event.dataTransfer.files.length) { $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change', { bubbles: true })); }">
    <label for="{{ $id }}"
           class="fm-drop group {{ $newFile ? 'has-file' : '' }}"
           :class="{ 'is-over': over }">

        <div wire:loading.flex wire:target="{{ $wireModel }}" class="fm-dz-loading">
            <i class="bi bi-arrow-repeat animate-spin"></i> Enviando arquivo...
        </div>

        @if($newFile)
            <div class="fm-dz-file" wire:loading.remove wire:target="{{ $wireModel }}">
                <span class="fm-dz-file-icon {{ $isPdf ? 'is-pdf' : 'is-csv' }}">
                    <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-spreadsheet-fill' }}"></i>
                </span>
                <span class="fm-dz-file-info">
                    <span class="fm-dz-file-name" title="{{ $fileName }}">{{ $fileName }}</span>
                    <span class="fm-dz-file-meta">
                        <span><i class="bi bi-check-circle-fill"></i> Pronto para processar</span>
                        <span>{{ strtoupper($extension) }} · {{ number_format($newFile->getSize() / 1024, 0, ',', '.') }} KB</span>
                    </span>
                </span>
                <span class="fm-dz-change"><i class="bi bi-arrow-repeat"></i><span>Trocar</span></span>
            </div>
        @else
            <div class="fm-dz-empty" wire:loading.remove wire:target="{{ $wireModel }}">
                <span class="fm-dz-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                <span class="fm-dz-title">{{ $title }}</span>
                <span class="fm-dz-desc">{{ $description }}</span>
                <span class="fm-dz-chips">
                    @foreach($formats as $format)
                        <span class="fm-dz-chip">{{ trim($format) }}</span>
                    @endforeach
                    <span class="fm-dz-chip is-muted">até {{ $maxSize }}</span>
                </span>
            </div>
        @endif

        <input type="file"
               id="{{ $id }}"
               name="{{ $name }}"
               x-ref="input"
               wire:model.live="{{ $wireModel }}"
               accept="{{ $acceptedTypes }}"
               class="hidden" />
    </label>
</div>

@once
<style>
    .fm-drop { position: relative; display: flex; align-items: center; justify-content: center; width: 100%; min-height: 230px; padding: 24px 18px;
        border: 2px dashed rgb(203 213 225); border-radius: 20px; cursor: pointer; text-align: center;
        background: linear-gradient(160deg, rgb(255 255 255), rgb(238 242 255 / .7)); transition: border-color .2s, background .2s, box-shadow .2s; }
    .fm-drop:hover, .fm-drop.is-over { border-color: rgb(129 140 248); background: linear-gradient(160deg, rgb(255 255 255), rgb(224 231 255)); box-shadow: 0 12px 32px -18px rgb(79 70 229 / .45); }
    .fm-drop.is-over { border-style: solid; }
    .fm-drop.has-file { min-height: 0; padding: 14px; border-style: solid; border-color: rgb(167 243 208); background: rgb(236 253 245 / .7); }
    .dark .fm-drop { border-color: rgb(71 85 105); background: linear-gradient(160deg, rgb(30 41 59 / .8), rgb(49 46 129 / .25)); }
    .dark .fm-drop:hover, .dark .fm-drop.is-over { border-color: rgb(129 140 248); background: linear-gradient(160deg, rgb(30 41 59), rgb(49 46 129 / .45)); }
    .dark .fm-drop.has-file { border-color: rgb(6 95 70); background: rgb(6 78 59 / .25); }

    .fm-dz-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .fm-dz-icon { width: 64px; height: 64px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin-bottom: 6px;
        font-size: 30px; color: #fff; background: linear-gradient(135deg, rgb(99 102 241), rgb(168 85 247)); box-shadow: 0 12px 24px -12px rgb(99 102 241 / .7);
        transition: transform .2s; }
    .fm-drop:hover .fm-dz-icon, .fm-drop.is-over .fm-dz-icon { transform: translateY(-3px) scale(1.04); }
    .fm-dz-title { font-size: 16px; font-weight: 800; color: rgb(15 23 42); }
    .fm-dz-desc { font-size: 13px; color: rgb(100 116 139); }
    .dark .fm-dz-title { color: rgb(241 245 249); }
    .dark .fm-dz-desc { color: rgb(148 163 184); }
    .fm-dz-chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 8px; }
    .fm-dz-chip { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: #fff; color: rgb(67 56 202); border: 1px solid rgb(224 231 255); }
    .fm-dz-chip.is-muted { color: rgb(100 116 139); border-color: rgb(226 232 240); }
    .dark .fm-dz-chip { background: rgb(30 41 59); color: rgb(165 180 252); border-color: rgb(55 48 163 / .6); }
    .dark .fm-dz-chip.is-muted { color: rgb(148 163 184); border-color: rgb(51 65 85); }

    .fm-dz-file { display: flex; align-items: center; gap: 12px; width: 100%; text-align: left; }
    .fm-dz-file-icon { width: 48px; height: 48px; flex-shrink: 0; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; }
    .fm-dz-file-icon.is-pdf { background: linear-gradient(135deg, rgb(244 63 94), rgb(225 29 72)); }
    .fm-dz-file-icon.is-csv { background: linear-gradient(135deg, rgb(16 185 129), rgb(5 150 105)); }
    .fm-dz-file-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
    .fm-dz-file-name { font-size: 14px; font-weight: 700; color: rgb(15 23 42); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dark .fm-dz-file-name { color: rgb(241 245 249); }
    .fm-dz-file-meta { display: flex; flex-wrap: wrap; gap: 4px 10px; font-size: 12px; color: rgb(100 116 139); }
    .fm-dz-file-meta i { color: rgb(16 185 129); }
    .dark .fm-dz-file-meta { color: rgb(148 163 184); }
    .fm-dz-change { display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0; font-size: 12px; font-weight: 700; color: rgb(79 70 229);
        padding: 6px 10px; border-radius: 10px; background: #fff; border: 1px solid rgb(224 231 255); }
    .dark .fm-dz-change { background: rgb(30 41 59); border-color: rgb(51 65 85); color: rgb(165 180 252); }
    .fm-dz-loading { align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: rgb(79 70 229); }
    .dark .fm-dz-loading { color: rgb(165 180 252); }
    @media (max-width: 640px) {
        .fm-drop { min-height: 190px; padding: 20px 14px; }
        .fm-dz-icon { width: 54px; height: 54px; font-size: 26px; }
        .fm-dz-change span { display: none; }
    }
</style>
@endonce
