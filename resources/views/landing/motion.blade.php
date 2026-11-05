{{-- Movimento da página inicial do FlowManager (Admin › Planos › Página inicial). "Sem movimento" não carrega nada daqui. --}}
<style>
    /* Só esconde se o script rodou (sem JS a página aparece normal) */
    html.lp-js .lp-rv { opacity: 0; transform: translateY(34px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); transition-delay: var(--lp-dl, 0ms); }
    html.lp-js .lp-rv.lp-in { opacity: 1; transform: none; }
    html.lp-js .lp-w { display: inline-block; overflow: hidden; vertical-align: top; padding-bottom: .06em; margin-bottom: -.06em; }
    html.lp-js .lp-w > span { display: inline-block; transform: translateY(105%); transition: transform .9s cubic-bezier(.2,.75,.15,1); transition-delay: var(--lp-dl, 0ms); }
    html.lp-js .lp-words.lp-in .lp-w > span { transform: none; }
    /* Telinha do sistema deitada que levanta ao rolar */
    html.lp-js body.lp-mo-completo .preview-section { perspective: 1400px; }
    html.lp-js body.lp-mo-completo .preview-browser { transform: rotateX(var(--lp-tilt, 0deg)) scale(var(--lp-sc, 1)); transform-origin: 50% 0; will-change: transform; }
    /* Cards que inclinam com o mouse */
    body.lp-mo-completo .lp-tilt { transition: transform .35s ease, box-shadow .35s ease; transform-style: preserve-3d; }
    body.lp-mo-completo .lp-tilt:hover { box-shadow: 0 26px 60px rgba(0, 0, 0, .22); }
    /* Depoimentos em vidro 3D */
    .lp-cf { perspective: 1200px; position: relative; display: block !important; height: var(--lp-h, 320px); margin-top: 3rem; }
    .lp-cf > .testi-card { position: absolute; top: 0; left: 50%; width: min(440px, 84vw); margin-left: calc(min(440px, 84vw) / -2);
        transition: transform .75s cubic-bezier(.2,.7,.2,1), opacity .6s ease, filter .6s ease;
        transform: translateX(calc(var(--d) * 62%)) translateZ(calc(var(--a) * -180px)) rotateY(calc(var(--d) * -32deg));
        opacity: calc(1 - var(--a) * .38); filter: blur(calc(var(--a) * 1.5px)); cursor: pointer;
        backdrop-filter: blur(16px) saturate(1.3); -webkit-backdrop-filter: blur(16px) saturate(1.3);
    }
    .lp-cf > .testi-card.on { cursor: default; box-shadow: 0 30px 70px rgba(0, 0, 0, .28); }
    .lp-cf-dots { display: flex; gap: .5rem; justify-content: center; margin-top: 1.4rem; }
    .lp-cf-dots button { width: 9px; height: 9px; border-radius: 99px; border: 0; padding: 0; background: var(--faint); cursor: pointer; transition: width .3s, background .3s; }
    .lp-cf-dots button.on { width: 26px; background: var(--text); }
    @media (prefers-reduced-motion: reduce) {
        html.lp-js .lp-rv, html.lp-js .lp-w > span { opacity: 1 !important; transform: none !important; transition: none !important; }
        html.lp-js body.lp-mo-completo .preview-browser { transform: none !important; }
        .lp-cf > .testi-card { transition: none; }
    }
</style>
<script>
(function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!('IntersectionObserver' in window)) return;
    document.documentElement.classList.add('lp-js');
    document.addEventListener('DOMContentLoaded', function () {
        var full = document.body.classList.contains('lp-mo-completo');

        // Títulos aparecendo palavra por palavra
        var words = full ? document.querySelectorAll('.hero-title, .section-h2, .cta-final h2') : [];
        words.forEach(function (h) {
            var i = 0;
            (function split(node) {
                Array.prototype.slice.call(node.childNodes).forEach(function (c) {
                    if (c.nodeType === 3) {
                        var frag = document.createDocumentFragment();
                        c.textContent.split(/(\s+)/).forEach(function (part) {
                            if (!part) return;
                            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
                            var w = document.createElement('span'); w.className = 'lp-w';
                            var s = document.createElement('span'); s.textContent = part; s.style.setProperty('--lp-dl', (i++ * 70) + 'ms');
                            w.appendChild(s); frag.appendChild(w);
                        });
                        c.parentNode.replaceChild(frag, c);
                    } else if (c.nodeType === 1 && c.tagName !== 'BR') {
                        split(c);
                    }
                });
            })(h);
            h.classList.add('lp-words');
        });

        // Blocos subindo ao rolar, um depois do outro
        var groups = [
            '.hero-eyebrow, .hero-sub, .hero-cta, .hero-trust, .preview-browser',
            '.stat-box', '.section-tag, .section-sub', '.feat-card', '.step-card',
            '.testi-card', '.plan-card', '.faq-item', '.cta-final-inner > p, .cta-final-actions, .cta-final-note', '.footer-top, .footer-bottom'
        ];
        if (!full) groups.unshift('.hero-title, .section-h2, .cta-final h2');
        groups.forEach(function (sel) {
            var byParent = new Map();
            document.querySelectorAll(sel).forEach(function (el) {
                if (el.classList.contains('lp-words')) return;
                var k = byParent.get(el.parentNode) || 0;
                byParent.set(el.parentNode, k + 1);
                el.classList.add('lp-rv');
                el.style.setProperty('--lp-dl', Math.min(k, 6) * 90 + 'ms');
            });
        });
        var io = new IntersectionObserver(function (list) {
            list.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('lp-in'); io.unobserve(e.target); } });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        document.querySelectorAll('.lp-rv, .lp-words').forEach(function (el) { io.observe(el); });

        if (!full || reduce) return;

        // Telinha do sistema levantando
        var pb = document.querySelector('.preview-browser');
        if (pb) {
            var tick = function () {
                var r = pb.getBoundingClientRect(), vh = window.innerHeight;
                var p = Math.min(1, Math.max(0, (vh - r.top) / (vh * 0.9)));
                pb.style.setProperty('--lp-tilt', (24 * (1 - p)).toFixed(2) + 'deg');
                pb.style.setProperty('--lp-sc', (0.9 + 0.1 * p).toFixed(3));
            };
            window.addEventListener('scroll', function () { requestAnimationFrame(tick); }, { passive: true });
            tick();
        }

        // Cards inclinando com o mouse
        if (window.matchMedia('(hover: hover)').matches) {
            document.querySelectorAll('.feat-card, .plan-card, .step-card').forEach(function (c) {
                c.classList.add('lp-tilt');
                c.addEventListener('pointermove', function (e) {
                    var r = c.getBoundingClientRect();
                    var x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
                    c.style.transform = 'perspective(900px) rotateX(' + (-y * 7).toFixed(2) + 'deg) rotateY(' + (x * 9).toFixed(2) + 'deg) translateY(-4px)';
                });
                c.addEventListener('pointerleave', function () { c.style.transform = ''; });
            });
        }

        // Depoimentos girando em vidro 3D
        var grid = document.querySelector('.testi-grid');
        var cards = grid ? Array.prototype.slice.call(grid.children) : [];
        if (cards.length >= 3) {
            var n = cards.length, active = 1, timer;
            grid.classList.add('lp-cf');
            var sec = grid.closest('section'); if (sec) sec.style.overflow = 'hidden';
            var dots = document.createElement('div'); dots.className = 'lp-cf-dots';
            cards.forEach(function (c, i) {
                c.classList.remove('lp-rv'); c.classList.add('lp-in');
                var b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Depoimento ' + (i + 1));
                b.addEventListener('click', function () { go(i, true); });
                dots.appendChild(b);
                c.addEventListener('click', function () { if (i !== active) go(i, true); });
            });
            grid.after(dots);
            var layout = function () {
                cards.forEach(function (c, i) {
                    var d = i - active; if (d > n / 2) d -= n; if (d < -n / 2) d += n;
                    var a = Math.abs(d);
                    c.style.setProperty('--d', d); c.style.setProperty('--a', a);
                    c.style.zIndex = String(10 - a);
                    c.classList.toggle('on', d === 0);
                });
                dots.querySelectorAll('button').forEach(function (b, i) { b.classList.toggle('on', i === active); });
                var h = Math.max.apply(null, cards.map(function (c) { return c.offsetHeight; }));
                if (h) grid.style.setProperty('--lp-h', (h + 20) + 'px');
            };
            var go = function (i, user) { active = (i + n) % n; layout(); if (user) restart(); };
            var restart = function () { clearInterval(timer); timer = setInterval(function () { if (!document.hidden && !grid.matches(':hover')) go(active + 1); }, 4200); };
            var sx = null;
            grid.addEventListener('pointerdown', function (e) { sx = e.clientX; });
            grid.addEventListener('pointerup', function (e) { if (sx === null) return; var dx = e.clientX - sx; sx = null; if (Math.abs(dx) > 40) go(active + (dx < 0 ? 1 : -1), true); });
            window.addEventListener('resize', layout);
            layout(); restart();
        }
    });
})();
</script>
