<?php

namespace App\Livewire\Hooks;

use Livewire\Mechanisms\HandleRequests\HandleRequests;

use function Livewire\on;
use function Livewire\store;

/**
 * Mostra no aviso global (toast) as mensagens de session()->flash()
 * criadas durante uma ação Livewire que não troca de página.
 *
 * Sem isso, a mensagem só aparecia se a própria view desenhasse o alerta,
 * e a maioria das listas (produtos, vendas, livro-caixa...) não desenha.
 */
class FlashToNotify
{
    protected const TYPES = ['success', 'error', 'warning', 'info'];

    public static function register(): void
    {
        on('render', function ($component) {
            return function ($html) use ($component) {
                if (! app()->has('session.store') || ! app(HandleRequests::class)->isLivewireRequest()) {
                    return;
                }

                // Com redirect, a mensagem segue na sessão e aparece na próxima página.
                if (store($component)->get('redirect')) {
                    return;
                }

                $messages = [];

                foreach (self::TYPES as $type) {
                    if (session()->has($type)) {
                        $messages[$type] = session($type);
                    }
                }

                if (session()->has('message')) {
                    $type = session('message_type', 'success');
                    $messages[in_array($type, self::TYPES, true) ? $type : 'success'] ??= session('message');
                }

                foreach ($messages as $type => $message) {
                    if (! is_string($message) || $message === '') {
                        continue;
                    }

                    // A própria tela já mostra esse alerta: não duplica.
                    if (is_string($html) && str_contains($html, e($message))) {
                        continue;
                    }

                    $component->dispatch('notify', type: $type, message: $message);
                }

                session()->forget([...self::TYPES, 'message', 'message_type']);
            };
        });
    }
}
