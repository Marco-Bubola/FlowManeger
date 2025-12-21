<div>
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
         class="fixed inset-0 z-[99999] overflow-y-auto"
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

                            <div id="export-sale-{{ $sale->id }}" style="width: 620px; background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 12px 32px rgba(0,0,0,0.08); font-family: 'Segoe UI', Tahoma, sans-serif;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                    <div style="font-weight:800; font-size:18px;">Venda #{{ $sale->id }}</div>
                                    <div style="font-size:14px; color:#6b7280;">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
                                </div>

                                <div style="display:flex; gap:14px; align-items:center; margin-bottom:12px;">
                                    <div style="width:64px; height:64px; background:linear-gradient(135deg,#9575cd,#b39ddb); border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700;">{{ strtoupper(substr($sale->client->name ?? 'CL',0,2)) }}</div>
                                    <div>
                                        <div style="font-weight:700;">{{ $sale->client->name ?? 'Cliente' }}</div>
                                        <div style="color:#6b7280; font-size:13px;">Itens: {{ $sale->saleItems->count() }} — Total: R$ {{ number_format($sale->total_price,2,',','.') }}</div>
                                    </div>
                                </div>

                                <div style="border-top:1px solid #f3f4f6; padding-top:10px;">
                                    @foreach($sale->saleItems as $item)
                                        <div style="display:flex; justify-content:space-between; padding:6px 0;">
                                            <div style="min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $item->product->name ?? 'Produto' }}</div>
                                            <div style="color:#6b7280;">{{ $item->quantity }} x R$ {{ number_format($item->price_sale,2,',','.') }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <div style="margin-top:12px; display:flex; justify-content:space-between; font-weight:800;">
                                    <div style="color:#6b7280;">Pagamento</div>
                                    <div>{{ ucfirst(str_replace('_',' ', $sale->payment_method ?? '')) }}</div>
                                </div>
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
