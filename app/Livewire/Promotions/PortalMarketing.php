<?php

namespace App\Livewire\Promotions;

use App\Services\Portal\ProductFeed;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Google e Instagram: aparecer no Google (mapa do site), a lista de produtos para o
 * Google Shopping e a loja do Instagram/Facebook, e os códigos de verificação.
 */
class PortalMarketing extends Component
{
    public array $cfg = [];

    public function mount(): void
    {
        $this->cfg = ProductFeed::settingsFor(Auth::id());
    }

    public function toggleGoogle(): void
    {
        $this->cfg['google'] = ! $this->cfg['google'];
        $this->save($this->cfg['google'] ? 'O Google já pode mostrar a sua loja.' : 'A loja saiu da busca do Google.');
    }

    public function updatedCfg(): void
    {
        $this->save('Salvo.');
    }

    private function save(string $msg): void
    {
        $this->validate(['cfg.gverify' => ['nullable', 'string', 'max:300'], 'cfg.mverify' => ['nullable', 'string', 'max:300'], 'cfg.brand' => ['nullable', 'string', 'max:70']]);
        $typed = $this->cfg;
        $this->cfg = ProductFeed::save(Auth::id(), $this->cfg);
        foreach (['gverify' => 'Google', 'mverify' => 'Meta'] as $k => $who) {
            if (trim((string) ($typed[$k] ?? '')) !== '' && $this->cfg[$k] === '') {
                $this->addError('cfg.' . $k, "Não reconheci o código do {$who}. Cole a linha inteira que ele mostra (começa com <meta).");
            }
        }
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function render()
    {
        $uid = Auth::id();

        return view('livewire.promotions.portal-marketing', [
            'check' => ProductFeed::check($uid),
            'feedUrl' => ProductFeed::feedUrl($uid),
            'sitemapUrl' => ProductFeed::sitemapUrl($uid),
            'catalogUrl' => route('portal.catalog', ['userId' => $uid]),
        ]);
    }
}
