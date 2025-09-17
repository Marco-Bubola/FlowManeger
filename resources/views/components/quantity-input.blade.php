@props([
    'name' => 'quantity',
    'id' => 'quantity',
    'wireModel' => 'quantity',
    'min' => 0,
    'max' => null,
    'value' => 0,
    'placeholder' => '0',
    'label' => 'Quantidade',
    'icon' => 'bi-stack',
    'iconColor' => 'cyan',
    'required' => false,
    'disabled' => false,
    'width' => 'max-w-[12rem]'
])

@php
    $iconColorClasses = [
        'cyan' => 'from-cyan-400 to-cyan-600 text-white',
        'blue' => 'from-blue-400 to-blue-600 text-white',
        'green' => 'from-emerald-400 to-green-600 text-white',
        'purple' => 'from-purple-400 to-purple-600 text-white',
        'red' => 'from-red-400 to-red-600 text-white',
    ];

    $iconColorClass = $iconColorClasses[$iconColor] ?? $iconColorClasses['cyan'];
    $focusRingColor = "focus:ring-{$iconColor}-500/30";
    $focusBorderColor = "focus:border-{$iconColor}-500";
    $hoverBorderColor = "hover:border-{$iconColor}-300";
    $borderErrorColor = $errors->has($wireModel) ? 'border-red-400' : 'border-slate-200 dark:border-slate-600';
@endphp

<div class="group space-y-4">
    <label for="{{ $id }}" class="flex items-center text-lg font-bold text-slate-800 dark:text-slate-200 group-hover:text-{{ $iconColor }}-600 dark:group-hover:text-{{ $iconColor }}-400 transition-colors duration-300">
        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br {{ $iconColorClass }} rounded-xl mr-4 shadow-lg group-hover:scale-110 transition-all duration-300 group-hover:shadow-xl group-hover:shadow-{{ $iconColor }}-500/30">
            <i class="{{ $icon }}"></i>
        </div>
        {{ $label }}
        @if($required)
            <span class="text-red-500 ml-2 animate-pulse">*</span>
        @endif
    </label>

    <div class="relative flex items-center {{ $width }}">
        <!-- Botão Decrementar Modernizado -->
        <button type="button"
                onclick="decrementQuantity_{{ $id }}()"
                @if($disabled) disabled @endif
                class="group/btn relative flex items-center justify-center h-14 w-14
                       bg-gradient-to-br from-slate-100 to-slate-200
                       dark:from-slate-700 dark:to-slate-600
                       hover:from-{{ $iconColor }}-100 hover:to-{{ $iconColor }}-200
                       dark:hover:from-{{ $iconColor }}-900/50 dark:hover:to-{{ $iconColor }}-800/50
                       border-2 {{ $borderErrorColor }} {{ $hoverBorderColor }}
                       rounded-2xl shadow-lg hover:shadow-xl
                       transition-all duration-300 transform hover:scale-105
                       {{ $focusRingColor }} focus:ring-4 focus:outline-none
                       {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">

            <!-- Ícone com animação -->
            <svg class="w-4 h-4 text-slate-600 dark:text-slate-300 group-hover/btn:text-{{ $iconColor }}-600 dark:group-hover/btn:text-{{ $iconColor }}-400 transition-colors duration-300 transform group-hover/btn:scale-110"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M1 1h16"/>
            </svg>

            <!-- Efeito de ripple -->
            <div class="absolute inset-0 rounded-2xl bg-{{ $iconColor }}-400/20 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
        </button>

        <!-- Input Modernizado -->
        <input type="number"
               wire:model="{{ $wireModel }}"
               id="{{ $id }}"
               name="{{ $name }}"
               min="{{ $min }}"
               @if($max) max="{{ $max }}" @endif
               @if($disabled) disabled @endif
               class="bg-white/80 dark:bg-slate-700/80 backdrop-blur-sm border-y-2 border-x-0
                      {{ $borderErrorColor }}
                      h-14 flex-1 text-center text-lg font-bold
                      text-slate-900 dark:text-slate-100
                      {{ $focusBorderColor }} focus:ring-4 {{ $focusRingColor }} focus:outline-none
                      transition-all duration-300
                      {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
               placeholder="{{ $placeholder }}"
               onchange="updateWireModel_{{ $id }}(this.value)">

        <!-- Botão Incrementar Modernizado -->
        <button type="button"
                onclick="incrementQuantity_{{ $id }}()"
                @if($disabled) disabled @endif
                class="group/btn relative flex items-center justify-center h-14 w-14
                       bg-gradient-to-br from-slate-100 to-slate-200
                       dark:from-slate-700 dark:to-slate-600
                       hover:from-{{ $iconColor }}-100 hover:to-{{ $iconColor }}-200
                       dark:hover:from-{{ $iconColor }}-900/50 dark:hover:to-{{ $iconColor }}-800/50
                       border-2 {{ $borderErrorColor }} {{ $hoverBorderColor }}
                       rounded-2xl shadow-lg hover:shadow-xl
                       transition-all duration-300 transform hover:scale-105
                       {{ $focusRingColor }} focus:ring-4 focus:outline-none
                       {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">

            <!-- Ícone com animação -->
            <svg class="w-4 h-4 text-slate-600 dark:text-slate-300 group-hover/btn:text-{{ $iconColor }}-600 dark:group-hover/btn:text-{{ $iconColor }}-400 transition-colors duration-300 transform group-hover/btn:scale-110"
                 aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 1v16M1 9h16"/>
            </svg>

            <!-- Efeito de ripple -->
            <div class="absolute inset-0 rounded-2xl bg-{{ $iconColor }}-400/20 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
        </button>
    </div>

    @error($wireModel)
    <div class="flex items-center mt-3 p-3 bg-red-50/80 dark:bg-red-900/30 rounded-xl border border-red-200 dark:border-red-800 backdrop-blur-sm animate-slideIn">
        <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3 animate-bounce"></i>
        <p class="text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
    </div>
    @enderror
</div>

<script>
    function incrementQuantity_{{ $id }}() {
        const input = document.getElementById('{{ $id }}');
        const currentValue = parseInt(input.value) || {{ $min }};

        let newValue = currentValue + 1;
        const maxValue = {{ $max ?? 'null' }};
        if (maxValue !== null && newValue > maxValue) {
            newValue = maxValue;
        }

        // Animação de feedback
        input.classList.add('ring-4', 'ring-green-200', 'dark:ring-green-800', 'scale-105');
        setTimeout(() => {
            input.classList.remove('ring-4', 'ring-green-200', 'dark:ring-green-800', 'scale-105');
        }, 200);

        input.value = newValue;
        input.dispatchEvent(new Event('input'));
        input.dispatchEvent(new Event('change'));
        updateWireModel_{{ $id }}(newValue);
    }    function decrementQuantity_{{ $id }}() {
        const input = document.getElementById('{{ $id }}');
        const currentValue = parseInt(input.value) || {{ $min }};

        let newValue = currentValue - 1;
        const minValue = {{ $min }};
        if (newValue < minValue) {
            newValue = minValue;
        }

        console.log('Decrement - Current:', currentValue, 'New:', newValue);

        // Animação de feedback
        input.classList.add('ring-4', 'ring-orange-200', 'dark:ring-orange-800', 'scale-95');
        setTimeout(() => {
            input.classList.remove('ring-4', 'ring-orange-200', 'dark:ring-orange-800', 'scale-95');
        }, 200);

        input.value = newValue;
        input.dispatchEvent(new Event('input'));
        input.dispatchEvent(new Event('change'));
        updateWireModel_{{ $id }}(newValue);
    }

    function updateWireModel_{{ $id }}(value) {
        @this.set('{{ $wireModel }}', value);
    }

    // Inicialização melhorada
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('{{ $id }}');

        if (input) {
            // Efeito de hover nos botões
            const buttons = input.parentElement.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.classList.add('animate-pulse');
                });
                button.addEventListener('mouseleave', function() {
                    this.classList.remove('animate-pulse');
                });
            });

            // Validação em tempo real
            input.addEventListener('input', function() {
                const value = parseInt(this.value);
                const min = parseInt('{{ $min }}');
                const max = {{ $max ?? 'null' }};

                if (value < min) {
                    this.value = min;
                    this.classList.add('animate-shake');
                    setTimeout(() => this.classList.remove('animate-shake'), 300);
                }
                else if (max !== null && value > max) {
                    this.value = max;
                    this.classList.add('animate-shake');
                    setTimeout(() => this.classList.remove('animate-shake'), 300);
                }
            });
        }
    });
</script>

<style>
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px); }
        75% { transform: translateX(3px); }
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
