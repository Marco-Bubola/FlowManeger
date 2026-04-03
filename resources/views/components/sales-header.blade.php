@props([
    'title' => 'Nova Venda',
    'description' => 'Registre uma nova venda no sistema seguindo os passos',
    'backRoute' => null,
    'currentStep' => 1,
    'steps' => []
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="sales-create-header relative overflow-hidden border border-white/60 dark:border-slate-700/60 bg-[linear-gradient(135deg,rgba(255,255,255,0.92),rgba(238,242,255,0.88),rgba(224,231,255,0.92))] dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.94),rgba(30,41,59,0.92),rgba(17,24,39,0.96))] backdrop-blur-2xl rounded-[28px] shadow-[0_24px_80px_rgba(15,23,42,0.16)]">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_30%)] dark:bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.24),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.16),transparent_30%)]"></div>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/70 to-transparent dark:via-slate-400/30"></div>

    <!-- Background decorativo -->
    <div class="absolute -top-10 right-8 h-32 w-32 rounded-full bg-indigo-400/20 blur-2xl"></div>
    <div class="absolute -bottom-8 left-0 h-28 w-28 rounded-full bg-emerald-400/15 blur-2xl"></div>

    <div class="sales-create-header-inner relative px-3 sm:px-6 py-4">
        <div class="sales-create-header-main flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0">
                @if($backRoute)
                <!-- Botão voltar compacto -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/85 dark:bg-slate-900/80 hover:bg-white dark:hover:bg-slate-800 transition-all duration-200 shadow-sm border border-slate-200/70 dark:border-slate-700/70 backdrop-blur-sm shrink-0">
                    <i class="bi bi-arrow-left text-lg text-indigo-600 dark:text-indigo-300 group-hover:-translate-x-0.5 transition-transform duration-150"></i>
                    <div class="absolute inset-0 rounded-xl bg-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </a>
                @endif

                <!-- Ícone principal e título (compacto) -->
                <div class="relative flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-sky-500 to-emerald-500 shadow-lg shadow-indigo-500/20 shrink-0">
                    <i class="bi bi-plus-circle text-white text-2xl"></i>
                    <div class="absolute inset-[1px] rounded-2xl border border-white/25"></div>
                </div>

                <div class="space-y-1 min-w-0">
                    @isset($breadcrumb)
                        <div class="hidden sm:block">{{ $breadcrumb }}</div>
                    @endisset
                    <h1 class="sales-create-header-title text-lg sm:text-2xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent truncate">
                        {{ $title }}
                    </h1>
                    <p class="sales-create-header-subtitle flex items-center gap-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-medium line-clamp-2 sm:line-clamp-none text-center sm:text-left">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300 shrink-0">
                            <i class="bi bi-stars text-[11px]"></i>
                        </span>
                        <span>{!! $description !!}</span>
                    </p>
                </div>
            </div>

            <div class="sales-create-header-actions flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-3 w-full lg:w-auto">
                {{-- Slot de ações (botões) passado pelo componente pai --}}
                {!! $actions ?? '' !!}

                <!-- Steppers compactos -->
                @if(count($steps) > 0)
                <div class="hidden sm:flex items-center justify-start lg:justify-center w-full lg:w-auto overflow-x-auto pb-1">
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-max">
                        @foreach($steps as $index => $step)
                            @php $stepNumber = $index + 1; @endphp

                            <!-- Step -->
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-lg transition-all duration-200 shrink-0"
                                    :class="$wire.currentStep === {{ $stepNumber }} ? 'bg-gradient-to-br {{ $step['gradient'] ?? 'from-indigo-500 to-purple-500' }} text-white shadow-md shadow-indigo-500/20' : ($wire.currentStep > {{ $stepNumber }} ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-600 dark:text-gray-400')">
                                    <i class="bi {{ $step['icon'] ?? 'bi-circle' }} text-lg" x-show="$wire.currentStep === {{ $stepNumber }}"></i>
                                    <i class="bi bi-check-lg text-lg" x-show="$wire.currentStep > {{ $stepNumber }}"></i>
                                </div>
                                <div class="ml-2 sm:ml-3">
                                    <div class="flex items-center">
                                        <p class="text-xs sm:text-sm font-semibold transition-colors duration-200 whitespace-nowrap"
                                            :class="$wire.currentStep === {{ $stepNumber }} ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">{{ $step['title'] }}</p>
                                        <i class="bi bi-check-circle-fill text-green-500 ml-2 text-sm" x-show="$wire.currentStep > {{ $stepNumber }}"></i>
                                    </div>
                                    <p class="hidden sm:block text-xs text-gray-600 dark:text-gray-400">{{ $step['description'] }}</p>
                                </div>
                            </div>

                            <!-- Connector -->
                            @if(!$loop->last)
                            <div class="w-8 sm:w-12 h-1 rounded-full transition-all duration-200"
                                :class="$wire.currentStep >= {{ $stepNumber + 1 }} ? 'bg-gradient-to-r {{ $step['connector_gradient'] ?? 'from-indigo-500 to-purple-500' }}' : 'bg-gray-300 dark:bg-zinc-600'"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
