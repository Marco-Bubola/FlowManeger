<script type="module">
import { initializeApp, getApps } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import {
    getAuth,
    signInWithPopup,
    GoogleAuthProvider,
    GithubAuthProvider,
    OAuthProvider,
} from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

const fbConfig = {
    apiKey:            "{{ config('services.firebase.api_key') }}",
    authDomain:        "{{ config('services.firebase.auth_domain') }}",
    projectId:         "{{ config('services.firebase.project_id') }}",
    storageBucket:     "{{ config('services.firebase.storage_bucket') }}",
    messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
    appId:             "{{ config('services.firebase.app_id') }}",
};

let _auth = null;

function getFirebaseAuth() {
    if (_auth) return _auth;
    if (!fbConfig.apiKey) {
        console.warn('[FlowManager] Firebase não configurado. Defina FIREBASE_API_KEY no .env');
        return null;
    }
    try {
        const app = getApps().length ? getApps()[0] : initializeApp(fbConfig);
        _auth = getAuth(app);
        return _auth;
    } catch (e) {
        console.warn('[FlowManager] Firebase init error:', e.message);
        return null;
    }
}

async function doFirebaseSignIn(provider) {
    const auth = getFirebaseAuth();
    if (!auth) {
        showAuthToast('Firebase não configurado. Contate o administrador.', 'error');
        return;
    }
    try {
        const result = await signInWithPopup(auth, provider);
        const idToken = await result.user.getIdToken();

        const res = await fetch('/auth/google/firebase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id_token: idToken }),
        });

        const data = await res.json();
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            throw new Error(data.error ?? 'Resposta inesperada do servidor.');
        }
    } catch (err) {
        if (err.code === 'auth/popup-closed-by-user') return;
        console.error('[FlowManager] Social login error:', err);
        showAuthToast(err.message ?? 'Erro ao fazer login social.', 'error');
        throw err;
    }
}

// ── Public API ──
window.FlowAuth = {
    google: async function(btn) {
        setButtonLoading(btn, true);
        try {
            const provider = new GoogleAuthProvider();
            provider.addScope('email');
            provider.addScope('profile');
            await doFirebaseSignIn(provider);
        } finally {
            setButtonLoading(btn, false);
        }
    },

    github: async function(btn) {
        setButtonLoading(btn, true);
        try {
            const provider = new GithubAuthProvider();
            provider.addScope('user:email');
            await doFirebaseSignIn(provider);
        } finally {
            setButtonLoading(btn, false);
        }
    },

    microsoft: async function(btn) {
        setButtonLoading(btn, true);
        try {
            const provider = new OAuthProvider('microsoft.com');
            provider.addScope('User.Read');
            await doFirebaseSignIn(provider);
        } finally {
            setButtonLoading(btn, false);
        }
    },
};

// ── Helpers ──
function setButtonLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = loading;
    const icon  = btn.querySelector('[data-icon]');
    const spin  = btn.querySelector('[data-spin]');
    const label = btn.querySelector('[data-label]');
    if (icon)  icon.style.display  = loading ? 'none'   : '';
    if (spin)  spin.style.display  = loading ? 'inline' : 'none';
    if (label) label.textContent   = loading ? '...'    : label.dataset.text;
}

function showAuthToast(msg, type = 'error') {
    const el = document.getElementById('auth-toast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'auth-toast auth-toast-' + type + ' auth-toast-show';
    setTimeout(() => el.classList.remove('auth-toast-show'), 4000);
}

// ── Password toggle ──
window.toggleAuthPassword = function(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    const btn = input.parentElement?.querySelector('.pw-eye');
    if (btn) btn.setAttribute('aria-pressed', input.type === 'text');
};

// ── Password strength ──
window.checkPasswordStrength = function(val) {
    const bar = document.getElementById('pw-strength-bar');
    const txt = document.getElementById('pw-strength-text');
    if (!bar || !txt) return;
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { w: '20%',  color: '#ef4444', label: 'Muito fraca' },
        { w: '40%',  color: '#f97316', label: 'Fraca'       },
        { w: '60%',  color: '#eab308', label: 'Razoável'    },
        { w: '80%',  color: '#22c55e', label: 'Boa'         },
        { w: '100%', color: '#10b981', label: 'Forte 🔒'    },
    ];
    const lvl = levels[Math.max(0, score - 1)] ?? levels[0];
    bar.style.width = val ? lvl.w : '0%';
    bar.style.backgroundColor = lvl.color;
    txt.textContent = val ? lvl.label : '';
    txt.style.color = lvl.color;
};
</script>

{{-- Toast notification --}}
<div id="auth-toast" style="
    position:fixed; bottom:1.5rem; left:50%; transform:translateX(-50%) translateY(100px);
    background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3);
    color:#fca5a5; padding:0.75rem 1.25rem; border-radius:12px; font-size:0.83rem;
    font-weight:500; z-index:9999; max-width:340px; text-align:center;
    backdrop-filter:blur(12px); transition:transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
    opacity:0; pointer-events:none;
"></div>

<style>
.auth-toast-show { transform: translateX(-50%) translateY(0) !important; opacity: 1 !important; pointer-events: auto !important; }
.auth-toast-error { background:rgba(239,68,68,0.12)!important; border-color:rgba(239,68,68,0.3)!important; color:#fca5a5!important; }
.auth-toast-success { background:rgba(52,211,153,0.10)!important; border-color:rgba(52,211,153,0.25)!important; color:#6ee7b7!important; }
</style>
