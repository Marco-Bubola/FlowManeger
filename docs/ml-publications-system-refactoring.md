# Sistema de Publicações Mercado Livre - Refatoração Completa

## 📋 Visão Geral

Sistema refatorado para suportar publicações com múltiplos produtos (kits), sincronização automática de estoque e auditoria completa de movimentações.

## 🎯 Objetivos Alcançados

### ✅ Funcionalidades Implementadas

1. **Múltiplos Produtos por Publicação (Kits)**
   - Uma publicação pode conter vários produtos
   - Cada produto tem um multiplicador de quantidade
   - Exemplo: Kit com 2 Shampoos + 1 Condicionador

2. **Mesmo Produto em Múltiplas Publicações**
   - Produto pode estar em várias publicações simultaneamente
   - Mudança de estoque atualiza TODAS as publicações automaticamente

3. **Product_code Awareness**
   - Produtos com mesmo `product_code` são tratados como variantes
   - Sincronização automática entre variantes

4. **Sincronização Automática de Estoque**
   - **Venda no ML:** Webhook → Dedução automática → Sync para ML
   - **Mudança manual:** Observer detecta → Recalcula publicações → Async sync
   - **Importação Excel:** Detecta origem → Registra logs → Sync cascata
   - **Venda interna:** System sale → Deduz estoque → Atualiza ML

5. **Auditoria Completa (Security Logs)**
   - Todas operações registradas em `ml_stock_logs`
   - Transaction ID para operações atômicas
   - Flag `rolled_back` para reverter operações
   - Detecção de conflitos (race conditions)

6. **Edição de Publicações**
   - Adicionar/remover produtos de publicações existentes
   - Atualizar quantidade de produtos no kit
   - Sincronização automática após mudanças

---

## 🗄️ Arquitetura do Banco de Dados

### Tabela: `ml_publications`

Substitui o relacionamento 1:1 de `mercadolivre_products`.

```sql
- id (bigint)
- ml_item_id (varchar 50, unique) -- ID no Mercado Livre
- ml_category_id (varchar 50)
- ml_permalink (varchar 500)
- title (varchar 255)
- description (text)
- price (decimal 10,2)
- available_quantity (int) -- Calculado automaticamente
- publication_type (enum: 'simple', 'kit')
- listing_type (varchar 50, default: 'gold_special')
- condition (enum: 'new', 'used')
- free_shipping (boolean)
- local_pickup (boolean)
- status (enum: 'active', 'paused', 'closed', 'under_review')
- sync_status (enum: 'synced', 'pending', 'error')
- last_sync_at (timestamp)
- error_message (text)
- ml_attributes (json) -- Atributos específicos da categoria ML
- pictures (json) -- URLs das imagens
- user_id (bigint)
- timestamps
```

**Índices:**
- `ml_item_id` (unique)
- `status`, `sync_status`, `publication_type`
- `[user_id, status]` (composto)

---

### Tabela: `ml_publication_products` (Pivot)

Relacionamento N:N entre publicações e produtos.

```sql
- id (bigint)
- ml_publication_id (FK → ml_publications, CASCADE)
- product_id (int unsigned, index)
- quantity (int, default: 1) -- Multiplicador por venda
- unit_cost (decimal 10,2) -- Snapshot do custo
- sort_order (int, default: 0)
- timestamps
```

**Constraints:**
- Unique: `[ml_publication_id, product_id]`

**Exemplo:** Kit "Combo Shampoo + Condicionador"
```
publication_id: 123
├─ product_id: 10 (Shampoo) → quantity: 2
└─ product_id: 15 (Condicionador) → quantity: 1
```

Quando vender 1 kit, deduz:
- 2 unidades do Shampoo (produto 10)
- 1 unidade do Condicionador (produto 15)

---

### Tabela: `ml_stock_logs`

Auditoria completa de movimentações de estoque.

```sql
- id (bigint)
- product_id (int unsigned)
- ml_publication_id (bigint, nullable)
- operation_type (enum):
    • ml_sale          -- Venda pelo Mercado Livre
    • manual_update    -- Edição manual no sistema
    • import_excel     -- Importação de planilha
    • internal_sale    -- Venda pelo sistema interno
    • sync_to_ml       -- Sincronização para ML
    • adjustment       -- Ajuste de inventário
    • return           -- Devolução de produto
- quantity_before (int)
- quantity_after (int)
- quantity_change (int) -- Positivo ou negativo
- source (varchar 100) -- Origem: ProductObserver, WebhookService, etc
- ml_order_id (varchar 50)
- notes (text)
- transaction_id (varchar 36) -- UUID para agrupar operações
- rolled_back (boolean, default: false)
- user_id (bigint, nullable)
- created_at (timestamp)
```

**Índices:**
- `product_id`, `ml_publication_id`, `operation_type`
- `ml_order_id`, `transaction_id`, `created_at`
- `[product_id, created_at]` (composto)

---

## 🔄 Fluxos de Sincronização

### 1. Venda no Mercado Livre

```
Webhook ML (pedido pago)
    ↓
WebhookService::handleOrderWebhook()
    ↓
MlStockSyncService::processMercadoLivreSale()
    ↓
MlPublication::deductStock()
    ├─ DB Transaction BEGIN
    ├─ Loop produtos:
    │   ├─ Calcula: quantity_to_deduct = pivot.quantity * sale_quantity
    │   ├─ Product::update(['stock_quantity' => new_stock])
    │   └─ MlStockLog::create([...transaction_id...])
    ├─ syncQuantityToMl() → Atualiza available_quantity
    ├─ DB Transaction COMMIT
    └─ On Error: ROLLBACK + marca logs como rolled_back
```

### 2. Mudança Manual de Estoque

```
Usuário edita Product::stock_quantity
    ↓
ProductObserver::updated()
    ├─ Detecta wasChanged('stock_quantity')
    ├─ Cria MlStockLog (operation_type: manual_update)
    └─ syncPublications()
        ├─ Busca publicações por Product ID
        ├─ Busca publicações por Product Code
        ├─ Merge e deduplica
        └─ Para cada publicação:
            ├─ Recalcula available_quantity
            ├─ Cria log (operation_type: sync_to_ml)
            └─ Dispatch SyncPublicationToMercadoLivre Job
```

### 3. Job Assíncrono

```
SyncPublicationToMercadoLivre::handle()
    ↓
MlStockSyncService::syncQuantityToMercadoLivre()
    ├─ Calcula quantidade disponível (min entre todos produtos)
    ├─ Chama ML API: PUT /items/{ml_item_id}
    │   Body: { available_quantity: X }
    ├─ Atualiza publication:
    │   ├─ sync_status: 'synced'
    │   └─ last_sync_at: now()
    └─ On Error:
        ├─ sync_status: 'error'
        ├─ error_message: ...
        └─ Retry: 3x com backoff [60s, 5min, 15min]
```

---

## 💻 Modelos e Métodos Principais

### MlPublication

**Métodos de Cálculo:**
```php
// Calcula quantidade disponível baseado no estoque dos produtos
$publication->calculateAvailableQuantity();
// Retorna: min(floor(product_stock / pivot.quantity)) para cada produto
```

**Dedução de Estoque (Atômico):**
```php
$result = $publication->deductStock($quantity, $mlOrderId);
// Retorna: ['success' => true/false, 'logs' => [], 'message' => '']
```

**Gerenciamento de Produtos:**
```php
$publication->addProduct($productId, $quantity = 1, $unitCost = null);
$publication->removeProduct($productId);
$publication->updateProductQuantity($productId, $newQuantity);
```

**Scopes:**
```php
MlPublication::active()->get();                    // status = 'active'
MlPublication::kits()->get();                      // publication_type = 'kit'
MlPublication::withProduct($productId)->get();     // Contém produto X
MlPublication::withProductCode($code)->get();      // Contém produtos com code X
MlPublication::withErrors()->get();                // sync_status = 'error'
MlPublication::pending()->get();                   // sync_status = 'pending'
```

---

### MlStockLog

**Criar Log:**
```php
MlStockLog::logStockChange([
    'product_id' => $product->id,
    'ml_publication_id' => $publication->id,
    'operation_type' => 'ml_sale',
    'quantity_before' => $oldStock,
    'quantity_after' => $newStock,
    'quantity_change' => $change,
    'source' => 'WebhookService',
    'ml_order_id' => $orderId,
    'transaction_id' => $uuid,
    'user_id' => auth()->id(),
    'notes' => 'Venda Mercado Livre processada'
]);
```

**Detectar Conflitos:**
```php
$conflicts = MlStockLog::findConflicts($productId, $minutesWindow = 5);
// Retorna logs de diferentes transações no mesmo produto
```

**Scopes:**
```php
MlStockLog::forProduct($productId)->get();
MlStockLog::mlSales()->get();                    // operation_type = 'ml_sale'
MlStockLog::rolledBack()->get();                 // rolled_back = true
MlStockLog::forTransaction($uuid)->get();        // transaction_id = X
MlStockLog::betweenDates($start, $end)->get();
```

---

### Product (Atualizado)

**Novos Métodos:**
```php
// Relacionamento N:N com publicações
$product->mlPublications()->get();

// Verifica se está em alguma publicação ativa
if ($product->hasActivePublications()) {
    // Não permitir deletar, por exemplo
}

// Retorna publicações ativas
$publications = $product->getActivePublications();
```

---

## 🔧 Services

### MlStockSyncService

**Sincronizar para ML:**
```php
$service = new MlStockSyncService();
$result = $service->syncQuantityToMercadoLivre($publication);
// Retorna: ['success' => bool, 'message' => '', 'data' => []]
```

**Processar Venda ML:**
```php
$result = $service->processMercadoLivreSale($mlOrderId, $mlItemId, $quantity);
// Retorna: ['success' => bool, 'publication' => MlPublication, 'logs' => []]
```

**Sincronização em Lote (Cron):**
```php
$result = $service->syncAllPending();
// Retorna: ['total' => X, 'success' => Y, 'failed' => Z, 'errors' => [...]]
```

**Auditoria e Correção:**
```php
$result = $service->auditAndFix($publication);
// Busca quantidade no ML, compara com local, auto-corrige divergência
```

---

## 🚀 Exemplos de Uso

### Criar Publicação Simples

```php
$publication = MlPublication::create([
    'ml_item_id' => 'MLB123456789',
    'ml_category_id' => 'MLB1051',
    'title' => 'Shampoo Anticaspa 400ml',
    'price' => 29.90,
    'publication_type' => 'simple',
    'listing_type' => 'gold_special',
    'condition' => 'new',
    'status' => 'active',
    'user_id' => auth()->id(),
]);

$publication->addProduct($product->id, 1, $product->price);
```

### Criar Kit

```php
$publication = MlPublication::create([
    'ml_item_id' => 'MLB987654321',
    'title' => 'Kit Completo: 2 Shampoos + 1 Condicionador',
    'price' => 79.90,
    'publication_type' => 'kit',
    'status' => 'active',
]);

$publication->addProduct($shampoo->id, 2, $shampoo->price);        // 2 unidades
$publication->addProduct($condicionador->id, 1, $condicionador->price); // 1 unidade
```

### Processar Venda Manual

```php
$syncService = new MlStockSyncService();
$result = $syncService->processMercadoLivreSale(
    '12345678900',           // ML Order ID
    'MLB123456789',          // ML Item ID
    2                        // Quantidade vendida
);

if ($result['success']) {
    // Estoque deduzido e sincronizado
    $logs = $result['logs']; // Logs criados
}
```

### Consultar Logs de Produto

```php
$logs = MlStockLog::forProduct($product->id)
    ->with(['publication', 'user'])
    ->orderBy('created_at', 'desc')
    ->paginate(20);

foreach ($logs as $log) {
    echo "{$log->created_at}: {$log->getOperationDescription()} - ";
    echo "Estoque: {$log->quantity_before} → {$log->quantity_after}";
    echo " (Transaction: {$log->transaction_id})";
}
```

---

## 🛠️ Próximos Passos

### ⏳ Pendente - UI (Livewire Components)

1. **Atualizar PublishProduct Component**
   - Adicionar seletor de múltiplos produtos
   - Campos para definir quantidade de cada produto
   - Preview de estoque disponível calculado
   - Radio button "Publicação Simple" vs "Kit"

2. **Criar EditPublication Component**
   - Página: `/mercadolivre/publications/{id}/edit`
   - Listar produtos atuais do kit
   - Adicionar/remover produtos
   - Atualizar quantidade de produtos
   - Preview em tempo real

3. **Dashboard de Publicações**
   - Listar todas publicações com status
   - Filtros: active, paused, com erro, kits only
   - Indicador de sincronização (synced/pending/error)
   - Botão "Forçar Sync"

### ⏳ Pendente - Melhorias

4. **Command Artisan: Sync All**
   ```bash
   php artisan ml:sync-publications
   php artisan ml:audit-and-fix
   ```

5. **Command Artisan: Generate Report**
   ```bash
   php artisan ml:stock-report --product=123
   php artisan ml:stock-report --publication=456
   php artisan ml:conflict-report
   ```

6. **Notificações**
   - Alerta quando sync_status = 'error'
   - Email diário com resumo de conflitos
   - Notificação em tempo real de vendas ML

---

## 📊 Cenários de Teste

### Teste 1: Venda de Kit

1. Criar kit com 2 produtos
2. Produto A: estoque 10, quantity 2
3. Produto B: estoque 5, quantity 1
4. Quantidade disponível: min(10/2, 5/1) = min(5, 5) = 5
5. Simular venda de 1 kit via webhook
6. Verificar dedução: A=8, B=4
7. Verificar logs com mesmo transaction_id

### Teste 2: Produto em Múltiplas Publicações

1. Criar produto X com estoque 20
2. Criar publicação simples P1 com X
3. Criar kit P2 com X (quantity 2) + Y
4. Alterar estoque de X para 15
5. Verificar P1: available_quantity = 15
6. Verificar P2: available_quantity = min(15/2, Y) = 7
7. Verificar 2 logs de sync_to_ml criados

### Teste 3: Product_code Sync

1. Produto A (code: SHAMP001) estoque 10
2. Produto B (code: SHAMP001) estoque 8 (variante)
3. Publicação P1 com Produto A
4. Alterar estoque de B para 12
5. Verificar se P1 foi sincronizada (porque mesmo code)

### Teste 4: Race Condition

1. Iniciar venda ML (deduz estoque)
2. Simultaneamente editar estoque manualmente
3. Executar: `MlStockLog::findConflicts($productId, 1)`
4. Verificar se detectou conflito
5. Analisar transaction_ids diferentes

### Teste 5: Rollback

1. Simular erro na API ML durante deductStock()
2. Verificar se logs foram marcados com rolled_back = true
3. Verificar se estoque foi revertido

---

## 🗂️ Estrutura de Arquivos Criados

```
database/migrations/
├─ 2026_02_09_000001_create_ml_publications_table.php
├─ 2026_02_09_000002_create_ml_publication_products_table.php
└─ 2026_02_09_000003_create_ml_stock_logs_table.php

app/Models/
├─ MlPublication.php           (330 linhas)
└─ MlStockLog.php              (170 linhas)

app/Observers/
└─ ProductObserver.php         (140 linhas)

app/Services/MercadoLivre/
└─ MlStockSyncService.php      (210 linhas)

app/Jobs/
├─ SyncPublicationToMercadoLivre.php    (Queue job)
└─ ProcessMercadoLivreWebhook.php       (Queue job)
```

**Arquivos Atualizados:**
- `app/Models/Product.php` → Adicionado mlPublications() relationship
- `app/Providers/AppServiceProvider.php` → Registrado ProductObserver
- `app/Services/MercadoLivre/WebhookService.php` → Integrado com MlStockSyncService

---

## 📝 Comandos para Executar

```bash
# 1. Rodar migrations
php artisan migrate

# 2. Verificar se observer está registrado
php artisan tinker
>>> app()->make(\App\Observers\ProductObserver::class)

# 3. Testar criação de publicação
php artisan tinker
>>> $pub = \App\Models\MlPublication::create([...])
>>> $pub->addProduct(1, 2)
>>> $pub->calculateAvailableQuantity()

# 4. Processar webhooks pendentes (se houver)
php artisan queue:work --tries=3

# 5. Monitorar logs
tail -f storage/logs/laravel.log | grep -i "ml"
```

---

## ⚠️ Observações Importantes

1. **Backward Compatibility:** Sistema mantém compatibilidade com `mercadolivre_products` (1:1) para publicações antigas

2. **Queue Configuration:** Configurar `QUEUE_CONNECTION=database` ou `redis` no `.env` para processar jobs assíncronos

3. **Cron Job Recomendado:** Adicionar comando para sync periódico
   ```bash
   # crontab
   */15 * * * * php /path/to/artisan ml:sync-all-pending
   ```

4. **ML API Rate Limits:** 
   - 10.000 requisições/dia para sellers Gold
   - Job usa backoff para retry inteligente

5. **Transaction Isolation:** `deductStock()` usa `DB::transaction()` para garantir atomicidade

---

## 🐛 Troubleshooting

### Problema: Publicação não sincroniza após mudança de estoque

**Solução:**
```bash
# Verificar se observer está registrado
php artisan tinker
>>> \App\Models\Product::getObservableEvents()

# Forçar sync manual
>>> $publication = \App\Models\MlPublication::find(1);
>>> $publication->syncQuantityToMl();
>>> \App\Jobs\SyncPublicationToMercadoLivre::dispatch($publication);
```

### Problema: Logs com rolled_back = true

**Causa:** Erro durante deductStock() (API ML offline, timeout, etc)

**Solução:**
```php
// Reprocessar transação
$transactionId = 'uuid-da-transação';
$logs = MlStockLog::forTransaction($transactionId)->get();

// Reverter manualmente
foreach ($logs as $log) {
    $log->product->update(['stock_quantity' => $log->quantity_before]);
}

// Tentar novamente
$publication->deductStock($quantity, $mlOrderId);
```

### Problema: Conflito de estoque (race condition)

**Detecção:**
```php
$conflicts = MlStockLog::findConflicts($productId, 5); // Últimos 5 minutos

foreach ($conflicts as $conflict) {
    echo "Transaction {$conflict->transaction_id} at {$conflict->created_at}";
}
```

**Correção:**
```php
$service = new MlStockSyncService();
$result = $service->auditAndFix($publication);
// Compara com ML API e corrige divergência
```

---

## 📚 Referências

- **Mercado Livre API:** https://developers.mercadolivre.com.br/
- **Webhooks ML:** https://developers.mercadolivre.com.br/pt_br/webhooks
- **Laravel Observers:** https://laravel.com/docs/eloquent#observers
- **Laravel Queue Jobs:** https://laravel.com/docs/queues

---

**Última Atualização:** 2026-02-09  
**Versão:** 1.0.0  
**Status:** Backend completo ✅ | UI pendente ⏳
