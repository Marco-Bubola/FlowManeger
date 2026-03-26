<div
    x-data="barcodeScanner()"
    x-on:show-toast.window="showToast($event.detail.message, $event.detail.type)"
    class="w-full app-viewport-fit">

    {{-- ========== TOAST NOTIFICATIONS ========== --}}
    <div x-show="toastVisible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-3 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-3 scale-95" class="fixed top-4 right-4 z-[60] max-w-sm" style="display:none">
        <div :class="{
            'bg-emerald-500 shadow-emerald-500/30': toastType === 'success',
            'bg-red-500 shadow-red-500/30': toastType === 'error',
            'bg-amber-500 shadow-amber-500/30': toastType === 'warning',
            'bg-blue-500 shadow-blue-500/30': toastType === 'info',
        }" class="text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 backdrop-blur-sm">
            <template x-if="toastType === 'success'"><i class="fas fa-circle-check text-lg"></i></template>
            <template x-if="toastType === 'error'"><i class="fas fa-circle-xmark text-lg"></i></template>
            <template x-if="toastType === 'warning'"><i class="fas fa-triangle-exclamation text-lg"></i></template>
            <template x-if="toastType === 'info'"><i class="fas fa-circle-info text-lg"></i></template>
            <span x-text="toastMsg" class="text-sm font-semibold"></span>
        </div>
    </div>

    {{-- ========== FULLSCREEN CAMERA MODAL ========== --}}
    <div x-show="scanMode === 'camera'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 bg-black" style="display:none">
        {{-- Camera viewport --}}
        <div id="camera-scanner-viewport" class="w-full h-full"></div>

        {{-- Scan overlay (planta baixa style) --}}
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
            <div class="relative" style="width: 80%; max-width: 400px; height: 200px;">
                <div class="absolute top-0 left-0 w-8 h-8 border-t-[3px] border-l-[3px] border-indigo-400 rounded-tl-lg"></div>
                <div class="absolute top-0 right-0 w-8 h-8 border-t-[3px] border-r-[3px] border-indigo-400 rounded-tr-lg"></div>
                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-[3px] border-l-[3px] border-purple-400 rounded-bl-lg"></div>
                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-[3px] border-r-[3px] border-purple-400 rounded-br-lg"></div>
                <div class="absolute left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-indigo-400 to-transparent animate-scan-line"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-6 h-6">
                    <div class="absolute top-1/2 left-0 right-0 h-px bg-indigo-400/50"></div>
                    <div class="absolute left-1/2 top-0 bottom-0 w-px bg-indigo-400/50"></div>
                </div>
            </div>
        </div>

        {{-- Top bar --}}
        <div class="absolute top-0 left-0 right-0 p-4 bg-gradient-to-b from-black/70 to-transparent safe-area-top">
            <div class="flex items-center justify-between">
                <button @click="setScanMode('manual')" class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/25 transition-all active:scale-95">
                    <i class="fas fa-arrow-left text-lg"></i>
                </button>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md">
                    <span class="relative flex h-2.5 w-2.5" x-show="cameraActive">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-white/90 text-sm font-semibold" x-text="cameraActive ? 'Escaneando...' : 'Iniciando...'"></span>
                </div>
                <button @click="toggleCameraFacing()" class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/25 transition-all active:scale-95">
                    <i class="fas fa-camera-rotate text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 pb-8 bg-gradient-to-t from-black/80 to-transparent safe-area-bottom">
            <div x-show="lastCameraScan" x-transition class="mb-4 mx-auto max-w-sm">
                <div class="bg-emerald-500/90 backdrop-blur-md rounded-2xl px-5 py-3 flex items-center gap-3">
                    <i class="fas fa-circle-check text-white text-lg"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-emerald-100 uppercase font-bold tracking-wider">Detectado</p>
                        <p class="text-white font-black font-mono text-base truncate" x-text="lastCameraScan"></p>
                    </div>
                </div>
            </div>
            <div x-show="cameraError" x-transition class="mb-4 mx-auto max-w-sm">
                <div class="bg-red-500/90 backdrop-blur-md rounded-2xl px-5 py-3 flex items-center gap-3">
                    <i class="fas fa-circle-exclamation text-white text-lg"></i>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-white" x-text="cameraError"></p>
                        <p class="text-[10px] text-red-100 mt-0.5">No iOS use Safari. Verifique permissões.</p>
                    </div>
                    <button @click="startCamera()" class="px-3 py-1.5 bg-white/20 rounded-lg text-white text-xs font-bold hover:bg-white/30 transition-all">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            </div>
            <p class="text-center text-white/50 text-xs" x-show="!lastCameraScan && !cameraError">
                <i class="fas fa-barcode mr-1"></i> Posicione o código de barras dentro da área
            </p>
        </div>
    </div>

    {{-- ========== HEADER ========== --}}
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
            @php $stats = $this->stats; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2">
                <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/40 dark:border-slate-700/40">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total</p>
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

    {{-- ========== MAIN CONTENT ========== --}}
    <div class="w-full px-2 sm:px-3 lg:px-4 xl:px-6 pb-10">

        <div class="w-full space-y-4">

            {{-- ═══ ZONA A — MODO DE OPERAÇÃO ═══ --}}
            <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg overflow-hidden">
                <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.028;"></div>
                <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-slate-200/50 dark:border-slate-700/50 rounded-br-xl" style="background:linear-gradient(to right,rgba(99,102,241,.07),transparent)">
                    <span class="text-[9px] font-black tracking-[0.3em] text-indigo-500/50 dark:text-indigo-400/40 uppercase">ZONA A · MODO DE OPERAÇÃO</span>
                </div>
                <div class="absolute top-2 right-2 w-5 h-5 border-t-2 border-r-2 border-slate-300/30 dark:border-slate-600/30 rounded-tr pointer-events-none"></div>
                <div class="absolute bottom-2 left-2 w-5 h-5 border-b-2 border-l-2 border-slate-300/30 dark:border-slate-600/30 rounded-bl pointer-events-none"></div>

                <div class="relative p-4 sm:p-5 pt-9">
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 sm:gap-3">
                        @foreach([
                            ['mode' => 'consulta',  'icon' => 'fas fa-search',         'label' => 'Consultar',  'color' => 'blue'],
                            ['mode' => 'preco',      'icon' => 'fas fa-tag',             'label' => 'Ver Preço',  'color' => 'pink'],
                            ['mode' => 'estoque',    'icon' => 'fas fa-boxes-stacked',   'label' => 'Estoque',    'color' => 'emerald'],
                            ['mode' => 'inventario', 'icon' => 'fas fa-clipboard-list',  'label' => 'Inventário', 'color' => 'orange'],
                            ['mode' => 'venda',      'icon' => 'fas fa-cart-shopping',   'label' => 'Venda',      'color' => 'purple'],
                            ['mode' => 'vincular',   'icon' => 'fas fa-link',            'label' => 'Vincular',   'color' => 'cyan'],
                        ] as $m)
                        <button wire:click="setMode('{{ $m['mode'] }}')"
                            class="group relative overflow-hidden rounded-2xl border-2 p-3 sm:p-4 text-center transition-all duration-300
                                {{ $activeMode === $m['mode']
                                    ? 'border-'.$m['color'].'-400 dark:border-'.$m['color'].'-600 shadow-lg shadow-'.$m['color'].'-500/20 scale-[1.03]'
                                    : 'border-slate-200/80 dark:border-slate-700/80 hover:border-'.$m['color'].'-300 dark:hover:border-'.$m['color'].'-700 hover:scale-[1.02]' }}">
                            @if($activeMode === $m['mode'])
                            <div class="absolute inset-0 bg-gradient-to-br from-{{ $m['color'] }}-500/12 to-{{ $m['color'] }}-400/5 dark:from-{{ $m['color'] }}-500/18 dark:to-{{ $m['color'] }}-400/5"></div>
                            <div class="absolute top-2 right-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $m['color'] }}-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $m['color'] }}-500"></span>
                                </span>
                            </div>
                            @endif
                            <div class="relative w-10 h-10 sm:w-12 sm:h-12 mx-auto rounded-xl flex items-center justify-center mb-2 transition-all duration-300
                                {{ $activeMode === $m['mode']
                                    ? 'bg-gradient-to-br from-'.$m['color'].'-500 to-'.$m['color'].'-600 text-white shadow-lg shadow-'.$m['color'].'-500/30'
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 group-hover:bg-'.$m['color'].'-50 dark:group-hover:bg-'.$m['color'].'-900/20 group-hover:text-'.$m['color'].'-500' }}">
                                <i class="{{ $m['icon'] }} text-base sm:text-lg"></i>
                            </div>
                            <p class="relative text-[10px] sm:text-xs font-bold leading-tight
                                {{ $activeMode === $m['mode']
                                    ? 'text-'.$m['color'].'-700 dark:text-'.$m['color'].'-300'
                                    : 'text-slate-500 dark:text-slate-400' }}">{{ $m['label'] }}</p>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ═══ MAIN GRID ═══ --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- ─────────── MAIN COLUMN ─────────── --}}
                <div class="lg:col-span-2 space-y-4">

                {{-- ═══ ZONA B — SCANNER ═══ --}}
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.028;"></div>
                    <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-slate-200/50 dark:border-slate-700/50 rounded-br-xl" style="background:linear-gradient(to right,rgba(99,102,241,.07),transparent)">
                        <span class="text-[9px] font-black tracking-[0.3em] text-indigo-500/50 dark:text-indigo-400/40 uppercase">ZONA B · SCANNER DE ENTRADA</span>
                    </div>
                    <div class="absolute top-2 right-2 w-5 h-5 border-t-2 border-r-2 border-slate-300/30 dark:border-slate-600/30 rounded-tr pointer-events-none"></div>
                    <div class="absolute bottom-2 left-2 w-5 h-5 border-b-2 border-l-2 border-slate-300/30 dark:border-slate-600/30 rounded-bl pointer-events-none"></div>
                    <div class="relative p-4 sm:p-6 pt-9">

                        {{-- Tab switcher --}}
                        <div class="flex items-center gap-1 mb-5 p-1 bg-slate-100/80 dark:bg-slate-800/60 rounded-2xl border border-slate-200/50 dark:border-slate-700/50">
                            <button @click="setScanMode('manual')" :class="scanMode === 'manual' ? 'bg-white dark:bg-slate-700 shadow-md text-indigo-600 dark:text-indigo-300 scale-[1.02]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200">
                                <i class="fas fa-keyboard"></i>
                                <span>Digitar</span>
                            </button>
                            <button @click="setScanMode('camera')" :class="scanMode === 'camera' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30 scale-[1.02]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200">
                                <i class="fas fa-camera"></i>
                                <span>Câmera</span>
                            </button>
                            <button @click="setScanMode('image')" :class="scanMode === 'image' ? 'bg-white dark:bg-slate-700 shadow-md text-purple-600 dark:text-purple-300 scale-[1.02]' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200">
                                <i class="fas fa-image"></i>
                                <span>Imagem</span>
                            </button>
                        </div>

                        {{-- MODE: MANUAL --}}
                        <div x-show="scanMode === 'manual'" x-transition.opacity.duration.200ms>
                            <form wire:submit.prevent="searchBarcode" class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1 relative group">
                                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                        <i class="fas fa-barcode text-slate-300 dark:text-slate-600 group-focus-within:text-indigo-500 transition-colors text-xl"></i>
                                    </div>
                                    <input id="barcode-input" type="text" wire:model="barcodeInput" placeholder="Ex: 7891234567890" autocomplete="off" autofocus class="w-full pl-12 pr-4 py-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-xl font-mono tracking-widest focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-300 dark:placeholder:text-slate-600 shadow-sm" />
                                </div>
                                <button type="submit" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 text-white font-bold shadow-xl shadow-indigo-500/30 transition-all hover:shadow-2xl hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 min-w-[130px]" wire:loading.attr="disabled" wire:target="searchBarcode">
                                    <i wire:loading.remove wire:target="searchBarcode" class="fas fa-search"></i>
                                    <i wire:loading wire:target="searchBarcode" class="fas fa-spinner fa-spin"></i>
                                    <span wire:loading.remove wire:target="searchBarcode">Buscar</span>
                                    <span wire:loading wire:target="searchBarcode" class="text-sm">Buscando...</span>
                                </button>
                            </form>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="flex-1 h-px bg-gradient-to-r from-transparent via-slate-200 dark:via-slate-700 to-transparent"></div>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">ou use leitor USB</span>
                                <div class="flex-1 h-px bg-gradient-to-r from-transparent via-slate-200 dark:via-slate-700 to-transparent"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                <i class="fas fa-usb text-indigo-400/50"></i>
                                <span>Conecte o leitor USB e leia diretamente no campo</span>
                            </div>
                        </div>

                        {{-- MODE: CAMERA (loading state — fullscreen opens automatically) --}}
                        <div x-show="scanMode === 'camera'" x-transition.opacity.duration.200ms class="flex flex-col items-center justify-center py-14 gap-4">
                            <div class="relative w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center">
                                <i class="fas fa-camera text-3xl text-indigo-500/80 animate-pulse"></i>
                                <div class="absolute inset-0 rounded-3xl border-2 border-dashed border-indigo-300/40 dark:border-indigo-600/40 animate-spin" style="animation-duration:8s"></div>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Câmera ativa em tela cheia</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Pressione o botão voltar para fechar</p>
                            </div>
                        </div>

                        {{-- MODE: IMAGE --}}
                        <div x-show="scanMode === 'image'" x-transition.opacity.duration.200ms>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                <p class="text-[10px] font-bold text-purple-500/70 dark:text-purple-400/70 uppercase tracking-[0.15em]">SCANNER POR IMAGEM</p>
                            </div>

                            <div @dragover.prevent="imageDragOver = true" @dragleave.prevent="imageDragOver = false" @drop.prevent="handleImageDrop($event)" :class="imageDragOver ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-900/20 scale-[1.01]' : 'border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-600'" class="relative border-2 border-dashed rounded-2xl p-6 sm:p-8 transition-all duration-300 text-center cursor-pointer" @click="$refs.imageFileInput.click()">
                                <input type="file" x-ref="imageFileInput" @change="handleImageSelect($event)" accept="image/*" class="hidden" />

                                <template x-if="imagePreview">
                                    <div class="space-y-3">
                                        <img :src="imagePreview" alt="Imagem" class="max-h-48 mx-auto rounded-xl shadow-lg border border-slate-200 dark:border-slate-700" />
                                        <div x-show="imageScanning" class="flex items-center justify-center gap-2 text-sm text-purple-600 dark:text-purple-400 font-semibold">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <span>Analisando...</span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!imagePreview">
                                    <div class="space-y-3">
                                        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-purple-500/15 to-pink-500/15 flex items-center justify-center">
                                            <i class="fas fa-cloud-arrow-up text-3xl text-purple-400/70"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Arraste ou clique para selecionar</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">JPG, PNG, WebP</p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="imagePreview" x-transition class="flex gap-2 mt-3">
                                <button @click="scanImageBarcode()" :disabled="imageScanning" class="flex-1 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-bold shadow-lg shadow-purple-500/25 transition-all hover:scale-[1.02] disabled:opacity-50 disabled:hover:scale-100 flex items-center justify-center gap-2">
                                    <i :class="imageScanning ? 'fas fa-spinner fa-spin' : 'fas fa-qrcode'"></i>
                                    <span x-text="imageScanning ? 'Analisando...' : 'Buscar Código'"></span>
                                </button>
                                <button @click="clearImage()" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all flex items-center gap-1.5">
                                    <i class="fas fa-xmark"></i> Limpar
                                </button>
                            </div>

                            <div x-show="imageResult" x-transition class="mt-3 p-3 rounded-xl border" :class="imageResultSuccess ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'">
                                <div class="flex items-center gap-2">
                                    <i :class="imageResultSuccess ? 'fas fa-circle-check text-emerald-500' : 'fas fa-circle-xmark text-red-500'"></i>
                                    <p class="text-sm font-semibold" :class="imageResultSuccess ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'" x-text="imageResult"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Search message --}}
                        @if($searchMessage)
                        <div class="mt-3 flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium
                            {{ $searchStatus === 'error' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800' : '' }}
                            {{ $searchStatus === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800' : '' }}">
                            @if($searchStatus === 'error')
                            <i class="fas fa-circle-exclamation flex-shrink-0"></i>
                            <span>{{ $searchMessage }}</span>
                            @if($activeMode !== 'vincular')
                            <button wire:click="setMode('vincular')" class="ml-auto text-xs underline font-bold whitespace-nowrap text-cyan-600 dark:text-cyan-400">Vincular →</button>
                            @endif
                            @elseif($searchStatus === 'warning')
                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                            <span>{{ $searchMessage }}</span>
                            @endif
                        </div>
                        @endif

                        {{-- Qty input for inventory/sale --}}
                        @if(in_array($activeMode, ['inventario', 'venda']))
                        <div class="mt-4 flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                            <i class="fas fa-hashtag text-slate-400"></i>
                            <span class="text-xs text-slate-500 font-semibold whitespace-nowrap">Quantidade:</span>
                            @if($activeMode === 'inventario')
                            <input type="number" wire:model="inventoryQtyInput" min="1" class="w-24 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm text-center font-black" />
                            @else
                            <input type="number" wire:model="saleQtyInput" min="1" class="w-24 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm text-center font-black" />
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══ ZONA D — PRODUTO ENCONTRADO ═══ --}}
                @if($foundProduct)
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border-2 border-emerald-400/50 dark:border-emerald-600/50 shadow-2xl shadow-emerald-500/10 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(16,185,129,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(16,185,129,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                    {{-- Hero banner --}}
                    <div class="relative h-28 sm:h-36 overflow-hidden" style="background:linear-gradient(135deg,rgba(16,185,129,.15) 0%,rgba(20,184,166,.08) 50%,transparent 100%)">
                        <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full" style="background:radial-gradient(circle,rgba(16,185,129,.12) 0%,transparent 70%)"></div>
                        <div class="absolute -bottom-8 right-32 w-32 h-32 rounded-full" style="background:radial-gradient(circle,rgba(20,184,166,.08) 0%,transparent 70%)"></div>
                        <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-emerald-300/30 dark:border-emerald-700/30 rounded-br-xl bg-emerald-500/10">
                            <span class="text-[9px] font-black tracking-[0.3em] text-emerald-600/60 dark:text-emerald-400/50 uppercase">ZONA D · PRODUTO ENCONTRADO</span>
                        </div>
                        <div class="absolute bottom-4 left-5 right-24 sm:right-32">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                                <span class="text-[9px] font-black text-emerald-600/80 dark:text-emerald-400/70 uppercase tracking-widest">Encontrado</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white leading-tight truncate">{{ $foundProduct['name'] }}</h2>
                        </div>
                        <div class="absolute right-4 top-4 w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-white/80 dark:border-slate-700 shadow-2xl shadow-black/20 bg-white dark:bg-slate-800 flex items-center justify-center">
                            @if($foundProduct['image'])
                            <img src="{{ asset('storage/' . $foundProduct['image']) }}" alt="{{ $foundProduct['name'] }}" class="w-full h-full object-cover" />
                            @else
                            <i class="fas fa-image text-3xl text-slate-300 dark:text-slate-600"></i>
                            @endif
                        </div>
                    </div>
                    <div class="relative p-4 sm:p-5">
                        {{-- Meta badges --}}
                        <div class="flex items-center gap-2 flex-wrap mb-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                <i class="fas fa-folder text-xs text-slate-400"></i>{{ $foundProduct['category_name'] }}
                            </span>
                            @if($foundProduct['brand'])
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                <i class="fas fa-building text-xs text-slate-400"></i>{{ $foundProduct['brand'] }}
                            </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold {{ $foundProduct['status'] === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                                <i class="fas fa-circle text-[8px]"></i>{{ $foundProduct['status'] === 'active' ? 'Ativo' : ucfirst($foundProduct['status'] ?? '—') }}
                            </span>
                            <a href="{{ route('products.edit', $foundProduct['id']) }}" class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-all hover:scale-105">
                                <i class="fas fa-pen-to-square text-xs"></i> Editar
                            </a>
                        </div>
                        {{-- Price + stock grid (modern cards) --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-4">
                            <div class="rounded-2xl p-3.5 text-center bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/40">
                                <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1">Custo</p>
                                <p class="text-sm font-black text-slate-700 dark:text-slate-300">R$ {{ number_format($foundProduct['price'], 2, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl p-3.5 text-center shadow-lg shadow-emerald-500/25 relative overflow-hidden" style="background:linear-gradient(135deg,#10b981,#0d9488)">
                                <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,white 1px,transparent 1px);background-size:16px 16px"></div>
                                <p class="relative text-[9px] text-emerald-100 uppercase font-black tracking-wider mb-1">Venda</p>
                                <p class="relative text-xl font-black text-white">R$ {{ number_format($foundProduct['price_sale'], 2, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl p-3.5 text-center border {{ $foundProduct['stock_quantity'] > 0 ? 'bg-blue-50/80 dark:bg-blue-900/15 border-blue-200/60 dark:border-blue-800/40' : 'bg-red-50 dark:bg-red-900/20 border-red-200/60 dark:border-red-800/40' }}">
                                <p class="text-[9px] {{ $foundProduct['stock_quantity'] > 0 ? 'text-blue-500' : 'text-red-500' }} uppercase font-black tracking-wider mb-1">Estoque</p>
                                <p class="text-xl font-black {{ $foundProduct['stock_quantity'] > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-500' }}">{{ $foundProduct['stock_quantity'] }}</p>
                            </div>
                            @if($foundProduct['barcode'])
                            <div class="rounded-2xl p-3.5 text-center bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/40">
                                <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1">EAN</p>
                                <p class="text-xs font-black text-slate-600 dark:text-slate-400 font-mono">{{ Str::limit($foundProduct['barcode'], 13) }}</p>
                            </div>
                            @else
                            <div class="rounded-2xl p-3.5 text-center bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40">
                                <i class="fas fa-exclamation-triangle text-amber-500 text-sm mb-1"></i>
                                <p class="text-[9px] text-amber-600 font-black">Sem código</p>
                            </div>
                            @endif
                        </div>

                        @if($activeMode === 'preco')
                        <div class="relative p-6 rounded-2xl overflow-hidden text-center shadow-xl shadow-pink-500/20" style="background:linear-gradient(135deg,#ec4899,#f43f5e,#ef4444)">
                            <div class="absolute inset-0 opacity-15" style="background-image:radial-gradient(circle,white 1px,transparent 1px);background-size:24px 24px"></div>
                            <p class="relative text-pink-100 text-xs font-black uppercase tracking-widest mb-2"><i class="fas fa-tag mr-1.5"></i>Preço de Venda</p>
                            <p class="relative text-5xl sm:text-6xl font-black text-white tracking-tight">R$ {{ number_format($foundProduct['price_sale'], 2, ',', '.') }}</p>
                            @if($foundProduct['price'] != $foundProduct['price_sale'])
                            <p class="relative text-sm text-pink-100/80 mt-2">Custo: R$ {{ number_format($foundProduct['price'], 2, ',', '.') }} &nbsp;·&nbsp; Margem: {{ $foundProduct['price'] > 0 ? number_format((($foundProduct['price_sale'] - $foundProduct['price']) / $foundProduct['price']) * 100, 1) : '∞' }}%</p>
                            @endif
                        </div>
                        @endif

                        @if($activeMode === 'estoque')
                        <div class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-900/20 border border-emerald-200/60 dark:border-emerald-800/40">
                            <p class="text-xs font-black text-emerald-700 dark:text-emerald-400 mb-3 flex items-center gap-2"><i class="fas fa-boxes-stacked"></i>Atualizar Estoque</p>
                            <div class="flex items-center gap-3 flex-wrap">
                                <select wire:model="stockOperation" class="px-3 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500/20">
                                    <option value="add">Adicionar (+)</option>
                                    <option value="remove">Remover (−)</option>
                                    <option value="set">Definir valor</option>
                                </select>
                                @if($stockOperation === 'set')
                                <input type="number" wire:model="stockSetValue" min="0" class="w-24 px-3 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-sm text-center font-black" />
                                @else
                                <input type="number" wire:model="stockDelta" min="1" class="w-24 px-3 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-800 text-sm text-center font-black" />
                                @endif
                                <button wire:click="updateStock" wire:loading.attr="disabled" wire:target="updateStock" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/25 transition-all hover:scale-105 flex items-center gap-2">
                                    <i wire:loading.remove wire:target="updateStock" class="fas fa-check"></i>
                                    <i wire:loading wire:target="updateStock" class="fas fa-spinner fa-spin"></i>
                                    Salvar
                                </button>
                            </div>
                            <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-2.5 font-semibold"><i class="fas fa-info-circle mr-1"></i>Estoque atual: <strong class="font-black">{{ $foundProduct['stock_quantity'] }} un.</strong></p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ═══ ZONA E — RESULTADO ONLINE ═══ --}}
                @if($onlineResult)
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border-2 border-blue-400/40 dark:border-blue-600/40 shadow-xl shadow-blue-500/8 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(59,130,246,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                    <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-blue-300/30 dark:border-blue-700/30 rounded-br-xl" style="background:linear-gradient(to right,rgba(59,130,246,.07),transparent)">
                        <span class="text-[9px] font-black tracking-[0.3em] text-blue-500/60 dark:text-blue-400/50 uppercase">ZONA E · DADOS ONLINE</span>
                    </div>
                    <div class="relative p-4 sm:p-5 pt-9">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                                    <i class="fas fa-globe text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800 dark:text-white">Informações Online</p>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ $onlineResult['source'] }}</span>
                                </div>
                            </div>
                            @if($foundProduct)
                            <button wire:click="applyOnlineData" wire:loading.attr="disabled" wire:target="applyOnlineData" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-xl shadow-lg shadow-blue-500/25 transition-all hover:scale-105 flex items-center gap-1.5">
                                <i wire:loading.remove wire:target="applyOnlineData" class="fas fa-download"></i>
                                <i wire:loading wire:target="applyOnlineData" class="fas fa-spinner fa-spin"></i>
                                Aplicar
                            </button>
                            @endif
                        </div>
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            @if($onlineResult['image_url'])
                            <div class="w-24 h-24 rounded-2xl overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-700 border-2 border-blue-200 dark:border-blue-800 shadow-lg">
                                <img src="{{ $onlineResult['image_url'] }}" alt="Produto" class="w-full h-full object-contain" />
                            </div>
                            @endif
                            <div class="flex-1 min-w-0 space-y-3">
                                @if($onlineResult['name'])
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">Nome</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $onlineResult['name'] }}</p>
                                </div>
                                @endif
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    @if($onlineResult['brand'])
                                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-200/50 dark:border-slate-700/40">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-0.5"><i class="fas fa-building mr-1"></i>Marca</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $onlineResult['brand'] }}</p>
                                    </div>
                                    @endif
                                    @if($onlineResult['categories'])
                                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-200/50 dark:border-slate-700/40">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-0.5"><i class="fas fa-folder mr-1"></i>Categoria</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $onlineResult['categories'] }}</p>
                                    </div>
                                    @endif
                                    @if($onlineResult['quantity'])
                                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-200/50 dark:border-slate-700/40">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-0.5"><i class="fas fa-weight-hanging mr-1"></i>Qtd.</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $onlineResult['quantity'] }}</p>
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
                                    <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">Descrição</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mt-0.5">{{ $onlineResult['description'] }}</p>
                                </div>
                                @endif
                                @if($onlineResult['ingredients'])
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">Ingredientes</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3 mt-0.5">{{ $onlineResult['ingredients'] }}</p>
                                </div>
                                @endif
                                <p class="text-[10px] text-slate-400 font-mono"><i class="fas fa-barcode mr-1"></i>{{ $onlineResult['barcode'] }}</p>
                            </div>
                        </div>
                        @if(!$foundProduct)
                        <div class="mt-4 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-2"><i class="fas fa-lightbulb mr-1"></i>Produto não cadastrado localmente.</p>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('products.create') }}" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg hover:scale-105 transition-all flex items-center gap-1.5">
                                    <i class="fas fa-plus"></i> Cadastrar
                                </a>
                                <button wire:click="setMode('vincular')" class="px-4 py-2 text-xs font-bold text-cyan-700 bg-cyan-100 hover:bg-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-400 rounded-xl transition-all flex items-center gap-1.5">
                                    <i class="fas fa-link"></i> Vincular
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($onlineError)
                <div class="bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm p-4">
                    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                        <i class="fas fa-globe text-slate-400"></i>
                        <span>{{ $onlineError }}</span>
                        <button wire:click="searchOnline('{{ $lastScannedBarcode }}')" class="ml-auto text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                </div>
                @endif

                {{-- ═══ ZONA F — VINCULAR ═══ --}}
                @if($activeMode === 'vincular')
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border-2 border-cyan-400/50 dark:border-cyan-600/50 shadow-xl shadow-cyan-500/8 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(6,182,212,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(6,182,212,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                    <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-cyan-300/30 dark:border-cyan-700/30 rounded-br-xl" style="background:linear-gradient(to right,rgba(6,182,212,.07),transparent)">
                        <span class="text-[9px] font-black tracking-[0.3em] text-cyan-500/60 dark:text-cyan-400/50 uppercase">ZONA F · VINCULAR CÓDIGO</span>
                    </div>
                    <div class="relative p-4 sm:p-5 pt-9">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                                <i class="fas fa-link text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 dark:text-white text-sm">Vincular Código de Barras</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Escaneie e selecione o produto para vincular</p>
                            </div>
                        </div>

                        @if($lastScannedBarcode)
                        <div class="mb-4 p-4 rounded-2xl border border-cyan-200 dark:border-cyan-800 flex items-center gap-3" style="background:linear-gradient(135deg,rgba(6,182,212,.08),rgba(59,130,246,.06))">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 flex-shrink-0">
                                <i class="fas fa-barcode text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[9px] text-cyan-600 dark:text-cyan-400 uppercase font-black tracking-wider">Código escaneado</p>
                                <p class="text-xl font-black font-mono text-slate-800 dark:text-white">{{ $lastScannedBarcode }}</p>
                            </div>
                        </div>
                        @else
                        <div class="mb-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border-2 border-dashed border-slate-200 dark:border-slate-700 text-center">
                            <i class="fas fa-barcode text-slate-300 dark:text-slate-600 text-3xl mb-2"></i>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Escaneie um código primeiro</p>
                        </div>
                        @endif

                        <div class="mb-3 relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-sm"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="linkSearchTerm" placeholder="Buscar produto..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all placeholder:text-slate-400" />
                        </div>

                        @if(count($linkCandidates) > 0)
                        <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                            @foreach($linkCandidates as $candidate)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 hover:border-cyan-300 dark:hover:border-cyan-700 transition-all">
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
                                    </p>
                                </div>
                                <p class="text-xs font-bold text-emerald-600 flex-shrink-0">R$ {{ number_format($candidate['price_sale'], 2, ',', '.') }}</p>
                                @if($lastScannedBarcode)
                                <button wire:click="linkBarcodeToProduct({{ $candidate['id'] }})" wire:confirm="Vincular {{ $lastScannedBarcode }} a '{{ $candidate['name'] }}'?" class="px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-cyan-500 to-blue-600 rounded-lg shadow-md transition-all hover:scale-105 flex-shrink-0 flex items-center gap-1">
                                    <i class="fas fa-link"></i> Vincular
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @elseif(!empty($linkSearchTerm))
                        <p class="text-sm text-slate-400 text-center py-4">Nenhum resultado para "{{ $linkSearchTerm }}"</p>
                        @endif

                        @if(count($productsWithoutBarcode) > 0 && empty($linkSearchTerm))
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Sem Código ({{ count($productsWithoutBarcode) }})</p>
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
                                        <p class="text-[10px] text-slate-400">{{ $noBarcodeProduct['product_code'] ?? '—' }}</p>
                                    </div>
                                    @if($lastScannedBarcode)
                                    <button wire:click="linkBarcodeToProduct({{ $noBarcodeProduct['id'] }})" wire:confirm="Vincular {{ $lastScannedBarcode }} a '{{ $noBarcodeProduct['name'] }}'?" class="px-2 py-1 text-[10px] font-bold text-cyan-700 bg-cyan-100 hover:bg-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-400 rounded-md transition-all flex-shrink-0">
                                        <i class="fas fa-link"></i>
                                    </button>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ═══ ZONA G — INVENTÁRIO ═══ --}}
                @if($activeMode === 'inventario' && count($inventoryItems) > 0)
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-orange-300/60 dark:border-orange-700/60 shadow-lg overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(249,115,22,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(249,115,22,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                    <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-orange-300/30 dark:border-orange-700/30 rounded-br-xl" style="background:linear-gradient(to right,rgba(249,115,22,.07),transparent)">
                        <span class="text-[9px] font-black tracking-[0.3em] text-orange-500/60 dark:text-orange-400/50 uppercase">ZONA G · INVENTÁRIO</span>
                    </div>
                    <div class="relative p-4 sm:p-5 pt-9">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg shadow-orange-500/30">
                                    <i class="fas fa-clipboard-list text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-800 dark:text-white text-sm">Inventário em Andamento</h3>
                                    <p class="text-xs text-slate-500">{{ count($inventoryItems) }} produto(s)</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="clearInventory" wire:confirm="Limpar inventário?" class="px-3 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 rounded-xl transition-all flex items-center gap-1">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                                <button wire:click="applyInventory" wire:confirm="Aplicar ao estoque?" wire:loading.attr="disabled" wire:target="applyInventory" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-amber-600 rounded-xl shadow-lg shadow-orange-500/25 transition-all hover:scale-105 flex items-center gap-1.5">
                                    <i wire:loading.remove wire:target="applyInventory" class="fas fa-check"></i>
                                    <i wire:loading wire:target="applyInventory" class="fas fa-spinner fa-spin"></i>
                                    Aplicar
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($inventoryItems as $bcode => $item)
                            <div class="flex items-center gap-3 py-2.5 px-3 rounded-xl bg-orange-50/60 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/30">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $item['product']['name'] }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono mt-0.5"><i class="fas fa-barcode mr-1"></i>{{ $bcode }}</p>
                                </div>
                                <input type="number" min="0" value="{{ $item['qty'] }}" wire:change="updateInventoryQty('{{ $bcode }}', $event.target.value)" class="w-20 px-2 py-1.5 rounded-xl border border-orange-300 dark:border-orange-700 bg-white dark:bg-slate-800 text-sm text-center font-black" />
                                <span class="text-xs text-slate-400 font-medium">un.</span>
                                <button wire:click="removeFromInventory('{{ $bcode }}')" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-all">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- ═══ ZONA H — VENDA ═══ --}}
                @if($activeMode === 'venda' && count($saleItems) > 0)
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-purple-300/60 dark:border-purple-700/60 shadow-lg overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(168,85,247,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(168,85,247,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                    <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-purple-300/30 dark:border-purple-700/30 rounded-br-xl" style="background:linear-gradient(to right,rgba(168,85,247,.07),transparent)">
                        <span class="text-[9px] font-black tracking-[0.3em] text-purple-500/60 dark:text-purple-400/50 uppercase">ZONA H · VENDA RÁPIDA</span>
                    </div>
                    <div class="relative p-4 sm:p-5 pt-9">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/30">
                                    <i class="fas fa-cart-shopping text-white text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-800 dark:text-white text-sm">Itens da Venda</h3>
                                    <p class="text-xs text-slate-500">{{ $this->saleItemsCount }} item(s) · <strong class="text-purple-600 dark:text-purple-400">R$ {{ number_format($this->saleTotal, 2, ',', '.') }}</strong></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="clearSale" wire:confirm="Limpar venda?" class="px-3 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 rounded-xl transition-all">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                                <a href="{{ route('sales.create') }}" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl shadow-lg shadow-purple-500/25 transition-all hover:scale-105 flex items-center gap-1.5">
                                    <i class="fas fa-arrow-right"></i> Venda
                                </a>
                            </div>
                        </div>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($saleItems as $bcode => $item)
                            <div class="flex items-center gap-3 py-2.5 px-3 rounded-xl bg-purple-50/60 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/30">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $item['product']['name'] }}</p>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">R$ {{ number_format($item['product']['price_sale'], 2, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button wire:click="updateSaleQty('{{ $bcode }}', {{ $item['qty'] - 1 }})" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-all font-bold text-sm text-slate-600 dark:text-slate-300">−</button>
                                    <span class="w-8 text-center font-black text-sm text-slate-800 dark:text-white">{{ $item['qty'] }}</span>
                                    <button wire:click="updateSaleQty('{{ $bcode }}', {{ $item['qty'] + 1 }})" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition-all font-bold text-sm text-slate-600 dark:text-slate-300">+</button>
                                </div>
                                <p class="text-sm font-black text-purple-600 dark:text-purple-400 w-20 text-right">R$ {{ number_format($item['product']['price_sale'] * $item['qty'], 2, ',', '.') }}</p>
                                <button wire:click="removeFromSale('{{ $bcode }}')" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-all">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-purple-200 dark:border-purple-800/60 flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-400"><i class="fas fa-receipt mr-1.5 text-purple-500"></i>Total da Venda</span>
                            <span class="text-3xl font-black text-purple-600 dark:text-purple-400">R$ {{ number_format($this->saleTotal, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endif
                </div>{{-- end main column --}}

                {{-- ─────────── SIDEBAR (right 1/3) ─────────── --}}
                <div class="space-y-4">

                    {{-- ═══ ZONA S1 — ESTATÍSTICAS ═══ --}}
                    @php $stats = $this->stats; @endphp
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.35) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.028;"></div>
                        <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-slate-200/50 dark:border-slate-700/50 rounded-br-xl" style="background:linear-gradient(to right,rgba(99,102,241,.06),transparent)">
                            <span class="text-[9px] font-black tracking-[0.3em] text-indigo-500/50 dark:text-indigo-400/40 uppercase">ZONA S1 · ESTATÍSTICAS</span>
                        </div>
                        <div class="relative p-4 pt-9">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="rounded-2xl p-3.5 text-center border border-slate-200/60 dark:border-slate-700/40 bg-slate-50 dark:bg-slate-800/60">
                                    <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1">Total</p>
                                    <p class="text-2xl font-black text-slate-700 dark:text-slate-200">{{ $stats['total'] }}</p>
                                </div>
                                <div class="rounded-2xl p-3.5 text-center border border-emerald-200/60 dark:border-emerald-800/40" style="background:linear-gradient(135deg,rgb(240,253,244),rgb(209,250,229))">
                                    <p class="text-[9px] text-emerald-600 uppercase font-black tracking-wider mb-1">Com Código</p>
                                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['with_barcode'] }}</p>
                                </div>
                                <div class="rounded-2xl p-3.5 text-center border border-amber-200/60 dark:border-amber-800/40" style="background:linear-gradient(135deg,rgb(255,251,235),rgb(254,243,199))">
                                    <p class="text-[9px] text-amber-600 uppercase font-black tracking-wider mb-1">Sem Código</p>
                                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['without_barcode'] }}</p>
                                </div>
                                <div class="rounded-2xl p-3.5 text-center border border-indigo-200/60 dark:border-indigo-800/40" style="background:linear-gradient(135deg,rgb(238,242,255),rgb(224,231,255))">
                                    <p class="text-[9px] text-indigo-600 uppercase font-black tracking-wider mb-1">Cobertura</p>
                                    <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['percentage'] }}%</p>
                                </div>
                            </div>
                            <div class="mt-3.5 h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-700" style="width: {{ $stats['percentage'] }}%"></div>
                            </div>
                            <p class="text-[9px] text-center text-slate-400 mt-1.5 font-semibold">{{ $stats['with_barcode'] }} de {{ $stats['total'] }} com código</p>
                        </div>
                    </div>

                    {{-- ═══ ZONA S2 — GUIA RÁPIDO ═══ --}}
                    <div class="relative backdrop-blur-md rounded-2xl border border-indigo-200/50 dark:border-indigo-700/50 shadow-lg overflow-hidden" style="background:linear-gradient(135deg,rgba(99,102,241,.05),rgba(168,85,247,.04),rgba(236,72,153,.03))">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.3) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.3) 1px, transparent 1px); background-size: 16px 16px; opacity: 0.03;"></div>
                        <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-indigo-300/20 dark:border-indigo-700/30 rounded-br-xl" style="background:linear-gradient(to right,rgba(99,102,241,.08),transparent)">
                            <span class="text-[9px] font-black tracking-[0.3em] text-indigo-500/50 dark:text-indigo-400/40 uppercase">ZONA S2 · GUIA RÁPIDO</span>
                        </div>
                        <div class="absolute top-2 right-2 w-5 h-5 border-t-2 border-r-2 border-indigo-300/20 dark:border-indigo-600/20 rounded-tr pointer-events-none"></div>
                        <div class="relative p-4 pt-9">
                            <div class="space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-[10px] font-black flex items-center justify-center flex-shrink-0 mt-0.5 shadow-md shadow-indigo-500/30">1</div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Selecione o modo</p>
                                        <p class="text-[10px] text-slate-400">Consultar, Preço, Estoque...</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-[10px] font-black flex items-center justify-center flex-shrink-0 mt-0.5 shadow-md shadow-indigo-500/30">2</div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Escolha a entrada</p>
                                        <div class="mt-1 space-y-0.5">
                                            <p class="text-[10px] text-slate-400"><i class="fas fa-keyboard text-slate-400 w-3 mr-1"></i><strong>Digitar</strong> — USB/teclado</p>
                                            <p class="text-[10px] text-slate-400"><i class="fas fa-camera text-indigo-500 w-3 mr-1"></i><strong>Câmera</strong> — Tela cheia auto</p>
                                            <p class="text-[10px] text-slate-400"><i class="fas fa-image text-purple-500 w-3 mr-1"></i><strong>Imagem</strong> — Upload foto</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-[10px] font-black flex items-center justify-center flex-shrink-0 mt-0.5 shadow-md shadow-indigo-500/30">3</div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Resultados automáticos</p>
                                        <p class="text-[10px] text-slate-400">Busca local + online simultânea</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-indigo-200/40 dark:border-indigo-800/40">
                                <p class="text-[9px] text-indigo-500 dark:text-indigo-400 font-black mb-2 uppercase tracking-widest">6 Modos</p>
                                <div class="grid grid-cols-2 gap-1">
                                    <p class="text-[10px] text-slate-500 flex items-center gap-1"><i class="fas fa-search text-blue-500 w-3"></i><strong>Consultar</strong></p>
                                    <p class="text-[10px] text-slate-500 flex items-center gap-1"><i class="fas fa-tag text-pink-500 w-3"></i><strong>Ver Preço</strong></p>
                                    <p class="text-[10px] text-slate-500 flex items-center gap-1"><i class="fas fa-boxes-stacked text-emerald-500 w-3"></i><strong>Estoque</strong></p>
                                    <p class="text-[10px] text-slate-500 flex items-center gap-1"><i class="fas fa-clipboard-list text-orange-500 w-3"></i><strong>Inventário</strong></p>
                                    <p class="text-[10px] text-slate-500 flex items-center gap-1"><i class="fas fa-cart-shopping text-purple-500 w-3"></i><strong>Venda</strong></p>
                                    <p class="text-[10px] text-slate-500 flex items-center gap-1"><i class="fas fa-link text-cyan-500 w-3"></i><strong>Vincular</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ ZONA S3 — HISTÓRICO ═══ --}}
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-lg overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.3) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.3) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.02;"></div>
                        <div class="absolute top-0 left-0 px-3 py-1.5 border-b border-r border-slate-200/50 dark:border-slate-700/50 rounded-br-xl" style="background:linear-gradient(to right,rgba(100,116,139,.05),transparent)">
                            <span class="text-[9px] font-black tracking-[0.3em] text-slate-400/60 dark:text-slate-500/50 uppercase">ZONA S3 · HISTÓRICO</span>
                        </div>
                        <div class="relative p-4 pt-9">
                            <div class="flex items-center justify-between mb-3">
                                @if(count($scanHistory) > 0)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ count($scanHistory) }} leitura(s)</span>
                                <button wire:click="clearHistory" class="text-[10px] text-red-400 hover:text-red-600 transition-colors font-bold flex items-center gap-1">
                                    <i class="fas fa-trash-can"></i> Limpar
                                </button>
                                @endif
                            </div>
                            @if(count($scanHistory) === 0)
                            <div class="text-center py-10">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800/60 flex items-center justify-center mb-3">
                                    <i class="fas fa-list-check text-2xl text-slate-300 dark:text-slate-600"></i>
                                </div>
                                <p class="text-xs text-slate-400 font-semibold">Nenhuma leitura ainda</p>
                                <p class="text-[10px] text-slate-300 dark:text-slate-600 mt-0.5">Escaneie para ver o histórico</p>
                            </div>
                            @else
                            <div class="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                                @foreach($scanHistory as $entry)
                                <div class="flex items-start gap-2.5 p-2.5 rounded-xl {{ $entry['found'] ? 'bg-emerald-50/60 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30' : 'bg-red-50/60 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30' }}">
                                    <div class="w-7 h-7 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm {{ $entry['found'] ? 'bg-gradient-to-br from-emerald-500 to-teal-600' : 'bg-gradient-to-br from-red-500 to-rose-600' }}">
                                        <i class="{{ $entry['found'] ? 'fas fa-check' : 'fas fa-xmark' }} text-white text-[10px]"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        @if($entry['found'])
                                        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $entry['product']['name'] }}</p>
                                        @else
                                        <p class="text-xs font-bold text-red-600 dark:text-red-400">Não encontrado</p>
                                        @endif
                                        <p class="text-[9px] text-slate-400 font-mono mt-0.5 truncate">{{ $entry['code'] }}</p>
                                        <p class="text-[9px] text-slate-400 mt-0.5">{{ $entry['scanned_at'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                </div>{{-- end sidebar --}}
            </div>{{-- end main grid --}}
        </div>{{-- end content wrapper --}}
    </div>{{-- end main wrapper --}}

    {{-- ========== STYLES ========== --}}
    <style>
        @keyframes scan-line {
            0% { top: 10%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }
        .animate-scan-line {
            animation: scan-line 2s ease-in-out infinite;
            position: absolute;
        }
        .safe-area-top { padding-top: env(safe-area-inset-top, 0px); }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
    </style>

    {{-- ========== CDN: html5-qrcode ========== --}}
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

                // Scan mode
                scanMode: 'manual',

                // Camera
                cameraActive: false,
                cameraError: null,
                lastCameraScan: null,
                cameraFacing: 'environment',
                _html5QrCode: null,
                _scanCooldown: false,

                // Image
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
                            var input = document.getElementById('barcode-input');
                            if (input) input.focus();
                        });
                    }
                    if (mode === 'camera') {
                        this.$nextTick(() => { this.startCamera(); });
                    }
                },

                // ------ CAMERA ------
                async startCamera() {
                    this.cameraError = null;
                    this.lastCameraScan = null;

                    try {
                        if (this._html5QrCode) {
                            try { await this._html5QrCode.stop(); } catch(e) {}
                            this._html5QrCode = null;
                        }

                        await new Promise(function(r) { setTimeout(r, 150); });

                        var viewport = document.getElementById('camera-scanner-viewport');
                        if (!viewport) {
                            this.cameraError = 'Viewport da câmera não encontrado. Tente novamente.';
                            return;
                        }

                        this._html5QrCode = new Html5Qrcode('camera-scanner-viewport');

                        var config = {
                            fps: 20,
                            qrbox: function(vw, vh) {
                                // Caixa generosa para leitura de 1D barcodes em qualquer orientação
                                var minDim = Math.min(vw, vh);
                                var maxDim = Math.max(vw, vh);
                                return {
                                    width:  Math.floor(maxDim * 0.75),
                                    height: Math.floor(minDim * 0.40)
                                };
                            },
                            // NÃO definir aspectRatio — no mobile portrait (innerH/innerW ≈ 2.16)
                            // isso força constraint inválido e quebra a detecção silenciosamente
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
                                Html5QrcodeSupportedFormats.DATA_MATRIX,
                            ],
                            // Usa o BarcodeDetector nativo do browser (Chrome/Edge/Safari 17+)
                            // muito mais rápido e preciso do que o ZXing em WASM
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            },
                            rememberLastUsedCamera: true,
                            videoConstraints: {
                                facingMode: this.cameraFacing,
                                width:  { ideal: 1280 },
                                height: { ideal: 720 }
                            },
                        };

                        var self = this;
                        await this._html5QrCode.start(
                            { facingMode: this.cameraFacing },
                            config,
                            function(decodedText) { self.onCameraCodeDetected(decodedText); },
                            function() {} // suppress per-frame errors
                        );

                        this.cameraActive = true;
                    } catch (err) {
                        console.error('Camera error:', err);
                        var msg = 'Não foi possível acessar a câmera.';
                        if (typeof err === 'string') {
                            if (err.includes('NotAllowedError') || err.includes('Permission')) {
                                msg = 'Permissão negada. Permita o acesso à câmera nas configurações.';
                            } else if (err.includes('NotFoundError') || err.includes('Requested device not found')) {
                                msg = 'Nenhuma câmera encontrada neste dispositivo.';
                            } else if (err.includes('NotReadableError') || err.includes('Could not start')) {
                                msg = 'A câmera está em uso por outro app.';
                            }
                        }
                        this.cameraError = msg;
                        this.cameraActive = false;
                    }
                },

                async stopCamera() {
                    if (this._html5QrCode) {
                        try { await this._html5QrCode.stop(); } catch (e) {}
                        this._html5QrCode = null;
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
                    var self = this;
                    setTimeout(function() { self._scanCooldown = false; }, 1500);

                    this.lastCameraScan = code;
                    this.playBeep();

                    this.$wire.set('barcodeInput', code).then(() => {
                        this.$wire.searchBarcode();
                    });
                },

                // ------ IMAGE ------
                handleImageSelect(event) {
                    var file = event.target.files[0];
                    if (file) this.loadImageFile(file);
                },

                handleImageDrop(event) {
                    this.imageDragOver = false;
                    var file = event.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        this.loadImageFile(file);
                    }
                },

                loadImageFile(file) {
                    this._imageFile = file;
                    this.imageResult = null;
                    this.imageResultSuccess = false;

                    var self = this;
                    var reader = new FileReader();
                    reader.onload = function(e) { self.imagePreview = e.target.result; };
                    reader.readAsDataURL(file);

                    this.$nextTick(() => {
                        setTimeout(() => this.scanImageBarcode(), 300);
                    });
                },

                async scanImageBarcode() {
                    if (!this._imageFile || this.imageScanning) return;

                    this.imageScanning = true;
                    this.imageResult = null;

                    try {
                        // scanFileV2 retorna Html5QrcodeResult com .decodedText
                        var result = await Html5Qrcode.scanFileV2(this._imageFile, false);
                        var code = (result && (result.decodedText || (result.result && result.result.text))) || null;

                        if (code) {
                            this.imageResult = 'Código encontrado: ' + code;
                            this.imageResultSuccess = true;
                            this.playBeep();
                            this.$wire.set('barcodeInput', code).then(() => {
                                this.$wire.searchBarcode();
                            });
                        } else {
                            this.imageResult = 'Nenhum código detectado. Tente com a imagem mais nítida.';
                            this.imageResultSuccess = false;
                        }
                    } catch (err) {
                        console.warn('Image scan error:', err);
                        // html5-qrcode rejeita a Promise com mensagem de texto quando não acha código
                        var errMsg = (typeof err === 'string') ? err : (err && err.message) ? err.message : '';
                        if (errMsg.toLowerCase().includes('no multiformat readers') ||
                            errMsg.toLowerCase().includes('no barcode or qr code detected') ||
                            errMsg.toLowerCase().includes('code not found')) {
                            this.imageResult = 'Código não encontrado. Tente uma foto mais próxima e nítida.';
                        } else {
                            this.imageResult = 'Erro ao analisar imagem. Tente outro formato.';
                        }
                        this.imageResultSuccess = false;
                    }

                    this.imageScanning = false;
                },

                clearImage() {
                    this.imagePreview = null;
                    this._imageFile = null;
                    this.imageResult = null;
                    this.imageResultSuccess = false;
                    if (this.$refs.imageFileInput) this.$refs.imageFileInput.value = '';
                },

                // ------ HELPERS ------
                playBeep() {
                    try {
                        var ctx = new (window.AudioContext || window.webkitAudioContext)();
                        var osc = ctx.createOscillator();
                        var gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 1200;
                        osc.type = 'sine';
                        gain.gain.value = 0.3;
                        osc.start();
                        osc.stop(ctx.currentTime + 0.15);
                    } catch (e) {}
                },

                destroy() {
                    this.stopCamera();
                },
            };
        }

        // Auto-focus barcode input
        document.addEventListener('livewire:updated', function() {
            var input = document.getElementById('barcode-input');
            if (input) {
                var xData = document.querySelector('[x-data]');
                if (xData && xData.__x && xData.__x.$data && xData.__x.$data.scanMode === 'manual') {
                    input.focus();
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.getElementById('barcode-input');
            if (input) input.focus();
        });
    </script>
</div>