<?php

namespace App\Services\Instagram;

use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use Illuminate\Support\Facades\Log;

/** Publica um post do app no Instagram: monta os containers, espera a Meta processar e publica. */
class InstagramPublisher
{
    /** @var callable|null para os testes não esperarem */
    public static $sleeper = null;

    public function publish(InstagramPost $post): InstagramPost
    {
        $s = InstagramSetting::where('user_id', $post->user_id)->first();
        if (! $s || ! $s->connected()) {
            return $this->fail($post, 'O Instagram não está conectado. Conecte em Configurações › Instagram.');
        }

        $post->forceFill(['status' => InstagramPost::POSTANDO, 'attempts' => $post->attempts + 1, 'error' => null])->save();

        if ($post->kind === 'reel') {
            return $this->startReel($post, new InstagramClient($s));
        }

        try {
            $client = new InstagramClient($s);
            $urls = $post->imageUrls();
            if (! $urls) {
                throw new \RuntimeException('O post não tem fotos.');
            }

            if ($post->kind === 'story') {
                $creation = $client->createImage($urls[0], 'story');
            } elseif (count($urls) === 1) {
                $creation = $client->createImage($urls[0], '', (string) $post->caption);
            } else {
                $children = [];
                foreach (array_slice($urls, 0, 10) as $u) {
                    $children[] = $client->createImage($u, 'item');
                }
                foreach ($children as $c) {
                    $this->waitReady($client, $c);
                }
                $creation = $client->createCarousel($children, (string) $post->caption);
            }
            $this->waitReady($client, $creation);

            $this->finish($post, $client, $creation);
        } catch (\Throwable $e) {
            Log::warning('Instagram: post não publicado', ['post' => $post->id, 'error' => $e->getMessage()]);

            return $this->fail($post, $e->getMessage());
        }

        return $post;
    }

    /**
     * Reels: manda o vídeo para a Meta e espera um pouco. Vídeo demora para processar; se não
     * ficar pronto em ~30 segundos o post fica "postando" e checkReel termina depois
     * (a rotina de cada minuto ou a tela de posts aberta).
     */
    private function startReel(InstagramPost $post, InstagramClient $client): InstagramPost
    {
        try {
            if (! $post->video) {
                throw new \RuntimeException('O Reels não tem vídeo.');
            }
            $cover = $post->imageUrls()[0] ?? null;
            $id = $client->createReel((string) $post->videoUrl(), (string) $post->caption, $cover, (bool) $post->share_to_feed);
            $post->forceFill(['container_id' => $id])->save();
            for ($i = 0; $i < 10; $i++) {
                if ($this->checkReel($post, $client)->status !== InstagramPost::POSTANDO) {
                    return $post;
                }
                self::$sleeper ? (self::$sleeper)(3) : sleep(3);
            }
        } catch (\Throwable $e) {
            Log::warning('Instagram: Reels não publicado', ['post' => $post->id, 'error' => $e->getMessage()]);

            return $this->fail($post, $e->getMessage());
        }

        return $post;
    }

    /** Confere um Reels que está processando: publica quando a Meta terminar. */
    public function checkReel(InstagramPost $post, ?InstagramClient $client = null): InstagramPost
    {
        if ($post->kind !== 'reel' || $post->status !== InstagramPost::POSTANDO || ! $post->container_id) {
            return $post;
        }
        try {
            $client ??= new InstagramClient(InstagramSetting::where('user_id', $post->user_id)->firstOrFail());
            $status = $client->containerStatus($post->container_id);
            if (in_array($status, ['ERROR', 'EXPIRED'], true)) {
                return $this->fail($post, 'O Instagram não aceitou o vídeo. Use MP4 ou MOV vertical (9:16), de 3 segundos a 15 minutos.');
            }
            if (in_array($status, ['FINISHED', 'PUBLISHED'], true)) {
                $this->finish($post, $client, $post->container_id);
            } elseif ($post->updated_at && $post->updated_at->lt(now()->subMinutes(40))) {
                return $this->fail($post, 'O Instagram demorou demais para processar o vídeo. Tente de novo.');
            }
        } catch (\Throwable $e) {
            return $this->fail($post, $e->getMessage());
        }

        return $post;
    }

    private function finish(InstagramPost $post, InstagramClient $client, string $creation): void
    {
        $mediaId = $client->publish($creation);
        $post->forceFill([
            'status' => InstagramPost::POSTADO,
            'media_id' => $mediaId,
            'permalink' => $client->permalink($mediaId),
            'published_at' => now(),
            'error' => null,
        ])->save();
    }

    /** Busca os resultados (curtidas, alcance...) de um post publicado. */
    public function refreshResults(InstagramPost $post): InstagramPost
    {
        if ($post->status !== InstagramPost::POSTADO || ! $post->media_id) {
            return $post;
        }
        $s = InstagramSetting::where('user_id', $post->user_id)->first();
        if (! $s || ! $s->connected()) {
            return $post;
        }
        try {
            $r = (new InstagramClient($s))->results($post->media_id, $post->kind);
            $permalink = $r['permalink'] ?? null;
            unset($r['permalink']);
            // Story some depois de 24 horas: guarda o último resultado que deu para ler
            if ($r || ! $post->metrics) {
                $post->forceFill(['metrics' => array_merge($post->metrics ?? [], $r), 'metrics_at' => now()] + ($permalink ? ['permalink' => $permalink] : []))->save();
            }
        } catch (\Throwable $e) {
            Log::info('Instagram: resultados não lidos', ['post' => $post->id, 'error' => $e->getMessage()]);
        }

        return $post;
    }

    private function waitReady(InstagramClient $client, string $id): void
    {
        for ($i = 0; $i < 15; $i++) {
            $status = $client->containerStatus($id);
            if (in_array($status, ['FINISHED', 'PUBLISHED'], true)) {
                return;
            }
            if (in_array($status, ['ERROR', 'EXPIRED'], true)) {
                throw new \RuntimeException('O Instagram não aceitou a foto. Tente outra foto ou outro formato.');
            }
            self::$sleeper ? (self::$sleeper)(2) : sleep(2);
        }
        throw new \RuntimeException('O Instagram demorou para processar as fotos. Tente de novo em alguns minutos.');
    }

    private function fail(InstagramPost $post, string $msg): InstagramPost
    {
        $post->forceFill(['status' => InstagramPost::ERRO, 'error' => mb_substr($msg, 0, 500)])->save();

        return $post;
    }
}
