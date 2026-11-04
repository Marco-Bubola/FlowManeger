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

            $mediaId = $client->publish($creation);
            $post->forceFill([
                'status' => InstagramPost::POSTADO,
                'media_id' => $mediaId,
                'permalink' => $client->permalink($mediaId),
                'published_at' => now(),
                'error' => null,
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Instagram: post não publicado', ['post' => $post->id, 'error' => $e->getMessage()]);

            return $this->fail($post, $e->getMessage());
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
