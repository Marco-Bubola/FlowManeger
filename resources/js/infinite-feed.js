// Rolagem infinita das listas (x-infinite-feed). Também fica inline no componente,
// para a primeira tela funcionar antes do Vite; aqui cobre listas que só aparecem
// depois (troca de aba, filtro), quando o script inline não roda de novo.
window.infiniteFeed = window.infiniteFeed || ((hasMore, call) => ({
        busy: false,
        onScroll: null,
        init() {
            if (!hasMore) return;
            // Checa pela posição (o layout tem áreas com overflow que atrapalham o IntersectionObserver)
            let raf = 0;
            this.onScroll = () => {
                if (raf) return;
                raf = requestAnimationFrame(() => { raf = 0; this.check(); });
            };
            window.addEventListener('scroll', this.onScroll, { passive: true, capture: true });
            window.addEventListener('resize', this.onScroll, { passive: true });
            setTimeout(() => this.check(), 300);
        },
        check() {
            if (this.busy || !this.$el.isConnected) return;
            const r = this.$el.getBoundingClientRect();
            if (r.top - window.innerHeight > 900 || r.height === 0) return;
            this.busy = true;
            Promise.resolve(this.$wire[call.m](...call.a)).finally(() => { this.busy = false; });
        },
        destroy() {
            if (!this.onScroll) return;
            window.removeEventListener('scroll', this.onScroll, { capture: true });
            window.removeEventListener('resize', this.onScroll);
        },
    }));
