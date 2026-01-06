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
    'maxlength' => 12
    ,'initial' => null
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

<div class="group space-y-2">
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

     <!-- Campo de entrada oculto (valor numérico para Livewire) -->
     <input type="hidden" wire:model="{{ $wireModel }}" id="{{ $id }}_hidden" name="{{ $name }}">

     <!-- Campo de entrada modernizado (visível apenas com máscara) -->
     <input type="text"
         wire:ignore.self
         id="{{ $id }}"
         name="{{ $name }}_masked"
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

<script>
    document.addEventListener('alpine:init', () => {
        const inputMasked = document.getElementById('{{ $id }}');
        const inputHidden = document.getElementById('{{ $id }}_hidden');
        // Pega o valor inicial passado, tratando como uma string para manipulação
        const initialValue = '{{ $attributes->get('value', '0') }}';

        // Converte um valor numérico (ex: 123.45) para uma string formatada (ex: '123,45')
        const formatNumber = (numStr) => {
            const num = parseFloat(numStr);
            if (isNaN(num)) return '0,00';
            return num.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        // Converte o valor de input do usuário para o formato de moeda
        const formatInput = (value) => {
            if (!value) return '';
            const digits = value.replace(/\D/g, '');
            if (digits === '') return '';

            const numberValue = parseInt(digits, 10) / 100;
            return formatNumber(numberValue.toFixed(2));
        };

        // Converte uma string formatada (ex: '1.234,56') para um formato numérico para o backend (ex: '1234.56')
        const unformat = (value) => {
            if (!value) return '0.00';
            return value.replace(/\./g, '').replace(',', '.');
        };


        // --- INICIALIZAÇÃO ---
        // 1. Define o valor inicial formatado no campo visível
        inputMasked.value = formatNumber(initialValue);
        // 2. Define o valor numérico correspondente no campo oculto
        inputHidden.value = unformat(inputMasked.value);
        // 3. Notifica o Livewire sobre o valor inicial para evitar que ele seja limpo
        inputHidden.dispatchEvent(new Event('input'));


        // --- ATUALIZAÇÃO NO INPUT DO USUÁRIO ---
        inputMasked.addEventListener('input', (e) => {
            const formattedValue = formatInput(e.target.value);
            e.target.value = formattedValue;
            inputHidden.value = unformat(formattedValue);
            inputHidden.dispatchEvent(new Event('input')); // Notifica o Livewire a cada mudança
        });
    });
</script>

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
