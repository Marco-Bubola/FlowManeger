{{-- Modal de confirmação GLOBAL e moderno (Midnight Galaxy) — substitui confirm()/alert() nativos --}}
<div x-data="fmConfirmModal()"
     @fm-confirm.window="open($event.detail)"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fm-confirm-overlay"
     @keydown.escape.window="cancel()"
     @click.self="cancel()"
     style="display:none;">
    <div class="fm-confirm-card"
         :class="`fm-confirm--${variant}`"
         x-show="show"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        <div class="fm-confirm-icon"><i class="bi" :class="iconClass"></i></div>
        <h3 class="fm-confirm-title" x-text="title"></h3>
        <p class="fm-confirm-text" x-html="message"></p>
        <div class="fm-confirm-actions">
            <button type="button" @click="cancel()" class="fm-confirm-btn-cancel" x-show="!alertMode">
                <i class="bi bi-x-lg"></i> <span x-text="cancelText"></span>
            </button>
            <button type="button" @click="confirm()" class="fm-confirm-btn-ok">
                <i class="bi" :class="okIcon"></i> <span x-text="confirmText"></span>
            </button>
        </div>
    </div>
</div>

@once
<style>
    [x-cloak] { display: none !important; }
    .fm-confirm-overlay { position: fixed; inset: 0; z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 1.5rem; background: rgba(43,30,62,0.55); backdrop-filter: blur(8px); }
    .fm-confirm-card { width: 100%; max-width: 430px; background: #fff; border-radius: 1.5rem; padding: 2rem 1.75rem 1.6rem; text-align: center; box-shadow: 0 30px 70px rgba(43,30,62,0.4); border: 1px solid rgba(164,144,194,0.25); }
    .dark .fm-confirm-card { background: linear-gradient(160deg,#2b1e3e,#241a35); border-color: rgba(164,144,194,0.3); }
    .fm-confirm-icon { width: 4.5rem; height: 4.5rem; margin: 0 auto 1.1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.1rem; color: #fff; }
    .fm-confirm--danger  .fm-confirm-icon { background: linear-gradient(135deg,#f87171,#dc2626); box-shadow: 0 10px 26px rgba(220,38,38,0.45); }
    .fm-confirm--warning .fm-confirm-icon { background: linear-gradient(135deg,#fbbf24,#f59e0b); box-shadow: 0 10px 26px rgba(245,158,11,0.45); }
    .fm-confirm--info    .fm-confirm-icon { background: linear-gradient(135deg,#a490c2,#4a4e8f); box-shadow: 0 10px 26px rgba(74,78,143,0.45); }
    .fm-confirm--success .fm-confirm-icon { background: linear-gradient(135deg,#34d399,#059669); box-shadow: 0 10px 26px rgba(5,150,105,0.45); }
    .fm-confirm-title { font-size: 1.25rem; font-weight: 800; color: #2b1e3e; margin: 0 0 0.6rem; }
    .dark .fm-confirm-title { color: #e6e6fa; }
    .fm-confirm-text { font-size: 0.92rem; line-height: 1.55; color: #475569; margin: 0 0 1.4rem; }
    .dark .fm-confirm-text { color: #cbd5e1; }
    .fm-confirm-text strong { color: #4a4e8f; }
    .dark .fm-confirm-text strong { color: #a490c2; }
    .fm-confirm-actions { display: flex; gap: 0.7rem; }
    .fm-confirm-btn-cancel, .fm-confirm-btn-ok { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.75rem 1rem; border-radius: 0.85rem; font-size: 0.9rem; font-weight: 800; cursor: pointer; border: none; transition: all 0.18s; }
    .fm-confirm-btn-cancel { background: rgba(148,163,184,0.18); color: #475569; }
    .fm-confirm-btn-cancel:hover { background: rgba(148,163,184,0.3); }
    .dark .fm-confirm-btn-cancel { background: rgba(148,163,184,0.18); color: #cbd5e1; }
    .fm-confirm-btn-ok { color: #fff; }
    .fm-confirm--danger  .fm-confirm-btn-ok { background: linear-gradient(135deg,#f87171,#dc2626); box-shadow: 0 6px 18px rgba(220,38,38,0.4); }
    .fm-confirm--danger  .fm-confirm-btn-ok:hover { background: linear-gradient(135deg,#ef4444,#b91c1c); transform: translateY(-1px); }
    .fm-confirm--warning .fm-confirm-btn-ok { background: linear-gradient(135deg,#fbbf24,#f59e0b); box-shadow: 0 6px 18px rgba(245,158,11,0.4); }
    .fm-confirm--warning .fm-confirm-btn-ok:hover { background: linear-gradient(135deg,#f59e0b,#d97706); transform: translateY(-1px); }
    .fm-confirm--info    .fm-confirm-btn-ok { background: linear-gradient(135deg,#a490c2,#4a4e8f); box-shadow: 0 6px 18px rgba(74,78,143,0.4); }
    .fm-confirm--info    .fm-confirm-btn-ok:hover { background: linear-gradient(135deg,#9582bd,#3f4380); transform: translateY(-1px); }
    .fm-confirm--success .fm-confirm-btn-ok { background: linear-gradient(135deg,#34d399,#059669); box-shadow: 0 6px 18px rgba(5,150,105,0.4); }
    .fm-confirm--success .fm-confirm-btn-ok:hover { background: linear-gradient(135deg,#10b981,#047857); transform: translateY(-1px); }
</style>

<script>
function fmConfirmModal() {
    return {
        show: false,
        title: 'Confirmar',
        message: '',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        variant: 'danger',     // danger | warning | info | success
        alertMode: false,
        _resolver: null,
        get iconClass() {
            return ({ danger: 'bi-exclamation-triangle-fill', warning: 'bi-exclamation-circle-fill', info: 'bi-info-circle-fill', success: 'bi-check-circle-fill' })[this.variant] || 'bi-question-circle-fill';
        },
        get okIcon() {
            return this.variant === 'danger' ? 'bi-trash3-fill' : 'bi-check-lg';
        },
        open(detail) {
            detail = detail || {};
            this.title = detail.title || 'Confirmar ação';
            this.message = detail.message || '';
            this.confirmText = detail.confirmText || 'Confirmar';
            this.cancelText = detail.cancelText || 'Cancelar';
            this.variant = detail.variant || 'danger';
            this.alertMode = !!detail.alertMode;
            this._resolver = detail.resolve || null;
            this.show = true;
        },
        confirm() { this.resolveWith(true); },
        cancel() { this.resolveWith(false); },
        resolveWith(value) {
            this.show = false;
            const r = this._resolver; this._resolver = null;
            if (r) setTimeout(() => r(value), 160);
        }
    };
}

/* API global — Promise-based, substitui confirm() */
window.fmConfirm = function(opts) {
    if (typeof opts === 'string') opts = { message: opts };
    return new Promise((resolve) => {
        window.dispatchEvent(new CustomEvent('fm-confirm', { detail: { ...(opts || {}), resolve } }));
    });
};

/* Helper para formulários: onsubmit="return fmConfirmSubmit(event, 'mensagem')" */
window.fmConfirmSubmit = function(event, message, opts) {
    const form = event.target.closest('form');
    if (!form || form.__fmConfirmed) { if (form) form.__fmConfirmed = false; return true; }
    event.preventDefault();
    window.fmConfirm({ message, variant: 'danger', confirmText: 'Sim, continuar', ...(opts || {}) })
        .then(function(ok) { if (ok) { form.__fmConfirmed = true; form.submit(); } });
    return false;
};

/* fmAlert — modal informativo (ou use window.notify para toast) */
window.fmAlert = function(opts) {
    if (typeof opts === 'string') opts = { message: opts };
    return new Promise((resolve) => {
        window.dispatchEvent(new CustomEvent('fm-confirm', {
            detail: { variant: 'info', confirmText: 'OK', alertMode: true, ...(opts || {}), resolve }
        }));
    });
};

/* Sobrescreve window.alert nativo → toast moderno (sem quebrar chamadas existentes) */
(function () {
    if (window.__fmAlertPatched) return;
    window.__fmAlertPatched = true;
    const nativeAlert = window.alert.bind(window);
    window.alert = function (msg) {
        try {
            const text = String(msg ?? '');
            const isError = /erro|error|❌|inválid|falha|n[ãa]o encontrad/i.test(text);
            const type = isError ? 'error' : 'info';
            if (typeof window.notify === 'function') {
                window.notify(type, text.replace(/^[✅❌⚠️ℹ️]\s*/, ''));
            } else {
                nativeAlert(msg);
            }
        } catch (e) { nativeAlert(msg); }
    };
})();

/* Interceptador GLOBAL de wire:confirm → usa o modal moderno em vez do confirm() nativo.
   Funciona em TODOS os wire:confirm do app sem editar cada arquivo. */
(function () {
    if (window.__fmWireConfirmPatched) return;
    window.__fmWireConfirmPatched = true;

    document.addEventListener('click', function (e) {
        const el = e.target.closest('[wire\\:confirm]');
        if (!el) return;

        const message = el.getAttribute('wire:confirm');
        if (!message) return;

        // Bloqueia o handler nativo do Livewire (capture phase, antes do bubbling)
        e.preventDefault();
        e.stopImmediatePropagation();

        const isDanger = /excluir|deletar|remover|apagar|desconectar|revogar|cancelar|encerrar/i.test(message);

        window.fmConfirm({
            title: isDanger ? 'Confirmar ação' : 'Confirmação',
            message: message,
            variant: isDanger ? 'danger' : 'info',
            confirmText: isDanger ? 'Sim, continuar' : 'Confirmar',
        }).then(function (ok) {
            if (!ok) return;
            // Re-dispara o clique sem o wire:confirm → Livewire executa a ação normalmente
            el.removeAttribute('wire:confirm');
            el.click();
            setTimeout(function () { el.setAttribute('wire:confirm', message); }, 400);
        });
    }, true); // capture = true
})();
</script>
@endonce
