// Animações nas telas do app (Configurações › Aparência › Movimento › "Cards surgindo").
// Só liga com <html data-fm-motion="sim|zoom|lado|desfoque">; desligado (padrão) não mexe em nada.
// Cabeçalho da página desce de leve, e cards, indicadores e listas surgem ao rolar, um depois do outro.
// Usa animações do navegador (el.animate) e não classes: o Livewire refaz as classes dos
// elementos a cada atualização da tela e apagaria qualquer marca posta aqui.
(function () {
    if (window.__fmMotionBound) return;
    window.__fmMotionBound = true;

    const reduce = () => window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mode = () => document.documentElement.getAttribute('data-fm-motion');
    const FROM = {
        sim: { opacity: 0, transform: 'translateY(18px) scale(.985)' },
        zoom: { opacity: 0, transform: 'scale(.9)' },
        lado: { opacity: 0, transform: 'translateX(-28px)' },
        desfoque: { opacity: 0, transform: 'scale(1.02)', filter: 'blur(8px)' },
        virar: { opacity: 0, transform: 'perspective(900px) rotateX(24deg) translateY(24px)', transformOrigin: '50% 100%' },
        cortina: { clipPath: 'inset(0 0 100% 0)' },
    };
    // Velocidade (Aparência › Movimento › Velocidade): multiplica a duração de tudo
    const speed = () => ({ calma: 1.6, rapida: .6 })[document.documentElement.getAttribute('data-fm-speed')] || 1;
    const isOn = () => !!FROM[mode()] && !reduce()
        && !document.documentElement.classList.contains('no-animations') // "Reduzir animações" em Aparência
        && 'IntersectionObserver' in window && typeof Element.prototype.animate === 'function';
    const CARD = /(^|\s)([a-z0-9]+-)*card(-modern)?(\s|$)/;

    let io = null;
    let mo = null;
    let seen = new WeakSet();
    let batch = 0;
    let batchTimer = null;

    function looksLikeCard(el) {
        if (/^(INPUT|SELECT|TEXTAREA|BUTTON|LABEL|OPTION|TEMPLATE|SCRIPT|STYLE)$/.test(el.tagName)) return false;
        const c = typeof el.className === 'string' ? el.className : '';
        if (CARD.test(c)) return true;
        // Campos de formulário em grade não animam
        if (el.querySelector('input:not([type="checkbox"]):not([type="hidden"]), select, textarea')) return false;
        return /rounded/.test(c) && /(shadow|border)/.test(c);
    }

    function watch(el) {
        if (seen.has(el) || el.closest('[role="dialog"], [aria-modal="true"], .mobile-action-sheet, .fixed')) return;
        seen.add(el);
        io.observe(el);
    }

    function scan(root) {
        if (!io) return;
        root.querySelectorAll('.grid, .dash-kpis, [class*="-grid"]').forEach((g) => {
            const kids = Array.from(g.children).filter((k) => k.nodeType === 1 && looksLikeCard(k));
            if (kids.length >= 2) kids.forEach(watch);
        });
        root.querySelectorAll('.product-card-modern, .ap-card').forEach(watch);
    }

    function show(el) {
        // Os que entram juntos na tela vão um depois do outro
        const d = Math.min(batch++, 8) * 55;
        clearTimeout(batchTimer);
        batchTimer = setTimeout(() => { batch = 0; }, 120);
        const from = FROM[mode()] || FROM.sim;
        const to = from.clipPath ? { clipPath: 'inset(0 0 0 0)' } : (from.filter ? { opacity: 1, transform: 'none', filter: 'blur(0)' } : { opacity: 1, transform: 'none' });
        el.animate([from, to], { duration: 600 * speed(), delay: d * speed(), easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'backwards' });
    }

    function stop() {
        if (io) io.disconnect();
        if (mo) mo.disconnect();
        io = mo = null;
        seen = new WeakSet();
    }

    function start() {
        stop();
        if (!isOn()) return;
        const main = document.getElementById('mainContent');
        if (!main) return;
        io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (!e.isIntersecting) return;
                io.unobserve(e.target);
                show(e.target);
            });
        }, { threshold: 0 });
        // Cabeçalho entrando de cima a cada página aberta
        main.querySelectorAll('.app-ph').forEach((h) => h.animate([
            { opacity: 0, transform: 'translateY(-14px)' }, { opacity: 1, transform: 'none' },
        ], { duration: 550, easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'backwards' }));
        scan(main);
        // Itens que chegam depois (rolagem infinita, filtros, abas)
        mo = new MutationObserver((list) => {
            if (!io) return;
            list.forEach((m) => m.addedNodes.forEach((n) => {
                if (n.nodeType !== 1) return;
                const p = n.parentElement;
                if (n.matches('.product-card-modern, .ap-card')) watch(n);
                else if (p && looksLikeCard(n) && p.children.length > 1 && /(^|\s)grid(\s|$)|-grid|dash-kpis/.test(typeof p.className === 'string' ? p.className : '')) watch(n);
                if (n.querySelectorAll) scan(n);
            }));
        });
        mo.observe(main, { childList: true, subtree: true });
    }

    document.addEventListener('livewire:navigated', start);

    // Troca de tela (Aparência › Movimento › Troca de tela): a página nova entra deslizando,
    // aparecendo aos poucos ou subindo. "Direta" (padrão) não mexe em nada.
    const PAGE = {
        desliza: [{ opacity: 0, transform: 'translateX(36px)' }, { opacity: 1, transform: 'none' }],
        esmaece: [{ opacity: 0 }, { opacity: 1 }],
        sobe: [{ opacity: 0, transform: 'translateY(26px)' }, { opacity: 1, transform: 'none' }],
    };
    let firstLoad = true;
    document.addEventListener('livewire:navigated', () => {
        if (firstLoad) { firstLoad = false; return; } // a primeira abertura do app não anima
        const k = document.documentElement.getAttribute('data-fm-page');
        const main = document.getElementById('mainContent');
        if (!PAGE[k] || !main || reduce() || document.documentElement.classList.contains('no-animations') || typeof main.animate !== 'function') return;
        main.animate(PAGE[k], { duration: (k === 'esmaece' ? 380 : 420) * speed(), easing: 'cubic-bezier(.2,.7,.2,1)' });
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
    // Ligou ou desligou na tela de Aparência (só reage quando muda de verdade)
    let was = document.documentElement.getAttribute('data-fm-motion');
    new MutationObserver(() => {
        const now = document.documentElement.getAttribute('data-fm-motion');
        if (now !== was) { was = now; if (FROM[now]) start(); else stop(); }
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-fm-motion'] });

    // ───── Mouse em cima dos cards › Card inclina (data-fm-hover="inclinar") ─────
    // Aproxima e Levanta são só CSS (fm-motion.css). Aqui o card segue o mouse.
    const tiltOn = () => document.documentElement.getAttribute('data-fm-hover') === 'inclinar' && !reduce()
        && !document.documentElement.classList.contains('no-animations');
    const TILT = '#mainContent .product-card-modern, #mainContent .dash-kpis > *, #mainContent :is(.grid, [class*="-grid"]) > [class*="rounded"]:is([class*="shadow"], [class*="border"]):not(:has(input, select, textarea))';
    let tilted = null;
    const untilt = () => { if (tilted) { tilted.style.transform = ''; tilted.style.transition = ''; tilted = null; } };
    document.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse' || !tiltOn()) return untilt();
        const card = e.target.closest && e.target.closest(TILT);
        if (card !== tilted) untilt();
        if (!card || card.closest('[role="dialog"], .fixed')) return;
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        tilted = card;
        card.style.transition = 'transform .08s linear';
        card.style.transform = `perspective(800px) rotateX(${(-y * 8).toFixed(2)}deg) rotateY(${(x * 10).toFixed(2)}deg) translateY(-2px)`;
    }, { passive: true });
    document.addEventListener('pointerleave', untilt);
    document.addEventListener('livewire:navigating', untilt);

    // ───── Ao pôr produto na venda (data-fm-cart="pular" | "voar") ─────
    // Nova venda, Editar venda e Adicionar produtos: tocar num produto que ainda não estava escolhido
    // faz o carrinho pular ou a foto voar até ele. Sem a opção (padrão), nada muda.
    const cartMode = () => document.documentElement.getAttribute('data-fm-cart');
    const cartBtn = () => Array.from(document.querySelectorAll('button .bi-cart3'))
        .map((i) => i.closest('button')).filter((b) => { if (!b) return false; const r = b.getBoundingClientRect(); return r.width > 0 && r.height > 0 && getComputedStyle(b).visibility !== 'hidden'; })
        .sort((a, b) => b.getBoundingClientRect().top - a.getBoundingClientRect().top)[0]; // o de baixo (botão flutuante) primeiro
    function bump(b) {
        if (!b || typeof b.animate !== 'function') return;
        b.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.25) rotate(-8deg)', offset: .3 }, { transform: 'scale(.94) rotate(4deg)', offset: .6 }, { transform: 'scale(1)' }],
            { duration: 600 * speed(), easing: 'cubic-bezier(.3,1.5,.5,1)' });
    }
    document.addEventListener('click', (e) => {
        const m = cartMode();
        if ((m !== 'pular' && m !== 'voar') || reduce() || document.documentElement.classList.contains('no-animations')) return;
        const card = e.target.closest && e.target.closest('.product-card-modern[wire\\:click^="toggleProduct"]');
        if (!card || card.classList.contains('selected')) return;
        const to = cartBtn();
        if (!to) return;
        const img = card.querySelector('img');
        if (m === 'pular' || !img || !img.currentSrc) return void setTimeout(() => bump(to), 120);
        const a = img.getBoundingClientRect(), b = to.getBoundingClientRect();
        const size = Math.min(a.width, a.height, 140);
        if (!size) return bump(to);
        const c = document.createElement('img');
        c.src = img.currentSrc;
        c.alt = '';
        Object.assign(c.style, { position: 'fixed', zIndex: 9999, pointerEvents: 'none', borderRadius: '14px', objectFit: 'cover', boxShadow: '0 12px 26px rgba(0,0,0,.3)',
            width: size + 'px', height: size + 'px', left: (a.left + a.width / 2 - size / 2) + 'px', top: (a.top + a.height / 2 - size / 2) + 'px' });
        document.body.appendChild(c);
        const dx = b.left + b.width / 2 - (a.left + a.width / 2), dy = b.top + b.height / 2 - (a.top + a.height / 2);
        c.animate([
            { transform: 'translate(0,0) scale(1)', opacity: 1 },
            { transform: `translate(${dx * .45}px, ${dy * .45 - 70}px) scale(.6) rotate(-12deg)`, opacity: 1, offset: .45 },
            { transform: `translate(${dx}px, ${dy}px) scale(.12) rotate(8deg)`, opacity: .6 },
        ], { duration: 760 * speed(), easing: 'cubic-bezier(.5,0,.6,1)' }).finished.then(() => { c.remove(); bump(cartBtn()); }, () => c.remove());
    }, true);
})();
