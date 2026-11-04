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
    protected $signature = 'instagram:publicar {--renovar : Só renova os acessos que vencem em até 20 dias}';

    protected $description = 'Publica os posts agendados do Instagram e renova o acesso das contas';

    public function handle(InstagramPublisher $publisher): int
    {
        if ($this->option('renovar')) {
            return $this->renew();
        }

        // Post que ficou "postando" por mais de 15 minutos (processo caiu) volta para a fila uma vez
        InstagramPost::where('status', InstagramPost::POSTANDO)->where('updated_at', '<', now()->subMinutes(15))
            ->where('attempts', '<', 2)->update(['status' => InstagramPost::AGENDADO]);

        $due = InstagramPost::where('status', InstagramPost::AGENDADO)->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')->limit(20)->get();
        foreach ($due as $post) {
            $publisher->publish($post);
            $this->line("#{$post->id} {$post->status}" . ($post->error ? ': ' . $post->error : ''));
        }

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
