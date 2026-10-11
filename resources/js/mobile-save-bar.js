/*
 * Botão Salvar fixo no celular e no iPad.
 * O botão principal de cada formulário leva o atributo data-mobile-save. Quando ele sai da tela
 * (o usuário rolou o formulário), aparece uma barra fixa acima da barra de baixo com o mesmo
 * botão; tocar nela clica no botão original, então a ação é sempre a mesma do formulário.
 */
(function () {
    const MAX_WIDTH = 1180;
    let bar = null;
    let target = null;
    let io = null;
    let mo = null;
    let targetVisible = true;

    function labelOf(btn) {
        const custom = btn.getAttribute('data-mobile-save');
        if (custom) return custom;
        const clone = btn.cloneNode(true);
        clone.querySelectorAll('[wire\\:loading], .sr-only, svg, i').forEach((el) => el.remove());
        return clone.textContent.replace(/\s+/g, ' ').trim() || 'Salvar';
    }

    function ensureBar() {
        if (bar && document.body.contains(bar)) return bar;
        bar = document.createElement('div');
        bar.id = 'fm-save-bar';
        bar.setAttribute('aria-hidden', 'true');
        bar.innerHTML = '<button type="button" class="fm-save-bar-btn"><i class="bi bi-check2-circle"></i><span></span></button>';
        bar.querySelector('button').addEventListener('click', () => {
            if (!target || target.disabled) return;
            const before = errorFields().length;
            target.click();
            // Se a validação falhar, leva até o primeiro campo com erro (os erros ficam lá em cima).
            let tries = 0;
            const timer = setInterval(() => {
                const errs = errorFields();
                if (errs.length > before || (errs.length && ++tries > 6)) {
                    clearInterval(timer);
                    errs[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else if (++tries > 15) {
                    clearInterval(timer);
                }
            }, 200);
        });
        document.body.appendChild(bar);
        return bar;
    }

    const ERROR_SEL = '.text-red-500, .text-red-600, .text-rose-500, .text-rose-600, .invalid-feedback, .field-error, [aria-invalid="true"]';
    function errorFields() {
        const form = target && (target.form || target.closest('form') || document.querySelector('main'));
        const root = (target && target.form) ? target.form : (form || document);
        return [...root.querySelectorAll(ERROR_SEL)].filter((el) => el.offsetParent !== null && el.textContent.trim().length > 0 && el.textContent.trim().length < 200);
    }

    function tabbarOffset() {
        const tb = document.querySelector('.mobile-bottom-tabbar');
        if (!tb) return 0;
        const st = getComputedStyle(tb);
        if (st.display === 'none' || st.visibility === 'hidden') return 0;
        return tb.getBoundingClientRect().height;
    }

    function sync() {
        if (!bar) return;
        const show = target && !targetVisible && window.innerWidth <= MAX_WIDTH && document.body.contains(target);
        bar.classList.toggle('is-visible', !!show);
        document.body.classList.toggle('has-save-bar', !!(target && window.innerWidth <= MAX_WIDTH));
        bar.setAttribute('aria-hidden', show ? 'false' : 'true');
        if (!target) return;
        const busy = target.disabled;
        const btn = bar.querySelector('button');
        btn.disabled = busy;
        bar.querySelector('span').textContent = busy ? 'Salvando...' : labelOf(target);
        bar.querySelector('i').className = busy ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-check2-circle';
        bar.style.bottom = 'calc(' + tabbarOffset() + 'px + 0.5rem)';
    }

    function attach() {
        const next = document.querySelector('[data-mobile-save]');
        if (next === target) { sync(); return; }
        if (io) { io.disconnect(); io = null; }
        if (mo) { mo.disconnect(); mo = null; }
        target = next;
        targetVisible = true;
        if (!target) { sync(); return; }
        ensureBar();
        io = new IntersectionObserver((entries) => {
            targetVisible = entries[0].isIntersecting;
            sync();
        });
        io.observe(target);
        mo = new MutationObserver(sync);
        mo.observe(target, { attributes: true, attributeFilter: ['disabled'] });
        sync();
    }

    document.addEventListener('DOMContentLoaded', attach);
    document.addEventListener('livewire:navigated', () => setTimeout(attach, 50));
    document.addEventListener('livewire:init', () => {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('morph.updated', () => setTimeout(attach, 30));
        }
    });
    window.addEventListener('resize', sync);
})();
