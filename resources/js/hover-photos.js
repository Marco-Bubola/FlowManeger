// Fotos que trocam sozinhas ao deixar o mouse em cima do card (produtos e catálogo).
// Marcação: um elemento com data-hover-photos (ou o card [data-hover-card] em volta dele)='["url1","url2"]' e um <img data-hp-img> dentro;
// bolinhas opcionais em [data-hp-dots]. Espera um pouco parado em cima, troca a cada 1,3 s e,
// ao sair, volta para a primeira foto. Em tela de toque não faz nada (lá o card mostra as bolinhas).
(() => {
    if (window.__fmHoverPhotos) return;
    window.__fmHoverPhotos = true;

    const style = document.createElement('style');
    style.textContent = `
        [data-hover-photos] img[data-hp-img] { transition: opacity .25s ease; }
        [data-hover-photos] img[data-hp-img].hp-fade { opacity: .25; }
        .hp-dots { position: absolute; left: 50%; bottom: 8px; translate: -50% 0; z-index: 3; display: flex; gap: 4px; padding: 3px 6px; border-radius: 999px; background: rgba(15,23,42,.38); pointer-events: none; }
        .hp-dots i { display: block; width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.55); transition: background .2s, transform .2s; }
        .hp-dots i.on { background: #fff; transform: scale(1.25); }
    `;
    document.head.appendChild(style);

    const fine = window.matchMedia('(hover: hover) and (pointer: fine)');
    let cur = null;

    const list = (el) => {
        try { return (JSON.parse(el.dataset.hoverPhotos || '[]') || []).filter(Boolean); } catch (e) { return []; }
    };
    const dots = (el, i) => el.querySelectorAll('[data-hp-dots] i').forEach((d, k) => d.classList.toggle('on', k === i));
    const show = (el, i) => {
        const s = el._hp;
        if (!s || !s.img) return;
        s.img.classList.add('hp-fade');
        setTimeout(() => {
            if (el._hp !== s) return;
            s.img.src = s.urls[i];
            s.img.classList.remove('hp-fade');
        }, 180);
        dots(el, i);
    };
    const stop = (el) => {
        const s = el && el._hp;
        if (!s) return;
        clearTimeout(s.wait);
        clearInterval(s.loop);
        if (s.img && s.img.src !== s.first) s.img.src = s.first;
        if (s.img) s.img.classList.remove('hp-fade');
        dots(el, 0);
        el._hp = null;
    };
    const start = (el) => {
        const urls = list(el);
        const img = el.querySelector('img[data-hp-img]');
        if (urls.length < 2 || !img) return;
        urls.slice(1).forEach((u) => { const p = new Image(); p.src = u; });
        const s = { urls, img, first: img.src, i: 0 };
        el._hp = s;
        s.wait = setTimeout(() => {
            const next = () => { s.i = (s.i + 1) % urls.length; show(el, s.i); };
            next();
            s.loop = setInterval(next, 1300);
        }, 700);
    };

    document.addEventListener('mouseover', (e) => {
        if (!fine.matches) return;
        // O link do card do catálogo cobre o card todo, então vale o card inteiro ([data-hover-card]).
        const t = e.target.closest ? e.target : null;
        const el = t ? (t.closest('[data-hover-photos]') || t.closest('[data-hover-card]')?.querySelector('[data-hover-photos]') || null) : null;
        if (el === cur) return;
        stop(cur);
        cur = el;
        if (el) start(el);
    }, { passive: true });
    document.addEventListener('mouseout', (e) => {
        if (!e.relatedTarget && cur) { stop(cur); cur = null; }
    }, { passive: true });
})();
