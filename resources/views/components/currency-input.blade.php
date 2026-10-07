@props([
    'name' => 'price',
    'id' => 'price',
    'wireModel' => 'price',
    'label' => 'Valor',
    'placeholder' => '0,00',
    'icon' => 'bi-currency-dollar',
    'iconColor' => 'green',
    'currency' => 'R$',
    'required' => false,
    'disabled' => false,
    'maxlength' => 12,
    'value' => null
])

@php
    $iconColorClasses = [
        'orange' => 'from-orange-400 to-orange-600 text-white',
        'green' => 'from-emerald-400 to-green-600 text-white',
        'blue' => 'from-blue-400 to-blue-600 text-white',
        'purple' => 'from-purple-400 to-purple-600 text-white',
    ];

    $iconColorClass = $iconColorClasses[$iconColor] ?? $iconColorClasses['green'];
    $focusRingColor = "focus:ring-{$iconColor}-500/30";
    $focusBorderColor = "focus:border-{$iconColor}-500";
    $hoverBorderColor = "hover:border-{$iconColor}-300";
    $borderErrorColor = $errors->has($wireModel) ? 'border-red-400 focus:border-red-500 focus:ring-red-500/30' : 'border-slate-200 dark:border-slate-600 ' . $focusBorderColor . ' ' . $hoverBorderColor;
@endphp

@php
    // Aceita o valor do PHP em formato numérico (1234.56) ou brasileiro (1.234,56)
    $rawValue = (string) ($value ?? '');
    if (str_contains($rawValue, ',')) {
        $rawValue = str_replace(['.', ','], ['', '.'], $rawValue);
    }
    $initialCents = $rawValue === '' ? 0 : (int) round(((float) $rawValue) * 100);
@endphp

{{--
  Máscara de centavos em Alpine (mesma regra do x-money-input):
  cada dígito entra pela direita (1 → 0,01 · 12 → 0,12 · 123 → 1,23) e focar o
  campo seleciona tudo. Vive no próprio elemento, então continua funcionando
  quando a página é aberta pelo menu (wire:navigate) ou o Livewire re-renderiza.
--}}
<div class="group space-y-2"
     x-data="{
         cts: {{ $initialCents }},
         fmt() {
             if (!this.cts) return '';
             let s = String(this.cts).padStart(3, '0');
             let d = s.slice(-2);
             let i = s.slice(0, -2).replace(/^0+/, '') || '0';
             i = i.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
             return i + ',' + d;
         },
         push() {
             $wire.set('{{ $wireModel }}', this.cts ? (this.cts / 100).toFixed(2) : '', false);
         },
         inp(e) {
             let digs = e.target.value.replace(/\D/g, '');
             this.cts = digs ? parseInt(digs) : 0;
             e.target.value = this.fmt();
             this.push();
         }
     }">
    <label for="{{ $id }}" class="flex items-center text-base font-semibold text-slate-800 dark:text-slate-200 group-hover:text-{{ $iconColor }}-600 dark:group-hover:text-{{ $iconColor }}-400 transition-colors duration-200">
        <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br {{ $iconColorClass }} rounded-lg mr-3 shadow-sm transition-transform duration-150">
            <i class="{{ $icon }}"></i>
        </div>
        {{ $label }}
        @if($required)
            <span class="text-red-500 ml-2 animate-pulse">*</span>
        @endif
    </label>

    <div class="relative">
        <!-- Ícone da moeda com animações -->
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="text-base font-bold bg-gradient-to-r from-{{ $iconColor }}-600 to-{{ $iconColor }}-500 bg-clip-text text-transparent transition-transform duration-150">{{ $currency }}</span>
        </div>



     <!-- Campo de entrada modernizado (visível apenas com máscara) -->
     <input type="text"
         wire:ignore
         inputmode="numeric"
         id="{{ $id }}"
         name="{{ $name }}_masked"
         x-init="$el.value = fmt()"
         x-on:focus="$el.select()"
         x-on:input="inp($event)"
         x-on:blur="push()"
         maxlength="{{ $maxlength }}"
         @if($disabled) disabled @endif
         class="w-full pl-12 pr-3 py-2.5 border-2 rounded-xl
             bg-white/60 dark:bg-slate-700/60 backdrop-blur-sm
             text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500
             {{ $borderErrorColor }}
             focus:ring-2 {{ $focusRingColor }} focus:outline-none
             transition-all duration-200 shadow-sm hover:shadow-sm
             {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
         placeholder="{{ $placeholder }}">

        <!-- Indicador de validação (pequeno) -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            @if(!$errors->has($wireModel) && $wireModel)
                <div class="flex items-center justify-center w-5 h-5 bg-gradient-to-r from-emerald-400 to-green-500 rounded-full animate-pulse">
                    <i class="bi bi-check text-white text-[10px] font-bold"></i>
                </div>
            @endif
        </div>

        <!-- Efeito de brilho no hover -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-{{ $iconColor }}-500/10 via-transparent to-{{ $iconColor }}-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
    </div>

    @error($wireModel)
    <div class="flex items-center mt-2 p-2 bg-red-50/80 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-800 backdrop-blur-sm animate-slideIn">
        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-2 animate-bounce"></i>
        <p class="text-red-600 dark:text-red-400 text-sm font-medium">{{ $message }}</p>
    </div>
    @enderror
</div>

<style>
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-shake {
        animation: shake 0.3s ease-in-out;
    }

    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }
</style>
