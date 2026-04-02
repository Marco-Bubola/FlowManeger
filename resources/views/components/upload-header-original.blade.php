@props([
    'title' => 'Upload de Produtos',
    'description' => 'Importe produtos através de arquivo PDF ou CSV',
    'backRoute' => null,
    'showProductsInfo' => false,
    'productsCount' => 0
])

<!-- Header Moderno com Gradiente e Glassmorphism -->
<div class="upload-page-header relative overflow-hidden bg-gradient-to-r from-white/80 via-blue-50/90 to-indigo-50/80 dark:from-slate-800/90 dark:via-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl mb-6">
    <!-- Efeito de brilho sutil -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent dark:via-white/5 animate-pulse" style="pointer-events:none"></div>

    <!-- Background decorativo -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400/20 via-blue-400/20 to-indigo-400/20 rounded-full transform translate-x-16 -translate-y-16"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 via-blue-400/10 to-purple-400/10 rounded-full transform -translate-x-10 translate-y-10"></div>

    <div class="upload-header-inner relative px-8 py-6">
        <div class="upload-header-main flex items-center justify-between">
            <div class="flex items-center gap-6">
                @if($backRoute)
                <!-- Botão voltar melhorado -->
                <a href="{{ $backRoute }}"
                    class="group relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-white to-blue-50 dark:from-slate-800 dark:to-slate-700 hover:from-blue-50 hover:to-indigo-100 dark:hover:from-slate-700 dark:hover:to-slate-600 transition-all duration-300 shadow-lg hover:shadow-xl border border-white/50 dark:border-slate-600/50 backdrop-blur-sm">
                    <i class="bi bi-arrow-left text-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-200"></i>
                    <!-- Efeito hover ring -->
                    <div class="absolute inset-0 rounded-2xl bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                @endif

                <!-- Ícone principal e título -->
                <div class="relative flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-xl shadow-purple-500/25">
                    <i class="bi bi-file-earmark-arrow-up text-white text-3xl"></i>
                    <!-- Efeito de brilho -->
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
                </div>

                <div class="space-y-2">
                    <h1 class="upload-header-title text-4xl font-bold bg-gradient-to-r from-slate-800 via-indigo-700 to-purple-700 dark:from-slate-100 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent">
                        {{ $title }}
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">
                        📄 {{ $description }}
                    </p>
                    @if($showProductsInfo)
                    <div class="upload-products-count-chip">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-700">
                            <i class="bi bi-box-seam text-xs"></i>
                            {{ $productsCount }} produto(s) encontrado(s)
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Slot para botões adicionais -->
            @if(isset($actions))
            <div class="upload-header-actions flex items-center gap-3">
                {{ $actions }}
            </div>
            @endif
        </div>
    </div>
</div>
