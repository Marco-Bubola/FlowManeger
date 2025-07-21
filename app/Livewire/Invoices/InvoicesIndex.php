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
    public int $month;
    public int $year;
    public $currentMonth;
    public $currentStartDate;
    public $currentEndDate;
    public $previousMonthDate;
    public $nextMonthDate;
    public $previousMonthName;
    public $nextMonthName;
    public $currentMonthName;

    // Propriedades para o calendário
    public $calendarData = [];
    public $calendarDays = [];
    public $calendarInvoices = [];
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

        // Inicializa o mês e o ano com os valores atuais
        $this->month = now()->month;
        $this->year = now()->year;
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

            // Preparar dados do calendário
            $this->prepareCalendarData();

            // Log temporário
            session()->flash('debug_info', "Dados carregados para: " . $this->month . "/" . $this->year . " - Total invoices: " . count($this->invoices));

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao carregar os dados: ' . $e->getMessage());
        }
    }    private function calculateDateRanges()
    {
        // Usar month e year para o calendário
        $this->currentStartDate = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $this->currentEndDate = $this->currentStartDate->copy()->endOfMonth()->endOfDay();

        $this->previousMonthDate = $this->currentStartDate->copy()->subMonth()->format('Y-m-d');
        $this->nextMonthDate = $this->currentStartDate->copy()->addMonth()->format('Y-m-d');
        $this->previousMonthName = Carbon::parse($this->previousMonthDate)->locale('pt_BR')->isoFormat('MMMM');
        $this->nextMonthName = Carbon::parse($this->nextMonthDate)->locale('pt_BR')->isoFormat('MMMM');

        Carbon::setLocale('pt_BR');
        $this->currentMonthName = ucfirst($this->currentStartDate->translatedFormat('F Y'));
    }

    private function loadInvoices()
    {
        // Primeiro, carregar todas as invoices do mês para o calendário
        $allMonthInvoices = Invoice::with(['category', 'client'])
            ->where('id_bank', $this->bank->id_bank)
            ->whereBetween('invoice_date', [$this->currentStartDate, $this->currentEndDate])
            ->orderBy('invoice_date', 'asc')
            ->get();

        // Agrupar todas as invoices do mês por data para uso no calendário
        $this->calendarInvoices = $allMonthInvoices->groupBy(function($invoice) {
            return Carbon::parse($invoice->invoice_date)->format('Y-m-d');
        })->map(function($dayInvoices) {
            return $dayInvoices->map(function($invoice) {
                return [
                    'id' => $invoice->id_invoice ?? $invoice->id,
                    'description' => $invoice->description,
                    'value' => $invoice->value,
                    'date' => $invoice->invoice_date,
                    'category' => $invoice->category ? [
                        'id' => $invoice->category->id_category,
                        'name' => $invoice->category->name,
                        'icon' => $invoice->category->icone ?? null
                    ] : null
                ];
            })->toArray();
        })->toArray();

        // Depois, filtrar as invoices para exibição (se uma data específica foi selecionada)
        if ($this->selectedDate) {
            // Filtrar por data específica para exibição
            $this->invoices = $allMonthInvoices->filter(function($invoice) {
                return Carbon::parse($invoice->invoice_date)->format('Y-m-d') === $this->selectedDate;
            });
        } else {
            // Mostrar todas as invoices do mês
            $this->invoices = $allMonthInvoices;
        }
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
            if (is_array($invoice)) {
                $invoice['type'] = 'despesa';
                return $invoice;
            } else {
                $invoice->type = 'despesa';
                return $invoice;
            }
        });

        // Calcular totais - apenas despesas
        $this->totalReceitas = 0; // Sempre zero pois não há receitas
        $this->totalDespesas = $processedInvoices->sum(function($invoice) {
            $value = is_array($invoice) ? $invoice['value'] : $invoice->value;
            return abs($value);
        });
        $this->saldo = 0 - $this->totalDespesas; // Sempre negativo

        // Estatísticas - calcular ANTES de converter para array
        $this->totalInvoices = $this->totalDespesas;
        $this->highestInvoice = $processedInvoices->sortByDesc(function($invoice) {
            $value = is_array($invoice) ? $invoice['value'] : $invoice->value;
            return abs($value);
        })->first();
        $this->lowestInvoice = $processedInvoices->sortBy(function($invoice) {
            $value = is_array($invoice) ? $invoice['value'] : $invoice->value;
            return abs($value);
        })->first();
        $this->totalTransactions = $processedInvoices->count();

        // Gera os dados diários para o gráfico de linhas
        $dailyData = $processedInvoices->groupBy(function ($invoice) {
            $date = is_array($invoice) ? $invoice['invoice_date'] : $invoice->invoice_date;
            return Carbon::parse($date)->day;
        })->map(function ($dayInvoices) {
            return $dayInvoices->sum(function($invoice) {
                $value = is_array($invoice) ? $invoice['value'] : $invoice->value;
                return abs($value);
            });
        });

        $this->dailyLabels = $dailyData->keys()->toArray();
        $this->dailyValues = $dailyData->values()->toArray();

        // Cria os dados agrupados por data para o calendário (já criado em loadInvoices)
        $this->invoiceDates = $this->calendarInvoices;

        // Agrupa as faturas por categoria
        $this->eventsGroupedByMonthAndCategory = [
            'current' => []
        ];
        foreach ($processedInvoices->groupBy(function($invoice) {
            return is_array($invoice) ? ($invoice['category_id'] ?? null) : $invoice->category_id;
        }) as $categoryId => $categoryInvoices) {
            $this->eventsGroupedByMonthAndCategory['current'][$categoryId] = $categoryInvoices->values()->toArray();
        }

        // Para ser usado no FullCalendar
        $this->eventsDetailed = $processedInvoices->map(function ($invoice) {
            if (is_array($invoice)) {
                return [
                    'id_invoice' => $invoice['id_invoice'] ?? $invoice['id'] ?? null,
                    'title' => $invoice['description'] ?? '',
                    'start' => $invoice['invoice_date'] ?? null,
                    'category' => isset($invoice['category']) ? ($invoice['category']['name'] ?? 'Sem Categoria') : 'Sem Categoria',
                    'installments' => $invoice['installments'] ?? null,
                    'value' => abs($invoice['value'] ?? 0),
                    'type' => 'despesa',
                ];
            } else {
                return [
                    'id_invoice' => $invoice->id_invoice ?? $invoice->id ?? null,
                    'title' => $invoice->description,
                    'start' => $invoice->invoice_date,
                    'category' => optional($invoice->category)->name ?? 'Sem Categoria',
                    'installments' => $invoice->installments,
                    'value' => abs($invoice->value),
                    'type' => 'despesa',
                ];
            }
        })->toArray();

        // Filtra as categorias com base nas transações do mês
        $categoriesWithTransactionsCollection = collect($this->categories)->filter(function ($category) use ($processedInvoices) {
            $categoryId = is_array($category) ? ($category['id_category'] ?? null) : $category->id_category;
            return $processedInvoices->where(function($invoice) use ($categoryId) {
                $invoiceCategoryId = is_array($invoice) ? ($invoice['category_id'] ?? null) : $invoice->category_id;
                return $invoiceCategoryId === $categoryId;
            })->isNotEmpty();
        });

        // Calculando as categorias e os valores totais por categoria
        $this->categoriesData = $categoriesWithTransactionsCollection->map(function ($category) use ($processedInvoices) {
            $categoryId = is_array($category) ? ($category['id_category'] ?? null) : $category->id_category;
            $categoryName = is_array($category) ? ($category['name'] ?? '') : $category->name;
            $categoryTotal = $processedInvoices->where(function($invoice) use ($categoryId) {
                $invoiceCategoryId = is_array($invoice) ? ($invoice['category_id'] ?? null) : $invoice->category_id;
                return $invoiceCategoryId === $categoryId;
            })->sum(function($invoice) {
                $value = is_array($invoice) ? $invoice['value'] : $invoice->value;
                return abs($value);
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

    /**
     * Atualiza dados quando month ou year mudarem via wire:model.live
     */
    public function updatedMonth()
    {
        // Limpar a data selecionada ao mudar o mês via select
        $this->selectedDate = null;
        $this->loadData();
    }

    public function updatedYear()
    {
        // Limpar a data selecionada ao mudar o ano via select
        $this->selectedDate = null;
        $this->loadData();
    }

    public function changeMonth($month)
    {
        $this->currentMonth = $month;
        $this->loadData();
    }

    /**
     * Método para ir para o mês anterior.
     */
    public function previousMonth()
    {
        $this->selectedDate = null;

        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }

        // Feedback temporário
        session()->flash('message', "Navegou para: " . $this->month . "/" . $this->year);

        $this->loadData();
    }

    /**
     * Método para ir para o próximo mês.
     */
    public function nextMonth()
    {
        $this->selectedDate = null;

        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }

        // Feedback temporário
        session()->flash('message', "Navegou para: " . $this->month . "/" . $this->year);

        $this->loadData();
    }

    /**
     * Seleciona um dia específico no calendário.
     */
    public function selectDate($date): void
    {
        if ($this->selectedDate === $date) {
            // Se clicar no mesmo dia, desmarca a seleção
            $this->selectedDate = null;
        } else {
            $this->selectedDate = $date;
        }
        $this->loadData();
    }

    /**
     * Limpa a seleção de data.
     */
    public function clearDateSelection(): void
    {
        $this->selectedDate = null;
        $this->loadData();
    }

    /**
     * Prepara os dados do calendário para o mês selecionado.
     */
    private function prepareCalendarData(): void
    {
        // Obter o primeiro dia do mês
        $firstDayOfMonth = Carbon::create($this->year, $this->month, 1);

        // Obter o último dia do mês
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        // Obter o primeiro dia da semana (domingo = 0, segunda = 1, etc.)
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;

        // Obter o último dia da semana
        $lastDayOfWeek = $lastDayOfMonth->dayOfWeek;

        // Calcular quantos dias precisamos mostrar antes do primeiro dia do mês
        $daysBeforeMonth = $firstDayOfWeek;

        // Calcular quantos dias precisamos mostrar depois do último dia do mês
        $daysAfterMonth = 6 - $lastDayOfWeek;

        // Criar array com todos os dias do calendário
        $calendarDays = [];

        // Adicionar dias do mês anterior
        for ($i = $daysBeforeMonth - 1; $i >= 0; $i--) {
            $day = $firstDayOfMonth->copy()->subDays($i + 1);
            $calendarDays[] = [
                'date' => $day->format('Y-m-d'),
                'day' => $day->day,
                'isCurrentMonth' => false,
                'isToday' => $day->isToday(),
                'invoices' => []
            ];
        }

        // Adicionar dias do mês atual
        for ($day = 1; $day <= $lastDayOfMonth->day; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $dateString = $date->format('Y-m-d');

            // Pegar invoices para este dia específico
            $dayInvoices = isset($this->calendarInvoices[$dateString]) ? $this->calendarInvoices[$dateString] : [];

            $calendarDays[] = [
                'date' => $dateString,
                'day' => $day,
                'isCurrentMonth' => true,
                'isToday' => $date->isToday(),
                'invoices' => $dayInvoices
            ];
        }

        // Adicionar dias do próximo mês
        for ($i = 1; $i <= $daysAfterMonth; $i++) {
            $day = $lastDayOfMonth->copy()->addDays($i);
            $calendarDays[] = [
                'date' => $day->format('Y-m-d'),
                'day' => $day->day,
                'isCurrentMonth' => false,
                'isToday' => $day->isToday(),
                'invoices' => []
            ];
        }

        $this->calendarDays = $calendarDays;

        // Log temporário
        session()->flash('calendar_debug', "Calendário preparado - Total dias: " . count($calendarDays) . " - Mês: " . $this->month . "/" . $this->year . " - Total invoices no calendário: " . count($this->calendarInvoices));
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
