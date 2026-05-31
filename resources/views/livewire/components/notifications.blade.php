<div x-data="notificationManager(@js($flash))"
     x-init="init()"
     @notify.window="addNotification($event.detail)"
     class="fm-notify-stack"
     role="region"
     aria-label="Notificações">

    <!-- Botão limpar tudo (aparece com 3+ notificações) -->
    <div x-show="items.length >= 3" x-transition class="fm-notify-clear-wrap">
        <button type="button" @click="clearAll()" class="fm-notify-clear-btn">
            <i class="bi bi-x-circle"></i>
            <span x-text="`Limpar todas (${items.length})`"></span>
        </button>
    </div>

    <template x-for="n in items" :key="n.id">
        <div class="fm-notify-item"
             :class="`fm-notify-${n.type}`"
             x-show="n.show"
             x-transition:enter="fm-notify-enter"
             x-transition:enter-start="fm-notify-enter-start"
             x-transition:enter-end="fm-notify-enter-end"
             x-transition:leave="fm-notify-leave"
             x-transition:leave-start="fm-notify-leave-start"
             x-transition:leave-end="fm-notify-leave-end"
             @mouseenter="pause(n)"
             @mouseleave="resume(n)"
             role="alert">

            <div class="fm-notify-accent"></div>

            <div class="fm-notify-icon">
                <i :class="iconFor(n.type)"></i>
            </div>

            <div class="fm-notify-body">
                <p class="fm-notify-title" x-text="titleFor(n.type)"></p>
                <p class="fm-notify-msg" x-text="n.message"></p>
                <!-- Badge de agrupamento (quando a mesma msg se repete) -->
                <span x-show="n.count > 1" class="fm-notify-count" x-text="`×${n.count}`"></span>
            </div>

            <button type="button" @click="remove(n.id)" class="fm-notify-close" aria-label="Fechar">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="fm-notify-progress">
                <div class="fm-notify-progress-bar" :style="`animation-duration:${n.duration}ms; animation-play-state:${n.paused ? 'paused' : 'running'}`"></div>
            </div>
        </div>
    </template>
</div>

@once
<style>
/* ============ Sistema de Notificações Moderno (global) ============ */
.fm-notify-stack {
    position: fixed;
    top: 1rem; right: 1rem;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    width: 100%;
    max-width: 380px;
    pointer-events: none;
}
.fm-notify-stack > * { pointer-events: auto; }

.fm-notify-clear-wrap { display: flex; justify-content: flex-end; }
.fm-notify-clear-btn {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.72rem; font-weight: 700;
    padding: 0.3rem 0.7rem;
    border-radius: 9999px;
    background: rgba(43, 30, 62, 0.85);
    color: #e6e6fa;
    border: 1px solid rgba(164, 144, 194, 0.4);
    cursor: pointer;
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 14px rgba(43, 30, 62, 0.3);
    transition: all 0.18s;
}
.fm-notify-clear-btn:hover { background: rgba(43, 30, 62, 0.95); transform: translateY(-1px); }

.fm-notify-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 0.9rem 0.85rem 1.1rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(14px);
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.7);
    overflow: hidden;
    min-height: 56px;
}
.dark .fm-notify-item {
    background: rgba(30, 27, 58, 0.94);
    border-color: rgba(164, 144, 194, 0.22);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5), 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* Barra de destaque lateral */
.fm-notify-accent {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;
}
.fm-notify-success .fm-notify-accent { background: linear-gradient(180deg, #34d399, #059669); }
.fm-notify-error   .fm-notify-accent { background: linear-gradient(180deg, #f87171, #dc2626); }
.fm-notify-warning .fm-notify-accent { background: linear-gradient(180deg, #fbbf24, #f59e0b); }
.fm-notify-info    .fm-notify-accent { background: linear-gradient(180deg, #a490c2, #4a4e8f); }

/* Ícone */
.fm-notify-icon {
    flex-shrink: 0;
    width: 2.4rem; height: 2.4rem;
    border-radius: 0.75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
}
.fm-notify-success .fm-notify-icon { background: rgba(52, 211, 153, 0.16); color: #059669; }
.fm-notify-error   .fm-notify-icon { background: rgba(248, 113, 113, 0.16); color: #dc2626; }
.fm-notify-warning .fm-notify-icon { background: rgba(251, 191, 36, 0.18); color: #d97706; }
.fm-notify-info    .fm-notify-icon { background: rgba(164, 144, 194, 0.2); color: #4a4e8f; }
.dark .fm-notify-success .fm-notify-icon { background: rgba(52, 211, 153, 0.2); color: #6ee7b7; }
.dark .fm-notify-error   .fm-notify-icon { background: rgba(248, 113, 113, 0.2); color: #fca5a5; }
.dark .fm-notify-warning .fm-notify-icon { background: rgba(251, 191, 36, 0.22); color: #fcd34d; }
.dark .fm-notify-info    .fm-notify-icon { background: rgba(164, 144, 194, 0.25); color: #c4b5fd; }

/* Texto */
.fm-notify-body { flex: 1; min-width: 0; padding-top: 0.05rem; }
.fm-notify-title {
    font-size: 0.8rem; font-weight: 800;
    line-height: 1.2; margin: 0;
    letter-spacing: 0.01em;
}
.fm-notify-success .fm-notify-title { color: #047857; }
.fm-notify-error   .fm-notify-title { color: #b91c1c; }
.fm-notify-warning .fm-notify-title { color: #b45309; }
.fm-notify-info    .fm-notify-title { color: #3730a3; }
.dark .fm-notify-success .fm-notify-title { color: #6ee7b7; }
.dark .fm-notify-error   .fm-notify-title { color: #fca5a5; }
.dark .fm-notify-warning .fm-notify-title { color: #fcd34d; }
.dark .fm-notify-info    .fm-notify-title { color: #c4b5fd; }

.fm-notify-msg {
    font-size: 0.8rem; line-height: 1.35;
    margin: 0.1rem 0 0; color: #334155;
    word-break: break-word;
}
.dark .fm-notify-msg { color: #cbd5e1; }

.fm-notify-count {
    display: inline-block;
    margin-top: 0.25rem;
    font-size: 0.65rem; font-weight: 800;
    padding: 0.05rem 0.4rem;
    border-radius: 9999px;
    background: rgba(74, 78, 143, 0.15);
    color: #4a4e8f;
}
.dark .fm-notify-count { background: rgba(164, 144, 194, 0.25); color: #c4b5fd; }

/* Botão fechar */
.fm-notify-close {
    flex-shrink: 0;
    width: 1.6rem; height: 1.6rem;
    display: flex; align-items: center; justify-content: center;
    border-radius: 0.5rem;
    border: none; background: transparent;
    color: #94a3b8; cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.15s;
}
.fm-notify-close:hover { background: rgba(148, 163, 184, 0.18); color: #475569; }
.dark .fm-notify-close { color: #64748b; }
.dark .fm-notify-close:hover { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; }

/* Barra de progresso (countdown) */
.fm-notify-progress {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 3px;
    background: rgba(148, 163, 184, 0.18);
}
.fm-notify-progress-bar {
    height: 100%;
    width: 100%;
    transform-origin: left;
    animation: fm-notify-countdown linear forwards;
}
.fm-notify-success .fm-notify-progress-bar { background: linear-gradient(90deg, #34d399, #059669); }
.fm-notify-error   .fm-notify-progress-bar { background: linear-gradient(90deg, #f87171, #dc2626); }
.fm-notify-warning .fm-notify-progress-bar { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
.fm-notify-info    .fm-notify-progress-bar { background: linear-gradient(90deg, #a490c2, #4a4e8f); }
@keyframes fm-notify-countdown { from { transform: scaleX(1); } to { transform: scaleX(0); } }

/* Transições */
.fm-notify-enter { transition: all 0.32s cubic-bezier(0.16, 1, 0.3, 1); }
.fm-notify-enter-start { opacity: 0; transform: translateX(110%) scale(0.9); }
.fm-notify-enter-end { opacity: 1; transform: translateX(0) scale(1); }
.fm-notify-leave { transition: all 0.22s ease-in; }
.fm-notify-leave-start { opacity: 1; transform: translateX(0) scale(1); }
.fm-notify-leave-end { opacity: 0; transform: translateX(110%) scale(0.9); }

/* Responsivo: mobile ocupa largura toda */
@media (max-width: 480px) {
    .fm-notify-stack {
        left: 0.6rem; right: 0.6rem; top: 0.6rem;
        max-width: none;
    }
    .fm-notify-msg, .fm-notify-title { font-size: 0.78rem; }
}
</style>
@endonce

<script>
function notificationManager(seed) {
    return {
        items: [],
        recent: {},   // dedupe: "type|message" -> timestamp
        maxVisible: 5,

        init() {
            // Semeia notificações de flash da sessão (uma única vez)
            (seed || []).forEach(f => this.addNotification(f));

            // Canal Livewire (eventos dispatch dos componentes PHP).
            // O dedupe por conteúdo impede duplicar com o @notify.window.
            const hook = () => {
                if (window.Livewire) {
                    window.Livewire.on('notify', (payload) => this.addNotification(payload));
                }
            };
            if (window.Livewire) hook();
            else document.addEventListener('livewire:init', hook);
        },

        iconFor(type) {
            return {
                success: 'bi bi-check-circle-fill',
                error:   'bi bi-exclamation-octagon-fill',
                warning: 'bi bi-exclamation-triangle-fill',
                info:    'bi bi-info-circle-fill'
            }[type] || 'bi bi-info-circle-fill';
        },

        titleFor(type) {
            return {
                success: 'Sucesso!',
                error:   'Erro!',
                warning: 'Atenção!',
                info:    'Informação'
            }[type] || 'Informação';
        },

        addNotification(detail) {
            if (!detail) return;

            // Normaliza o payload — Livewire pode entregar:
            //  • objeto nomeado {type,message,duration}   (dispatch named params)
            //  • array  [{type,message,...}]               (dispatch positional)
            //  • objeto com chave numérica {0:{...}}        (dispatch single array)
            let data = detail;
            if (Array.isArray(data)) {
                data = data[0] || {};
            } else if (data && typeof data === 'object'
                       && data.type === undefined && data.message === undefined
                       && data['0'] !== undefined) {
                data = data['0'];
            }
            data = data || {};
            const type = data.type || 'info';
            const message = (data.message ?? '').toString().trim();
            const duration = data.duration || 4000;

            if (!message) return; // ignora notificações vazias

            const key = type + '|' + message;
            const now = Date.now();

            // 1) Dedupe: mesma mensagem já visível → incrementa contador e reinicia timer
            const existing = this.items.find(i => i.key === key && i.show);
            if (existing) {
                existing.count++;
                this.restartTimer(existing);
                return;
            }

            // 2) Anti-burst: mesma mensagem disparada <1500ms atrás (3x do mesmo evento)
            if (this.recent[key] && (now - this.recent[key]) < 1500) {
                return;
            }
            this.recent[key] = now;

            const n = {
                id: now + '-' + Math.random().toString(36).slice(2, 7),
                key, type, message, duration,
                count: 1,
                show: false,
                paused: false,
                timer: null,
                remaining: duration,
                startedAt: now,
            };

            this.items.push(n);

            // Limita a quantidade visível removendo os mais antigos
            while (this.items.length > this.maxVisible) {
                this.remove(this.items[0].id);
            }

            this.$nextTick(() => { n.show = true; });
            this.startTimer(n);
        },

        startTimer(n) {
            if (n.duration <= 0) return;
            n.startedAt = Date.now();
            n.timer = setTimeout(() => this.remove(n.id), n.remaining);
        },

        restartTimer(n) {
            clearTimeout(n.timer);
            n.remaining = n.duration;
            this.startTimer(n);
        },

        pause(n) {
            if (!n.timer) return;
            clearTimeout(n.timer);
            n.paused = true;
            n.remaining -= (Date.now() - n.startedAt);
        },

        resume(n) {
            if (n.duration <= 0) return;
            n.paused = false;
            if (n.remaining <= 0) { this.remove(n.id); return; }
            this.startTimer(n);
        },

        remove(id) {
            const n = this.items.find(i => i.id === id);
            if (!n) return;
            clearTimeout(n.timer);
            n.show = false;
            setTimeout(() => {
                this.items = this.items.filter(i => i.id !== id);
            }, 240);
        },

        clearAll() {
            this.items.forEach(n => { clearTimeout(n.timer); n.show = false; });
            setTimeout(() => { this.items = []; }, 240);
        }
    };
}

// API global para uso em JS puro (mantém compatibilidade)
window.notify = function(type, message, duration = 4000) {
    window.dispatchEvent(new CustomEvent('notify', { detail: { type, message, duration } }));
};
window.notifySuccess = (m, d = 4000) => window.notify('success', m, d);
window.notifyError   = (m, d = 6000) => window.notify('error', m, d);
window.notifyWarning = (m, d = 5000) => window.notify('warning', m, d);
window.notifyInfo    = (m, d = 4000) => window.notify('info', m, d);
</script>
