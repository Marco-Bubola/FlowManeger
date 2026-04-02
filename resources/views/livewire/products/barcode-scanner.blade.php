@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/produtos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/produtos-extra.css') }}">
    <style>
        /* ── Cards para seção Vincular (proporcionais, responsivos) ────────── */
        .vincular-grid .product-card-modern {
            min-height: 0;
            border-radius: 1em;
        }
        .vincular-grid .product-img-area {
            min-height: 96px;
            height: 96px;
            border-top-left-radius: 0.9em;
            border-top-right-radius: 0.9em;
        }
        .vincular-grid .product-img {
            border-top-left-radius: 0.9em;
            border-top-right-radius: 0.9em;
        }
        .vincular-grid .badge-product-code {
            font-size: 0.6em;
            padding: 0.1em 0.5em;
        }
        .vincular-grid .badge-quantity {
            font-size: 0.58em;
            padding: 0.08em 0.42em;
        }
        .vincular-grid .no-barcode-badge {
            position: absolute;
            top: 0.35em;
            right: 0.35em;
            background: rgba(245,158,11,0.92);
            color: #fff;
            font-size: 0.58em;
            font-weight: 800;
            padding: 0.12em 0.45em;
            border-radius: 0.5em;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 0.2em;
        }
        .vincular-grid .category-icon-wrapper {
            width: 26px;
            height: 26px;
            bottom: -13px;
            border-width: 2px;
        }
        .vincular-grid .category-icon {
            font-size: 0.75em;
        }
        .vincular-grid .card-body {
            padding: 1em 0.5em 0.6em 0.5em;
            gap: 0.08em;
            min-height: 0;
        }
        .vincular-grid .product-title {
            font-size: 0.7em;
            letter-spacing: 0.02em;
            -webkit-line-clamp: 2;
            line-clamp: 2;
        }
        .vincular-grid .price-area {
            margin-top: 0.3em;
            min-height: 0;
        }
        .vincular-grid .badge-price,
        .vincular-grid .badge-price-sale {
            font-size: 0.64em;
            padding: 0.12em 0.5em;
        }
        .vincular-grid .btn-action-group {
            top: 0.3rem;
            right: 0.3rem;
            flex-direction: row;
            gap: 0.18rem;
        }
        .vincular-grid .btn-action-group .btn {
            width: 26px;
            height: 26px;
            font-size: 0.7em;
            border-radius: 0.45em;
            padding: 0;
        }
        /* iPhone (max col-2) - cards um pouco maiores */
        @media (max-width: 639px) {
            .vincular-grid .product-img-area {
                min-height: 120px;
                height: 120px;
            }
            .vincular-grid .product-title { font-size: 0.75em; }
            .vincular-grid .badge-price,
            .vincular-grid .badge-price-sale { font-size: 0.68em; }
        }
    </style>
@endpush

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

    {{-- ========== MODAL DE DICAS ========== --}}
    <div x-show="showTipsModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4"
         style="background-color:rgba(15,23,42,.5);backdrop-filter:blur(14px);display:none">

        <div @click.outside="showTipsModal = false; tipStep = 1"
             class="relative bg-white dark:bg-slate-800 rounded-[28px] shadow-2xl w-full max-w-2xl max-h-[88vh] overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Cabeçalho gradiente --}}
            <div class="relative overflow-hidden px-5 py-5 sm:px-6" style="background:linear-gradient(135deg,#6366f1,#8b5cf6,#ec4899)">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full" style="background:radial-gradient(circle,rgba(255,255,255,.12) 0%,transparent 70%)"></div>
                <button @click="showTipsModal = false; tipStep = 1"
                        class="absolute top-3 right-3 w-9 h-9 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-all active:scale-90">
                    <i class="fas fa-xmark"></i>
                </button>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-lightbulb text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-white/70 font-black uppercase tracking-widest">Guia do Scanner</p>
                        <h2 class="text-xl font-black text-white">Dicas de uso</h2>
                    </div>
                </div>
                <div class="flex gap-1.5">
                    <template x-for="s in tipTotal" :key="s">
                        <div class="flex-1 h-1.5 rounded-full overflow-hidden bg-white/20">
                            <div class="h-full bg-white rounded-full transition-all duration-500" :style="tipStep >= s ? 'width:100%' : 'width:0%'"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Conteúdo --}}
            <div class="overflow-y-auto max-h-[calc(88vh-220px)] p-5 sm:p-6">

                {{-- Passo 1 --}}
                <div x-show="tipStep === 1"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-6"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-5">
                        <div class="inline-flex w-16 h-16 items-center justify-center rounded-3xl shadow-xl mb-3" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                            <i class="fas fa-sliders text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 dark:text-white">Passo 1 — Escolha o Modo</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Define como o código lido vai agir no sistema</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach([
                            ['icon'=>'fas fa-search','color'=>'blue','name'=>'Consultar','desc'=>'Exibe dados completos do produto'],
                            ['icon'=>'fas fa-tag','color'=>'pink','name'=>'Ver Preço','desc'=>'Mostra custo e preço de venda em destaque'],
                            ['icon'=>'fas fa-boxes-stacked','color'=>'emerald','name'=>'Estoque','desc'=>'Atualiza o saldo de estoque do item'],
                            ['icon'=>'fas fa-clipboard-list','color'=>'orange','name'=>'Inventário','desc'=>'Acumula itens para lançamento em lote'],
                            ['icon'=>'fas fa-cart-shopping','color'=>'purple','name'=>'Venda','desc'=>'Monta lista de itens para nova venda'],
                            ['icon'=>'fas fa-link','color'=>'cyan','name'=>'Vincular','desc'=>'Associa EAN a produto já cadastrado'],
                        ] as $tip)
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-3 bg-slate-50/80 dark:bg-slate-900/40 flex flex-col gap-1.5">
                            <i class="{{ $tip['icon'] }} text-{{ $tip['color'] }}-500 text-lg"></i>
                            <p class="text-xs font-black text-slate-800 dark:text-white">{{ $tip['name'] }}</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ $tip['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Passo 2 --}}
                <div x-show="tipStep === 2"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-6"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-5">
                        <div class="inline-flex w-16 h-16 items-center justify-center rounded-3xl shadow-xl mb-3" style="background:linear-gradient(135deg,#8b5cf6,#ec4899)">
                            <i class="fas fa-keyboard text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 dark:text-white">Passo 2 — Entrada do Código</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Escolha como o código vai entrar no scanner</p>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-4 rounded-2xl border border-indigo-200/60 dark:border-indigo-800/40 bg-indigo-50/60 dark:bg-indigo-900/15">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 shadow flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-keyboard text-indigo-500"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-white">Digitar / Leitor USB</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Conecte o leitor USB e leia direto no campo — ou digite o EAN manualmente.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-2xl border border-purple-200/60 dark:border-purple-800/40 bg-purple-50/60 dark:bg-purple-900/15">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 shadow flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-camera text-purple-500"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-white">Câmera (tela cheia)</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Abre câmera em fullscreen com BarcodeDetector nativo + Quagga2 como fallback. Funciona em iOS 17+ via Safari.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-2xl border border-pink-200/60 dark:border-pink-800/40 bg-pink-50/60 dark:bg-pink-900/15">
                            <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 shadow flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-image text-pink-500"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-white">Upload de Imagem</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Faça upload ou arraste uma foto. O sistema tenta múltiplas variantes para maximizar a detecção.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Passo 3 --}}
                <div x-show="tipStep === 3"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-6"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="text-center mb-5">
                        <div class="inline-flex w-16 h-16 items-center justify-center rounded-3xl shadow-xl mb-3" style="background:linear-gradient(135deg,#10b981,#0d9488)">
                            <i class="fas fa-circle-check text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 dark:text-white">Passo 3 — Resultados</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">O que acontece após a leitura</p>
                    </div>
                    <div class="space-y-3">
                        <div class="p-4 rounded-2xl border border-emerald-200/60 dark:border-emerald-800/40 bg-emerald-50/70 dark:bg-emerald-900/15">
                            <p class="text-xs font-black text-emerald-700 dark:text-emerald-400 mb-2"><i class="fas fa-database mr-1.5"></i>Busca Local Instantânea</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">O produto aparece em destaque com dados completos, imagem, preços e estoque.</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-blue-200/60 dark:border-blue-800/40 bg-blue-50/70 dark:bg-blue-900/15">
                            <p class="text-xs font-black text-blue-700 dark:text-blue-400 mb-2"><i class="fas fa-globe mr-1.5"></i>Busca Online Automática</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">Consulta Open Food Facts, Beauty Facts e UPC DB. Permite aplicar dados ao produto local.</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-cyan-200/60 dark:border-cyan-800/40 bg-cyan-50/70 dark:bg-cyan-900/15">
                            <p class="text-xs font-black text-cyan-700 dark:text-cyan-400 mb-2"><i class="fas fa-link mr-1.5"></i>Modo Vincular</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">Não achou? Mude para <strong>Vincular</strong> e selecione o produto correto no grid para associar o código.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-slate-700 dark:to-slate-700 border border-indigo-200/50">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-heart text-pink-500 text-xl"></i>
                                <div>
                                    <p class="text-sm font-black text-slate-800 dark:text-white">Tudo pronto!</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Consulte o histórico na barra lateral para revisitar leituras anteriores.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rodapé --}}
            <div class="bg-slate-50 dark:bg-slate-900/60 px-5 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                <button @click="tipStep > 1 ? tipStep-- : null" :class="tipStep === 1 ? 'opacity-30 pointer-events-none' : 'hover:bg-slate-200 dark:hover:bg-slate-700'" class="px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                <div class="flex gap-1.5">
                    <template x-for="s in tipTotal" :key="s">
                        <button @click="tipStep = s" class="w-2.5 h-2.5 rounded-full transition-all" :class="tipStep === s ? 'bg-indigo-500 scale-125' : 'bg-slate-300 dark:bg-slate-600'"></button>
                    </template>
                </div>
                <button @click="tipStep < tipTotal ? tipStep++ : (showTipsModal = false, tipStep = 1)" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:scale-105 flex items-center gap-2" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                    <span x-text="tipStep < tipTotal ? 'Próximo' : 'Fechar'"></span>
                    <i :class="tipStep < tipTotal ? 'fas fa-arrow-right' : 'fas fa-check'"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL: LOG DA ANÁLISE ========== --}}
    <div x-show="showLogModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-4"
         style="background-color:rgba(15,23,42,.6);backdrop-filter:blur(14px);display:none">
        <div class="relative bg-white dark:bg-slate-800 rounded-[28px] shadow-2xl w-full max-w-xl max-h-[80vh] flex flex-col border border-slate-200/50 dark:border-slate-700/50 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showLogModal = false">
            {{-- Cabeçalho --}}
            <div class="relative overflow-hidden px-5 py-4 flex-shrink-0" style="background:linear-gradient(135deg,#6366f1,#4f46e5,#7c3aed)">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full" style="background:radial-gradient(circle,rgba(255,255,255,.1) 0%,transparent 70%)"></div>
                <button @click="showLogModal = false" class="absolute top-3 right-3 w-9 h-9 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-all active:scale-90">
                    <i class="fas fa-xmark"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-list-check text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-white/70 font-black uppercase tracking-widest">Scanner por Imagem</p>
                        <h2 class="text-lg font-black text-white">Log da análise</h2>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold" x-text="imageDebugLogs.length + ' linhas'"></span>
                    </div>
                </div>
            </div>
            {{-- Conteúdo scrollável --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-1.5 bg-slate-50 dark:bg-slate-900/40">
                {{-- Banner de resultado --}}
                <div x-show="imageResult" class="mb-3 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm"
                     :class="imageResultSuccess
                        ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                         :class="imageResultSuccess ? 'bg-emerald-500' : 'bg-red-500'">
                        <i :class="imageResultSuccess ? 'fas fa-barcode' : 'fas fa-xmark'" class="text-white text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-wider"
                           :class="imageResultSuccess ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                           x-text="imageResultSuccess ? 'Código detectado com sucesso' : 'Nenhum código detectado'"></p>
                        <p class="text-sm font-mono font-black"
                           :class="imageResultSuccess ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-700 dark:text-red-400'"
                           x-text="imageResult"></p>
                    </div>
                </div>
                <template x-for="(log, index) in imageDebugLogs" :key="index">
                    <div class="flex items-start gap-2.5 py-2 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 shadow-sm">
                        <span class="mt-1.5 h-2 w-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                        <span class="font-mono text-xs text-slate-700 dark:text-slate-300 leading-relaxed" x-text="log"></span>
                    </div>
                </template>
                <div x-show="!imageDebugLogs.length" class="text-center py-8 text-slate-400 text-sm">
                    Nenhum log disponível
                </div>
            </div>
            {{-- Rodapé --}}
            <div class="flex-shrink-0 px-5 py-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center gap-3 bg-white dark:bg-slate-800">
                <button @click="imageDebugLogs = []; showLogModal = false" type="button" class="px-4 py-2 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-bold hover:bg-red-100 transition-all flex items-center gap-1.5">
                    <i class="fas fa-trash-can"></i> Limpar log
                </button>
                <button @click="showLogModal = false" type="button" class="px-5 py-2 rounded-xl bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold transition-all flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Fechar
                </button>
            </div>
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

        {{-- Flash overlay ao tirar foto --}}
        <div x-show="photoSnapping"
             class="absolute inset-0 bg-white pointer-events-none z-10"
             x-transition:enter="transition duration-75"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-90"
             x-transition:leave="transition duration-300"
             x-transition:leave-start="opacity-90"
             x-transition:leave-end="opacity-0"
             style="display:none">
        </div>

        {{-- Bottom bar --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 pb-8 bg-gradient-to-t from-black/80 to-transparent safe-area-bottom">

            {{-- Banner de CONFIRMAÇÃO (aparece após detectar código) --}}
            <div x-show="pendingCode" x-transition class="mb-4 mx-auto max-w-sm">
                <div class="bg-slate-900/95 backdrop-blur-md rounded-2xl px-4 py-3 border border-emerald-500/50 shadow-2xl">
                    <p class="text-[10px] text-emerald-400 uppercase font-black tracking-widest mb-1 text-center">Código detectado</p>
                    <p class="text-white font-black font-mono text-xl text-center tracking-wider mb-3" x-text="pendingCode"></p>
                    <div class="flex gap-2">
                        <button @click="confirmCode()"
                            class="flex-1 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-white font-black text-sm transition-all shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Confirmar
                        </button>
                        <button @click="rejectCode()"
                            class="flex-1 py-3 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 text-white font-bold text-sm transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-camera"></i> Continuar
                        </button>
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

            {{-- Botões da parte inferior --}}
            <div class="flex flex-col items-center gap-3 mb-2">

                {{-- Linha com: galeria | obturador | câmera nativa --}}
                <div class="flex items-end justify-center gap-8">

                    {{-- Botão: Câmera nativa iOS (abre app câmera do sistema) --}}
                    <div class="flex flex-col items-center gap-1">
                        <label for="native-camera-input"
                            class="flex items-center justify-center rounded-2xl bg-white/15 backdrop-blur-md text-white transition-all active:scale-90 cursor-pointer hover:bg-white/25"
                            style="width:2.75rem;height:2.75rem"
                            title="Abrir câmera nativa">
                            <i class="fas fa-camera-viewfinder text-base"></i>
                        </label>
                        <p class="text-white/50 text-[9px] font-semibold tracking-wide uppercase">Foto iOS</p>
                    </div>

                    {{-- Botão obturador principal --}}
                    <div class="flex flex-col items-center gap-1">
                        <button @click="snapPhoto()"
                            :disabled="photoSnapping"
                            class="flex items-center justify-center rounded-full border-4 border-white/70 shadow-2xl transition-all active:scale-90 disabled:opacity-50"
                            style="width:4.5rem;height:4.5rem"
                            title="Tirar foto do código">
                            <span class="block rounded-full bg-white transition-colors"
                                :class="photoSnapping ? 'bg-slate-300 animate-pulse' : 'bg-white group-active:bg-slate-200'"
                                style="width:3.25rem;height:3.25rem"></span>
                        </button>
                        <p class="text-white/60 text-[10px] font-semibold tracking-wider uppercase">Tirar foto</p>
                    </div>

                    {{-- Botão: retornar ao modo manual --}}
                    <div class="flex flex-col items-center gap-1">
                        <button @click="setScanMode('manual')"
                            class="flex items-center justify-center rounded-2xl bg-white/15 backdrop-blur-md text-white transition-all active:scale-90 hover:bg-white/25"
                            style="width:2.75rem;height:2.75rem"
                            title="Voltar ao modo manual">
                            <i class="fas fa-keyboard text-base"></i>
                        </button>
                        <p class="text-white/50 text-[9px] font-semibold tracking-wide uppercase">Digitar</p>
                    </div>
                </div>

                <p class="text-center text-white/40 text-xs" x-show="!pendingCode && !cameraError && cameraActive">
                    <i class="fas fa-barcode mr-1"></i> Aponte para o código ou pressione o obturador
                </p>
                <p class="text-center text-amber-300/80 text-xs" x-show="!cameraActive && !cameraError && !pendingCode">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Iniciando câmera...
                </p>
            </div>
        </div>

        {{-- Input nativo para câmera iOS (capture=environment) — abre app câmera do iPhone --}}
        <input id="native-camera-input"
            type="file"
            accept="image/*"
            capture="environment"
            @change="handleNativeCameraPhoto($event)"
            class="hidden" />
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
            {{-- ══ MODO DE LEITURA: Dropdown compacto no header ══ --}}
            @php
            $modeHeaderMap = [
                'consulta'   => ['icon' => 'fas fa-search',         'label' => 'Consultar',  'color' => 'blue',    'desc' => 'Exibe dados completos do produto'],
                'preco'      => ['icon' => 'fas fa-tag',             'label' => 'Ver Preço',  'color' => 'pink',    'desc' => 'Mostra custo e preço de venda'],
                'estoque'    => ['icon' => 'fas fa-boxes-stacked',   'label' => 'Estoque',    'color' => 'emerald', 'desc' => 'Atualiza o saldo de estoque'],
                'inventario' => ['icon' => 'fas fa-clipboard-list',  'label' => 'Inventário', 'color' => 'orange',  'desc' => 'Acumula itens para lançamento em lote'],
                'venda'      => ['icon' => 'fas fa-cart-shopping',   'label' => 'Venda',      'color' => 'purple',  'desc' => 'Monta lista de itens para nova venda'],
                'vincular'   => ['icon' => 'fas fa-link',            'label' => 'Vincular',   'color' => 'cyan',    'desc' => 'Associa EAN a produto já cadastrado'],
            ];
            $chm = $modeHeaderMap[$activeMode] ?? $modeHeaderMap['consulta'];
            @endphp
            <div class="relative" @keydown.escape.window="modeOpen = false" @click.outside="modeOpen = false">
                <button @click="modeOpen = !modeOpen" type="button"
                        class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white/80 dark:bg-slate-700/80 backdrop-blur-sm border border-slate-200 dark:border-slate-600 text-sm font-semibold hover:bg-white dark:hover:bg-slate-600 transition-all shadow-sm"
                        :class="modeOpen ? 'ring-2 ring-indigo-400/50 border-indigo-300 dark:border-indigo-600' : ''">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-{{ $chm['color'] }}-500 to-{{ $chm['color'] }}-600">
                        <i class="{{ $chm['icon'] }} text-white text-[10px]"></i>
                    </div>
                    <span class="hidden md:inline text-slate-700 dark:text-slate-200">{{ $chm['label'] }}</span>
                    <span class="inline md:hidden text-[10px] font-black uppercase tracking-wide text-{{ $chm['color'] }}-600 dark:text-{{ $chm['color'] }}-400">Modo</span>
                    <i class="fas fa-chevron-down text-slate-400 text-[11px] transition-transform duration-200" :class="modeOpen ? 'rotate-180' : ''"></i>
                </button>
                {{-- Painel dropdown --}}
                <div x-show="modeOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                     class="absolute right-0 top-[calc(100%+8px)] z-[100] w-72 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xl shadow-slate-900/20 overflow-hidden"
                     style="display:none">
                    <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        <p class="text-[9px] font-black tracking-[0.2em] text-slate-400 uppercase">Modo de leitura</p>
                    </div>
                    <div class="p-1.5 space-y-0.5">
                        @foreach($modeHeaderMap as $modeId => $m)
                        <button wire:click="setMode('{{ $modeId }}')"
                                @click="modeOpen = false"
                                type="button"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all border
                                    {{ $activeMode === $modeId
                                        ? 'bg-'.$m['color'].'-50/80 dark:bg-'.$m['color'].'-900/20 border-'.$m['color'].'-200/60 dark:border-'.$m['color'].'-800/50'
                                        : 'border-transparent hover:bg-slate-50 dark:hover:bg-slate-700/60' }}">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm
                                {{ $activeMode === $modeId
                                    ? 'bg-gradient-to-br from-'.$m['color'].'-500 to-'.$m['color'].'-600'
                                    : 'bg-slate-100 dark:bg-slate-700' }}">
                                <i class="{{ $m['icon'] }} text-xs {{ $activeMode === $modeId ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold leading-tight
                                    {{ $activeMode === $modeId ? 'text-'.$m['color'].'-700 dark:text-'.$m['color'].'-300' : 'text-slate-700 dark:text-slate-200' }}">{{ $m['label'] }}</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $m['desc'] }}</p>
                            </div>
                            @if($activeMode === $modeId)
                            <span class="relative flex h-2 w-2 flex-shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $m['color'] }}-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $m['color'] }}-500"></span>
                            </span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <button @click="showTipsModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-slate-700/80 backdrop-blur-sm border border-slate-200 dark:border-slate-600 text-amber-600 dark:text-amber-400 text-sm font-semibold hover:bg-white dark:hover:bg-slate-600 transition-all shadow-sm" title="Dicas de uso">
                <i class="fas fa-lightbulb"></i>
                <span class="hidden sm:inline">Dicas</span>
            </button>
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


            {{-- ═══ WORKSPACE: Scanner (esq. 1/4) + Painel (dir. 3/4 sticky) ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 items-start">

                {{-- ─────────── SCANNER (≈ 1/4 da tela) ─────────── --}}
                <div class="md:col-span-5 lg:col-span-3">

                    {{-- ═══ SCANNER ═══ --}}
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border border-slate-200/60 dark:border-slate-700/60 shadow-xl overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.028;"></div>
                        <div class="relative p-4 sm:p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center shadow-md" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                                    <i class="fas fa-barcode text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black tracking-[0.2em] text-indigo-500/70 dark:text-indigo-400/70 uppercase">Entrada principal</p>
                                    <h3 class="text-sm sm:text-base font-black text-slate-800 dark:text-white leading-tight">Scanner inteligente</h3>
                                </div>
                                <div class="hidden sm:flex items-center gap-1.5">
                                    <span class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] font-bold border
                                        {{ match($activeMode) {
                                            'consulta'   => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/50',
                                            'preco'      => 'bg-pink-50 dark:bg-pink-900/20 text-pink-700 dark:text-pink-300 border-pink-200/60 dark:border-pink-800/50',
                                            'estoque'    => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/50',
                                            'inventario' => 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 border-orange-200/60 dark:border-orange-800/50',
                                            'venda'      => 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/50',
                                            'vincular'   => 'bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 border-cyan-200/60 dark:border-cyan-800/50',
                                            default      => 'bg-slate-50 text-slate-500 border-slate-200',
                                        } }}">
                                        <i class="{{ match($activeMode) {
                                            'consulta'   => 'fas fa-search',
                                            'preco'      => 'fas fa-tag',
                                            'estoque'    => 'fas fa-boxes-stacked',
                                            'inventario' => 'fas fa-clipboard-list',
                                            'venda'      => 'fas fa-cart-shopping',
                                            'vincular'   => 'fas fa-link',
                                            default      => 'fas fa-circle',
                                        } }}"></i>
                                        {{ match($activeMode) {
                                            'consulta'   => 'Consultar',
                                            'preco'      => 'Ver Preço',
                                            'estoque'    => 'Estoque',
                                            'inventario' => 'Inventário',
                                            'venda'      => 'Venda',
                                            'vincular'   => 'Vincular',
                                            default      => $activeMode,
                                        } }}
                                    </span>
                                </div>
                            </div>

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

                                <div x-show="imageDebugLogs.length" x-transition class="mt-3">
                                    <button @click="showLogModal = true" type="button"
                                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/70 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-all hover:scale-[1.01]">
                                        <i class="fas fa-list-check"></i>
                                        <span>Ver Log da análise</span>
                                        <span class="ml-auto px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[10px] font-black" x-text="imageDebugLogs.length + ' linhas'"></span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>{{-- end scanner col --}}

                {{-- ─────────── RESULTADO / VINCULAR (≈ 1/2 da tela) ─────────── --}}
                <div class="md:col-span-7 lg:col-span-6 space-y-4">

                    @if($activeMode === 'vincular')
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border-2 border-cyan-400/50 dark:border-cyan-600/50 shadow-xl shadow-cyan-500/8 overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(6,182,212,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(6,182,212,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                        <div class="relative p-4 sm:p-5 space-y-4">
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                                        <i class="fas fa-link text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black tracking-[0.18em] text-cyan-500/70 dark:text-cyan-400/70 uppercase">Vinculação inteligente</p>
                                        <h3 class="font-black text-slate-800 dark:text-white text-base">Escolha o produto correto para receber o código</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Grid compacto inspirado no índice de produtos</p>
                                    </div>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-2xl border border-cyan-200/60 dark:border-cyan-800/40 bg-cyan-50/70 dark:bg-cyan-900/20 px-3 py-2 text-[11px] font-bold text-cyan-700 dark:text-cyan-300 self-start">
                                    <i class="fas fa-grid-2"></i>
                                    {{ count($linkCandidates) > 0 ? count($linkCandidates) : count($productsWithoutBarcode) }} opções visíveis
                                </div>
                            </div>

                            @if($lastScannedBarcode)
                            <div class="p-4 rounded-[24px] border border-cyan-200 dark:border-cyan-800 flex items-center gap-3" style="background:linear-gradient(135deg,rgba(6,182,212,.08),rgba(59,130,246,.06))">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 flex-shrink-0">
                                    <i class="fas fa-barcode text-white text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] text-cyan-600 dark:text-cyan-400 uppercase font-black tracking-wider">Código escaneado</p>
                                    <p class="text-lg sm:text-xl font-black font-mono text-slate-800 dark:text-white break-all">{{ $lastScannedBarcode }}</p>
                                </div>
                            </div>
                            @else
                            <div class="p-5 rounded-[24px] bg-slate-50 dark:bg-slate-800/60 border-2 border-dashed border-slate-200 dark:border-slate-700 text-center">
                                <i class="fas fa-barcode text-slate-300 dark:text-slate-600 text-3xl mb-2"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Escaneie um código primeiro</p>
                            </div>
                            @endif

                            <div class="relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-slate-400 text-sm"></i>
                                </div>
                                <input type="text" wire:model.live.debounce.300ms="linkSearchTerm" placeholder="Buscar produto..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all placeholder:text-slate-400" />
                            </div>

                            @if(count($linkCandidates) > 0)
                            <div class="link-candidates-grid vincular-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 ultrawind:grid-cols-8 gap-3 max-h-[66vh] overflow-y-auto pr-1">
                                @foreach($linkCandidates as $candidate)
                                <div class="product-card-modern">

                                    <!-- Botão vincular flutuante -->
                                    @if($lastScannedBarcode)
                                    <div class="btn-action-group">
                                        <button wire:click="linkBarcodeToProduct({{ $candidate['id'] }})" wire:confirm="Vincular {{ $lastScannedBarcode }} a '{{ $candidate['name'] }}'?" class="btn btn-primary" title="Vincular código">
                                            <i class="bi bi-link-45deg"></i>
                                        </button>
                                    </div>
                                    @endif

                                    <!-- Área da imagem com badges -->
                                    <div class="product-img-area">
                                        <img src="{{ $candidate['image'] ? asset('storage/products/' . $candidate['image']) : asset('storage/products/product-placeholder.png') }}" class="product-img" alt="{{ $candidate['name'] }}">

                                        @if(!$candidate['barcode'])
                                        <span class="no-barcode-badge"><i class="bi bi-upc"></i> s/cod</span>
                                        @endif

                                        <!-- Código do produto -->
                                        <span class="badge-product-code" title="Código do Produto">
                                            <i class="bi bi-upc-scan"></i> {{ $candidate['product_code'] ?? '—' }}
                                        </span>

                                        <!-- Quantidade em estoque -->
                                        <span class="badge-quantity" title="Quantidade em Estoque">
                                            <i class="bi bi-stack"></i> {{ $candidate['stock_quantity'] ?? 0 }}
                                        </span>

                                        <!-- Ícone da categoria -->
                                        <div class="category-icon-wrapper">
                                            <i class="{{ $candidate['category_icon'] ?? 'bi bi-box' }} category-icon"></i>
                                        </div>
                                    </div>

                                    <!-- Conteúdo -->
                                    <div class="card-body">
                                        <div class="product-title" title="{{ $candidate['name'] }}">
                                            {{ ucwords($candidate['name']) }}
                                        </div>

                                        <!-- Área de preços -->
                                        <div class="price-area mt-3">
                                            <div class="flex flex-col gap-2">
                                                <span class="badge-price" title="Preço de Custo">
                                                    <i class="bi bi-tag"></i>
                                                    R$ {{ number_format($candidate['price'], 2, ',', '.') }}
                                                </span>
                                                <span class="badge-price-sale" title="Preço de Venda">
                                                    <i class="bi bi-currency-dollar"></i>
                                                    R$ {{ number_format($candidate['price_sale'], 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endforeach
                            </div>
                            @elseif(!empty($linkSearchTerm))
                            <p class="text-sm text-slate-400 text-center py-4">Nenhum resultado para "{{ $linkSearchTerm }}"</p>
                            @endif

                            @if(count($productsWithoutBarcode) > 0 && empty($linkSearchTerm))
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 space-y-3">
                                <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider"><i class="bi bi-exclamation-triangle mr-1"></i>Sem Código ({{ count($productsWithoutBarcode) }})</p>
                                <div class="vincular-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 ultrawind:grid-cols-8 gap-3 max-h-[56vh] overflow-y-auto pr-1">
                                    @foreach($productsWithoutBarcode as $noBarcodeProduct)
                                    <div class="product-card-modern">

                                        <!-- Botão vincular flutuante -->
                                        @if($lastScannedBarcode)
                                        <div class="btn-action-group">
                                            <button wire:click="linkBarcodeToProduct({{ $noBarcodeProduct['id'] }})" wire:confirm="Vincular {{ $lastScannedBarcode }} a '{{ $noBarcodeProduct['name'] }}'?" class="btn btn-primary" title="Vincular código">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                        </div>
                                        @endif

                                        <!-- Área da imagem com badges -->
                                        <div class="product-img-area">
                                            <img src="{{ $noBarcodeProduct['image'] ? asset('storage/products/' . $noBarcodeProduct['image']) : asset('storage/products/product-placeholder.png') }}" class="product-img" alt="{{ $noBarcodeProduct['name'] }}">

                                            <span class="no-barcode-badge"><i class="bi bi-upc"></i> s/cod</span>

                                            <!-- Código do produto -->
                                            <span class="badge-product-code" title="Código do Produto">
                                                <i class="bi bi-upc-scan"></i> {{ $noBarcodeProduct['product_code'] ?? '—' }}
                                            </span>

                                            <!-- Quantidade em estoque -->
                                            <span class="badge-quantity" title="Quantidade em Estoque">
                                                <i class="bi bi-stack"></i> {{ $noBarcodeProduct['stock_quantity'] ?? 0 }}
                                            </span>

                                            <!-- Ícone da categoria -->
                                            <div class="category-icon-wrapper">
                                                <i class="{{ $noBarcodeProduct['category_icon'] ?? 'bi bi-box' }} category-icon"></i>
                                            </div>
                                        </div>

                                        <!-- Conteúdo -->
                                        <div class="card-body">
                                            <div class="product-title" title="{{ $noBarcodeProduct['name'] }}">
                                                {{ ucwords($noBarcodeProduct['name']) }}
                                            </div>

                                            <!-- Área de preços -->
                                            <div class="price-area mt-3">
                                                <div class="flex flex-col gap-2">
                                                    <span class="badge-price" title="Preço de Custo">
                                                        <i class="bi bi-tag"></i>
                                                        R$ {{ number_format($noBarcodeProduct['price'], 2, ',', '.') }}
                                                    </span>
                                                    <span class="badge-price-sale" title="Preço de Venda">
                                                        <i class="bi bi-currency-dollar"></i>
                                                        R$ {{ number_format($noBarcodeProduct['price_sale'], 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @elseif($onlineLoading || $onlineResult || $onlineError || $foundProduct || $searchMessage)
                    {{-- ##RESULT_PLACEHOLDER## --}}
                    @else
                    {{-- ##PLACEHOLDER_CARD## --}}
                    @endif

                    {{-- ##QTY_INVENTORY_SALE_PLACEHOLDER## --}}

                </div>{{-- end result col --}}

                {{-- ─────────── HISTÓRICO (≈ 1/4 da tela) ─────────── --}}
                <div class="md:col-span-12 lg:col-span-3">

                    {{-- ═══ ESTATÍSTICAS ═══ --}}
                    

                    {{-- ═══ HISTÓRICO ═══ --}}
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border border-slate-200/60 dark:border-slate-700/60 shadow-lg overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(99,102,241,.3) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.3) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.02;"></div>
                        <div class="relative p-4">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black tracking-[0.22em] text-slate-500/70 dark:text-slate-400/70 uppercase">Linha do tempo</p>
                                    <h3 class="text-base font-black text-slate-800 dark:text-white">Últimas leituras</h3>
                                </div>
                            </div>
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
            </div>{{-- end workspace grid --}}

            {{-- ═══════════════════════════════════════════════════════════════
                 ZONA DE RESULTADOS: Produto Encontrado + Resultado Online
                 (2 colunas lado a lado quando ambos existem)
            ═══════════════════════════════════════════════════════════════ --}}
            @php
                $hasFoundProduct = (bool) $foundProduct;
                $hasOnlineData   = $onlineLoading || $onlineResult || $onlineError;
            @endphp
            @if($hasFoundProduct || $hasOnlineData)
            <div class="grid grid-cols-1 {{ $hasFoundProduct && $hasOnlineData ? 'lg:grid-cols-2' : '' }} gap-4 items-start">

                {{-- ─── PRODUTO ENCONTRADO ─── --}}
                @if($foundProduct)
                <div>
                <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border-2 border-emerald-400/50 dark:border-emerald-600/50 shadow-2xl shadow-emerald-500/10 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(16,185,129,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(16,185,129,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                    {{-- Hero banner --}}
                    <div class="relative h-28 sm:h-36 overflow-hidden" style="background:linear-gradient(135deg,rgba(16,185,129,.15) 0%,rgba(20,184,166,.08) 50%,transparent 100%)">
                        <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full" style="background:radial-gradient(circle,rgba(16,185,129,.12) 0%,transparent 70%)"></div>
                        <div class="absolute -bottom-8 right-32 w-32 h-32 rounded-full" style="background:radial-gradient(circle,rgba(20,184,166,.08) 0%,transparent 70%)"></div>
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
                </div>{{-- end product col --}}
                @endif{{-- end $foundProduct --}}

                {{-- ─── RESULTADO ONLINE ─── --}}
                @if($onlineLoading || $onlineResult || $onlineError)
                <div>
                @if($onlineLoading)
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border border-blue-200/60 dark:border-blue-700/50 shadow-lg overflow-hidden">
                        <div class="relative p-4 sm:p-5 flex items-center gap-4">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                                <i class="fas fa-globe text-white animate-pulse"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-black tracking-[0.18em] text-blue-500/70 dark:text-blue-400/70 uppercase">Busca online</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-white">Consultando bases externas para enriquecer o produto</p>
                                <p class="text-xs text-slate-400 mt-1">Open Food Facts, Beauty Facts, Products Facts e UPC Item DB</p>
                            </div>
                            <i class="fas fa-spinner fa-spin text-blue-500 text-lg"></i>
                        </div>
                    </div>
                    @endif

                    @if($onlineResult)
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border-2 border-blue-400/40 dark:border-blue-600/40 shadow-xl shadow-blue-500/8 overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(59,130,246,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                        <div class="relative p-4 sm:p-5 space-y-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30 flex-shrink-0">
                                        <i class="fas fa-globe text-white text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-black tracking-[0.18em] text-blue-500/70 dark:text-blue-400/70 uppercase">Busca online</p>
                                        <h3 class="text-base sm:text-lg font-black text-slate-800 dark:text-white truncate">Informações encontradas na internet</h3>
                                        <span class="inline-flex mt-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ $onlineResult['source'] }}</span>
                                    </div>
                                </div>
                                @if($foundProduct)
                                <button wire:click="applyOnlineData" wire:loading.attr="disabled" wire:target="applyOnlineData" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-2xl shadow-lg shadow-blue-500/25 transition-all hover:scale-105 flex items-center gap-1.5 self-start">
                                    <i wire:loading.remove wire:target="applyOnlineData" class="fas fa-download"></i>
                                    <i wire:loading wire:target="applyOnlineData" class="fas fa-spinner fa-spin"></i>
                                    Aplicar dados
                                </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-[140px,1fr] gap-4">
                                <div class="space-y-3">
                                    <div class="w-full h-32 rounded-[22px] overflow-hidden bg-slate-100 dark:bg-slate-700 border-2 border-blue-200/70 dark:border-blue-800/70 shadow-lg">
                                        @if($onlineResult['image_url'])
                                        <img src="{{ $onlineResult['image_url'] }}" alt="Produto" class="w-full h-full object-contain" />
                                        @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                            <i class="fas fa-image text-3xl"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-slate-50/80 dark:bg-slate-800/60 px-3 py-2.5">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1">Código consultado</p>
                                        <p class="text-xs font-mono font-black text-slate-700 dark:text-slate-200 break-all">{{ $onlineResult['barcode'] }}</p>
                                    </div>
                                </div>

                                <div class="space-y-3 min-w-0">
                                    @if($onlineResult['name'])
                                    <div>
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">Nome</p>
                                        <p class="text-sm sm:text-base font-black text-slate-800 dark:text-white leading-tight">{{ $onlineResult['name'] }}</p>
                                    </div>
                                    @endif

                                    <div class="grid grid-cols-2 xl:grid-cols-4 gap-2.5">
                                        @if($onlineResult['brand'])
                                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-200/50 dark:border-slate-700/40">
                                            <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1"><i class="fas fa-building mr-1"></i>Marca</p>
                                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 line-clamp-2">{{ $onlineResult['brand'] }}</p>
                                        </div>
                                        @endif
                                        @if($onlineResult['categories'])
                                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-200/50 dark:border-slate-700/40 xl:col-span-2">
                                            <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1"><i class="fas fa-folder mr-1"></i>Categoria</p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $onlineResult['categories'] }}</p>
                                        </div>
                                        @endif
                                        @if($onlineResult['quantity'])
                                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-200/50 dark:border-slate-700/40">
                                            <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1"><i class="fas fa-weight-hanging mr-1"></i>Quantidade</p>
                                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $onlineResult['quantity'] }}</p>
                                        </div>
                                        @endif
                                        @if($onlineResult['countries'])
                                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-200/50 dark:border-slate-700/40">
                                            <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1"><i class="fas fa-globe mr-1"></i>País</p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $onlineResult['countries'] }}</p>
                                        </div>
                                        @endif
                                        @if($onlineResult['nutriscore'])
                                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-200/50 dark:border-slate-700/40">
                                            <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1"><i class="fas fa-leaf mr-1"></i>Nutriscore</p>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase {{ in_array(strtoupper($onlineResult['nutriscore']), ['A','B']) ? 'bg-emerald-100 text-emerald-700' : (in_array(strtoupper($onlineResult['nutriscore']), ['C']) ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ strtoupper($onlineResult['nutriscore']) }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    @if($onlineResult['description'])
                                    <div class="rounded-2xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/80 dark:bg-slate-800/50 px-3 py-3">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1">Descrição</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $onlineResult['description'] }}</p>
                                    </div>
                                    @endif

                                    @if($onlineResult['ingredients'])
                                    <div class="rounded-2xl border border-slate-200/60 dark:border-slate-700/60 bg-slate-50/80 dark:bg-slate-800/50 px-3 py-3">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider mb-1">Ingredientes</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3">{{ $onlineResult['ingredients'] }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($onlineDebug))
                            <div class="rounded-2xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/70 dark:bg-blue-900/10 p-3">
                                <p class="text-[10px] font-black tracking-[0.18em] uppercase text-blue-500/70 dark:text-blue-400/70 mb-2">Rastro da consulta</p>
                                <div class="space-y-1 max-h-36 overflow-y-auto">
                                    @foreach($onlineDebug as $debugLine)
                                    <p class="text-[11px] text-slate-600 dark:text-slate-300 font-mono">• {{ $debugLine }}</p>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if(!$foundProduct)
                            <div class="rounded-2xl overflow-hidden border border-amber-200/80 dark:border-amber-800/60 shadow-md">
                                <div class="p-4 flex items-center gap-3" style="background:linear-gradient(135deg,rgba(245,158,11,.12),rgba(234,179,8,.06))">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-500/25">
                                        <i class="fas fa-lightbulb text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Produto não cadastrado</p>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Encontrado online mas não está no seu catálogo</p>
                                    </div>
                                </div>
                                <div class="p-4 bg-white/90 dark:bg-slate-800/80 border-t border-amber-100 dark:border-amber-900/30 flex flex-col sm:flex-row gap-2">
                                    <a href="{{ route('products.create') }}"
                                       class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl font-bold text-sm text-white shadow-lg shadow-indigo-500/25 hover:scale-[1.02] transition-all"
                                       style="background:linear-gradient(135deg,#6366f1,#7c3aed)">
                                        <i class="fas fa-plus"></i>
                                        Cadastrar produto
                                    </a>
                                    @if($activeMode !== 'vincular')
                                    <button wire:click="setMode('vincular')"
                                            class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl font-bold text-sm text-cyan-700 dark:text-cyan-300 bg-cyan-50 dark:bg-cyan-900/30 hover:bg-cyan-100 dark:hover:bg-cyan-900/40 transition-all border border-cyan-200 dark:border-cyan-800">
                                        <i class="fas fa-link"></i>
                                        Vincular a produto existente
                                    </button>
                                    @endif
                                </div>
                            </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($onlineError)
                    <div class="bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border border-slate-200/60 dark:border-slate-700/60 shadow-sm overflow-hidden">
                        {{-- Cabeçalho do erro --}}
                        <div class="p-4 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-globe-slash text-slate-400 text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $onlineError }}</p>
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                    As bases consultadas (Open Food Facts, Open Beauty Facts, Open Products Facts e UPC Item DB) são gratuitas e focadas em produtos alimentícios e de beleza internacionais.
                                    <strong class="text-amber-600 dark:text-amber-400">Produtos locais, artesanais ou fabricantes menores frequentemente não constam nessas bases.</strong>
                                </p>
                            </div>
                        </div>
                        {{-- Ações --}}
                        <div class="px-4 pb-4 flex flex-wrap gap-2">
                            <button wire:click="searchOnline('{{ $lastScannedBarcode }}')" wire:loading.attr="disabled" wire:target="searchOnline" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-all">
                                <i wire:loading.remove wire:target="searchOnline" class="fas fa-rotate-right"></i>
                                <i wire:loading wire:target="searchOnline" class="fas fa-spinner fa-spin"></i>
                                Tentar novamente
                            </button>
                            @if($activeMode !== 'vincular')
                            <button wire:click="setMode('vincular')" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 text-xs font-bold hover:bg-cyan-100 dark:hover:bg-cyan-900/30 transition-all">
                                <i class="fas fa-link"></i> Vincular manualmente
                            </button>
                            @endif
                        </div>
                        {{-- Fontes consultadas (colapsável) --}}
                        @if(!empty($onlineDebug))
                        <details class="group">
                            <summary class="cursor-pointer px-4 py-2.5 border-t border-slate-100 dark:border-slate-700/70 flex items-center gap-2 text-[10px] font-black tracking-[0.15em] uppercase text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors list-none select-none">
                                <i class="fas fa-chevron-right text-[9px] transition-transform group-open:rotate-90"></i>
                                Fontes consultadas ({{ count($onlineDebug) }} linhas)
                            </summary>
                            <div class="px-4 pb-4 pt-2 space-y-1 max-h-44 overflow-y-auto bg-slate-50/80 dark:bg-slate-800/40">
                                @foreach($onlineDebug as $debugLine)
                                <div class="flex items-start gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-slate-400 flex-shrink-0"></span>
                                    <p class="text-[11px] font-mono text-slate-600 dark:text-slate-300 leading-relaxed">{{ $debugLine }}</p>
                                </div>
                                @endforeach
                            </div>
                        </details>
                        @endif
                    </div>
                    @endif{{-- end $onlineError --}}
                </div>{{-- end online col --}}
                @endif{{-- end $hasOnlineData --}}
            </div>{{-- end results grid --}}
            @endif{{-- end results zone --}}

            {{-- ═══ VINCULAR (largura total) ═══ --}}
            @if($activeMode === 'vincular')
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border-2 border-cyan-400/50 dark:border-cyan-600/50 shadow-xl shadow-cyan-500/8 overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(6,182,212,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(6,182,212,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                        <div class="relative p-4 sm:p-5 space-y-4">
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                                        <i class="fas fa-link text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black tracking-[0.18em] text-cyan-500/70 dark:text-cyan-400/70 uppercase">Vinculação inteligente</p>
                                        <h3 class="font-black text-slate-800 dark:text-white text-base">Escolha o produto correto para receber o código</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Grid compacto inspirado no índice de produtos</p>
                                    </div>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-2xl border border-cyan-200/60 dark:border-cyan-800/40 bg-cyan-50/70 dark:bg-cyan-900/20 px-3 py-2 text-[11px] font-bold text-cyan-700 dark:text-cyan-300 self-start">
                                    <i class="fas fa-grid-2"></i>
                                    {{ count($linkCandidates) > 0 ? count($linkCandidates) : count($productsWithoutBarcode) }} opções visíveis
                                </div>
                            </div>

                            @if($lastScannedBarcode)
                            <div class="p-4 rounded-[24px] border border-cyan-200 dark:border-cyan-800 flex items-center gap-3" style="background:linear-gradient(135deg,rgba(6,182,212,.08),rgba(59,130,246,.06))">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 flex-shrink-0">
                                    <i class="fas fa-barcode text-white text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] text-cyan-600 dark:text-cyan-400 uppercase font-black tracking-wider">Código escaneado</p>
                                    <p class="text-lg sm:text-xl font-black font-mono text-slate-800 dark:text-white break-all">{{ $lastScannedBarcode }}</p>
                                </div>
                            </div>
                            @else
                            <div class="p-5 rounded-[24px] bg-slate-50 dark:bg-slate-800/60 border-2 border-dashed border-slate-200 dark:border-slate-700 text-center">
                                <i class="fas fa-barcode text-slate-300 dark:text-slate-600 text-3xl mb-2"></i>
                                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Escaneie um código primeiro</p>
                            </div>
                            @endif

                            <div class="relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-slate-400 text-sm"></i>
                                </div>
                                <input type="text" wire:model.live.debounce.300ms="linkSearchTerm" placeholder="Buscar produto..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all placeholder:text-slate-400" />
                            </div>

                            @if(count($linkCandidates) > 0)
                            <div class="link-candidates-grid vincular-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 ultrawind:grid-cols-8 gap-3 max-h-[66vh] overflow-y-auto pr-1">
                                @foreach($linkCandidates as $candidate)
                                <div class="product-card-modern">

                                    <!-- Botão vincular flutuante -->
                                    @if($lastScannedBarcode)
                                    <div class="btn-action-group">
                                        <button wire:click="linkBarcodeToProduct({{ $candidate['id'] }})" wire:confirm="Vincular {{ $lastScannedBarcode }} a '{{ $candidate['name'] }}'?" class="btn btn-primary" title="Vincular código">
                                            <i class="bi bi-link-45deg"></i>
                                        </button>
                                    </div>
                                    @endif

                                    <!-- Área da imagem com badges -->
                                    <div class="product-img-area">
                                        <img src="{{ $candidate['image'] ? asset('storage/products/' . $candidate['image']) : asset('storage/products/product-placeholder.png') }}" class="product-img" alt="{{ $candidate['name'] }}">

                                        @if(!$candidate['barcode'])
                                        <span class="no-barcode-badge"><i class="bi bi-upc"></i> s/cod</span>
                                        @endif

                                        <!-- Código do produto -->
                                        <span class="badge-product-code" title="Código do Produto">
                                            <i class="bi bi-upc-scan"></i> {{ $candidate['product_code'] ?? '—' }}
                                        </span>

                                        <!-- Quantidade em estoque -->
                                        <span class="badge-quantity" title="Quantidade em Estoque">
                                            <i class="bi bi-stack"></i> {{ $candidate['stock_quantity'] ?? 0 }}
                                        </span>

                                        <!-- Ícone da categoria -->
                                        <div class="category-icon-wrapper">
                                            <i class="{{ $candidate['category_icon'] ?? 'bi bi-box' }} category-icon"></i>
                                        </div>
                                    </div>

                                    <!-- Conteúdo -->
                                    <div class="card-body">
                                        <div class="product-title" title="{{ $candidate['name'] }}">
                                            {{ ucwords($candidate['name']) }}
                                        </div>

                                        <!-- Área de preços -->
                                        <div class="price-area mt-3">
                                            <div class="flex flex-col gap-2">
                                                <span class="badge-price" title="Preço de Custo">
                                                    <i class="bi bi-tag"></i>
                                                    R$ {{ number_format($candidate['price'], 2, ',', '.') }}
                                                </span>
                                                <span class="badge-price-sale" title="Preço de Venda">
                                                    <i class="bi bi-currency-dollar"></i>
                                                    R$ {{ number_format($candidate['price_sale'], 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endforeach
                            </div>
                            @elseif(!empty($linkSearchTerm))
                            <p class="text-sm text-slate-400 text-center py-4">Nenhum resultado para "{{ $linkSearchTerm }}"</p>
                            @endif

                            @if(count($productsWithoutBarcode) > 0 && empty($linkSearchTerm))
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 space-y-3">
                                <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider"><i class="bi bi-exclamation-triangle mr-1"></i>Sem Código ({{ count($productsWithoutBarcode) }})</p>
                                <div class="vincular-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 ultrawind:grid-cols-8 gap-3 max-h-[56vh] overflow-y-auto pr-1">
                                    @foreach($productsWithoutBarcode as $noBarcodeProduct)
                                    <div class="product-card-modern">

                                        <!-- Botão vincular flutuante -->
                                        @if($lastScannedBarcode)
                                        <div class="btn-action-group">
                                            <button wire:click="linkBarcodeToProduct({{ $noBarcodeProduct['id'] }})" wire:confirm="Vincular {{ $lastScannedBarcode }} a '{{ $noBarcodeProduct['name'] }}'?" class="btn btn-primary" title="Vincular código">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                        </div>
                                        @endif

                                        <!-- Área da imagem com badges -->
                                        <div class="product-img-area">
                                            <img src="{{ $noBarcodeProduct['image'] ? asset('storage/products/' . $noBarcodeProduct['image']) : asset('storage/products/product-placeholder.png') }}" class="product-img" alt="{{ $noBarcodeProduct['name'] }}">

                                            <span class="no-barcode-badge"><i class="bi bi-upc"></i> s/cod</span>

                                            <!-- Código do produto -->
                                            <span class="badge-product-code" title="Código do Produto">
                                                <i class="bi bi-upc-scan"></i> {{ $noBarcodeProduct['product_code'] ?? '—' }}
                                            </span>

                                            <!-- Quantidade em estoque -->
                                            <span class="badge-quantity" title="Quantidade em Estoque">
                                                <i class="bi bi-stack"></i> {{ $noBarcodeProduct['stock_quantity'] ?? 0 }}
                                            </span>

                                            <!-- Ícone da categoria -->
                                            <div class="category-icon-wrapper">
                                                <i class="{{ $noBarcodeProduct['category_icon'] ?? 'bi bi-box' }} category-icon"></i>
                                            </div>
                                        </div>

                                        <!-- Conteúdo -->
                                        <div class="card-body">
                                            <div class="product-title" title="{{ $noBarcodeProduct['name'] }}">
                                                {{ ucwords($noBarcodeProduct['name']) }}
                                            </div>

                                            <!-- Área de preços -->
                                            <div class="price-area mt-3">
                                                <div class="flex flex-col gap-2">
                                                    <span class="badge-price" title="Preço de Custo">
                                                        <i class="bi bi-tag"></i>
                                                        R$ {{ number_format($noBarcodeProduct['price'], 2, ',', '.') }}
                                                    </span>
                                                    <span class="badge-price-sale" title="Preço de Venda">
                                                        <i class="bi bi-currency-dollar"></i>
                                                        R$ {{ number_format($noBarcodeProduct['price_sale'], 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ═══ INVENTÁRIO ═══ --}}
                    @if($activeMode === 'inventario' && count($inventoryItems) > 0)
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border border-orange-300/60 dark:border-orange-700/60 shadow-lg overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(249,115,22,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(249,115,22,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                        <div class="relative p-4 sm:p-5">
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

                    {{-- ═══ VENDA ═══ --}}
                    @if($activeMode === 'venda' && count($saleItems) > 0)
                    <div class="relative bg-white/95 dark:bg-slate-900/90 backdrop-blur-md rounded-[28px] border border-purple-300/60 dark:border-purple-700/60 shadow-lg overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(168,85,247,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(168,85,247,.35) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.025;"></div>
                        <div class="relative p-4 sm:p-5">
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
        </div>{{-- end content wrapper --}}
    </div>{{-- end main wrapper --}}

    {{-- ========== STYLES ========== --}}
    <style>
        @keyframes scan-line {
            0% {
                top: 10%;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                top: 90%;
                opacity: 0;
            }
        }

        .animate-scan-line {
            animation: scan-line 2s ease-in-out infinite;
            position: absolute;
        }

        .safe-area-top {
            padding-top: env(safe-area-inset-top, 0px);
        }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        /* Oculta tab bar e FAB durante a câmera fullscreen */
        body.camera-active .mobile-bottom-tabbar,
        body.camera-active #mobileFabSheet,
        body.camera-active #mobileMoreSheet {
            display: none !important;
        }
    </style>

    {{-- ========== CDN: html5-qrcode ========== --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.7.4/dist/quagga.min.js"></script>

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

                // Tips modal
                showTipsModal: false,
                tipStep: 1,
                tipTotal: 3,

                // Scan mode
                scanMode: 'manual',

                // Camera
                cameraActive: false,
                cameraError: null,
                lastCameraScan: null,
                pendingCode: null,
                cameraFacing: 'environment',
                _html5QrCode: null,
                _scanCooldown: false,
                photoSnapping: false,
                // getUserMedia direto (substitui html5-qrcode para live scan)
                _stream: null,
                _video: null,
                _scanLoopId: null,
                _barcodeDetector: null,
                _scanCanvas: null,

                // Image
                imagePreview: null,
                imageScanning: false,
                imageDragOver: false,
                imageResult: null,
                imageResultSuccess: false,
                imageDebugLogs: [],
                showLogModal: false,
                modeOpen: false,
                _imageFile: null,

                setScanMode(mode) {
                    if (this.scanMode === 'camera' && mode !== 'camera') {
                        this.stopCamera();
                        document.body.classList.remove('camera-active');
                    }
                    this.scanMode = mode;
                    if (mode === 'manual') {
                        document.body.classList.remove('camera-active');
                        this.$nextTick(() => {
                            var input = document.getElementById('barcode-input');
                            if (input) input.focus();
                        });
                    }
                    if (mode === 'camera') {
                        document.body.classList.add('camera-active');
                        this.$nextTick(() => {
                            this.startCamera();
                        });
                    }
                },

                // ------ CAMERA (getUserMedia direto + BarcodeDetector nativo) ------
                async startCamera() {
                    this.cameraError = null;
                    this.lastCameraScan = null;
                    this._stopStream();

                    // Aguarda o elemento estar visível e com dimensões reais
                    await new Promise(function(r) { setTimeout(r, 400); });

                    try {
                        // Verifica suporte a getUserMedia
                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            this.cameraError = 'Seu browser não suporta acesso à câmera. No iOS use Safari.';
                            return;
                        }

                        var constraints = {
                            audio: false,
                            video: {
                                facingMode: this.cameraFacing === 'environment'
                                    ? { ideal: 'environment' }
                                    : { ideal: 'user' },
                                width:  { ideal: 1280 },
                                height: { ideal: 720 }
                            }
                        };

                        this._stream = await navigator.mediaDevices.getUserMedia(constraints);

                        var viewport = document.getElementById('camera-scanner-viewport');
                        if (!viewport) {
                            this.cameraError = 'Viewport da câmera não encontrado.';
                            this._stopStream();
                            return;
                        }

                        viewport.innerHTML = '';

                        var video = document.createElement('video');
                        // CRÍTICO para iOS Safari — sem estes atributos o vídeo não toca inline
                        video.setAttribute('autoplay', '');
                        video.setAttribute('muted', '');
                        video.setAttribute('playsinline', '');
                        video.setAttribute('webkit-playsinline', '');
                        video.muted = true;
                        video.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
                        video.srcObject = this._stream;
                        viewport.appendChild(video);
                        this._video = video;

                        // Aguarda metadados e inicia playback
                        await new Promise(function(resolve) {
                            video.addEventListener('loadedmetadata', resolve, { once: true });
                            setTimeout(resolve, 3000); // failsafe
                        });
                        await video.play();

                        // Tenta aplicar zoom 2.5x (iOS 17+, Chrome Android)
                        try {
                            var track = this._stream.getVideoTracks()[0];
                            if (track) {
                                var caps = track.getCapabilities ? track.getCapabilities() : {};
                                if (caps.zoom) {
                                    var zoomVal = Math.min(2.5, caps.zoom.max || 2.5);
                                    await track.applyConstraints({ advanced: [{ zoom: zoomVal }] });
                                }
                            }
                        } catch (ze) { /* zoom não suportado neste dispositivo */ }

                        // Inicializa BarcodeDetector nativo (iOS 17+, Chrome/Edge)
                        this._barcodeDetector = null;
                        if ('BarcodeDetector' in window) {
                            try {
                                this._barcodeDetector = new BarcodeDetector({
                                    formats: ['ean_13','ean_8','upc_a','upc_e','code_128',
                                              'code_39','code_93','itf','qr_code','data_matrix']
                                });
                            } catch (e) { this._barcodeDetector = null; }
                        }

                        this.cameraActive = true;
                        this._startScanLoop();

                    } catch (err) {
                        console.error('Camera error:', err);
                        this._stopStream();
                        var errName = (err && err.name)    ? err.name    : '';
                        var errMsg  = (err && err.message) ? err.message : (typeof err === 'string' ? err : '');
                        var combined = errName + ' ' + errMsg;
                        var msg = 'Não foi possível acessar a câmera.';
                        if (combined.match(/NotAllowed|Permission|denied/i)) {
                            msg = 'Permissão negada. Permita a câmera nas configurações do Safari/browser.';
                        } else if (combined.match(/NotFound|DevicesNotFound|device not found/i)) {
                            msg = 'Nenhuma câmera encontrada neste dispositivo.';
                        } else if (combined.match(/NotReadable|Could not start|TrackStart/i)) {
                            msg = 'Câmera em uso por outro app. Feche-o e tente novamente.';
                        } else if (combined.match(/Overconstrained/i)) {
                            msg = 'Configuração de câmera não suportada. Recarregue e tente novamente.';
                        } else if (combined.match(/https|secure|security/i)) {
                            msg = 'A câmera exige HTTPS. Acesse o app via https://';
                        }
                        this.cameraError = msg;
                        this.cameraActive = false;
                    }
                },

                // Loop de detecção contínua via RAF
                _startScanLoop() {
                    var self = this;
                    var lastScan = 0;
                    var INTERVAL_MS = 350; // escaneia ~3x por segundo

                    function loop() {
                        if (!self.cameraActive || !self._video) return;
                        var now = Date.now();
                        if (now - lastScan >= INTERVAL_MS) {
                            lastScan = now;
                            self._scanFrame();
                        }
                        self._scanLoopId = requestAnimationFrame(loop);
                    }
                    self._scanLoopId = requestAnimationFrame(loop);
                },

                _scanFrame() {
                    var self = this;
                    var video = this._video;
                    if (!video || !video.videoWidth || !video.videoHeight || this._scanCooldown) return;

                    // Reutiliza canvas persistente para evitar alloc a cada frame
                    if (!this._scanCanvas) {
                        this._scanCanvas = document.createElement('canvas');
                    }
                    var canvas = this._scanCanvas;
                    // Redimensiona somente quando necessário
                    if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                        canvas.width  = video.videoWidth;
                        canvas.height = video.videoHeight;
                    }
                    var ctx = canvas.getContext('2d', { willReadFrequently: true });
                    ctx.drawImage(video, 0, 0);

                    if (this._barcodeDetector) {
                        // BarcodeDetector nativo — iOS 17+, Chrome
                        // Usar canvas (não video nem ImageBitmap) é mais confiável no iOS WKWebView
                        this._barcodeDetector.detect(canvas).then(function(results) {
                            if (results && results.length > 0 && results[0].rawValue) {
                                self.onCameraCodeDetected(results[0].rawValue);
                            }
                        }).catch(function() {});
                        return;
                    }

                    // Fallback: Quagga2 (browsers sem BarcodeDetector)
                    if (window.Quagga && window.Quagga.decodeSingle) {
                        canvas.toBlob(function(blob) {
                            if (!blob) return;
                            var url = URL.createObjectURL(blob);
                            window.Quagga.decodeSingle({
                                src: url,
                                numOfWorkers: 0,
                                locate: true,
                                inputStream: { size: 800 },
                                decoder: { readers: ['ean_reader','ean_8_reader','upc_reader',
                                    'upc_e_reader','code_128_reader','code_39_reader'] }
                            }, function(result) {
                                URL.revokeObjectURL(url);
                                if (result && result.codeResult && result.codeResult.code) {
                                    self.onCameraCodeDetected(result.codeResult.code);
                                }
                            });
                        }, 'image/jpeg', 0.85);
                    }
                },

                _stopStream() {
                    if (this._scanLoopId) {
                        cancelAnimationFrame(this._scanLoopId);
                        this._scanLoopId = null;
                    }
                    if (this._stream) {
                        this._stream.getTracks().forEach(function(t) { t.stop(); });
                        this._stream = null;
                    }
                    this._video = null;
                    this._barcodeDetector = null;
                    this._scanCanvas = null;
                },

                async stopCamera() {
                    this._stopStream();
                    var viewport = document.getElementById('camera-scanner-viewport');
                    if (viewport) viewport.innerHTML = '';
                    document.body.classList.remove('camera-active');
                    this.cameraActive = false;
                    this.pendingCode = null;
                    this._scanCooldown = false;
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

                    this.lastCameraScan = code;
                    this.pendingCode = code;
                    this.playBeep();
                    // Não fecha a câmera ainda — aguarda confirmação do usuário
                },

                // Usuário confirmou o código detectado
                confirmCode() {
                    var code = this.pendingCode;
                    if (!code) return;
                    this.pendingCode = null;
                    this._scanCooldown = false;
                    this.stopCamera();
                    this.scanMode = 'manual';
                    this.$wire.set('barcodeInput', code).then(() => {
                        this.$wire.searchBarcode();
                    });
                },

                // Usuário rejeitou e quer continuar escaneando
                rejectCode() {
                    this.pendingCode = null;
                    this.lastCameraScan = null;
                    this._scanCooldown = false;
                },

                // ------ TIRAR FOTO DA CÂMERA ------
                async snapPhoto() {
                    // Se câmera não está ativa, abre câmera nativa do iOS via input file
                    if (!this.cameraActive || !this._video || !this._video.videoWidth) {
                        var nativeInput = document.getElementById('native-camera-input');
                        if (nativeInput) nativeInput.click();
                        return;
                    }

                    this.photoSnapping = true;

                    // Efeito flash — aguarda render do overlay branco
                    await new Promise(function(r) { setTimeout(r, 80); });

                    var video = this._video;
                    var canvas = document.createElement('canvas');
                    canvas.width  = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);

                    var blob = await new Promise(function(resolve) {
                        canvas.toBlob(resolve, 'image/jpeg', 0.92);
                    });

                    this.photoSnapping = false;

                    if (!blob) {
                        // fallback: abre câmera nativa
                        var ni = document.getElementById('native-camera-input');
                        if (ni) ni.click();
                        return;
                    }

                    var file = new File([blob], 'foto-camera-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    await this.stopCamera();
                    this.scanMode = 'image';
                    this.loadImageFile(file);
                    this.showToast('Foto capturada! Analisando código...', 'info');
                },

                // Chamado quando usuário tira foto via câmera nativa (input file capture)
                handleNativeCameraPhoto(event) {
                    var file = event.target.files && event.target.files[0];
                    event.target.value = '';
                    if (!file) return;
                    this.stopCamera();
                    this.scanMode = 'image';
                    this.$nextTick(() => {
                        this.loadImageFile(file);
                        this.showToast('Foto carregada! Analisando código...', 'info');
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
                    this.imageDebugLogs = [];
                    this.pushImageDebug('Arquivo selecionado: ' + (file.name || 'sem-nome') + ' · ' + Math.round((file.size || 0) / 1024) + ' KB');

                    var self = this;
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        self.imagePreview = e.target.result;
                        var img = new Image();
                        img.onload = function() {
                            self.pushImageDebug('Dimensões originais: ' + img.width + 'x' + img.height);
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    this.$nextTick(() => {
                        setTimeout(() => this.scanImageBarcode(), 300);
                    });
                },

                async scanImageBarcode() {
                    if (!this._imageFile || this.imageScanning) return;

                    this.imageScanning = true;
                    this.imageResult = null;
                    this.imageDebugLogs = [];
                    this.pushImageDebug('Iniciando leitura da imagem');

                    try {
                        var code = await this.tryDetectImageCode(this._imageFile, 'original');

                        if (!code) {
                            var candidates = await this.createBarcodeCandidates(this._imageFile);
                            for (var i = 0; i < candidates.length && !code; i++) {
                                this.pushImageDebug('Tentando variante: ' + candidates[i].label);
                                code = await this.tryDetectImageCode(candidates[i].file, candidates[i].label);
                            }
                        }

                        if (code) {
                            this.pushImageDebug('Sucesso final: ' + code);
                            this.imageResult = 'Código encontrado: ' + code;
                            this.imageResultSuccess = true;
                            this.playBeep();
                            this.$wire.set('barcodeInput', code).then(() => {
                                this.$wire.searchBarcode();
                            });
                        } else {
                            this.pushImageDebug('Nenhuma estratégia encontrou um código válido');
                            this.imageResult = 'Nenhum código detectado. Tente com a imagem mais nítida.';
                            this.imageResultSuccess = false;
                        }
                    } catch (err) {
                        console.warn('Image scan error:', err);
                        // html5-qrcode rejeita a Promise com string de texto quando não acha código
                        var errMsg = (typeof err === 'string') ? err.toLowerCase() :
                            (err && err.message) ? err.message.toLowerCase() : '';
                        if (errMsg.includes('no multiformat') || errMsg.includes('no barcode') ||
                            errMsg.includes('code not found') || errMsg.includes('unable to') ||
                            errMsg.includes('not found')) {
                            this.pushImageDebug('ZXing não detectou código na imagem enviada');
                            this.imageResult = 'Código não encontrado. Tente uma foto mais próxima e nítida.';
                        } else {
                            this.pushImageDebug('Falha inesperada: ' + (errMsg || 'erro desconhecido'));
                            this.imageResult = 'Erro ao analisar imagem. Tente outro formato.';
                        }
                        this.imageResultSuccess = false;
                    }

                    this.imageScanning = false;
                },

                async tryDetectImageCode(file, label) {
                    var code = await this.detectImageWithBarcodeDetector(file, label);
                    if (code) return code;
                    code = await this.detectImageWithHtml5Qrcode(file, label);
                    if (code) return code;
                    return await this.detectImageWithQuagga(file, label);
                },

                async detectImageWithBarcodeDetector(file, label) {
                    if (!('BarcodeDetector' in window)) {
                        this.pushImageDebug('BarcodeDetector indisponível no browser para ' + label);
                        return null;
                    }

                    try {
                        var detector = new window.BarcodeDetector({
                            formats: ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf', 'qr_code', 'data_matrix']
                        });
                        var bitmap = await createImageBitmap(file);
                        var results = await detector.detect(bitmap);
                        if (bitmap && bitmap.close) bitmap.close();
                        if (results && results.length > 0) {
                            this.pushImageDebug('BarcodeDetector encontrou ' + results.length + ' resultado(s) em ' + label);
                            return results[0].rawValue || null;
                        }
                        this.pushImageDebug('BarcodeDetector não encontrou código em ' + label);
                    } catch (e) {
                        console.warn('BarcodeDetector image scan error:', e);
                        this.pushImageDebug('BarcodeDetector falhou em ' + label + ': ' + ((e && e.message) || e || 'erro'));
                    }

                    return null;
                },

                async detectImageWithHtml5Qrcode(file, label) {
                    var tempId = 'image-scanner-temp';
                    var tempNode = document.getElementById(tempId);
                    if (!tempNode) {
                        tempNode = document.createElement('div');
                        tempNode.id = tempId;
                        tempNode.style.position = 'fixed';
                        tempNode.style.width = '1px';
                        tempNode.style.height = '1px';
                        tempNode.style.opacity = '0';
                        tempNode.style.pointerEvents = 'none';
                        tempNode.style.left = '-9999px';
                        tempNode.style.top = '-9999px';
                        document.body.appendChild(tempNode);
                    }

                    var imageScanner = new Html5Qrcode(tempId);
                    try {
                        var code = await imageScanner.scanFile(file, false);
                        if (code) {
                            this.pushImageDebug('html5-qrcode encontrou código em ' + label + ': ' + code);
                        }
                        return code;
                    } catch (e) {
                        var message = (typeof e === 'string') ? e : ((e && e.message) ? e.message : 'erro desconhecido');
                        this.pushImageDebug('html5-qrcode falhou em ' + label + ': ' + message);
                        return null;
                    } finally {
                        try {
                            await imageScanner.clear();
                        } catch (e) {}
                    }
                },

                async detectImageWithQuagga(file, label) {
                    if (!window.Quagga || !window.Quagga.decodeSingle) {
                        this.pushImageDebug('Quagga2 indisponível para ' + label);
                        return null;
                    }

                    var objectUrl = URL.createObjectURL(file);
                    try {
                        var self = this;
                        var result = await new Promise(function(resolve) {
                            window.Quagga.decodeSingle({
                                src: objectUrl,
                                numOfWorkers: 0,
                                locate: true,
                                inputStream: {
                                    size: 1600,
                                    singleChannel: true,
                                },
                                decoder: {
                                    readers: [
                                        'ean_reader',
                                        'ean_8_reader',
                                        'upc_reader',
                                        'upc_e_reader',
                                        'code_128_reader',
                                        'code_39_reader',
                                        'i2of5_reader'
                                    ],
                                    multiple: false,
                                },
                                locator: {
                                    patchSize: 'large',
                                    halfSample: false,
                                },
                            }, function(result) {
                                resolve(result || null);
                            });
                        });

                        if (result && result.codeResult && result.codeResult.code) {
                            self.pushImageDebug('Quagga2 encontrou código em ' + label + ': ' + result.codeResult.code);
                            return result.codeResult.code;
                        }

                        self.pushImageDebug('Quagga2 não encontrou código em ' + label);
                        return null;
                    } catch (e) {
                        this.pushImageDebug('Quagga2 falhou em ' + label + ': ' + ((e && e.message) || e || 'erro'));
                        return null;
                    } finally {
                        URL.revokeObjectURL(objectUrl);
                    }
                },

                async createBarcodeCandidates(file) {
                    var candidates = [];
                    try {
                        var source = await this.readImageForCanvas(file);
                        if (!source) return candidates;

                        candidates.push(await this.buildCanvasVariant(source, 'ampliada + contraste', {
                            cropX: 0,
                            cropY: 0,
                            cropW: source.width,
                            cropH: source.height,
                            scale: 4.5,
                            filter: 'grayscale(1) contrast(1.75) brightness(1.1)'
                        }));

                        candidates.push(await this.buildCanvasVariant(source, 'faixa inferior', {
                            cropX: 0,
                            cropY: Math.floor(source.height * 0.54),
                            cropW: source.width,
                            cropH: Math.floor(source.height * 0.36),
                            scale: 6.0,
                            filter: 'grayscale(1) contrast(2.1) brightness(1.16)'
                        }));

                        candidates.push(await this.buildCanvasVariant(source, 'faixa inferior rotacionada 90', {
                            cropX: 0,
                            cropY: Math.floor(source.height * 0.54),
                            cropW: source.width,
                            cropH: Math.floor(source.height * 0.36),
                            scale: 6.0,
                            filter: 'grayscale(1) contrast(2.1) brightness(1.16)',
                            rotate: 90
                        }));

                        candidates.push(await this.buildCanvasVariant(source, 'faixa central larga', {
                            cropX: 0,
                            cropY: Math.floor(source.height * 0.40),
                            cropW: source.width,
                            cropH: Math.floor(source.height * 0.38),
                            scale: 4.8,
                            filter: 'grayscale(1) contrast(1.85) brightness(1.12)'
                        }));

                        candidates.push(await this.buildCanvasVariant(source, 'rodape focado em barras', {
                            cropX: Math.floor(source.width * 0.02),
                            cropY: Math.floor(source.height * 0.64),
                            cropW: Math.floor(source.width * 0.96),
                            cropH: Math.floor(source.height * 0.24),
                            scale: 7.5,
                            filter: 'grayscale(1) contrast(2.3) brightness(1.18)'
                        }));

                        candidates = candidates.filter(Boolean);
                        this.pushImageDebug('Variantes geradas: ' + candidates.map(function(item) {
                            return item.label;
                        }).join(', '));
                        return candidates;
                    } catch (e) {
                        console.warn('Enhanced image generation error:', e);
                        this.pushImageDebug('Falha ao gerar variantes: ' + ((e && e.message) || e || 'erro'));
                        return candidates;
                    }
                },

                async readImageForCanvas(file) {
                    try {
                        var objectUrl = URL.createObjectURL(file);
                        var image = new Image();
                        image.src = objectUrl;
                        await new Promise(function(resolve, reject) {
                            image.onload = resolve;
                            image.onerror = reject;
                        });
                        URL.revokeObjectURL(objectUrl);
                        return image;
                    } catch (e) {
                        return null;
                    }
                },

                async buildCanvasVariant(source, label, options) {
                    try {
                        var rotate = options.rotate || 0;
                        var cropX = Math.max(0, options.cropX || 0);
                        var cropY = Math.max(0, options.cropY || 0);
                        var cropW = Math.max(1, options.cropW || source.width);
                        var cropH = Math.max(1, options.cropH || source.height);
                        var scale = options.scale || 1;

                        var outW = Math.max(1, Math.floor(cropW * scale));
                        var outH = Math.max(1, Math.floor(cropH * scale));
                        var canvas = document.createElement('canvas');

                        if (rotate === 90 || rotate === 270) {
                            canvas.width = outH;
                            canvas.height = outW;
                        } else {
                            canvas.width = outW;
                            canvas.height = outH;
                        }

                        var ctx = canvas.getContext('2d', {
                            willReadFrequently: true
                        });
                        ctx.filter = options.filter || 'none';

                        if (rotate === 90) {
                            ctx.translate(canvas.width, 0);
                            ctx.rotate(Math.PI / 2);
                            ctx.drawImage(source, cropX, cropY, cropW, cropH, 0, 0, outW, outH);
                        } else if (rotate === 270) {
                            ctx.translate(0, canvas.height);
                            ctx.rotate(-Math.PI / 2);
                            ctx.drawImage(source, cropX, cropY, cropW, cropH, 0, 0, outW, outH);
                        } else {
                            ctx.drawImage(source, cropX, cropY, cropW, cropH, 0, 0, outW, outH);
                        }

                        var blob = await new Promise(function(resolve) {
                            canvas.toBlob(resolve, 'image/png', 1);
                        });
                        if (!blob) return null;
                        this.pushImageDebug('Variante pronta: ' + label + ' · ' + canvas.width + 'x' + canvas.height);
                        return {
                            label: label,
                            file: new File([blob], label.replace(/\s+/g, '-') + '.png', {
                                type: 'image/png'
                            })
                        };
                    } catch (e) {
                        this.pushImageDebug('Falha na variante ' + label + ': ' + ((e && e.message) || e || 'erro'));
                        return null;
                    }
                },

                pushImageDebug(message) {
                    var stamp = new Date().toLocaleTimeString('pt-BR', {
                        hour12: false
                    });
                    this.imageDebugLogs.push('[' + stamp + '] ' + message);
                    if (this.imageDebugLogs.length > 40) {
                        this.imageDebugLogs = this.imageDebugLogs.slice(-40);
                    }
                },

                clearImage() {
                    this.imagePreview = null;
                    this._imageFile = null;
                    this.imageResult = null;
                    this.imageResultSuccess = false;
                    this.imageDebugLogs = [];
                    if (this.$refs.imageFileInput) this.$refs.imageFileInput.value = '';
                },

                // ------ HELPERS ------
                playBeep() {
                    try {
                        var ctx = new(window.AudioContext || window.webkitAudioContext)();
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
                    this._stopStream();
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