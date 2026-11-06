<?php

namespace App\Console\Commands;

use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use App\Services\Instagram\InstagramClient;
use App\Services\Instagram\InstagramPublisher;
use Illuminate\Console\Command;

/** Publica os posts agendados que chegaram na hora e renova o acesso ao Instagram uma vez por dia. */
class InstagramPublish extends Command
{
    protected $signature = 'instagram:publicar {--renovar : Só renova os acessos que vencem em até 20 dias} {--resultados : Só atualiza curtidas, alcance e visualizações dos posts}';

    protected $description = 'Publica os posts agendados do Instagram e renova o acesso das contas';

    public function handle(InstagramPublisher $publisher): int
    {
        if ($this->option('renovar')) {
            return $this->renew();
        }
        if ($this->option('resultados')) {
            return $this->results($publisher);
        }

        // Reels que a Meta ainda estava processando
        InstagramPost::where('status', InstagramPost::POSTANDO)->where('kind', 'reel')->whereNotNull('container_id')
            ->limit(20)->get()->each(fn ($p) => $publisher->checkReel($p));

        // Post que ficou "postando" por mais de 15 minutos (processo caiu) volta para a fila uma vez
        InstagramPost::where('status', InstagramPost::POSTANDO)->whereNull('container_id')->where('updated_at', '<', now()->subMinutes(15))
            ->where('attempts', '<', 2)->update(['status' => InstagramPost::AGENDADO]);

        $due = InstagramPost::where('status', InstagramPost::AGENDADO)->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')->limit(20)->get();
        foreach ($due as $post) {
            $publisher->publish($post);
            $this->line("#{$post->id} {$post->status}" . ($post->error ? ': ' . $post->error : ''));
        }

        return self::SUCCESS;
    }

    /** Posts dos últimos 60 dias (stories: só nas primeiras 24 horas, depois a Meta apaga os números). */
    private function results(InstagramPublisher $publisher): int
    {
        InstagramPost::where('status', InstagramPost::POSTADO)->whereNotNull('media_id')
            ->where('published_at', '>=', now()->subDays(60))
            ->where(fn ($q) => $q->where('kind', '!=', 'story')->orWhere('published_at', '>=', now()->subDay()))
            ->orderByDesc('published_at')->limit(200)->get()
            ->each(fn ($p) => $publisher->refreshResults($p));

        return self::SUCCESS;
    }

    private function renew(): int
    {
        InstagramSetting::whereNotNull('access_token')->where('token_expires_at', '<=', now()->addDays(20))
            ->where('token_expires_at', '>', now())
            ->get()->each(function (InstagramSetting $s) {
                try {
                    (new InstagramClient($s))->refresh();
                    $s->update(['last_error' => null]);
                    $this->line("{$s->username} renovado");
                } catch (\Throwable $e) {
                    $s->update(['last_error' => mb_substr($e->getMessage(), 0, 500)]);
                    $this->warn("{$s->username}: {$e->getMessage()}");
                }
            });

        return self::SUCCESS;
    }
}
