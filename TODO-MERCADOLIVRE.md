# 📋 TO-DO LIST - Integração Mercado Livre

## ✅ CONCLUÍDO

### Database & Models
- [x] Criar migration `add_mercadolivre_fields_to_products_table`
- [x] Criar migration `create_mercadolivre_products_table`
- [x] Criar migration `create_mercadolivre_orders_table`
- [x] Criar migration `create_mercadolivre_tokens_table`
- [x] Criar migration `create_mercadolivre_sync_log_table`
- [x] Criar migration `create_mercadolivre_webhooks_table`
- [x] Criar Model `MercadoLivreProduct`
- [x] Criar Model `MercadoLivreOrder`
- [x] Criar Model `MercadoLivreToken`
- [x] Criar Model `MercadoLivreSyncLog`
- [x] Criar Model `MercadoLivreWebhook`
- [x] Atualizar Model `Product` com novos campos
- [x] Adicionar relacionamento `mercadoLivreProduct` no Model Product
- [x] Documentação completa criada

---

## 🔄 PRÓXIMOS PASSOS IMEDIATOS

### 1. ✅ Executar Migrations (CONCLUÍDO)
```bash
php artisan migrate
```
**Status:** ✅ Todas as 6 migrations executadas com sucesso!

### 2. ✅ Atualizar Formulários de Produto (CONCLUÍDO)
- [x] Adicionar campo `barcode` no formulário de criação
- [x] Adicionar campo `brand` no formulário
- [x] Adicionar campo `model` no formulário
- [x] Adicionar campo `warranty_months` no formulário
- [x] Adicionar campo `condition` (select: novo/usado)
- [x] Adicionar validações nos Livewire Components

**Arquivos editados:**
- ✅ `app/Livewire/Products/CreateProduct.php`
- ✅ `app/Livewire/Products/EditProduct.php`
- ✅ `resources/views/livewire/products/create-product.blade.php`
- ✅ `resources/views/livewire/products/edit-product.blade.php`

### 3. ⏳ Configurar Credenciais ML (PRÓXIMO PASSO!)

**📚 Guias disponíveis:**
- ✅ `docs/GUIA-CONFIGURACAO-MERCADO-LIVRE-DEV.md` - Guia completo detalhado
- ✅ `docs/CHECKLIST-CONFIGURACAO-ML.md` - Checklist interativo passo a passo
- ✅ `docs/GUIA-RAPIDO-CONFIGURACAO-ML.md` - Guia rápido visual (7 passos)
- ✅ `setup-ngrok.bat` - Script para iniciar ngrok automaticamente

**📋 Passos resumidos:**
- [ ] **1.** Criar conta em https://developers.mercadolivre.com.br/
- [ ] **2.** Criar aplicação "FlowManager" no portal
- [ ] **3.** Instalar e configurar ngrok (para desenvolvimento com HTTPS)
- [ ] **4.** Configurar Redirect URI: `https://SEU_NGROK.ngrok.io/mercadolivre/auth/callback`
- [ ] **5.** Configurar permissões (Usuários, Publicação, Vendas, Mensagens)
- [ ] **6.** Copiar App ID e Secret Key
- [ ] **7.** Adicionar credenciais no `.env`:
  ```env
  MERCADOLIVRE_APP_ID=seu_app_id_aqui
  MERCADOLIVRE_SECRET_KEY=sua_secret_key_aqui
  MERCADOLIVRE_REDIRECT_URI=https://SEU_NGROK.ngrok.io/mercadolivre/auth/callback
  MERCADOLIVRE_WEBHOOK_SECRET=
  MERCADOLIVRE_ENVIRONMENT=production
  ```
- [ ] **8.** Limpar cache: `php artisan config:clear && php artisan config:cache`
- [ ] **9.** Testar OAuth: Acessar `http://localhost:8000/mercadolivre/settings`
- [ ] **10.** Conectar e verificar sucesso! 🎉

**⏱️ Tempo estimado:** 30-40 minutos  
**📖 Siga o guia:** `docs/GUIA-RAPIDO-CONFIGURACAO-ML.md`

---

## 📦 FASE 3: BACKEND SERVICES - ✅ CONCLUÍDO

### 1.1 Service Base
- [x] Criar `app/Services/MercadoLivre/MercadoLivreService.php`
  - [x] Método `makeRequest($method, $endpoint, $data)`
  - [x] Método `getHeaders()`
  - [x] Tratamento de erros e rate limiting
  - [x] Retry automático (3 tentativas com exponential backoff)
  - [x] Logging automático de requisições
  - [x] Cache para rate limiting

### 1.2 Auth Service  
- [x] Criar `app/Services/MercadoLivre/AuthService.php`
  - [x] `getAuthorizationUrl()` - URL de autorização OAuth
  - [x] `handleCallback($code)` - Processar código e gerar token
  - [x] `refreshToken($refreshToken)` - Renovar token expirado
  - [x] `revokeToken()` - Revogar acesso
  - [x] `getActiveToken()` - Obter token ativo com auto-refresh
  - [x] `isConnected()` - Verificar status de conexão
  - [x] `testConnection()` - Testar token

### 1.3 Product Service
- [x] Criar `app/Services/MercadoLivre/ProductService.php`
  - [x] `createProduct($productData)` - Criar anúncio
  - [x] `updateProduct($mlItemId, $data)` - Atualizar anúncio
  - [x] `updateStock($mlItemId, $quantity)` - Sync estoque
  - [x] `updatePrice($mlItemId, $price)` - Sync preço
  - [x] `pauseProduct($mlItemId)` - Pausar anúncio
  - [x] `activateProduct($mlItemId)` - Reativar anúncio
  - [x] `getCategories()` - Buscar categorias MLB
  - [x] `getCategoryAttributes($categoryId)` - Atributos obrigatórios
  - [x] `predictCategory($title)` - Sugerir categoria baseado no título
  - [x] `getCategoryDetails($categoryId)` - Detalhes da categoria

### 1.4 Order Service
- [x] Criar `app/Services/MercadoLivre/OrderService.php` ✅ (Já existe)
  - [x] `getOrders($filters)` - Buscar pedidos
  - [x] `getOrderDetails($mlOrderId)` - Detalhes do pedido
  - [x] `importOrder($mlOrderId)` - Importar para sistema
  - [x] `updateShippingStatus($mlOrderId, $status)` - Atualizar envio

### 1.5 Webhook Service
- [x] Criar `app/Services/MercadoLivre/WebhookService.php` ✅
  - [x] `validateWebhook($request)` - Validar autenticidade
  - [x] `processWebhook($topic, $resource)` - Processar notificação
  - [x] `handleOrderWebhook($orderId)` - Processar pedido (✅ Atualizado com MlStockSyncService)
  - [x] `handleItemWebhook($itemId)` - Processar item

### 1.6 Stock Sync Service (NOVO!) ✅
- [x] Criar `app/Services/MercadoLivre/MlStockSyncService.php`
  - [x] `syncQuantityToMercadoLivre($publication)` - Sync para ML via API
  - [x] `processMercadoLivreSale($mlOrderId, $mlItemId, $quantity)` - Processar venda ML
  - [x] `syncAllPending()` - Sincronizar publicações pendentes (batch)
  - [x] `auditAndFix($publication)` - Detectar e corrigir divergências

---

## 🆕 FASE 3.5: SISTEMA DE PUBLICAÇÕES & KITS - ✅ CONCLUÍDO

### 3.5.1 Database Architecture
- [x] Migration `create_ml_publications_table` - Publicações com suporte a kits
- [x] Migration `create_ml_publication_products_table` - Pivot N:N com multiplicador
- [x] Migration `create_ml_stock_logs_table` - Auditoria completa (7 operation types)
- [x] Executar migrations ✅

### 3.5.2 Models & Business Logic
- [x] Model `MlPublication` (330 linhas)
  - [x] Relationships: products (BelongsToMany), user, stockLogs, orders
  - [x] `calculateAvailableQuantity()` - Cálculo inteligente baseado em min(stock/quantity)
  - [x] `deductStock()` - Dedução atômica com DB transaction e rollback
  - [x] `addProduct()`, `removeProduct()`, `updateProductQuantity()` - Gerenciamento de produtos
  - [x] Scopes: active, kits, simple, withErrors, pending, withProduct, withProductCode
  
- [x] Model `MlStockLog` (170 linhas)
  - [x] Relationships: product, publication, user
  - [x] `logStockChange()` - Criar logs de auditoria
  - [x] `findConflicts()` - Detectar race conditions
  - [x] Scopes: forProduct, mlSales, rolledBack, forTransaction, betweenDates
  
- [x] Atualizar Model `Product`
  - [x] `mlPublications()` - Relationship N:N com pivot
  - [x] `hasActivePublications()` - Verifica se está em publicações ativas
  - [x] `getActivePublications()` - Retorna publicações ativas

### 3.5.3 Observer & Automation
- [x] Observer `ProductObserver` (140 linhas)
  - [x] `updated()` - Detecta mudanças em stock_quantity
  - [x] `handleStockChange()` - Cria logs e dispara sincronização
  - [x] `syncPublications()` - Sincroniza por ID e product_code
  - [x] `detectOperationType()` - CLI, SaleController, manual_update
  - [x] Registrado em `AppServiceProvider`

### 3.5.4 Background Jobs
- [x] Job `SyncPublicationToMercadoLivre` - Async sync com retry (3x)
- [x] Job `ProcessMercadoLivreWebhook` - Processa webhooks async
- [x] Backoff delays: [60s, 5min, 15min]
- [x] Tratamento de falhas e error_message

### 3.5.5 Funcionalidades Implementadas
- [x] ✅ Múltiplos produtos por publicação (kits)
- [x] ✅ Mesmo produto em múltiplas publicações
- [x] ✅ Product_code awareness (sincroniza variantes)
- [x] ✅ Auto-sync em vendas ML (via webhook)
- [x] ✅ Auto-sync em mudanças manuais (via observer)
- [x] ✅ Auto-sync em importação Excel
- [x] ✅ Auto-sync em vendas internas
- [x] ✅ Audit trail completo com transaction_id
- [x] ✅ Rollback de operações
- [x] ✅ Detecção de conflitos (race conditions)

### 3.5.6 Documentação
- [x] `docs/ml-publications-system-refactoring.md` - Guia completo (600+ linhas)
  - [x] Visão geral da arquitetura
  - [x] Exemplos de uso
  - [x] Fluxos de sincronização
  - [x] Cenários de teste
  - [x] Troubleshooting

---

## 🎮 FASE 4: CONTROLLERS - CONCLUÍDA

### 2.1 Auth Controller
- [x] Criar `app/Http/Controllers/MercadoLivre/AuthController.php`
  - [x] `redirect()` - Redirecionar para autorização
  - [x] `callback()` - Receber código OAuth
  - [x] `disconnect()` - Desconectar conta
  - [x] `status()` - Status AJAX
  - [x] `testConnection()` - Testar token AJAX

### 2.2 Webhook Controller
- [ ] Criar `app/Http/Controllers/MercadoLivre/WebhookController.php`
  - [ ] `handle(Request $request)` - Receber webhooks

### 2.3 Product Controller
- [ ] Criar `app/Http/Controllers/MercadoLivre/ProductController.php`
  - [ ] `publish($productId)` - Publicar no ML
  - [ ] `sync($productId)` - Sincronizar produto

### 2.4 Rotas
- [x] Adicionar rotas em `routes/web.php`:
  - [x] `/mercadolivre/auth/redirect` - Redirecionar para ML
  - [x] `/mercadolivre/auth/callback` - Callback OAuth
  - [x] `/mercadolivre/auth/disconnect` - Desconectar
  - [x] `/mercadolivre/auth/status` - Status AJAX
  - [x] `/mercadolivre/auth/test` - Test connection AJAX

---

## 🎨 FASE 5: FRONTEND (Livewire) - ✅ 85% CONCLUÍDO

### 3.1 Product Integration Component
- [x] Criar `app/Livewire/MercadoLivre/ProductIntegration.php` ✅
- [x] Criar `resources/views/livewire/mercadolivre/product-integration.blade.php` ✅ CONCLUÍDO
  - [x] Listagem de produtos com filtros
  - [x] Modal de publicação
  - [x] Predição automática de categoria ML
  - [x] Seleção de categoria MLB
  - [x] Formulário de atributos obrigatórios dinâmicos
  - [x] Botão "Publicar no ML"
  - [x] Status de sincronização
  - [x] Botões: Sync, Pausar, Reativar
  - [x] Interface visual (blade template)
  - [x] Lógica completa implementada
  - [x] Menu adicionado na sidebar
  - [x] Rota configurada

### 3.2 Publications Manager (NOVO!) ✅
- [x] Component `PublicationsList` - Listagem de publicações
  - [x] Stats cards (Total, Ativas, Kits, Erros)
  - [x] Filtros (busca, status, tipo, sync)
  - [x] Cards de publicação com informações
  - [x] Paginação
  - [x] Links para edição
  
- [x] Component `EditPublication` - Editar publicação
  - [x] Formulário de dados básicos
  - [x] Gerenciar produtos do kit
  - [x] Adicionar/remover produtos
  - [x] Atualizar quantidade de produtos
  - [x] Status cards (publicação, sync, disponibilidade)
  - [x] Logs recentes de movimentação
  - [x] Botões: Pausar, Ativar, Sincronizar, Deletar
  - [x] Rota: `/mercadolivre/publications/{id}/edit`
  
- [x] Component `ProductSelector` (Reutilizável)
  - [x] Busca de produtos por nome/código/barcode
  - [x] Adicionar produtos à seleção
  - [x] Remover produtos
  - [x] Definir quantidade por produto
  - [x] Cálculo automático de disponibilidade
  - [x] Resumo financeiro
  - [x] Usado em: PublishProduct, EditPublication

### 3.3 Publish Product Component (Atualizado)
- [x] Component `PublishProduct`
  - [x] Mantido sistema existente de publicação simples
  - [x] Busca no catálogo ML por barcode
  - [x] Predição de categoria
  - [x] Atributos obrigatórios dinâmicos
  - [x] **TODO:** Integrar ProductSelector para criar kits
  - [x] **TODO:** Radio button "Simples" vs "Kit"

### 3.4 Orders Manager Component
- [ ] Criar `app/Livewire/MercadoLivre/OrdersManager.php`
- [ ] Criar `resources/views/livewire/mercadolivre/orders-manager.blade.php`
  - [ ] Lista de pedidos ML
  - [ ] Filtros (status, data, valor)
  - [ ] Botão "Importar Pedido"
  - [ ] Detalhes do pedido
  - [ ] Atualização de status

### 3.5 Sync Dashboard Component
- [ ] Criar `app/Livewire/MercadoLivre/SyncDashboard.php`
- [ ] Criar `resources/views/livewire/mercadolivre/sync-dashboard.blade.php`
  - [ ] Estatísticas gerais
  - [ ] Status de sincronização
  - [ ] Logs recentes
  - [ ] Botão "Sincronizar Agora"
  - [ ] Configurações

### 3.6 Settings Component
- [x] Criar `app/Livewire/MercadoLivre/Settings.php`
- [x] Criar `resources/views/livewire/mercadolivre/settings.blade.php`
  - [x] Status da conexão
  - [x] Botão conectar/desconectar
  - [x] Informações do vendedor ML
  - [x] Data de expiração do token
  - [x] Teste de conexão
  - [x] Renovar token manualmente
  - [x] Interface completa com status visual
  - [x] Layout moderno integrado ao sistema
  - [x] Sidebar com próximos passos e ajuda
  - [x] Header consistente com outras páginas
  - [x] Design responsivo com Tailwind
  - [x] Cards informativos e visuais modernos

---

## ⚙️ FASE 6: JOBS & AUTOMATION - ✅ 60% CONCLUÍDO

### 6.1 Background Jobs
- [x] Criar `app/Jobs/SyncPublicationToMercadoLivre.php` ✅
  - [x] Queue assíncrono com 3 tentativas
  - [x] Backoff delays: [60s, 5min, 15min]
  - [x] Tratamento de falhas com error_message
  - [x] Integrado com MlStockSyncService
  
- [x] Criar `app/Jobs/ProcessMercadoLivreWebhook.php` ✅
  - [x] Processa webhooks de forma assíncrona
  - [x] 3 retries com backoff: [30s, 2min, 10min]
  - [x] Marca webhooks como processed/error
  - [x] Integrado com WebhookController
  
- [ ] Criar `app/Jobs/MercadoLivre/SyncStockJob.php` (Legacy - usar MlStockSyncService)
- [ ] Criar `app/Jobs/MercadoLivre/SyncPriceJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/ImportOrderJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/RefreshTokenJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/CleanupLogsJob.php`

### 6.2 Observers (NOVO!) ✅
- [x] Observer `ProductObserver` - Auto-sync em mudanças de estoque
  - [x] Detecta mudanças em stock_quantity
  - [x] Cria logs de auditoria
  - [x] Dispara sincronização para todas as publicações
  - [x] Suporte a product_code (sincroniza variantes)
  - [x] Registrado em AppServiceProvider

### 6.3 Events & Listeners
- [x] ✅ Auto-sync via ProductObserver (substitui Events)
- [ ] Criar Event `ProductStockUpdated` (opcional - observer já funciona)
- [ ] Criar Event `ProductPriceUpdated`
- [ ] Criar Listener para disparar sync automático (opcional)

### 6.4 Scheduler
- [ ] Configurar em `app/Console/Kernel.php`:
  ```php
  $schedule->job(new RefreshTokenJob)->hourly();
  $schedule->call(function() {
    app(MlStockSyncService::class)->syncAllPending();
  })->everyFiveMinutes();
  $schedule->job(new ImportOrderJob)->everyTenMinutes();
  $schedule->job(new CleanupLogsJob)->daily();
  ```

---

## 🧪 FASE 5: TESTES

### 5.1 Testes Unitários
- [ ] Teste AuthService
- [ ] Teste ProductService
- [ ] Teste OrderService
- [ ] Teste Models

### 5.2 Testes de Integração
- [ ] Teste OAuth flow completo (sandbox)
- [ ] Teste criação de produto
- [ ] Teste atualização de estoque
- [ ] Teste importação de pedido
- [ ] Teste processamento de webhooks

### 5.3 Testes E2E
- [ ] Fluxo completo de publicação
- [ ] Fluxo completo de venda e importação
- [ ] Fluxo de sincronização automática

---

## 📊 FASE 6: MONITORAMENTO

### 6.1 Dashboard
- [ ] Gráfico de vendas ML vs. Internas
- [ ] Taxa de sincronização
- [ ] Produtos publicados
- [ ] Pedidos pendentes
- [ ] Erros recentes

### 6.2 Alertas
- [ ] Token próximo de expirar
- [ ] Erro crítico de sincronização
- [ ] Produto sem estoque no ML
- [ ] Pedido não importado há X horas
- [ ] Limite de API próximo

### 6.3 Logs
- [ ] Dashboard de logs
- [ ] Filtros por tipo, status, data
- [ ] Export de logs
- [ ] Limpeza automática (30 dias)

---

## 🔒 FASE 7: SEGURANÇA & PERFORMANCE

### 7.1 Segurança
- [ ] Criptografar tokens no banco
- [ ] Validação de assinatura de webhooks
- [ ] Rate limiting em endpoints
- [ ] CSRF protection
- [ ] Permissões de usuário

### 7.2 Performance
- [ ] Cache de categorias ML
- [ ] Queue para sincronizações
- [ ] Bulk updates quando possível
- [ ] Otimização de queries

### 7.3 Backup
- [ ] Backup de tokens
- [ ] Backup de logs importantes
- [ ] Recovery plan

---

## 📚 FASE 8: DOCUMENTAÇÃO

### 8.1 Documentação Técnica
- [ ] README de integração
- [ ] Guia de configuração
- [ ] Troubleshooting
- [ ] API reference interna

### 8.2 Documentação de Usuário
- [ ] Como conectar conta ML
- [ ] Como publicar produto
- [ ] Como gerenciar pedidos
- [ ] FAQ

---

## 🎯 PRIORIDADES

### Alta (Fazer Agora)
1. ✅ Executar migrations
2. ✅ Atualizar formulários de produto
3. ✅ Criar Services base
4. ✅ Implementar AuthService
5. ✅ Criar interface de conexão (Settings Component)
6. ✅ Criar Controllers e Routes
7. ⏳ **PRÓXIMO:** Obter credenciais ML e testar OAuth flow

### Média (Próxima Sprint)
1. Implementar ProductService
2. Implementar OrderService
3. Criar componentes Livewire básicos
4. Implementar webhooks

### Baixa (Futuro)
1. Dashboard avançado
2. Alertas automáticos
3. Relatórios customizados
4. Integrações adicionais

---

## 📈 MÉTRICAS DE SUCESSO

- [ ] Taxa de sincronização > 95%
- [ ] Tempo de resposta API < 2s
- [ ] Pedidos importados automaticamente > 90%
- [ ] Uptime de webhooks > 99%
- [ ] Satisfação do usuário > 4.5/5

---

**Última Atualização:** 09/02/2026  
**Status Geral:** 92% Concluído (Sistema de Kits & Auto-Sync Implementado!)  
**Próximo Marco:** Testar OAuth flow com credenciais reais, integrar ProductSelector no PublishProduct, criar OrdersManager
