{{-- Portal: pedir orçamento (produto sob medida / outra cor / reforma) com fotos. --}}
@php
    $pInfo = $product ? \App\Http\Controllers\ClientPortalController::cardInfo($product) : null;
    $colorGroups = $settings['colors'] ?? [];
@endphp
<x-portal-catalog-layout :title="$product ? 'Quero em outra cor' : 'Pedir orçamento'" :owner-id="$ownerId">
    @push('styles')
        @include('portal.partials.custom-quote-styles')
    @endpush

    <div class="cq-page">
        @if(session('quote_sent'))
            <section class="cq-card cq-pad" style="text-align:center">
                <div style="width:64px;height:64px;margin:0 auto .6rem;border-radius:20px;background:#ecfdf5;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:2rem"><i class="fas fa-circle-check"></i></div>
                <h1 style="margin:0 0 .3rem;font-size:1.4rem;font-weight:900">Pedido enviado!</h1>
                <p class="cq-muted" style="font-size:.95rem;margin:0 0 1rem">A loja vai olhar e te mandar o orçamento em até {{ ($d = (int) session('quote_days', $settings['reply_days'])) }} {{ $d === 1 ? 'dia útil' : 'dias úteis' }}, pelo WhatsApp{{ $client ? ' e aqui em Meus pedidos' : '' }}.</p>
                <div class="cq-actions two">
                    <a href="{{ route('portal.catalog', ['userId' => $ownerId]) }}" class="cq-btn cq-btn-soft"><i class="fas fa-store"></i>Voltar à loja</a>
                    @if($client)
                        <a href="{{ route('portal.quotes', ['ver' => 'orcamentos']) }}" class="cq-btn cq-btn-primary"><i class="fas fa-file-lines"></i>Meus orçamentos</a>
                    @else
                        <a href="{{ route('portal.custom-quote.create', ['userId' => $ownerId]) }}" class="cq-btn cq-btn-primary"><i class="fas fa-plus"></i>Pedir outro</a>
                    @endif
                </div>
            </section>
        @elseif(!$enabled)
            <section class="cq-card cq-pad">
                <h1 class="cq-h2">Pedidos de orçamento pausados</h1>
                <p class="cq-muted">A loja não está recebendo pedidos por aqui agora. Fale direto com ela.</p>
            </section>
        @else
            <section class="cq-card">
                <div class="cq-hero">
                    <span class="cq-code">{{ $product ? 'Do seu jeito' : 'Sob medida ou reforma' }}</span>
                    <h1>{{ $product ? 'Quero este produto em outra cor' : 'Pedir orçamento' }}</h1>
                    <p>{{ $product ? 'Escolha as cores e a medida. A loja te manda o preço e o prazo.' : 'Conte o que você quer, mande fotos e receba o orçamento pelo WhatsApp.' }}</p>
                </div>
                <div class="cq-pad">
                    <div class="cq-steps">
                        <div><i class="fas fa-pen"></i>Você conta como quer</div>
                        <div><i class="fas fa-calculator"></i>A loja faz o orçamento</div>
                        <div><i class="fas fa-circle-check"></i>Você aprova pelo link</div>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('portal.custom-quote.store', ['userId' => $ownerId]) }}" enctype="multipart/form-data" class="cq-card cq-pad" style="display:flex;flex-direction:column;gap:1.1rem"
                  x-data="cqForm()" @submit="sending = true">
                @csrf
                <input type="text" name="site" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
                @if($errors->any())
                    <div class="cq-flash bad"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
                @endif

                @if($product)
                    <input type="hidden" name="produto" value="{{ $product->id }}">
                    <input type="hidden" name="tipo" value="produto">
                    <div class="cq-product">
                        @if($pInfo['img'])<img src="{{ $pInfo['img'] }}" alt="{{ $product->name }}">@endif
                        <div style="min-width:0">
                            <b style="display:block">{{ $product->name }}</b>
                            <span class="cq-muted">A partir de {{ \App\Services\Quotes\QuoteService::money($pInfo['price']) }}</span>
                        </div>
                        <a href="{{ $pInfo['url'] }}" class="cq-muted" style="margin-left:auto;white-space:nowrap">Ver produto</a>
                    </div>
                    @if($options)
                        <div class="cq-label">Cores que a loja já fez <small>Toque em uma para usar de base (opcional)</small>
                            <div class="cq-swatches">
                                @foreach($options as $o)
                                    <label class="cq-sw" :class="picked['Opção'] === @js($o) ? 'on' : ''">
                                        <input type="radio" name="cores[Opção]" value="{{ $o }}" x-model="picked['Opção']" style="display:none"><i style="background: linear-gradient(135deg,#ddd,#bbb)"></i>{{ $o }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="cq-label">O que você precisa?
                        <div class="cq-kind">
                            <label><input type="radio" name="tipo" value="produto" @checked(old('tipo', $kind) === 'produto')><i class="fas fa-ruler-combined"></i><b>Produto novo</b><span>Sob medida, do tamanho e cor que você quer</span></label>
                            <label><input type="radio" name="tipo" value="reforma" @checked(old('tipo', $kind) === 'reforma')><i class="fas fa-screwdriver-wrench"></i><b>Reforma</b><span>Estofar, pintar ou consertar a sua peça</span></label>
                        </div>
                    </div>
                @endif

                <label class="cq-label">{{ $product ? 'Conte como você quer' : 'Descreva o que você quer' }}
                    <small>{{ $product ? 'Ex.: igual a da foto, mas com assento caramelo e 65 cm de altura.' : 'Ex.: 4 banquetas de ferro preto, assento de madeira, 75 cm. Ou: trocar o assento de 6 cadeiras.' }}</small>
                    <textarea name="descricao" class="cq-input" required maxlength="2000">{{ old('descricao') }}</textarea>
                    @error('descricao')<span class="cq-err">{{ $message }}</span>@enderror
                </label>

                @if($colorGroups)
                    <div class="cq-label">Cores <small>Escolha o que quiser em cada parte (pode deixar em branco)</small>
                        <div style="display:flex;flex-direction:column;gap:.5rem">
                            @foreach($colorGroups as $g)
                                <details class="cq-group">
                                    <summary><span>{{ $g['name'] }}</span><span class="cq-picked" x-text="picked[@js($g['name'])] || ''"></span><i class="fas fa-chevron-down cq-muted" style="margin-left:{{ '.4rem' }}"></i></summary>
                                    <div class="cq-group-body">
                                        <div class="cq-swatches">
                                            @foreach($g['items'] as $c)
                                                <label class="cq-sw" :class="picked[@js($g['name'])] === @js($c[0]) ? 'on' : ''">
                                                    <input type="radio" name="cores[{{ $g['name'] }}]" value="{{ $c[0] }}" x-model="picked[@js($g['name'])]" style="display:none"><i style="background: {{ $c[1] }}"></i>{{ $c[0] }}
                                                </label>
                                            @endforeach
                                            <button type="button" class="cq-sw" x-show="picked[@js($g['name'])]" @click="picked[@js($g['name'])] = ''"><i class="fas fa-xmark" style="border:0;display:flex;align-items:center;justify-content:center"></i>Tirar</button>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                        <input type="text" name="outra_cor" value="{{ old('outra_cor') }}" class="cq-input" placeholder="Outra cor ou acabamento que não está na lista" maxlength="120">
                    </div>
                @endif

                <label class="cq-label">Medidas <small>Se souber (altura, largura, profundidade)</small>
                    <input type="text" name="medidas" value="{{ old('medidas') }}" class="cq-input" maxlength="200" placeholder="Ex.: 75 cm de altura, assento de 33 cm">
                </label>

                <div class="cq-label">Fotos <small>Até 8: da peça para reformar, do lugar, ou uma referência que você gostou</small>
                    <label class="cq-drop">
                        <i class="fas fa-camera" style="font-size:1.6rem"></i>
                        <span x-text="files.length ? files.length + ' foto(s) escolhida(s)' : 'Tirar foto ou escolher da galeria'"></span>
                        <small>JPG ou PNG, até 12 MB cada</small>
                        <input type="file" name="fotos[]" accept="image/*" multiple style="display:none" x-ref="input" @change="pick($event)">
                    </label>
                    <div class="cq-previews" x-show="previews.length">
                        <template x-for="(p, i) in previews" :key="p">
                            <div><img :src="p" alt=""><button type="button" @click="remove(i)" aria-label="Tirar foto"><i class="fas fa-xmark"></i></button></div>
                        </template>
                    </div>
                    @error('fotos')<span class="cq-err">{{ $message }}</span>@enderror
                    @error('fotos.*')<span class="cq-err">{{ $message }}</span>@enderror
                </div>

                @if($client)
                    <p class="cq-muted" style="margin:0"><i class="fas fa-user"></i> Pedido em nome de <b>{{ $client->name }}</b>. A resposta chega no seu WhatsApp e em Meus pedidos.</p>
                @else
                    <div class="cq-grid2">
                        <label class="cq-label">Seu nome
                            <input type="text" name="nome" value="{{ old('nome') }}" class="cq-input" required maxlength="120" autocomplete="name">
                            @error('nome')<span class="cq-err">{{ $message }}</span>@enderror
                        </label>
                        <label class="cq-label">WhatsApp
                            <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" class="cq-input" required maxlength="30" autocomplete="tel" placeholder="(00) 00000-0000">
                            @error('whatsapp')<span class="cq-err">{{ $message }}</span>@enderror
                        </label>
                    </div>
                @endif

                <button type="submit" class="cq-btn cq-btn-primary" :disabled="sending">
                    <i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                    <span x-text="sending ? 'Enviando...' : 'Pedir orçamento'"></span>
                </button>
                <p class="cq-muted" style="margin:-.4rem 0 0;text-align:center">A loja responde em até {{ $settings['reply_days'] }} {{ (int) $settings['reply_days'] === 1 ? 'dia útil' : 'dias úteis' }}. Sem compromisso.</p>
            </form>
            <script>
                function cqForm() {
                    return {
                        picked: @js((object) array_map('strval', (array) old('cores', []))),
                        files: [], previews: [], sending: false,
                        pick(e) {
                            const add = [...e.target.files].filter(f => f.type.startsWith('image/'));
                            this.files = [...this.files, ...add].slice(0, 8);
                            this.sync();
                        },
                        remove(i) { this.files.splice(i, 1); this.sync(); },
                        sync() {
                            this.previews.forEach(u => URL.revokeObjectURL(u));
                            this.previews = this.files.map(f => URL.createObjectURL(f));
                            const dt = new DataTransfer();
                            this.files.forEach(f => dt.items.add(f));
                            this.$refs.input.files = dt.files;
                        },
                    };
                }
            </script>
        @endif
    </div>
</x-portal-catalog-layout>
