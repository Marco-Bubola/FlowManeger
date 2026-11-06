<?php

namespace App\Livewire\Crm;

use App\Models\Client;
use App\Models\CrmDeal;
use App\Services\Clients\ClientStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Funil de vendas: negócios em colunas (Contato → Orçamento → Negociando → Ganho / Perdido).
 * Um negócio pode ser de um cliente ou de um lead (só nome + telefone). Ganho abre a Nova venda.
 */
#[Layout('components.layouts.app')]
class SalesFunnel extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'origem', except: '')]
    public string $sourceFilter = '';

    /** Coluna visível no celular. */
    #[Url(as: 'etapa', except: 'contato')]
    public string $mobileStage = 'contato';

    #[Url(as: 'fechados', except: false)]
    public bool $showClosed = false;

    // Formulário
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $who = 'cliente'; // cliente | lead
    public ?int $clientId = null;
    public string $clientSearch = '';
    public string $name = '';
    public string $phone = '';
    public string $source = '';
    public string $title = '';
    public string $value = '';
    public string $stage = 'contato';
    public string $expectedDate = '';
    public string $notes = '';

    // Fechar negócio
    public ?int $winId = null;
    public ?int $loseId = null;
    public string $lostReason = '';
    public string $lostNote = '';

    public function mount(): void
    {
        $novo = (int) request()->query('novo');
        if ($novo && ($c = $this->ownedClient($novo))) {
            $this->openForm();
            $this->clientId = $c->id;
        }
    }

    protected function ownedClient(?int $id): ?Client
    {
        return $id ? Client::where('user_id', Auth::id())->find($id) : null;
    }

    protected function deal(int $id): CrmDeal
    {
        return CrmDeal::ownedBy(Auth::id())->findOrFail($id);
    }

    public function setMobileStage(string $stage): void
    {
        $this->mobileStage = isset(CrmDeal::STAGES[$stage]) ? $stage : 'contato';
        if (! in_array($this->mobileStage, CrmDeal::OPEN, true)) {
            $this->showClosed = true;
        }
    }

    public function toggleClosed(): void
    {
        $this->showClosed = ! $this->showClosed;
        if (! $this->showClosed && ! in_array($this->mobileStage, CrmDeal::OPEN, true)) {
            $this->mobileStage = 'contato';
        }
    }

    // ===== Formulário =====

    public function openForm(?string $stage = null, ?int $clientId = null): void
    {
        $this->resetForm();
        if ($stage && in_array($stage, CrmDeal::OPEN, true)) {
            $this->stage = $stage;
        }
        if ($clientId && ($c = $this->ownedClient($clientId))) {
            $this->clientId = $c->id;
            $this->title = '';
        }
        $this->showForm = true;
    }

    public function setWho(string $who): void
    {
        $this->who = $who === 'lead' ? 'lead' : 'cliente';
    }

    public function pickClient(int $id): void
    {
        $this->clientId = $this->ownedClient($id)?->id;
        $this->clientSearch = '';
    }

    public function edit(int $id): void
    {
        $d = $this->deal($id);
        $this->resetForm();
        $this->editingId = $d->id;
        $this->who = $d->client_id ? 'cliente' : 'lead';
        $this->clientId = $d->client_id;
        $this->name = (string) $d->name;
        $this->phone = (string) $d->phone;
        $this->source = (string) $d->source;
        $this->title = $d->title;
        $this->value = $d->value !== null ? number_format((float) $d->value, 2, ',', '.') : '';
        $this->stage = $d->stage;
        $this->expectedDate = $d->expected_date?->toDateString() ?? '';
        $this->notes = (string) $d->notes;
        $this->showForm = true;
    }

    public static function parseMoney(string $v): ?float
    {
        $v = trim(str_replace(['R$', ' '], '', $v));
        if ($v === '') {
            return null;
        }
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }

        return is_numeric($v) ? round((float) $v, 2) : null;
    }

    public function save(): void
    {
        $rules = [
            'title' => 'required|string|max:160',
            'value' => 'nullable|string|max:20',
            'stage' => 'required|in:' . implode(',', array_keys(CrmDeal::STAGES)),
            'expectedDate' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'source' => 'nullable|in:' . implode(',', array_keys(ClientStatus::SOURCES)),
        ];
        if ($this->who === 'lead') {
            $rules['name'] = 'required|string|max:120';
            $rules['phone'] = 'nullable|string|max:30';
        } else {
            $rules['clientId'] = 'required|integer';
        }
        $this->validate($rules, [
            'title.required' => 'Diga o que a pessoa quer (ex.: Mesa de jantar 6 lugares).',
            'name.required' => 'Coloque o nome do contato.',
            'clientId.required' => 'Escolha o cliente ou troque para "Contato novo".',
        ]);
        if ($this->value !== '' && self::parseMoney($this->value) === null) {
            $this->addError('value', 'Valor inválido. Ex.: 1.250,00');

            return;
        }

        $client = $this->who === 'cliente' ? $this->ownedClient($this->clientId) : null;
        if ($this->who === 'cliente' && ! $client) {
            $this->addError('clientId', 'Cliente não encontrado.');

            return;
        }

        $data = [
            'client_id' => $client?->id,
            'name' => $client ? null : trim($this->name),
            'phone' => $client ? null : (trim($this->phone) ?: null),
            'source' => $this->source ?: ($client?->source ?: null),
            'title' => trim($this->title),
            'value' => self::parseMoney($this->value),
            'expected_date' => $this->expectedDate ?: null,
            'notes' => trim($this->notes) ?: null,
        ];

        if ($this->editingId) {
            $d = $this->deal($this->editingId);
            $d->fill($data)->save();
            if ($d->stage !== $this->stage) {
                $this->changeStage($d, $this->stage);
            }
        } else {
            $d = CrmDeal::create($data + [
                'user_id' => Auth::id(),
                'stage' => in_array($this->stage, CrmDeal::OPEN, true) ? $this->stage : 'contato',
                'stage_at' => now(),
                'position' => 0,
            ]);
        }

        $this->showForm = false;
        $this->mobileStage = $d->isOpen() ? $d->stage : $this->mobileStage;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Negócio salvo.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'clientId', 'clientSearch', 'name', 'phone', 'source', 'title', 'value', 'expectedDate', 'notes']);
        $this->who = 'cliente';
        $this->stage = 'contato';
        $this->resetValidation();
    }

    // ===== Mover =====

    protected function changeStage(CrmDeal $d, string $stage): void
    {
        if ($stage === 'ganho') {
            $this->winId = $d->id;

            return;
        }
        if ($stage === 'perdido') {
            $this->loseId = $d->id;
            $this->lostReason = '';
            $this->lostNote = '';

            return;
        }
        $d->update([
            'stage' => $stage,
            'stage_at' => now(),
            'closed_at' => null,
            'lost_reason' => null,
            'lost_note' => null,
        ]);
    }

    public function move(int $id, string $stage): void
    {
        if (! isset(CrmDeal::STAGES[$stage])) {
            return;
        }
        $d = $this->deal($id);
        if ($d->stage === $stage) {
            return;
        }
        $this->changeStage($d, $stage);
    }

    public function next(int $id): void
    {
        $d = $this->deal($id);
        $i = array_search($d->stage, CrmDeal::OPEN, true);
        $next = $i === false ? null : (CrmDeal::OPEN[$i + 1] ?? 'ganho');
        if ($next) {
            $this->changeStage($d, $next);
        }
    }

    /** Lead vira cliente (cadastro com nome, telefone e origem). */
    protected function makeClient(CrmDeal $d): Client
    {
        if ($d->client) {
            return $d->client;
        }

        return DB::transaction(function () use ($d) {
            $c = Client::create([
                'user_id' => Auth::id(),
                'name' => $d->name ?: 'Sem nome',
                'phone' => $d->phone,
                'source' => $d->source,
            ]);
            $d->update(['client_id' => $c->id, 'name' => null, 'phone' => null]);

            return $c;
        });
    }

    public function convertLead(int $id): void
    {
        $c = $this->makeClient($this->deal($id));
        $this->dispatch('notify', type: 'success', message: $c->name . ' agora é cliente.');
    }

    public function win(bool $launchSale)
    {
        if (! $this->winId) {
            return null;
        }
        $d = $this->deal($this->winId);
        $client = $this->makeClient($d);
        $d->update(['stage' => 'ganho', 'stage_at' => now(), 'closed_at' => now(), 'lost_reason' => null, 'lost_note' => null]);
        $this->winId = null;

        if ($launchSale) {
            return $this->redirectRoute('sales.create', ['client_id' => $client->id, 'negocio' => $d->id], navigate: true);
        }
        $this->dispatch('notify', type: 'success', message: 'Negócio ganho! 🎉');

        return null;
    }

    public function lose(): void
    {
        if (! $this->loseId) {
            return;
        }
        $this->validate(['lostReason' => 'required|in:' . implode(',', array_keys(CrmDeal::LOST_REASONS)), 'lostNote' => 'nullable|string|max:255'],
            ['lostReason.required' => 'Escolha o motivo. Ele aparece nos relatórios.']);
        $this->deal($this->loseId)->update([
            'stage' => 'perdido',
            'stage_at' => now(),
            'closed_at' => now(),
            'lost_reason' => $this->lostReason,
            'lost_note' => trim($this->lostNote) ?: null,
        ]);
        $this->loseId = null;
    }

    public function closeDialogs(): void
    {
        $this->winId = null;
        $this->loseId = null;
        $this->resetValidation();
    }

    public function reopen(int $id): void
    {
        $this->deal($id)->update(['stage' => 'negociando', 'stage_at' => now(), 'closed_at' => null, 'lost_reason' => null, 'lost_note' => null]);
        $this->mobileStage = 'negociando';
    }

    public function delete(int $id): void
    {
        $this->deal($id)->delete();
    }

    /** Sugestão: quem se cadastrou nos últimos 30 dias, ainda não comprou e não está no funil. */
    public function addSuggestion(int $clientId): void
    {
        $c = $this->ownedClient($clientId);
        if (! $c) {
            return;
        }
        $this->openForm('contato', $c->id);
    }

    public function render()
    {
        $uid = Auth::id();
        $base = CrmDeal::ownedBy($uid)->with('client:id,name,phone,caminho_foto,google_avatar,source');
        if ($this->search !== '') {
            $s = '%' . $this->search . '%';
            $base->where(fn ($q) => $q->where('title', 'like', $s)->orWhere('name', 'like', $s)->orWhere('phone', 'like', $s)
                ->orWhereHas('client', fn ($c) => $c->withoutGlobalScopes()->where('name', 'like', $s)));
        }
        if ($this->sourceFilter !== '') {
            $base->where('source', $this->sourceFilter);
        }

        $open = (clone $base)->open()->orderByDesc('stage_at')->orderByDesc('id')->limit(300)->get()->groupBy('stage');
        $closed = $this->showClosed
            ? (clone $base)->whereIn('stage', ['ganho', 'perdido'])->where('closed_at', '>=', now()->subDays(60))
                ->orderByDesc('closed_at')->limit(120)->get()->groupBy('stage')
            : collect();

        $columns = [];
        foreach (CrmDeal::STAGES as $key => $def) {
            if (! $this->showClosed && ! in_array($key, CrmDeal::OPEN, true)) {
                continue;
            }
            $items = in_array($key, CrmDeal::OPEN, true) ? ($open[$key] ?? collect()) : ($closed[$key] ?? collect());
            $columns[$key] = $def + ['items' => $items, 'total' => (float) $items->sum('value')];
        }

        // Números do topo (sem filtros de busca)
        $all = CrmDeal::ownedBy($uid);
        $openCount = (clone $all)->open()->count();
        $openValue = (float) (clone $all)->open()->sum('value');
        $monthStart = now()->startOfMonth();
        $wonMonth = (clone $all)->where('stage', 'ganho')->where('closed_at', '>=', $monthStart);
        $wonCount = (clone $wonMonth)->count();
        $wonValue = (float) (clone $wonMonth)->sum('value');
        $since = now()->subDays(90);
        $won90 = (clone $all)->where('stage', 'ganho')->where('closed_at', '>=', $since)->count();
        $lost90 = (clone $all)->where('stage', 'perdido')->where('closed_at', '>=', $since)->count();
        $rate = ($won90 + $lost90) ? (int) round($won90 * 100 / ($won90 + $lost90)) : null;
        $topLost = (clone $all)->where('stage', 'perdido')->where('closed_at', '>=', $since)
            ->select('lost_reason', DB::raw('count(*) as n'))->groupBy('lost_reason')->orderByDesc('n')->first();
        $leads = (clone $all)->open()->whereNull('client_id')->count();

        $suggestions = Client::where('user_id', $uid)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereRaw(ClientStatus::lastSaleSql() . ' is null')
            ->whereNotIn('id', CrmDeal::ownedBy($uid)->whereNotNull('client_id')->select('client_id'))
            ->orderByDesc('created_at')->limit(6)->get(['id', 'name', 'phone', 'source', 'created_at', 'caminho_foto', 'google_avatar']);

        $clientResults = collect();
        if ($this->showForm && $this->who === 'cliente' && ! $this->clientId && mb_strlen(trim($this->clientSearch)) >= 2) {
            $s = '%' . trim($this->clientSearch) . '%';
            $clientResults = Client::where('user_id', $uid)->where(fn ($q) => $q->where('name', 'like', $s)->orWhere('phone', 'like', $s))
                ->orderBy('name')->limit(8)->get(['id', 'name', 'phone', 'caminho_foto', 'google_avatar']);
        }

        return view('livewire.crm.sales-funnel', [
            'columns' => $columns,
            'kpi' => compact('openCount', 'openValue', 'wonCount', 'wonValue', 'rate', 'won90', 'lost90', 'topLost', 'leads'),
            'suggestions' => $suggestions,
            'clientResults' => $clientResults,
            'pickedClient' => $this->ownedClient($this->clientId),
            'winDeal' => $this->winId ? CrmDeal::ownedBy($uid)->with('client')->find($this->winId) : null,
            'loseDeal' => $this->loseId ? CrmDeal::ownedBy($uid)->with('client')->find($this->loseId) : null,
            'sources' => ClientStatus::SOURCES,
        ]);
    }
}
