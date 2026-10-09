<div class="client-transferencias-page w-full mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-transferencias-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-transferencias-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-transferencias-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-transferencias-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-transferencias-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/client-transferencias-ultrawide.css') }}">
    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8">
        <x-client-page-header :client="$client" title="Transferências" icon="bi-arrow-left-right" active="transferencias" />

        <!-- Filtros e Estatísticas -->
        <div class="space-y-3 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 [&>*:last-child:nth-child(odd)]:col-span-2 sm:[&>*:last-child:nth-child(odd)]:col-span-1">
                <x-gestao-stat label="Total recebido" :value="'R$ ' . number_format($totalRecebido, 2, ',', '.')" icon="bi-arrow-down-circle" tone="emerald" />
                <x-gestao-stat label="Total enviado" :value="'R$ ' . number_format($totalEnviado, 2, ',', '.')" icon="bi-arrow-up-circle" tone="amber" />
                <x-gestao-stat label="Saldo" :value="'R$ ' . number_format($totalRecebido - $totalEnviado, 2, ',', '.')" icon="bi-calculator" :tone="($totalRecebido - $totalEnviado) >= 0 ? 'sky' : 'rose'" />
            </div>
            <x-list-toolbar model="" class="!mb-0">
                <x-toolbar.group icon="bi-funnel">
                    <x-toolbar.chip wire:click="setTipo('all')" :active="$tipo === 'all'" icon="bi-list">Todas</x-toolbar.chip>
                    <x-toolbar.chip wire:click="setTipo('recebidas')" :active="$tipo === 'recebidas'" icon="bi-arrow-down">Recebidas</x-toolbar.chip>
                    <x-toolbar.chip wire:click="setTipo('enviadas')" :active="$tipo === 'enviadas'" icon="bi-arrow-up">Enviadas</x-toolbar.chip>
                </x-toolbar.group>
            </x-list-toolbar>
        </div>

        <!-- Lista de Transferências -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
            @if($transferencias->count() > 0)
                <!-- Header da tabela -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Lista de Transferências
                        @if($tipo !== 'all')
                            - {{ ucfirst($tipo) }}
                        @endif
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $transferencias->total() }} registros)</span>
                    </h3>
                </div>

                <!-- Grid de Transferências -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($transferencias as $transferencia)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 border-l-4 {{ $transferencia->type_id == 1 ? 'border-green-500' : 'border-orange-500' }}">
                            <!-- Header da transferência -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-full {{ $transferencia->type_id == 1 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-orange-100 dark:bg-orange-900/30' }} flex items-center justify-center">
                                        <i class="bi bi-arrow-{{ $transferencia->type_id == 1 ? 'down' : 'up' }} {{ $transferencia->type_id == 1 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }} text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($transferencia->date)->format('d/m/Y') }}
                                        </p>
                                        @if($transferencia->category)
                                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $transferencia->category->name }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tipo da transferência -->
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transferencia->type_id == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                                    {{ $transferencia->type_id == 1 ? 'Recebido' : 'Enviado' }}
                                </span>
                            </div>

                            <!-- Descrição -->
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 line-clamp-2">
                                {{ $transferencia->description }}
                            </h4>

                            <!-- Valor -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Valor:</span>
                                <span class="text-lg font-bold {{ $transferencia->type_id == 1 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $transferencia->type_id == 1 ? '+' : '-' }} R$ {{ number_format($transferencia->value, 2, ',', '.') }}
                                </span>
                            </div>

                            <!-- Categoria com cor -->
                            @if($transferencia->category && $transferencia->category->hexcolor_category)
                                <div class="mt-2 flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $transferencia->category->hexcolor_category }}"></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $transferencia->category->name }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Paginação -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-zinc-700">
                    {{ $transferencias->links() }}
                </div>
            @else
                <!-- Estado vazio -->
                <div class="text-center py-16">
                    <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-6">
                        <i class="bi bi-arrow-left-right text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Nenhuma transferência encontrada
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        @if($tipo === 'all')
                            Este cliente ainda não possui transferências cadastradas.
                        @elseif($tipo === 'recebidas')
                            Este cliente ainda não possui transferências recebidas.
                        @else
                            Este cliente ainda não possui transferências enviadas.
                        @endif
                    </p>
                    <a href="{{ route('cashbook.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="bi bi-plus mr-2"></i>
                        Criar Nova Transferência
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
