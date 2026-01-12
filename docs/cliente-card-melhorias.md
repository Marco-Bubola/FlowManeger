# Melhorias do Card de Cliente - FlowManager

**Data:** 2024  
**Status:** 🔨 Em Desenvolvimento

---

## 📋 Visão Geral

Este documento detalha todas as melhorias planejadas e pendentes para os cards de cliente no sistema FlowManager, incluindo redesenho visual, novas funcionalidades, e melhorias de usabilidade.

---

## 🎯 Objetivos Principais

1. **Design Moderno e Compacto** - Deixar os cards mais bonitos, organizados e ocupando menos espaço
2. **Múltiplos Pontos de Acesso** - Facilitar navegação para diferentes dashboards
3. **Funcionalidade de Export** - Permitir exportação de dados de vendas e consórcios
4. **Comunicação Integrada** - Verificar e melhorar funcionalidades WhatsApp e Email
5. **Acesso Rápido a Consórcios** - Botão dedicado para ClientShowConsorcio

---

## 🎨 Redesenho do Card

### Layout Atual (Problemas Identificados)

- ❌ Muito espaçado verticalmente
- ❌ Botões grandes demais
- ❌ Informações desorganizadas
- ❌ Dashboard completo é único botão principal
- ❌ Sem acesso direto ao dashboard de consórcio
- ❌ Sem funcionalidade de export

### Layout Proposto (Melhorias)

#### Estrutura Compacta

```
┌──────────────────────────────────────┐
│ [Avatar] Nome do Cliente      [Menu] │
│ 📧 email@cliente.com                 │
│ 📱 (11) 99999-9999                   │
├──────────────────────────────────────┤
│ Vendas: 15 | Consórcios: 2          │
│ Total: R$ 45.280,00                  │
├──────────────────────────────────────┤
│ [Dashboard] [Resumo] [Consórcio]    │ ← 3 BOTÕES NA MESMA LINHA
├──────────────────────────────────────┤
│ [✏️ Editar] [✉️ Email] [🗑️ Delete]  │
│ [📤 Export] [💬 WhatsApp]            │
└──────────────────────────────────────┘
```

#### Especificações de Design

**Dimensões:**
- Altura: ~280px (vs atual ~380px) - 26% mais compacto
- Padding: `p-4` (vs atual `p-6`)
- Gaps: `gap-2` e `gap-3` (vs `gap-4`)

**Tipografia:**
- Nome: `text-base font-bold` (vs `text-lg`)
- Email/Phone: `text-xs` (vs `text-sm`)
- Stats: `text-sm font-medium`
- Botões: `text-xs` (vs `text-sm`)

**Cores (Dark Mode First):**
- Card: `bg-slate-800/90 backdrop-blur-sm`
- Border: `border-slate-700 hover:border-purple-500`
- Gradientes: Dashboard (blue-purple), Resumo (indigo), Consórcio (green-teal)

---

## 🔘 Sistema de Botões Redesenhado

### Linha 1: Dashboards (Principais - 3 botões)

#### 1. Dashboard Completo
```blade
<a href="{{ route('clients.dashboard', $client->id) }}"
   class="flex-1 flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
    <i class="bi bi-speedometer2 mr-1.5"></i>
    Dashboard
</a>
```

#### 2. Dashboard Resumo
```blade
<a href="{{ route('clients.resumo', $client->id) }}"
   class="flex-1 flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200">
    <i class="bi bi-graph-up mr-1.5"></i>
    Resumo
</a>
```

#### 3. Dashboard Consórcio *(NOVO)*
```blade
<a href="{{ route('clients.show.consortium', $client->id) }}"
   class="flex-1 flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 transition-all duration-200">
    <i class="bi bi-building mr-1.5"></i>
    Consórcio
</a>
```

**Layout:** `grid grid-cols-3 gap-2`

---

### Linha 2: Ações Rápidas (5 botões em 2 colunas)

#### Primeira Linha (3 botões)
1. **Editar** - `bi-pencil` - Gray
2. **Email** - `bi-envelope` - Blue  
3. **Delete** - `bi-trash` - Red (condicional: sem vendas)

#### Segunda Linha (2 botões)
4. **Export** *(NOVO)* - `bi-download` - Purple - Abre modal
5. **WhatsApp** *(VERIFICAR)* - `bi-whatsapp` - Green

**Layout:** 
```blade
<div class="grid grid-cols-3 gap-2">
    <!-- Editar, Email, Delete -->
</div>
<div class="grid grid-cols-2 gap-2 mt-2">
    <!-- Export, WhatsApp -->
</div>
```

---

## 📤 Modal de Export (NOVA FUNCIONALIDADE)

### Trigger
Botão "Export" no card do cliente abre modal com opções.

### Estrutura do Modal

```blade
<!-- Modal Export Cliente -->
<div x-data="{ showExportModal: false, exportType: 'vendas', selectedSale: null, selectedConsortium: null }">
    <!-- Botão no Card -->
    <button @click="showExportModal = true" 
            class="...">
        <i class="bi bi-download mr-1"></i>
        Export
    </button>

    <!-- Modal -->
    <div x-show="showExportModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.away="showExportModal = false">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <!-- Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full p-6">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-white">
                        Exportar Dados do Cliente
                    </h3>
                    <button @click="showExportModal = false">
                        <i class="bi bi-x-lg text-gray-400 hover:text-white"></i>
                    </button>
                </div>

                <!-- Tabs: Vendas | Consórcios -->
                <div class="flex gap-2 mb-6">
                    <button @click="exportType = 'vendas'"
                            :class="exportType === 'vendas' ? 'bg-blue-600' : 'bg-slate-700'"
                            class="flex-1 px-4 py-2 rounded-lg text-white font-medium">
                        <i class="bi bi-cart mr-2"></i>
                        Vendas
                    </button>
                    <button @click="exportType = 'consortiums'"
                            :class="exportType === 'consortiums' ? 'bg-green-600' : 'bg-slate-700'"
                            class="flex-1 px-4 py-2 rounded-lg text-white font-medium">
                        <i class="bi bi-building mr-2"></i>
                        Consórcios
                    </button>
                </div>

                <!-- Conteúdo: Vendas -->
                <div x-show="exportType === 'vendas'" class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-300">
                        Selecione uma venda para exportar:
                    </h4>
                    
                    <!-- Lista de Vendas -->
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($client->sales as $sale)
                        <label class="flex items-center justify-between p-3 bg-slate-700/50 hover:bg-slate-700 rounded-lg cursor-pointer transition">
                            <div class="flex items-center gap-3">
                                <input type="radio" 
                                       name="sale_export" 
                                       value="{{ $sale->id }}"
                                       x-model="selectedSale"
                                       class="text-blue-600">
                                <div>
                                    <p class="text-sm font-medium text-white">
                                        Venda #{{ $sale->id }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $sale->created_at->format('d/m/Y') }} - R$ {{ number_format($sale->total_amount, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $sale->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <!-- Botões Export Vendas -->
                    <div class="flex gap-2 pt-4">
                        <button wire:click="exportSalePDF(selectedSale)"
                                :disabled="!selectedSale"
                                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-600 text-white rounded-lg font-medium transition">
                            <i class="bi bi-file-pdf mr-2"></i>
                            PDF
                        </button>
                        <button wire:click="exportSaleExcel(selectedSale)"
                                :disabled="!selectedSale"
                                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white rounded-lg font-medium transition">
                            <i class="bi bi-file-excel mr-2"></i>
                            Excel
                        </button>
                    </div>
                </div>

                <!-- Conteúdo: Consórcios -->
                <div x-show="exportType === 'consortiums'" class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-300">
                        Selecione um consórcio para exportar:
                    </h4>
                    
                    <!-- Lista de Consórcios -->
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($client->consortiumParticipants as $participant)
                        <label class="flex items-center justify-between p-3 bg-slate-700/50 hover:bg-slate-700 rounded-lg cursor-pointer transition">
                            <div class="flex items-center gap-3">
                                <input type="radio" 
                                       name="consortium_export" 
                                       value="{{ $participant->consortium_id }}"
                                       x-model="selectedConsortium"
                                       class="text-green-600">
                                <div>
                                    <p class="text-sm font-medium text-white">
                                        {{ $participant->consortium->product->name ?? 'Consórcio' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Cota: {{ $participant->quota_number }} - 
                                        R$ {{ number_format($participant->consortium->total_value, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $participant->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ ucfirst($participant->status) }}
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <!-- Botões Export Consórcio -->
                    <div class="flex gap-2 pt-4">
                        <button wire:click="exportConsortiumPDF(selectedConsortium)"
                                :disabled="!selectedConsortium"
                                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-600 text-white rounded-lg font-medium transition">
                            <i class="bi bi-file-pdf mr-2"></i>
                            PDF
                        </button>
                        <button wire:click="exportConsortiumExcel(selectedConsortium)"
                                :disabled="!selectedConsortium"
                                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white rounded-lg font-medium transition">
                            <i class="bi bi-file-excel mr-2"></i>
                            Excel
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
```

---

## 💻 Implementação Backend

### Arquivo: `app/Livewire/Clients/ClientsIndex.php`

#### Métodos a Adicionar

```php
/**
 * Export venda específica em PDF
 */
public function exportSalePDF($saleId)
{
    try {
        $sale = Sale::with([
            'client',
            'saleItems.product',
            'payments',
            'parcelas'
        ])->findOrFail($saleId);
        
        // Verificar se a venda pertence ao cliente correto
        // Gerar PDF usando DOMPDF ou similar
        
        $pdf = PDF::loadView('exports.sale-details', [
            'sale' => $sale
        ]);
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "venda-{$saleId}-" . now()->format('Y-m-d') . ".pdf");
        
    } catch (\Exception $e) {
        Log::error('Erro ao exportar venda PDF: ' . $e->getMessage());
        $this->notifyError('Erro ao gerar PDF da venda');
    }
}

/**
 * Export venda específica em Excel
 */
public function exportSaleExcel($saleId)
{
    try {
        return Excel::download(
            new VendaDetalhadaExport($saleId), 
            "venda-{$saleId}-" . now()->format('Y-m-d') . ".xlsx"
        );
    } catch (\Exception $e) {
        Log::error('Erro ao exportar venda Excel: ' . $e->getMessage());
        $this->notifyError('Erro ao gerar Excel da venda');
    }
}

/**
 * Export consórcio do participante em PDF
 */
public function exportConsortiumPDF($participantId)
{
    try {
        $participant = ConsortiumParticipant::with([
            'consortium.product',
            'client',
            'payments'
        ])->findOrFail($participantId);
        
        $pdf = PDF::loadView('exports.consortium-participant', [
            'participant' => $participant
        ]);
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, "consorcio-participante-{$participantId}-" . now()->format('Y-m-d') . ".pdf");
        
    } catch (\Exception $e) {
        Log::error('Erro ao exportar consórcio PDF: ' . $e->getMessage());
        $this->notifyError('Erro ao gerar PDF do consórcio');
    }
}

/**
 * Export consórcio do participante em Excel
 */
public function exportConsortiumExcel($participantId)
{
    try {
        return Excel::download(
            new ConsortiumParticipantExport($participantId), 
            "consorcio-participante-{$participantId}-" . now()->format('Y-m-d') . ".xlsx"
        );
    } catch (\Exception $e) {
        Log::error('Erro ao exportar consórcio Excel: ' . $e->getMessage());
        $this->notifyError('Erro ao gerar Excel do consórcio');
    }
}
```

### Classes Export a Criar

#### 1. `app/Exports/VendaDetalhadaExport.php`

```php
<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendaDetalhadaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $saleId;

    public function __construct($saleId)
    {
        $this->saleId = $saleId;
    }

    public function collection()
    {
        return Sale::with(['client', 'saleItems.product'])
            ->where('id', $this->saleId)
            ->get()
            ->first()
            ->saleItems;
    }

    public function headings(): array
    {
        return [
            'ID Item',
            'Produto',
            'Quantidade',
            'Preço Unitário',
            'Subtotal',
            'Desconto',
            'Total'
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->product->name,
            $item->quantity,
            'R$ ' . number_format($item->unit_price, 2, ',', '.'),
            'R$ ' . number_format($item->quantity * $item->unit_price, 2, ',', '.'),
            'R$ ' . number_format($item->discount ?? 0, 2, ',', '.'),
            'R$ ' . number_format($item->total_price, 2, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
```

#### 2. `app/Exports/ConsortiumParticipantExport.php`

```php
<?php

namespace App\Exports;

use App\Models\ConsortiumParticipant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ConsortiumParticipantExport implements FromCollection, WithHeadings, WithMapping
{
    protected $participantId;

    public function __construct($participantId)
    {
        $this->participantId = $participantId;
    }

    public function collection()
    {
        return ConsortiumParticipant::with(['consortium', 'payments'])
            ->where('id', $this->participantId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Participante',
            'Cliente',
            'Produto',
            'Número Cota',
            'Valor Total',
            'Valor Pago',
            'Saldo Devedor',
            'Status',
            'Data Adesão'
        ];
    }

    public function map($participant): array
    {
        $valorPago = $participant->payments->sum('amount');
        $saldoDevedor = $participant->consortium->total_value - $valorPago;

        return [
            $participant->id,
            $participant->client->name,
            $participant->consortium->product->name ?? 'N/A',
            $participant->quota_number,
            'R$ ' . number_format($participant->consortium->total_value, 2, ',', '.'),
            'R$ ' . number_format($valorPago, 2, ',', '.'),
            'R$ ' . number_format($saldoDevedor, 2, ',', '.'),
            ucfirst($participant->status),
            $participant->created_at->format('d/m/Y')
        ];
    }
}
```

---

## 📱 Funcionalidade WhatsApp

### Verificação Necessária

Verificar se a funcionalidade atual de WhatsApp está implementada ou é apenas um placeholder.

#### Implementação Proposta (se não existir)

```blade
<!-- Botão WhatsApp no Card -->
<a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $client->phone) }}?text={{ urlencode('Olá ' . $client->name . ', tudo bem?') }}"
   target="_blank"
   class="flex items-center justify-center px-3 py-2 text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-all duration-200">
    <i class="bi bi-whatsapp mr-1.5"></i>
    WhatsApp
</a>
```

#### Melhorias Possíveis

1. **Mensagem Personalizada** - Detectar contexto (venda pendente, pagamento atrasado, etc)
2. **Log de Interações** - Registrar quando WhatsApp foi aberto
3. **Verificação de Número** - Validar formato do telefone antes de abrir
4. **Fallback** - Se não tiver telefone, desabilitar botão

```php
// Método auxiliar no componente
public function getWhatsAppLink($client)
{
    if (!$client->phone) {
        return null;
    }
    
    $phone = preg_replace('/[^0-9]/', '', $client->phone);
    $message = "Olá {$client->name}, ";
    
    // Personalizar mensagem baseado em contexto
    if ($client->hasOverduePayments()) {
        $message .= "identificamos pagamentos pendentes. Podemos conversar?";
    } else {
        $message .= "tudo bem? Como posso ajudar?";
    }
    
    return "https://wa.me/55{$phone}?text=" . urlencode($message);
}
```

---

## ✉️ Funcionalidade Email

### Estado Atual

Atualmente usa `mailto:` simples. Funciona mas poderia ser melhorado.

### Melhorias Propostas

#### 1. Envio Direto pelo Sistema

```php
// Método no ClientsIndex.php
public function sendEmailToClient($clientId, $type = 'general')
{
    $client = Client::findOrFail($clientId);
    
    switch($type) {
        case 'payment_reminder':
            Mail::to($client->email)->send(new PaymentReminderMail($client));
            break;
        case 'invoice':
            Mail::to($client->email)->send(new InvoiceMail($client));
            break;
        default:
            // Abrir modal para compor email personalizado
            $this->dispatch('openEmailModal', clientId: $clientId);
    }
    
    $this->notifySuccess('Email enviado com sucesso!');
}
```

#### 2. Modal de Composição de Email

```blade
<!-- Modal para compor email -->
<div x-data="{ showEmailModal: false, emailSubject: '', emailBody: '' }">
    <button @click="showEmailModal = true" class="...">
        <i class="bi bi-envelope"></i>
        Email
    </button>
    
    <!-- Modal Content -->
    <div x-show="showEmailModal" x-cloak class="fixed inset-0 z-50">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/60"></div>
        
        <!-- Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-slate-800 rounded-2xl p-6 max-w-2xl w-full">
                <h3 class="text-xl font-bold text-white mb-4">
                    Enviar Email para {{ $client->name }}
                </h3>
                
                <!-- Assunto -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Assunto
                    </label>
                    <input type="text" 
                           x-model="emailSubject"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white">
                </div>
                
                <!-- Mensagem -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Mensagem
                    </label>
                    <textarea x-model="emailBody"
                              rows="6"
                              class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white"></textarea>
                </div>
                
                <!-- Templates Rápidos -->
                <div class="mb-4">
                    <p class="text-sm text-gray-400 mb-2">Templates:</p>
                    <div class="flex gap-2">
                        <button @click="emailSubject='Lembrete de Pagamento'; emailBody='...'"
                                class="px-3 py-1 text-xs bg-slate-700 hover:bg-slate-600 text-white rounded">
                            Pagamento
                        </button>
                        <button @click="emailSubject='Fatura Disponível'; emailBody='...'"
                                class="px-3 py-1 text-xs bg-slate-700 hover:bg-slate-600 text-white rounded">
                            Fatura
                        </button>
                        <button @click="emailSubject='Agradecimento'; emailBody='...'"
                                class="px-3 py-1 text-xs bg-slate-700 hover:bg-slate-600 text-white rounded">
                            Obrigado
                        </button>
                    </div>
                </div>
                
                <!-- Botões -->
                <div class="flex gap-3">
                    <button @click="showEmailModal = false"
                            class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">
                        Cancelar
                    </button>
                    <button wire:click="sendCustomEmail($client->id, emailSubject, emailBody)"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        <i class="bi bi-send mr-2"></i>
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 🎬 Rota ClientShowConsorcio

### Verificar Existência

Verificar se a rota `clients.show.consortium` existe em `routes/web.php`.

### Se não existir, criar:

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {
    // ... outras rotas de clientes
    
    Route::get('/clients/{client}/consortium', [ClientConsortiumDashboard::class, 'render'])
        ->name('clients.show.consortium');
});
```

### Criar Componente Livewire

```php
// app/Livewire/Clients/ClientConsortiumDashboard.php

<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ClientConsortiumDashboard extends Component
{
    public Client $client;
    
    public function mount(Client $client)
    {
        $this->client = $client->load([
            'consortiumParticipants.consortium.product',
            'consortiumParticipants.payments'
        ]);
    }
    
    public function render()
    {
        return view('livewire.clients.client-consortium-dashboard', [
            'participants' => $this->client->consortiumParticipants,
            'totalInvested' => $this->getTotalInvested(),
            'activeConsortiums' => $this->getActiveConsortiums(),
            'completedConsortiums' => $this->getCompletedConsortiums(),
        ]);
    }
    
    private function getTotalInvested()
    {
        return $this->client->consortiumParticipants
            ->sum(function($participant) {
                return $participant->payments->sum('amount');
            });
    }
    
    private function getActiveConsortiums()
    {
        return $this->client->consortiumParticipants
            ->where('status', 'active')
            ->count();
    }
    
    private function getCompletedConsortiums()
    {
        return $this->client->consortiumParticipants
            ->where('status', 'completed')
            ->count();
    }
}
```

### Criar View

```blade
<!-- resources/views/livewire/clients/client-consortium-dashboard.blade.php -->
<div class="p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    Consórcios de {{ $client->name }}
                </h1>
                <p class="text-green-100">
                    Acompanhamento completo de participações em consórcios
                </p>
            </div>
            <div class="text-right">
                <p class="text-green-100 text-sm">Total Investido</p>
                <p class="text-3xl font-bold text-white">
                    R$ {{ number_format($totalInvested, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Consórcios Ativos</p>
                    <p class="text-3xl font-bold text-green-400">{{ $activeConsortiums }}</p>
                </div>
                <div class="w-14 h-14 bg-green-500/20 rounded-full flex items-center justify-center">
                    <i class="bi bi-building text-2xl text-green-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Consórcios Concluídos</p>
                    <p class="text-3xl font-bold text-blue-400">{{ $completedConsortiums }}</p>
                </div>
                <div class="w-14 h-14 bg-blue-500/20 rounded-full flex items-center justify-center">
                    <i class="bi bi-check-circle text-2xl text-blue-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total de Cotas</p>
                    <p class="text-3xl font-bold text-purple-400">{{ $participants->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-purple-500/20 rounded-full flex items-center justify-center">
                    <i class="bi bi-card-list text-2xl text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de Participações -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($participants as $participant)
        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden hover:border-green-500 transition">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-green-600 to-teal-600 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white">
                            {{ $participant->consortium->product->name ?? 'Consórcio' }}
                        </h3>
                        <p class="text-green-100 text-sm">
                            Cota #{{ $participant->quota_number }}
                        </p>
                    </div>
                    <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-bold text-white">
                        {{ strtoupper($participant->status) }}
                    </span>
                </div>
            </div>
            
            <!-- Card Body -->
            <div class="p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 text-sm">Valor Total:</span>
                    <span class="text-white font-bold">
                        R$ {{ number_format($participant->consortium->total_value, 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 text-sm">Valor Pago:</span>
                    <span class="text-green-400 font-bold">
                        R$ {{ number_format($participant->payments->sum('amount'), 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400 text-sm">Saldo Devedor:</span>
                    <span class="text-red-400 font-bold">
                        R$ {{ number_format($participant->consortium->total_value - $participant->payments->sum('amount'), 2, ',', '.') }}
                    </span>
                </div>
                
                <!-- Progress Bar -->
                @php
                    $percentPaid = ($participant->payments->sum('amount') / $participant->consortium->total_value) * 100;
                @endphp
                <div class="pt-2">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>Progresso</span>
                        <span>{{ number_format($percentPaid, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-gradient-to-r from-green-500 to-teal-500 h-2 rounded-full transition-all"
                             style="width: {{ $percentPaid }}%"></div>
                    </div>
                </div>
                
                <!-- Botões -->
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('consortiums.show', $participant->consortium_id) }}"
                       class="flex-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-medium rounded-lg text-center transition">
                        Ver Detalhes
                    </a>
                    <button wire:click="exportConsortiumPDF({{ $participant->id }})"
                            class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition">
                        <i class="bi bi-download mr-1"></i>
                        Exportar
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    @if($participants->isEmpty())
    <div class="bg-slate-800 rounded-xl border border-slate-700 p-12 text-center">
        <i class="bi bi-building text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-xl font-bold text-white mb-2">
            Nenhum Consórcio Encontrado
        </h3>
        <p class="text-gray-400">
            Este cliente ainda não participa de nenhum consórcio.
        </p>
    </div>
    @endif
</div>
```

---

## 📊 Estatísticas no Card

### Informações Compactas a Exibir

```blade
<!-- Dentro do card, antes dos botões -->
<div class="grid grid-cols-2 gap-3 py-3 border-y border-slate-700">
    <!-- Vendas -->
    <div class="text-center">
        <div class="flex items-center justify-center gap-1 text-blue-400 mb-1">
            <i class="bi bi-cart3 text-xs"></i>
            <span class="text-xs font-medium">Vendas</span>
        </div>
        <p class="text-lg font-bold text-white">
            {{ $client->sales->count() }}
        </p>
        <p class="text-xs text-gray-400">
            R$ {{ number_format($client->sales->sum('total_amount'), 2, ',', '.') }}
        </p>
    </div>
    
    <!-- Consórcios -->
    <div class="text-center">
        <div class="flex items-center justify-center gap-1 text-green-400 mb-1">
            <i class="bi bi-building text-xs"></i>
            <span class="text-xs font-medium">Consórcios</span>
        </div>
        <p class="text-lg font-bold text-white">
            {{ $client->consortiumParticipants->count() }}
        </p>
        <p class="text-xs text-gray-400">
            {{ $client->consortiumParticipants->where('status', 'active')->count() }} ativos
        </p>
    </div>
</div>
```

---

## ✅ Checklist de Implementação

### Fase 1: Redesign do Card (2-3 horas)
- [ ] Reduzir padding e gaps do card
- [ ] Ajustar tipografia (text-base, text-xs)
- [ ] Reorganizar layout com 3 botões de dashboard na mesma linha
- [ ] Adicionar seção de estatísticas compacta
- [ ] Melhorar hover states e transições

### Fase 2: Botão Consórcio (1-2 horas)
- [ ] Verificar se rota `clients.show.consortium` existe
- [ ] Criar componente ClientConsortiumDashboard se necessário
- [ ] Criar view do dashboard de consórcios
- [ ] Testar navegação e carregamento de dados

### Fase 3: Modal de Export (3-4 horas)
- [ ] Criar estrutura do modal com Alpine.js
- [ ] Implementar toggle entre Vendas/Consórcios
- [ ] Criar lista selecionável de vendas
- [ ] Criar lista selecionável de consórcios
- [ ] Adicionar métodos export no componente Livewire

### Fase 4: Classes Export (2-3 horas)
- [ ] Criar VendaDetalhadaExport (Excel)
- [ ] Criar view de PDF para venda
- [ ] Criar ConsortiumParticipantExport (Excel)
- [ ] Criar view de PDF para consórcio
- [ ] Testar downloads de todos os formatos

### Fase 5: WhatsApp e Email (1-2 horas)
- [ ] Verificar implementação atual do WhatsApp
- [ ] Melhorar link com mensagem personalizada
- [ ] Criar modal de composição de email (opcional)
- [ ] Adicionar templates rápidos de email
- [ ] Testar envio de emails

### Fase 6: Testes e Refinamentos (1-2 horas)
- [ ] Testar responsividade do novo card
- [ ] Verificar dark mode em todos os componentes
- [ ] Testar todas as funcionalidades de export
- [ ] Validar links e navegação
- [ ] Corrigir bugs encontrados

**Tempo Total Estimado:** 10-16 horas

---

## 🚀 Ideias Futuras

### Funcionalidades Avançadas

1. **Ações em Massa**
   - Selecionar múltiplos clientes
   - Enviar email/WhatsApp para múltiplos
   - Export em batch

2. **Tags e Categorias**
   - Etiquetar clientes (VIP, Inadimplente, etc)
   - Filtrar por tags
   - Cores personalizadas por categoria

3. **Timeline de Interações**
   - Histórico de emails enviados
   - Registro de ligações/WhatsApp
   - Notas de atendimento

4. **Dashboards Personalizados**
   - Cliente escolhe quais métricas ver
   - Widgets arrastáveis
   - Gráficos customizáveis

5. **Notificações Inteligentes**
   - Lembrete de aniversário
   - Alerta de pagamento próximo
   - Cliente sem compras há X dias

6. **Análise Preditiva**
   - Probabilidade de compra
   - Risco de churn
   - Produtos recomendados

7. **Integração com CRM**
   - Sincronização com RD Station
   - Integração com Pipedrive
   - Webhook para eventos

8. **Chat em Tempo Real**
   - Chat interno no sistema
   - Integração com WhatsApp Business API
   - Chatbot para atendimento

---

## 📝 Notas de Desenvolvimento

### Dependências Necessárias

```bash
# Excel Export (já instalado)
composer require maatwebsite/excel

# PDF Generation (verificar se já instalado)
composer require barryvdh/laravel-dompdf

# Queue para jobs pesados (opcional)
php artisan queue:table
php artisan migrate
```

### Configurações

```env
# .env - Configurações de email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Boas Práticas

1. **Sempre usar Jobs para exports pesados**
   ```php
   dispatch(new ExportSaleJob($saleId, $format));
   ```

2. **Cache para dados de dashboard**
   ```php
   Cache::remember("client-{$clientId}-stats", 3600, function() {
       // cálculos pesados
   });
   ```

3. **Validar permissões**
   ```php
   $this->authorize('export', $sale);
   ```

4. **Log de todas as ações importantes**
   ```php
   Log::info("Export realizado", [
       'user_id' => auth()->id(),
       'client_id' => $clientId,
       'type' => $exportType
   ]);
   ```

---

## 🎯 Prioridades

### Alta Prioridade (Fazer primeiro)
1. ✅ Redesign do card (base de tudo)
2. ✅ 3 botões de dashboard na mesma linha
3. ✅ Modal de export com opções

### Média Prioridade
4. Implementação dos exports (PDF/Excel)
5. Dashboard de consórcio dedicado
6. Melhorias no WhatsApp/Email

### Baixa Prioridade (Pode ser depois)
7. Modal de composição de email
8. Templates de email
9. Ideias futuras listadas

---

## 📚 Referências

- [Laravel Excel Documentation](https://docs.laravel-excel.com/)
- [DomPDF Documentation](https://github.com/barryvdh/laravel-dompdf)
- [Alpine.js x-cloak](https://alpinejs.dev/directives/cloak)
- [Tailwind CSS Cards](https://tailwindcss.com/docs/border-radius)
- [Livewire File Downloads](https://livewire.laravel.com/docs/file-downloads)

---

## 🔄 Histórico de Atualizações

**2024-XX-XX** - Documento criado com todas as ideias e especificações

---

**Desenvolvido com ❤️ para FlowManager**
