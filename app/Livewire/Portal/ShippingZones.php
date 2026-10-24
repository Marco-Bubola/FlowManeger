<?php

namespace App\Livewire\Portal;

use App\Models\PaymentSetting;
use App\Models\PortalShippingZone;
use App\Services\Portal\ShippingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Configurações › Pagamentos online › Frete por região: regiões de entrega por
 * faixa de CEP ou cidade, com taxa, frete grátis acima de e prazo.
 */
class ShippingZones extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $kind = 'cep';
    public string $cep_from = '';
    public string $cep_to = '';
    public string $city = '';
    public string $uf = '';
    public string $fee = '';
    public string $free_above = '';
    public string $eta = '';
    public bool $active = true;

    public string $outside = 'fee';
    public string $testCep = '';
    public ?array $testResult = null;

    public function mount(): void
    {
        $this->outside = PaymentSetting::for(Auth::id())?->shipping_outside ?: 'fee';
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $z = $this->zone($id);
        if (! $z) {
            return;
        }
        $this->resetForm();
        $this->editingId = $z->id;
        $this->name = $z->name;
        $this->kind = $z->kind;
        $this->cep_from = PortalShippingZone::formatCep($z->cep_from);
        $this->cep_to = PortalShippingZone::formatCep($z->cep_to);
        $this->city = (string) $z->city;
        $this->uf = (string) $z->uf;
        $this->fee = $this->num($z->fee);
        $this->free_above = $z->free_above ? $this->num($z->free_above) : '';
        $this->eta = (string) $z->eta;
        $this->active = (bool) $z->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        foreach (['fee', 'free_above'] as $f) {
            $this->{$f} = str_replace(',', '.', trim($this->{$f}));
        }
        $this->cep_from = PortalShippingZone::cleanCep($this->cep_from);
        $this->cep_to = PortalShippingZone::cleanCep($this->cep_to);
        $this->uf = strtoupper(trim($this->uf));
        $isCep = $this->kind === 'cep';
        $this->validate([
            'name'       => ['required', 'string', 'max:80'],
            'kind'       => ['required', 'in:cep,city'],
            'cep_from'   => $isCep ? ['required', 'digits:8'] : ['nullable'],
            'cep_to'     => $isCep ? ['required', 'digits:8'] : ['nullable'],
            'city'       => $isCep ? ['nullable'] : ['required', 'string', 'max:80'],
            'uf'         => $isCep ? ['nullable'] : ['nullable', 'alpha', 'size:2'],
            'fee'        => ['required', 'numeric', 'min:0', 'max:99999'],
            'free_above' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'eta'        => ['nullable', 'string', 'max:60'],
        ], [
            'cep_from.digits' => 'CEP com 8 números.',
            'cep_to.digits' => 'CEP com 8 números.',
            'uf.size' => 'Use a sigla, ex.: SP.',
        ], ['name' => 'nome', 'cep_from' => 'CEP inicial', 'cep_to' => 'CEP final', 'city' => 'cidade', 'fee' => 'frete', 'free_above' => 'frete grátis acima de', 'eta' => 'prazo']);

        if ($isCep && $this->cep_to < $this->cep_from) {
            $this->addError('cep_to', 'O CEP final tem que ser maior que o inicial.');

            return;
        }

        $row = [
            'name' => trim($this->name),
            'kind' => $this->kind,
            'cep_from' => $isCep ? $this->cep_from : null,
            'cep_to' => $isCep ? $this->cep_to : null,
            'city' => $isCep ? null : trim($this->city),
            'uf' => $isCep ? null : ($this->uf ?: null),
            'fee' => (float) ($this->fee ?: 0),
            'free_above' => $this->free_above !== '' && (float) $this->free_above > 0 ? (float) $this->free_above : null,
            'eta' => trim($this->eta) ?: null,
            'active' => $this->active,
        ];
        if ($this->editingId && ($z = $this->zone($this->editingId))) {
            $z->update($row);
        } else {
            PortalShippingZone::create($row + ['user_id' => Auth::id()]);
        }
        $this->showForm = false;
        $this->testResult = null;
        $this->dispatch('notify', type: 'success', message: 'Região ' . $row['name'] . ' salva.');
    }

    public function toggle(int $id): void
    {
        if ($z = $this->zone($id)) {
            $z->update(['active' => ! $z->active]);
        }
    }

    public function delete(int $id): void
    {
        if ($z = $this->zone($id)) {
            $z->delete();
            $this->dispatch('notify', type: 'success', message: 'Região excluída.');
        }
    }

    public function updatedOutside(string $v): void
    {
        $s = PaymentSetting::firstOrNew(['user_id' => Auth::id()]);
        $s->shipping_outside = $v === 'none' ? 'none' : 'fee';
        $s->save();
        $this->testResult = null;
    }

    public function test(): void
    {
        $this->testResult = ShippingService::quote(Auth::id(), $this->testCep, 0);
    }

    private function zone(int $id): ?PortalShippingZone
    {
        return PortalShippingZone::where('user_id', Auth::id())->find($id);
    }

    private function num($v): string
    {
        return number_format((float) $v, 2, ',', '');
    }

    private function resetForm(): void
    {
        $this->resetErrorBag();
        $this->editingId = null;
        $this->name = $this->cep_from = $this->cep_to = $this->city = $this->uf = $this->fee = $this->free_above = $this->eta = '';
        $this->kind = 'cep';
        $this->active = true;
    }

    public function render()
    {
        $s = PaymentSetting::for(Auth::id());

        return view('livewire.portal.shipping-zones', [
            'zones' => PortalShippingZone::where('user_id', Auth::id())->orderBy('kind')->orderBy('cep_from')->orderBy('name')->get(),
            'defaultFee' => (float) ($s?->delivery_fee ?? 0),
            'deliveryOn' => ShippingService::deliveryOn($s),
        ]);
    }
}
