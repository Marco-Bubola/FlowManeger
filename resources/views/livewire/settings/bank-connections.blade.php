<?php

use App\Models\Account;
use App\Models\Bank;
use App\Models\BankConnection;
use App\Models\BankConnectionAccount;
use App\Services\BankSync\BankSyncService;
use App\Services\FinanceCapture\Institutions;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public bool $enabled = true;
    public string $client_id = '';
    public string $client_secret = '';
    public string $item_ids = '';
    public bool $hasSecret = false;

    /** Para onde vai cada conta do banco: "account:5", "bank:3", "new" ou "none" */
    public array $map = [];
    public array $from = [];

    public ?string $result = null;
    public bool $resultOk = true;

    public function mount(): void
    {
        $c = BankConnection::for(Auth::id());
        if ($c) {
            $this->enabled = (bool) $c->enabled;
            $this->client_id = (string) $c->client_id;
            $this->item_ids = implode("\n", $c->itemIdList());
            $this->hasSecret = filled($c->client_secret);
        }
        $this->loadMap();
    }

    private function loadMap(): void
    {
        foreach (BankConnectionAccount::where('user_id', Auth::id())->get() as $a) {
            $this->map[$a->id] = $a->target_type === 'none' || ! $a->target_id ? 'none' : ($a->target_type . ':' . $a->target_id);
            $this->from[$a->id] = $a->import_from?->toDateString() ?? now()->toDateString();
        }
    }

    public function save(): void
    {
        $this->client_id = trim($this->client_id);
        $this->client_secret = trim($this->client_secret);
        $this->validate([
            'client_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9-]+$/'],
            'client_secret' => ['nullable', 'string', 'max:200'],
            'item_ids' => ['nullable', 'string', 'max:2000'],
        ], ['client_id.regex' => 'O Client ID tem letras, números e traços (formato de UUID).']);

        $ids = array_values(array_unique(array_filter(array_map('trim', preg_split('/[\s,;]+/', $this->item_ids) ?: []))));
        foreach ($ids as $id) {
            if (! preg_match('/^[A-Za-z0-9-]{8,64}$/', $id)) {
                $this->addError('item_ids', "\"{$id}\" não parece um Item ID.");

                return;
            }
        }

        $c = BankConnection::firstOrNew(['user_id' => Auth::id()]);
        $c->fill(['provider' => 'pluggy', 'enabled' => $this->enabled, 'client_id' => $this->client_id ?: null, 'item_ids' => implode("\n", $ids) ?: null]);
        if ($this->client_secret !== '') {
            $c->client_secret = $this->client_secret;
        }
        $c->save();

        $this->client_secret = '';
        $this->hasSecret = filled($c->client_secret);
        $this->item_ids = implode("\n", $ids);
        $this->dispatch('banks-saved');
    }

    public function removeSecret(): void
    {
        if ($c = BankConnection::for(Auth::id())) {
            $c->fill(['client_secret' => null, 'enabled' => false])->save();
        }
        $this->hasSecret = false;
        $this->enabled = false;
    }

    private function connection(): ?BankConnection
    {
        $c = BankConnection::for(Auth::id());
        if (! $c || blank($c->client_id) || blank($c->client_secret) || $c->itemIdList() === []) {
            $this->resultOk = false;
            $this->result = 'Salve o Client ID, o Client Secret e pelo menos um Item ID antes.';

            return null;
        }

        return $c;
    }

    public function discover(): void
    {
        if (! $c = $this->connection()) {
            return;
        }
        try {
            $n = app(BankSyncService::class)->discover($c);
            $this->resultOk = true;
            $this->result = $n ? "Achei {$n} " . ($n === 1 ? 'conta' : 'contas e cartões') . '. Escolha abaixo para onde vai cada uma e salve.' : 'A Pluggy não devolveu nenhuma conta para esses Item IDs.';
        } catch (\Throwable $e) {
            $this->resultOk = false;
            $this->result = $e->getMessage();
        }
        $this->loadMap();
    }

    public function saveMap(): void
    {
        $uid = Auth::id();
        $accountIds = Account::where('user_id', $uid)->pluck('id')->all();
        $bankIds = Bank::where('user_id', $uid)->pluck('id_bank')->all();

        foreach (BankConnectionAccount::where('user_id', $uid)->get() as $a) {
            $choice = $this->map[$a->id] ?? 'none';
            [$type, $id] = array_pad(explode(':', $choice, 2), 2, null);
            if ($choice === 'new' && ! $a->isCard()) {
                $acc = Account::create([
                    'user_id' => $uid, 'name' => $a->institution ?: ($a->name ?: 'Conta'),
                    'type' => $a->subtype === 'SAVINGS_ACCOUNT' ? 'poupanca' : 'corrente',
                    'initial_balance' => 0, 'archived' => false,
                ]);
                [$type, $id] = ['account', $acc->id];
                $accountIds[] = $acc->id;
            }
            $valid = ($type === 'account' && ! $a->isCard() && in_array((int) $id, $accountIds, true))
                || ($type === 'bank' && $a->isCard() && in_array((int) $id, $bankIds, true));
            $date = $this->from[$a->id] ?? null;
            $a->fill([
                'target_type' => $valid ? $type : 'none',
                'target_id' => $valid ? (int) $id : null,
                'import_from' => $date && strtotime($date) ? $date : now()->toDateString(),
            ])->save();
        }
        $this->loadMap();
        $this->resultOk = true;
        $this->result = 'Destinos salvos. Toque em Sincronizar agora para trazer as transações.';
    }

    public function syncNow(): void
    {
        if (! $c = $this->connection()) {
            return;
        }
        try {
            $r = app(BankSyncService::class)->sync($c);
            $this->resultOk = $r['errors'] === [];
            $this->result = "{$r['created']} lançamentos novos, {$r['linked']} já estavam lançados (só liguei)."
                . ($r['errors'] ? ' Erros: ' . implode(' | ', $r['errors']) : '');
        } catch (\Throwable $e) {
            $this->resultOk = false;
            $this->result = $e->getMessage();
        }
    }

    public function with(): array
    {
        $uid = Auth::id();
        $c = BankConnection::for($uid);
        $items = $c?->itemIdList() ?? [];

        return [
            'connection' => $c,
            'ready' => $c?->ready() ?? false,
            'rows' => BankConnectionAccount::where('user_id', $uid)->when($items, fn ($q) => $q->whereIn('item_id', $items))->orderBy('institution')->orderBy('type')->get(),
            'accounts' => Account::where('user_id', $uid)->where('archived', false)->orderBy('name')->get(),
            'banks' => Bank::where('user_id', $uid)->orderBy('name')->get(),
            'institutions' => array_keys(Institutions::ALL),
        ];
    }
}; ?>

<section class="settings-banks-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        <div class="s-pg-grid">
        <div class="s-col-main" style="display:flex;flex-direction:column;gap:1rem">

        <form wire:submit="save" class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.12);color:#6366f1"><i class="bi bi-bank" style="font-size:1rem"></i></div>
                    <div>
                        <p class="settings-card-title">Bancos conectados (Open Finance)</p>
                        <p class="settings-card-desc">Entradas e saídas da conta vão para o livro caixa; compras do cartão, para a fatura</p>
                    </div>
                </div>
                @if($ready)
                    <span class="s-badge s-badge-success">Ativo</span>
                @else
                    <span class="s-badge">Desligado</span>
                @endif
            </div>
            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1rem">
                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-arrow-repeat"></i></div>
                        <div>
                            <p class="settings-notif-title">Sincronizar sozinho a cada 3 horas</p>
                            <p class="settings-notif-desc">Desligado, só quando você tocar em Sincronizar agora</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $enabled ? 'active' : '' }}" wire:click="$toggle('enabled')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                <div class="settings-form-grid">
                    <div class="settings-form-full">
                        <label class="settings-field-label">Client ID (Pluggy)</label>
                        <flux:input wire:model="client_id" type="text" autocomplete="off" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"/>
                        @error('client_id') <p class="bk-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Client Secret</label>
                        @if($hasSecret)
                            <div class="bk-saved"><i class="bi bi-lock-fill"></i> Salvo
                                <button type="button" wire:click="removeSecret" wire:confirm="Remover o Client Secret? A sincronização para." class="bk-link-danger">Remover</button>
                            </div>
                        @endif
                        <flux:input wire:model="client_secret" type="password" autocomplete="off" placeholder="{{ $hasSecret ? 'Cole um novo só se quiser trocar' : 'Client Secret da aplicação' }}"/>
                        @error('client_secret') <p class="bk-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Item IDs (um por banco conectado no Meu Pluggy)</label>
                        <textarea wire:model="item_ids" rows="3" class="bk-textarea" placeholder="Um por linha"></textarea>
                        @error('item_ids') <p class="bk-err">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
                    <button data-mobile-save type="submit" class="settings-save-btn"><i class="bi bi-check2"></i> Salvar</button>
                    <button type="button" wire:click="discover" class="bk-btn" wire:loading.attr="disabled" wire:target="discover">
                        <i class="bi bi-search"></i><span wire:loading.remove wire:target="discover">Buscar contas</span><span wire:loading wire:target="discover">Buscando...</span>
                    </button>
                    <x-action-message on="banks-saved"><span style="display:inline-flex;align-items:center;gap:.35rem;color:#059669;font-size:.83rem;font-weight:600"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
                </div>
            </div>
        </form>

        @if($result)
            <div class="bk-msg {{ $resultOk ? 'ok' : 'bad' }}"><i class="bi {{ $resultOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $result }}</div>
        @endif

        @if($rows->isNotEmpty())
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-diagram-3" style="font-size:1rem"></i></div>
                    <div>
                        <p class="settings-card-title">Para onde vai cada conta</p>
                        <p class="settings-card-desc">{{ $connection?->last_sync_at ? 'Última sincronização ' . $connection->last_sync_at->diffForHumans() : 'Ainda não sincronizou' }}</p>
                    </div>
                </div>
            </div>
            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.75rem">
                @foreach($rows as $r)
                    <div class="bk-row" wire:key="bk-{{ $r->id }}">
                        <div class="bk-row-head">
                            <div class="bk-row-ic {{ $r->isCard() ? 'card' : '' }}"><i class="bi {{ $r->isCard() ? 'bi-credit-card' : 'bi-bank' }}"></i></div>
                            <div class="min-w-0 flex-1">
                                <p class="bk-row-title">{{ $r->label() }}</p>
                                <p class="bk-row-sub">{{ $r->name }}@if($r->balance !== null) · {{ $r->isCard() ? 'fatura atual' : 'saldo' }} R$ {{ number_format((float) $r->balance, 2, ',', '.') }}@endif @if($r->imported_count) · {{ $r->imported_count }} lançados @endif</p>
                            </div>
                        </div>
                        <div class="bk-row-fields">
                            <div>
                                <label class="settings-field-label">Vai para</label>
                                <select wire:model="map.{{ $r->id }}" class="settings-select">
                                    <option value="none">Não importar</option>
                                    @if($r->isCard())
                                        @foreach($banks as $b)<option value="bank:{{ $b->id_bank }}">Fatura do cartão {{ $b->name }}</option>@endforeach
                                    @else
                                        @foreach($accounts as $a)<option value="account:{{ $a->id }}">Livro caixa · conta {{ $a->name }}</option>@endforeach
                                        <option value="new">Livro caixa · criar conta "{{ $r->institution ?: 'nova' }}"</option>
                                    @endif
                                </select>
                                @if($r->isCard() && $banks->isEmpty())
                                    <p class="bk-hint">Cadastre o cartão em <a href="{{ route('banks.create') }}" class="underline">Cartões</a> primeiro.</p>
                                @endif
                            </div>
                            <div>
                                <label class="settings-field-label">Trazer a partir de</label>
                                <input type="date" wire:model="from.{{ $r->id }}" class="bk-date" max="{{ now()->toDateString() }}">
                            </div>
                        </div>
                    </div>
                @endforeach
                <p class="bk-hint"><i class="bi bi-info-circle"></i> O que já estiver lançado (por extrato, fatura em PDF ou print) com o mesmo valor e data não entra de novo: só é ligado.</p>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                    <button type="button" wire:click="saveMap" class="settings-save-btn"><i class="bi bi-check2"></i> Salvar destinos</button>
                    <button type="button" wire:click="syncNow" class="bk-btn" wire:loading.attr="disabled" wire:target="syncNow">
                        <i class="bi bi-arrow-repeat"></i><span wire:loading.remove wire:target="syncNow">Sincronizar agora</span><span wire:loading wire:target="syncNow">Sincronizando...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
        @if($connection?->last_error)
            <div class="bk-msg bad"><i class="bi bi-exclamation-triangle-fill"></i> Último erro: {{ $connection->last_error }}</div>
        @endif
        </div>

        <div class="s-col-side">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-signpost-split" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Como conectar (grátis, uso pessoal)</p>
                            <p class="settings-card-desc">Uma vez só, uns 15 minutos</p>
                        </div>
                    </div>
                </div>
                <ol class="bk-steps">
                    <li>Crie conta em <b>dashboard.pluggy.ai</b>, crie um time e uma <b>aplicação</b>. Copie o <b>Client ID</b> e o <b>Client Secret</b> para cá.</li>
                    <li>Ainda no período de teste da Pluggy, entre em <b>meu.pluggy.ai</b> e conecte todos os seus bancos (pelo app de cada banco, via Open Finance).</li>
                    <li>Na Pluggy, abra a aplicação › <b>Ir para Demo</b> › menu de três pontos de cada conexão › copie o <b>Item ID</b> e cole aqui, um por linha.</li>
                    <li>Salve, toque em <b>Buscar contas</b>, escolha o destino de cada conta e cartão e toque em <b>Sincronizar agora</b>.</li>
                </ol>
                <p class="bk-steps-note"><i class="bi bi-shield-lock"></i> O app só lê as transações; não consegue mover dinheiro. A autorização do Open Finance vence (em geral em 12 meses) e se renova no Meu Pluggy.</p>
            </div>
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-buildings" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Bancos que funcionam</p>
                            <p class="settings-card-desc">Todos do Open Finance Brasil, entre eles</p>
                        </div>
                    </div>
                </div>
                <div class="bk-chips">
                    @foreach($institutions as $inst)<span>{{ $inst }}</span>@endforeach
                </div>
            </div>
        </div>
        </div>
    </x-settings.layout>

    <style>
        .settings-banks-page .bk-err{color:#ef4444;font-size:.73rem;margin-top:.2rem}
        .settings-banks-page .bk-hint{color:#64748b;font-size:.74rem;margin-top:.3rem}
        .settings-banks-page .bk-saved{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#059669;margin:.1rem 0 .35rem}
        .settings-banks-page .bk-link-danger{margin-left:auto;background:none;border:0;color:#ef4444;font-weight:600;font-size:.78rem;cursor:pointer;text-decoration:underline}
        .settings-banks-page .bk-textarea{width:100%;border:1.5px solid rgba(148,163,184,.4);border-radius:.6rem;padding:.55rem .7rem;font-size:.85rem;font-family:ui-monospace,monospace;background:transparent;color:inherit}
        .settings-banks-page .bk-date{width:100%;min-height:2.6rem;border:1.5px solid rgba(148,163,184,.4);border-radius:.6rem;padding:.5rem .7rem;font-size:.88rem;background:transparent;color:inherit}
        .settings-banks-page .bk-btn{display:inline-flex;align-items:center;gap:.4rem;border:1.5px solid rgba(var(--s-accent-rgb),.35);color:var(--s-accent);background:transparent;border-radius:.75rem;padding:.6rem 1rem;font-size:.85rem;font-weight:700;cursor:pointer}
        .settings-banks-page .bk-msg{border-radius:.75rem;padding:.7rem .9rem;font-size:.83rem;font-weight:600;display:flex;gap:.5rem;align-items:flex-start}
        .settings-banks-page .bk-msg.ok{background:rgba(16,185,129,.1);color:#047857}
        .settings-banks-page .bk-msg.bad{background:rgba(239,68,68,.1);color:#b91c1c}
        .dark .settings-banks-page .bk-msg.ok{color:#6ee7b7}.dark .settings-banks-page .bk-msg.bad{color:#fca5a5}
        .settings-banks-page .bk-row{border:1px solid rgba(148,163,184,.3);border-radius:.9rem;padding:.75rem;display:flex;flex-direction:column;gap:.6rem}
        .settings-banks-page .bk-row-head{display:flex;gap:.6rem;align-items:center}
        .settings-banks-page .bk-row-ic{width:2.2rem;height:2.2rem;border-radius:.65rem;display:flex;align-items:center;justify-content:center;background:rgba(16,185,129,.12);color:#059669;flex-shrink:0}
        .settings-banks-page .bk-row-ic.card{background:rgba(99,102,241,.12);color:#4f46e5}
        .settings-banks-page .bk-row-title{font-weight:700;font-size:.88rem}
        .settings-banks-page .bk-row-sub{font-size:.74rem;color:#64748b}
        .settings-banks-page .bk-row-fields{display:grid;grid-template-columns:1fr;gap:.6rem}
        @media(min-width:640px){.settings-banks-page .bk-row-fields{grid-template-columns:2fr 1fr}}
        .settings-banks-page .bk-steps{margin:0;padding:0 1.25rem 0 2.6rem;display:flex;flex-direction:column;gap:.55rem;font-size:.82rem;line-height:1.45;list-style:decimal}
        .settings-banks-page .bk-steps-note{margin:.9rem 1.25rem 1.25rem;font-size:.76rem;color:#64748b;display:flex;gap:.4rem}
        .settings-banks-page .bk-chips{display:flex;flex-wrap:wrap;gap:.35rem;padding:0 1.25rem 1.25rem}
        .settings-banks-page .bk-chips span{font-size:.72rem;font-weight:600;border-radius:999px;padding:.2rem .55rem;background:rgba(99,102,241,.08);color:#4f46e5}
        .dark .settings-banks-page .bk-chips span{color:#a5b4fc}
    </style>
</section>
