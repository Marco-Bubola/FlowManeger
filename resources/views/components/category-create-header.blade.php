@props([
    'title' => 'Nova Categoria',
    'description' => 'Crie e configure uma nova categoria para organizar seus produtos, transações ou transferências',
    'type' => null,
    'breadcrumbs' => []
])

<div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-800 dark:via-purple-800 dark:to-pink-800 shadow-lg border-b-4 border-white/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Breadcrumb -->
        @if(count($breadcrumbs) > 0)
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                @foreach($breadcrumbs as $index => $breadcrumb)
                    <li class="inline-flex items-center">
                        @if($index > 0)
                            <i class="fas fa-chevron-right text-white/60 text-xs mx-2"></i>
                        @endif
                        @if(isset($breadcrumb['url']))
                            <a href="{{ $breadcrumb['url'] }}" class="inline-flex items-center text-sm font-medium text-white/90 hover:text-white transition-colors">
                                @if(isset($breadcrumb['icon']))
                                    <i class="{{ $breadcrumb['icon'] }} mr-2"></i>
                                @endif
                                {{ $breadcrumb['label'] }}
                            </a>
                        @else
                            <span class="inline-flex items-center text-sm font-medium text-white">
                                @if(isset($breadcrumb['icon']))
                                    <i class="{{ $breadcrumb['icon'] }} mr-2"></i>
                                @endif
                                {{ $breadcrumb['label'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
        @endif

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Ícone -->
                <div class="hidden sm:flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm shadow-lg">
                    <i class="fas fa-tag text-white text-2xl"></i>
                </div>

                <!-- Título e Descrição -->
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-1 flex items-center gap-3">
                        {{ $title }}
                        @if($type)
                            @php
                                $badges = [
                                    'product' => ['label' => 'Produto', 'color' => 'bg-blue-500'],
                                    'transaction' => ['label' => 'Transação', 'color' => 'bg-emerald-500'],
                                    'transfer' => ['label' => 'Transferência', 'color' => 'bg-purple-500']
                                ];
                                $badge = $badges[$type] ?? ['label' => ucfirst($type), 'color' => 'bg-slate-500'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badge['color'] }} text-white shadow-lg">
                                {{ $badge['label'] }}
                            </span>
                        @endif
                    </h1>
                    <p class="text-white/90 text-sm sm:text-base max-w-2xl">{{ $description }}</p>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-center gap-3">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
