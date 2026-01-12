# 🔔 Sistema de Notificações - Resumo da Implementação

## ✅ O que foi feito

### 1. **Localização Movida**
- ❌ **Antes**: Top bar separada acima do conteúdo
- ✅ **Agora**: Integrado na **sidebar**, acima do perfil do usuário

### 2. **Sistema Generalizado**
- ❌ **Antes**: Apenas para consórcios (consortium_notifications)
- ✅ **Agora**: Sistema **app-wide** para qualquer módulo

### 3. **Módulos Suportados**
Agora o sistema suporta notificações de:
- 🎯 **Consortium** (Consórcios): sorteios disponíveis, resgates pendentes
- 🛒 **Sale** (Vendas): vendas pendentes, vendas concluídas
- 💰 **Payment** (Pagamentos): pagamentos atrasados, pagamentos recebidos
- 👤 **Client** (Clientes): novos clientes, aniversários
- ⚙️ **System** (Sistema): backups, atualizações, erros

## 📦 Arquivos Modificados/Criados

### Migrações
1. `2026_01_10_120000_create_consortium_notifications_table.php` - Tabela original
2. `2026_01_10_130000_generalize_notifications_system.php` - **NOVA**: Adiciona campos module, entity_type, entity_id
3. `2026_01_10_140000_add_new_notification_types.php` - **NOVA**: Adiciona novos tipos ao ENUM

### Model
- `app/Models/ConsortiumNotification.php` - **MODIFICADO**:
  - Adicionados campos: `module`, `entity_type`, `entity_id`
  - Novos scopes: `ofModule()`, `forEntity()`
  - Método `createGeneric()` para criar notificações de qualquer módulo
  - Novos ícones e cores para cada tipo de notificação

### View (Sidebar)
- `resources/views/components/layouts/app/sidebar.blade.php` - **MODIFICADO**:
  - ❌ Removida top bar
  - ✅ Notificações adicionadas no footer da sidebar
  - ✅ Posicionamento acima do menu do usuário
  - ❌ Removido `pt-16` do main content
  - ✅ Ajustado CSS do compact mode

### Componente Livewire
- `resources/views/livewire/components/consortium-notifications.blade.php` - **MODIFICADO**:
  - Botão full-width para sidebar
  - Dropdown abre para a esquerda (left-0)
  - Título genérico "Notificações" (não "Notificações de Consórcios")
  - Mostra contador e status na sidebar expandida/colapsada

### Comando de Teste
- `app/Console/Commands/TestGenericNotification.php` - **NOVO**:
  - Cria 5 notificações de teste (vendas, pagamentos, clientes)
  - Útil para testar o sistema

### Documentação
- `docs/notification-system-generalized.md` - **NOVA**: Documentação completa do sistema generalizado

## 🚀 Como Usar

### Ver Notificações
As notificações aparecem automaticamente na sidebar, acima do perfil do usuário:
- Badge com contador de não lidas
- Clique para abrir dropdown
- Marcar como lida/não lida
- Deletar notificações
- Botão "Ver todas" / "Marcar todas como lidas"

### Criar Notificação Genérica (Código)

```php
use App\Models\ConsortiumNotification;

// Exemplo: Notificação de venda
ConsortiumNotification::createGeneric(
    module: 'sale',
    type: 'sale_pending',
    userId: auth()->id(),
    title: '🛒 Nova Venda Pendente',
    message: 'Venda #1234 aguardando aprovação.',
    options: [
        'entity_type' => 'Sale',
        'entity_id' => 1234,
        'priority' => 'high',
        'action_url' => route('sales.show', 1234),
        'data' => ['amount' => 1500.00]
    ]
);
```

### Criar Notificações de Teste

```bash
php artisan notification:test-generic
```

### Verificar Notificações de Consórcio

```bash
# Verificar e criar notificações de consórcios
php artisan consortium:check-notifications

# Limpar notificações antigas
php artisan consortium:check-notifications --clean
```

## 🎨 Tipos de Notificação Disponíveis

| Tipo | Ícone | Cor | Módulo |
|------|-------|-----|--------|
| `draw_available` | 🏆 trophy | Purple | Consortium |
| `redemption_pending` | ⚠️ triangle | Amber | Consortium |
| `sale_pending` | 🛒 cart | Orange | Sale |
| `sale_completed` | ✅ check-circle | Green | Sale |
| `payment_overdue` | ⚠️ exclamation | Red | Payment |
| `payment_received` | 💰 cash | Green | Payment |
| `client_new` | 👤 person-plus | Blue | Client |
| `client_birthday` | 🎂 cake | Pink | Client |
| `system_backup` | 💾 | Blue | System |
| `system_update` | 🔄 | Blue | System |
| `system_error` | ❌ | Red | System |

## 📊 Estrutura do Banco de Dados

### Tabela: `consortium_notifications`

```sql
CREATE TABLE `consortium_notifications` (
  `id` bigint PRIMARY KEY,
  `module` varchar(50) DEFAULT 'consortium',         -- NOVO
  `entity_type` varchar(100) NULL,                   -- NOVO
  `entity_id` bigint NULL,                           -- NOVO
  `consortium_id` bigint NULL,                       -- Agora nullable
  `user_id` bigint NOT NULL,
  `related_participant_id` bigint NULL,
  `type` enum(...) NOT NULL,                         -- Expandido
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json NULL,
  `is_read` boolean DEFAULT 0,
  `read_at` timestamp NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `action_url` text NULL,
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp NULL
);

-- Índices para performance
INDEX `idx_module_read_created` (module, is_read, created_at)
INDEX `idx_entity` (entity_type, entity_id)
```

## 🎯 Exemplos de Uso por Módulo

### Vendas (Sale)

```php
// Ao criar uma venda
ConsortiumNotification::createGeneric(
    module: 'sale',
    type: 'sale_pending',
    userId: auth()->id(),
    title: '🛒 Nova Venda Pendente',
    message: "Venda #{$sale->id} para {$client->name}",
    options: [
        'entity_type' => 'Sale',
        'entity_id' => $sale->id,
        'priority' => 'medium',
        'action_url' => route('sales.show', $sale)
    ]
);
```

### Pagamentos (Payment)

```php
// Ao detectar pagamento atrasado
ConsortiumNotification::createGeneric(
    module: 'payment',
    type: 'payment_overdue',
    userId: $payment->user_id,
    title: '⚠️ Pagamento Atrasado',
    message: "Pagamento #{$payment->id} está {$days} dias em atraso",
    options: [
        'entity_type' => 'Payment',
        'entity_id' => $payment->id,
        'priority' => 'high',
        'action_url' => route('payments.show', $payment)
    ]
);
```

### Clientes (Client)

```php
// Ao cadastrar novo cliente
ConsortiumNotification::createGeneric(
    module: 'client',
    type: 'client_new',
    userId: auth()->id(),
    title: '👤 Novo Cliente Cadastrado',
    message: "Cliente {$client->name} cadastrado com sucesso",
    options: [
        'entity_type' => 'Client',
        'entity_id' => $client->id,
        'priority' => 'low',
        'action_url' => route('clients.show', $client)
    ]
);
```

## 🔄 Migração de Dados Existentes

Se você já tinha notificações antigas apenas de consórcio, elas continuarão funcionando pois:
- O campo `module` tem valor padrão `'consortium'`
- O campo `consortium_id` agora é nullable mas ainda existe
- Os tipos antigos (`draw_available`, `redemption_pending`) continuam no ENUM

## 📱 Interface na Sidebar

### Estado Colapsado (Sidebar Minimizada)
- Mostra apenas ícone do sino
- Badge com contador de não lidas

### Estado Expandido (Sidebar Normal)
- Mostra ícone + "Notificações"
- Contador de não lidas em texto
- Botão com dropdown

### Dropdown
- Header com título e botão refresh
- Lista de notificações (últimas 5 ou todas)
- Botões de ação (marcar lida, deletar)
- Botão "Marcar todas como lidas"
- Botão "Ver todas" / "Mostrar menos"
- Scroll automático para muitas notificações

## ⚙️ Configuração no Scheduler

Para automatizar as verificações:

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Verificar consórcios a cada hora
    $schedule->command('consortium:check-notifications')
        ->hourly();
        
    // Limpar notificações antigas diariamente
    $schedule->command('consortium:check-notifications --clean')
        ->daily();
}
```

## 🧪 Testes

```bash
# Criar notificações de teste
php artisan notification:test-generic

# Ver quantidade de notificações
php artisan tinker
>>> ConsortiumNotification::count()

# Ver não lidas do usuário 1
>>> ConsortiumNotification::unread()->forUser(1)->count()

# Ver por módulo
>>> ConsortiumNotification::ofModule('sale')->count()
```

## 📈 Estatísticas

```php
// No código
$stats = [
    'total' => ConsortiumNotification::count(),
    'unread' => ConsortiumNotification::unread()->count(),
    'by_module' => ConsortiumNotification::groupBy('module')
        ->selectRaw('module, count(*) as count')
        ->pluck('count', 'module'),
];
```

## 🎉 Próximos Passos

Para expandir o sistema, você pode:

1. **Adicionar novos tipos**: Edite a migração para adicionar ao ENUM
2. **Criar notificações automáticas**: Use Events/Listeners do Laravel
3. **Notificações em tempo real**: Integrar com Pusher ou Laravel Echo
4. **Email/WhatsApp**: Enviar notificações também por outros canais
5. **Central de notificações**: Criar uma página dedicada com filtros avançados

## 📚 Documentação Completa

Veja [docs/notification-system-generalized.md](docs/notification-system-generalized.md) para documentação detalhada.

---

**Sistema implementado em**: 10/01/2026  
**Versão**: 2.0 (Sistema Generalizado App-Wide)
