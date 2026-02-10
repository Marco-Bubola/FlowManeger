<div>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Editar Publicação ML
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $publication->ml_item_id }} | 
                    <span class="font-medium">{{ $publication->publication_type === 'kit' ? 'Kit' : 'Simples' }}</span>
                </p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('mercadolivre.publications') }}" 
                   class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Voltar
                </a>
                
                @if($publication->status === 'active')
                <button wire:click="pausePublication" 
                        class="px-4 py-2 text-yellow-700 bg-yellow-50 border border-yellow-300 rounded-lg hover:bg-yellow-100">
                    Pausar
                </button>
                @elseif($publication->status === 'paused')
                <button wire:click="activatePublication" 
                        class="px-4 py-2 text-green-700 bg-green-50 border border-green-300 rounded-lg hover:bg-green-100">
                    Ativar
                </button>
                @endif
                
                <button wire:click="syncPublication" 
                        class="px-4 py-2 text-blue-700 bg-blue-50 border border-blue-300 rounded-lg hover:bg-blue-100">
                    Sincronizar
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Coluna Principal --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Informações Básicas --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Título *
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="title"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    maxlength="255"
                                />
                                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição
                                </label>
                                <textarea 
                                    wire:model="description"
                                    rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                ></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Preço *
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">R$</span>
                                        <input 
                                            type="number" 
                                            wire:model="price"
                                            step="0.01"
                                            min="0.01"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Anúncio
                                    </label>
                                    <select 
                                        wire:model="listingType"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="gold_special">Gold Special</option>
                                        <option value="gold_pro">Gold Pro</option>
                                        <option value="free">Clássico</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="freeShipping"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Frete Grátis</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="localPickup"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Retirada Local</span>
                                </label>
                            </div>
                            
                            <button 
                                wire:click="updatePublication" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                    
                    {{-- Produtos do Kit --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Produtos {{ $publicationType === 'kit' ? 'do Kit' : '' }} 
                                ({{ count($products) }})
                            </h3>
                            
                            <button 
                                wire:click="toggleProductSelector"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                            >
                                {{ $showProductSelector ? 'Fechar' : 'Adicionar Produto' }}
                            </button>
                        </div>
                        
                        @if($showProductSelector)
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            @livewire('mercado-livre.components.product-selector', ['initialProducts' => []])
                        </div>
                        @endif
                        
                        @if(!empty($products))
                        <div class="space-y-3">
                            @foreach($products as $product)
                            <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-4">
                                <img 
                                    src="{{ $product['image_url'] }}" 
                                    alt="{{ $product['name'] }}"
                                    class="w-16 h-16 object-cover rounded"
                                />
                                
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $product['name'] }}</h4>
                                    <p class="text-sm text-gray-500">
                                        Código: {{ $product['product_code'] }} | 
                                        Estoque: {{ $product['stock_quantity'] }} | 
                                        Disponível: {{ floor($product['stock_quantity'] / $product['quantity']) }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <div>
                                        <label class="text-xs text-gray-600 block mb-1">Qtd.</label>
                                        <input 
                                            type="number" 
                                            value="{{ $product['quantity'] }}"
                                            wire:change="updateProductQuantity({{ $product['id'] }}, $event.target.value)"
                                            min="1"
                                            class="w-20 px-2 py-1 text-center border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    
                                    <button 
                                        wire:click="removeProduct({{ $product['id'] }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                        title="Remover"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            <p class="text-sm">Nenhum produto associado</p>
                        </div>
                        @endif
                    </div>
                    
                </div>
                
                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Status Card --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Publicação:</span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $publication->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $publication->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $publication->status === 'closed' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($publication->status) }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Sincronização:</span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $publication->sync_status === 'synced' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $publication->sync_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $publication->sync_status === 'error' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($publication->sync_status) }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Qtd. Disponível:</span>
                                <span class="text-sm font-bold text-blue-600">{{ $availableQuantity }}</span>
                            </div>
                            
                            @if($publication->last_sync_at)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Última Sync:</span>
                                <span class="text-xs text-gray-500">{{ $publication->last_sync_at->diffForHumans() }}</span>
                            </div>
                            @endif
                            
                            @if($publication->error_message)
                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-xs text-red-700">{{ $publication->error_message }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Logs Recentes --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Logs Recentes</h3>
                        
                        @if($stockLogs->count() > 0)
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($stockLogs as $log)
                            <div class="text-xs p-2 bg-gray-50 rounded border-l-2 
                                {{ $log->operation_type === 'ml_sale' ? 'border-red-500' : '' }}
                                {{ $log->operation_type === 'sync_to_ml' ? 'border-blue-500' : '' }}
                                {{ $log->operation_type === 'manual_update' ? 'border-yellow-500' : '' }}
                            ">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-700">{{ $log->getOperationDescription() }}</span>
                                    <span class="text-gray-500">{{ $log->created_at->format('d/m H:i') }}</span>
                                </div>
                                <div class="text-gray-600">
                                    {{ $log->product->name ?? 'Produto' }}: 
                                    {{ $log->quantity_before }} → {{ $log->quantity_after }}
                                    ({{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }})
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhum log ainda</p>
                        @endif
                    </div>
                    
                    {{-- Ações Perigosas --}}
                    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                        <h3 class="text-lg font-semibold text-red-600 mb-4">Zona de Perigo</h3>
                        
                        <button 
                            wire:click="deletePublication"
                            wire:confirm="Tem certeza que deseja deletar esta publicação? Esta ação não pode ser desfeita."
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Deletar Publicação
                        </button>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </div>
</div>
