<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Webhook do WhatsApp Cloud API, um endereço por usuário. */
class WhatsAppWebhookController extends Controller
{
    /** A Meta confere o endereço: devolve o hub.challenge se o token bater. */
    public function verify(Request $request, int $user)
    {
        $s = WhatsAppSetting::where('user_id', $user)->first();
        if ($s && $request->query('hub_mode') === 'subscribe' && hash_equals((string) $s->verify_token, (string) $request->query('hub_verify_token'))) {
            return response((string) $request->query('hub_challenge'), 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request, int $user)
    {
        $s = WhatsAppSetting::where('user_id', $user)->first();
        if (! $s || ! $s->ready()) {
            return response()->json(['ok' => true]); // 200: a Meta não fica reenviando
        }

        // Com a chave secreta do app salva, só aceita o que veio assinado pela Meta
        if (filled($s->app_secret)) {
            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), (string) $s->app_secret);
            if (! hash_equals($expected, (string) $request->header('X-Hub-Signature-256'))) {
                Log::warning('WhatsApp: assinatura inválida', ['user' => $user]);

                return response('Invalid signature', 401);
            }
        }

        foreach ((array) $request->input('entry', []) as $entry) {
            foreach ((array) ($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];
                // Mensagem para outro número do mesmo app não é deste usuário
                if ((string) data_get($value, 'metadata.phone_number_id') !== (string) $s->phone_number_id) {
                    continue;
                }
                foreach ((array) ($value['messages'] ?? []) as $msg) {
                    ProcessWhatsAppMessage::dispatchAfterResponse($user, $msg);
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}
