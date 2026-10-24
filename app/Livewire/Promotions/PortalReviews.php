<?php

namespace App\Livewire\Promotions;

use App\Models\PortalReview;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Avaliações que os clientes deixaram no catálogo: a loja responde (aparece embaixo
 * da avaliação), esconde do catálogo ou exclui.
 */
class PortalReviews extends Component
{
    public string $filter = 'todas';
    public ?int $replyingId = null;
    public string $replyText = '';

    protected $queryString = ['filter' => ['except' => 'todas']];

    private function review(int $id): ?PortalReview
    {
        return PortalReview::where('user_id', Auth::id())->find($id);
    }

    public function toggleHidden(int $id): void
    {
        if ($r = $this->review($id)) {
            $r->update(['hidden' => ! $r->hidden]);
        }
    }

    public function startReply(int $id): void
    {
        $r = $this->review($id);
        $this->replyingId = $r?->id;
        $this->replyText = (string) $r?->reply;
    }

    public function saveReply(): void
    {
        $this->validate(['replyText' => ['nullable', 'string', 'max:600']]);
        if ($this->replyingId && ($r = $this->review($this->replyingId))) {
            $text = trim($this->replyText);
            $r->update(['reply' => $text ?: null, 'replied_at' => $text ? now() : null]);
            $this->dispatch('notify', type: 'success', message: $text ? 'Resposta publicada.' : 'Resposta tirada.');
        }
        $this->replyingId = null;
        $this->replyText = '';
    }

    public function delete(int $id): void
    {
        $this->review($id)?->delete();
        $this->dispatch('notify', type: 'success', message: 'Avaliação excluída.');
    }

    public function render()
    {
        $uid = Auth::id();
        $base = fn () => PortalReview::where('user_id', $uid);
        $reviews = $base()
            ->when($this->filter === 'sem_resposta', fn ($q) => $q->whereNull('reply')->where('hidden', false))
            ->when($this->filter === 'baixas', fn ($q) => $q->where('rating', '<=', 3))
            ->when($this->filter === 'escondidas', fn ($q) => $q->where('hidden', true))
            ->orderByDesc('created_at')->limit(200)->get();
        $products = Product::withoutGlobalScope('team_visibility')->where('user_id', $uid)->whereIn('id', $reviews->pluck('product_id')->unique())
            ->with('images')->get()->keyBy('id');
        $shown = $base()->where('hidden', false);

        return view('livewire.promotions.portal-reviews', [
            'reviews'  => $reviews,
            'products' => $products,
            'avg'      => round((float) (clone $shown)->avg('rating'), 1),
            'total'    => (clone $shown)->count(),
            'counts'   => [
                'todas'        => $base()->count(),
                'sem_resposta' => $base()->whereNull('reply')->where('hidden', false)->count(),
                'baixas'       => $base()->where('rating', '<=', 3)->count(),
                'escondidas'   => $base()->where('hidden', true)->count(),
            ],
            'catalogUrl' => route('portal.catalog', ['userId' => $uid]),
        ]);
    }
}
