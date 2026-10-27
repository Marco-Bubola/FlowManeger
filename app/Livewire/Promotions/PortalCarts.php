<?php

namespace App\Livewire\Promotions;

use App\Models\PortalCart;
use App\Models\WhatsAppSetting;
use App\Services\Portal\CartRecovery;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Carrinhos abandonados do catálogo: liga e desliga a lembrança, escolhe quando e como
 * mandar e a mensagem; lista quem deixou produtos no carrinho, com lembrete em um clique.
 */
class PortalCarts extends Component
{
    public array $cfg = [];

    public string $filter = 'abertos';

    protected $queryString = ['filter' => ['except' => 'abertos']];

    public function mount(): void
    {
        $this->cfg = CartRecovery::settingsFor(Auth::id());
    }

    public function updatedCfg(): void
    {
        $this->save();
    }

    public function toggle(): void
    {
        $this->cfg['on'] = ! $this->cfg['on'];
        $this->save();
    }

    public function setMode(string $mode): void
    {
        $this->cfg['mode'] = $mode;
        $this->save();
    }

    public function resetMsg(): void
    {
        $this->cfg['msg'] = CartRecovery::DEFAULT_MSG;
        $this->save();
    }

    private function save(): void
    {
        $this->validate(['cfg.msg' => ['nullable', 'string', 'max:700']], [], ['cfg.msg' => 'mensagem']);
        $this->cfg = CartRecovery::saveSettings(Auth::id(), $this->cfg);
        $this->dispatch('notify', type: 'success', message: $this->cfg['on'] ? 'Lembrete de carrinho salvo.' : 'Lembrete de carrinho desligado.');
    }

    /** O dono clicou em "Mandar lembrete" (o WhatsApp abre no navegador). */
    public function markSent(int $id): void
    {
        PortalCart::where('user_id', Auth::id())->whereKey($id)->update(['reminded_at' => now(), 'reminder_via' => 'manual']);
    }

    public function dismiss(int $id): void
    {
        PortalCart::where('user_id', Auth::id())->whereKey($id)->update(['dismissed_at' => now()]);
        $this->dispatch('notify', type: 'success', message: 'Carrinho tirado da lista.');
    }

    public function render()
    {
        $uid = Auth::id();
        $all = CartRecovery::ready()
            ? PortalCart::where('user_id', $uid)->where('updated_at', '>=', now()->subDays(60))->with('client')->latest('last_activity_at')->get()
            : collect();
        $open = fn ($c) => ! $c->recovered_at && ! $c->dismissed_at && (float) $c->total > 0 && $c->last_activity_at?->gte(now()->subDays(CartRecovery::MAX_DAYS));
        $groups = [
            'abertos' => $all->filter($open),
            'lembrados' => $all->filter(fn ($c) => $open($c) && $c->reminded_at),
            'recuperados' => $all->filter(fn ($c) => $c->recovered_at && $c->reminded_at),
        ];
        $list = $groups[$this->filter] ?? $groups['abertos'];
        $month = $all->filter(fn ($c) => $c->recovered_at && $c->reminded_at && $c->recovered_at->isCurrentMonth());
        $wa = \Illuminate\Support\Facades\Schema::hasTable('whatsapp_settings') ? WhatsAppSetting::where('user_id', $uid)->first() : null;

        return view('livewire.promotions.portal-carts', [
            'list' => $list->values(),
            'counts' => array_map(fn ($g) => $g->count(), $groups),
            'stats' => [
                'open' => $groups['abertos']->count(),
                'openValue' => (float) $groups['abertos']->sum('total'),
                'recovered' => $month->count(),
                'recoveredValue' => (float) $month->sum('total'),
            ],
            'waReady' => (bool) $wa?->ready(),
            'preview' => $this->preview(),
            'settings' => $this->cfg,
        ]);
    }

    /** Mensagem de exemplo com um carrinho de mentira. */
    private function preview(): string
    {
        $cart = new PortalCart(['user_id' => Auth::id(), 'items' => [['id' => 0, 'name' => 'Perfume Floral 100ml', 'qty' => 1, 'price' => 129.9], ['id' => 0, 'name' => 'Hidratante Corporal', 'qty' => 2, 'price' => 39.9]], 'total' => 209.7, 'token' => 'exemplo']);
        $cart->setRelation('client', new \App\Models\Client(['name' => 'Ana Souza']));

        return CartRecovery::message($cart, $this->cfg);
    }
}
