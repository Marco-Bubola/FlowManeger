<?php

namespace App\Livewire\Finance;

use App\Models\Category;
use App\Models\FinanceCapture;
use App\Models\WhatsAppSetting;
use App\Services\FinanceCapture\CaptureService;
use App\Services\FinanceCapture\EntryWriter;
use App\Services\FinanceCapture\PrintReader;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Finanças › Lançar por print: prints ou texto de notificação viram compra na fatura ou lançamento no livro caixa. */
#[Title('Lançar por print')]
class PrintCapture extends Component
{
    use WithFileUploads;

    public array $prints = [];
    public string $pasteText = '';
    public bool $autoSave = true;
    public ?string $message = null;
    public bool $messageOk = true;

    /** Campos editáveis de cada item para revisar, por id */
    public array $form = [];

    public function mount(): void
    {
        $this->loadForms();
    }

    private function service(): CaptureService
    {
        return app(CaptureService::class);
    }

    private function loadForms(): void
    {
        $this->form = [];
        foreach ($this->reviewQuery()->get() as $c) {
            $this->form[$c->id] = [
                'kind' => $c->kind ?? '',
                'amount' => $c->amount !== null ? number_format((float) $c->amount, 2, ',', '') : '',
                'description' => (string) $c->description,
                'date' => $c->date?->toDateString() ?? now()->toDateString(),
                'installments' => $c->installments ? (int) explode(' de ', $c->installments)[1] : 1,
                'bank_id' => $c->bank_id ?? '',
                'account_id' => $c->account_id ?? '',
                'category_id' => $c->category_id ?? '',
            ];
        }
    }

    private function reviewQuery()
    {
        return FinanceCapture::where('user_id', Auth::id())->whereIn('status', ['review', 'failed'])->latest('id')->limit(30);
    }

    public function updatedPrints(): void
    {
        $this->validate([
            'prints' => 'array|max:10',
            'prints.*' => 'file|mimes:jpg,jpeg,png,webp,heic,heif|max:8192',
        ], [
            'prints.max' => 'Mande até 10 prints de cada vez.',
            'prints.*.mimes' => 'Mande imagem (print) em JPG, PNG, WEBP ou HEIC.',
            'prints.*.max' => 'Cada print pode ter até 8 MB.',
        ]);
        $this->processPrints();
    }

    public function processPrints(): void
    {
        $saved = 0;
        $review = 0;
        $errors = [];
        foreach ($this->prints as $file) {
            $mime = $file->getMimeType() ?: 'image/png';
            $result = $this->service()->fromImage(Auth::id(), file_get_contents($file->getRealPath()), $mime, 'app', $this->autoSave);
            foreach ($result['captures'] as $c) {
                $c->status === 'saved' ? $saved++ : ($c->status === 'review' ? $review++ : null);
            }
            if ($result['error']) {
                $errors[] = $result['error'];
            }
        }
        $this->prints = [];
        $this->report($saved, $review, $errors);
    }

    public function processText(): void
    {
        $this->validate(['pasteText' => 'required|string|max:5000'], ['pasteText.required' => 'Cole o texto da notificação.']);
        $result = $this->service()->fromText(Auth::id(), $this->pasteText, 'app', $this->autoSave);
        $saved = collect($result['captures'])->where('status', 'saved')->count();
        $review = collect($result['captures'])->where('status', 'review')->count();
        if ($result['captures']) {
            $this->pasteText = '';
        }
        $this->report($saved, $review, $result['error'] ? [$result['error']] : []);
    }

    private function report(int $saved, int $review, array $errors): void
    {
        $parts = [];
        if ($saved) {
            $parts[] = $saved === 1 ? '1 lançamento gravado' : "{$saved} lançamentos gravados";
        }
        if ($review) {
            $parts[] = $review === 1 ? '1 para conferir' : "{$review} para conferir";
        }
        $this->messageOk = $errors === [];
        $this->message = $parts ? implode(', ', $parts) . '.' . ($errors ? ' ' . implode(' ', array_unique($errors)) : '') : implode(' ', array_unique($errors));
        $this->loadForms();
    }

    private function own(int $id): FinanceCapture
    {
        return FinanceCapture::where('user_id', Auth::id())->findOrFail($id);
    }

    public function saveItem(int $id, bool $reload = true): void
    {
        $c = $this->own($id);
        $f = $this->form[$id] ?? [];
        $amount = \App\Services\FinanceCapture\NotificationParser::money(preg_replace('/[^\d.,]/', '', (string) ($f['amount'] ?? '')) ?: '0');
        $kind = in_array($f['kind'] ?? '', [FinanceCapture::KIND_CARD, FinanceCapture::KIND_INCOME, FinanceCapture::KIND_EXPENSE], true) ? $f['kind'] : null;
        $writer = app(EntryWriter::class);
        $bankId = $kind === FinanceCapture::KIND_CARD && $writer->banks(Auth::id())->contains('id_bank', (int) ($f['bank_id'] ?? 0)) ? (int) $f['bank_id'] : null;
        $accountId = $kind !== FinanceCapture::KIND_CARD && $writer->accounts(Auth::id())->contains('id', (int) ($f['account_id'] ?? 0)) ? (int) $f['account_id'] : null;
        $categoryId = Category::where('user_id', Auth::id())->where('id_category', (int) ($f['category_id'] ?? 0))->value('id_category');
        $total = max(1, min(48, (int) ($f['installments'] ?? 1)));

        $errors = [];
        if (! $kind) $errors["form.$id.kind"] = 'Escolha o tipo.';
        if ($amount <= 0) $errors["form.$id.amount"] = 'Informe o valor.';
        if ($kind === FinanceCapture::KIND_CARD && ! $bankId) $errors["form.$id.bank_id"] = 'Escolha o cartão.';
        if (! $categoryId) $errors["form.$id.category_id"] = 'Escolha a categoria.';
        if (! strtotime((string) ($f['date'] ?? ''))) $errors["form.$id.date"] = 'Data inválida.';
        foreach ($errors as $k => $msg) {
            $this->addError($k, $msg);
        }
        if ($errors) {
            return;
        }

        $c->fill([
            'kind' => $kind,
            'amount' => round($amount, 2),
            'description' => mb_substr(trim((string) $f['description']), 0, 120) ?: null,
            'date' => $f['date'],
            'installments' => $kind === FinanceCapture::KIND_CARD && $total > 1 ? "1 de {$total}" : null,
            'bank_id' => $bankId,
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'status' => 'review',
        ])->save();
        $this->service()->save($c);

        $this->messageOk = true;
        $this->message = $c->linked_existing ? 'Já estava lançado: só liguei o print ao lançamento.' : 'Gravado: ' . $this->service()->summary($c->fresh());
        if ($reload) {
            $this->loadForms();
        }
    }

    public function saveAll(): void
    {
        foreach (array_keys($this->form) as $id) {
            if ($this->own($id)->status === 'review') {
                $this->saveItem($id, false);
            }
        }
        $this->loadForms();
    }

    public function discard(int $id): void
    {
        $this->own($id)->update(['status' => 'discarded']);
        $this->loadForms();
    }

    public function undo(int $id): void
    {
        $c = $this->service()->undo($this->own($id));
        $this->messageOk = true;
        $this->message = 'Desfeito: ' . $c->description . ' saiu ' . ($c->kind === FinanceCapture::KIND_CARD ? 'da fatura.' : 'do livro caixa.');
    }

    /** Desfeito por engano: volta para conferir */
    public function reopen(int $id): void
    {
        $c = $this->own($id);
        if ($c->status === 'undone') {
            $c->update(['status' => 'review']);
        }
        $this->loadForms();
    }

    public function render()
    {
        $writer = app(EntryWriter::class);
        $uid = Auth::id();
        $recent = FinanceCapture::with(['bank', 'account'])->where('user_id', $uid)
            ->whereIn('status', ['saved', 'undone'])->latest('updated_at')->limit(20)->get();
        $wa = WhatsAppSetting::where('user_id', $uid)->first();

        return view('livewire.finance.print-capture', [
            'review' => $this->reviewQuery()->get(),
            'recent' => $recent,
            'banks' => $writer->banks($uid),
            'accounts' => $writer->accounts($uid),
            'categories' => Category::where('user_id', $uid)->where('is_active', 1)->where('type', 'transaction')->orderBy('name')->get(['id_category', 'name']),
            'aiReady' => app(PrintReader::class)->available(),
            'waReady' => $wa?->ready() ?? false,
            'monthCount' => FinanceCapture::where('user_id', $uid)->where('status', 'saved')->where('created_at', '>=', now()->startOfMonth())->count(),
        ]);
    }
}
