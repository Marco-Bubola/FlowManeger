<div x-data="{ 
    activeTab: 'overview',
    chartInitialized: false 
}" class="min-h-screen w-screen overflow-x-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 relative">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 opacity-30 dark:opacity-20 pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
    </div>

    <!-- Header -->
    <div class="relative backdrop-blur-md bg-white/80 dark:bg-slate-900/80 shadow-xl border-b border-white/20 dark:border-slate-700/50 sticky top-0 z-50">
        <div class="w-full px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <!-- Voltar -->
                    <a href="{{ route('products.index') }}"
                        class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-800 dark:hover:to-indigo-800 transition-all duration-300 shadow-md hover:shadow-lg group">
                        <i class="bi bi-arrow-left text-xl text-slate-600 dark:text-slate-300 group-hover:scale-110 transition-transform duration-300"></i>
                    </a>

                    <!-- Imagem do produto -->
                    <div class="relative w-20 h-20 rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-white to-slate-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center group">
                        @if($mainProduct->image)
                        <img src="{{ asset('storage/products/' . $mainProduct->image) }}"
                            alt="{{ $mainProduct->name }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                        <div class="relative">
                            <i class="bi bi-box-seam text-3xl text-slate-400 dark:text-slate-500 group-hover:text-blue-500 transition-colors duration-300"></i>
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-pulse"></div>
                        </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    <!-- Info básica -->
                    <div class="space-y-3">
                        <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-slate-800 via-blue-600 to-indigo-600 dark:from-slate-100 dark:via-blue-400 dark:to-indigo-400">{{ $mainProduct->name }}</h1>
                        <div class="flex items-center space-x-4 mt-3">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <i class="bi bi-hash mr-2 text-white/80"></i>
                                {{ $productCode }}
                            </span>
                            @if($category)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-purple-100 via-pink-50 to-purple-100 dark:from-purple-900 dark:via-purple-800 dark:to-purple-900 text-purple-800 dark:text-purple-200 shadow-lg border border-purple-200 dark:border-purple-700 hover:shadow-xl transition-all duration-300">
                                <i class="{{ $this->getCategoryIcon($category->icone) }} mr-1.5 text-sm text-purple-600 dark:text-purple-400"></i>
                                {{ $category->name }}
                            </span>
                            @endif
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold shadow-lg border transition-all duration-300 hover:shadow-xl
                                {{ $products->count() > 1 ? 'bg-gradient-to-r from-amber-100 via-orange-50 to-amber-100 dark:from-amber-900 dark:via-orange-900 dark:to-amber-900 text-amber-800 dark:text-amber-200 border-amber-200 dark:border-amber-700 hover:from-amber-200 hover:to-orange-200' : 'bg-gradient-to-r from-green-100 via-emerald-50 to-green-100 dark:from-green-900 dark:via-emerald-900 dark:to-green-900 text-green-800 dark:text-green-200 border-green-200 dark:border-green-700 hover:from-green-200 hover:to-emerald-200' }}">
                                <i class="bi bi-layers mr-2 {{ $products->count() > 1 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}"></i>
                                {{ $products->count() }} {{ $products->count() === 1 ? 'variação' : 'variações' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Editar -->
                <div class="flex items-center space-x-4">
                    <!-- Quick Stats -->
                    <div class="hidden lg:flex items-center space-x-6 px-6 py-3 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl border border-white/20 dark:border-slate-700/50 shadow-lg">
                        <div class="text-center">
                            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Vendas</div>
                            <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($analytics['total_quantity_sold']) }}</div>
                        </div>
                        <div class="w-px h-8 bg-slate-300 dark:bg-slate-600"></div>
                        <div class="text-center">
                            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Receita</div>
                            <div class="text-xl font-bold text-blue-600 dark:text-blue-400">R$ {{ number_format($analytics['total_revenue'], 0, ',', '.') }}</div>
                        </div>
                        <div class="w-px h-8 bg-slate-300 dark:bg-slate-600"></div>
                        <div class="text-center">
                            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Estoque</div>
                            <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($analytics['total_stock']) }}</div>
                        </div>
                    </div>
                    
                    <a href="{{ route('products.edit', $mainProduct->id) }}"
                        class="group inline-flex items-center px-8 py-4 bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 hover:from-orange-600 hover:via-red-600 hover:to-pink-600 text-white font-bold rounded-2xl shadow-2xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                        <i class="bi bi-pencil-square mr-3 text-lg group-hover:rotate-12 transition-transform duration-300"></i>
                        Editar Produto
                        <i class="bi bi-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="relative w-full px-6 py-8">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4">Página do Produto</h2>
            <p class="text-slate-600 dark:text-slate-400">A página está sendo reconstruída para corrigir o erro do Livewire.</p>
            <div class="mt-6">
                <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">Produto: {{ $mainProduct->name }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Código: {{ $productCode }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
console.log('Livewire component carregado com sucesso');
</script>
@endpush
