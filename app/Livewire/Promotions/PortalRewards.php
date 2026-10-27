<?php

namespace App\Livewire\Promotions;

use App\Models\Client;
use App\Models\ClientQuoteRequest;
use App\Models\PortalPoint;
use App\Services\Portal\Rewards;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Pix e pontos: desconto automático para quem paga no Pix e o programa de pontos
 * (ganha a cada compra, troca por desconto no próximo pedido), com o extrato dos clientes.
 */
class PortalRewards extends Component
{
    public array $cfg = [];

    // Ajuste manual de pontos
    public string $adjClient = '';
    public string $adjPoints = '';
    public string $adjNote = '';
    public string $adjSearch = '';

    public function mount(): void
    {
        $this->cfg = Rewards::for(Auth::id());
    }

    public function updatedCfg(): void
    {
        $this->save('Salvo.');
    }

    public function togglePoints(): void
    {
        $this->cfg['pts'] = ! $this->cfg['pts'];
        $this->save($this->cfg['pts'] ? 'Pontos ligados.' : 'Pontos desligados. Quem já tem pontos não perde.');
    }

    public function setPix($pct): void
    {
        $this->cfg['pix'] = $pct;
        $this->save((float) $pct > 0 ? 'Desconto no Pix salvo.' : 'Desconto no Pix desligado.');
    }

    private function save(string $msg): void
    {
        $this->cfg = Rewards::save(Auth::id(), $this->cfg);
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function pickClient(int $id): void
    {
        $this->adjClient = (string) $id;
        $this->adjSearch = '';
    }

    public function adjust(): void
    {
        $this->validate([
            'adjClient' => ['required', 'integer'],
            'adjPoints' => ['required', 'integer', 'not_in:0', 'between:-1000000,1000000'],
            'adjNote'   => ['nullable', 'string', 'max:160'],
        ], [], ['adjClient' => 'cliente', 'adjPoints' => 'pontos', 'adjNote' => 'motivo']);
        $client = Client::withoutGlobalScope('team_visibility')->where('user_id', Auth::id())->find((int) $this->adjClient);
        if (! $client) {
            $this->addError('adjClient', 'Escolha um cliente da lista.');

            return;
        }
        $pts = (int) $this->adjPoints;
        if ($pts < 0 && Rewards::balance($client) + $pts < 0) {
            $this->addError('adjPoints', 'O cliente só tem ' . Rewards::balance($client) . ' pontos.');

            return;
        }
        Rewards::adjust(Auth::id(), $client, $pts, $this->adjNote);
        $this->reset(['adjClient', 'adjPoints', 'adjNote']);
        $this->dispatch('notify', type: 'success', message: ($pts > 0 ? 'Pontos dados para ' : 'Pontos tirados de ') . $client->name . '.');
    }

    public function render()
    {
        $uid = Auth::id();
        $ready = Rewards::ready();
        $monthStart = now()->startOfMonth();
        $stats = ['circ' => 0, 'clients' => 0, 'earned' => 0, 'used' => 0, 'pixOrders' => 0, 'pixOff' => 0.0];
        $top = collect();
        $recent = collect();
        if ($ready) {
            $balances = PortalPoint::where('user_id', $uid)->select('client_id', DB::raw('SUM(points) as bal'))->groupBy('client_id')->having('bal', '>', 0)->orderByDesc('bal')->get();
            $stats['circ'] = (int) $balances->sum('bal');
            $stats['clients'] = $balances->count();
            $stats['earned'] = (int) PortalPoint::where('user_id', $uid)->whereIn('kind', ['ganho', 'bonus'])->where('created_at', '>=', $monthStart)->sum('points');
            $stats['used'] = (int) -PortalPoint::where('user_id', $uid)->where('kind', 'uso')->where('created_at', '>=', $monthStart)->sum('points');
            $names = Client::withoutGlobalScope('team_visibility')->whereIn('id', $balances->take(15)->pluck('client_id'))->pluck('name', 'id');
            $top = $balances->take(15)->map(fn ($b) => ['id' => $b->client_id, 'name' => $names[$b->client_id] ?? 'Cliente', 'bal' => (int) $b->bal]);
            $recent = PortalPoint::where('user_id', $uid)->with('client:id,name')->latest('id')->limit(20)->get();
            $pixQuotes = ClientQuoteRequest::where('user_id', $uid)->where('pix_pct', '>', 0)->where('payment_preference', 'pix')
                ->where('status', 'approved')->where('updated_at', '>=', $monthStart)->get();
            $stats['pixOrders'] = $pixQuotes->count();
            $stats['pixOff'] = round($pixQuotes->sum(fn ($q) => Rewards::quotePixOff($q)), 2);
        }
        $found = strlen(trim($this->adjSearch)) >= 2
            ? Client::withoutGlobalScope('team_visibility')->where('user_id', $uid)->where('name', 'like', '%' . trim($this->adjSearch) . '%')->orderBy('name')->limit(8)->get(['id', 'name', 'phone'])
            : collect();
        $picked = $this->adjClient !== '' ? Client::withoutGlobalScope('team_visibility')->where('user_id', $uid)->find((int) $this->adjClient) : null;

        return view('livewire.promotions.portal-rewards', [
            'ready' => $ready,
            'stats' => $stats,
            'top' => $top,
            'recent' => $recent,
            'found' => $found,
            'picked' => $picked,
            'pickedBal' => $picked ? Rewards::balance($picked) : 0,
            'pixOnline' => Rewards::pixOnline($uid),
        ]);
    }
}
