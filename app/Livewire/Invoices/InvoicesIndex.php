<?php

namespace App\Livewire\Invoices;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        // Verificar se há parâmetros de retorno na query string
        $returnMonth = request()->query('return_month');
        $returnYear = request()->query('return_year');

        // Inicializa o mês e o ano com os valores atuais
        $this->month = now()->month;
        $this->year = now()->year;
        $this->currentMonth = request()->query('month', now()->format('Y-m-d'));

        // Se veio de uma edição com mês/ano específico, ajustar
        if ($returnMonth && $returnYear) {
            $this->month = (int)$returnMonth;
            $this->year = (int)$returnYear;
        }

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

            // debug flashes removed

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao carregar os dados: ' . $e->getMessage());
        }
    }

    private function calculateDateRanges()
    {
        // Obter o dia de início e fim do ciclo do banco/cartão
        // Se start_date e end_date são datas, extrair apenas o dia
        $startDay = $this->bank->start_date ? Carbon::parse($this->bank->start_date)->day : 1;
        $endDay = $this->bank->end_date ? Carbon::parse($this->bank->end_date)->day : Carbon::create($this->year, $this->month, 1)->endOfMonth()->day;

        // Calcular as datas de início e fim do ciclo de fatura atual
        // Se o dia de início é maior que o dia de fim, o ciclo passa de um mês para o próximo
        if ($startDay > $endDay) {
            // Exemplo: dia 6 até dia 5 do próximo mês
            // Se estamos vendo o ciclo de Janeiro 2025, mostramos de 06/Jan até 05/Fev
            $this->currentStartDate = Carbon::create($this->year, $this->month, $startDay)->startOfDay();
            $this->currentEndDate = Carbon::create($this->year, $this->month, $startDay)->addMonth()->day($endDay)->endOfDay();
        } else {
            // Ciclo normal dentro do mesmo mês
            $this->currentStartDate = Carbon::create($this->year, $this->month, $startDay)->startOfDay();
            $this->currentEndDate = Carbon::create($this->year, $this->month, $endDay)->endOfDay();
        }

        // Calcular o ciclo anterior e próximo
        $this->previousMonthDate = $this->currentStartDate->copy()->subMonth()->format('Y-m-d');
        $this->nextMonthDate = $this->currentStartDate->copy()->addMonth()->format('Y-m-d');

        Carbon::setLocale('pt_BR');
        $this->previousMonthName = Carbon::parse($this->previousMonthDate)->locale('pt_BR')->isoFormat('MMMM');
        $this->nextMonthName = Carbon::parse($this->nextMonthDate)->locale('pt_BR')->isoFormat('MMMM');

        // Nome do ciclo atual (ex: "Fatura Jan 2025 (06/Jan - 05/Fev)")
        if ($startDay > $endDay) {
            // Ciclo que abrange dois meses
            $this->currentMonthName = sprintf(
                'Fatura %s - %s (dia %d até dia %d)',
                ucfirst($this->currentStartDate->translatedFormat('M/Y')),
                ucfirst($this->currentEndDate->translatedFormat('M/Y')),
                $startDay,
                $endDay
            );
        } else {
            // Ciclo dentro do mesmo mês
            $this->currentMonthName = sprintf(
                'Fatura %s (dia %d até dia %d)',
                ucfirst($this->currentStartDate->translatedFormat('M/Y')),
                $startDay,
                $endDay
            );
        }
    }

    private function loadInvoices()
    {
        // Debug: Log das datas sendo usadas
        Log::info('Loading invoices', [
            'start_date' => $this->currentStartDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->currentEndDate?->format('Y-m-d H:i:s'),
            'bank_id' => $this->bank->id_bank
        ]);

        // Primeiro, carregar todas as invoices do mês para o calendário
        $allMonthInvoices = Invoice::with(['category', 'client'])
            ->where('id_bank', $this->bank->id_bank)
            ->whereBetween('invoice_date', [$this->currentStartDate, $this->currentEndDate])
            ->orderBy('invoice_date', 'asc')
            ->get();

        Log::info('Invoices loaded', ['count' => $allMonthInvoices->count()]);

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
     * Método para ir para o ciclo de fatura anterior.
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

        $this->loadData();
    }

    /**
     * Método para ir para o próximo ciclo de fatura.
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
     * Prepara os dados do calendário baseado no ciclo de fatura do cartão.
     */
    private function prepareCalendarData(): void
    {
        // Usar as datas de início e fim do ciclo de fatura (já calculadas em calculateDateRanges)
        $firstDayOfCycle = $this->currentStartDate->copy();
        $lastDayOfCycle = $this->currentEndDate->copy();

        // Obter o primeiro dia da semana do ciclo (domingo = 0, segunda = 1, etc.)
        $firstDayOfWeek = $firstDayOfCycle->dayOfWeek;

        // Obter o último dia da semana do ciclo
        $lastDayOfWeek = $lastDayOfCycle->dayOfWeek;

        // Calcular quantos dias precisamos mostrar antes do primeiro dia do ciclo
        $daysBeforeCycle = $firstDayOfWeek;

        // Calcular quantos dias precisamos mostrar depois do último dia do ciclo
        $daysAfterCycle = 6 - $lastDayOfWeek;

        // Criar array com todos os dias do calendário
        $calendarDays = [];

        // Adicionar dias antes do ciclo
        for ($i = $daysBeforeCycle - 1; $i >= 0; $i--) {
            $day = $firstDayOfCycle->copy()->subDays($i + 1);
            $calendarDays[] = [
                'date' => $day->format('Y-m-d'),
                'day' => $day->day,
                'isCurrentMonth' => false,
                'isToday' => $day->isToday(),
                'invoices' => []
            ];
        }

        // Adicionar todos os dias do ciclo de fatura
        $currentDay = $firstDayOfCycle->copy();
        while ($currentDay->lte($lastDayOfCycle)) {
            $dateString = $currentDay->format('Y-m-d');

            // Pegar invoices para este dia específico
            $dayInvoices = isset($this->calendarInvoices[$dateString]) ? $this->calendarInvoices[$dateString] : [];

            $calendarDays[] = [
                'date' => $dateString,
                'day' => $currentDay->day,
                'isCurrentMonth' => true, // Dentro do ciclo de fatura
                'isToday' => $currentDay->isToday(),
                'invoices' => $dayInvoices
            ];

            $currentDay->addDay();
        }

        // Adicionar dias depois do ciclo
        for ($i = 1; $i <= $daysAfterCycle; $i++) {
            $day = $lastDayOfCycle->copy()->addDays($i);
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
        // calendar debug removed
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

    /**
     * Alterna o modo de visualização entre 'cards' e 'list'.
     */
    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'cards' ? 'list' : 'cards';
        // Opcional: recarregar dados ou disparar evento
        $this->dispatch('view-mode-changed', ['mode' => $this->viewMode]);
    }

    /**
     * Define explicitamente o modo de visualização.
     * @param string $mode
     */
    public function setViewMode(string $mode): void
    {
        if (!in_array($mode, ['cards', 'list'])) {
            return;
        }

        if ($this->viewMode === $mode) {
            return;
        }

        $this->viewMode = $mode;
        $this->dispatch('view-mode-changed', ['mode' => $this->viewMode]);
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
