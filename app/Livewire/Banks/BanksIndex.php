<?php

namespace App\Livewire\Banks;

use App\Models\Bank;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\On;

class BanksIndex extends Component
{
    // Propriedades do estado para o filtro
    public int $month;
    public int $year;

    // Propriedades reativas para os dados - convertidas para arrays
    public $banks = [];
    public $groupedInvoices = [];
    public $allInvoices = [];
    public $invoicesByCategory = [];
    public float $totalMonth = 0;
    public $highestInvoice = null;
    public $lowestInvoice = null;
    public int $totalTransactions = 0;

    // Propriedades para o calendário
    public $calendarData = [];
    public $calendarDays = [];
    public $calendarInvoices = [];
    public $selectedDate = null;

    // Propriedades para o modal de edição
    public ?Bank $editingBank = null;
    public bool $showEditModal = false;

    // Propriedades para o modal de exclusão
    public ?Bank $deletingBank = null;
    public bool $showDeleteModal = false;

    // Propriedade que não deve ser serializada pelo Livewire
    protected $bankIcons;

    public $pieData = [];

    public function mount(): void
    {
        // Inicializa o mês e o ano com os valores atuais ou da requisição
        $this->month = now()->month;
        $this->year = now()->year;

        // Define os ícones dos bancos como array (não Collection)
        $this->bankIcons = [
            ['name' => 'Nubank', 'icon' => asset('assets/img/banks/nubank.svg')],
            ['name' => 'Inter', 'icon' => asset('assets/img/banks/inter.png')],
            ['name' => 'Santander', 'icon' => asset('assets/img/banks/santander.png')],
            ['name' => 'Itaú', 'icon' => asset('assets/img/banks/itau.png')],
            ['name' => 'Banco do Brasil', 'icon' => asset('assets/img/banks/bb.png')],
            ['name' => 'Caixa', 'icon' => asset('assets/img/banks/caixa.png')],
            ['name' => 'Bradesco', 'icon' => asset('assets/img/banks/bradesco.png')],
        ];

        // Carrega os dados iniciais
        $this->loadData();
    }

    /**
     * Carrega os dados de bancos e faturas com base no mês e ano.
     */
    public function loadData(): void
    {
        // Obtendo apenas os bancos do usuário logado e convertendo para array
        $banksCollection = Bank::where('user_id', Auth::id())->get();
        $this->banks = $banksCollection->toArray();

        // Validando os valores de mês e ano
        $month = max(1, min(12, $this->month));
        $year = max(1900, min(now()->year + 1, $this->year));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        // Obtendo as transações do usuário logado para o mês e ano selecionados
        $invoices = Invoice::where('user_id', Auth::id())
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->with(['bank', 'category'])
            ->orderBy('invoice_date', 'asc')
            ->get();

        // Otimização: Agrupar faturas por data UMA VEZ para reutilização no calendário.
        $invoicesByDate = $invoices->groupBy(fn ($invoice) => Carbon::parse($invoice->invoice_date)->format('Y-m-d'));

        // Calculando as métricas e convertendo para arrays
        $this->groupedInvoices = $invoicesByDate->toArray();

        $displayInvoices = $invoices;

        if ($this->selectedDate) {
            $displayInvoices = $invoices->filter(function ($invoice) {
                return Carbon::parse($invoice->invoice_date)->format('Y-m-d') === $this->selectedDate;
            });
        }

        $this->allInvoices = $displayInvoices->map(fn ($invoice) => $this->mapInvoice($invoice))->toArray();
        $this->invoicesByCategory = $this->groupInvoicesByCategory($displayInvoices);

        $this->pieData = collect($this->invoicesByCategory)
            ->map(function ($group) {
                return [
                    'label' => $group['category']['name'] ?? 'Sem Categoria',
                    'value' => $group['total'] ?? 0,
                    'color' => $group['category']['hexcolor_category'] ?? '#6366f1',
                ];
            })
            ->filter(fn ($item) => ($item['value'] ?? 0) > 0)
            ->values()
            ->toArray();

        $this->dispatch('update-pie-chart', data: $this->pieData);

        $this->totalMonth = $invoices->sum('value');

        $highestInvoice = $invoices->sortByDesc('value')->first();
        $this->highestInvoice = $highestInvoice ? $highestInvoice->toArray() : null;

        $lowestInvoice = $invoices->sortBy('value')->first();
        $this->lowestInvoice = $lowestInvoice ? $lowestInvoice->toArray() : null;

        $this->totalTransactions = $invoices->count();

        // Preparar dados do calendário, passando a coleção já agrupada por data para melhor performance.
        $this->prepareCalendarData($year, $month, $invoicesByDate);

        // Pie chart data: gastos por categoria
    }

    private function mapInvoice($invoice): array
    {
        return [
            'id' => $invoice->id,
            'description' => $invoice->description,
            'value' => $invoice->value,
            'date' => $invoice->invoice_date,
            'bank' => $invoice->bank ? [
                'id' => $invoice->bank->id_bank,
                'name' => $invoice->bank->name,
                'icon' => $invoice->bank->caminho_icone
            ] : null,
            'category' => $invoice->category ? [
                'id_category' => $invoice->category->id_category,
                'name' => $invoice->category->name,
                'hexcolor_category' => $invoice->category->hexcolor_category ?? null,
                'icone' => $invoice->category->icone ?? null,
            ] : null,
        ];
    }

    private function groupInvoicesByCategory($invoices): array
    {
        if ($invoices->isEmpty()) {
            return [];
        }

        return $invoices->groupBy(function ($invoice) {
            return optional($invoice->category)->id_category ?? 'sem_categoria';
        })->map(function ($group, $categoryId) {
            $first = $group->first();
            $category = $first->category ?? null;

            $categoryName = $category->name ?? 'Sem Categoria';
            $categoryColor = $category->hexcolor_category ?? '#64748b';
            $categoryIcon = $category->icone ?? 'fas fa-tag';

            $formattedInvoices = $group->map(fn ($invoice) => $this->mapInvoice($invoice))->toArray();

            return [
                'category_id' => $categoryId,
                'category' => [
                    'name' => $categoryName,
                    'hexcolor_category' => $categoryColor,
                    'icone' => $categoryIcon,
                ],
                'total' => $group->sum(fn ($invoice) => abs($invoice->value)),
                'invoices' => $formattedInvoices,
            ];
        })->values()->sortByDesc('total')->values()->toArray();
    }
    /**
     * Prepara os dados do calendário para o mês selecionado.
     */
    private function prepareCalendarData(int $year, int $month, $invoicesByDate): void
    {
        // Obter o primeiro dia do mês
        $firstDayOfMonth = Carbon::create($year, $month, 1);

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
            $date = Carbon::create($year, $month, $day);
            $dateString = $date->format('Y-m-d');

            // Otimização: Em vez de filtrar a coleção inteira 30x, apenas pegamos o grupo já existente.
            $dayInvoices = $invoicesByDate->get($dateString, collect());

            $calendarDays[] = [
                'date' => $dateString,
                'day' => $day,
                'isCurrentMonth' => true,
                'isToday' => $date->isToday(),
                'invoices' => $dayInvoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'description' => $invoice->description,
                        'value' => $invoice->value,
                        'date' => $invoice->invoice_date,
                        'bank' => $invoice->bank ? [
                            'id' => $invoice->bank->id_bank,
                            'name' => $invoice->bank->name,
                            'icon' => $invoice->bank->caminho_icone
                        ] : null,
                        'category' => $invoice->category ? [
                            'id' => $invoice->category->id_category,
                            'name' => $invoice->category->name,
                            'icon' => $invoice->category->icone ?? null
                        ] : null
                    ];
                })->toArray()
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

        // Agrupar invoices por data para o calendário
        // Otimização: Reutilizar os dados já formatados em $this->allInvoices e agrupá-los.
        $this->calendarInvoices = collect($this->allInvoices)->groupBy('date')->toArray();
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
        $this->loadData(); // CORREÇÃO: Recarregar os dados para exibir o mês inteiro novamente.
    }

    /**
     * Método para ir para o mês anterior.
     */
    public function previousMonth(): void
    {
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
        $this->loadData();
    }

    /**
     * Método para ir para o próximo mês.
     */
    public function nextMonth(): void
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
        $this->loadData();
    }

    /**
     * Deleta um banco e suas faturas relacionadas.
     */
    public function destroyBank(int $id): void
    {
        $bank = Bank::findOrFail($id);

        // Verifica se o banco pertence ao usuário logado para evitar exclusão indevida
        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Excluir todas as faturas relacionadas ao banco
        Invoice::where('id_bank', $bank->id_bank)->delete();

        // Excluir o banco
        $bank->delete();

        // Fecha o modal de exclusão
        $this->closeDeleteModal();

        // Recarrega os dados após a exclusão
        $this->loadData();

        // Emite um evento para mostrar uma notificação de sucesso
        Session::flash('success', 'Cartão e suas faturas foram excluídos com sucesso.');
    }



    /**
     * Fecha o modal de edição.
     */
    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingBank = null;
    }

    /**
     * Abre o modal de exclusão e carrega os dados do banco.
     */
    public function openDeleteModal(int $bankId): void
    {
        $bank = Bank::findOrFail($bankId);

        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->deletingBank = $bank;
        $this->showDeleteModal = true;
    }

    /**
     * Abre modal de upload para um banco específico.
     */
    public function openUploadModal(int $bankId)
    {
        $bank = Bank::findOrFail($bankId);

        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Redireciona para a página de upload com o bankId
        return redirect()->route('invoices.upload', ['bankId' => $bankId]);
    }

    /**
     * Fecha o modal de exclusão.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingBank = null;
    }



    /**
     * Confirma a exclusão do banco.
     */
    public function delete(): void
    {
        if (!$this->deletingBank) {
            return;
        }

        $this->destroyBank($this->deletingBank->id_bank);
    }

    /**
     * Atualiza o banco.
     */
    public function update(): void
    {
        if (!$this->editingBank) {
            return;
        }

        $validated = $this->validate([
            'editingBank.name' => 'required|string|max:255',
            'editingBank.description' => 'required|string|max:255',
            'editingBank.start_date' => 'required|date',
            'editingBank.end_date' => 'required|date|after_or_equal:editingBank.start_date',
            'editingBank.caminho_icone' => 'required|string',
        ]);

        $this->editingBank->update($validated['editingBank']);

        // Fecha o modal e recarrega os dados
        $this->closeEditModal();
        $this->loadData();

        Session::flash('success', 'Cartão atualizado com sucesso!');
    }

    /**
     * Renderiza a view do componente.
     */
     public function render()
    {
        // Obtendo os bancos com paginação dinâmica para a view
        $paginatedBanks = Bank::where('user_id', Auth::id())->paginate(3, ['*'], 'page', $this->page ?? 1)->withQueryString();

        return view('livewire.banks.banks-index', [
            'showDeleteModal' => $this->showDeleteModal,
            'paginatedBanks' => $paginatedBanks,
            'calendarDays' => $this->calendarDays,
            'calendarInvoices' => $this->calendarInvoices,
            'selectedDate' => $this->selectedDate,
            'allInvoices' => $this->allInvoices,
            'pieData' => $this->pieData,
        ]);
    }

    // Propriedade pública para controlar a página
    public $page = 1;

    // Método para trocar de página
    public function goToPage($page)
    {
        $this->page = $page;
    }

    public function updatedMonth()
    {
        $this->selectedDate = null;
        $this->loadData();
    }

    public function updatedYear()
    {
        $this->selectedDate = null;
        $this->loadData();
    }

    /**
     * Métodos para ações dos cards de invoice
     */
    public function editInvoice($invoiceId)
    {
        return redirect()->route('invoices.edit', $invoiceId);
    }

    public function copyInvoice($invoiceId)
    {
        return redirect()->route('invoices.copy', $invoiceId);
    }

    public function confirmDeleteInvoice($invoiceId)
    {
        $this->dispatch('confirm-delete-invoice', ['invoiceId' => $invoiceId]);
    }

}
