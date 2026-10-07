<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\Cashbook;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Contas (corrente, poupança, carteira...), saldo de cada uma e
 * transferências entre elas.
 */
class AccountsIndex extends Component
{
    // Formulário de conta
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $type = 'corrente';
    public $initial_balance = 0;

    // Transferência
    public bool $showTransfer = false;
    public $fromId = '';
    public $toId = '';
    public $transferValue = 0;
    public string $transferDate = '';
    public string $transferNote = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'initial_balance']);
        $this->type = 'corrente';
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $account = Account::owned()->findOrFail($id);
        $this->editingId = $account->id;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->initial_balance = (float) $account->initial_balance;
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:'.implode(',', array_keys(Account::TYPES)),
            'initial_balance' => 'nullable|numeric',
        ], [
            'name.required' => 'Dê um nome para a conta.',
        ]);
        $data['initial_balance'] = (float) ($data['initial_balance'] ?? 0);

        if ($this->editingId) {
            Account::owned()->findOrFail($this->editingId)->update($data);
        } else {
            Account::create($data + ['user_id' => Auth::id()]);
        }

        $this->showForm = false;
        $this->dispatch('notify', type: 'success', message: 'Conta salva.');
    }

    public function toggleArchive(int $id): void
    {
        $account = Account::owned()->findOrFail($id);
        $account->update(['archived' => ! $account->archived]);
    }

    public function openTransfer(?int $fromId = null): void
    {
        $this->reset(['toId', 'transferValue', 'transferNote']);
        $this->fromId = $fromId ?? '';
        $this->transferDate = now()->toDateString();
        $this->resetErrorBag();
        $this->showTransfer = true;
    }

    public function transfer(): void
    {
        $this->validate([
            'fromId' => 'required|different:toId',
            'toId' => 'required',
            'transferValue' => 'required|numeric|min:0.01',
            'transferDate' => 'required|date',
            'transferNote' => 'nullable|string|max:200',
        ], [
            'fromId.different' => 'Escolha contas diferentes.',
            'fromId.required' => 'Escolha a conta de origem.',
            'toId.required' => 'Escolha a conta de destino.',
            'transferValue.min' => 'Informe um valor maior que zero.',
        ]);

        $from = Account::owned()->findOrFail($this->fromId);
        $to = Account::owned()->findOrFail($this->toId);
        $categoryId = $this->transferCategoryId();
        $note = trim('Transferência '.$from->name.' → '.$to->name.' '.$this->transferNote);

        DB::transaction(function () use ($from, $to, $categoryId, $note) {
            foreach ([[$from, 2], [$to, 1]] as [$account, $typeId]) {
                Cashbook::create([
                    'user_id' => Auth::id(),
                    'value' => $this->transferValue,
                    'description' => $typeId === 2 ? 'Transferência para '.$to->name : 'Transferência de '.$from->name,
                    'date' => $this->transferDate,
                    'is_pending' => false,
                    'category_id' => $categoryId,
                    'type_id' => $typeId,
                    'note' => mb_substr($note, 0, 255),
                    'account_id' => $account->id,
                    'inc_datetime' => now(),
                ]);
            }
        });

        $this->showTransfer = false;
        $this->dispatch('notify', type: 'success', message: 'Transferência registrada.');
    }

    private function transferCategoryId(): int
    {
        $category = Category::where('user_id', Auth::id())
            ->where('type', 'transaction')
            ->whereRaw('LOWER(name) = ?', ['transferência'])
            ->first();

        if (! $category) {
            $category = new Category();
            $category->forceFill([
                'name' => 'Transferência',
                'user_id' => Auth::id(),
                'type' => 'transaction',
                'tipo' => 'ambos',
                'hexcolor_category' => '#6366f1',
                'icone' => 'bi bi-arrow-left-right',
                'is_active' => 1,
            ])->save();
        }

        return $category->id_category;
    }

    public function render()
    {
        $accounts = Account::owned()->orderBy('archived')->orderBy('name')->get()
            ->map(fn ($a) => ['account' => $a, 'balance' => $a->balance()]);

        $unassigned = Cashbook::where('user_id', Auth::id())
            ->whereNull('account_id')
            ->where('is_pending', false)
            ->selectRaw('SUM(CASE WHEN type_id = 1 THEN value ELSE 0 END) - SUM(CASE WHEN type_id = 2 THEN value ELSE 0 END) as balance')
            ->value('balance');

        return view('livewire.accounts.accounts-index', [
            'accounts' => $accounts,
            'total' => $accounts->filter(fn ($r) => ! $r['account']->archived)->sum('balance'),
            'unassigned' => (float) $unassigned,
            'types' => Account::TYPES,
        ]);
    }
}
