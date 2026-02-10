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

## 📦 FASE 3: BACKEND SERVICES - EM ANDAMENTO

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
- [ ] Criar `app/Services/MercadoLivre/OrderService.php`
  - [ ] `getOrders($filters)` - Buscar pedidos
  - [ ] `getOrderDetails($mlOrderId)` - Detalhes do pedido
  - [ ] `importOrder($mlOrderId)` - Importar para sistema
  - [ ] `updateShippingStatus($mlOrderId, $status)` - Atualizar envio

### 1.5 Webhook Service
- [ ] Criar `app/Services/MercadoLivre/WebhookService.php`
  - [ ] `validateWebhook($request)` - Validar autenticidade
  - [ ] `processWebhook($topic, $resource)` - Processar notificação
  - [ ] `handleOrderWebhook($orderId)` - Processar pedido
  - [ ] `handleItemWebhook($itemId)` - Processar item

### 1.6 Sync Service
- [ ] Criar `app/Services/MercadoLivre/SyncService.php`
  - [ ] `syncAllProducts()` - Sincronizar todos
  - [ ] `syncProductStock($productId)` - Sync estoque específico
  - [ ] `syncProductPrice($productId)` - Sync preço específico
  - [ ] `syncOrders($dateFrom)` - Sync pedidos

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

## 🎨 FASE 5: FRONTEND (Livewire) - EM ANDAMENTO

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

### 3.2 Orders Manager Component
- [ ] Criar `app/Livewire/MercadoLivre/OrdersManager.php`
- [ ] Criar `resources/views/livewire/mercadolivre/orders-manager.blade.php`
  - [ ] Lista de pedidos ML
  - [ ] Filtros (status, data, valor)
  - [ ] Botão "Importar Pedido"
  - [ ] Detalhes do pedido
  - [ ] Atualização de status

### 3.3 Sync Dashboard Component
- [ ] Criar `app/Livewire/MercadoLivre/SyncDashboard.php`
- [ ] Criar `resources/views/livewire/mercadolivre/sync-dashboard.blade.php`
  - [ ] Estatísticas gerais
  - [ ] Status de sincronização
  - [ ] Logs recentes
  - [ ] Botão "Sincronizar Agora"
  - [ ] Configurações

### 3.4 Settings Component
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

## ⚙️ FASE 4: JOBS & AUTOMATION

### 4.1 Background Jobs
- [ ] Criar `app/Jobs/MercadoLivre/SyncStockJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/SyncPriceJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/ImportOrderJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/RefreshTokenJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/ProcessWebhookJob.php`
- [ ] Criar `app/Jobs/MercadoLivre/CleanupLogsJob.php`

### 4.2 Events & Listeners
- [ ] Criar Event `ProductStockUpdated`
- [ ] Criar Event `ProductPriceUpdated`
- [ ] Criar Listener para disparar sync automático

### 4.3 Scheduler
- [ ] Configurar em `app/Console/Kernel.php`:
  ```php
  $schedule->job(new RefreshTokenJob)->hourly();
  $schedule->job(new SyncStockJob)->everyFiveMinutes();
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

**Última Atualização:** 08/02/2026  
**Status Geral:** 80% Concluído (OAuth Flow Completo Implementado!)  
**Próximo Marco:** Testar OAuth flow com credenciais reais e implementar ProductService
