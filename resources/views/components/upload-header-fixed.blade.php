@props([
    'title' => 'Upload de Produtos',
    'description' => 'Importe produtos através de arquivo PDF ou CSV',
    'icon' => 'bi bi-file-earmark-arrow-up',
    'backRoute' => null
])

<div class="bg-white/80 dark:bg-neutral-800/80 backdrop-blur-xl border-b border-neutral-200/50 dark:border-neutral-700/50 sticky top-0 z-50">
    <div class="px-6 py-6">
        <div class="flex items-center justify-between">
            <!-- Breadcrumb e Título -->
            <div class="flex items-center space-x-4">
                @if($backRoute)
                <a href="{{ $backRoute }}"
                   class="group inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600 hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-800 dark:hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="bi bi-arrow-left text-neutral-600 dark:text-neutral-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200"></i>
                </a>
                @endif

                <div class="space-y-1">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm text-neutral-500 dark:text-neutral-400">
                        <a href="{{ route('products.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-200">
                            <i class="bi bi-box-seam mr-1"></i>
                            Produtos
                        </a>
                        <i class="bi bi-chevron-right text-xs"></i>
                        <span class="text-neutral-700 dark:text-neutral-300 font-medium">Upload</span>
                    </nav>

                    <!-- Título Principal -->
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-neutral-800 to-neutral-600 dark:from-neutral-100 dark:to-neutral-300 bg-clip-text text-transparent flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mr-4 shadow-lg">
                            <i class="{{ $icon }} text-white text-lg"></i>
                        </div>
                        {{ $title }}
                    </h1>

                    <!-- Descrição -->
                    <p class="text-neutral-600 dark:text-neutral-400 max-w-2xl">{{ $description }}</p>
                </div>
            </div>

            <!-- Actions/Stats -->
            <div class="hidden lg:flex items-center space-x-4">
                <!-- Upload Status -->
                <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 rounded-xl px-4 py-3 border border-emerald-200/50 dark:border-emerald-700/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                            Sistema pronto para upload
                        </span>
                    </div>
                </div>

                <!-- Help Button -->
                <button class="group p-3 bg-gradient-to-br from-neutral-100 to-neutral-200 dark:from-neutral-700 dark:to-neutral-600 rounded-xl hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-800 dark:hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
                        title="Ajuda">
                    <i class="bi bi-question-circle text-neutral-600 dark:text-neutral-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200"></i>
                </button>
            </div>
        </div>
    </div>
</div>
