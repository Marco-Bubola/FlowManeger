{{-- Componente Alpine do catálogo (carrinho, favoritos, rolagem infinita). --}}
<script>
function mlCatalog(products, cartUrl, stockMap, extra = {}) {
    return {
        init() {
            // Carrinho só com o que ainda tem estoque, e no máximo o disponível.
            Alpine.store('cart').sync(stockMap);
            if (this.setup) this.setup();
            // Rolagem infinita do catálogo.
            const more = this.$refs.more;
            if (more) {
                this.moreNext = more.dataset.next || null;
                if (this.moreNext && 'IntersectionObserver' in window) {
                    this.moreObs = new IntersectionObserver(es => { if (es.some(e => e.isIntersecting)) this.loadMore(); }, { rootMargin: '900px 0px' });
                    this.moreObs.observe(more);
                }
            }
        },
        moreNext: null,
        moreLoading: false,
        moreError: false,
        moreObs: null,
        async loadMore() {
            if (!this.moreNext || this.moreLoading) return;
            this.moreLoading = true;
            this.moreError = false;
            try {
                const url = new URL(this.moreNext, location.href);
                url.searchParams.set('frag', '1');
                const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!r.ok) throw new Error(r.status);
                const data = await r.json();
                Object.assign(this.products, data.items || {});
                const grid = this.$refs.more.previousElementSibling;
                if (grid && data.html) grid.insertAdjacentHTML('beforeend', data.html);
                this.moreNext = data.next || null;
                if (!this.moreNext && this.moreObs) this.moreObs.disconnect();
            } catch (e) {
                this.moreError = true;
            } finally {
                this.moreLoading = false;
                // Tela grande pode já mostrar o fim: confere de novo.
                this.$nextTick(() => {
                    const m = this.$refs.more;
                    if (this.moreNext && !this.moreError && m && m.getBoundingClientRect().top < innerHeight + 900) this.loadMore();
                });
            }
        },
        products,
        p: null,
        qty: 1,
        slide: 0,
        toast: '',
        cartOpen: false,
        timer: null,
        money(v) {
            return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        },
        intPart(v) { return Math.floor(Number(v) || 0).toLocaleString('pt-BR'); },
        cents(v) { return String(Math.round((Number(v) || 0) * 100) % 100).padStart(2, '0'); },
        goTo(i) {
            const t = this.$refs.track;
            if (t) t.scrollTo({ left: i * t.clientWidth, behavior: 'smooth' });
            this.slide = i;
        },
        add(p, qty = 1) {
            Alpine.store('cart').add(p, qty);
            this.say('Adicionado ao carrinho');
        },
        say(text) {
            this.toast = text;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.toast = '', 2200);
        },
        fav(id) {
            const on = Alpine.store('favs').toggle(id);
            this.say(on ? 'Salvo em Meus favoritos' : 'Tirado dos favoritos');
        },
        openCart() {
            this.toast = '';
            this.cartOpen = true;
            document.documentElement.style.overflow = 'hidden';
        },
        closeCart() {
            this.cartOpen = false;
            document.documentElement.style.overflow = '';
        },
        buyNow() {
            Alpine.store('cart').add(this.p, this.qty);
            this.openCart();
        },
        ...extra,
    };
}
</script>
