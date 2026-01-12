# 🔔 Sistema de Notificações de Consórcios

## 📋 Visão Geral

Sistema completo de notificações para gerenciar avisos sobre:
- **Sorteios Disponíveis**: Consórcios prontos para realizar sorteio
- **Resgates Pendentes**: Contemplados que ainda não realizaram o resgate

---

## 🗂️ Estrutura do Sistema

### 1. **Database**

#### Migration: `2026_01_10_120000_create_consortium_notifications_table.php`
```sql
- id
- consortium_id (FK → consortiums)
- user_id (FK → users - dono do consórcio)
- related_participant_id (FK → consortium_participants - opcional)
- type (enum: draw_available, redemption_pending)
- title
- message
- data (json - dados extras)
- is_read (boolean)
- read_at (timestamp)
- priority (enum: low, medium, high)
- action_url (link para ação rápida)
- timestamps, soft_deletes
```

---

### 2. **Model: ConsortiumNotification**

**Localização**: `app/Models/ConsortiumNotification.php`

#### Relationships:
```php
- consortium(): BelongsTo
- user(): BelongsTo
- participant(): BelongsTo
```

#### Scopes:
```php
- unread(): Notificações não lidas
- read(): Notificações lidas
- ofType($type): Filtrar por tipo
- highPriority(): Apenas prioridade alta
- recent(): Últimos 7 dias
- forUser($userId): De um usuário específico
```

#### Static Methods:
```php
// Criar notificação de sorteio disponível
ConsortiumNotification::createDrawAvailable($consortium);

// Criar notificação de resgate pendente
ConsortiumNotification::createRedemptionPending($participant);

// Contar não lidas
ConsortiumNotification::unreadCountForUser($userId);

// Marcar todas como lidas
ConsortiumNotification::markAllAsReadForUser($userId);
```

#### Instance Methods:
```php
$notification->markAsRead();
$notification->markAsUnread();
```

#### Accessors:
```php
$notification->icon          // bi-trophy-fill, bi-exclamation-triangle-fill
$notification->color         // red, purple, amber, blue
$notification->getTypeLabel() // "Sorteio Disponível", etc
$notification->time_ago      // "há 2 horas"
```

---

### 3. **Service: ConsortiumNotificationService**

**Localização**: `app/Services/ConsortiumNotificationService.php`

#### Métodos Principais:

```php
// Verificar todos os consórcios e criar notificações
$stats = $service->checkAndCreateNotifications();
// Retorna: ['draw_available' => 2, 'redemption_pending' => 5, 'total' => 7]

// Limpar notificações antigas
$service->cleanOldNotifications();
// Remove: lidas > 90 dias, não lidas > 180 dias

// Estatísticas de um usuário
$stats = $service->getStats($userId);

// Notificações recentes
$notifications = $service->getRecentNotifications($userId, $limit);

// Disparar para consórcio específico
$count = $service->triggerForConsortium($consortium);
```

#### Lógica de Verificação:

**Sorteios Disponíveis:**
- Consórcio ativo com modo "draw"
- Método `canPerformDraw()` retorna true
- Sem notificação nas últimas 24h

**Resgates Pendentes:**
- Participante contemplado
- Resgate = "pending"
- Contemplação há 7+ dias
- Notifica em: 7, 15, 30 dias e depois a cada 30 dias
- Sem notificação no último dia

---

### 4. **Command: CheckConsortiumNotifications**

**Localização**: `app/Console/Commands/CheckConsortiumNotifications.php`

#### Uso:

```bash
# Verificar e criar notificações
php artisan consortium:check-notifications

# Limpar notificações antigas
php artisan consortium:check-notifications --clean

# Verificar consórcio específico
php artisan consortium:check-notifications --consortium=21
```

#### Agendar no Cron:

Adicione ao `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Verificar notificações a cada 6 horas
    $schedule->command('consortium:check-notifications')
             ->everySixHours()
             ->withoutOverlapping();

    // Limpar notificações antigas toda segunda às 3h
    $schedule->command('consortium:check-notifications --clean')
             ->weekly()
             ->mondays()
             ->at('03:00');
}
```

**Configurar Cron no servidor:**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

### 5. **Componente Livewire: ConsortiumNotifications**

**Localização**: 
- `app/Livewire/Components/ConsortiumNotifications.php`
- `resources/views/livewire/components/consortium-notifications.blade.php`

#### Propriedades:
```php
public $notifications = [];
public $unreadCount = 0;
public $showDropdown = false;
public $showAll = false; // Mostrar todas ou apenas 10
```

#### Métodos Públicos:
```php
toggleDropdown()         // Abrir/fechar dropdown
markAsRead($id)          // Marcar como lida e redirecionar
markAsUnread($id)        // Marcar como não lida
markAllAsRead()          // Marcar todas como lidas
delete($id)              // Remover notificação
toggleShowAll()          // Ver todas/menos
refreshNotifications()   // Atualizar manualmente
```

#### Event Listeners:
```php
#[On('notification-created')]
loadNotifications()      // Recarregar quando nova notificação
```

#### Features da UI:
- ✅ Badge com contador de não lidas
- ✅ Dropdown com lista de notificações
- ✅ Indicador visual de não lidas (bolinha azul)
- ✅ Ícones por tipo (troféu, alerta)
- ✅ Cores por prioridade/tipo
- ✅ Badge "URGENTE" para alta prioridade
- ✅ Botões de ação (ir, marcar lida, remover)
- ✅ Timestamp relativo ("há 2 horas")
- ✅ Empty state quando sem notificações
- ✅ Botão atualizar manual
- ✅ Responsivo (mobile-friendly)

---

### 6. **Integração no Layout**

**Arquivo**: `resources/views/components/layouts/app/sidebar.blade.php`

#### Top Bar Adicionada:
```html
<!-- Top Bar com Notificações -->
<div id="topBar" class="fixed top-0 right-0 left-0 lg:left-[280px] h-16 bg-white/80 backdrop-blur-xl...">
    @livewire('components.consortium-notifications')
</div>
```

#### CSS Responsivo:
- **Desktop**: Top bar começa após sidebar (280px)
- **Modo Compacto**: Top bar ajusta para 100px
- **Mobile**: Top bar full width (left: 0)

---

## 🚀 Como Usar

### 1. **Ver Notificações (Usuário)**

O componente aparece automaticamente no topo de todas as páginas:
- Clique no ícone de sino
- Veja contador de não lidas
- Clique em notificações para ir à ação
- Marque como lida/não lida
- Remova notificações

### 2. **Criar Notificações Manualmente (Código)**

```php
use App\Models\ConsortiumNotification;
use App\Models\Consortium;
use App\Models\ConsortiumParticipant;

// Notificação de sorteio disponível
$consortium = Consortium::find(1);
ConsortiumNotification::createDrawAvailable($consortium);

// Notificação de resgate pendente
$participant = ConsortiumParticipant::find(5);
ConsortiumNotification::createRedemptionPending($participant);
```

### 3. **Verificar Notificações via Command**

```bash
# Rodar manualmente
php artisan consortium:check-notifications

# Ver ajuda
php artisan consortium:check-notifications --help
```

### 4. **Acessar via Service**

```php
use App\Services\ConsortiumNotificationService;

$service = new ConsortiumNotificationService();

// Verificar e criar notificações
$stats = $service->checkAndCreateNotifications();

// Obter estatísticas
$stats = $service->getStats(auth()->id());

// Notificações recentes
$notifications = $service->getRecentNotifications(auth()->id(), 10);
```

---

## 📊 Exemplos de Notificações

### Sorteio Disponível
```
Título: 🎯 Sorteio Disponível!
Mensagem: O consórcio "Moto 2025" está pronto para realizar um novo sorteio. 
          8 participantes elegíveis aguardando.
Prioridade: high
Cor: purple
Ícone: bi-trophy-fill
Ação: /consortiums/21/draw
```

### Resgate Pendente (7 dias)
```
Título: ⏰ Resgate Pendente
Mensagem: O participante "João Silva" foi contemplado há 7 dias no consórcio 
          "Carro Popular" e ainda não realizou o resgate.
Prioridade: medium
Cor: amber
Ícone: bi-exclamation-triangle-fill
Ação: /consortiums/21#contemplated
```

### Resgate Pendente (30+ dias)
```
Título: ⏰ Resgate Pendente
Mensagem: O participante "Maria Santos" foi contemplado há 35 dias...
Prioridade: high (após 30 dias)
Cor: red
Badge: URGENTE
```

---

## 🎨 Personalização

### Alterar Frequência de Verificação

No `app/Services/ConsortiumNotificationService.php`:

```php
// Linha 66 - Intervalo sem notificação duplicada
where('created_at', '>=', now()->subDay()) // 24h padrão
// Altere para: ->subHours(6) para 6h, etc

// Linha 91 - Dias para resgate pendente
where('contemplation_date', '<=', now()->subDays(7))
// Altere o número de dias conforme necessário
```

### Alterar Intervalos de Notificação de Resgate

No método `checkRedemptionsPending()`:

```php
// Linha 96-107
if ($daysSince >= 7 && $daysSince < 8) {
    $shouldNotify = true; // Primeiro aviso
} elseif ($daysSince >= 15 && $daysSince < 16) {
    $shouldNotify = true; // Segundo aviso
} elseif ($daysSince >= 30 && $daysSince < 31) {
    $shouldNotify = true; // Terceiro aviso
} elseif ($daysSince > 30 && $daysSince % 30 === 0) {
    $shouldNotify = true; // A cada 30 dias
}
```

### Adicionar Novos Tipos de Notificação

1. **Atualizar Migration** - adicionar tipo no enum
2. **Atualizar Model** - adicionar cor/ícone no accessor
3. **Criar método estático** no Model para criar notificação
4. **Adicionar lógica** no Service para verificar condição

---

## 🔧 Manutenção

### Limpar Notificações Antigas

```bash
# Manual
php artisan consortium:check-notifications --clean

# Automático (agendar)
$schedule->command('consortium:check-notifications --clean')->weekly();
```

### Monitorar Performance

```php
// Ver logs
tail -f storage/logs/laravel.log | grep "Consortium notifications"

// Estatísticas no banco
SELECT type, priority, is_read, COUNT(*) 
FROM consortium_notifications 
GROUP BY type, priority, is_read;
```

### Debugging

```php
// Ativar logs detalhados
Log::info('Consortium notifications checked', $stats);
Log::info('Old consortium notifications cleaned', $stats);
```

---

## ✅ Checklist de Implementação

- [x] Migration criada e executada
- [x] Model ConsortiumNotification com relationships e scopes
- [x] Service ConsortiumNotificationService completo
- [x] Command CheckConsortiumNotifications funcional
- [x] Componente Livewire ConsortiumNotifications
- [x] View do componente com UI moderna
- [x] Integração no layout principal (Top Bar)
- [x] CSS responsivo (desktop, compact, mobile)
- [x] Método eligibleParticipantsCount() no Consortium
- [x] Testes funcionais do comando
- [x] Documentação completa

---

## 🎯 Próximos Passos (Opcional)

### Melhorias Futuras:

1. **Notificações por Email**
   - Usar Laravel Notifications
   - Enviar resumo diário/semanal

2. **Notificações Push**
   - Integrar com Laravel Echo
   - Pusher ou Socket.io

3. **Dashboard de Notificações**
   - Página dedicada com filtros
   - Gráficos e estatísticas

4. **Notificações para Clientes**
   - Avisar clientes sobre sorteios
   - Avisar contemplados sobre resgate

5. **Automação Avançada**
   - Notificações antes do sorteio (3 dias)
   - Lembretes de pagamento
   - Alertas de inadimplência

---

## 📝 Notas Importantes

1. **Agendamento do Cron é ESSENCIAL** para o sistema funcionar automaticamente
2. **Notificações NÃO são emails** - são avisos internos no sistema
3. **Customize os intervalos** conforme necessidade do negócio
4. **Monitore o banco** - limpe notificações antigas regularmente
5. **Performance**: Índices criados automaticamente pela migration

---

## 🆘 Troubleshooting

### Notificações não aparecem?
```bash
# Verificar se a migration rodou
php artisan migrate:status | Select-String "consortium"

# Rodar manualmente
php artisan consortium:check-notifications

# Ver logs
tail -f storage/logs/laravel.log
```

### Componente não aparece no topo?
- Verificar se `@livewire('components.consortium-notifications')` está no layout
- Verificar cache: `php artisan view:clear && php artisan config:clear`
- Verificar se Livewire está carregado corretamente

### Command falha?
- Verificar se model Consortium tem método `canPerformDraw()`
- Verificar se model Consortium tem método `eligibleParticipantsCount()`
- Ver erro completo nos logs

---

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte esta documentação
2. Verifique os logs: `storage/logs/laravel.log`
3. Execute os comandos de debug acima
4. Revise o código com comentários inline

---

**Versão**: 1.0  
**Data**: 10/01/2026  
**Status**: ✅ Implementado e Funcional
