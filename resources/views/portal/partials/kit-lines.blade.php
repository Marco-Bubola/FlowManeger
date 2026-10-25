{{-- Lista e total do kit. Dentro do x-data do kitBuilder. --}}
<ul class="kb-lines" data-testid="kit-lines" x-show="type">
    <li><span class="ic" x-text="look[1]"></span><span class="nm" x-text="type ? type.name + ' (base)' : ''"></span><span class="vl" x-text="base > 0 ? money(base) : 'Grátis'"></span></li>
    <template x-for="x in pickedList" :key="'lp' + x.p.id">
        <li>
            <span class="ic"><template x-if="x.p.img"><img :src="x.p.img" alt=""></template><template x-if="!x.p.img"><span>🎁</span></template></span>
            <span class="nm" x-text="(x.n > 1 ? x.n + 'x ' : '') + x.p.name"></span>
            <span class="vl" x-text="money(x.p.price * x.n)"></span>
            <button type="button" class="rm" @click="setProduct(x.p, 0)" aria-label="Tirar do kit"><i class="fas fa-xmark"></i></button>
        </li>
    </template>
    <template x-for="x in chosenList" :key="'le' + x.e.id">
        <li>
            <span class="ic"><template x-if="x.e.img"><img :src="x.e.img" alt=""></template><template x-if="!x.e.img"><span x-text="groupEmoji(x.e.group)"></span></template></span>
            <span class="nm" x-text="(x.n > 1 ? x.n + 'x ' : '') + x.e.name"></span>
            <span class="vl" x-text="money(x.e.price * x.n)"></span>
            <button type="button" class="rm" @click="setExtra(x.e, 0)" aria-label="Tirar do kit"><i class="fas fa-xmark"></i></button>
        </li>
    </template>
    <li x-show="message || to" style="font-style:italic;color:var(--ml-muted,#777)"><span class="ic">💌</span><span class="nm" x-text="(to ? 'Para ' + to + ': ' : '') + (message || '')"></span></li>
</ul>
<div x-show="type">
    <div class="kb-sumrow" x-show="extrasTotal > 0"><span>Produtos</span><span x-text="money(productsTotal)"></span></div>
    <div class="kb-sumrow" x-show="extrasTotal > 0"><span>Base e enfeites</span><span x-text="money(base + extrasTotal)"></span></div>
    <div class="kb-sumrow" x-show="step === 4 && delivery === 'entrega'"><span>Entrega</span><span x-text="shipFee > 0 ? money(shipFee) : 'Grátis'"></span></div>
    <div class="kb-total"><span>Total do kit</span><strong x-text="money(total)" data-testid="kit-total"></strong></div>
</div>
