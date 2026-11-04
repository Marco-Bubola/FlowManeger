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
        $this->tab = in_array($tab, ['todos', 'agendado', 'postado', 'erro'], true) ? $tab : 'todos';
        $this->resetPage();
    }

    private function find(int $id): ?InstagramPost
    {
        return InstagramPost::where('user_id', Auth::id())->find($id);
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
        $q = (clone $base)->with('product')
            ->when($this->tab !== 'todos', fn ($q) => $this->tab === 'erro' ? $q->whereIn('status', [InstagramPost::ERRO, InstagramPost::CANCELADO]) : $q->where('status', $this->tab))
            ->orderByRaw("case when status = 'agendado' then 0 when status = 'postando' then 1 else 2 end")
            ->orderByRaw("case when status = 'agendado' then scheduled_at end asc")
            ->orderByDesc('updated_at');

        return view('livewire.instagram.posts-index', [
            'posts' => $this->infinitePaginate($q, 24),
            'counts' => $counts,
            'setting' => InstagramSetting::forUser(Auth::id()),
        ]);
    }
}
