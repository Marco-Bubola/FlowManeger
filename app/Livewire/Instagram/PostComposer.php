<?php

namespace App\Livewire\Instagram;

use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\Instagram\CaptionBuilder;
use App\Services\Instagram\InstagramImages;
use App\Services\Instagram\InstagramPublisher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Novo post no Instagram: escolhe as fotos (do produto, enviadas ou a arte da oferta),
 * o formato, a legenda pronta, e posta na hora ou agenda.
 */
class PostComposer extends Component
{
    use WithFileUploads;

    public const MAX_PHOTOS = 10;

    #[Url(as: 'produto', except: null)]
    public ?int $productId = null;

    #[Url(as: 'promocao', except: null)]
    public ?int $promotionId = null;

    public ?int $postId = null;

    public string $kind = 'feed'; // feed | story
    public string $format = 'quadrado'; // quadrado | retrato
    public string $fit = 'inteira'; // inteira | preencher
    public string $bg = '#ffffff';

    /** Fotos escolhidas, na ordem (links). */
    public array $picked = [];
    /** Fotos enviadas pela pessoa ou artes criadas (links no disco public). */
    public array $extra = [];

    public string $caption = '';
    public bool $captionEdited = false;

    public bool $scheduling = false;
    public string $scheduleDate = '';
    public string $scheduleTime = '';

    public string $search = '';

    public $uploads = [];
    public $artFile = null;

    public function mount($post = null): void
    {
        $post = $post ? InstagramPost::where('user_id', Auth::id())->findOrFail((int) $post) : null;
        $tz = Promotion::tz();
        $next = now($tz)->addHour()->startOfHour();
        $this->scheduleDate = $next->format('Y-m-d');
        $this->scheduleTime = $next->format('H:i');

        if ($post && $post->exists) {
            abort_unless(in_array($post->status, [InstagramPost::AGENDADO, InstagramPost::ERRO], true), 404);
            $this->postId = $post->id;
            $this->productId = $post->product_id;
            $this->promotionId = $post->promotion_id;
            $this->kind = $post->kind;
            $this->format = $post->format;
            $this->caption = (string) $post->caption;
            $this->captionEdited = true;
            $this->extra = $post->imageUrls();
            $this->picked = $this->extra;
            $this->fit = 'inteira';
            if ($post->scheduled_at) {
                $this->scheduling = true;
                $this->scheduleDate = $post->scheduled_at->copy()->setTimezone($tz)->format('Y-m-d');
                $this->scheduleTime = $post->scheduled_at->copy()->setTimezone($tz)->format('H:i');
            }

            return;
        }

        $promo = $this->promotion();
        if ($promo && ! $this->productId) {
            $this->productId = $promo->product_id;
        }
        if (! $this->product()) {
            $this->productId = null;
        }
        $this->picked = array_slice($this->productPhotos(), 0, 1);
        $this->caption = $this->suggestedCaption();
    }

    // ─── Dados ────────────────────────────────────────────────────

    public function product(): ?Product
    {
        return $this->productId ? Product::where('user_id', Auth::id())->find($this->productId) : null;
    }

    public function promotion(): ?Promotion
    {
        if ($this->promotionId) {
            return Promotion::ofUser(Auth::id())->with('product')->find($this->promotionId);
        }
        $p = $this->product();

        return $p ? $p->livePromotion() : null;
    }

    /** Fotos do produto e das variações (família toda), sem repetir. */
    public function productPhotos(): array
    {
        $p = $this->product();
        if (! $p) {
            return [];
        }
        $root = $p->parent_id ?: $p->id;
        $family = Product::where('user_id', Auth::id())
            ->where(fn ($q) => $q->where('id', $root)->orWhere('parent_id', $root))
            ->with('images')->orderByRaw('id = ? desc', [$p->id])->orderBy('id')->limit(30)->get();

        return $family->flatMap(fn (Product $x) => $x->all_images)->unique()->take(40)->values()->all();
    }

    private function suggestedCaption(): string
    {
        return CaptionBuilder::build(InstagramSetting::forUser(Auth::id()), $this->product(), $this->promotionId || $this->promotion() ? $this->promotion() : null);
    }

    // ─── Ações da tela ────────────────────────────────────────────

    public function chooseProduct(int $id): void
    {
        $this->productId = Product::where('user_id', Auth::id())->whereKey($id)->value('id');
        $this->promotionId = null;
        $this->search = '';
        $this->picked = array_values(array_unique(array_merge(array_slice($this->productPhotos(), 0, 1), array_intersect($this->picked, $this->extra))));
        if (! $this->captionEdited) {
            $this->caption = $this->suggestedCaption();
        }
    }

    public function clearProduct(): void
    {
        $this->productId = null;
        $this->promotionId = null;
        $this->picked = array_values(array_intersect($this->picked, $this->extra));
        if (! $this->captionEdited) {
            $this->caption = $this->suggestedCaption();
        }
    }

    public function togglePhoto(string $url): void
    {
        if (in_array($url, $this->picked, true)) {
            $this->picked = array_values(array_diff($this->picked, [$url]));

            return;
        }
        if (! in_array($url, array_merge($this->productPhotos(), $this->extra), true)) {
            return;
        }
        if (count($this->picked) >= self::MAX_PHOTOS) {
            $this->addError('picked', 'O Instagram aceita até 10 fotos por post.');

            return;
        }
        $this->picked[] = $url;
    }

    public function movePhoto(int $from, int $to): void
    {
        if (! isset($this->picked[$from]) || $to < 0 || $to >= count($this->picked)) {
            return;
        }
        $item = array_splice($this->picked, $from, 1);
        array_splice($this->picked, $to, 0, $item);
    }

    public function updatedUploads(): void
    {
        $this->validate(['uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192']], ['uploads.*.max' => 'Cada foto pode ter até 8 MB.', 'uploads.*.image' => 'Envie só fotos.'], ['uploads.*' => 'foto']);
        foreach ((array) $this->uploads as $file) {
            $url = $this->storeExtra($file->getRealPath(), $file->getClientOriginalExtension() ?: 'jpg');
            if (count($this->picked) < self::MAX_PHOTOS) {
                $this->picked[] = $url;
            }
        }
        $this->uploads = [];
    }

    /** Arte da oferta desenhada na tela (canvas) e enviada como arquivo: entra como primeira foto. */
    public function updatedArtFile(): void
    {
        $this->validate(['artFile' => ['image', 'max:8192']], [], ['artFile' => 'arte']);
        $url = $this->storeExtra($this->artFile->getRealPath(), 'jpg');
        $this->artFile = null;
        array_unshift($this->picked, $url);
        $this->picked = array_slice(array_values(array_unique($this->picked)), 0, self::MAX_PHOTOS);
        $this->dispatch('ig-art-ready');
    }

    private function storeExtra(string $path, string $ext): string
    {
        $dest = 'instagram/' . Auth::id() . '/enviadas/' . Str::random(24) . '.' . strtolower($ext);
        Storage::disk('public')->put($dest, file_get_contents($path));
        $url = Storage::disk('public')->url($dest);
        $this->extra[] = $url;

        return $url;
    }

    public function updatedCaption(): void
    {
        $this->captionEdited = true;
    }

    public function resetCaption(): void
    {
        $this->caption = $this->suggestedCaption();
        $this->captionEdited = false;
    }

    public function postNow(InstagramPublisher $publisher)
    {
        $posts = $this->createPosts(null);
        if (! $posts) {
            return null;
        }
        $ok = 0;
        $error = null;
        foreach ($posts as $post) {
            $post = $publisher->publish($post);
            $post->status === InstagramPost::POSTADO ? $ok++ : $error = $post->error;
        }
        if ($error && ! $ok) {
            $this->addError('publish', $error);

            return null;
        }
        session()->flash('ig_ok', $ok === 1 ? 'Postado no Instagram!' : "{$ok} stories postados no Instagram!");

        return $this->redirectRoute('instagram.index', navigate: true);
    }

    public function saveSchedule()
    {
        $tz = Promotion::tz();
        try {
            $when = Carbon::createFromFormat('Y-m-d H:i', $this->scheduleDate . ' ' . $this->scheduleTime, $tz);
        } catch (\Throwable $e) {
            $this->addError('schedule', 'Escolha o dia e a hora.');

            return null;
        }
        if ($when->lt(now($tz)->addMinutes(2))) {
            $this->addError('schedule', 'Escolha uma hora daqui a pelo menos 2 minutos.');

            return null;
        }
        if ($when->gt(now($tz)->addDays(75))) {
            $this->addError('schedule', 'Dá para agendar até 75 dias para frente.');

            return null;
        }
        $posts = $this->createPosts($when->copy()->utc());
        if (! $posts) {
            return null;
        }
        session()->flash('ig_ok', 'Agendado para ' . $when->locale('pt_BR')->translatedFormat('d/m \à\s H:i') . '.');

        return $this->redirectRoute('instagram.index', navigate: true);
    }

    /** Valida, prepara as fotos e grava o(s) post(s). Story: um post para cada foto. */
    private function createPosts(?Carbon $scheduledAt): array
    {
        $this->resetErrorBag();
        $s = InstagramSetting::forUser(Auth::id());
        if (! $s->connected()) {
            $this->addError('publish', 'Conecte o Instagram em Configurações › Instagram antes de postar.');

            return [];
        }
        $allowed = array_merge($this->productPhotos(), $this->extra);
        $picked = array_values(array_filter($this->picked, fn ($u) => in_array($u, $allowed, true)));
        if (! $picked) {
            $this->addError('picked', 'Escolha pelo menos uma foto.');

            return [];
        }
        $this->caption = mb_substr(trim($this->caption), 0, CaptionBuilder::MAX);
        if ($this->kind === 'feed' && CaptionBuilder::countHashtags($this->caption) > 30) {
            $this->addError('caption', 'O Instagram aceita até 30 hashtags. Tire algumas.');

            return [];
        }

        $size = $this->kind === 'story' ? 'story' : ($this->format === 'retrato' ? 'retrato' : 'quadrado');
        try {
            $paths = array_map(fn ($u) => InstagramImages::prepare(Auth::id(), $u, $size, $this->fit, $this->bg), array_slice($picked, 0, self::MAX_PHOTOS));
        } catch (\Throwable $e) {
            $this->addError('picked', $e->getMessage());

            return [];
        }

        $base = [
            'user_id' => Auth::id(),
            'product_id' => $this->product()?->id,
            'promotion_id' => $this->promotion()?->id,
            'kind' => $this->kind,
            'format' => $size,
            'caption' => $this->kind === 'story' ? null : $this->caption,
            'status' => InstagramPost::AGENDADO,
            'scheduled_at' => $scheduledAt ?? now(),
        ];

        // Editando um agendado: substitui
        if ($this->postId && ($old = InstagramPost::where('user_id', Auth::id())->find($this->postId))) {
            $old->deleteFiles();
            $old->delete();
        }

        $groups = $this->kind === 'story' ? array_map(fn ($p) => [$p], $paths) : [$paths];

        return array_map(fn ($imgs) => InstagramPost::create($base + ['images' => $imgs]), $groups);
    }

    public function render()
    {
        $s = InstagramSetting::forUser(Auth::id());
        $product = $this->product();
        $promo = $this->promotion();
        $results = collect();
        if (! $product && mb_strlen(trim($this->search)) >= 2) {
            $term = '%' . trim($this->search) . '%';
            $results = Product::where('user_id', Auth::id())
                ->whereNull('parent_id')
                ->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('product_code', 'like', $term))
                ->orderBy('name')->limit(12)->get();
        } elseif (! $product) {
            $results = Product::where('user_id', Auth::id())->whereNull('parent_id')
                ->availableForSale()->latest('updated_at')->limit(8)->get();
        }
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');

        return view('livewire.instagram.post-composer', [
            'setting' => $s,
            'connected' => $s->connected(),
            'product' => $product,
            'promo' => $promo,
            'photos' => $this->productPhotos(),
            'results' => $results,
            'hashtagCount' => CaptionBuilder::countHashtags($this->caption),
            'art' => [
                'name' => $product ? Str::limit(CaptionBuilder::title((string) $product->name), 60) : '',
                'price' => $promo ? $money($promo->promo_price) : ($product && $product->price_sale > 0 ? $money($product->price_sale) : ''),
                'old' => $promo ? $money($promo->original_price) : '',
                'off' => $promo ? '-' . $promo->discount_percent . '%' : '',
                'ends' => $promo && $promo->ends_at ? 'Até ' . $promo->endsLocal()->format('d/m') : '',
                'store' => \App\Services\Portal\StoreIdentity::for(Auth::id())['name'] ?? '',
                'insta' => $s->username ? '@' . $s->username : '',
            ],
        ]);
    }
}
