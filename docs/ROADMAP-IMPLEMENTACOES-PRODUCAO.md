# 🚀 FlowManager — Roadmap de Implementações para Produção

> Documento gerado em: 03/04/2026  
> Baseado na análise completa do código-fonte do projeto.

---

## 📋 ÍNDICE

1. [Status das Integrações Atuais](#1-status-das-integrações-atuais)
2. [Integrações que Precisam Ser Configuradas no Host](#2-integrações-que-precisam-ser-configuradas-no-host)
3. [Ideias de IA — Onde Implementar](#3-ideias-de-ia--onde-implementar)
4. [Novas Integrações Recomendadas](#4-novas-integrações-recomendadas)
5. [Checklist de Variáveis de Ambiente (.env)](#5-checklist-de-variáveis-de-ambiente-env)
6. [Prioridade de Implementação](#6-prioridade-de-implementação)

---

## 1. Status das Integrações Atuais

### ✅ Funcionando (precisa de chaves no host)

| Integração | Arquivo(s) | Status |
|------------|-----------|--------|
| **Gemini AI** (PDF + Transações) | `GeminiPdfExtractorService.php`, `GeminiTransactionProcessorService.php` | ✅ Código pronto, precisa de `GEMINI_API_KEY` |
| **Mercado Livre** | `app/Services/MercadoLivre/` (12 services) | ✅ Código pronto, precisa de credenciais ML |
| **Shopee** | `app/Services/Shopee/` (6 services) | ✅ Código pronto, precisa de credenciais Shopee |
| **Firebase** | `config/services.php` (firebase block) | ✅ Config pronta, precisa das 7 chaves Firebase |
| **Google OAuth** (Login com Google) | `Laravel Socialite` | ✅ Pronto, precisa de `GOOGLE_CLIENT_ID` e `GOOGLE_CLIENT_SECRET` |
| **DomPDF** (exportação PDF) | `barryvdh/laravel-dompdf` | ✅ Instalado |
| **Maatwebsite Excel** (exportação XLSX) | `maatwebsite/excel` | ✅ Instalado |

### ⚠️ STUBS — Código existe mas não está completo

| Integração | Arquivo | O que falta |
|------------|---------|-------------|
| **Stripe** | `StripeGateway.php` | Instalar `stripe/stripe-php` via Composer + configurar preços no Stripe Dashboard |
| **Mercado Pago** (assinaturas) | `MercadoPagoGateway.php` | Instalar `mercadopago/dx-php` via Composer + configure preferências |
| **PagSeguro** | `PagSeguroGateway.php` | Implementar chamadas reais à API PagBank v4 + `PAGSEGURO_TOKEN` |
| **E-mail (SMTP)** | `config/mail.php` | Está como `log` por padrão — trocar para SMTP real no host |

---

## 2. Integrações que Precisam Ser Configuradas no Host

### 🔴 CRÍTICO — Sem isso o app não funciona em produção

#### 2.1 Banco de Dados
```env
DB_CONNECTION=mysql
DB_HOST=seu-host-mysql
DB_PORT=3306
DB_DATABASE=flowmanager
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

#### 2.2 App URL e Chaves
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com
APP_KEY=base64:... (gerar com php artisan key:generate)
```

#### 2.3 E-mail (SMTP) — Obrigatório para senhas, notificações
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com          # ou smtp.sendgrid.net, smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=resend                # depende do provedor
MAIL_PASSWORD=re_sua_chave_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seudominio.com
MAIL_FROM_NAME="FlowManager"
```
> **Recomendado:** [Resend.com](https://resend.com) (gratuito até 3.000 e-mails/mês) ou SendGrid

---

### 🟡 IMPORTANTE — Para funcionalidades principais

#### 2.4 Gemini AI (já usado no app)
```env
GEMINI_API_KEY=AIza...
GEMINI_MODEL=gemini-2.5-flash
```
> Obter em: https://aistudio.google.com/app/apikey

#### 2.5 Google OAuth (Login com Google)
```env
GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxx
GOOGLE_REDIRECT_URI=https://seudominio.com/auth/google/callback
```
> Configurar em: https://console.cloud.google.com → Credenciais → OAuth 2.0

#### 2.6 Mercado Livre
```env
ML_CLIENT_ID=1234567890            # App ID do ML Developers
ML_CLIENT_SECRET=abc123xyz
ML_REDIRECT_URI=https://seudominio.com/mercadolivre/auth/callback
ML_WEBHOOK_SECRET=sua_chave_secreta
ML_ENVIRONMENT=production          # trocar de sandbox para production
```
> Dashboard: https://developers.mercadolivre.com.br

#### 2.7 Shopee
```env
SHOPEE_PARTNER_ID=123456
SHOPEE_PARTNER_KEY=abc123...
SHOPEE_REDIRECT_URI=https://seudominio.com/shopee/auth/callback
SHOPEE_ENVIRONMENT=production
```
> Dashboard: https://open.shopee.com

#### 2.8 Firebase (notificações push)
```env
FIREBASE_API_KEY=AIzaSy...
FIREBASE_AUTH_DOMAIN=seu-projeto.firebaseapp.com
FIREBASE_PROJECT_ID=seu-projeto
FIREBASE_STORAGE_BUCKET=seu-projeto.appspot.com
FIREBASE_MESSAGING_SENDER_ID=123456789
FIREBASE_APP_ID=1:123456789:web:abc123
FIREBASE_MEASUREMENT_ID=G-XXXXXXXX
```
> Dashboard: https://console.firebase.google.com

---

### 🟢 OPCIONAL — Gateways de pagamento (assinaturas/planos)

#### 2.9 Stripe (cartão internacional)
```bash
composer require stripe/stripe-php
```
```env
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_ID_MONTHLY=price_...
STRIPE_PRICE_ID_ANNUAL=price_...
```
> Dashboard: https://dashboard.stripe.com

#### 2.10 Mercado Pago (assinaturas — melhor para BR)
```bash
composer require mercadopago/dx-php
```
```env
MERCADOPAGO_ACCESS_TOKEN=APP_USR-...
MERCADOPAGO_PUBLIC_KEY=APP_USR-...
```
> Dashboard: https://www.mercadopago.com.br/developers

#### 2.11 PagSeguro / PagBank
```env
PAGSEGURO_TOKEN=seu_token_pagseguro
PAGSEGURO_EMAIL=seu@email.com
```

---

### ⚙️ Configurações de Infraestrutura do Host

#### 2.12 Queue (filas — Jobs de webhook ML/Shopee)
```env
QUEUE_CONNECTION=database   # ou redis se disponível
```
```bash
# No servidor, configurar supervisor para processar fila
php artisan queue:work --daemon
```

#### 2.13 Cache e Session
```env
CACHE_STORE=file            # ou redis
SESSION_DRIVER=file         # ou database
```

#### 2.14 Storage (imagens de produtos)
```bash
php artisan storage:link    # criar link simbólico storage/public
```

---

## 3. Ideias de IA — Onde Implementar

> O projeto já usa Gemini AI para extração de PDFs e categorização de transações. Abaixo, novas áreas onde IA agrega muito valor.

---

### 🤖 3.1 Assistente Financeiro Inteligente (Dashboard)
**Onde:** `app/Services/Dashboard/` + `app/Livewire/Dashboard/DashboardIndex.php`  
**Ideia:** Análise automática das finanças do mês com insights em linguagem natural.

**O que a IA faria:**
- "Seus gastos com Alimentação aumentaram 23% esse mês vs mês anterior"
- "Você tem R$ 1.200 de receita recorrente prevista para semana que vem"
- "Risco de déficit em 15 dias — baseado no histórico"
- Sugestão de quais despesas cortar baseado nos padrões

**Implementação:** Gemini recebe resumo financeiro mensal → retorna parágrafo de análise

---

### 🤖 3.2 Geração de Descrição de Produtos (ML/Shopee)
**Onde:** `app/Livewire/Products/CreateProduct.php`, `EditPublication.php`  
**Ideia:** Botão "Gerar descrição com IA" na criação/edição de produto.

**O que a IA faria:**
- Recebe nome + categoria + preço + atributos do produto
- Gera título otimizado para SEO no Mercado Livre / Shopee
- Gera descrição persuasiva e detalhada
- Sugere palavras-chave para o anúncio

**Implementação:** Novo método em `GeminiService` → chamado no componente Livewire

---

### 🤖 3.3 Chatbot de Atendimento ao Cliente
**Onde:** Nova seção no módulo de Clientes (`app/Livewire/Clients/`)  
**Ideia:** Chat embutido para tirar dúvidas sobre pedidos, faturas e pagamentos.

**O que a IA faria:**
- Responde perguntas sobre status de pedido
- Informa parcelas em aberto
- Explica a fatura atual
- Escalona para humano quando não souber responder

**Implementação:** Gemini API com histórico de conversa + contexto do cliente injetado no prompt

---

### 🤖 3.4 Previsão de Vendas (Forecasting)
**Onde:** `app/Services/Dashboard/DashboardSalesMetrics.php` (novo)  
**Ideia:** Previsão de faturamento para os próximos 30/60/90 dias.

**O que a IA faria:**
- Analisa histórico de vendas dos últimos 6 meses
- Identifica sazonalidades e tendências
- Gera forecast com margem de confiança
- Alerta sobre produtos com estoque crítico vs demanda prevista

**Implementação:** Gemini recebe série histórica de vendas → retorna JSON com previsão

---

### 🤖 3.5 Resposta Inteligente a Perguntas (Mercado Livre)
**Onde:** `app/Livewire/MercadoLivre/Questions.php` + `app/Services/MercadoLivre/QuestionService.php`  
**Ideia:** IA sugere resposta automática para perguntas de compradores.

**O que a IA faria:**
- Lê a pergunta do comprador
- Consulta dados do produto (descrição, specs, estoque)
- Gera resposta profissional e completa
- Você revisa e aprova com 1 clique antes de enviar

**Implementação:** Botão "Sugerir resposta" → Gemini com contexto do produto → retorna sugestão editável

---

### 🤖 3.6 Análise de Risco em Consórcios
**Onde:** `app/Livewire/Consortiums/ShowConsortium.php`  
**Ideia:** IA identifica participantes com risco de inadimplência.

**O que a IA faria:**
- Analisa histórico de pagamentos de cada participante
- Classifica risco: Baixo / Médio / Alto
- Sugere ação: contatação preventiva, redistribuição de cotas
- Gera relatório de saúde financeira do consórcio

---

### 🤖 3.7 Coach de Metas e Hábitos
**Onde:** `app/Livewire/Goals/`, `app/Livewire/DailyHabits/`  
**Ideia:** IA analisa progresso e oferece coaching personalizado.

**O que a IA faria:**
- Analisa taxa de conclusão de hábitos dos últimos 30 dias
- Identifica padrões (falha às segundas, sucesso nos fins de semana)
- Sugere ajuste de frequência ou dificuldade das metas
- Mensagem motivacional personalizada baseada no contexto real do usuário

---

### 🤖 3.8 Detecção de Fraude / Anomalias Financeiras
**Onde:** `app/Livewire/Cashbook/`, `app/Livewire/Invoices/`  
**Ideia:** IA detecta lançamentos suspeitos ou fora do padrão.

**O que a IA faria:**
- Analisa lançamentos novos vs padrão histórico
- Alerta sobre valores muito acima da média na categoria
- Detecta duplicatas possíveis
- Flag em transações com descrição incomum

---

## 4. Novas Integrações Recomendadas

### 📱 4.1 WhatsApp (Evolution API ou Twilio)
**Por que:** Notificações de cobrança, confirmações de pedido e alertas por WhatsApp  
**Onde usar:**
- Notificações de vencimento de parcelas de consórcio
- Confirmação de venda / pedido Mercado Livre
- Alertas de metas batidas
- Lembretes de hábitos diários

**Opções:**
- **Evolution API** (self-hosted, gratuito): https://evolution-api.com
- **Twilio WhatsApp** (pago, mais estável): https://twilio.com
- **Z-API** (brasileiro, simples): https://z-api.io

---

### 📊 4.2 Google Analytics / Hotjar
**Por que:** Entender comportamento dos usuários no app  
**Onde:** Layout principal + eventos de conversão  
```env
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

---

### 📲 4.3 Push Notifications Web (já tem Firebase)
**Por que:** Firebase está configurado mas não totalmente usado para push  
**O que falta:** Service Worker no frontend + lógica de inscrição de dispositivos  
**Onde usar:** Alertas de pedidos ML/Shopee, vencimentos, sorteios de consórcio

---

### 💬 4.4 Slack / Telegram (notificações operacionais)
**Por que:** Alertas internos sobre erros, novos pedidos, webhooks falhos  
**Slack:** Já tem `slack.notifications` na config!  
```env
SLACK_BOT_USER_OAUTH_TOKEN=xoxb-...
SLACK_BOT_USER_DEFAULT_CHANNEL=#alertas
```

---

### 📦 4.5 Correios / Jadlog API (tracking de pedidos)
**Por que:** Rastreamento automático de entregas para pedidos ML/Shopee  
**Onde:** `app/Services/MercadoLivre/OrderService.php`, `ShopeeOrderService.php`  
**API:** https://developers.correios.com.br

---

### 🗺️ 4.6 ViaCEP + Google Maps (endereços)
**Por que:** Auto-completar endereço de clientes pelo CEP  
**Onde:** `CreateClient.php`, cadastro de participantes de consórcio  
**API:** https://viacep.com.br (gratuita, sem chave)

---

### 💰 4.7 Open Finance / Plaid Brasil (Belvo/Pluggy)
**Por que:** Importar extratos bancários automaticamente (em vez de upload CSV/PDF)  
**Onde:** Substituir/complementar `UploadCashbook.php`  
**Opções:**
- **Pluggy** (brasileiro): https://pluggy.ai
- **Belvo** (LATAM): https://belvo.com
- **Open Finance BACEN:** direto nas instituições

---

### 📈 4.8 Nota Fiscal Eletrônica (NF-e)
**Por que:** Emissão automática de NF ao concluir venda  
**Onde:** `app/Livewire/Sales/ShowSale.php`  
**Opções:**
- **NFE.io**: https://nfe.io
- **Omie API**: https://developer.omie.com.br
- **Focus NF-e**: https://focusnfe.com.br

---

## 5. Checklist de Variáveis de Ambiente (.env)

### Copie este bloco e preencha no servidor:

```env
# ============================================================
# APLICAÇÃO
# ============================================================
APP_NAME="FlowManager"
APP_ENV=production
APP_KEY=base64:GERAR_COM_PHP_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://seudominio.com

# ============================================================
# BANCO DE DADOS
# ============================================================
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=flowmanager_prod
DB_USERNAME=USUARIO
DB_PASSWORD=SENHA

# ============================================================
# E-MAIL (obrigatório)
# ============================================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_SUA_CHAVE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seudominio.com
MAIL_FROM_NAME="FlowManager"

# ============================================================
# IA — GOOGLE GEMINI (já em uso no app)
# ============================================================
GEMINI_API_KEY=AIzaSy...
GEMINI_MODEL=gemini-2.5-flash

# ============================================================
# GOOGLE OAUTH (login social)
# ============================================================
GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxx
GOOGLE_REDIRECT_URI=https://seudominio.com/auth/google/callback

# ============================================================
# FIREBASE (push notifications)
# ============================================================
FIREBASE_API_KEY=AIzaSy...
FIREBASE_AUTH_DOMAIN=projeto.firebaseapp.com
FIREBASE_PROJECT_ID=projeto-id
FIREBASE_STORAGE_BUCKET=projeto.appspot.com
FIREBASE_MESSAGING_SENDER_ID=123456789
FIREBASE_APP_ID=1:123456789:web:abc123
FIREBASE_MEASUREMENT_ID=G-XXXXXXXX

# ============================================================
# MERCADO LIVRE
# ============================================================
ML_CLIENT_ID=1234567890
ML_CLIENT_SECRET=abc123xyz
ML_REDIRECT_URI=https://seudominio.com/mercadolivre/auth/callback
ML_WEBHOOK_SECRET=webhook_secret
ML_ENVIRONMENT=production

# ============================================================
# SHOPEE
# ============================================================
SHOPEE_PARTNER_ID=123456
SHOPEE_PARTNER_KEY=abc123...
SHOPEE_REDIRECT_URI=https://seudominio.com/shopee/auth/callback
SHOPEE_ENVIRONMENT=production

# ============================================================
# STRIPE (opcional — assinaturas cartão internacional)
# ============================================================
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# ============================================================
# MERCADO PAGO (opcional — assinaturas BR)
# ============================================================
MERCADOPAGO_ACCESS_TOKEN=APP_USR-...
MERCADOPAGO_PUBLIC_KEY=APP_USR-...

# ============================================================
# PAGSEGURO (opcional)
# ============================================================
PAGSEGURO_TOKEN=seu_token
PAGSEGURO_EMAIL=seu@email.com

# ============================================================
# QUEUE
# ============================================================
QUEUE_CONNECTION=database

# ============================================================
# CACHE / SESSION
# ============================================================
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ============================================================
# SLACK (notificações internas — já configurado no código)
# ============================================================
SLACK_BOT_USER_OAUTH_TOKEN=xoxb-...
SLACK_BOT_USER_DEFAULT_CHANNEL=#alertas
```

---

## 6. Prioridade de Implementação

### 🔴 Fase 1 — Deploy Básico Funcional (fazer AGORA)
- [ ] Gerar `APP_KEY` com `php artisan key:generate`
- [ ] Configurar banco de dados MySQL no host
- [ ] Rodar migrações: `php artisan migrate --force`
- [ ] Criar link de storage: `php artisan storage:link`
- [ ] Configurar SMTP para e-mails (ex: Resend)
- [ ] Configurar `APP_URL` correto
- [ ] Definir `APP_DEBUG=false`

### 🟡 Fase 2 — Funcionalidades Principais (1ª semana)
- [ ] Configurar `GEMINI_API_KEY` (já usado no app)
- [ ] Configurar `GOOGLE_CLIENT_ID/SECRET` (login social)
- [ ] Configurar credenciais Firebase
- [ ] Configurar Mercado Livre (production)
- [ ] Configurar Shopee (production)
- [ ] Configurar worker de filas (queue:work via supervisor ou cron)

### 🟢 Fase 3 — Monetização (2ª semana)
- [ ] Instalar `mercadopago/dx-php` + configurar planos
- [ ] Instalar `stripe/stripe-php` + configurar preços
- [ ] Implementar webhooks de pagamento
- [ ] Configurar planos no banco de dados

### 🔵 Fase 4 — IA Avançada (próximo sprint)
- [ ] Implementar Assistente Financeiro Inteligente (dashboard)
- [ ] Implementar geração de descrição de produtos com IA
- [ ] Implementar sugestão de respostas ML com IA
- [ ] Implementar Coach de Metas

### ⚪ Fase 5 — Novas Integrações (futuro)
- [ ] WhatsApp (Evolution API ou Z-API)
- [ ] ViaCEP (auto-complete de endereços)
- [ ] Push notifications (Firebase Web Push)
- [ ] NF-e (emissor de notas fiscais)
- [ ] Open Finance / Pluggy (extrato automático)

---

## 📌 Comandos Úteis para o Host

```bash
# Após configurar .env no servidor
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar configuração
php artisan config:show services
php artisan env

# Queue (deixar rodando com supervisor)
php artisan queue:work --daemon --sleep=3 --tries=3

# Limpar caches se algo der errado
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

*Documento mantido por: FlowManager Dev Team*  
*Última atualização: 03/04/2026*
