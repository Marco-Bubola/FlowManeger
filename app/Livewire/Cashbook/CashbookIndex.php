<?php

namespace App\Livewire\Cashbook;

use App\Models\Cashbook;
use App\Models\Category;
use App\Models\Type;
use App\Models\Segment;
use App\Models\Client;
use App\Models\Cofrinho;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class CashbookIndex extends Component
{
    use WithPagination;

    // Propriedades do estado para o filtro
    public int $month;
    public int $year;

    // Propriedades reativas para os dados
    public $transactions = [];
    public $transactionsByCategory = [];
    public $totals = [];
    public $categories = [];
    public $types = [];
    public $segments = [];
    public $clients = [];
    public $cofrinhos = [];
    public string $monthName = '';
    public string $currentMonth = '';
    public $calendarData = [];

    // Propriedades para os dados dos meses anterior e próximo
    public $prevMonth = [];
    public $nextMonth = [];

    // Propriedades para filtros
    public string $search = '';
    public string $categoryFilter = '';
    public string $typeFilter = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public string $segmentFilter = '';
    public string $cofrinhoFilter = '';
    public string $dateStart = '';
    public string $dateEnd = '';
    public int $perPage = 15;

    // Propriedade para data selecionada no calendário
    public ?string $selectedDate = null;

    // Resumos para exibição no header
    public int $transactionsCount = 0;
    public float $totalBalance = 0.0;

    // Modal de exclusão
    public ?Cashbook $deletingTransaction = null;
    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'clientFilter' => ['except' => ''],
        'segmentFilter' => ['except' => ''],
        'cofrinhoFilter' => ['except' => ''],
        'dateStart' => ['except' => ''],
        'dateEnd' => ['except' => ''],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public bool $showTipsModal = false;
    public $categoriesChartData = [];

    public function toggleTips()
    {
        $this->showTipsModal = !$this->showTipsModal;
    }

    public function mount(): void
    {
        // Verificar se há parâmetros de retorno na URL
        $returnMonth = request()->query('return_month');
        $returnYear = request()->query('return_year');

        // Se houver parâmetros, usar eles; senão, usar mês/ano atual
        if ($returnMonth && $returnYear) {
            $this->month = (int) $returnMonth;
            $this->year = (int) $returnYear;
        } else {
            $this->month = now()->month;
            $this->year = now()->year;
        }

        $this->currentMonth = Carbon::create($this->year, $this->month, 1)->format('Y-m');
        $this->monthName = Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');

        $this->loadData();
    }

    public function loadData(): void
    {
        $this->loadTransactions();
        $this->loadCategories();
        $this->loadTypes();
        $this->loadSegments();
        $this->loadClients();
        $this->loadCofrinhos();
        $this->loadAdjacentMonths();
        $this->loadCalendarData();
        
        // Prepare chart data and dispatch event
        $this->prepareChartData();
        $this->dispatch('cashbook-chart-updated', data: $this->categoriesChartData);
    }

    public function prepareChartData(): void
    {
        $this->categoriesChartData = collect($this->transactionsByCategory)
            ->map(function ($categoryGroup) {
                return [
                    'label' => $categoryGroup['name'] ?? 'Sem categoria',
                    'value' => (float) ($categoryGroup['total'] ?? 0),
                    'color' => $categoryGroup['color'] ?? '#667eea',
                ];
            })
            ->values()
            ->toArray();
    }

    public function loadCalendarData(): void
    {
        $date = Carbon::create($this->year, $this->month, 1);

        // Obter todas as transações do mês para o calendário
        $transactions = Cashbook::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        // Agrupar transações por dia
        $transactionsByDay = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->date)->format('Y-m-d');
        });

        // Preparar dados do calendário
        $this->calendarData = [];

        // Primeiro dia do mês e último dia
        $firstDay = $date->copy()->startOfMonth();
        $lastDay = $date->copy()->endOfMonth();

        // Primeiro dia da semana (0 = domingo)
        $startOfWeek = $firstDay->copy()->startOfWeek(Carbon::SUNDAY);

        // Último dia da semana
        $endOfWeek = $lastDay->copy()->endOfWeek(Carbon::SATURDAY);

        // Criar as semanas do calendário
        $weeks = [];
        $currentWeek = [];

        for ($day = $startOfWeek->copy(); $day <= $endOfWeek; $day->addDay()) {
            $dayNumber = $day->format('j');
            $isCurrentMonth = $day->month === $date->month;
            $dateString = $day->format('Y-m-d');

            $dayData = [
                'day' => $dayNumber,
                'date' => $dateString,
                'is_current_month' => $isCurrentMonth,
                'is_today' => $day->isToday(),
                'is_weekend' => $day->isWeekend(),
                'has_income' => false,
                'has_expense' => false,
                'total_income' => 0,
                'total_expense' => 0,
                'transaction_count' => 0
            ];

            if ($isCurrentMonth && isset($transactionsByDay[$dateString])) {
                $dayTransactions = $transactionsByDay[$dateString];
                $dayData['transaction_count'] = $dayTransactions->count();
                $dayData['has_income'] = $dayTransactions->where('type_id', 1)->count() > 0;
                $dayData['has_expense'] = $dayTransactions->where('type_id', 2)->count() > 0;
                $dayData['total_income'] = $dayTransactions->where('type_id', 1)->sum('value');
                $dayData['total_expense'] = $dayTransactions->where('type_id', 2)->sum('value');
            }

            $currentWeek[] = $dayData;

            if ($day->dayOfWeek === Carbon::SATURDAY) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }

        $this->calendarData = $weeks;
    }

    /**
     * Gera os dados do calendário no formato esperado pela view
     */
    public function getCalendarDaysProperty()
    {
        $date = Carbon::create($this->year, $this->month, 1);
        $firstDay = $date->copy()->startOfMonth();
        $lastDay = $date->copy()->endOfMonth();
        $startOfWeek = $firstDay->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $lastDay->copy()->endOfWeek(Carbon::SATURDAY);

        // Obter todas as transações do mês
        $transactions = Cashbook::where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        // Agrupar por data
        $transactionsByDate = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->date)->format('Y-m-d');
        });

        $calendarDays = [];

        for ($day = $startOfWeek->copy(); $day <= $endOfWeek; $day->addDay()) {
            $dateString = $day->format('Y-m-d');
            $isCurrentMonth = $day->month === $date->month;
            $hasTransactions = isset($transactionsByDate[$dateString]);

            $calendarDays[] = [
                'day' => $isCurrentMonth ? $day->day : null,
                'date' => $dateString,
                'isToday' => $day->isToday(),
                'isSelected' => $this->dateStart === $dateString,
                'hasTransactions' => $hasTransactions && $isCurrentMonth,
                'transactionsCount' => $hasTransactions ? $transactionsByDate[$dateString]->count() : 0,
                'isCurrentMonth' => $isCurrentMonth,
            ];
        }

        return $calendarDays;
    }

    public function loadCategories(): void
    {
        $this->categories = Category::where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->orderBy('name')
            ->get();
    }

    public function loadTypes(): void
    {
        $this->types = Type::orderBy('desc_type')->get();
    }

    public function loadSegments(): void
    {
        $this->segments = Segment::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function loadClients(): void
    {
        $this->clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function loadCofrinhos(): void
    {
        $this->cofrinhos = Cofrinho::where('user_id', Auth::id())
            ->orderBy('nome')
            ->get();
    }

    public function loadAdjacentMonths(): void
    {
        $date = Carbon::create($this->year, $this->month, 1);

        // Calcular dados do mês anterior
        $prevDate = (clone $date)->subMonth();
        $prevMonthName = $prevDate->translatedFormat('F Y');
        $prevTransactions = Cashbook::where('user_id', Auth::id())
            ->whereYear('date', $prevDate->year)
            ->whereMonth('date', $prevDate->month)
            ->get();
        $this->prevMonth = [
            'name' => $prevMonthName,
            'balance' => $prevTransactions->where('type_id', 1)->sum('value') - $prevTransactions->where('type_id', 2)->sum('value'),
        ];

        // Calcular dados do próximo mês
        $nextDate = (clone $date)->addMonth();
        $nextMonthName = $nextDate->translatedFormat('F Y');
        $nextTransactions = Cashbook::where('user_id', Auth::id())
            ->whereYear('date', $nextDate->year)
            ->whereMonth('date', $nextDate->month)
            ->get();
        $this->nextMonth = [
            'name' => $nextMonthName,
            'balance' => $nextTransactions->where('type_id', 1)->sum('value') - $nextTransactions->where('type_id', 2)->sum('value'),
        ];
    }

    public function loadTransactions(): void
    {
        $date = Carbon::create($this->year, $this->month, 1);

        // Obter transações do mês selecionado para o usuário logado
        $transactionsQuery = Cashbook::with(['category', 'client', 'cofrinho'])
            ->where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month);

        // Aplicar filtros
        if ($this->categoryFilter) {
            $transactionsQuery->where('category_id', $this->categoryFilter);
        }

        if ($this->typeFilter) {
            $transactionsQuery->where('type_id', $this->typeFilter);
        }

        if ($this->clientFilter) {
            $transactionsQuery->where('client_id', $this->clientFilter);
        }

        if ($this->cofrinhoFilter) {
            $transactionsQuery->where('cofrinho_id', $this->cofrinhoFilter);
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'pending') {
                $transactionsQuery->where('is_pending', true);
            } elseif ($this->statusFilter === 'confirmed') {
                $transactionsQuery->where('is_pending', false);
            }
        }

        // Aplicar filtro de data específica se definido
        if ($this->dateStart && $this->dateEnd) {
            $transactionsQuery->whereBetween('date', [$this->dateStart, $this->dateEnd]);
        }

        $transactions = $transactionsQuery->orderBy('date', 'desc')->get();

        // Aplicar filtros
        if ($this->categoryFilter) {
            $transactionsQuery->where('category_id', $this->categoryFilter);
        }

        if ($this->typeFilter) {
            $transactionsQuery->where('type_id', $this->typeFilter);
        }

        if ($this->clientFilter) {
            $transactionsQuery->where('client_id', $this->clientFilter);
        }

        if ($this->cofrinhoFilter) {
            $transactionsQuery->where('cofrinho_id', $this->cofrinhoFilter);
        }

        $transactions = $transactionsQuery->orderBy('date', 'desc')->get();

        // Calcular totais do mês selecionado
        $this->totals = [
            'income' => $transactions->where('type_id', 1)->sum('value'),
            'expense' => $transactions->where('type_id', 2)->sum('value'),
            'balance' => $transactions->where('type_id', 1)->sum('value') - $transactions->where('type_id', 2)->sum('value'),
        ];

        // Agrupar transações por categoria no formato esperado pelo blade
        $totalGeral = $transactions->sum(function($t) { return abs($t->value); });
        $grouped = $transactions->groupBy(function ($transaction) {
            return $transaction->category_id ?: 'sem_categoria';
        });

        $transactionsByCategory = [];
        foreach ($grouped as $catId => $group) {
            $first = $group->first();
            $category = $first->category;

            // Calcular total da categoria
            $categoryTotal = $group->sum(function($t) { return abs($t->value); });
            $percentage = $totalGeral > 0 ? ($categoryTotal / $totalGeral) * 100 : 0;

            $transactionsByCategory[] = [
                'category_id' => $catId,
                'name' => $category->name ?? 'Sem Categoria',
                'color' => $category->hexcolor_category ?? '#667eea',
                'icon' => $category->icone ?? 'fas fa-tag',
                'count' => $group->count(),
                'total' => $categoryTotal,
                'percentage' => $percentage,
                'transactions' => $group->map(function ($transaction) {
                    $category = $transaction->category;
                    return [
                        'id' => $transaction->id,
                        'description' => $transaction->description,
                        'value' => $transaction->value,
                        'type_id' => $transaction->type_id,
                        'date' => $transaction->date,
                        'time' => Carbon::parse($transaction->date)->format('d/m'),
                        'category_id' => $transaction->category_id,
                        'category_name' => $category->name ?? 'Sem Categoria',
                        'category_hexcolor_category' => $category->hexcolor_category ?? '#cccccc',
                        'category_icone' => $category->icone ?? 'fas fa-question',
                        'note' => $transaction->note,
                        'client_id' => $transaction->client_id,
                        'client_name' => $transaction->client->name ?? null,
                        'cofrinho_id' => $transaction->cofrinho_id,
                        'cofrinho_name' => $transaction->cofrinho->name ?? null,
                        'is_pending' => $transaction->is_pending,
                    ];
                })->values()->toArray(),
            ];
        }

        $this->transactionsByCategory = $transactionsByCategory;

        // Atualizar propriedades
        $this->currentMonth = $date->format('Y-m');
        $this->monthName = $date->translatedFormat('F Y');

        // Calcular categorias para gráficos
        $this->categories = [
            'income' => $transactions->where('type_id', 1)
                ->groupBy('category_id')
                ->map(function ($group) {
                    $category = $group->first()->category;
                    return [
                        'name' => $category->name ?? 'Sem Categoria',
                        'total' => $group->sum('value'),
                        'hexcolor_category' => $category->hexcolor_category ?? '#cccccc',
                    ];
                })->values(),
            'expense' => $transactions->where('type_id', 2)
                ->groupBy('category_id')
                ->map(function ($group) {
                    $category = $group->first()->category;
                    return [
                        'name' => $category->name ?? 'Sem Categoria',
                        'total' => $group->sum('value'),
                        'hexcolor_category' => $category->hexcolor_category ?? '#cccccc',
                    ];
                })->values(),
        ];

    // Atualizar resumos para o header
    $this->transactionsCount = $transactions->count();
    $this->totalBalance = $this->totals['balance'] ?? 0;
    }

    public function changeMonth(string $direction): void
    {
        if ($direction === 'previous') {
            $this->previousMonth();
        } elseif ($direction === 'next') {
            $this->nextMonth();
        }
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->loadData();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->loadData();
    }

    public function selectDay($date): void
    {
        // Aplicar filtro de data específica
        $this->dateStart = $date;
        $this->dateEnd = $date;
        $this->loadData();
    }

    public function selectDate($date): void
    {
        // Aplicar filtro de data específica e atualizar selectedDate
        $this->selectedDate = $date;
        $this->dateStart = $date;
        $this->dateEnd = $date;
        $this->loadData();
    }

    public function clearDateFilter(): void
    {
        $this->selectedDate = null;
        $this->dateStart = '';
        $this->dateEnd = '';
        $this->loadData();
    }

    public function confirmDelete($transactionId): void
    {
        $this->deletingTransaction = Cashbook::find($transactionId);
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingTransaction = null;
        $this->showDeleteModal = false;
    }

    public function deleteTransaction(): void
    {
        if ($this->deletingTransaction) {
            $this->deletingTransaction->delete();
            $this->showDeleteModal = false;
            $this->deletingTransaction = null;
            $this->loadData();

            session()->flash('success', 'Transação excluída com sucesso!');
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->clientFilter = '';
        $this->segmentFilter = '';
        $this->cofrinhoFilter = '';
        $this->dateStart = '';
        $this->dateEnd = '';
        $this->resetPage();
        $this->loadData();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['categoryFilter', 'typeFilter', 'statusFilter', 'clientFilter', 'segmentFilter', 'cofrinhoFilter', 'dateStart', 'dateEnd'])) {
            $this->resetPage();
            $this->loadData();
        }
    }

    #[On('transaction-created')]
    public function refreshTransactions(): void
    {
        $this->loadData();
    }

    #[On('transaction-updated')]
    public function refreshTransactionsAfterUpdate(): void
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.cashbook.cashbook-index');
    }
}
