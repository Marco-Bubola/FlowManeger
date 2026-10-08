<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

/**
 * Provider do guard "portal". O cliente entra sem um usuário da loja logado,
 * então a regra de visibilidade da equipe (que esconde tudo de visitantes)
 * não pode valer ao localizar o próprio cadastro dele por id, token ou login.
 */
class PortalClientProvider extends EloquentUserProvider
{
    public function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)->withoutGlobalScope('team_visibility');
    }
}
