# 🔥 Guia Completo — Configuração do Firebase no FlowManager

> **Tempo estimado:** 20–30 minutos  
> **Pré-requisito:** Conta Google ativa

---

## Visão Geral

O FlowManager usa o **Firebase Authentication** para login social (Google, GitHub e Microsoft). O fluxo é simples:

1. Usuário clica em "Entrar com Google" (ou GitHub/Microsoft)
2. Firebase abre um popup e autentica com o provedor
3. Firebase retorna um **ID Token** (JWT)
4. O Laravel verifica esse token e cria/autentica o usuário no banco

---

## PASSO 1 — Criar Projeto no Firebase Console

1. Acesse [https://console.firebase.google.com](https://console.firebase.google.com)
2. Clique em **"Adicionar projeto"**
3. Nome do projeto: `FlowManager` (ou qualquer nome)
4. **Desabilite o Google Analytics** (não precisamos)
5. Clique em **"Criar projeto"**

---

## PASSO 2 — Registrar o App Web

1. Na tela do projeto, clique no ícone **`</>`** (Web)
2. Nome do app: `FlowManager Web`
3. **NÃO** ative "Firebase Hosting"
4. Clique em **"Registrar app"**
5. Você verá um bloco de código como:

```js
const firebaseConfig = {
  apiKey: "AIzaSy...",
  authDomain: "flowmanager-xxxxx.firebaseapp.com",
  projectId: "flowmanager-xxxxx",
  storageBucket: "flowmanager-xxxxx.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abcdef"
};
```

> **Copie esses valores** — você vai precisar no PASSO 5.

---

## PASSO 3 — Ativar Provedores de Autenticação

No Firebase Console, vá em **Authentication → Sign-in method**

### 3.1 — Google
1. Clique em **Google** → habilitar
2. E-mail de suporte: seu e-mail Google
3. Clique em **Salvar**

### 3.2 — GitHub
1. Clique em **GitHub** → habilitar
2. Copie o **Callback URL** exibido (ex: `https://flowmanager-xxxxx.firebaseapp.com/__/auth/handler`)
3. Abra [https://github.com/settings/developers](https://github.com/settings/developers)
4. Clique em **"New OAuth App"**:
   - **Application name:** FlowManager
   - **Homepage URL:** `http://localhost` (ou seu domínio)
   - **Authorization callback URL:** Cole a URL do Firebase
5. Gere um **Client Secret**
6. Volte ao Firebase e cole o **Client ID** e **Client Secret** do GitHub
7. Salve

### 3.3 — Microsoft
1. Clique em **Microsoft** → habilitar
2. Copie o **Callback URL** exibido
3. Acesse [https://portal.azure.com → Azure Active Directory → App Registrations](https://portal.azure.com/#view/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/~/RegisteredApps)
4. Clique em **"New registration"**:
   - **Name:** FlowManager
   - **Supported account types:** Accounts in any organizational directory and personal Microsoft accounts
   - **Redirect URI:** Web → Cole a URL do Firebase
5. Após criar, anote o **Application (client) ID**
6. Em **Certificates & secrets → New client secret** → gere e copie o valor
7. Volte ao Firebase e cole o **Application ID** e **Client Secret**
8. Salve

---

## PASSO 4 — Adicionar Domínios Autorizados

No Firebase Console: **Authentication → Settings → Authorized domains**

Adicione:
- `localhost`
- `127.0.0.1`
- Seu domínio de produção (ex: `flowmanager.com.br`)
- Seu domínio ngrok se estiver usando (ex: `xxxx.ngrok.io`)

---

## PASSO 5 — Configurar o `.env`

Abra o arquivo `.env` na raiz do projeto e preencha:

```env
# ═══════════════════════════════════════
# FIREBASE
# ═══════════════════════════════════════
FIREBASE_API_KEY=AIzaSy...           # apiKey do passo 2
FIREBASE_AUTH_DOMAIN=flowmanager-xxxxx.firebaseapp.com
FIREBASE_PROJECT_ID=flowmanager-xxxxx

# ═══════════════════════════════════════
# GOOGLE OAUTH (opcional - para Socialite)
# ═══════════════════════════════════════
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

> **Atenção:** O `FIREBASE_API_KEY` é público e vai para o frontend — isso é normal e seguro. NÃO confunda com chaves de serviço do servidor.

---

## PASSO 6 — Verificar o `config/services.php`

O arquivo já está configurado. Confirme que existe:

```php
'firebase' => [
    'api_key'     => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'project_id'  => env('FIREBASE_PROJECT_ID'),
],
```

---

## PASSO 7 — Testar

1. Inicie o servidor:
   ```bash
   php artisan serve
   ```
2. Acesse `http://localhost:8000/login`
3. Clique em **"Entrar com Google"**
4. Faça login com sua conta Google
5. Você deve ser redirecionado para o **dashboard**

### Se der erro:

| Erro | Causa | Solução |
|------|-------|---------|
| `auth/unauthorized-domain` | Domínio não autorizado | Adicionar em Firebase → Authorized domains |
| `auth/popup-blocked` | Navegador bloqueou popup | Habilitar popups para localhost |
| `Unauthenticated` (Laravel) | Token inválido ou projeto_id errado | Verificar FIREBASE_PROJECT_ID |
| `auth/operation-not-allowed` | Provedor não habilitado | Habilitar no Firebase Console |
| `auth/configuration-not-found` | API key inválida | Verificar FIREBASE_API_KEY no .env |

---

## PASSO 8 — Produção (HTTPS obrigatório)

Para deploy em produção:

1. **HTTPS é obrigatório** para Firebase Auth em produção
2. No Firebase Console → Authentication → Authorized domains → adicione seu domínio
3. Atualize o `.env` de produção com os mesmos valores do Firebase
4. Execute:
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

---

## Estrutura de Arquivos do Projeto

```
app/Http/Controllers/Auth/
└── GoogleFirebaseController.php   ← Verifica o token Firebase no backend

resources/views/partials/
└── firebase-auth.blade.php        ← JS do Firebase (Google/GitHub/Microsoft)

resources/views/livewire/auth/
├── login.blade.php                ← Chama FlowAuth.google/github/microsoft()
└── register.blade.php             ← Mesma coisa

config/services.php                ← firebase.project_id, api_key, auth_domain
routes/auth.php                    ← POST /auth/google/firebase
.env                               ← FIREBASE_* vars
```

---

## Fluxo Técnico Detalhado

```
[Frontend]                          [Backend]
    │                                    │
    ├─ FlowAuth.google(btn)              │
    ├─ Firebase popup abre               │
    ├─ Usuário faz login Google          │
    ├─ Firebase retorna ID Token         │
    │                                    │
    ├─ POST /auth/google/firebase ──────►│
    │   { id_token: "eyJ..." }           │
    │                                    ├─ GoogleFirebaseController
    │                                    ├─ Verifica token na API Firebase
    │                                    ├─ Extrai email, nome, uid
    │                                    ├─ User::firstOrCreate(...)
    │                                    ├─ Auth::login($user)
    │                                    │
    │◄─── { redirect: "/dashboard" } ───┤
    │                                    │
    ├─ window.location = redirect        │
```

---

## Segurança

- ✅ O token Firebase é verificado no **servidor** (não confiamos no frontend)
- ✅ Rate limiting: `throttle:10,1` (10 tentativas por minuto)
- ✅ CSRF token obrigatório em todas as requisições
- ✅ `project_id` validado contra a variável de ambiente
- ✅ Usuário autenticado via `Auth::login()` (sessão Laravel padrão)
- ✅ `email_verified_at` preenchido se o e-mail social for verificado

---

*Documentação gerada em 03/04/2026 para FlowManager v1*
