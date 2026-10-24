<?php

namespace App\Livewire\Promotions;

use App\Models\PortalCoupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Cupons de desconto do catálogo: a loja cria o código (porcentagem ou valor),
 * com mínimo, validade e limite de usos. O cliente digita no carrinho.
 */
class PortalCoupons extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $code = '';
    public string $kind = 'percent';
    public string $value = '';
    public string $min_total = '';
    public string $starts_at = '';
    public string $ends_at = '';
    public string $max_uses = '';
    public bool $once_per_client = true;
    public bool $active = true;

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $c = $this->coupon($id);
        if (! $c) {
            return;
        }
        $this->resetErrorBag();
        $this->editingId = $c->id;
        $this->code = $c->code;
        $this->kind = $c->kind;
        $this->value = $this->num($c->value);
        $this->min_total = $c->min_total ? $this->num($c->min_total) : '';
        $this->starts_at = $c->starts_at?->format('Y-m-d') ?? '';
        $this->ends_at = $c->ends_at?->format('Y-m-d') ?? '';
        $this->max_uses = $c->max_uses ? (string) $c->max_uses : '';
        $this->once_per_client = (bool) $c->once_per_client;
        $this->active = (bool) $c->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->code = PortalCoupon::normalize($this->code);
        foreach (['value', 'min_total'] as $f) {
            $this->{$f} = str_replace(',', '.', trim($this->{$f}));
        }
        $data = $this->validate([
            'code'            => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/u',
                Rule::unique('portal_coupons', 'code')->where('user_id', Auth::id())->ignore($this->editingId)],
            'kind'            => ['required', 'in:percent,fixed'],
            'value'           => ['required', 'numeric', 'gt:0', $this->kind === 'percent' ? 'max:100' : 'max:999999'],
            'min_total'       => ['nullable', 'numeric', 'min:0'],
            'starts_at'       => ['nullable', 'date'],
            'ends_at'         => array_filter(['nullable', 'date', $this->starts_at ? 'after_or_equal:starts_at' : null]),
            'max_uses'        => ['nullable', 'integer', 'min:1'],
            'once_per_client' => ['boolean'],
            'active'          => ['boolean'],
        ], [
            'code.regex' => 'Use só letras, números, - ou _ (sem espaço).',
            'code.unique' => 'Você já tem um cupom com esse código.',
            'value.max' => $this->kind === 'percent' ? 'A porcentagem vai até 100.' : 'Valor alto demais.',
            'ends_at.after_or_equal' => 'O fim tem que ser depois do começo.',
        ], ['code' => 'código', 'value' => 'desconto', 'min_total' => 'pedido mínimo', 'ends_at' => 'fim', 'max_uses' => 'limite de usos']);

        $row = [
            'code' => $data['code'],
            'kind' => $data['kind'],
            'value' => $data['value'],
            'min_total' => $data['min_total'] !== '' && $data['min_total'] !== null ? $data['min_total'] : null,
            'starts_at' => $data['starts_at'] ?: null,
            'ends_at' => $data['ends_at'] ?: null,
            'max_uses' => $data['max_uses'] !== '' && $data['max_uses'] !== null ? (int) $data['max_uses'] : null,
            'once_per_client' => $this->once_per_client,
            'active' => $this->active,
        ];
        if ($this->editingId && ($c = $this->coupon($this->editingId))) {
            $c->update($row);
        } else {
            PortalCoupon::create($row + ['user_id' => Auth::id()]);
        }
        $this->showForm = false;
        $this->dispatch('notify', type: 'success', message: 'Cupom ' . $data['code'] . ' salvo.');
    }

    public function toggle(int $id): void
    {
        if ($c = $this->coupon($id)) {
            $c->update(['active' => ! $c->active]);
        }
    }

    public function delete(int $id): void
    {
        if ($c = $this->coupon($id)) {
            $c->delete();
            $this->dispatch('notify', type: 'success', message: 'Cupom ' . $c->code . ' excluído.');
        }
    }

    private function coupon(int $id): ?PortalCoupon
    {
        return PortalCoupon::where('user_id', Auth::id())->find($id);
    }

    private function num($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    }

    private function resetForm(): void
    {
        $this->resetErrorBag();
        $this->editingId = null;
        $this->code = '';
        $this->kind = 'percent';
        $this->value = '';
        $this->min_total = '';
        $this->starts_at = '';
        $this->ends_at = '';
        $this->max_uses = '';
        $this->once_per_client = true;
        $this->active = true;
    }

    /** Situação do cupom para a etiqueta: ativo, agendado, vencido, esgotado ou desligado. */
    public static function state(PortalCoupon $c, int $uses): array
    {
        $today = now()->startOfDay();

        return match (true) {
            ! $c->active => ['Desligado', 'slate'],
            $c->ends_at && $today->gt($c->ends_at) => ['Vencido', 'rose'],
            $c->max_uses && $uses >= $c->max_uses => ['Esgotado', 'amber'],
            $c->starts_at && $today->lt($c->starts_at) => ['Agendado', 'sky'],
            default => ['Valendo', 'emerald'],
        };
    }

    public function render()
    {
        $coupons = PortalCoupon::where('user_id', Auth::id())->orderByDesc('active')->orderByDesc('id')->get();
        $uses = \App\Models\ClientQuoteRequest::where('user_id', Auth::id())
            ->whereNotNull('coupon_code')->where('status', '!=', 'rejected')
            ->selectRaw('coupon_code, COUNT(*) as n, SUM(discount) as total')
            ->groupBy('coupon_code')->get()->keyBy('coupon_code');

        return view('livewire.promotions.portal-coupons', [
            'coupons' => $coupons,
            'uses' => $uses,
            'catalogUrl' => route('portal.catalog', ['userId' => Auth::id()]),
        ]);
    }
}
