# Sistema de Notificações - FlowManager

## 📋 Visão Geral

Sistema de notificações genérico e modular para toda a aplicação FlowManager. Suporta múltiplos módulos (consórcios, vendas, pagamentos, clientes, etc) com notificações em tempo real integradas na sidebar.

## 🏗️ Arquitetura

### Localização na Interface
- **Sidebar**: Componente de notificações integrado acima do perfil do usuário
- **Layout**: Full-width button com contador de não lidas e dropdown expansível
- **Posicionamento**: Fixo na sidebar esquerda, sempre visível

### Estrutura do Banco de Dados

**Tabela**: `consortium_notifications`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | ID único |
| `module` | varchar(50) | Módulo da notificação (consortium, sale, payment, client, etc) |
| `entity_type` | varchar(100) | Tipo da entidade relacionada (Consortium, Sale, Payment, etc) |
| `entity_id` | bigint | ID da entidade relacionada |
| `consortium_id` | bigint (nullable) | ID do consórcio (para compatibilidade) |
| `user_id` | bigint | Usuário que receberá a notificação |
| `related_participant_id` | bigint (nullable) | ID do participante relacionado |
| `type` | varchar(50) | Tipo específico da notificação |
| `title` | varchar(255) | Título da notificação |
| `message` | text | Mensagem detalhada |
| `data` | json | Dados adicionais |
| `is_read` | boolean | Status de leitura |
| `read_at` | timestamp | Data/hora da leitura |
| `priority` | enum | Prioridade (low, medium, high) |
| `action_url` | text | URL de ação |

**Índices**:
- `idx_module_read_created`: (module, is_read, created_at)
- `idx_entity`: (entity_type, entity_id)

## 📦 Módulos Suportados

### 1. Consortium (Consórcios)
Tipos de notificação:
- `draw_available`: Sorteio disponível para realização
- `redemption_pending`: Cliente contemplado com resgate pendente

### 2. Sale (Vendas) - Expansível
Tipos de notificação:
- `sale_pending`: Venda pendente de aprovação
- `sale_completed`: Venda concluída com sucesso

### 3. Payment (Pagamentos) - Expansível
Tipos de notificação:
- `payment_overdue`: Pagamento em atraso
- `payment_received`: Pagamento recebido

### 4. Client (Clientes) - Expansível
Tipos de notificação:
- `client_new`: Novo cliente cadastrado
- `client_birthday`: Aniversário de cliente

## 🎨 Ícones e Cores por Tipo

| Tipo | Ícone | Cor |
|------|-------|-----|
| `draw_available` | `bi-trophy-fill` | Purple |
| `redemption_pending` | `bi-exclamation-triangle-fill` | Amber |
| `sale_pending` | `bi-cart-fill` | Orange |
| `sale_completed` | `bi-check-circle-fill` | Green |
| `payment_overdue` | `bi-exclamation-circle-fill` | Red |
| `payment_received` | `bi-cash-coin` | Green |
| `client_new` | `bi-person-plus-fill` | Blue |
| `client_birthday` | `bi-cake-fill` | Pink |

## 🔧 Uso do Model

### Criar Notificação Genérica

```php
use App\Models\ConsortiumNotification;

// Método genérico para qualquer módulo
ConsortiumNotification::createGeneric(
    module: 'sale',
    type: 'sale_pending',
    userId: 1,
    title: '🛒 Nova Venda Pendente',
    message: 'Venda #1234 aguardando aprovação.',
    options: [
        'entity_type' => 'Sale',
        'entity_id' => 1234,
        'priority' => 'high',
        'action_url' => route('sales.show', 1234),
        'data' => [
            'amount' => 1500.00,
            'client_name' => 'João Silva'
        ]
    ]
);
```

### Criar Notificação de Consórcio (Métodos Específicos)

```php
// Sorteio disponível
ConsortiumNotification::createDrawAvailable($consortium);

// Resgate pendente
ConsortiumNotification::createRedemptionPending($participant);
```

### Buscar Notificações

```php
// Todas não lidas do usuário
$notifications = ConsortiumNotification::unread()
    ->forUser(auth()->id())
    ->latest()
    ->get();

// Por módulo específico
$consortiumNotifications = ConsortiumNotification::ofModule('consortium')
    ->forUser(auth()->id())
    ->get();

// Por entidade específica
$saleNotifications = ConsortiumNotification::forEntity('Sale', 1234)
    ->get();

// Alta prioridade
$urgent = ConsortiumNotification::highPriority()
    ->unread()
    ->forUser(auth()->id())
    ->get();
```

### Marcar como Lida/Não Lida

```php
// Individual
$notification->markAsRead();
$notification->markAsUnread();

// Todas do usuário
ConsortiumNotification::markAllAsReadForUser(auth()->id());
```

## 🎯 Componente Livewire

### Localização
```
app/Livewire/Components/ConsortiumNotifications.php
resources/views/livewire/components/consortium-notifications.blade.php
```

### Propriedades Públicas
- `$showDropdown`: Controla exibição do dropdown
- `$showAll`: Mostrar todas ou apenas 5 mais recentes
- `$notifications`: Collection de notificações
- `$unreadCount`: Contador de não lidas

### Métodos Públicos
```php
toggleDropdown()          // Abre/fecha dropdown
refreshNotifications()    // Atualiza lista
markAsRead($id)          // Marca uma como lida
markAsUnread($id)        // Marca uma como não lida
markAllAsRead()          // Marca todas como lidas
deleteNotification($id)  // Deleta notificação
toggleShowAll()          // Alterna visualização completa/resumida
```

### Uso no Blade
```blade
@livewire('components.consortium-notifications')
```

## 🤖 Command Artisan

### Verificar e Criar Notificações
```bash
# Verificar todos os consórcios
php artisan consortium:check-notifications

# Limpar notificações antigas (>30 dias)
php artisan consortium:check-notifications --clean

# Verificar consórcio específico
php artisan consortium:check-notifications --consortium=1
```

### Agendar no Schedule
```php
// app/Console/Kernel.php
$schedule->command('consortium:check-notifications')
    ->hourly();

$schedule->command('consortium:check-notifications --clean')
    ->daily();
```

## 📊 Service Layer

### ConsortiumNotificationService

```php
use App\Services\ConsortiumNotificationService;

$service = new ConsortiumNotificationService();

// Verificar e criar notificações
$stats = $service->checkAndCreateNotifications();
// Retorna: ['draw_available' => 2, 'redemption_pending' => 5, 'total' => 7]

// Limpar notificações antigas
$deleted = $service->cleanOldNotifications(30); // dias

// Obter estatísticas
$stats = $service->getStats($userId);

// Obter notificações recentes
$recent = $service->getRecentNotifications($userId, 10);
```

## 🎨 Integração na Sidebar

O componente está integrado no footer da sidebar, acima do perfil do usuário:

```blade
<!-- Notificações -->
<div class="mb-3">
    @livewire('components.consortium-notifications')
</div>

<!-- User Profile -->
<div class="mb-3">
    <!-- Menu do usuário -->
</div>
```

### Features da UI
- ✅ Contador de não lidas com badge animado
- ✅ Dropdown expansível com scroll
- ✅ Ações rápidas (marcar como lida, deletar)
- ✅ Botão "Marcar todas como lidas"
- ✅ Toggle "Ver todas" / "Mostrar menos"
- ✅ Design responsivo e dark mode
- ✅ Ícones e cores por tipo de notificação
- ✅ Indicador visual de não lidas
- ✅ Badges de prioridade alta (URGENTE)
- ✅ Links diretos para ações

## 📝 Migrações

### Migração Original
```
database/migrations/2026_01_10_120000_create_consortium_notifications_table.php
```

### Migração de Generalização
```
database/migrations/2026_01_10_130000_generalize_notifications_system.php
```

Para aplicar:
```bash
php artisan migrate
```

## 🚀 Exemplos de Uso para Outros Módulos

### Exemplo: Notificação de Venda

```php
// No controller de vendas
use App\Models\ConsortiumNotification;

// Quando uma venda for criada
ConsortiumNotification::createGeneric(
    module: 'sale',
    type: 'sale_pending',
    userId: auth()->id(),
    title: '🛒 Nova Venda Pendente',
    message: "Venda #{$sale->id} criada para o cliente {$sale->client->name}. Aguardando aprovação.",
    options: [
        'entity_type' => 'Sale',
        'entity_id' => $sale->id,
        'priority' => 'medium',
        'action_url' => route('sales.show', $sale),
        'data' => [
            'sale_id' => $sale->id,
            'amount' => $sale->total_amount,
            'client_name' => $sale->client->name,
        ]
    ]
);
```

### Exemplo: Notificação de Pagamento Atrasado

```php
use App\Models\ConsortiumNotification;

// Em um command/job que verifica pagamentos
$overduePayments = Payment::overdue()->get();

foreach ($overduePayments as $payment) {
    ConsortiumNotification::createGeneric(
        module: 'payment',
        type: 'payment_overdue',
        userId: $payment->user_id,
        title: '⚠️ Pagamento Atrasado',
        message: "Pagamento #{$payment->id} está {$payment->days_overdue} dias em atraso.",
        options: [
            'entity_type' => 'Payment',
            'entity_id' => $payment->id,
            'priority' => $payment->days_overdue > 15 ? 'high' : 'medium',
            'action_url' => route('payments.show', $payment),
            'data' => [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'due_date' => $payment->due_date,
                'days_overdue' => $payment->days_overdue,
            ]
        ]
    );
}
```

## 🔍 Debugging

### Verificar notificações no tinker
```bash
php artisan tinker
```

```php
// Ver todas as notificações
ConsortiumNotification::all();

// Ver não lidas do usuário 1
ConsortiumNotification::unread()->forUser(1)->get();

// Ver por módulo
ConsortiumNotification::ofModule('consortium')->get();

// Criar notificação de teste
ConsortiumNotification::createGeneric(
    'test',
    'test_notification',
    1,
    'Teste',
    'Mensagem de teste'
);
```

## 📈 Métricas e Monitoramento

```php
// Total de notificações não lidas
$unreadCount = ConsortiumNotification::unreadCountForUser(auth()->id());

// Por módulo
$consortiumUnread = ConsortiumNotification::ofModule('consortium')
    ->unread()
    ->forUser(auth()->id())
    ->count();

// Estatísticas gerais
$stats = [
    'total' => ConsortiumNotification::count(),
    'unread' => ConsortiumNotification::unread()->count(),
    'high_priority' => ConsortiumNotification::highPriority()->unread()->count(),
    'recent' => ConsortiumNotification::recent()->count(),
];
```

## 🎯 Próximas Expansões

### Módulos Planejados
1. **Sales (Vendas)**
   - Venda criada
   - Venda aprovada
   - Venda cancelada
   - Meta atingida

2. **Payments (Pagamentos)**
   - Pagamento recebido
   - Pagamento atrasado
   - Pagamento agendado próximo

3. **Clients (Clientes)**
   - Novo cliente cadastrado
   - Aniversário de cliente
   - Cliente inativo (sem compras há X dias)

4. **System (Sistema)**
   - Backup realizado
   - Atualização disponível
   - Erro crítico

### Features Futuras
- [ ] Notificações push (browser)
- [ ] Notificações por email
- [ ] Notificações por WhatsApp
- [ ] Central de notificações (página dedicada)
- [ ] Filtros avançados (por módulo, tipo, prioridade)
- [ ] Configurações de preferências (quais tipos receber)
- [ ] Notificações agrupadas
- [ ] Som ao receber nova notificação

## 📚 Referências

- **Model**: `app/Models/ConsortiumNotification.php`
- **Service**: `app/Services/ConsortiumNotificationService.php`
- **Livewire**: `app/Livewire/Components/ConsortiumNotifications.php`
- **View**: `resources/views/livewire/components/consortium-notifications.blade.php`
- **Sidebar**: `resources/views/components/layouts/app/sidebar.blade.php`
- **Command**: `app/Console/Commands/CheckConsortiumNotifications.php`

---

**Última atualização**: 10/01/2026
**Versão**: 2.0 (Sistema Generalizado)
