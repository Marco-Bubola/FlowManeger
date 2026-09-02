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

            try {
                await this.waitForImages(cardElement);
                const canvas = await html2canvas(cardElement, {
                    backgroundColor: '#ffffff',
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    imageTimeout: 15000
                });

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

                            <div id="export-sale-{{ $sale->id }}"
                                 style="width:620px; background:#ffffff; color:#0f172a; border-radius:14px; padding:18px;
                                        box-shadow:0 12px 32px rgba(0,0,0,0.08);
                                        font-family:'Segoe UI', Tahoma, sans-serif;">

                                {{-- Mesmo cabecalho do PDF --}}
                                @include('exports.partials.sale-header', ['sale' => $sale])

                                {{-- Produtos: tabela, com nome / qtd / unitario / total --}}
                                <table style="width:100%; border-collapse:collapse; margin-top:12px;">
                                    <tr>
                                        <th style="text-align:left; padding:6px 8px; font-size:10px; letter-spacing:0.08em;
                                                   color:#64748b; border-bottom:1px solid #e2e8f0;">PRODUTO</th>
                                        <th style="text-align:center; padding:6px 8px; font-size:10px; letter-spacing:0.08em;
                                                   color:#64748b; border-bottom:1px solid #e2e8f0; width:48px;">QTD</th>
                                        <th style="text-align:right; padding:6px 8px; font-size:10px; letter-spacing:0.08em;
                                                   color:#64748b; border-bottom:1px solid #e2e8f0; width:92px;">UNIT.</th>
                                        <th style="text-align:right; padding:6px 8px; font-size:10px; letter-spacing:0.08em;
                                                   color:#64748b; border-bottom:1px solid #e2e8f0; width:100px;">TOTAL</th>
                                    </tr>
                                    @foreach($sale->saleItems as $item)
                                    <tr>
                                        <td style="padding:7px 8px; font-size:12px; color:#0f172a; border-bottom:1px solid #f1f5f9;">
                                            {{ \Illuminate\Support\Str::limit($item->product->name ?? 'Produto', 46) }}
                                            @if($item->product?->product_code)
                                                <span style="color:#94a3b8; font-size:10px;">#{{ $item->product->product_code }}</span>
                                            @endif
                                        </td>
                                        <td style="padding:7px 8px; font-size:12px; color:#334155; text-align:center; border-bottom:1px solid #f1f5f9;">
                                            {{ $item->quantity }}
                                        </td>
                                        <td style="padding:7px 8px; font-size:12px; color:#334155; text-align:right; border-bottom:1px solid #f1f5f9;">
                                            R$ {{ number_format($item->price_sale, 2, ',', '.') }}
                                        </td>
                                        <td style="padding:7px 8px; font-size:12px; color:#0f172a; font-weight:bold; text-align:right; border-bottom:1px solid #f1f5f9;">
                                            R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>

                                {{-- Totais --}}
                                <table style="width:100%; border-collapse:collapse; margin-top:12px;
                                              background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                                    <tr>
                                        <td style="padding:8px 12px; font-size:12px; color:#475569;">Total da venda</td>
                                        <td style="padding:8px 12px; font-size:16px; font-weight:bold; color:#0f172a; text-align:right;">
                                            R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @if(($sale->amount_paid ?? 0) > 0)
                                    <tr>
                                        <td style="padding:8px 12px; font-size:12px; color:#475569;">Pago</td>
                                        <td style="padding:8px 12px; font-size:13px; font-weight:bold; color:#15803d; text-align:right;">
                                            R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:8px 12px; font-size:12px; color:#475569;">Restante</td>
                                        <td style="padding:8px 12px; font-size:13px; font-weight:bold; color:#b91c1c; text-align:right;">
                                            R$ {{ number_format(max(0, $sale->total_price - $sale->amount_paid), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    @endif
</div>
