# 🛒 INTEGRAÇÃO MERCADO LIVRE - Plano Completo de Implementação

**Data de Criação:** 08/02/2026  
**Projeto:** FlowManager  
**Status:** 📋 Planejamento

---

## 📊 VISÃO GERAL

Este documento detalha o plano completo para integração do FlowManager com a API do Mercado Livre, permitindo:

- ✅ Sincronização de Estoque em tempo real
- ✅ Gestão de Vendas (importação automática)
- ✅ Publicação e Gerenciamento de Anúncios
- ✅ Atualização automática de preços
- ✅ Notificações de eventos importantes

---

## 🎯 REQUISITOS TÉCNICOS DA API MERCADO LIVRE

### 1. Credenciais e Autenticação

**Tipo de Autenticação:** OAuth 2.0

**Endpoints Principais:**
```
Base URL: https://api.mercadolibre.com
Auth URL: https://auth.mercadolivre.com.br/authorization
Token URL: https://api.mercadolibre.com/oauth/token
```

**Credenciais Necessárias:**
- `APP_ID` (Client ID)
- `SECRET_KEY` (Client Secret)
- `REDIRECT_URI` (Callback URL)

### 2. Campos Obrigatórios para Publicação

#### Produto Básico
| Campo | Tipo | Descrição | Exemplo |
|-------|------|-----------|---------|
| `title` | string | Título do anúncio (max 60 chars) | "iPhone 13 Pro Max 256GB" |
| `category_id` | string | ID da categoria MLB | "MLB1055" |
| `price` | decimal | Preço de venda | 5999.99 |
| `currency_id` | string | Moeda (fixo para BR) | "BRL" |
| `available_quantity` | integer | Quantidade em estoque | 10 |
| `buying_mode` | string | Modo de compra | "buy_it_now" |
| `listing_type_id` | string | Tipo de anúncio | "gold_special" |
| `condition` | string | Condição do produto | "new" ou "used" |

#### Campos Recomendados
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `description` | object | Descrição completa HTML |
| `pictures` | array | URLs das imagens |
| `attributes` | array | Atributos obrigatórios da categoria |
| `video_id` | string | ID do vídeo no YouTube |
| `warranty` | string | Garantia do produto |
| `shipping` | object | Configurações de frete |

#### Identificadores Importantes
- **GTIN/EAN:** Código de barras universal (obrigatório para algumas categorias)
- **Brand:** Marca do produto
- **Model:** Modelo específico

### 3. Categorias MLB

O Mercado Livre usa IDs específicos para categorias:
- `MLB1055` - Celulares e Telefones
- `MLB1000` - Eletrônicos, Áudio e Vídeo
- `MLB5672` - Computação
- `MLB1574` - Casa, Móveis e Decoração
- `MLB1430` - Esporte e Fitness

**API para buscar categorias:**
```
GET https://api.mercadolibre.com/sites/MLB/categories
GET https://api.mercadolibre.com/categories/{category_id}
```

### 4. Atributos Obrigatórios por Categoria

Cada categoria exige atributos específicos. Exemplos:

**Eletrônicos:**
- `BRAND` (Marca)
- `MODEL` (Modelo)
- `GTIN` (Código de barras)
- `WARRANTY_TYPE` (Tipo de garantia)

**Roupas:**
- `BRAND` (Marca)
- `SIZE` (Tamanho)
- `GENDER` (Gênero)
- `COLOR` (Cor)

### 5. Webhooks (Notificações)

O Mercado Livre envia notificações para eventos importantes:

**Tipos de Notificações:**
- `orders` - Nova venda realizada
- `items` - Mudanças em anúncios
- `questions` - Perguntas de clientes
- `claims` - Reclamações

**Endpoint de Webhook:**
```
POST https://seu-dominio.com/api/webhooks/mercadolivre
```

### 6. Limites da API

| Recurso | Limite |
|---------|--------|
| Requests/hora | 10,000 |
| Publicações/dia | 5,000 |
| Atualizações/hora | 1,000 |
| Token Expiration | 6 horas |
| Refresh Token | 6 meses |

---

## 🗄️ ESTRUTURA DO BANCO DE DADOS

### Tabela: `products` (Ajustes)

**Novos Campos:**
```sql
ALTER TABLE products ADD COLUMN barcode VARCHAR(15) UNIQUE NULL COMMENT 'EAN/GTIN';
ALTER TABLE products ADD COLUMN brand VARCHAR(100) NULL COMMENT 'Marca do produto';
ALTER TABLE products ADD COLUMN model VARCHAR(100) NULL COMMENT 'Modelo do produto';
ALTER TABLE products ADD COLUMN warranty_months INT NULL DEFAULT 3 COMMENT 'Meses de garantia';
ALTER TABLE products ADD COLUMN condition ENUM('new', 'used') DEFAULT 'new';
```

### Tabela: `mercadolivre_products`

Relaciona produtos internos com anúncios no ML:

```sql
CREATE TABLE mercadolivre_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    ml_item_id VARCHAR(50) NOT NULL UNIQUE COMMENT 'ID do anúncio no ML',
    ml_category_id VARCHAR(20) NOT NULL COMMENT 'Categoria MLB',
    listing_type VARCHAR(20) DEFAULT 'gold_special',
    status ENUM('active', 'paused', 'closed', 'under_review') DEFAULT 'active',
    ml_permalink VARCHAR(255) COMMENT 'Link do anúncio',
    sync_status ENUM('synced', 'pending', 'error') DEFAULT 'pending',
    last_sync_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_ml_item_id (ml_item_id),
    INDEX idx_sync_status (sync_status),
    INDEX idx_status (status)
);
```

### Tabela: `mercadolivre_orders`

Armazena vendas vindas do ML:

```sql
CREATE TABLE mercadolivre_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ml_order_id VARCHAR(50) NOT NULL UNIQUE,
    ml_item_id VARCHAR(50) NOT NULL,
    product_id INT UNSIGNED NULL,
    buyer_id BIGINT COMMENT 'ID do comprador no ML',
    buyer_nickname VARCHAR(100),
    buyer_email VARCHAR(255),
    buyer_phone VARCHAR(20),
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency_id VARCHAR(3) DEFAULT 'BRL',
    order_status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_id VARCHAR(50) NULL,
    tracking_number VARCHAR(100) NULL,
    date_created TIMESTAMP NOT NULL,
    date_closed TIMESTAMP NULL,
    imported_to_sale_id INT UNSIGNED NULL COMMENT 'ID da venda importada no sistema',
    sync_status ENUM('pending', 'imported', 'error') DEFAULT 'pending',
    error_message TEXT NULL,
    raw_data JSON COMMENT 'Dados completos da API',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (imported_to_sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    INDEX idx_ml_order_id (ml_order_id),
    INDEX idx_order_status (order_status),
    INDEX idx_sync_status (sync_status),
    INDEX idx_date_created (date_created)
);
```

### Tabela: `mercadolivre_tokens`

Gerencia tokens OAuth:

```sql
CREATE TABLE mercadolivre_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    ml_user_id BIGINT COMMENT 'ID do usuário no ML',
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    token_type VARCHAR(20) DEFAULT 'Bearer',
    expires_at TIMESTAMP NOT NULL,
    scope TEXT COMMENT 'Permissões do token',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_active (is_active)
);
```

### Tabela: `mercadolivre_sync_log`

Log de sincronizações:

```sql
CREATE TABLE mercadolivre_sync_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    sync_type ENUM('stock', 'price', 'product', 'order', 'status') NOT NULL,
    entity_type VARCHAR(50) COMMENT 'Product, Order, etc',
    entity_id BIGINT UNSIGNED COMMENT 'ID da entidade',
    action VARCHAR(50) COMMENT 'create, update, delete, sync',
    status ENUM('success', 'error', 'warning') NOT NULL,
    message TEXT,
    request_data JSON NULL,
    response_data JSON NULL,
    execution_time INT COMMENT 'Tempo em ms',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_sync (user_id, sync_type, created_at),
    INDEX idx_status (status),
    INDEX idx_entity (entity_type, entity_id)
);
```

### Tabela: `mercadolivre_webhooks`

Registro de webhooks recebidos:

```sql
CREATE TABLE mercadolivre_webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topic VARCHAR(100) NOT NULL COMMENT 'orders, items, questions, claims',
    resource VARCHAR(255) NOT NULL COMMENT 'URL do recurso',
    ml_user_id BIGINT NOT NULL,
    application_id BIGINT NOT NULL,
    attempts INT DEFAULT 0,
    sent TIMESTAMP NOT NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    raw_data JSON NOT NULL,
    error_message TEXT NULL,
    INDEX idx_topic (topic),
    INDEX idx_processed (processed),
    INDEX idx_received (received_at)
);
```

---

## 📝 TO-DO LIST DETALHADA

### 🔧 FASE 1: CONFIGURAÇÃO INICIAL (Backend)

#### 1.1 Mercado Livre Developers
- [ ] Criar conta no [Mercado Livre Developers](https://developers.mercadolivre.com.br/)
- [ ] Criar aplicação e obter `APP_ID` e `SECRET_KEY`
- [ ] Configurar URL de redirecionamento
- [ ] Definir permissões necessárias:
  - `offline_access`
  - `read`
  - `write`
- [ ] Configurar webhook notifications

#### 1.2 Configuração do Ambiente
```env
# .env
MERCADOLIVRE_APP_ID=seu_app_id
MERCADOLIVRE_SECRET_KEY=sua_secret_key
MERCADOLIVRE_REDIRECT_URI=https://seu-dominio.com/auth/mercadolivre/callback
MERCADOLIVRE_WEBHOOK_SECRET=sua_webhook_secret
MERCADOLIVRE_ENVIRONMENT=production # ou sandbox para testes
```

#### 1.3 Estrutura de Arquivos
```
app/
├── Services/
│   ├── MercadoLivre/
│   │   ├── MercadoLivreService.php
│   │   ├── AuthService.php
│   │   ├── ProductService.php
│   │   ├── OrderService.php
│   │   ├── WebhookService.php
│   │   └── SyncService.php
├── Http/
│   ├── Controllers/
│   │   └── MercadoLivre/
│   │       ├── AuthController.php
│   │       ├── WebhookController.php
│   │       └── ProductController.php
├── Jobs/
│   └── MercadoLivre/
│       ├── SyncStockJob.php
│       ├── SyncPriceJob.php
│       ├── ImportOrderJob.php
│       └── RefreshTokenJob.php
├── Models/
│   ├── MercadoLivreProduct.php
│   ├── MercadoLivreOrder.php
│   ├── MercadoLivreToken.php
│   ├── MercadoLivreSyncLog.php
│   └── MercadoLivreWebhook.php
└── Livewire/
    └── MercadoLivre/
        ├── ProductIntegration.php
        ├── OrdersManager.php
        └── SyncDashboard.php
```

---

### 🗄️ FASE 2: DATABASE (Migrations)

- [x] **Migration 1:** Adicionar campo `barcode` à tabela `products`
- [x] **Migration 2:** Adicionar campos `brand`, `model`, `warranty_months`, `condition`
- [ ] **Migration 3:** Criar tabela `mercadolivre_products`
- [ ] **Migration 4:** Criar tabela `mercadolivre_orders`
- [ ] **Migration 5:** Criar tabela `mercadolivre_tokens`
- [ ] **Migration 6:** Criar tabela `mercadolivre_sync_log`
- [ ] **Migration 7:** Criar tabela `mercadolivre_webhooks`

---

### 💻 FASE 3: BACKEND (Services & Controllers)

#### 3.1 Service Layer
- [ ] `AuthService.php` - Autenticação OAuth 2.0
  - [ ] Método `getAuthorizationUrl()`
  - [ ] Método `handleCallback($code)`
  - [ ] Método `refreshToken($refreshToken)`
  - [ ] Método `revokeToken()`

- [ ] `ProductService.php` - Gerenciamento de Produtos
  - [ ] Método `listProducts()` - Listar anúncios do ML
  - [ ] Método `createProduct($productData)` - Criar anúncio
  - [ ] Método `updateProduct($mlItemId, $data)` - Atualizar anúncio
  - [ ] Método `updateStock($mlItemId, $quantity)` - Atualizar estoque
  - [ ] Método `updatePrice($mlItemId, $price)` - Atualizar preço
  - [ ] Método `pauseProduct($mlItemId)` - Pausar anúncio
  - [ ] Método `deleteProduct($mlItemId)` - Deletar anúncio

- [ ] `OrderService.php` - Gerenciamento de Pedidos
  - [ ] Método `getOrders($filters)` - Buscar pedidos
  - [ ] Método `getOrderDetails($mlOrderId)` - Detalhes do pedido
  - [ ] Método `importOrder($mlOrderId)` - Importar para o sistema
  - [ ] Método `updateShippingStatus($mlOrderId, $status)`

- [ ] `WebhookService.php` - Processamento de Webhooks
  - [ ] Método `validateWebhook($request)` - Validar autenticidade
  - [ ] Método `processWebhook($topic, $resource)` - Processar notificação
  - [ ] Método `handleOrderWebhook($orderId)`
  - [ ] Método `handleItemWebhook($itemId)`

- [ ] `SyncService.php` - Sincronização
  - [ ] Método `syncAllProducts()` - Sincronizar todos os produtos
  - [ ] Método `syncProductStock($productId)` - Sincronizar estoque
  - [ ] Método `syncProductPrice($productId)` - Sincronizar preço
  - [ ] Método `syncOrders($dateFrom)` - Sincronizar pedidos

#### 3.2 Controllers
- [ ] `AuthController.php`
  - [ ] `redirect()` - Redirecionar para autorização ML
  - [ ] `callback()` - Receber código e gerar token
  - [ ] `disconnect()` - Desconectar conta ML

- [ ] `WebhookController.php`
  - [ ] `handle(Request $request)` - Receber e processar webhooks

- [ ] `ProductController.php`
  - [ ] `publish($productId)` - Publicar produto no ML
  - [ ] `sync($productId)` - Sincronizar produto específico

---

### 🎨 FASE 4: FRONTEND (Livewire Components)

#### 4.1 Componentes Principais

- [ ] **ProductIntegration.php**
  - [ ] Interface para vincular produtos
  - [ ] Botão "Publicar no ML"
  - [ ] Seleção de categoria MLB
  - [ ] Configuração de atributos obrigatórios
  - [ ] Preview do anúncio

- [ ] **OrdersManager.php**
  - [ ] Lista de pedidos do ML
  - [ ] Filtros (status, data, valor)
  - [ ] Botão "Importar Pedido"
  - [ ] Visualização de detalhes
  - [ ] Atualização de status de envio

- [ ] **SyncDashboard.php**
  - [ ] Status de sincronização
  - [ ] Logs de atividades
  - [ ] Estatísticas (vendas, estoque, erros)
  - [ ] Botão "Sincronizar Agora"
  - [ ] Configurações de sync automático

#### 4.2 Telas Blade

- [ ] `resources/views/livewire/mercadolivre/auth.blade.php`
- [ ] `resources/views/livewire/mercadolivre/product-integration.blade.php`
- [ ] `resources/views/livewire/mercadolivre/orders-manager.blade.php`
- [ ] `resources/views/livewire/mercadolivre/sync-dashboard.blade.php`
- [ ] `resources/views/livewire/mercadolivre/settings.blade.php`

---

### ⚙️ FASE 5: JOBS & AUTOMATION

- [ ] **SyncStockJob** - Sincronizar estoque a cada X minutos
- [ ] **SyncPriceJob** - Sincronizar preços quando alterados
- [ ] **ImportOrderJob** - Importar novos pedidos automaticamente
- [ ] **RefreshTokenJob** - Renovar token antes de expirar
- [ ] **CleanupLogsJob** - Limpar logs antigos

#### Configuração no Laravel Scheduler
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new RefreshTokenJob)->hourly();
    $schedule->job(new SyncStockJob)->everyFiveMinutes();
    $schedule->job(new ImportOrderJob)->everyTenMinutes();
    $schedule->job(new CleanupLogsJob)->daily();
}
```

---

### 🧪 FASE 6: TESTES

- [ ] Testes unitários para Services
- [ ] Testes de integração com API (sandbox)
- [ ] Testes de webhook handling
- [ ] Testes de sincronização
- [ ] Testes de autenticação OAuth

---

## 🎯 PRÓXIMO PASSO IMEDIATO

### ✅ AÇÃO 1: Migration para campo `barcode`

**Arquivo:** `database/migrations/2026_02_08_000001_add_barcode_to_products_table.php`

### ✅ AÇÃO 2: Atualizar Model Product

Adicionar `barcode` ao `$fillable` e validações

### ✅ AÇÃO 3: Atualizar Formulários

Adicionar input para código de barras nos formulários de produtos

---

## 📊 FLUXOS DE TRABALHO

### Fluxo 1: Publicação de Produto

```
1. Usuário seleciona produto no sistema
2. Sistema verifica se tem código de barras e campos obrigatórios
3. Usuário seleciona categoria MLB
4. Sistema busca atributos obrigatórios da categoria
5. Usuário preenche atributos
6. Sistema valida dados
7. API cria anúncio no ML
8. Sistema salva ml_item_id e relaciona com produto
9. Sync automático de estoque e preço
```

### Fluxo 2: Sincronização de Estoque

```
1. Produto vendido no sistema interno
2. Estoque é atualizado
3. Event StockUpdated é disparado
4. Job SyncStockJob é enfileirado
5. Job chama ProductService->updateStock()
6. API do ML atualiza quantidade
7. Log de sincronização é registrado
```

### Fluxo 3: Importação de Venda

```
1. Cliente compra no ML
2. Webhook recebe notificação
3. Sistema busca detalhes do pedido na API
4. Valida produto existe no sistema
5. Cria/atualiza cliente se necessário
6. Importa pedido como venda
7. Atualiza estoque automaticamente
8. Envia notificação ao usuário
```

---

## 📈 MÉTRICAS & MONITORAMENTO

### Indicadores Importantes

- Taxa de sucesso de sincronizações
- Tempo médio de resposta da API
- Quantidade de erros por tipo
- Vendas importadas vs. totais
- Produtos publicados vs. ativos

### Alertas Necessários

- Token prestes a expirar
- Erro em sincronização crítica
- Produto sem estoque no ML
- Pedido não importado
- Limite de API próximo

---

## 🔒 SEGURANÇA

### Checklist de Segurança

- [ ] Tokens armazenados criptografados
- [ ] Validação de assinatura de webhooks
- [ ] Rate limiting em endpoints
- [ ] Logs de acesso à API
- [ ] Permissões de usuário para ML
- [ ] HTTPS obrigatório em produção
- [ ] Backup de tokens e logs

---

## 📚 RECURSOS & DOCUMENTAÇÃO

### Links Úteis

- [Documentação Oficial ML API](https://developers.mercadolivre.com.br/pt_br/api-docs-pt-br)
- [Guia de OAuth 2.0](https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao)
- [Gestão de Produtos](https://developers.mercadolivre.com.br/pt_br/lista-de-produtos)
- [Orders API](https://developers.mercadolivre.com.br/pt_br/gerenciar-vendas)
- [Webhooks](https://developers.mercadolivre.com.br/pt_br/notificacoes-via-webhook)

### Bibliotecas Recomendadas

```json
{
    "guzzlehttp/guzzle": "^7.8",
    "laravel/socialite": "^5.12",
    "spatie/laravel-webhook-client": "^3.3"
}
```

---

**Status Atual:** 📋 Aguardando implementação  
**Prioridade:** Alta  
**Estimativa:** 4-6 semanas para implementação completa

---

**Próxima Atualização:** Após criação das migrations e models base
