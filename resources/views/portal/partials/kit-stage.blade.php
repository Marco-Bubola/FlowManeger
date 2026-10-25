{{-- Palco do kit: a base com os produtos em leque e os enfeites em volta. Dentro do x-data do kitBuilder. --}}
<div class="kb-stage" data-testid="kit-stage">
    <div class="kb-stage-name" x-show="type"><span x-text="type ? type.name : ''"></span><span x-show="count" x-text="count + (count === 1 ? ' produto' : ' produtos')"></span></div>
    <template x-if="type && type.img"><div class="kb-stage-base img"><img :src="type.img" alt=""></div></template>
    <template x-if="!type || !type.img"><div class="kb-stage-base" x-text="look[1]"></div></template>
    <template x-for="(x, i) in pickedList" :key="'sp' + x.p.id">
        <div class="kb-stage-p" :style="pPos(i)">
            <template x-if="x.p.img"><img :src="x.p.img" alt=""></template>
            <template x-if="!x.p.img"><span style="font-size:24px">🎁</span></template>
            <b x-show="x.n > 1" x-text="x.n"></b>
        </div>
    </template>
    <template x-for="(e, i) in stageExtras" :key="'se' + i">
        <div class="kb-stage-e" :class="e.img && 'img'" :style="ePos(i)">
            <template x-if="e.img"><img :src="e.img" alt=""></template>
            <template x-if="!e.img"><span x-text="groupEmoji(e.group)"></span></template>
        </div>
    </template>
    <div class="kb-stage-hint" x-show="type && !count" style="padding-bottom:120px">Coloque os produtos e veja seu kit tomando forma ✨</div>
    <div class="kb-stage-hint" x-show="!type">Escolha um kit para começar 🎁</div>
</div>
