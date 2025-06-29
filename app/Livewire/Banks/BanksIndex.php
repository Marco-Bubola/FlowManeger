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
    public float $totalMonth = 0;
    public $highestInvoice = null;
    public $lowestInvoice = null;
    public int $totalTransactions = 0;

    // Propriedades para o modal de edição
    public ?Bank $editingBank = null;
    public bool $showEditModal = false;

    // Propriedades para o modal de exclusão
    public ?Bank $deletingBank = null;
    public bool $showDeleteModal = false;

    // Propriedade que não deve ser serializada pelo Livewire
    protected $bankIcons;

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

        // Calculando as métricas e convertendo para arrays
        $groupedInvoicesCollection = $invoices->groupBy(fn ($invoice) => Carbon::parse($invoice->invoice_date)->format('Y-m-d'));
        $this->groupedInvoices = $groupedInvoicesCollection->toArray();
        
        $this->totalMonth = $invoices->sum('value');
        
        $highestInvoice = $invoices->sortByDesc('value')->first();
        $this->highestInvoice = $highestInvoice ? $highestInvoice->toArray() : null;
        
        $lowestInvoice = $invoices->sortBy('value')->first();
        $this->lowestInvoice = $lowestInvoice ? $lowestInvoice->toArray() : null;
        
        $this->totalTransactions = $invoices->count();
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
     * Abre o modal de edição e carrega os dados do banco.
     */
public function openEditModal(int $bankId): void
{
    $bank = Bank::findOrFail($bankId);
    
        // Verifica se o banco pertence ao usuário logado
        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
    $this->editingBank = $bank;
    $this->showEditModal = true;
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
        
        // Verifica se o banco pertence ao usuário logado
        if ($bank->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->deletingBank = $bank;
        $this->showDeleteModal = true;
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
        return view('livewire.banks.banks-index');
    }

}