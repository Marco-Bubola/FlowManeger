# 🔗 Integração do Sistema de Consórcios com Outras Páginas

## 📊 1. DASHBOARD PRINCIPAL

### **Adicionar Card de Consórcios**

#### Localização:
`resources/views/livewire/dashboard/dashboard-index.blade.php`

#### Sugestões de KPIs:
```html
<!-- Card Consórcios Ativos -->
<div class="group relative bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-4">
    <div class="flex items-center gap-3 mb-2">
        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shadow-md">
            <i class="bi bi-people-fill text-white text-xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-xs text-purple-800 dark:text-purple-300 font-medium">Consórcios Ativos</p>
            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ $consorciosAtivos }}</p>
        </div>
    </div>
    <div class="flex items-center justify-between text-xs">
        <span class="text-purple-600 dark:text-purple-400">{{ $totalParticipantes }} participantes</span>
        <a href="{{ route('consortiums.index') }}" class="text-purple-600 hover:text-purple-800">Ver todos →</a>
    </div>
</div>
```

#### Adicionar no Controller DashboardIndex.php:
```php
use App\Models\Consortium;
use App\Models\ConsortiumParticipant;

public int $consorciosAtivos = 0;
public int $totalParticipantes = 0;
public float $arrecadacaoConsorciosMes = 0;
public int $sorteiosPendentes = 0;

public function mount()
{
    // ... código existente ...
    
    // Dados de Consórcios
    $this->consorciosAtivos = Consortium::where('user_id', Auth::id())
        ->where('status', 'active')
        ->count();
    
    $this->totalParticipantes = ConsortiumParticipant::whereIn(
        'consortium_id',
        Consortium::where('user_id', Auth::id())->pluck('id')
    )->where('status', 'active')->count();
    
    $this->arrecadacaoConsorciosMes = ConsortiumPayment::whereIn(
        'consortium_participant_id',
        ConsortiumParticipant::whereIn(
            'consortium_id',
            Consortium::where('user_id', Auth::id())->pluck('id')
        )->pluck('id')
    )
    ->where('status', 'paid')
    ->whereMonth('payment_date', now()->month)
    ->whereYear('payment_date', now()->year)
    ->sum('amount');
    
    $this->sorteiosPendentes = Consortium::where('user_id', Auth::id())
        ->where('status', 'active')
        ->get()
        ->filter(fn($c) => $c->canPerformDraw())
        ->count();
}
```

### **Widget Sorteios Próximos**
```html
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-bold mb-4">
        <i class="bi bi-trophy text-yellow-500"></i>
        Sorteios Próximos
    </h3>
    @foreach($proximosSorteios as $sorteio)
        <div class="flex items-center justify-between py-2 border-b">
            <div>
                <p class="font-semibold">{{ $sorteio->name }}</p>
                <p class="text-xs text-slate-500">{{ $sorteio->active_participants_count }} participantes</p>
            </div>
            <span class="text-sm text-blue-600">{{ $sorteio->days_until }} dias</span>
        </div>
    @endforeach
</div>
```

---

## 👥 2. PÁGINA DE CLIENTES

### **Adicionar Aba "Consórcios" na Visualização**

#### Em: `resources/views/livewire/clients/show-client.blade.php`

```html
<!-- Adicionar na navegação de abas -->
<button @click="activeTab = 'consortiums'"
    :class="activeTab === 'consortiums' ? 'border-purple-500 text-purple-600' : 'border-transparent'"
    class="px-4 py-2 border-b-2 font-medium">
    <i class="bi bi-people"></i>
    Consórcios
    @if($client->consortiumParticipations->count() > 0)
        <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-600 rounded-full text-xs">
            {{ $client->consortiumParticipations->count() }}
        </span>
    @endif
</button>

<!-- Conteúdo da aba -->
<div x-show="activeTab === 'consortiums'" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($client->consortiumParticipations as $participation)
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                <h4 class="font-bold text-lg mb-2">{{ $participation->consortium->name }}</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Número:</span>
                        <span class="font-semibold">{{ $participation->participation_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Status:</span>
                        <span class="px-2 py-0.5 rounded {{ $participation->status_color }}">
                            {{ $participation->status_label }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Pago:</span>
                        <span class="font-semibold text-green-600">
                            R$ {{ number_format($participation->total_paid, 2, ',', '.') }}
                        </span>
                    </div>
                    @if($participation->is_contemplated)
                        <div class="flex items-center gap-2 text-blue-600">
                            <i class="bi bi-trophy-fill"></i>
                            <span>Contemplado</span>
                        </div>
                    @endif
                </div>
                <div class="mt-4">
                    <a href="{{ route('consortiums.show', $participation->consortium) }}" 
                       class="block text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Ver Detalhes
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    
    @if($client->consortiumParticipations->count() === 0)
        <div class="text-center py-8 text-slate-500">
            <i class="bi bi-people text-4xl mb-2"></i>
            <p>Cliente não participa de nenhum consórcio</p>
            <a href="{{ route('consortiums.index') }}" class="text-purple-600 hover:underline mt-2 inline-block">
                Ver consórcios disponíveis →
            </a>
        </div>
    @endif
</div>
```

#### Adicionar no Model Client.php:
```php
public function consortiumParticipations()
{
    return $this->hasMany(ConsortiumParticipant::class);
}

public function activeConsortiums()
{
    return $this->hasMany(ConsortiumParticipant::class)->where('status', 'active');
}
```

### **Badge no Card do Cliente**
```html
<!-- Na listagem de clientes -->
<div class="flex items-center gap-2">
    @if($client->activeConsortiums->count() > 0)
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs">
            <i class="bi bi-people"></i>
            {{ $client->activeConsortiums->count() }} consórcio(s)
        </span>
    @endif
</div>
```

---

## 📦 3. PÁGINA DE PRODUTOS

### **Vincular Produtos com Consórcios**

#### Em: `resources/views/livewire/products/show-product.blade.php`

```html
<!-- Nova seção: Consórcios que incluem este produto -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-bold mb-4">
        <i class="bi bi-trophy text-purple-600"></i>
        Consórcios com Este Produto
    </h3>
    
    @php
        $contemplations = \App\Models\ConsortiumContemplation::whereJsonContains('products', ['product_id' => $product->id])->get();
    @endphp
    
    @if($contemplations->count() > 0)
        <div class="space-y-3">
            @foreach($contemplations as $contemplation)
                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                    <div>
                        <p class="font-semibold">{{ $contemplation->participant->consortium->name }}</p>
                        <p class="text-sm text-slate-600">
                            Cliente: {{ $contemplation->participant->client->name }}
                        </p>
                    </div>
                    <span class="text-sm text-green-600">Resgatado</span>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-slate-500 text-sm italic">Produto ainda não foi resgatado em nenhum consórcio</p>
    @endif
</div>
```

---

## 💰 4. PÁGINA FINANCEIRA/CAIXA

### **Integrar Pagamentos de Consórcios**

#### Em: `app/Livewire/Cashbook/CashbookIndex.php`

```php
// Adicionar filtro de tipo
public function mount()
{
    // ... código existente ...
    
    // Buscar pagamentos de consórcios como receitas
    $consortiumPayments = ConsortiumPayment::where('status', 'paid')
        ->whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
        ->get();
    
    foreach ($consortiumPayments as $payment) {
        $this->addToFinancialSummary([
            'date' => $payment->payment_date,
            'type' => 'Consórcio',
            'description' => "Parcela {$payment->reference_month_name}/{$payment->reference_year} - {$payment->participant->client->name}",
            'amount' => $payment->amount,
            'category' => 'Receita de Consórcio',
        ]);
    }
}
```

#### Visualização no Cashbook:
```html
<!-- Card resumo de receitas de consórcios -->
<div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-purple-700 dark:text-purple-400">
            <i class="bi bi-people"></i> Receitas de Consórcios
        </span>
        <i class="bi bi-graph-up text-2xl text-purple-600"></i>
    </div>
    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
        R$ {{ number_format($receitasConsorciosMes, 2, ',', '.') }}
    </p>
    <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
        {{ $pagamentosConsorciosMes }} pagamentos este mês
    </p>
</div>
```

---

## ⚙️ 5. CONFIGURAÇÕES

### **Nova Seção: Configurações de Consórcios**

#### Criar: `resources/views/livewire/settings/consortium-settings.blade.php`

```html
<div class="space-y-6">
    <h2 class="text-2xl font-bold">Configurações de Consórcios</h2>
    
    <!-- Taxa de Juros -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6">
        <label class="block text-sm font-medium mb-2">Taxa de Juros Mensal (%)</label>
        <input type="number" step="0.01" wire:model="interestRate" 
               class="w-full px-4 py-2 border rounded-lg">
        <p class="text-xs text-slate-500 mt-1">Juros aplicados em pagamentos atrasados</p>
    </div>
    
    <!-- Taxa de Multa -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6">
        <label class="block text-sm font-medium mb-2">Taxa de Multa (%)</label>
        <input type="number" step="0.01" wire:model="fineRate" 
               class="w-full px-4 py-2 border rounded-lg">
        <p class="text-xs text-slate-500 mt-1">Multa fixa aplicada em atrasos</p>
    </div>
    
    <!-- Dias de Tolerância -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6">
        <label class="block text-sm font-medium mb-2">Dias de Tolerância</label>
        <input type="number" wire:model="gracePeriod" 
               class="w-full px-4 py-2 border rounded-lg">
        <p class="text-xs text-slate-500 mt-1">Dias após vencimento antes de aplicar multa</p>
    </div>
    
    <!-- Notificações -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6">
        <label class="flex items-center gap-3">
            <input type="checkbox" wire:model="sendPaymentReminders" class="rounded">
            <span>Enviar lembretes de pagamento automáticos</span>
        </label>
    </div>
    
    <!-- Botão Salvar -->
    <button wire:click="saveSettings" 
            class="px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700">
        <i class="bi bi-check-circle"></i> Salvar Configurações
    </button>
</div>
```

---

## 🔔 6. SISTEMA DE NOTIFICAÇÕES

### **Eventos para Disparar Notificações**

#### Criar: `app/Events/ConsortiumPaymentDue.php`
```php
<?php

namespace App\Events;

use App\Models\ConsortiumPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsortiumPaymentDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ConsortiumPayment $payment;
    public int $daysUntilDue;

    public function __construct(ConsortiumPayment $payment, int $daysUntilDue)
    {
        $this->payment = $payment;
        $this->daysUntilDue = $daysUntilDue;
    }
}
```

#### Command para verificar vencimentos:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConsortiumPayment;
use App\Events\ConsortiumPaymentDue;

class CheckConsortiumPayments extends Command
{
    protected $signature = 'consortium:check-payments';
    protected $description = 'Verifica pagamentos vencendo e dispara notificações';

    public function handle()
    {
        // Pagamentos vencendo em 7 dias
        $paymentsDueSoon = ConsortiumPayment::where('status', 'pending')
            ->whereDate('due_date', now()->addDays(7))
            ->get();

        foreach ($paymentsDueSoon as $payment) {
            event(new ConsortiumPaymentDue($payment, 7));
        }

        // Pagamentos vencendo em 3 dias
        $paymentsDueSooner = ConsortiumPayment::where('status', 'pending')
            ->whereDate('due_date', now()->addDays(3))
            ->get();

        foreach ($paymentsDueSooner as $payment) {
            event(new ConsortiumPaymentDue($payment, 3));
        }

        $this->info('Verificação de pagamentos concluída!');
    }
}
```

#### Agendar no Kernel:
```php
protected function schedule(Schedule $schedule)
{
    // Verificar pagamentos diariamente às 9h
    $schedule->command('consortium:check-payments')->dailyAt('09:00');
}
```

---

## 📱 7. MENU DE NAVEGAÇÃO

### **Adicionar Link para Consórcios**

#### Em: `resources/views/layouts/app.blade.php` ou navegação principal

```html
<a href="{{ route('consortiums.index') }}"
   class="flex items-center gap-3 px-4 py-3 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors {{ request()->routeIs('consortiums.*') ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600' : '' }}">
    <i class="bi bi-people text-xl"></i>
    <span class="font-medium">Consórcios</span>
    @if($consorciosComAlertas > 0)
        <span class="ml-auto px-2 py-1 bg-red-500 text-white rounded-full text-xs">
            {{ $consorciosComAlertas }}
        </span>
    @endif
</a>
```

---

## 🎯 PRIORIDADE DE IMPLEMENTAÇÃO

### **Fase 1 - Essencial:**
1. ✅ Adicionar card de consórcios no dashboard
2. ✅ Badge de consórcios na página de clientes
3. ✅ Link no menu de navegação

### **Fase 2 - Importante:**
4. Aba completa de consórcios em show-client
5. Integração com fluxo de caixa
6. Widget de sorteios próximos

### **Fase 3 - Avançado:**
7. Página de configurações
8. Sistema de notificações
9. Vincular produtos com consórcios
10. Relatórios consolidados

---

## 📝 CÓDIGO PRONTO PARA COPIAR

### **Dashboard - Adicionar ao Controller:**
```php
// No mount() do DashboardIndex.php
use App\Models\Consortium;
use App\Models\ConsortiumParticipant;
use App\Models\ConsortiumPayment;

$this->consorciosAtivos = Consortium::where('user_id', Auth::id())
    ->where('status', 'active')
    ->count();

$this->totalParticipantesConsorcio = ConsortiumParticipant::whereIn(
    'consortium_id',
    Consortium::where('user_id', Auth::id())->pluck('id')
)->where('status', 'active')->count();

$this->arrecadacaoConsorciosMes = ConsortiumPayment::whereIn(
    'consortium_participant_id',
    ConsortiumParticipant::whereIn(
        'consortium_id',
        Consortium::where('user_id', Auth::id())->pluck('id')
    )->pluck('id')
)
->where('status', 'paid')
->whereMonth('payment_date', now()->month)
->whereYear('payment_date', now()->year)
->sum('amount');
```

### **Model Client - Adicionar Relationships:**
```php
public function consortiumParticipations()
{
    return $this->hasMany(\App\Models\ConsortiumParticipant::class);
}

public function activeConsortiumParticipations()
{
    return $this->hasMany(\App\Models\ConsortiumParticipant::class)
        ->where('status', 'active');
}
```

---

**🎉 Sistema totalmente integrado e profissional!**
