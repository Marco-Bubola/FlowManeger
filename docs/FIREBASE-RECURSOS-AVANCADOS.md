# 🚀 O que Mais Implementar com Firebase no FlowManager

> Guia estratégico de features do Firebase que agregam valor real ao app

---

## Resumo — O que já está implementado

| Feature | Status |
|---------|--------|
| Auth: Google / GitHub / Microsoft | ✅ Pronto |
| Verificação de token no backend | ✅ Pronto |
| Criação automática de usuário | ✅ Pronto |

---

## 1. 🔔 Firebase Cloud Messaging (FCM) — Notificações Push

### O que é
Notificações push em tempo real para o navegador (e futuramente mobile).

### Valor para o FlowManager
- Notificar vendedores quando um pedido muda de status
- Alertas de estoque baixo
- Novas mensagens de clientes no CRM
- Sincronização ML concluída

### Como implementar (resumo)
1. Ative **Cloud Messaging** no Firebase Console
2. Gere uma **VAPID Key** em Project Settings → Cloud Messaging
3. Registre o Service Worker no frontend:

```js
// public/firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({ apiKey: "...", projectId: "...", messagingSenderId: "..." });
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    self.registration.showNotification(payload.notification.title, {
        body: payload.notification.body,
        icon: '/apple-touch-icon.png'
    });
});
```

4. No backend, use a biblioteca `kreait/firebase-php`:
```bash
composer require kreait/firebase-php
```

5. Envie notificações via Laravel Jobs:
```php
// app/Jobs/SendPushNotification.php
use Kreait\Firebase\Factory;

$factory = (new Factory)->withServiceAccount(storage_path('app/firebase-credentials.json'));
$messaging = $factory->createMessaging();

$messaging->send(CloudMessage::withTarget('token', $deviceToken)
    ->withNotification(Notification::create('Novo pedido!', 'Pedido #1234 recebido'))
);
```

### Esforço estimado: 1–2 dias

---

## 2. ⚡ Firebase Realtime Database ou Firestore — Dados em Tempo Real

### O que é
Banco de dados NoSQL com sincronização em tempo real via WebSocket.

### Valor para o FlowManager
- **Chat interno** entre operadores — sem polling
- **Dashboard ao vivo** — métricas de vendas atualizando em tempo real
- **Status de pedidos** — operador vê mudanças instantâneas
- **Notificações in-app** sem precisar de websocket próprio

### Comparativo: Realtime DB vs Firestore

| | Realtime Database | Firestore |
|-|-------------------|-----------|
| Estrutura | JSON plana | Coleções e documentos |
| Consultas | Simples | Poderosas (índices) |
| Preço | Mais barato | Mais caro em leitura |
| **Recomendado para** | Chat, presença online | Dados estruturados |

### Exemplo — Dashboard em tempo real

```js
// resources/js/realtime-dashboard.js
import { initializeApp } from 'firebase/app';
import { getFirestore, collection, onSnapshot, query, orderBy } from 'firebase/firestore';

const db = getFirestore(app);

// Escuta pedidos em tempo real
onSnapshot(query(collection(db, 'orders'), orderBy('created_at', 'desc')), (snapshot) => {
    snapshot.docChanges().forEach((change) => {
        if (change.type === 'added') {
            // Adiciona pedido na UI sem reload
            addOrderToTable(change.doc.data());
        }
    });
});
```

### Esforço estimado: 2–4 dias

---

## 3. 📊 Firebase Analytics — Análise de Comportamento

### O que é
Analytics gratuito integrado com o Firebase para entender como usuários usam o app.

### Valor para o FlowManager
- Saber quais funcionalidades são mais usadas
- Identificar onde usuários abandonam o fluxo
- Medir conversão de registro → primeiro pedido
- Funis de uso: quantos chegam ao dashboard vs. usam relatórios

### Como implementar

```js
import { getAnalytics, logEvent } from 'firebase/analytics';

const analytics = getAnalytics(app);

// Rastrea ações importantes
logEvent(analytics, 'produto_criado', { categoria: 'roupas' });
logEvent(analytics, 'relatorio_gerado', { tipo: 'vendas_mensais' });
logEvent(analytics, 'sync_ml_iniciado');
```

> ⚠️ **LGPD:** Adicione o aviso de cookies/analytics antes de ativar.

### Esforço estimado: Algumas horas

---

## 4. 🗄️ Firebase Storage — Armazenamento de Arquivos

### O que é
Armazenamento de arquivos na nuvem (imagens, documentos, CSV) com URLs públicas/privadas.

### Valor para o FlowManager
- **Fotos de produtos** sem ocupar o servidor local
- **Importação de catálogos** via CSV direto do navegador
- **Documentos fiscais** (NF-e, boletos) com download seguro
- **Avatares de usuários** (já temos o campo `avatar` na tabela)

### Como implementar — Upload de imagem de produto

```js
import { getStorage, ref, uploadBytes, getDownloadURL } from 'firebase/storage';

const storage = getStorage(app);

async function uploadProductImage(file, productId) {
    const storageRef = ref(storage, `products/${productId}/${file.name}`);
    const snapshot = await uploadBytes(storageRef, file);
    const url = await getDownloadURL(snapshot.ref);
    
    // Salva URL no Laravel
    await fetch(`/api/products/${productId}/image`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ image_url: url })
    });
    
    return url;
}
```

### Regras de segurança recomendadas

```
// Firebase Console → Storage → Rules
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /products/{productId}/{filename} {
      // Somente usuários autenticados podem ler/escrever
      allow read: if request.auth != null;
      allow write: if request.auth != null && request.resource.size < 5 * 1024 * 1024; // 5MB max
    }
    match /avatars/{userId}/{filename} {
      allow read: if true; // Avatares são públicos
      allow write: if request.auth.uid == userId; // Só o próprio usuário
    }
  }
}
```

### Esforço estimado: 1–2 dias

---

## 5. 🔐 Firebase App Check — Proteção Anti-Abuse

### O que é
Verifica que as requisições vêm do seu app legítimo, bloqueando bots e abuso de API.

### Valor para o FlowManager
- Protege a rota `/auth/google/firebase` contra ataques automatizados
- Impede que alguém use seu Firebase fora do seu domínio
- Reduz custos com requisições indevidas

### Como ativar
1. Firebase Console → App Check
2. Selecione **reCAPTCHA v3** para web
3. Registre seu site no [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
4. Cole as chaves no Firebase Console

```js
import { initializeAppCheck, ReCaptchaV3Provider } from 'firebase/app-check';

initializeAppCheck(app, {
    provider: new ReCaptchaV3Provider('SUA_RECAPTCHA_SITE_KEY'),
    isTokenAutoRefreshEnabled: true
});
```

### Esforço estimado: Algumas horas

---

## 6. 📱 Firebase App Distribution — Deploy para Testes

### O que é
Distribui versões de teste internas para a equipe (mais útil se criar app mobile no futuro).

### Valor futuro
- Distribuir versão beta do app mobile FlowManager para vendedores testarem
- Coletar feedback antes do release oficial

### Esforço estimado: 1 dia (quando tiver app mobile)

---

## 7. 🤖 Firebase Remote Config — Feature Flags

### O que é
Controla configurações e features do app remotamente sem fazer deploy.

### Valor para o FlowManager
- Habilitar/desabilitar features para grupos de usuários
- Testar novas UIs (A/B testing)
- Mudar textos e configurações sem deploy
- Controlar limites por plano (free vs. premium) via config remota

### Exemplo de uso

```js
import { getRemoteConfig, getValue, fetchAndActivate } from 'firebase/remote-config';

const remoteConfig = getRemoteConfig(app);
remoteConfig.defaultConfig = {
    'max_products_free_plan': 50,
    'show_ml_integration': true,
    'maintenance_mode': false,
};

await fetchAndActivate(remoteConfig);

const maxProducts = getValue(remoteConfig, 'max_products_free_plan').asNumber();
```

### Esforço estimado: Algumas horas

---

## 🗺️ Roadmap Recomendado por Prioridade

| Prioridade | Feature | Valor | Esforço |
|-----------|---------|-------|---------|
| 🔴 Alta | **FCM — Notificações Push** | Alto | 1–2 dias |
| 🔴 Alta | **Storage — Upload de imagens** | Alto | 1–2 dias |
| 🟡 Média | **Firestore — Dashboard realtime** | Médio-Alto | 2–4 dias |
| 🟡 Média | **Analytics** | Médio | Horas |
| 🟢 Baixa | **App Check** | Segurança | Horas |
| 🟢 Baixa | **Remote Config** | Conveniência | Horas |

---

## 💰 Custos Firebase (Free Tier — Spark Plan)

| Serviço | Limite Gratuito |
|---------|----------------|
| Authentication | **Ilimitado** |
| Firestore | 50k leituras/dia, 20k gravações/dia |
| Realtime Database | 1 GB armazenado, 10 GB/mês transferência |
| Storage | 5 GB armazenado, 1 GB/dia download |
| FCM (Push) | **Ilimitado** |
| Analytics | **Ilimitado** |
| Remote Config | **Ilimitado** |

> Para um app com < 1.000 usuários ativos, o free tier é mais que suficiente.

---

## Dependências PHP para Recursos Avançados

```bash
# Para FCM, Firestore, Storage via PHP (backend)
composer require kreait/firebase-php

# Configurar com Service Account (baixe do Firebase Console → Project Settings → Service Accounts)
```

```php
// config/firebase.php ou AppServiceProvider
use Kreait\Firebase\Factory;

$factory = (new Factory)->withServiceAccount(storage_path('app/firebase-service-account.json'));

// Serviços disponíveis:
$auth      = $factory->createAuth();       // Administrar usuários
$messaging = $factory->createMessaging();  // Enviar FCM
$firestore = $factory->createFirestore();  // Ler/escrever Firestore
$storage   = $factory->createStorage();    // Gerenciar arquivos
```

> ⚠️ O arquivo `firebase-service-account.json` contém chaves privadas — **nunca commitar no git**. Adicione ao `.gitignore`.

---

*Documentação gerada em 03/04/2026 para FlowManager v1*
