<?php

namespace App\Http\Controllers;

use App\Models\InstagramSetting;
use App\Services\Instagram\InstagramClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** Login do Instagram: manda para a tela do Instagram e recebe a volta com o código. */
class InstagramAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $s = InstagramSetting::forUser(Auth::id());
        if (! $s->hasApp()) {
            return redirect()->route('settings.instagram')->with('ig_error', 'Salve o ID e a chave secreta do app antes de conectar.');
        }

        $state = Str::random(40);
        $request->session()->put('instagram_state', $state);

        return redirect()->away(InstagramClient::authorizeUrl($s, route('instagram.callback'), $state));
    }

    public function callback(Request $request)
    {
        $expected = $request->session()->pull('instagram_state');
        if ($request->filled('error') || $request->filled('error_reason')) {
            return redirect()->route('settings.instagram')->with('ig_error', 'A conexão foi cancelada no Instagram.');
        }
        if (! $expected || ! hash_equals($expected, (string) $request->query('state')) || ! $request->filled('code')) {
            return redirect()->route('settings.instagram')->with('ig_error', 'A volta do Instagram não confere. Clique em Conectar de novo.');
        }

        $s = InstagramSetting::forUser(Auth::id());
        try {
            // O Instagram às vezes acrescenta "#_" no fim do código
            (new InstagramClient($s))->connect(preg_replace('/#_$/', '', (string) $request->query('code')), route('instagram.callback'));
        } catch (\Throwable $e) {
            $s->update(['last_error' => mb_substr($e->getMessage(), 0, 500)]);

            return redirect()->route('settings.instagram')->with('ig_error', $e->getMessage());
        }

        return redirect()->route('settings.instagram')->with('ig_ok', 'Instagram conectado: @' . $s->username);
    }
}
