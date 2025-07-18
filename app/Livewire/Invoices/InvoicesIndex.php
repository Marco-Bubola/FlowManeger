<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class InvoicesIndex extends Component
{
    // Propriedades do estado para o filtro
    public $bankId;
    public $currentMonth;
    public $currentStartDate;
    public $currentEndDate;
    public $previousMonth;
    public $nextMonth;
    public $previousMonthName;
    public $nextMonthName;
    public $currentMonthName;

    // Nova propriedade para filtro por data
    public $selectedDate = null;
    public $viewMode = 'cards'; // 'cards' ou 'list'

    // Propriedades reativas para os dados
    public $bank = null;
    public $banks = [];
    public $categories = [];
    public $clients = [];
    public $invoices = [];
    public $invoiceDates = [];
    public $eventsGroupedByMonthAndCategory = [];
    public $eventsDetailed = [];
    public $categoriesWithTransactions = [];
    public $categoriesData = [];
    public $dailyLabels = [];
    public $dailyValues = [];

    // Estatísticas
    public $totalInvoices = 0;
    public $totalReceitas = 0;
    public $totalDespesas = 0;
    public $saldo = 0;
    public $highestInvoice = null;
    public $lowestInvoice = null;
    public $totalTransactions = 0;

    // Modal de exclusão
    public ?Invoice $deletingInvoice = null;
    public bool $showDeleteModal = false;

    public function mount($bankId = null)
    {
        $this->bankId = $bankId;
        $this->currentMonth = request()->query('month', now()->format('Y-m-d'));

        // Se não há bankId, redirecionar para a página de bancos ou usar o primeiro banco
        if (!$this->bankId) {
            $firstBank = Bank::first();
            if ($firstBank) {
                return redirect()->route('invoices.index', ['bankId' => $firstBank->id_bank]);
            } else {
                return redirect()->route('banks.index')->with('error', 'Você precisa ter pelo menos um banco cadastrado para ver as transações.');
            }
        }

        $this->loadData();
    }

    public function loadData()
    {
        try {
            if (!$this->bankId) {
                return;
            }

            $this->bank = Bank::findOrFail($this->bankId);
            $this->banks = Bank::all();
            $this->categories = Category::all();
            $this->clients = Client::all();

            $this->calculateDateRanges();
            $this->loadInvoices();
            $this->processInvoiceData();

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao carregar os dados: ' . $e->getMessage());
        }
    }

    private function calculateDateRanges()
    {
        $this->currentStartDate = Carbon::parse($this->currentMonth)
            ->setDay(Carbon::parse($this->bank->start_date)->day)
            ->startOfDay();

        $this->currentEndDate = $this->currentStartDate->copy()->addMonth()->subDay()->endOfDay();

        $this->previousMonth = $this->currentStartDate->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->nextMonth = $this->currentStartDate->copy()->addMonth()->startOfMonth()->format('Y-m-d');
        $this->previousMonthName = Carbon::parse($this->previousMonth)->locale('pt_BR')->isoFormat('MMMM');
        $this->nextMonthName = Carbon::parse($this->nextMonth)->locale('pt_BR')->isoFormat('MMMM');

        Carbon::setLocale('pt_BR');
        $this->currentMonthName = ucfirst($this->currentStartDate->translatedFormat('F'));
    }

    private function loadInvoices()
    {
        $query = Invoice::with(['category', 'client'])
            ->where('id_bank', $this->bank->id_bank);

        if ($this->selectedDate) {
            // Filtrar por data específica
            $query->whereDate('invoice_date', $this->selectedDate);
        } else {
            // Filtrar por período do mês
            $query->whereBetween('invoice_date', [$this->currentStartDate, $this->currentEndDate]);
        }

        $this->invoices = $query->orderBy('invoice_date', 'asc')->get();
    }

    public function filterByDate($date)
    {
        $this->selectedDate = $date;
        $this->loadInvoices();
        $this->processInvoiceData();
        $this->dispatch('dateFiltered');
    }

    public function clearDateFilter()
    {
        $this->selectedDate = null;
        $this->loadInvoices();
        $this->processInvoiceData();
    }

    private function processInvoiceData()
    {
        // Converter para coleção para processamento e manter como array para Livewire
        $invoicesCollection = collect($this->invoices);

        // Adicionar propriedade type a cada invoice
        $processedInvoices = $invoicesCollection->map(function ($invoice) {
            $invoice->type = 'despesa';
            return $invoice;
        });

        // Calcular totais - apenas despesas
        $this->totalReceitas = 0; // Sempre zero pois não há receitas
        $this->totalDespesas = $processedInvoices->sum(function($invoice) {
            return abs($invoice->value);
        });
        $this->saldo = 0 - $this->totalDespesas; // Sempre negativo

        // Estatísticas - calcular ANTES de converter para array
        $this->totalInvoices = $this->totalDespesas;
        $this->highestInvoice = $processedInvoices->sortByDesc(function($invoice) {
            return abs($invoice->value);
        })->first();
        $this->lowestInvoice = $processedInvoices->sortBy(function($invoice) {
            return abs($invoice->value);
        })->first();
        $this->totalTransactions = $processedInvoices->count();

        // Gera os dados diários para o gráfico de linhas
        $dailyData = $processedInvoices->groupBy(function ($invoice) {
            return Carbon::parse($invoice->invoice_date)->day;
        })->map(function ($dayInvoices) {
            return $dayInvoices->sum(function($invoice) {
                return abs($invoice->value);
            });
        });

        $this->dailyLabels = $dailyData->keys()->toArray();
        $this->dailyValues = $dailyData->values()->toArray();

        // Cria os dados agrupados por data para o calendário
        $this->invoiceDates = $processedInvoices->groupBy(function ($invoice) {
            return Carbon::parse($invoice->invoice_date)->format('Y-m-d');
        })->toArray();

        // Agrupa as faturas por categoria
        $this->eventsGroupedByMonthAndCategory = [
            'current' => []
        ];
        foreach ($processedInvoices->groupBy('category_id') as $categoryId => $categoryInvoices) {
            $this->eventsGroupedByMonthAndCategory['current'][$categoryId] = $categoryInvoices->values()->toArray();
        }

        // Para ser usado no FullCalendar
        $this->eventsDetailed = $processedInvoices->map(function ($invoice) {
            return [
                'id_invoice' => $invoice->id_invoice ?? $invoice->id ?? null,
                'title' => $invoice->description,
                'start' => $invoice->invoice_date,
                'category' => optional($invoice->category)->name ?? 'Sem Categoria',
                'installments' => $invoice->installments,
                'value' => abs($invoice->value),
                'type' => 'despesa',
            ];
        })->toArray();

        // Filtra as categorias com base nas transações do mês
        $categoriesWithTransactionsCollection = collect($this->categories)->filter(function ($category) use ($processedInvoices) {
            $categoryId = is_array($category) ? ($category['id_category'] ?? null) : $category->id_category;
            return $processedInvoices->where('category_id', $categoryId)->isNotEmpty();
        });

        // Calculando as categorias e os valores totais por categoria
        $this->categoriesData = $categoriesWithTransactionsCollection->map(function ($category) use ($processedInvoices) {
            $categoryId = is_array($category) ? ($category['id_category'] ?? null) : $category->id_category;
            $categoryName = is_array($category) ? ($category['name'] ?? '') : $category->name;
            $categoryTotal = $processedInvoices->where('category_id', $categoryId)->sum(function($invoice) {
                return abs($invoice->value);
            });
            return [
                'label' => $categoryName,
                'value' => $categoryTotal,
            ];
        })->values()->toArray();

        // Converter as categorias para array para o Livewire
        $this->categoriesWithTransactions = $categoriesWithTransactionsCollection->toArray();

        // Converter de volta para array para o Livewire DEPOIS de todos os cálculos
        $this->invoices = $processedInvoices->toArray();
    }

    public function changeMonth($month)
    {
        $this->currentMonth = $month;
        $this->loadData();
    }

    public function changeBank($newBankId)
    {
        return redirect()->route('invoices.index', ['bankId' => $newBankId]);
    }

    public function confirmDelete($invoiceId)
    {
        $this->deletingInvoice = Invoice::find($invoiceId);
        $this->showDeleteModal = true;
    }

    public function deleteInvoice()
    {
        if ($this->deletingInvoice) {
            $this->deletingInvoice->delete();
            $this->showDeleteModal = false;
            $this->deletingInvoice = null;
            $this->loadData();
            session()->flash('success', 'Transação excluída com sucesso!');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletingInvoice = null;
    }

    #[On('invoice-updated')]
    public function handleInvoiceUpdated()
    {
        $this->loadData();
    }

    #[On('invoice-created')]
    public function handleInvoiceCreated()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.invoices.invoices-index');
    }
}
