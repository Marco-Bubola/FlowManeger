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
    // Máscara de moeda para o campo {{ $id }} - Versão melhorada
    function formatCurrency_{{ $id }}(value) {
        // Remove tudo que não é dígito
        const digits = value.replace(/\D/g, '');

        // Se não há dígitos, retorna 0,00
        if (!digits) return '0,00';

        // Converte para centavos
        const centavos = parseInt(digits);

        // Formata para reais
        const reais = (centavos / 100).toFixed(2).replace('.', ',');

        return reais;
    }

    function applyCurrencyMask_{{ $id }}(input) {
        const formatted = formatCurrency_{{ $id }}(input.value);
        input.value = formatted;

        // Efeito visual de feedback
        input.classList.add('ring-4', 'ring-{{ $iconColor }}-200', 'dark:ring-{{ $iconColor }}-800');
        setTimeout(() => {
            input.classList.remove('ring-4', 'ring-{{ $iconColor }}-200', 'dark:ring-{{ $iconColor }}-800');
        }, 200);

        // Atualiza o input hidden com o valor numérico (formato US para validação)
        const numericValue = formatted.replace(',', '.');
        const hidden = document.getElementById('{{ $id }}_hidden');
        if (hidden) {
            hidden.value = numericValue;
            // Dispara event para Livewire reconhecer alteração
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    // Configuração melhorada do campo {{ $id }}
    document.addEventListener('DOMContentLoaded', function() {
        const input_{{ $id }} = document.getElementById('{{ $id }}');

            if (input_{{ $id }}) {
            // Inicializa hidden e visível com valores atuais do Livewire, quando existirem
            const hiddenInit = document.getElementById('{{ $id }}_hidden');
            // Se foi passado um valor inicial via prop blade, usa-o para init
            @if(!is_null($initial))
                try {
                    const initialVal = String(@json($initial));
                    if (hiddenInit) {
                        hiddenInit.value = initialVal;
                    }
                    const digitsFromInitial = initialVal.replace(/\D/g, '');
                    if (digitsFromInitial) {
                        input_{{ $id }}.value = formatCurrency_{{ $id }}(digitsFromInitial);
                    }
                } catch(e) {}
            @else
                if (hiddenInit) {
                    // Se o hidden já tem um valor (ex: edição via Livewire), sincroniza para o campo visível
                    if (hiddenInit.value && hiddenInit.value !== '0') {
                        const digitsFromHidden = hiddenInit.value.replace(/\D/g, '');
                        if (digitsFromHidden) {
                            input_{{ $id }}.value = formatCurrency_{{ $id }}(digitsFromHidden);
                        }
                    } else {
                        // Caso contrário, inicializa hidden a partir do campo visível padrão
                        if (!input_{{ $id }}.value || input_{{ $id }}.value === '0') {
                            input_{{ $id }}.value = '0,00';
                        }
                        hiddenInit.value = input_{{ $id }}.value.replace(',', '.');
                    }
                } else {
                    // Sem hidden: garante uma exibição padrão
                    if (!input_{{ $id }}.value || input_{{ $id }}.value === '0') {
                        input_{{ $id }}.value = '0,00';
                    }
                }
            @endif

            // Eventos de input com feedback visual
            input_{{ $id }}.addEventListener('input', function() {
                applyCurrencyMask_{{ $id }}(this);
            });

            input_{{ $id }}.addEventListener('focus', function() {
                if (this.value === '0,00') {
                    this.value = '';
                }
                // Efeito de glow no focus
                this.parentElement.classList.add('ring-4', 'ring-{{ $iconColor }}-200', 'dark:ring-{{ $iconColor }}-800');
            });

            input_{{ $id }}.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = '0,00';
                    applyCurrencyMask_{{ $id }}(this);
                }
                // Remove efeito de glow
                this.parentElement.classList.remove('ring-4', 'ring-{{ $iconColor }}-200', 'dark:ring-{{ $iconColor }}-800');
            });

            // Restringir entrada apenas a números com feedback
            input_{{ $id }}.addEventListener('keydown', function(e) {
                // Permite: backspace, delete, tab, escape, enter e .
                if ([46, 8, 9, 27, 13, 110, 190].indexOf(e.keyCode) !== -1 ||
                    // Permite: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true) ||
                    // Permite: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                // Garante que é um número e previne outros caracteres
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                    // Feedback visual para entrada inválida
                    this.classList.add('animate-shake');
                    setTimeout(() => {
                        this.classList.remove('animate-shake');
                    }, 300);
                }
            });
        }
    });

    // Sincroniza o campo visível a partir do hidden (valor do Livewire)
    function syncFromHidden_{{ $id }}() {
        const inputEl = document.getElementById('{{ $id }}');
        const hidden = document.getElementById('{{ $id }}_hidden');
        if (!inputEl || !hidden) return;
        const hv = String(hidden.value || '').trim();
        if (!hv) return;
        // Aceita formatos como '123.45', '123,45' ou apenas dígitos
        const digits = hv.replace(/\D/g, '');
        if (!digits) return;
        inputEl.value = formatCurrency_{{ $id }}(digits);
    }

    // Escuta vários eventos do Livewire para garantir sincronização após hidratação/atualizações
    ['livewire:load', 'livewire:update', 'livewire:message.processed', 'livewire:navigated'].forEach(evt => {
        document.addEventListener(evt, function() {
            try { syncFromHidden_{{ $id }}(); } catch (e) { /* ignore */ }
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
