@props([
    /** Nome da propriedade Livewire, ex.: "meta_valor" ou "itens.0.preco" */
    'model',
    /** Valor inicial (float) */
    'value' => 0,
    'id' => null,
    'label' => null,
    'placeholder' => '0,00',
    'prefix' => 'R$',
    'required' => false,
    'disabled' => false,
    /**
     * `bare` renderiza SÓ o <input>, sem wrapper, label, prefixo ou erro.
     * Serve para encaixar a máscara em telas que já têm marcação própria
     * (prefixo "R$", classes de layout), padronizando o comportamento sem
     * mexer no visual daquela tela.
     */
    'bare' => false,
    /** Dispara no input (live) ou só ao sair do campo (blur) */
    'live' => true,
])

{{--
  Campo de valor padrão do app — máscara de centavos.

  Cada dígito entra pela direita:  1 → 0,01 · 12 → 0,12 · 123 → 1,23
  Focar o campo seleciona tudo, então digitar por cima substitui o valor
  em vez de concatenar (era o que fazia "37,90" virar "379,01").

  A máscara vive em Alpine, dentro do próprio elemento. Não depende de
  funções globais declaradas em <script> da view: esses scripts não voltam
  a rodar depois de um morph do Livewire, o que quebrava o campo assim que
  a tela re-renderizava.

  Uso:
    <x-money-input model="meta_valor" :value="$meta_valor" label="Meta" />
    <x-money-input :model="'itens.'.$i.'.preco'" :value="$item['preco']" />
--}}

@php
    $inputId = $id ?? 'money-' . \Illuminate\Support\Str::slug($model, '-');
    $centavos = (int) round(((float) $value) * 100);
@endphp

<div @class(['fm-money-field' => ! $bare, 'contents' => $bare])
     x-data="{
         cts: {{ $centavos }},
         fmt() {
             let s = String(this.cts).padStart(3, '0');
             let d = s.slice(-2);
             let i = s.slice(0, -2).replace(/^0+/, '') || '0';
             i = i.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
             return i + ',' + d;
         },
         push() {
             $wire.set('{{ $model }}', (this.cts / 100).toFixed(2));
         },
         inp(e) {
             let digs = e.target.value.replace(/\D/g, '');
             this.cts = digs ? parseInt(digs) : 0;
             e.target.value = this.fmt();
             @if($live) this.push(); @endif
         },
         setValor(centavos) {
             this.cts = centavos;
             this.$refs.campo.value = this.fmt();
             this.push();
         }
     }">

    @if($label && ! $bare)
        <label for="{{ $inputId }}" class="fm-money-label">
            {{ $label }}@if($required)<span class="fm-money-req">*</span>@endif
        </label>
    @endif

    @if($bare)
        <input
            type="text"
            inputmode="numeric"
            id="{{ $inputId }}"
            x-ref="campo"
            x-init="$el.value = fmt()"
            x-on:focus="$el.select()"
            x-on:input="inp($event)"
            x-on:blur="push()"
            wire:ignore
            placeholder="{{ $placeholder }}"
            @if($disabled) disabled @endif
            {{ $attributes }}
        />
    @else
        <div class="fm-money-wrap">
            <span class="fm-money-prefix" aria-hidden="true">{{ $prefix }}</span>
            <input
                type="text"
                inputmode="numeric"
                id="{{ $inputId }}"
                x-ref="campo"
                x-init="$el.value = fmt()"
                x-on:focus="$el.select()"
                x-on:input="inp($event)"
                x-on:blur="push()"
                wire:ignore
                placeholder="{{ $placeholder }}"
                @if($disabled) disabled @endif
                {{ $attributes->merge(['class' => 'fm-money-input']) }}
            />
        </div>

        @error($model)
            <p class="fm-money-error">{{ $message }}</p>
        @enderror
    @endif
</div>
