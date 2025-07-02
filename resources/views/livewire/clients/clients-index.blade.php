@extends('layouts.user_type.auth')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-users text-indigo-600 mr-3"></i>Clientes
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">Gerencie seus clientes de forma simples e eficiente</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('clients.create') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Cliente
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Pesquisa -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search text-gray-400 mr-2"></i>Pesquisar
                    </label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           id="search"
                           placeholder="Digite o nome do cliente..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>

                <!-- Ordenar -->
                <div>
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort text-gray-400 mr-2"></i>Ordenar por
                    </label>
                    <select wire:model.live="filter" 
                            id="filter"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                        <option value="">Selecione...</option>
                        <option value="created_at">Últimos Adicionados</option>
                        <option value="updated_at">Últimos Atualizados</option>
                        <option value="name_asc">Nome A-Z</option>
                        <option value="name_desc">Nome Z-A</option>
                    </select>
                </div>

                <!-- Itens por página -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list text-gray-400 mr-2"></i>Por página
                    </label>
                    <select wire:model.live="perPage" 
                            id="perPage"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                        <option value="12">12</option>
                        <option value="18">18</option>
                        <option value="24">24</option>
                        <option value="36">36</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lista de Clientes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($clients->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
                    @foreach($clients as $client)
                        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-200 group">
                            <!-- Avatar -->
                            <div class="flex justify-center mb-4">
                                <div class="relative">
                                    <img src="{{ $client->caminho_foto }}" 
                                         alt="Avatar de {{ $client->name }}"
                                         class="w-16 h-16 rounded-full border-4 border-indigo-100 group-hover:border-indigo-200 transition-colors duration-200">
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white"></div>
                                </div>
                            </div>

                            <!-- Informações -->
                            <div class="text-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $client->name }}</h3>
                                @if($client->email)
                                    <p class="text-sm text-gray-600 mb-1">
                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                        {{ $client->email }}
                                    </p>
                                @endif
                                @if($client->phone)
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-phone text-gray-400 mr-1"></i>
                                        {{ $client->phone }}
                                    </p>
                                @endif
                            </div>

                            <!-- Estatísticas -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="flex justify-center">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-indigo-600">{{ $client->sales->count() }}</p>
                                        <p class="text-xs text-gray-600">
                                            <i class="fas fa-shopping-cart mr-1"></i>
                                            {{ $client->sales->count() === 1 ? 'Compra' : 'Compras' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="flex gap-2">
                                <a href="{{ route('clients.edit', $client->id) }}" 
                                   class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $client->id }})" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <i class="fas fa-trash mr-1"></i>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Paginação -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $clients->links() }}
                </div>
            @else
                <!-- Estado vazio -->
                <div class="text-center py-16">
                    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-users text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum cliente encontrado</h3>
                    <p class="text-gray-600 mb-6">
                        @if($search)
                            Não encontramos clientes com o termo "{{ $search }}".
                        @else
                            Comece adicionando seu primeiro cliente.
                        @endif
                    </p>
                    <a href="{{ route('clients.create') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Adicionar Cliente
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
@if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Tem certeza de que deseja excluir o cliente <strong>{{ $deletingClient?->name }}</strong>? 
                                Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="deleteClient"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Excluir
                    </button>
                    <button type="button" 
                            wire:click="cancelDelete"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
