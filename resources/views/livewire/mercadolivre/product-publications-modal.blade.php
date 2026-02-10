<div>
    @if($showModal)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak>
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

        {{-- Modal --}}
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-4xl transform transition-all">
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="bi bi-list-ul text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Publicações do Produto</h3>
                                    @if($product)
                                    <p class="text-sm text-white/80">{{ $product->name }}</p>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="closeModal" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/20 hover:bg-white/30 text-white transition-all">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        @if($publications && count($publications) > 0)
                            <div class="grid gap-4">
                                @foreach($publications as $pub)
                                    <div class="group relative bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition-all duration-300 overflow-hidden">
                                        {{-- Badge Status --}}
                                        <div class="absolute top-3 right-3">
                                            @php
                                                $statusConfig = match($pub->status) {
                                                    'active' => ['bg' => 'bg-green-500', 'text' => 'Ativo', 'icon' => 'check-circle-fill'],
                                                    'paused' => ['bg' => 'bg-amber-500', 'text' => 'Pausado', 'icon' => 'pause-circle-fill'],
                                                    'closed' => ['bg' => 'bg-red-500', 'text' => 'Fechado', 'icon' => 'x-circle-fill'],
                                                    'under_review' => ['bg' => 'bg-purple-500', 'text' => 'Em Revisão', 'icon' => 'search'],
                                                    default => ['bg' => 'bg-gray-500', 'text' => 'Desconhecido', 'icon' => 'question-circle'],
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $statusConfig['bg'] }} text-white text-xs font-bold shadow-lg">
                                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i>
                                                {{ $statusConfig['text'] }}
                                            </span>
                                        </div>

                                        <div class="p-5">
                                            <div class="flex gap-4">
                                                {{-- Imagem --}}
                                                @if($pub->pictures && count($pub->pictures) > 0)
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ $pub->pictures[0] }}" alt="{{ $pub->title }}" class="w-24 h-24 object-cover rounded-lg shadow-md">
                                                    </div>
                                                @endif

                                                {{-- Conteúdo --}}
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2 pr-24">
                                                        {{ $pub->title }}
                                                    </h4>
                                                    
                                                    <div class="flex flex-wrap gap-3 mb-3">
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium">
                                                            <i class="bi bi-tag"></i>
                                                            R$ {{ number_format($pub->price, 2, ',', '.') }}
                                                        </span>
                                                        
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium">
                                                            <i class="bi bi-{{ $pub->publication_type === 'kit' ? 'box-seam' : 'box' }}"></i>
                                                            {{ $pub->publication_type === 'kit' ? 'Kit' : 'Simples' }}
                                                        </span>
                                                        
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-medium">
                                                            <i class="bi bi-stack"></i>
                                                            {{ $pub->available_quantity }} disponível
                                                        </span>

                                                        @if($pub->products->first())
                                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-medium">
                                                                <i class="bi bi-box-seam"></i>
                                                                {{ $pub->products->first()->pivot->quantity }}x nesta publicação
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                                        <i class="bi bi-calendar3"></i>
                                                        <span>Criado em {{ $pub->created_at->format('d/m/Y H:i') }}</span>
                                                        @if($pub->ml_item_id)
                                                            <span class="mx-2">•</span>
                                                            <i class="bi bi-link-45deg"></i>
                                                            <span>{{ $pub->ml_item_id }}</span>
                                                        @endif
                                                    </div>

                                                    {{-- Ações --}}
                                                    <div class="flex gap-2 mt-3">
                                                        <a href="{{ route('mercadolivre.publications.show', $pub->id) }}" 
                                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-medium transition-all"
                                                           wire:navigate>
                                                            <i class="bi bi-eye"></i>
                                                            Ver Detalhes
                                                        </a>
                                                        
                                                        <a href="{{ route('mercadolivre.publications.edit', $pub->id) }}" 
                                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-500 hover:bg-purple-600 text-white text-xs font-medium transition-all"
                                                           wire:navigate>
                                                            <i class="bi bi-pencil"></i>
                                                            Editar
                                                        </a>

                                                        @if($pub->ml_permalink)
                                                            <a href="{{ $pub->ml_permalink }}" target="_blank"
                                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium transition-all">
                                                                <i class="bi bi-box-arrow-up-right"></i>
                                                                Ver no ML
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="bi bi-inbox text-4xl text-slate-400"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Nenhuma publicação encontrada</h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Este produto ainda não foi publicado no Mercado Livre</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex justify-end">
                            <button wire:click="closeModal" 
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-medium transition-all">
                                <i class="bi bi-x-lg"></i>
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
