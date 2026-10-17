<?php

namespace App\Jobs;

use App\Models\WhatsAppSetting;
use App\Services\WhatsApp\WhatsAppBot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/** Lê o print e responde depois que o webhook já devolveu 200 para a Meta. */
class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 120;

    public function __construct(public int $userId, public array $message)
    {
    }

    public function handle(WhatsAppBot $bot): void
    {
        $setting = WhatsAppSetting::where('user_id', $this->userId)->first();
        if ($setting && $setting->ready()) {
            $bot->handle($setting, $this->message);
        }
    }
}
