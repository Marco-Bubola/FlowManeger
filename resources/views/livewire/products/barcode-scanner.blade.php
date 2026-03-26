<div
    x-data="barcodeScanner()"
    x-on:show-toast.window="showToast($event.detail.message, $event.detail.type)"
    class="w-full app-viewport-fit">

    {{-- ========== TOAST ========== --}}
    <div
        x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 z-50 max-w-sm"
        style="display:none">
        <div :class="{
            'bg-emerald-500': toastType === 'success',
            'bg-red-500':     toastType === 'error',
            'bg-amber-500':   toastType === 'warning',
            'bg-blue-500':    toastType === 'info',
        }" class="text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3">
            <svg x-show="toastType === 'success'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg x-show="toastType === 'error'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <svg x-show="toastType === 'warning'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14A7 7 0 0012 5z"></path>
            </svg>
            <svg x-show="toastType === 'info'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"></path>
            </svg>
            <span x-text="toastMsg" class="text-sm font-medium"></span>
        </div>
    </div>

    {{-- ========== HEADER MODERNO (padrão do projeto) ========== --}}
    <x-modern-header
        icon="fas fa-barcode"
        title="Scanner de Código de Barras"
        :subtitle="'Consulte, identifique e gerencie produtos pelo código de barras — busca <strong>local</strong> e <strong>online</strong>'"
        :breadcrumb="[
            ['icon' => 'fas fa-home', 'url' => route('dashboard.index'), 'label' => 'Dashboard'],
            ['icon' => 'fas fa-box', 'url' => route('products.index'), 'label' => 'Produtos'],
            ['label' => 'Scanner de Barras'],
        ]"
        gradient="from-indigo-500 via-purple-500 to-pink-500"
        iconBg="from-indigo-500 via-purple-500 to-pink-500">
        <x-slot name="actions">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-slate-700/80 backdrop-blur-sm border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-white dark:hover:bg-slate-600 transition-all shadow-sm">
                <i class="fas fa-list text-indigo-500"></i>
                <span class="hidden sm:inline">Ver Produtos</span>
            </a>
            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-bold shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:scale-105 transition-all">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Novo Produto</span>
            </a>
        </x-slot>
        <x-slot name="extra">
            {{-- Stats rápido --}}
            @php $stats = $this->stats; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2">
                <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/40 dark:border-slate-700/40">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Produtos</p>
                    <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/40 dark:border-slate-700/40">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Com Barcode</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['with_barcode'] }}</p>
                </div>
                <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/40 dark:border-slate-700/40">
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Sem Barcode</p>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['without_barcode'] }}</p>
                </div>
                <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/40 dark:border-slate-700/40">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Cobertura</p>
                    <div class="flex items-center gap-2">
                        <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['percentage'] }}%</p>
                        <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500" style="width: {{ $stats['percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modern-header>

    {{-- ========== CONTEÚDO PRINCIPAL ========== --}}
    <div class="px-2 sm:px-4 lg:px-6 pb-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ========== COLUNA ESQUERDA (2/3): Scanner + resultados ========== --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- ---- Seletor de Modo ---- --}}
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm p-4">
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3"><i class="fas fa-layer-group mr-1"></i> Selecione o Modo de Operação</p>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        {{-- Consultar --}}
                        <button wire:click="setMode('consulta')" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 transition-all duration-200 text-sm font-semibold {{ $activeMode === 'consulta' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 shadow-md shadow-blue-500/20' : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-blue-300 hover:bg-blue-50/50 dark:hover:bg-blue-900/20' }}">
                            <i class="fas fa-search text-lg"></i>
                            <span class="text-[10px] sm:text-xs text-center leading-tight">Consultar</span>
                        </button>
                        {{-- Preço --}}
                        <button wire:click="setMode('preco')" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 transition-all duration-200 text-sm font-semibold {{ $activeMode === 'preco' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300 shadow-md shadow-pink-500/20' : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-pink-300 hover:bg-pink-50/50 dark:hover:bg-pink-900/20' }}">
                            <i class="fas fa-tag text-lg"></i>
                            <span class="text-[10px] sm:text-xs text-center leading-tight">Ver Preço</span>
                        </button>
                        {{-- Estoque --}}
                        <button wire:click="setMode('estoque')" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 transition-all duration-200 text-sm font-semibold {{ $activeMode === 'estoque' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 shadow-md shadow-emerald-500/20' : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-emerald-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20' }}">
                            <i class="fas fa-boxes-stacked text-lg"></i>
                            <span class="text-[10px] sm:text-xs text-center leading-tight">Estoque</span>
                        </button>
                        {{-- Inventário --}}
                        <button wire:click="setMode('inventario')" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 transition-all duration-200 text-sm font-semibold {{ $activeMode === 'inventario' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 shadow-md shadow-orange-500/20' : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-orange-300 hover:bg-orange-50/50 dark:hover:bg-orange-900/20' }}">
                            <i class="fas fa-clipboard-list text-lg"></i>
                            <span class="text-[10px] sm:text-xs text-center leading-tight">Inventário</span>
                        </button>
                        {{-- Venda --}}
                        <button wire:click="setMode('venda')" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 transition-all duration-200 text-sm font-semibold {{ $activeMode === 'venda' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 shadow-md shadow-purple-500/20' : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-purple-300 hover:bg-purple-50/50 dark:hover:bg-purple-900/20' }}">
                            <i class="fas fa-cart-shopping text-lg"></i>
                            <span class="text-[10px] sm:text-xs text-center leading-tight">Venda</span>
                        </button>
                        {{-- Vincular --}}
                        <button wire:click="setMode('vincular')" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 transition-all duration-200 text-sm font-semibold {{ $activeMode === 'vincular' ? 'border-cyan-500 bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 shadow-md shadow-cyan-500/20' : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-cyan-300 hover:bg-cyan-50/50 dark:hover:bg-cyan-900/20' }}">
                            <i class="fas fa-link text-lg"></i>
                            <span class="text-[10px] sm:text-xs text-center leading-tight">Vincular</span>
                        </button>
                    </div>
                </div>

                {{-- ---- Campo de Código de Barras ---- --}}
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm p-4 sm:p-5">

                    {{-- Abas: Digitar | Câmera | Imagem --}}
                    <div class="flex items-center gap-1 mb-4 p-1 bg-slate-100 dark:bg-slate-900/60 rounded-xl">
                        <button @click="setScanMode('manual')" :class="scanMode === 'manual' ? 'bg-white dark:bg-slate-700 shadow-sm text-indigo-700 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all duration-200">
                            <i class="fas fa-keyboard"></i>
                            <span>Digitar</span>
                        </button>
                        <button @click="setScanMode('camera')" :class="scanMode === 'camera' ? 'bg-white dark:bg-slate-700 shadow-sm text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all duration-200">
                            <i class="fas fa-camera"></i>
                            <span>Câmera</span>
                        </button>
                        <button @click="setScanMode('image')" :class="scanMode === 'image' ? 'bg-white dark:bg-slate-700 shadow-sm text-purple-700 dark:text-purple-300' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all duration-200">
                            <i class="fas fa-image"></i>
                            <span>Imagem</span>
                        </button>
                    </div>

                    {{-- ======= ABA: DIGITAR (manual) ======= --}}
                    <div x-show="scanMode === 'manual'" x-transition.opacity.duration.200ms>
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-barcode text-indigo-500"></i>
                            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Código de Barras</p>
                            <span class="text-[10px] text-slate-400 hidden sm:inline">— leitor USB digita automaticamente</span>
                        </div>

                        <form wire:submit.prevent="searchBarcode" class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-slate-400"></i>
                                </div>
                                <input
                                    id="barcode-input"
                                    type="text"
                                    wire:model="barcodeInput"
                                    placeholder="Ex: 7891234567890"
                                    autocomplete="off"
                                    autofocus
                                    class="w-full pl-11 pr-4 py-3 sm:py-3.5 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-base sm:text-lg font-mono tracking-wider focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 placeholder:text-slate-300 dark:placeholder:text-slate-600" />
                            </div>
                            <button type="submit"
                                class="px-5 sm:px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold shadow-lg shadow-indigo-500/30 transition-all duration-200 hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                                wire:loading.attr="disabled" wire:target="searchBarcode">
                                <i wire:loading.remove wire:target="searchBarcode" class="fas fa-search"></i>
                                <i wire:loading wire:target="searchBarcode" class="fas fa-spinner fa-spin"></i>
                                <span>Buscar</span>
                            </button>
                        </form>
                    </div>

                    {{-- ======= ABA: CÂMERA (ao vivo) ======= --}}
                    <div x-show="scanMode === 'camera'" x-transition.opacity.duration.200ms>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-camera text-emerald-500"></i>
                                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Scanner por Câmera</p>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- Alternar câmera frontal/traseira --}}
                                <button @click="toggleCameraFacing()" x-show="cameraActive" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all" title="Alternar câmera">
                                    <i class="fas fa-camera-rotate"></i>
                                </button>
                                <button @click="cameraActive ? stopCamera() : startCamera()" :class="cameraActive ? 'bg-red-500 hover:bg-red-600 shadow-red-500/30' : 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 shadow-emerald-500/30'" class="px-4 py-2 rounded-xl text-white text-sm font-bold shadow-lg transition-all duration-200 hover:scale-105 flex items-center gap-2">
                                    <i :class="cameraActive ? 'fas fa-stop' : 'fas fa-play'"></i>
                                    <span x-text="cameraActive ? 'Parar' : 'Iniciar'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Viewport da câmera --}}
                        <div class="relative rounded-xl overflow-hidden border-2 transition-all duration-300" :class="cameraActive ? 'border-emerald-400 dark:border-emerald-600' : 'border-slate-200 dark:border-slate-700'">
                            <div id="camera-scanner-viewport" class="w-full" style="min-height: 280px;"></div>

                            {{-- Overlay quando câmera está desligada --}}
                            <div x-show="!cameraActive" class="absolute inset-0 bg-slate-100 dark:bg-slate-900 flex flex-col items-center justify-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center">
                                    <i class="fas fa-camera text-3xl text-emerald-500/60"></i>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 text-center px-4">Clique em <strong>"Iniciar"</strong> para ativar a câmera</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center">Funciona em iPhone, iPad e Android</p>
                            </div>
                        </div>

                        {{-- Status da câmera --}}
                        <div x-show="cameraActive" class="mt-2 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
                            <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span></span>
                            <span>Escaneando… aponte a câmera para o código de barras</span>
                        </div>

                        {{-- Erro da câmera --}}
                        <div x-show="cameraError" x-transition class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-semibold text-red-700 dark:text-red-400" x-text="cameraError"></p>
                                    <p class="text-[10px] text-red-500 dark:text-red-500 mt-1">Verifique se o navegador tem permissão para acessar a câmera. No iOS, use Safari.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Último código escaneado pela câmera --}}
                        <div x-show="lastCameraScan" x-transition class="mt-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
                            <i class="fas fa-circle-check text-emerald-500"></i>
                            <div class="flex-1">
                                <p class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-bold">Último código detectado</p>
                                <p class="text-sm font-black font-mono text-slate-800 dark:text-white" x-text="lastCameraScan"></p>
                            </div>
                        </div>
                    </div>

                    {{-- ======= ABA: IMAGEM (upload) ======= --}}
                    <div x-show="scanMode === 'image'" x-transition.opacity.duration.200ms>
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-image text-purple-500"></i>
                            <p class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Scanner por Imagem</p>
                        </div>

                        {{-- Drop zone / file input --}}
                        <div
                            @dragover.prevent="imageDragOver = true"
                            @dragleave.prevent="imageDragOver = false"
                            @drop.prevent="handleImageDrop($event)"
                            :class="imageDragOver ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-slate-300 dark:border-slate-600 hover:border-purple-400'"
                            class="relative border-2 border-dashed rounded-xl p-6 sm:p-8 transition-all duration-200 text-center cursor-pointer"
                            @click="$refs.imageFileInput.click()">
                            <input type="file" x-ref="imageFileInput" @change="handleImageSelect($event)" accept="image/*" class="hidden" />

                            {{-- Preview da imagem carregada --}}
                            <template x-if="imagePreview">
                                <div class="space-y-3">
                                    <img :src="imagePreview" alt="Imagem carregada" class="max-h-48 mx-auto rounded-lg shadow-md" />
                                    <div x-show="imageScanning" class="flex items-center justify-center gap-2 text-sm text-purple-600 dark:text-purple-400">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <span>Analisando imagem...</span>
                                    </div>
                                </div>
                            </template>

                            {{-- Placeholder --}}
                            <template x-if="!imagePreview">
                                <div class="space-y-3">
                                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center">
                                        <i class="fas fa-cloud-arrow-up text-3xl text-purple-500/60"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Arraste uma imagem ou clique para selecionar</p>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Foto do código de barras — JPG, PNG, WebP</p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Botões de ação --}}
                        <div x-show="imagePreview" x-transition class="flex gap-2 mt-3">
                            <button @click="scanImageBarcode()" :disabled="imageScanning" class="flex-1 px-4 py-2 rounded-xl bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white text-sm font-bold shadow-lg shadow-purple-500/30 transition-all hover:scale-105 disabled:opacity-50 disabled:hover:scale-100 flex items-center justify-center gap-2">
                                <i :class="imageScanning ? 'fas fa-spinner fa-spin' : 'fas fa-qrcode'"></i>
                                <span x-text="imageScanning ? 'Analisando...' : 'Buscar Código na Imagem'"></span>
                            </button>
                            <button @click="clearImage()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-1">
                                <i class="fas fa-times"></i>
                                Limpar
                            </button>
                        </div>

                        {{-- Resultado do scan por imagem --}}
                        <div x-show="imageResult" x-transition class="mt-3 p-3 rounded-xl border" :class="imageResultSuccess ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'">
                            <div class="flex items-center gap-2">
                                <i :class="imageResultSuccess ? 'fas fa-circle-check text-emerald-500' : 'fas fa-circle-xmark text-red-500'"></i>
                                <p class="text-sm font-semibold" :class="imageResultSuccess ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'" x-text="imageResult"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Mensagem de erro/aviso --}}
                    @if($searchMessage)
                    <div class="mt-3 flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium
                            {{ $searchStatus === 'error' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800' : '' }}
                            {{ $searchStatus === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800' : '' }}
                        ">
                        @if($searchStatus === 'error')
                        <i class="fas fa-circle-exclamation flex-shrink-0"></i>
                        <span>{{ $searchMessage }}</span>
                        @if($activeMode !== 'vincular')
                        <button wire:click="setMode('vincular')" class="ml-auto text-xs underline font-bold whitespace-nowrap text-cyan-600 dark:text-cyan-400">Vincular a produto →</button>
                        @endif
                        @elseif($searchStatus === 'warning')
                        <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                        <span>{{ $searchMessage }}</span>
                        @endif
                    </div>
                    @endif

                    {{-- Quantity input para inventario/venda --}}
                    @if(in_array($activeMode, ['inventario', 'venda']))
                    <div class="mt-3 flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                        <i class="fas fa-hashtag text-slate-400 text-sm"></i>
                        <span class="text-xs text-slate-500 font-medium whitespace-nowrap">Qtd. ao escanear:</span>
                        @if($activeMode === 'inventario')
                        <input type="number" wire:model="inventoryQtyInput" min="1" class="w-20 px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white text-center font-bold" />
                        @elseif($activeMode === 'venda')
                        <input type="number" wire:model="saleQtyInput" min="1" class="w-20 px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white text-center font-bold" />
                        @endif
                    </div>
                    @endif
                </div>

                {{-- ========== RESULTADO PRODUTO ENCONTRADO ========== --}}
                @if($foundProduct)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border-2 border-emerald-400/60 dark:border-emerald-600/60 shadow-lg shadow-emerald-500/10 p-4 sm:p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-circle-check text-emerald-500"></i>
                        <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Produto Encontrado</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        {{-- Imagem --}}
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-700 flex items-center justify-center border border-slate-200 dark:border-slate-600">
                            @if($foundProduct['image'])
                            <img src="{{ asset('storage/' . $foundProduct['image']) }}" alt="{{ $foundProduct['name'] }}" class="w-full h-full object-cover" />
                            @else
                            <i class="fas fa-image text-3xl text-slate-300 dark:text-slate-600"></i>
                            @endif
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 flex-wrap">
                                <h2 class="text-lg font-black text-slate-800 dark:text-white leading-tight">{{ $foundProduct['name'] }}</h2>
                                <a href="{{ route('products.edit', $foundProduct['id']) }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline whitespace-nowrap flex items-center gap-1">
                                    <i class="fas fa-pen-to-square"></i> Editar
                                </a>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <i class="fas fa-folder text-xs mr-1"></i>{{ $foundProduct['category_name'] }}
                                @if($foundProduct['brand']) · <i class="fas fa-building text-xs mr-1"></i>{{ $foundProduct['brand'] }} @endif
                                @if($foundProduct['model']) {{ $foundProduct['model'] }} @endif
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                                <div class="text-center p-2 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                                    <p class="text-[10px] text-slate-400 uppercase">Custo</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">R$ {{ number_format($foundProduct['price'], 2, ',', '.') }}</p>
                                </div>
                                <div class="text-center p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                                    <p class="text-[10px] text-emerald-600 uppercase">Venda</p>
                                    <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">R$ {{ number_format($foundProduct['price_sale'], 2, ',', '.') }}</p>
                                </div>
                                <div class="text-center p-2 rounded-lg {{ $foundProduct['stock_quantity'] > 0 ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                                    <p class="text-[10px] {{ $foundProduct['stock_quantity'] > 0 ? 'text-blue-600' : 'text-red-500' }} uppercase">Estoque</p>
                                    <p class="text-lg font-black {{ $foundProduct['stock_quantity'] > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-500' }}">{{ $foundProduct['stock_quantity'] }}</p>
                                </div>
                                <div class="text-center p-2 rounded-lg bg-slate-50 dark:bg-slate-900/50">
                                    <p class="text-[10px] text-slate-400 uppercase">Status</p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $foundProduct['status'] === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                        {{ $foundProduct['status'] === 'active' ? 'Ativo' : ucfirst($foundProduct['status'] ?? '—') }}
                                    </span>
                                </div>
                            </div>
                            @if($foundProduct['barcode'])
                            <p class="text-xs text-slate-400 mt-2 font-mono"><i class="fas fa-barcode mr-1"></i>{{ $foundProduct['barcode'] }}</p>
                            @else
                            <p class="text-xs text-amber-500 mt-2 font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i>Sem código de barras cadastrado</p>
                            @endif
                        </div>
                    </div>

                    {{-- ---- AÇÕES POR MODO ---- --}}

                    {{-- Preço --}}
                    @if($activeMode === 'preco')
                    <div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 border border-pink-200 dark:border-pink-800 text-center">
                        <p class="text-xs text-pink-600 dark:text-pink-400 font-semibold mb-1"><i class="fas fa-tag mr-1"></i>Preço de Venda</p>
                        <p class="text-4xl sm:text-5xl font-black text-pink-600 dark:text-pink-400">R$ {{ number_format($foundProduct['price_sale'], 2, ',', '.') }}</p>
                        @if($foundProduct['price'] != $foundProduct['price_sale'])
                        <p class="text-sm text-slate-500 mt-1">Custo: R$ {{ number_format($foundProduct['price'], 2, ',', '.') }}
                            — Margem: {{ $foundProduct['price'] > 0 ? number_format((($foundProduct['price_sale'] - $foundProduct['price']) / $foundProduct['price']) * 100, 1) : '∞' }}%
                        </p>
                        @endif
                    </div>
                    @endif

                    {{-- Estoque --}}
                    @if($activeMode === 'estoque')
                    <div class="mt-4 p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400 mb-3"><i class="fas fa-boxes-stacked mr-2"></i>Atualizar Estoque</p>
                        <div class="flex items-center gap-3 flex-wrap">
                            <select wire:model="stockOperation" class="px-3 py-2 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500/20">
                                <option value="add">Adicionar (+)</option>
                                <option value="remove">Remover (−)</option>
                                <option value="set">Definir valor</option>
                            </select>
                            @if($stockOperation === 'set')
                            <input type="number" wire:model="stockSetValue" min="0" class="w-24 px-3 py-2 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-sm text-center font-bold" />
                            @else
                            <input type="number" wire:model="stockDelta" min="1" class="w-24 px-3 py-2 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-sm text-center font-bold" />
                            @endif
                            <button wire:click="updateStock" wire:loading.attr="disabled" wire:target="updateStock"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm shadow-md transition-all hover:scale-105 flex items-center gap-2">
                                <i wire:loading.remove wire:target="updateStock" class="fas fa-check"></i>
                                <i wire:loading wire:target="updateStock" class="fas fa-spinner fa-spin"></i>
                                Salvar
                            </button>
                        </div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-2">Estoque atual: <strong>{{ $foundProduct['stock_quantity'] }} un.</strong></p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- ========== RESULTADO BUSCA ONLINE ========== --}}
                @if($onlineResult)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border-2 border-blue-400/60 dark:border-blue-600/60 shadow-lg shadow-blue-500/10 p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-globe text-blue-500"></i>
                            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Informações Online</p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ $onlineResult['source'] }}</span>
                        </div>
                        @if($foundProduct)
                        <button wire:click="applyOnlineData" wire:loading.attr="disabled" wire:target="applyOnlineData"
                            class="px-4 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-lg shadow-md transition-all hover:scale-105 flex items-center gap-1.5">
                            <i wire:loading.remove wire:target="applyOnlineData" class="fas fa-download"></i>
                            <i wire:loading wire:target="applyOnlineData" class="fas fa-spinner fa-spin"></i>
                            Aplicar ao Produto
                        </button>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        @if($onlineResult['image_url'])
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-700 border border-blue-200 dark:border-blue-800">
                            <img src="{{ $onlineResult['image_url'] }}" alt="Produto online" class="w-full h-full object-contain" />
                        </div>
                        @endif
                        <div class="flex-1 min-w-0 space-y-2">
                            @if($onlineResult['name'])
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold">Nome</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $onlineResult['name'] }}</p>
                            </div>
                            @endif
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @if($onlineResult['brand'])
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-building mr-1"></i>Marca</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $onlineResult['brand'] }}</p>
                                </div>
                                @endif
                                @if($onlineResult['categories'])
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-folder mr-1"></i>Categorias</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $onlineResult['categories'] }}</p>
                                </div>
                                @endif
                                @if($onlineResult['quantity'])
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-weight-hanging mr-1"></i>Quantidade</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $onlineResult['quantity'] }}</p>
                                </div>
                                @endif
                                @if($onlineResult['countries'])
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-globe mr-1"></i>País</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">{{ $onlineResult['countries'] }}</p>
                                </div>
                                @endif
                                @if($onlineResult['nutriscore'])
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-leaf mr-1"></i>Nutriscore</p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase {{ in_array(strtoupper($onlineResult['nutriscore']), ['A','B']) ? 'bg-emerald-100 text-emerald-700' : (in_array(strtoupper($onlineResult['nutriscore']), ['C']) ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ strtoupper($onlineResult['nutriscore']) }}</span>
                                </div>
                                @endif
                            </div>
                            @if($onlineResult['description'])
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-align-left mr-1"></i>Descrição</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $onlineResult['description'] }}</p>
                            </div>
                            @endif
                            @if($onlineResult['ingredients'])
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold"><i class="fas fa-flask mr-1"></i>Ingredientes</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3">{{ $onlineResult['ingredients'] }}</p>
                            </div>
                            @endif
                            <p class="text-[10px] text-slate-400 font-mono"><i class="fas fa-barcode mr-1"></i>{{ $onlineResult['barcode'] }}</p>
                        </div>
                    </div>

                    {{-- Se não achou local, oferecer ações --}}
                    @if(!$foundProduct)
                    <div class="mt-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-2"><i class="fas fa-lightbulb mr-1"></i>Este produto não está no seu cadastro. O que deseja fazer?</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('products.create') }}" class="px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-md hover:scale-105 transition-all flex items-center gap-1.5">
                                <i class="fas fa-plus"></i> Cadastrar Novo
                            </a>
                            <button wire:click="setMode('vincular')" class="px-3 py-1.5 text-xs font-bold text-cyan-700 bg-cyan-100 hover:bg-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-400 rounded-lg transition-all flex items-center gap-1.5">
                                <i class="fas fa-link"></i> Vincular a produto existente
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Erro da busca online --}}
                @if($onlineError)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm p-4">
                    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                        <i class="fas fa-globe text-slate-400"></i>
                        <span>{{ $onlineError }}</span>
                        <button wire:click="searchOnline('{{ $lastScannedBarcode }}')" class="ml-auto text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline whitespace-nowrap flex items-center gap-1">
                            <i class="fas fa-redo"></i> Tentar novamente
                        </button>
                    </div>
                </div>
                @endif

                {{-- ========== MODO VINCULAR: buscar e associar produto ========== --}}
                @if($activeMode === 'vincular')
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border-2 border-cyan-400/60 dark:border-cyan-600/60 shadow-lg shadow-cyan-500/10 p-4 sm:p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-link text-cyan-500 text-lg"></i>
                        <h3 class="font-black text-slate-800 dark:text-white">Vincular Código de Barras</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Escaneie o código e depois selecione o produto para vincular. Útil para produtos já cadastrados mas sem código de barras.</p>

                    @if($lastScannedBarcode)
                    <div class="mb-4 p-3 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 flex items-center gap-3">
                        <i class="fas fa-barcode text-cyan-600 dark:text-cyan-400 text-xl"></i>
                        <div>
                            <p class="text-[10px] text-cyan-600 dark:text-cyan-400 uppercase font-bold">Código escaneado</p>
                            <p class="text-lg font-black font-mono text-slate-800 dark:text-white">{{ $lastScannedBarcode }}</p>
                        </div>
                    </div>
                    @else
                    <div class="mb-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-center">
                        <i class="fas fa-arrow-up text-slate-400 text-xl mb-2"></i>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Escaneie um código de barras acima primeiro</p>
                    </div>
                    @endif

                    {{-- Buscar produto para vincular --}}
                    <div class="mb-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-sm"></i>
                            </div>
                            <input type="text"
                                wire:model.live.debounce.300ms="linkSearchTerm"
                                placeholder="Buscar produto por nome, código, marca..."
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all placeholder:text-slate-400" />
                        </div>
                    </div>

                    {{-- Lista de candidatos --}}
                    @if(count($linkCandidates) > 0)
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        @foreach($linkCandidates as $candidate)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-cyan-300 dark:hover:border-cyan-700 transition-all group">
                            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                @if($candidate['image'])
                                <img src="{{ asset('storage/' . $candidate['image']) }}" alt="" class="w-full h-full object-cover" />
                                @else
                                <i class="fas fa-box text-slate-400 text-sm"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $candidate['name'] }}</p>
                                <p class="text-[10px] text-slate-500 flex items-center gap-2 flex-wrap">
                                    <span><i class="fas fa-hashtag mr-0.5"></i>{{ $candidate['product_code'] ?? '—' }}</span>
                                    @if($candidate['barcode'])
                                    <span class="text-emerald-600"><i class="fas fa-barcode mr-0.5"></i>{{ $candidate['barcode'] }}</span>
                                    @else
                                    <span class="text-amber-500"><i class="fas fa-exclamation-triangle mr-0.5"></i>Sem barcode</span>
                                    @endif
                                    <span>{{ $candidate['category_name'] }}</span>
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs font-bold text-emerald-600">R$ {{ number_format($candidate['price_sale'], 2, ',', '.') }}</p>
                            </div>
                            @if($lastScannedBarcode)
                            <button wire:click="linkBarcodeToProduct({{ $candidate['id'] }})"
                                wire:confirm="Vincular o código {{ $lastScannedBarcode }} ao produto '{{ $candidate['name'] }}'?"
                                class="px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 rounded-lg shadow-md transition-all hover:scale-105 flex-shrink-0 flex items-center gap-1">
                                <i class="fas fa-link"></i> Vincular
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @elseif(!empty($linkSearchTerm))
                    <p class="text-sm text-slate-400 text-center py-4"><i class="fas fa-search mr-1"></i>Nenhum produto encontrado para "{{ $linkSearchTerm }}"</p>
                    @endif

                    {{-- Stats: produtos sem barcode --}}
                    @if(count($productsWithoutBarcode) > 0 && empty($linkSearchTerm))
                    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Produtos sem Código de Barras ({{ count($productsWithoutBarcode) }})</p>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            @foreach($productsWithoutBarcode as $noBarcodeProduct)
                            <div class="flex items-center gap-3 p-2 rounded-lg bg-amber-50/60 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30">
                                <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                    @if($noBarcodeProduct['image'])
                                    <img src="{{ asset('storage/' . $noBarcodeProduct['image']) }}" alt="" class="w-full h-full object-cover" />
                                    @else
                                    <i class="fas fa-box text-slate-400 text-xs"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">{{ $noBarcodeProduct['name'] }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $noBarcodeProduct['product_code'] ?? '—' }} · {{ $noBarcodeProduct['category_name'] }}</p>
                                </div>
                                @if($lastScannedBarcode)
                                <button wire:click="linkBarcodeToProduct({{ $noBarcodeProduct['id'] }})"
                                    wire:confirm="Vincular o código {{ $lastScannedBarcode }} ao produto '{{ $noBarcodeProduct['name'] }}'?"
                                    class="px-2 py-1 text-[10px] font-bold text-cyan-700 bg-cyan-100 hover:bg-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-400 rounded-md transition-all flex-shrink-0">
                                    <i class="fas fa-link"></i> Vincular
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- ========== PAINEL DE INVENTÁRIO ========== --}}
                @if($activeMode === 'inventario' && count($inventoryItems) > 0)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-orange-300/60 dark:border-orange-700/60 shadow-sm p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-orange-500"></i>
                            <div>
                                <h3 class="font-black text-slate-800 dark:text-white text-sm">Lista de Inventário</h3>
                                <p class="text-xs text-slate-500">{{ count($inventoryItems) }} produto(s)</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="clearInventory" wire:confirm="Limpar toda a lista de inventário?"
                                class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 rounded-lg transition-all flex items-center gap-1">
                                <i class="fas fa-trash-can"></i> Limpar
                            </button>
                            <button wire:click="applyInventory" wire:confirm="Aplicar as quantidades ao estoque?"
                                wire:loading.attr="disabled" wire:target="applyInventory"
                                class="px-4 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-amber-600 rounded-lg shadow-md transition-all hover:scale-105 flex items-center gap-1">
                                <i wire:loading.remove wire:target="applyInventory" class="fas fa-check"></i>
                                <i wire:loading wire:target="applyInventory" class="fas fa-spinner fa-spin"></i>
                                Aplicar ao Estoque
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($inventoryItems as $bcode => $item)
                        <div class="flex items-center gap-3 py-2 px-3 rounded-xl bg-orange-50/60 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/30">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $item['product']['name'] }}</p>
                                <p class="text-[10px] text-slate-500 font-mono"><i class="fas fa-barcode mr-1"></i>{{ $bcode }} · {{ $item['scanned_at'] }}</p>
                            </div>
                            <input type="number" min="0" value="{{ $item['qty'] }}"
                                wire:change="updateInventoryQty('{{ $bcode }}', $event.target.value)"
                                class="w-20 px-2 py-1 rounded-lg border border-orange-300 dark:border-orange-700 bg-white dark:bg-slate-800 text-sm text-center font-bold" />
                            <span class="text-xs text-slate-400">un.</span>
                            <button wire:click="removeFromInventory('{{ $bcode }}')" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-all">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ========== PAINEL DE VENDA ========== --}}
                @if($activeMode === 'venda' && count($saleItems) > 0)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-purple-300/60 dark:border-purple-700/60 shadow-sm p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-cart-shopping text-purple-500"></i>
                            <div>
                                <h3 class="font-black text-slate-800 dark:text-white text-sm">Lista de Venda</h3>
                                <p class="text-xs text-slate-500">{{ $this->saleItemsCount }} item(s) · <strong class="text-purple-600 dark:text-purple-400">R$ {{ number_format($this->saleTotal, 2, ',', '.') }}</strong></p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="clearSale" wire:confirm="Limpar a lista de venda?"
                                class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 rounded-lg transition-all flex items-center gap-1">
                                <i class="fas fa-trash-can"></i> Limpar
                            </button>
                            <a href="{{ route('sales.create') }}"
                                class="px-4 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-md transition-all hover:scale-105 flex items-center gap-1">
                                <i class="fas fa-arrow-right"></i> Ir para Venda
                            </a>
                        </div>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($saleItems as $bcode => $item)
                        <div class="flex items-center gap-3 py-2 px-3 rounded-xl bg-purple-50/60 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/30">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $item['product']['name'] }}</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">R$ {{ number_format($item['product']['price_sale'], 2, ',', '.') }} un.</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="updateSaleQty('{{ $bcode }}', {{ $item['qty'] - 1 }})" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all font-bold text-sm">−</button>
                                <span class="w-8 text-center font-bold text-sm text-slate-800 dark:text-white">{{ $item['qty'] }}</span>
                                <button wire:click="updateSaleQty('{{ $bcode }}', {{ $item['qty'] + 1 }})" class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all font-bold text-sm">+</button>
                            </div>
                            <p class="text-sm font-black text-purple-600 dark:text-purple-400 w-24 text-right">R$ {{ number_format($item['product']['price_sale'] * $item['qty'], 2, ',', '.') }}</p>
                            <button wire:click="removeFromSale('{{ $bcode }}')" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-all">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-purple-200 dark:border-purple-800 flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400 font-medium"><i class="fas fa-receipt mr-1"></i>Total</span>
                        <span class="text-2xl font-black text-purple-600 dark:text-purple-400">R$ {{ number_format($this->saleTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif

            </div>

            {{-- ========== COLUNA DIREITA (1/3): Guide + histórico ========== --}}
            <div class="space-y-5">

                {{-- Guia --}}
                <div class="bg-gradient-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 rounded-2xl border border-indigo-200/60 dark:border-indigo-700/60 p-4">
                    <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-2"><i class="fas fa-info-circle mr-1"></i>Como Usar</p>
                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                            <span>Selecione o <strong>modo</strong> desejado</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                            <span>Escolha como ler o código:</span>
                        </li>
                    </ul>
                    <div class="ml-7 mt-1 space-y-1 text-[10px] text-slate-500 dark:text-slate-500">
                        <p><i class="fas fa-keyboard text-slate-400 w-3.5 text-center mr-1"></i><strong>Digitar</strong> — Leitor USB ou teclado</p>
                        <p><i class="fas fa-camera text-emerald-500 w-3.5 text-center mr-1"></i><strong>Câmera</strong> — Usa a câmera do celular/tablet ao vivo</p>
                        <p><i class="fas fa-image text-purple-500 w-3.5 text-center mr-1"></i><strong>Imagem</strong> — Envie uma foto com o código</p>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400 mt-2">
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                            <span>Busca no <strong>banco de dados local</strong> e <strong>bases online</strong> automaticamente</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">4</span>
                            <span>Realize a <strong>ação</strong> conforme o modo</span>
                        </li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-indigo-200/60 dark:border-indigo-800/60">
                        <p class="text-xs text-indigo-500 dark:text-indigo-400 font-semibold mb-1.5">Modos disponíveis:</p>
                        <ul class="text-xs text-slate-500 dark:text-slate-500 space-y-1">
                            <li><i class="fas fa-search text-blue-500 w-4 text-center mr-1"></i><strong>Consultar</strong> — Dados completos</li>
                            <li><i class="fas fa-tag text-pink-500 w-4 text-center mr-1"></i><strong>Ver Preço</strong> — Preço em destaque + margem</li>
                            <li><i class="fas fa-boxes-stacked text-emerald-500 w-4 text-center mr-1"></i><strong>Estoque</strong> — Adicionar/remover/definir</li>
                            <li><i class="fas fa-clipboard-list text-orange-500 w-4 text-center mr-1"></i><strong>Inventário</strong> — Contagem em lote</li>
                            <li><i class="fas fa-cart-shopping text-purple-500 w-4 text-center mr-1"></i><strong>Venda</strong> — Montar lista de venda</li>
                            <li><i class="fas fa-link text-cyan-500 w-4 text-center mr-1"></i><strong>Vincular</strong> — Associar barcode a produto</li>
                        </ul>
                    </div>
                    <div class="mt-3 pt-3 border-t border-indigo-200/60 dark:border-indigo-800/60">
                        <p class="text-xs text-indigo-500 dark:text-indigo-400 font-semibold mb-1.5"><i class="fas fa-globe mr-1"></i>Busca Online</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-500 leading-relaxed">A busca online consulta automaticamente o <strong>Open Food Facts</strong> e <strong>UPC Item DB</strong> para encontrar informações do produto pela internet. Você pode aplicar os dados encontrados ao seu cadastro local.</p>
                    </div>
                </div>

                {{-- Histórico --}}
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1"><i class="fas fa-clock-rotate-left"></i> Histórico</p>
                        @if(count($scanHistory) > 0)
                        <button wire:click="clearHistory" class="text-xs text-red-400 hover:text-red-600 transition-colors font-semibold flex items-center gap-1"><i class="fas fa-trash-can"></i> Limpar</button>
                        @endif
                    </div>

                    @if(count($scanHistory) === 0)
                    <div class="text-center py-6 text-slate-400 dark:text-slate-600">
                        <i class="fas fa-list-check text-3xl mb-2 opacity-30"></i>
                        <p class="text-xs">Nenhuma leitura ainda</p>
                    </div>
                    @else
                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        @foreach($scanHistory as $entry)
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl {{ $entry['found'] ? 'bg-emerald-50/60 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30' : 'bg-red-50/60 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30' }}">
                            <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0 {{ $entry['found'] ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                @if($entry['found'])
                                <i class="fas fa-check text-emerald-600 dark:text-emerald-400 text-[10px]"></i>
                                @else
                                <i class="fas fa-times text-red-500 text-[10px]"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($entry['found'])
                                <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">{{ $entry['product']['name'] }}</p>
                                @else
                                <p class="text-xs font-semibold text-red-600 dark:text-red-400">Não encontrado</p>
                                @endif
                                <p class="text-[10px] text-slate-400 font-mono truncate">{{ $entry['code'] }}</p>
                            </div>
                            <span class="text-[10px] text-slate-400 flex-shrink-0">{{ $entry['scanned_at'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- CDN: html5-qrcode (câmera + imagem) --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        function barcodeScanner() {
            return {
                // Toast
                toastMsg: '',
                toastType: 'success',
                toastVisible: false,
                showToast(msg, type) {
                    this.toastMsg = msg;
                    this.toastType = type ?? 'success';
                    this.toastVisible = true;
                    setTimeout(() => this.toastVisible = false, 3500);
                },

                // Scan mode: 'manual' | 'camera' | 'image'
                scanMode: 'manual',

                // Camera scanner
                cameraActive: false,
                cameraError: null,
                lastCameraScan: null,
                cameraFacing: 'environment', // 'environment' = traseira, 'user' = frontal
                _html5QrCode: null,
                _scanCooldown: false,

                // Image scanner
                imagePreview: null,
                imageScanning: false,
                imageDragOver: false,
                imageResult: null,
                imageResultSuccess: false,
                _imageFile: null,

                setScanMode(mode) {
                    if (this.scanMode === 'camera' && mode !== 'camera') {
                        this.stopCamera();
                    }
                    this.scanMode = mode;
                    if (mode === 'manual') {
                        this.$nextTick(() => {
                            const input = document.getElementById('barcode-input');
                            if (input) input.focus();
                        });
                    }
                },

                // ------ CÂMERA ------

                async startCamera() {
                    this.cameraError = null;
                    this.lastCameraScan = null;

                    try {
                        if (!this._html5QrCode) {
                            this._html5QrCode = new Html5Qrcode('camera-scanner-viewport');
                        }

                        const config = {
                            fps: 10,
                            qrbox: (vw, vh) => {
                                const size = Math.min(vw, vh);
                                return {
                                    width: Math.floor(size * 0.8),
                                    height: Math.floor(size * 0.4)
                                };
                            },
                            aspectRatio: 1.0,
                            formatsToSupport: [
                                Html5QrcodeSupportedFormats.EAN_13,
                                Html5QrcodeSupportedFormats.EAN_8,
                                Html5QrcodeSupportedFormats.UPC_A,
                                Html5QrcodeSupportedFormats.UPC_E,
                                Html5QrcodeSupportedFormats.CODE_128,
                                Html5QrcodeSupportedFormats.CODE_39,
                                Html5QrcodeSupportedFormats.CODE_93,
                                Html5QrcodeSupportedFormats.ITF,
                                Html5QrcodeSupportedFormats.QR_CODE,
                            ],
                        };

                        await this._html5QrCode.start({
                                facingMode: this.cameraFacing
                            },
                            config,
                            (decodedText) => this.onCameraCodeDetected(decodedText),
                            () => {} // ignore error frames
                        );

                        this.cameraActive = true;
                    } catch (err) {
                        console.error('Camera error:', err);
                        let msg = 'Não foi possível acessar a câmera.';
                        if (typeof err === 'string') {
                            if (err.includes('NotAllowedError') || err.includes('Permission')) {
                                msg = 'Permissão negada. Permita o acesso à câmera nas configurações do navegador.';
                            } else if (err.includes('NotFoundError') || err.includes('Requested device not found')) {
                                msg = 'Nenhuma câmera encontrada neste dispositivo.';
                            } else if (err.includes('NotReadableError') || err.includes('Could not start')) {
                                msg = 'A câmera está sendo usada por outro aplicativo.';
                            }
                        }
                        this.cameraError = msg;
                        this.cameraActive = false;
                    }
                },

                async stopCamera() {
                    if (this._html5QrCode && this.cameraActive) {
                        try {
                            await this._html5QrCode.stop();
                        } catch (e) {
                            // ignore
                        }
                    }
                    this.cameraActive = false;
                },

                async toggleCameraFacing() {
                    this.cameraFacing = this.cameraFacing === 'environment' ? 'user' : 'environment';
                    if (this.cameraActive) {
                        await this.stopCamera();
                        await this.startCamera();
                    }
                },

                onCameraCodeDetected(code) {
                    if (this._scanCooldown) return;
                    this._scanCooldown = true;
                    setTimeout(() => {
                        this._scanCooldown = false;
                    }, 1500);

                    this.lastCameraScan = code;

                    // Feedback sonoro
                    this.playBeep();

                    // Envia para o Livewire
                    this.$wire.set('barcodeInput', code).then(() => {
                        this.$wire.searchBarcode();
                    });
                },

                // ------ IMAGEM ------

                handleImageSelect(event) {
                    const file = event.target.files[0];
                    if (file) this.loadImageFile(file);
                },

                handleImageDrop(event) {
                    this.imageDragOver = false;
                    const file = event.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        this.loadImageFile(file);
                    }
                },

                loadImageFile(file) {
                    this._imageFile = file;
                    this.imageResult = null;
                    this.imageResultSuccess = false;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    // Auto-scan após carregar
                    this.$nextTick(() => {
                        setTimeout(() => this.scanImageBarcode(), 300);
                    });
                },

                async scanImageBarcode() {
                    if (!this._imageFile || this.imageScanning) return;

                    this.imageScanning = true;
                    this.imageResult = null;

                    try {
                        const html5QrCode = new Html5Qrcode('image-scanner-temp-' + Date.now(), false);

                        // Cria container temporário invisível
                        const tempDiv = document.createElement('div');
                        tempDiv.id = html5QrCode.elementId || 'img-scan-tmp';
                        tempDiv.style.display = 'none';
                        document.body.appendChild(tempDiv);

                        const result = await Html5Qrcode.scanFileV2(this._imageFile, true);

                        if (result && result.decodedText) {
                            this.imageResult = 'Código encontrado: ' + result.decodedText;
                            this.imageResultSuccess = true;

                            this.playBeep();

                            // Envia para Livewire
                            this.$wire.set('barcodeInput', result.decodedText).then(() => {
                                this.$wire.searchBarcode();
                            });
                        } else {
                            this.imageResult = 'Nenhum código de barras detectado na imagem.';
                            this.imageResultSuccess = false;
                        }

                        tempDiv.remove();
                    } catch (err) {
                        console.warn('Image scan error:', err);
                        this.imageResult = 'Nenhum código de barras detectado. Tente outra imagem com melhor qualidade ou iluminação.';
                        this.imageResultSuccess = false;
                    }

                    this.imageScanning = false;
                },

                clearImage() {
                    this.imagePreview = null;
                    this._imageFile = null;
                    this.imageResult = null;
                    this.imageResultSuccess = false;
                    if (this.$refs.imageFileInput) {
                        this.$refs.imageFileInput.value = '';
                    }
                },

                // ------ HELPERS ------

                playBeep() {
                    try {
                        const ctx = new(window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 1200;
                        osc.type = 'sine';
                        gain.gain.value = 0.3;
                        osc.start();
                        osc.stop(ctx.currentTime + 0.15);
                    } catch (e) {
                        // Audio not supported
                    }
                },

                // Cleanup ao sair da página
                destroy() {
                    this.stopCamera();
                },
            };
        }

        // Foco automático no input
        document.addEventListener('livewire:updated', () => {
            const input = document.getElementById('barcode-input');
            if (input && document.querySelector('[x-data]')?.__x?.$data?.scanMode === 'manual') {
                input.focus();
            }
        });
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('barcode-input');
            if (input) input.focus();
        });
    </script>
</div>