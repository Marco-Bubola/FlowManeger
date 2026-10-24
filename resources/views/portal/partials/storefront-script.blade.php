<script>
// Carrossel dos banners do topo: arrasta no celular, setas no computador, passa sozinho.
function sfHero(n) {
    return {
        n, i: 0, timer: null,
        init() {
            if (n < 2) return;
            const t = this.$refs.track;
            t.addEventListener('scroll', () => { this.i = Math.round(t.scrollLeft / Math.max(1, t.clientWidth)); }, { passive: true });
            ['mouseenter', 'touchstart', 'focusin'].forEach(e => this.$el.addEventListener(e, () => this.stop(), { passive: true }));
            this.$el.addEventListener('mouseleave', () => this.play());
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.play();
        },
        go(k) { const t = this.$refs.track; this.i = (k + n) % n; t.scrollTo({ left: this.i * t.clientWidth, behavior: 'smooth' }); },
        play() { this.stop(); this.timer = setInterval(() => { if (!document.hidden) this.go(this.i + 1); }, 5500); },
        stop() { clearInterval(this.timer); this.timer = null; },
    };
}
</script>
