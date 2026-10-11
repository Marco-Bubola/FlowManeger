{{-- Barra do carrossel de variações, logo abaixo do nome no card; usa o estado do x-variation-carousel em volta. --}}
<div class="vcn" @click.stop>
    <button type="button" class="vcn-arrow" @click.stop.prevent="go(-1)" aria-label="Variação anterior"><i class="bi bi-chevron-left"></i></button>
    <span class="vcn-mid" :title="labels[vi] + (note ? ' · ' + note : '')">
        <span class="vcn-name"><i class="bi bi-diagram-3"></i> <span x-text="labels[vi]"></span></span>
        <span class="vcn-meta">
            <span class="vcn-count" x-text="(vi + 1) + '/' + n"></span>
            <span class="vcn-dots" x-show="n <= 8"><template x-for="i in n" :key="i"><span class="vcn-dot" :class="{ on: vi === i - 1 }"></span></template></span>
            <span class="vcn-note" x-show="note" x-text="note"></span>
        </span>
    </span>
    <button type="button" class="vcn-arrow" @click.stop.prevent="go(1)" aria-label="Próxima variação"><i class="bi bi-chevron-right"></i></button>
</div>
