<?php

namespace App\Livewire\Instagram;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use App\Services\Instagram\InstagramPublisher;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Posts do Instagram feitos pelo app: agendados, postados e os que não foram. */
class PostsIndex extends Component
{
    use WithPagination, WithInfiniteScroll;

    #[Url(as: 'aba', except: 'todos')]
    public string $tab = 'todos';

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['todos', 'agendado', 'postado', 'melhores', 'erro'], true) ? $tab : 'todos';
        $this->resetPage();
    }

    private function find(int $id): ?InstagramPost
    {
        return InstagramPost::where('user_id', Auth::id())->find($id);
    }

    /** Busca de novo curtidas, alcance e visualizações (no máximo a cada 10 minutos). */
    public function refreshResults(InstagramPublisher $publisher): void
    {
        $last = InstagramPost::where('user_id', Auth::id())->max('metrics_at');
        if ($last && now()->diffInMinutes($last, true) < 10) {
            session()->flash('ig_ok', 'Os resultados já foram atualizados agora há pouco.');

            return;
        }
        InstagramPost::where('user_id', Auth::id())->where('status', InstagramPost::POSTADO)->whereNotNull('media_id')
            ->where('published_at', '>=', now()->subDays(60))
            ->orderByDesc('published_at')->limit(60)->get()
            ->each(fn ($p) => $publisher->refreshResults($p));
        session()->flash('ig_ok', 'Resultados atualizados.');
    }

    /** Reels processando: a tela aberta confere a cada 10 segundos (não depende do cron). */
    public function checkProcessing(InstagramPublisher $publisher): void
    {
        InstagramPost::where('user_id', Auth::id())->where('status', InstagramPost::POSTANDO)
            ->where('kind', 'reel')->whereNotNull('container_id')->get()
            ->each(fn ($p) => $publisher->checkReel($p));
    }

    public function cancel(int $id): void
    {
        $p = $this->find($id);
        if ($p && $p->status === InstagramPost::AGENDADO) {
            $p->update(['status' => InstagramPost::CANCELADO]);
        }
    }

    public function retry(int $id, InstagramPublisher $publisher): void
    {
        $p = $this->find($id);
        if ($p && in_array($p->status, [InstagramPost::ERRO, InstagramPost::CANCELADO], true)) {
            $publisher->publish($p);
            session()->flash($p->status === InstagramPost::POSTADO ? 'ig_ok' : 'ig_error', $p->status === InstagramPost::POSTADO ? 'Postado no Instagram!' : $p->error);
        }
    }

    public function remove(int $id): void
    {
        $p = $this->find($id);
        if ($p && $p->status !== InstagramPost::POSTANDO) {
            $p->deleteFiles();
            $p->delete();
        }
    }

    public function render()
    {
        $base = InstagramPost::where('user_id', Auth::id());
        $counts = (clone $base)->selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status');
        $q = (clone $base)->with('product');
        if ($this->tab === 'melhores') {
            $views = "coalesce(cast(json_unquote(json_extract(metrics, '$.views')) as unsigned), 0)";
            $acts = "coalesce(cast(json_unquote(json_extract(metrics, '$.likes')) as unsigned), 0) + coalesce(cast(json_unquote(json_extract(metrics, '$.comments')) as unsigned), 0) + coalesce(cast(json_unquote(json_extract(metrics, '$.saved')) as unsigned), 0) + coalesce(cast(json_unquote(json_extract(metrics, '$.shares')) as unsigned), 0)";
            $q->where('status', InstagramPost::POSTADO)->whereNotNull('metrics')->orderByRaw("{$views} desc")->orderByRaw("{$acts} desc");
        } else {
            $q->when($this->tab !== 'todos', fn ($q) => $this->tab === 'erro' ? $q->whereIn('status', [InstagramPost::ERRO, InstagramPost::CANCELADO]) : $q->where('status', $this->tab))
                ->orderByRaw("case when status = 'agendado' then 0 when status = 'postando' then 1 else 2 end")
                ->orderByRaw("case when status = 'agendado' then scheduled_at end asc")
                ->orderByDesc('updated_at');
        }

        // Resumo dos últimos 30 dias
        $recent = (clone $base)->where('status', InstagramPost::POSTADO)->where('published_at', '>=', now()->subDays(30))->get(['id', 'kind', 'metrics', 'product_id', 'images']);
        $sum = fn ($k) => (int) $recent->sum(fn ($p) => (int) ($p->metrics[$k] ?? 0));
        $summary = [
            'posts' => $recent->count(),
            'views' => $sum('views'),
            'reach' => $sum('reach'),
            'interactions' => $sum('likes') + $sum('comments') + $sum('saved') + $sum('shares'),
            'saved' => $sum('saved'),
            'best' => $recent->filter(fn ($p) => $p->metrics)->sortByDesc(fn ($p) => [(int) ($p->metrics['views'] ?? 0), $p->interactions()])->first(),
            'hasMetrics' => $recent->contains(fn ($p) => ! empty($p->metrics)),
            'noInsights' => $recent->contains(fn ($p) => ! empty($p->metrics['_no_insights'])),
        ];

        return view('livewire.instagram.posts-index', [
            'posts' => $this->infinitePaginate($q, 24),
            'counts' => $counts,
            'summary' => $summary,
            'processing' => (clone $base)->where('status', InstagramPost::POSTANDO)->where('kind', 'reel')->exists(),
            'lastResults' => (clone $base)->max('metrics_at'),
            'setting' => InstagramSetting::forUser(Auth::id()),
        ]);
    }
}
