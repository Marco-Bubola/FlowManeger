{{-- Vistos recentemente: ids guardados no aparelho; os cards vêm atualizados do servidor. Fica dentro do x-data do mlCatalog. --}}
<section class="ml-box ml-offers ml-seen" x-data="mlSeen(@js(route('portal.catalog.seen', ['userId' => $owner])), @js($owner), @js(array_values($exclude ?? [])))"
         x-show="count > 0" x-cloak data-testid="recently-viewed">
    <div class="ml-sec-head">
        <h2><i class="fas fa-clock-rotate-left"></i> Vistos recentemente</h2>
        <button type="button" class="ml-seen-clear" @click="clear()">Limpar</button>
    </div>
    <div class="ml-rail" x-ref="rail"></div>
</section>
