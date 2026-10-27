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

@php
    $bkMapped = $rows->filter(fn ($r) => ($map[$r->id] ?? 'none') !== 'none')->count();
    $bkImported = (int) $rows->sum('imported_count');
    $bkSteps = [
        ['Client ID', filled($client_id), 'bi-person-badge'],
        ['Client Secret', $hasSecret, 'bi-key'],
        ['Item IDs', filled($item_ids), 'bi-link-45deg'],
        ['Destinos', $bkMapped > 0, 'bi-diagram-3'],
        ['Sincronizou', (bool) $connection?->last_sync_at, 'bi-arrow-repeat'],
    ];
@endphp

<section class="settings-banks-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="bk-page">
        {{-- Topo: situação, passos e números --}}
        <div class="bk-hero">
            <span class="bk-glow"></span>
            <div class="bk-hero-ico"><i class="bi bi-bank2"></i></div>
            <div class="bk-hero-txt">
                <span class="bk-kicker"><i class="bi bi-shield-check"></i> Open Finance</span>
                <h2>{{ $ready ? ($enabled ? 'Conectado, sincroniza a cada 3 horas' : 'Conectado, sincroniza quando você pedir') : 'Ainda não conectado' }}</h2>
                <p>Entradas e saídas da conta vão para o livro caixa; compras do cartão, para a fatura. O app só lê, não move dinheiro.</p>
                <div class="bk-progress">
                    @foreach($bkSteps as [$label, $ok, $icon])
                        <span class="{{ $ok ? 'is-ok' : '' }}"><i class="bi {{ $ok ? 'bi-check-circle-fill' : $icon }}"></i> {{ $label }}</span>
                    @endforeach
                </div>
            </div>
            <div class="bk-hero-stats">
                <div><b>{{ $rows->count() }}</b><small>{{ $rows->count() === 1 ? 'conta encontrada' : 'contas e cartões' }}</small></div>
                <div><b>{{ $bkImported }}</b><small>lançamentos trazidos</small></div>
                <div class="bk-wide"><b>{{ $connection?->last_sync_at ? $connection->last_sync_at->locale('pt_BR')->diffForHumans() : 'Nunca' }}</b><small>última sincronização</small></div>
                @if($ready)
                    <button type="button" wire:click="syncNow" class="bk-hero-btn" wire:loading.attr="disabled" wire:target="syncNow">
                        <i class="bi bi-arrow-repeat"></i><span wire:loading.remove wire:target="syncNow">Sincronizar agora</span><span wire:loading wire:target="syncNow">Sincronizando...</span>
                    </button>
                @endif
            </div>
        </div>

        @if($result)
            <div class="bk-msg {{ $resultOk ? 'ok' : 'bad' }}"><i class="bi {{ $resultOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $result }}</div>
        @endif
        @if($connection?->last_error)
            <div class="bk-msg bad"><i class="bi bi-exclamation-triangle-fill"></i> Último erro: {{ $connection->last_error }}</div>
        @endif

        <div class="bk-grid">
        <div class="bk-main">

            {{-- 1. Chaves da Pluggy --}}
            <form wire:submit="save" class="ap-card">
                <div class="ap-card-head">
                    <span class="bk-num">1</span>
                    <div class="min-w-0">
                        <h2>Chaves da Pluggy</h2>
                        <p>Da sua aplicação em dashboard.pluggy.ai. O Client Secret fica guardado criptografado.</p>
                    </div>
                </div>
                <button type="button" class="bk-switch-row {{ $enabled ? 'is-on' : '' }}" wire:click="$toggle('enabled')">
                    <span class="bk-sw-ico"><i class="bi bi-arrow-repeat"></i></span>
                    <span class="bk-sw-txt"><b>Sincronizar sozinho a cada 3 horas</b><small>{{ $enabled ? 'Ligado: o app busca os lançamentos sozinho. Lembre de salvar.' : 'Desligado, só quando você tocar em Sincronizar agora. Lembre de salvar.' }}</small></span>
                    <span class="bk-switch"><span></span></span>
                </button>
                <div class="bk-fields">
                    <div class="bk-field">
                        <label class="settings-field-label">Client ID</label>
                        <flux:input wire:model="client_id" type="text" autocomplete="off" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"/>
                        @error('client_id') <p class="bk-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="bk-field">
                        <label class="settings-field-label">Client Secret</label>
                        @if($hasSecret)
                            <div class="bk-saved"><i class="bi bi-lock-fill"></i> Salvo
                                <button type="button" wire:click="removeSecret" wire:confirm="Remover o Client Secret? A sincronização para." class="bk-link-danger">Remover</button>
                            </div>
                        @endif
                        <flux:input wire:model="client_secret" type="password" autocomplete="off" placeholder="{{ $hasSecret ? 'Cole um novo só se quiser trocar' : 'Client Secret da aplicação' }}"/>
                        @error('client_secret') <p class="bk-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="bk-field bk-field-full">
                        <label class="settings-field-label">Item IDs (um por banco conectado no Meu Pluggy)</label>
                        <textarea wire:model="item_ids" rows="3" class="bk-textarea" placeholder="Um por linha"></textarea>
                        @error('item_ids') <p class="bk-err">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="bk-actions">
                    <button data-mobile-save type="submit" class="bk-btn-save"><i class="bi bi-check2"></i> Salvar</button>
                    <button type="button" wire:click="discover" class="bk-btn" wire:loading.attr="disabled" wire:target="discover">
                        <i class="bi bi-search"></i><span wire:loading.remove wire:target="discover">Buscar contas</span><span wire:loading wire:target="discover">Buscando...</span>
                    </button>
                    <x-action-message on="banks-saved"><span class="bk-ok-msg"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
                </div>
            </form>

            {{-- 2. Destinos --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="bk-num">2</span>
                    <div class="min-w-0">
                        <h2>Para onde vai cada conta</h2>
                        <p>{{ $rows->isNotEmpty() ? $bkMapped . ' de ' . $rows->count() . ' com destino escolhido' : 'Depois de salvar as chaves, toque em Buscar contas.' }}</p>
                    </div>
                </div>
                @if($rows->isNotEmpty())
                    <div class="bk-rows">
                        @foreach($rows as $r)
                            @php $on = ($map[$r->id] ?? 'none') !== 'none'; @endphp
                            <div class="bk-row {{ $on ? 'is-on' : '' }}" wire:key="bk-{{ $r->id }}">
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
                    </div>
                    <p class="bk-hint"><i class="bi bi-info-circle"></i> O que já estiver lançado (por extrato, fatura em PDF ou print) com o mesmo valor e data não entra de novo: só é ligado.</p>
                    <div class="bk-actions">
                        <button type="button" wire:click="saveMap" class="bk-btn-save"><i class="bi bi-check2"></i> Salvar destinos</button>
                        <button type="button" wire:click="syncNow" class="bk-btn" wire:loading.attr="disabled" wire:target="syncNow">
                            <i class="bi bi-arrow-repeat"></i><span wire:loading.remove wire:target="syncNow">Sincronizar agora</span><span wire:loading wire:target="syncNow">Sincronizando...</span>
                        </button>
                    </div>
                @else
                    <div class="bk-empty">
                        <span class="bk-empty-art"><i class="bi bi-bank"></i><i class="bi bi-arrow-right"></i><i class="bi bi-journal-text"></i></span>
                        <small>As contas e cartões aparecem aqui para você escolher se vão para o livro caixa ou para a fatura.</small>
                    </div>
                @endif
            </div>
        </div>

        <aside class="bk-side">
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="ap-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-signpost-split"></i></span>
                    <div class="min-w-0">
                        <h2>Como conectar</h2>
                        <p>Grátis para uso pessoal, uns 15 minutos.</p>
                    </div>
                </div>
                <ol class="bk-steps">
                    <li class="{{ filled($client_id) && $hasSecret ? 'is-done' : '' }}">Crie conta em <b>dashboard.pluggy.ai</b>, crie um time e uma <b>aplicação</b>. Copie o <b>Client ID</b> e o <b>Client Secret</b> para o passo 1.</li>
                    <li class="{{ filled($item_ids) ? 'is-done' : '' }}">Ainda no período de teste da Pluggy, entre em <b>meu.pluggy.ai</b> e conecte todos os seus bancos (pelo app de cada banco, via Open Finance).</li>
                    <li class="{{ filled($item_ids) ? 'is-done' : '' }}">Na Pluggy, abra a aplicação › <b>Ir para Demo</b> › menu de três pontos de cada conexão › copie o <b>Item ID</b> e cole aqui, um por linha.</li>
                    <li class="{{ $connection?->last_sync_at ? 'is-done' : '' }}">Salve, toque em <b>Buscar contas</b>, escolha o destino de cada conta e cartão e toque em <b>Sincronizar agora</b>.</li>
                </ol>
                <p class="bk-steps-note"><i class="bi bi-shield-lock"></i> A autorização do Open Finance vence (em geral em 12 meses) e se renova no Meu Pluggy.</p>
            </div>
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="ap-ico bg-gradient-to-br from-indigo-500 to-blue-600"><i class="bi bi-buildings"></i></span>
                    <div class="min-w-0">
                        <h2>Bancos que funcionam</h2>
                        <p>Todos do Open Finance Brasil, entre eles:</p>
                    </div>
                </div>
                <div class="bk-chips">
                    @foreach($institutions as $inst)<span>{{ $inst }}</span>@endforeach
                </div>
            </div>
        </aside>
        </div>
    </div>

    <style>
        .bk-page { display: flex; flex-direction: column; gap: 1rem; }
        .bk-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 45%, #0891b2 100%); box-shadow: 0 24px 50px -28px rgba(30, 58, 138, .8); }
        .bk-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .28), transparent 40%); }
        .bk-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.8rem; }
        .bk-hero-txt { position: relative; flex: 1; min-width: 0; }
        .bk-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .bk-hero-txt h2 { font-size: 1.4rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .bk-hero-txt p { font-size: .8rem; opacity: .92; max-width: 40rem; }
        .bk-progress { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
        .bk-progress span { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .6rem; border-radius: 999px; background: rgba(0, 0, 0, .15); border: 1px dashed rgba(255, 255, 255, .4); font-size: .72rem; font-weight: 700; opacity: .85; }
        .bk-progress span.is-ok { background: rgba(255, 255, 255, .95); color: #1d4ed8; border-style: solid; border-color: transparent; opacity: 1; }
        .bk-hero-stats { position: relative; flex: none; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; width: 19rem; }
        .bk-hero-stats > div { padding: .6rem .7rem; border-radius: .9rem; background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25); }
        .bk-hero-stats .bk-wide { grid-column: 1 / -1; }
        .bk-hero-stats b { display: block; font-size: 1.1rem; font-weight: 900; line-height: 1.15; }
        .bk-hero-stats small { display: block; font-size: .66rem; opacity: .9; }
        .bk-hero-btn { grid-column: 1 / -1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .6rem; border-radius: .9rem; background: #fff; color: #1d4ed8; font-size: .82rem; font-weight: 800; }
        .bk-msg { border-radius: .9rem; padding: .7rem .9rem; font-size: .83rem; font-weight: 600; display: flex; gap: .5rem; align-items: flex-start; }
        .bk-msg.ok { background: rgba(16, 185, 129, .1); color: #047857; }
        .bk-msg.bad { background: rgba(239, 68, 68, .1); color: #b91c1c; }
        .dark .bk-msg.ok { color: #6ee7b7; }
        .dark .bk-msg.bad { color: #fca5a5; }
        .bk-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1rem; align-items: start; }
        .bk-main, .bk-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .bk-side { position: sticky; top: 1rem; }
        .bk-num { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: linear-gradient(135deg, #1d4ed8, #0891b2); color: #fff; font-weight: 900; box-shadow: 0 8px 18px -8px rgba(29, 78, 216, .6); }
        .bk-switch-row { width: 100%; display: flex; align-items: center; gap: .75rem; padding: .75rem .85rem; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; text-align: left; margin-bottom: .9rem; transition: all .15s; }
        .bk-switch-row.is-on { background: #fff; border-color: #bfdbfe; box-shadow: 0 8px 20px -16px #1d4ed8; }
        .dark .bk-switch-row { background: #111827; border-color: #1f2937; }
        .dark .bk-switch-row.is-on { background: rgba(59, 130, 246, .08); border-color: rgba(59, 130, 246, .35); box-shadow: none; }
        .bk-sw-ico { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: #dbeafe; color: #1d4ed8; }
        .dark .bk-sw-ico { background: rgba(59, 130, 246, .15); color: #93c5fd; }
        .bk-sw-txt { display: block; flex: 1; min-width: 0; }
        .bk-sw-txt b { display: block; font-size: .88rem; color: #0f172a; }
        .bk-sw-txt small { display: block; font-size: .73rem; color: #64748b; }
        .dark .bk-sw-txt b { color: #f1f5f9; }
        .bk-switch { flex: none; position: relative; width: 2.6rem; height: 1.5rem; border-radius: 999px; background: #cbd5e1; transition: background .2s; }
        .bk-switch span { position: absolute; top: .18rem; left: .18rem; width: 1.14rem; height: 1.14rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .2s; }
        .bk-switch-row.is-on .bk-switch { background: #2563eb; }
        .bk-switch-row.is-on .bk-switch span { transform: translateX(1.1rem); }
        .dark .bk-switch { background: #334155; }
        .bk-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem; }
        .bk-field { min-width: 0; }
        .bk-field-full { grid-column: 1 / -1; }
        .bk-err { color: #ef4444; font-size: .73rem; margin-top: .2rem; }
        .bk-hint { color: #64748b; font-size: .74rem; margin-top: .6rem; }
        .bk-saved { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: #059669; margin: .1rem 0 .35rem; }
        .bk-link-danger { margin-left: auto; background: none; border: 0; color: #ef4444; font-weight: 600; font-size: .78rem; cursor: pointer; text-decoration: underline; }
        .bk-textarea { width: 100%; border: 1.5px solid rgba(148, 163, 184, .4); border-radius: .7rem; padding: .55rem .7rem; font-size: .85rem; font-family: ui-monospace, monospace; background: transparent; color: inherit; }
        .bk-date { width: 100%; min-height: 2.6rem; border: 1.5px solid rgba(148, 163, 184, .4); border-radius: .7rem; padding: .5rem .7rem; font-size: .88rem; background: transparent; color: inherit; }
        .bk-actions { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; margin-top: 1rem; }
        .bk-btn-save { display: inline-flex; align-items: center; gap: .4rem; padding: .7rem 1.4rem; border-radius: .9rem; background: linear-gradient(135deg, #2563eb, #1e3a8a); color: #fff; font-size: .88rem; font-weight: 800; box-shadow: 0 12px 24px -14px #1e3a8a; }
        .bk-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .66rem 1.1rem; border-radius: .9rem; border: 1.5px solid #bfdbfe; color: #1d4ed8; background: #fff; font-size: .85rem; font-weight: 800; }
        .dark .bk-btn { background: #111827; color: #93c5fd; border-color: rgba(59, 130, 246, .35); }
        .bk-ok-msg { display: inline-flex; align-items: center; gap: .35rem; color: #059669; font-size: .83rem; font-weight: 700; }
        .bk-rows { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }
        .bk-row { border: 1px solid #e2e8f0; border-radius: 1rem; padding: .75rem; display: flex; flex-direction: column; gap: .6rem; background: #f8fafc; }
        .bk-row.is-on { background: #fff; border-color: #bfdbfe; }
        .dark .bk-row { background: #111827; border-color: #1f2937; }
        .dark .bk-row.is-on { background: rgba(59, 130, 246, .06); border-color: rgba(59, 130, 246, .3); }
        .bk-row-head { display: flex; gap: .6rem; align-items: center; }
        .bk-row-ic { width: 2.2rem; height: 2.2rem; border-radius: .65rem; display: flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, .12); color: #059669; flex-shrink: 0; }
        .bk-row-ic.card { background: rgba(99, 102, 241, .12); color: #4f46e5; }
        .bk-row-title { font-weight: 800; font-size: .88rem; }
        .bk-row-sub { font-size: .74rem; color: #64748b; }
        .bk-row-fields { display: grid; grid-template-columns: 2fr 1fr; gap: .6rem; }
        .bk-empty { display: flex; flex-direction: column; align-items: center; gap: .6rem; padding: 1.4rem 1rem; border-radius: 1rem; border: 1px dashed #cbd5e1; text-align: center; }
        .dark .bk-empty { border-color: #334155; }
        .bk-empty-art { display: inline-flex; align-items: center; gap: .6rem; font-size: 1.5rem; color: #93c5fd; }
        .bk-empty-art .bi-arrow-right { font-size: 1rem; color: #cbd5e1; }
        .bk-empty small { font-size: .78rem; color: #64748b; max-width: 26rem; }
        .bk-steps { counter-reset: bk; display: flex; flex-direction: column; gap: .6rem; font-size: .8rem; line-height: 1.45; color: #334155; }
        .bk-steps li { counter-increment: bk; position: relative; padding-left: 2.1rem; }
        .bk-steps li::before { content: counter(bk); position: absolute; left: 0; top: 0; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 900; }
        .bk-steps li.is-done::before { content: '✓'; background: #2563eb; color: #fff; }
        .bk-steps li.is-done { color: #94a3b8; }
        .dark .bk-steps { color: #cbd5e1; }
        .dark .bk-steps li::before { background: #1e293b; color: #cbd5e1; }
        .bk-steps-note { margin-top: .9rem; font-size: .74rem; color: #64748b; display: flex; gap: .4rem; }
        .bk-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
        .bk-chips span { font-size: .72rem; font-weight: 700; border-radius: 999px; padding: .22rem .6rem; background: #eff6ff; color: #1d4ed8; }
        .dark .bk-chips span { background: rgba(59, 130, 246, .12); color: #93c5fd; }
        @media (max-width: 1535px) { .bk-hero { flex-wrap: wrap; } .bk-hero-stats { width: 100%; grid-template-columns: repeat(4, minmax(0, 1fr)); } .bk-hero-stats .bk-wide, .bk-hero-btn { grid-column: auto; } .bk-rows { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1279px) { .bk-grid { grid-template-columns: minmax(0, 1fr) 290px; } .bk-fields { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1023px) { .bk-grid { grid-template-columns: minmax(0, 1fr); } .bk-side { position: static; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } .bk-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .bk-hero { padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .bk-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.4rem; }
            .bk-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .bk-hero-txt h2 { font-size: 1.1rem; }
            .bk-hero-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .bk-hero-stats .bk-wide, .bk-hero-btn { grid-column: 1 / -1; }
            .bk-fields, .bk-row-fields { grid-template-columns: minmax(0, 1fr); }
            .bk-side { display: flex; }
            .bk-actions > button { flex: 1; justify-content: center; }
        }
    </style>
    </x-settings.layout>
</section>
