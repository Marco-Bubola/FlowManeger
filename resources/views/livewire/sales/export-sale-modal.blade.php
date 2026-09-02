<div class="mobile-393-base">
    @if($showModal)
    <div x-data="{
        modalOpen: true,
        exportType: @entangle('exportType'),
        downloading: false,

        async waitForImages(element) {
            const images = element.querySelectorAll('img');
            const promises = Array.from(images).map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                    setTimeout(reject, 5000);
                });
            });
            return Promise.allSettled(promises);
        },

        async exportSale(saleId, type) {
            this.downloading = true;
            await new Promise(resolve => setTimeout(resolve, 100));

            if(type === 'pdf'){
                // Chamar método no componente Livewire local para gerar o PDF diretamente
                try {
                    // Tenta usar $wire.call (resolvido pelo Livewire para este componente)
                    await $wire.call('exportPdf', saleId);
                } catch (err) {
                    console.error('Erro ao solicitar exportação de PDF via Livewire', err);
                    alert('Erro ao iniciar download do PDF: ' + (err?.message || err));
                } finally {
                    this.downloading = false;
                }

                return;
            }

            // Para exportar como imagem, capturamos o preview já renderizado no modal
            const cardElement = document.getElementById('export-sale-' + saleId);
            if (!cardElement) {
                alert('Erro: preview não encontrado');
                this.downloading = false;
                return;
            }

            // O preview e exibido reduzido para caber no modal, mas o
            // html2canvas HERDA o transform do ancestral — capturado assim a
            // imagem saia a 1425px em vez de 1860px. Desliga a escala so
            // durante a captura e devolve depois.
            const involucro = cardElement.parentElement;
            const escalaOriginal = involucro ? involucro.style.transform : '';
            const alturaOriginal = involucro?.parentElement ? involucro.parentElement.style.height : '';
            if (involucro) {
                involucro.style.transform = 'none';
                if (involucro.parentElement) involucro.parentElement.style.height = 'auto';
            }

            try {
                await this.waitForImages(cardElement);
                const canvas = await html2canvas(cardElement, {
                    backgroundColor: '#ffffff',
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    imageTimeout: 15000
                });

                if (involucro) {
                    involucro.style.transform = escalaOriginal;
                    if (involucro.parentElement) involucro.parentElement.style.height = alturaOriginal;
                }

                canvas.toBlob((blob) => {
                    if (!blob) {
                        alert('Erro ao gerar imagem');
                        this.downloading = false;
                        return;
                    }
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const name = 'sale-' + saleId + '-' + (type || 'image') + '.png';
                    link.href = url;
                    link.download = name;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                    this.downloading = false;
                }, 'image/png', 1.0);

            } catch (err) {
                console.error(err);
                alert('Erro ao exportar imagem: ' + err.message);
                this.downloading = false;
            }
        }
    }"
         x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] overflow-y-auto"
         @keydown.escape.window="modalOpen = false; $wire.closeModal()">

        <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-slate-900/80 to-blue-900/40 backdrop-blur-md"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalOpen" class="relative w-full max-w-5xl bg-gradient-to-br from-white/95 to-slate-50/95 dark:from-slate-800/95 dark:to-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden">

                <div class="relative bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                <i class="bi bi-file-earmark-arrow-down text-xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Exportar Venda</h3>
                                <p class="text-xs text-white/80">Escolha o formato de exportação</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" @click="modalOpen = false" class="w-8 h-8 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg flex items-center justify-center text-white transition-all duration-200">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-3"><i class="bi bi-toggles text-purple-500 mr-2"></i>Formato</label>

                            <div class="space-y-3">
                                <label class="relative cursor-pointer group block">
                                    <input type="radio" x-model="exportType" value="pdf" class="sr-only peer">
                                    <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-file-earmark-pdf text-lg text-white"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">PDF</h4>
                                                <p class="text-xs text-slate-600 dark:text-slate-400">Gerar documento PDF tradicional</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer group block">
                                    <input type="radio" x-model="exportType" value="image-complete" class="sr-only peer">
                                    <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-image text-lg text-white"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Imagem (completo)</h4>
                                                <p class="text-xs text-slate-600 dark:text-slate-400">Imagem com todos os detalhes da venda</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer group block">
                                    <input type="radio" x-model="exportType" value="image-summary" class="sr-only peer">
                                    <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-card-text text-lg text-white"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Imagem (resumo)</h4>
                                                <p class="text-xs text-slate-600 dark:text-slate-400">Resumo da venda em imagem</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                            </div>

                            <div class="space-y-2 pt-4">
                                @if($sale)
                                <button @click.prevent="exportSale({{ $sale->id }}, exportType)" :disabled="downloading" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold rounded-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="!downloading">
                                        <i class="bi bi-download text-lg"></i>
                                    </template>
                                    <template x-if="downloading">
                                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                                    </template>
                                    <span x-text="downloading ? 'Gerando...' : (exportType === 'pdf' ? 'Gerar PDF' : 'Baixar Imagem')"></span>
                                </button>
                                @endif

                                <button wire:click="closeModal" @click="modalOpen = false" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-xl">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-3"><i class="bi bi-eye text-purple-500 mr-2"></i>Preview</label>

                            @if($sale)

                            {{-- O card tem 620px fixos porque e essa a largura da imagem
                                 exportada. Dentro do modal o painel tem ~475px, entao ele
                                 vazava 145px pela direita. A escala fica no INVOLUCRO: o
                                 html2canvas captura o card pelo id e monta o proprio render
                                 a partir dele, sem herdar transform de ancestral — a imagem
                                 sai nos mesmos 620px. --}}
                            <div class="export-preview-viewport"
                                 x-data="{
                                     k: 1,
                                     ajustar() {
                                         const card = $refs.cardWrap.firstElementChild;
                                         if (!card) return;
                                         const disponivel = $el.clientWidth;
                                         this.k = Math.min(1, disponivel / card.offsetWidth);
                                         $refs.cardWrap.style.transform = 'scale(' + this.k + ')';
                                         $el.style.height = (card.offsetHeight * this.k) + 'px';
                                     }
                                 }"
                                 x-init="$nextTick(() => ajustar())"
                                 x-on:resize.window.debounce.150ms="ajustar()"
                                 style="overflow: hidden;">
                            <div x-ref="cardWrap" style="transform-origin: top left; width: max-content;">

                            <div id="export-sale-{{ $sale->id }}"
                                 style="width:620px; background:#ffffff; color:#0f172a; border-radius:14px; padding:18px;
                                        box-shadow:0 12px 32px rgba(0,0,0,0.08);
                                        font-family:'Segoe UI', Tahoma, sans-serif;">

                                {{-- Mesmo cabecalho do PDF --}}
                                @include('exports.partials.sale-header', ['sale' => $sale])

                                {{-- Mesma estrutura do PDF: titulo, cards em 3 colunas
                                     e o mesmo rodape de totais. --}}
                                <div style="text-align:center; margin:16px 0 6px;">
                                    <div style="font-size:15px; font-weight:bold; letter-spacing:0.06em; color:#3f3357;">PRODUTOS DA VENDA</div>
                                    <div style="height:2px; background:#7c3aed; margin:6px auto 0; width:100%;"></div>
                                </div>

                                @foreach($sale->saleItems->chunk(3) as $linha)
                                <table style="width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:10px;">
                                    <tr>
                                        @foreach($linha as $item)
                                        <td style="width:33.33%; vertical-align:top; border:1.5px solid #c4b5fd;
                                                   border-radius:12px; background:#faf8ff; padding:0;">
                                            <div style="position:relative; height:110px; background:#f1ecfb; text-align:center;">
                                                <img src="{{ $item->product && $item->product->image ? asset('storage/products/' . $item->product->image) : asset('storage/products/product-placeholder.png') }}"
                                                     onerror="this.onerror=null; this.src='{{ asset('storage/products/product-placeholder.png') }}';"
                                                     alt="{{ $item->product->name ?? 'Produto' }}"
                                                     style="height:110px; max-width:100%; object-fit:contain; display:inline-block;">
                                                <span style="position:absolute; top:5px; left:5px; background:#ede9fe; color:#5b21b6;
                                                             font-size:9px; font-weight:bold; padding:2px 5px; border-radius:5px;">
                                                    #{{ $item->product->product_code ?? 'N/A' }}
                                                </span>
                                            </div>

                                            <div style="padding:8px;">
                                                <div style="font-size:11px; font-weight:bold; color:#3f3357; text-align:center;
                                                            line-height:1.25; height:28px; overflow:hidden;">
                                                    {{ \Illuminate\Support\Str::limit($item->product->name ?? 'Produto', 42) }}
                                                </div>

                                                <table style="width:100%; border-collapse:separate; border-spacing:3px 0; margin:6px 0;">
                                                    <tr>
                                                        <td style="width:50%; background:#f4f1fb; border:1px solid #ded5f2; border-radius:7px;
                                                                   padding:4px 2px; text-align:center;">
                                                            <span style="display:block; font-size:7px; font-weight:bold; letter-spacing:0.09em;
                                                                         color:#8b7fa8;">QTD</span>
                                                            <span style="display:block; font-size:13px; font-weight:bold; color:#3f3357;">{{ $item->quantity }}</span>
                                                        </td>
                                                        <td style="width:50%; background:#f4f1fb; border:1px solid #ded5f2; border-radius:7px;
                                                                   padding:4px 2px; text-align:center;">
                                                            <span style="display:block; font-size:7px; font-weight:bold; letter-spacing:0.09em;
                                                                         color:#8b7fa8;">UNIT.</span>
                                                            <span style="display:block; font-size:13px; font-weight:bold; color:#3f3357;">{{ number_format($item->price_sale, 2, ',', '.') }}</span>
                                                        </td>
                                                    </tr>
                                                </table>

                                                <div style="background:#ffffff; color:#4c1d95; border:1.5px solid #c4b5fd;
                                                            border-radius:7px; padding:5px; text-align:center;
                                                            font-size:13px; font-weight:bold;">
                                                    R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}
                                                </div>
                                            </div>
                                        </td>
                                        @endforeach

                                        @for($i = $linha->count(); $i < 3; $i++)
                                        <td style="width:33.33%;"></td>
                                        @endfor
                                    </tr>
                                </table>
                                @endforeach

                                {{-- Rodape de totais: igual ao do PDF --}}
                                <div style="margin-top:14px; background:#faf9fe; border:1px solid #e2dcf3;
                                            border-radius:12px; padding:14px 16px;">
                                    <table style="width:62%; margin-left:38%; border-collapse:collapse;">
                                        <tr>
                                            <td style="padding:5px 0; font-size:13px; font-weight:600; color:#57506b;">Subtotal:</td>
                                            <td style="padding:5px 0; font-size:14px; font-weight:bold; color:#3f3357; text-align:right;">
                                                R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        @if(($sale->amount_paid ?? 0) > 0)
                                        <tr>
                                            <td style="padding:5px 0; font-size:13px; font-weight:600; color:#57506b;">Valor Pago:</td>
                                            <td style="padding:5px 0; font-size:14px; font-weight:bold; color:#15803d; text-align:right;">
                                                R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                        @if(max(0, $sale->total_price - ($sale->amount_paid ?? 0)) > 0)
                                        <tr>
                                            <td style="padding:5px 0; font-size:13px; font-weight:600; color:#57506b;">Valor Pendente:</td>
                                            <td style="padding:5px 0; font-size:14px; font-weight:bold; color:#b91c1c; text-align:right;">
                                                R$ {{ number_format(max(0, $sale->total_price - ($sale->amount_paid ?? 0)), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>

                                    <table style="width:62%; margin-left:38%; margin-top:10px; border-collapse:collapse;
                                                  background:#6d28d9; border-radius:10px;">
                                        <tr>
                                            <td style="padding:11px 14px; font-size:11px; font-weight:bold; letter-spacing:0.09em;
                                                       color:#e9d5ff; vertical-align:middle;">TOTAL DA VENDA</td>
                                            <td style="padding:11px 14px; font-size:19px; font-weight:bold; color:#ffffff;
                                                       text-align:right; vertical-align:middle;">
                                                R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                            </div>{{-- /#export-sale --}}

                            </div>{{-- /x-ref cardWrap --}}
                            </div>{{-- /.export-preview-viewport --}}

                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    @endif
</div>
