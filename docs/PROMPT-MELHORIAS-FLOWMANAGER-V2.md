# 🚀 PROMPT DE MELHORIAS — FlowManager v2.0

> Documento operacional para evoluir o FlowManager. Cada fase é executável por seções: contexto → arquivos → comandos → código → testes → validação.

---

## 🎯 Visão Geral

**Stack atual:** Laravel 12 · PHP 8.2 · Livewire 3 · Volt · Flux · Tailwind 4 · Flowbite 3 · Gemini AI
**Stack alvo:** + Reverb · Horizon/Redis · Sentry · Meilisearch · Multi-provider IA · NFe · Brasil API · WhatsApp · PIX · Repricer

**Princípios não-negociáveis (todas as fases):**
- Toda chamada externa → Job em fila com `tries=3`, `backoff=[10,30,60]`.
- Todo Service → Interface (testável, swap em runtime).
- Toda migration → factory + seeder + feature test.
- Toda config nova → `config/services.php` + `.env.example`.
- Toda mudança destrutiva → soft delete + activity log.
- Componentes Livewire >400 linhas → refatorar em `app/Livewire/X/Partials/`.

---

## 📦 FASE 0 — Preparação do ambiente (2 dias)

### 0.1 Reorganizar `config/`
Criar `config/ai.php`, `fiscal.php`, `frete.php`, `whatsapp.php`, `repricer.php`.

`config/ai.php`:
```php
return [
    'default' => env('AI_DEFAULT_PROVIDER', 'gemini'),
    'fallback_chain' => explode(',', env('AI_FALLBACK_CHAIN', 'gemini,claude,openai')),
    'providers' => [
        'gemini' => ['api_key' => env('GEMINI_API_KEY'), 'model' => env('GEMINI_MODEL', 'gemini-2.5-flash')],
        'claude' => ['api_key' => env('ANTHROPIC_API_KEY'), 'model' => env('CLAUDE_MODEL', 'claude-sonnet-4-6')],
        'openai' => ['api_key' => env('OPENAI_API_KEY'), 'model' => env('OPENAI_MODEL', 'gpt-4o-mini')],
    ],
    'features' => [
        'pdf_extraction'      => ['provider' => 'gemini', 'budget_cents' => 5],
        'product_description' => ['provider' => 'claude', 'budget_cents' => 3],
        'ml_question_reply'   => ['provider' => 'claude', 'budget_cents' => 2],
    ],
];
```

### 0.2 Feature flags
`feature_flags` table (key, enabled, rules json) + helper `App\Support\Feature::enabled($key, $user)`.

---

## 🏗️ FASE 1 — Realtime + Jobs + Observabilidade (Sprint 1)

### 1.1 Reverb + Horizon + Redis
```bash
composer require laravel/reverb laravel/horizon predis/predis
php artisan reverb:install && php artisan horizon:install && php artisan migrate
```
`.env`: `BROADCAST_CONNECTION=reverb`, `QUEUE_CONNECTION=redis`.

Horizon supervisors: `default` (4 proc), `ml-sync` (2 proc, 300s), `ai-tasks` (3 proc, 120s).

### 1.2 Extração de PDF em Job
- `app/Jobs/ProcessPdfExtractionJob.php` (tries=3, backoff=[10,30,60]).
- Broadcast `PdfExtractionProgress(userId, file, pct, msg)` em `PrivateChannel("user.{id}")`.
- Frontend: Laravel Echo + Reverb ouvindo progresso, barra de upload em tempo real.

### 1.3 Bell de notificações em tempo real (sidebar)
- `app/Livewire/Components/RealtimeBell.php` ouvindo `echo-private:user.{id},.NotificationSent`.

### 1.4 Observabilidade
```bash
composer require sentry/sentry-laravel spatie/laravel-activitylog spatie/laravel-backup
```
- ActivityLog em Sale, Product, Invoice, Cashbook, Client (logOnlyDirty).
- Backup diário 02:00 (S3 + local). Cron: `backup:run`, `backup:clean`, `activitylog:clean`.

---

## 🎨 FASE 2 — Imagens otimizadas (3 dias)
```bash
composer require intervention/image-laravel
```
- `ProductImageProcessor`: gera thumb(200)/card(600)/full(1200) em WebP + LQIP base64.
- Componente `<x-lazy-image>` com blur placeholder.
- Migration: `product_images.variants` json.

---

## 🧠 FASE 3 — IA multi-provider com fallback (5 dias)

### 3.1 Arquitetura
- Interface `App\AI\Contracts\TextGenerator` → `generate(prompt, opts): GenerationResult`.
- Implementações: `GeminiGenerator`, `ClaudeGenerator`, `OpenAIGenerator`.
- `AiOrchestrator` recebe Collection de providers + chain de fallback.
- `AiUsageLog` table (user, feature, provider, tokens_in/out, cost_cents, success).

### 3.2 ClaudeGenerator (referência)
```php
Http::withHeaders([
    'x-api-key' => $this->apiKey,
    'anthropic-version' => '2023-06-01',
])->post('https://api.anthropic.com/v1/messages', [
    'model' => $this->model,
    'max_tokens' => $opts['max_tokens'] ?? 4096,
    'messages' => [['role' => 'user', 'content' => $prompt]],
]);
```

### 3.3 Novos casos de uso
- **Categorização automática** de produto (foto+nome → categoria).
- **Descrição ML/Shopee** SEO (título 60 chars + bullets + descrição).
- **Detecção de duplicatas** (Gemini Embeddings, cosine > 0.92).
- **Previsão de demanda** (12 semanas → estoque mínimo dinâmico).
- **Resposta a perguntas ML** (3 sugestões: curta/média/longa).
- **OCR de PDFs escaneados** (Tesseract quando PdfParser < 50 chars).

### 3.4 Painel de custos `AiCostsDashboard` (por dia/feature/usuário).

---

## 🇧🇷 FASE 4 — Integrações brasileiras (Sprint 2)

### 4.1 NF-e via PlugNotas/NFE.io
- Interface `NfeIssuer` → issue/cancel/getPdfUrl/getXml.
- Migration `nfes` (sale_id, access_key, number, status, pdf_url, xml).
- Botão "Emitir NF-e" em sales/show.

### 4.2 Frete via Melhor Envio
- `MelhorEnvioService::calculate(toZip, items): Collection`.
- UI no checkout do portal: input CEP + lista de transportadoras.

### 4.3 Pagamentos: PIX dinâmico (Mercado Pago)
```bash
composer require mercadopago/dx-php
```
- `MercadoPagoPixService::createPix(Sale, 30min): PixResult` (qrCode, qrCodeBase64, expiresAt).
- Webhook `webhooks.mercadopago` confirma pagamento → atualiza Sale.
- Boleto + cobrança automática do plano SaaS.

### 4.4 WhatsApp via Z-API
- `ZApiWhatsAppService::sendTemplate(to, template, vars)`.
- Templates: sale_confirmed, invoice_due, goal_reached.
- Listener em SaleConfirmed → SendWhatsAppJob (opt-in do cliente).

### 4.5 Brasil API (gratuita)
- `BrasilApiService`: cep() (autopreenche endereço), cnpj() (razão social/IE), banks().
- Uso: `wire:blur="lookupZip"` em formulários.

---

## 🛒 FASE 5 — Marketplace inteligente (Sprint 3)

### 5.1 Repricer ML
- `ml_repricer_rules` (strategy: match_lowest/beat_by_pct/buy_box, min/max price).
- `RepricerScanJob` a cada 30min → busca concorrentes por EAN, ajusta preço.
- `MlPriceLog` registra alterações.

### 5.2 Agendador de publicações
- `marketplace_scheduled_posts` (product, marketplace, scheduled_at, status).
- UI FullCalendar.js (arrastar produto → data). Job `marketplace:publish-due` a cada minuto.

### 5.3 Sincronizador unificado ML + Shopee
- `UnifiedStockSyncService::syncFromSale(Sale)` → desconta em todos marketplaces.
- Painel comparativo com alerta de divergência > 5%.

---

## 🛍️ FASE 6 — Portal e e-commerce (Sprint 4)

### 6.1 Cupons + Cashback + Fidelidade
- `coupons` (type: percent/fixed/free_shipping/buy_x_get_y, rules json).
- `CouponEngine::apply(Cart, code): CouponResult`.
- `cashback_wallets` + `cashback_transactions` (credit/debit/expiration).
- Listener CreditCashbackOnSale (3% configurável, expira 6 meses).
- Resgate de XP existente em pontos.

### 6.2 Wishlist + Reviews
- `wishlists` (client+product unique).
- `product_reviews` (rating 1-5, photos, seller_reply, verified_purchase).

### 6.3 SEO + Marketing
- `SchemaOrgGenerator::product()` (structured data).
- Pixel Meta + GTM por loja. Sitemap dinâmico diário.

---

## ⚡ FASE 7 — UX premium (Sprint 5)

### 7.1 Busca global Meilisearch (Cmd+K)
```bash
composer require meilisearch/meilisearch-php laravel/scout
```
- Scout Searchable em Product, Client, Sale.
- `CommandPalette` Livewire: produtos + clientes + atalhos.
- Atalho `Cmd/Ctrl+K` global.

### 7.2 PWA + offline
```bash
npm i workbox-window vite-plugin-pwa -D
```
- Manifest + Service Worker. Vendas offline via IndexedDB, sync ao reconectar.

### 7.3 Push notifications
```bash
composer require laravel-notification-channels/webpush
```
- VAPID. Eventos: pergunta ML sem resposta 1h, fatura vencendo, meta atingida.

### 7.4 Onboarding (Driver.js) + i18n (pt_BR/en/es).

---

## 📦 FASE 8 — Estoque avançado (Sprint 6)

### 8.1 Multi-armazém
- `warehouses` (physical_store/depot/dropshipping).
- `product_stock` (product+warehouse, quantity, reserved).
- `stock_transfers` + items.

### 8.2 FEFO + validade
- `product_lots` (lot_number, expiry_date, quantity).
- Picking ordenado por validade. Alerta diário de produtos vencendo (30 dias).

### 8.3 Inventário
- `inventories` + `inventory_counts` (expected/counted/difference).
- Contagem via barcode scanner existente → ajustes automáticos.

---

## 🧪 FASE 9 — Qualidade (Sprint 7)

### 9.1 Pest 4 Browser Tests
```bash
composer require pestphp/pest-plugin-browser --dev
```
- Fluxos críticos: criar venda completa, adicionar kit, emitir NF.

### 9.2 CI GitHub Actions
- MySQL + Redis services. `vendor/bin/pest --coverage --min=60`.

### 9.3 Refactor componentes >400 linhas
- Padrão: orquestrador + Concerns/ (traits) + Forms/ (Form Objects).
- Exemplo CreateKit: ManagesProducts, CalculatesPricing, HandlesAdditionalCosts, PersistsKit.

---

## 📋 Checklist por feature (PR)
- [ ] Migration + factory + seeder
- [ ] Feature test (Pest) + Browser test se UI crítica
- [ ] Doc em `docs/<feature>.md`
- [ ] Config + `.env.example`
- [ ] Activity log nos modelos afetados
- [ ] Job em fila para chamadas externas
- [ ] Feature flag se experimental
- [ ] Tradução pt_BR/en/es
- [ ] Acessibilidade (aria-label, focus visible)
- [ ] Mobile/iPad responsive + dark mode
- [ ] Queries N+1 verificadas

---

## 🎯 Roadmap consolidado

| Q | Foco | Entregas |
|---|------|----------|
| **Q1** | Fundação | Reverb · Horizon · Sentry · ActivityLog · Backup · Imagens WebP |
| **Q2** | IA + Brasil | Multi-provider · OCR · NFe · PIX · Brasil API · WhatsApp |
| **Q3** | Marketplace | Repricer · Agendador · Sync unificado · Concorrência |
| **Q4** | E-commerce + UX | Cupons · Cashback · Wishlist · Reviews · PWA · Cmd+K · i18n · Multi-armazém · FEFO |

---

## 💰 Custo estimado de infra (~393 USD/mês)
App t3.medium (30) · Worker t3.small (16) · RDS MySQL (25) · Redis (12) · Reverb (12) · Meilisearch (24) · S3 (3) · Bandwidth (45) · IA tokens (80) · PlugNotas (90) · Z-API (30) · Sentry (26).

---

**Fonte da verdade para os próximos 6-9 meses. Cada fase é independente e re-priorizável conforme o negócio.**
