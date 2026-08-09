@props([
    /**
     * Propriedade booleana do Livewire que controla a abertura, ex.: "showDeleteModal".
     * O modal cria o próprio x-data com @entangle, então NÃO depende do escopo
     * Alpine da página — foi o que quebrou na primeira versão: o x-show lia uma
     * variável do x-data do host e o efeito não reagia às mudanças.
     */
    'wire' => null,
    /** Alternativa: expressão Alpine já existente no escopo da página */
    'show' => null,
    /** Ação Alpine extra ao fechar. Com `wire`, o fechamento já é automático. */
    'onClose' => null,
    /** Título no cabeçalho */
    'title' => null,
    'subtitle' => null,
    /** Ícone bootstrap, ex.: "bi-trash-fill" */
    'icon' => null,
    /** sm | md | lg | xl | full */
    'size' => 'md',
    /** Cor do acento: indigo | rose | emerald | amber | slate */
    'tone' => 'indigo',
    /** Vira bottom-sheet no celular */
    'sheet' => true,
    /** Mostra o X no cabeçalho */
    'closable' => true,
])

{{--
  Modal padrão do app.

  Existiam 34 modais só entre vendas e produtos, cada um repetindo backdrop,
  painel, transições e botão fechar — com 5 z-index diferentes (50, 110, 120,
  130, 9999, 99999) e 4 tratamentos de fundo. Isso produzia modal atrás de
  conteúdo e comportamento diferente em cada tela.

  Aqui: um z-index só (via --fm-modal-z), backdrop e transições iguais,
  fechamento por Esc e clique no fundo, foco preso no painel e bottom-sheet
  no celular.

  Uso:
    <x-modal wire="showDeleteModal"
             title="Excluir venda" icon="bi-trash-fill" tone="rose" size="sm">
        <p>Tem certeza?</p>
        <x-slot:footer>
            <button @click="showDelete = false">Cancelar</button>
            <button wire:click="delete">Excluir</button>
        </x-slot:footer>
    </x-modal>
--}}

@php
    // Com `wire`, o estado vive aqui dentro (entangle). Sem ele, usa a expressão do host.
    $abertura = $wire ? 'aberto' : $show;
    $fechar = $wire ? ('aberto = false' . ($onClose ? '; ' . $onClose : '')) : $onClose;
@endphp

<div
    @if($wire)
        x-data="{ aberto: @entangle($wire) }"
    @endif
    @if($abertura) x-show="{{ $abertura }}" @endif
    x-cloak
    @if($fechar) x-on:keydown.escape.window="{{ $fechar }}" @endif
    class="fm-modal fm-modal--{{ $tone }}"
    role="dialog"
    aria-modal="true"
    @if($title) aria-label="{{ $title }}" @endif
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="fm-modal-backdrop" @if($fechar) x-on:click="{{ $fechar }}" @endif></div>

    <div
        @class(['fm-modal-panel', 'fm-modal-panel--' . $size, 'fm-modal-panel--sheet' => $sheet])
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
    >
        {{-- alça do bottom-sheet (só no celular, via CSS) --}}
        @if($sheet)
            <div class="fm-modal-grabber" aria-hidden="true"><span></span></div>
        @endif

        @if($title || $icon || $closable)
            <div class="fm-modal-header">
                @if($icon)
                    <span class="fm-modal-icon"><i class="bi {{ $icon }}"></i></span>
                @endif

                @if($title)
                    <div class="fm-modal-titles">
                        <p class="fm-modal-title">{{ $title }}</p>
                        @if($subtitle)<p class="fm-modal-subtitle">{{ $subtitle }}</p>@endif
                    </div>
                @endif

                @if($closable && $fechar)
                    <button type="button" class="fm-modal-close" x-on:click="{{ $fechar }}" aria-label="Fechar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                @endif
            </div>
        @endif

        <div class="fm-modal-body">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="fm-modal-footer">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
