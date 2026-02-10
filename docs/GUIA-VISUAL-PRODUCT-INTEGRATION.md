# 🎨 GUIA VISUAL: Product Integration View

**Arquivo a criar:** `resources/views/livewire/mercadolivre/product-integration.blade.php`

---

## 📐 LAYOUT COMPLETO

```
┌─────────────────────────────────────────────────────────────────┐
│                           HEADER                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ [🟡 Logo ML] Integração com Mercado Livre                   │ │
│ │              Publique e gerencie seus produtos              │ │
│ │                                          [⚙️ Configurações]  │ │
│ └─────────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│                         FILTROS                                  │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ 🔍 [Buscar produto...]  📊 [Status ▼]  📂 [Categoria ▼]   │ │
│ └─────────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│                    GRID DE PRODUTOS                              │
│                                                                  │
│ ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│ │  🖼️     │  │  🖼️     │  │  🖼️     │  │  🖼️     │        │
│ │ Produto1 │  │ Produto2 │  │ Produto3 │  │ Produto4 │        │
│ │ Código   │  │ Código   │  │ Código   │  │ Código   │        │
│ │ R$ 10,00 │  │ R$ 15,00 │  │ R$ 20,00 │  │ R$ 25,00 │        │
│ │ Estoque:5│  │ Estoque:3│  │ Estoque:8│  │ Estoque:2│        │
│ │          │  │          │  │          │  │          │        │
│ │ ❌ Não   │  │ ✅ Publi │  │ ⏸️ Pausa │  │ ❌ Não   │        │
│ │ Publicado│  │ cado     │  │ do       │  │ Publicado│        │
│ │          │  │          │  │          │  │          │        │
│ │[Publicar]│  │[🔄 Sync] │  │[▶️Ativar]│  │[Publicar]│        │
│ └──────────┘  └──────────┘  └──────────┘  └──────────┘        │
│                                                                  │
│ ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│ │  🖼️     │  │  🖼️     │  │  🖼️     │  │  🖼️     │        │
│ │ Produto5 │  │ Produto6 │  │ Produto7 │  │ Produto8 │        │
│ └──────────┘  └──────────┘  └──────────┘  └──────────┘        │
│                                                                  │
│                    [1] [2] [3] ... [10]                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 ESTRUTURA DETALHADA

### 1️⃣ HEADER

```blade
<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <!-- Logo ML -->
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-600 
                            flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-white">...</svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        Integração com Mercado Livre
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Publique e gerencie seus produtos no marketplace
                    </p>
                </div>
            </div>
            
            <!-- Botão Configurações -->
            <a href="{{ route('mercadolivre.settings') }}" 
               class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800...">
                <i class="bi bi-gear"></i> Configurações
            </a>
        </div>
```

### 2️⃣ ALERTA SE NÃO CONECTADO

```blade
@if(!$isConnected)
    <div class="mb-6 rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 
                dark:from-amber-950/50 dark:to-yellow-950/50 
                border-2 border-amber-200 dark:border-amber-800 p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-500
                        flex items-center justify-center flex-shrink-0">
                <i class="bi bi-exclamation-triangle text-white text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-2">
                    Conexão Necessária
                </h3>
                <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">
                    Você precisa conectar sua conta do Mercado Livre...
                </p>
                <a href="{{ route('mercadolivre.settings') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl
                          bg-gradient-to-br from-yellow-400 to-amber-600 text-white...">
                    <i class="bi bi-link-45deg"></i>
                    Conectar Agora
                </a>
            </div>
        </div>
    </div>
@endif
```

### 3️⃣ FILTROS

```blade
<div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Busca -->
    <div class="relative">
        <input type="text" 
               wire:model.live.debounce.300ms="search"
               placeholder="Buscar por nome, código ou barcode..."
               class="w-full pl-10 pr-4 py-3 rounded-xl border-2...">
        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2..."></i>
    </div>
    
    <!-- Filtro Status -->
    <select wire:model.live="statusFilter" class="px-4 py-3 rounded-xl...">
        <option value="all">Todos os Produtos</option>
        <option value="published">Publicados no ML</option>
        <option value="unpublished">Não Publicados</option>
    </select>
    
    <!-- Filtro Categoria -->
    <select wire:model.live="categoryFilter" class="px-4 py-3 rounded-xl...">
        <option value="all">Todas as Categorias</option>
        @foreach($categories as $category)
            <option value="{{ $category->id_category }}">
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>
```

### 4️⃣ GRID DE PRODUTOS

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($products as $product)
        <div class="relative rounded-2xl border-2 border-slate-200 dark:border-slate-800
                    bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl
                    overflow-hidden transition-all duration-200
                    hover:shadow-2xl hover:scale-[1.02]">
            
            <!-- Badge Status (canto superior direito) -->
            <div class="absolute top-3 right-3 z-10">
                @if($product->mercadoLivreProduct)
                    @if($product->mercadoLivreProduct->status === 'active')
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                     bg-emerald-500 text-white shadow-lg">
                            ✅ Ativo no ML
                        </span>
                    @elseif($product->mercadoLivreProduct->status === 'paused')
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                     bg-amber-500 text-white shadow-lg">
                            ⏸️ Pausado
                        </span>
                    @endif
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                 bg-slate-500 text-white shadow-lg">
                        ❌ Não Publicado
                    </span>
                @endif
            </div>
            
            <!-- Imagem -->
            <div class="relative h-48 bg-gradient-to-br from-slate-100 to-slate-200
                        dark:from-slate-800 dark:to-slate-900">
                <img src="{{ $product->image_url }}" 
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover">
            </div>
            
            <!-- Conteúdo -->
            <div class="p-5">
                <!-- Título -->
                <h3 class="text-lg font-bold text-slate-900 dark:text-white
                           mb-2 line-clamp-2">
                    {{ $product->name }}
                </h3>
                
                <!-- Código -->
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                    <i class="bi bi-upc-scan"></i>
                    Código: {{ $product->product_code }}
                </p>
                
                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <!-- Preço -->
                    <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-green-50
                                dark:from-emerald-950/50 dark:to-green-950/50
                                border border-emerald-200 dark:border-emerald-800 p-3">
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mb-1">
                            Preço
                        </p>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            R$ {{ number_format($product->price_sale, 2, ',', '.') }}
                        </p>
                    </div>
                    
                    <!-- Estoque -->
                    <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50
                                dark:from-blue-950/50 dark:to-indigo-950/50
                                border border-blue-200 dark:border-blue-800 p-3">
                        <p class="text-xs text-blue-700 dark:text-blue-300 mb-1">
                            Estoque
                        </p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ $product->stock_quantity }} un
                        </p>
                    </div>
                </div>
                
                <!-- Link ML (se publicado) -->
                @if($product->mercadoLivreProduct && $product->mercadoLivreProduct->permalink)
                    <a href="{{ $product->mercadoLivreProduct->permalink }}" 
                       target="_blank"
                       class="block mb-3 text-sm text-blue-600 dark:text-blue-400 
                              hover:underline truncate">
                        <i class="bi bi-box-arrow-up-right"></i>
                        Ver no Mercado Livre
                    </a>
                @endif
                
                <!-- Botões de Ação -->
                <div class="flex gap-2">
                    @if($product->mercadoLivreProduct)
                        <!-- Produto já publicado -->
                        @if($product->mercadoLivreProduct->status === 'active')
                            <!-- Sync -->
                            <button wire:click="syncProduct({{ $product->id }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 px-3 py-2 rounded-xl
                                           bg-gradient-to-br from-blue-500 to-indigo-600
                                           text-white font-semibold text-sm
                                           hover:scale-105 transition-all">
                                <i class="bi bi-arrow-repeat"></i>
                                Sincronizar
                            </button>
                            
                            <!-- Pausar -->
                            <button wire:click="pauseProduct({{ $product->id }})"
                                    wire:confirm="Tem certeza que deseja pausar este anúncio?"
                                    class="px-3 py-2 rounded-xl
                                           bg-gradient-to-br from-amber-500 to-orange-600
                                           text-white font-semibold text-sm
                                           hover:scale-105 transition-all">
                                <i class="bi bi-pause-circle"></i>
                            </button>
                        @else
                            <!-- Reativar -->
                            <button wire:click="activateProduct({{ $product->id }})"
                                    class="flex-1 px-3 py-2 rounded-xl
                                           bg-gradient-to-br from-emerald-500 to-green-600
                                           text-white font-semibold text-sm
                                           hover:scale-105 transition-all">
                                <i class="bi bi-play-circle"></i>
                                Reativar
                            </button>
                        @endif
                    @else
                        <!-- Produto não publicado -->
                        <button wire:click="openPublishModal({{ $product->id }})"
                                class="w-full px-4 py-3 rounded-xl
                                       bg-gradient-to-br from-yellow-400 via-yellow-500 to-amber-600
                                       text-white font-bold text-sm
                                       shadow-lg shadow-yellow-500/30
                                       hover:shadow-xl hover:shadow-yellow-500/50
                                       hover:scale-105 transition-all">
                            <i class="bi bi-upload"></i>
                            Publicar no ML
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <!-- Estado vazio -->
        <div class="col-span-full">
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-900/50 
                        border-2 border-dashed border-slate-300 dark:border-slate-700 
                        p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full
                            bg-slate-200 dark:bg-slate-800
                            flex items-center justify-center">
                    <i class="bi bi-inbox text-4xl text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Nenhum produto encontrado
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Tente ajustar os filtros ou adicione novos produtos
                </p>
            </div>
        </div>
    @endforelse
</div>

<!-- Paginação -->
<div class="mt-6">
    {{ $products->links() }}
</div>
```

### 5️⃣ MODAL DE PUBLICAÇÃO

```blade
@if($showPublishModal && $selectedProduct)
    <div class="fixed inset-0 z-50 overflow-y-auto" 
         x-data="{ show: @entangle('showPublishModal') }"
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-3xl
                        bg-white dark:bg-slate-900 
                        border-2 border-slate-200 dark:border-slate-800
                        shadow-2xl p-6"
                 @click.away="show = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">
                
                <!-- Header Modal -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br 
                                    from-yellow-400 to-amber-600
                                    flex items-center justify-center">
                            <i class="bi bi-upload text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                Publicar no Mercado Livre
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                {{ $selectedProduct->name }}
                            </p>
                        </div>
                    </div>
                    <button @click="show = false"
                            class="w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <!-- Conteúdo Modal -->
                <div class="space-y-6">
                    <!-- Categoria ML -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 
                                      dark:text-slate-300 mb-2">
                            Categoria do Mercado Livre *
                        </label>
                        <select wire:model.live="mlCategoryId"
                                class="w-full px-4 py-3 rounded-xl border-2...">
                            <option value="">Selecione uma categoria</option>
                            @foreach($mlCategories as $category)
                                <option value="{{ $category['id'] }}">
                                    {{ $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Atributos Obrigatórios -->
                    @if(!empty($mlCategoryAttributes))
                        <div class="space-y-4">
                            <h4 class="font-bold text-slate-900 dark:text-white">
                                Atributos Obrigatórios
                            </h4>
                            @foreach($mlCategoryAttributes as $attr)
                                <div>
                                    <label class="block text-sm font-medium...">
                                        {{ $attr['name'] }} *
                                    </label>
                                    @if($attr['value_type'] === 'list')
                                        <select wire:model="selectedAttributes.{{ $attr['id'] }}"...>
                                            <option value="">Selecione...</option>
                                            @foreach($attr['values'] as $value)
                                                <option value="{{ $value['id'] }}">
                                                    {{ $value['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text"
                                               wire:model="selectedAttributes.{{ $attr['id'] }}"...>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <!-- Tipo de Anúncio -->
                    <div>
                        <label class="block text-sm font-semibold...">
                            Tipo de Anúncio
                        </label>
                        <select wire:model="listingType"...>
                            <option value="gold_special">Gold Special (Premium)</option>
                            <option value="gold_pro">Gold Pro</option>
                            <option value="gold">Gold</option>
                            <option value="free">Grátis</option>
                        </select>
                    </div>
                    
                    <!-- Opções de Envio -->
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-4 rounded-xl 
                                      border-2 cursor-pointer...">
                            <input type="checkbox" 
                                   wire:model="freeShipping"
                                   class="w-5 h-5 rounded...">
                            <span class="text-sm font-medium...">
                                Frete Grátis
                            </span>
                        </label>
                        
                        <label class="flex items-center gap-3 p-4 rounded-xl...">
                            <input type="checkbox" 
                                   wire:model="localPickup"
                                   class="w-5 h-5 rounded...">
                            <span class="text-sm font-medium...">
                                Retirada Local
                            </span>
                        </label>
                    </div>
                </div>
                
                <!-- Footer Modal -->
                <div class="flex gap-3 mt-6 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <button @click="show = false"
                            class="flex-1 px-6 py-3 rounded-xl
                                   bg-slate-200 dark:bg-slate-800...">
                        Cancelar
                    </button>
                    <button wire:click="publishProduct"
                            wire:loading.attr="disabled"
                            class="flex-1 px-6 py-3 rounded-xl
                                   bg-gradient-to-br from-yellow-400 to-amber-600
                                   text-white font-bold...">
                        <span wire:loading.remove wire:target="publishProduct">
                            <i class="bi bi-upload"></i>
                            Publicar Agora
                        </span>
                        <span wire:loading wire:target="publishProduct">
                            <i class="bi bi-arrow-repeat animate-spin"></i>
                            Publicando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
```

---

## 🎨 CLASSES TAILWIND IMPORTANTES

```css
/* Containers */
min-h-screen bg-slate-50 dark:bg-slate-950
px-4 sm:px-6 lg:px-8 py-6

/* Cards */
rounded-2xl border-2 border-slate-200 dark:border-slate-800
bg-white/80 dark:bg-slate-900/60 backdrop-blur shadow-xl

/* Gradientes ML */
bg-gradient-to-br from-yellow-400 via-yellow-500 to-amber-600

/* Grid Responsivo */
grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6

/* Hover Effects */
hover:shadow-2xl hover:scale-[1.02] transition-all duration-200

/* Badges */
px-3 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white

/* Botões */
px-4 py-3 rounded-xl font-bold shadow-lg hover:scale-105 transition-all
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

- [ ] Copiar estrutura base da Settings
- [ ] Implementar header com logo ML
- [ ] Adicionar alerta de não conectado
- [ ] Criar seção de filtros (busca, status, categoria)
- [ ] Implementar grid de produtos responsivo
- [ ] Adicionar badges de status em cada card
- [ ] Criar botões de ação (Publicar, Sync, Pausar, Reativar)
- [ ] Implementar modal de publicação com Alpine.js
- [ ] Adicionar formulário de categoria e atributos
- [ ] Implementar estado vazio
- [ ] Adicionar paginação
- [ ] Testar responsividade
- [ ] Testar dark mode
- [ ] Adicionar loading states
- [ ] Implementar notificações toast

---

**Tempo Estimado:** 1-2 horas  
**Complexidade:** Média  
**Referência:** resources/views/livewire/mercadolivre/settings.blade.php

🎯 **Use este guia para criar a view completa!**
