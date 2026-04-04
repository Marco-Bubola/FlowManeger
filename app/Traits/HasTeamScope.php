<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasTeamScope
{
    protected static function bootHasTeamScope(): void
    {
        static::addGlobalScope('team_visibility', function (Builder $builder) {
            if (app()->runningInConsole() || app()->runningUnitTests()) {
                return;
            }

            $viewer = Auth::user();

            // Usuário não autenticado: bloqueia tudo em vez de expor todos os registros.
            if (!$viewer instanceof User) {
                $builder->whereRaw('1 = 0');
                return;
            }

            // Admin vê todos os registros (bypass intencional).
            if ($viewer->isAdmin()) {
                return;
            }

            $model = $builder->getModel();
            $module = $model->getTeamScopeModule();
            $column = $model->qualifyColumn($model->getTeamOwnerColumn());

            $visibleUserIds = $viewer->visibleTeamUserIds($module);

            $builder->whereIn($column, $visibleUserIds);
        });
    }

    public function scopeVisibleToCurrentUser(Builder $query, ?User $viewer = null): Builder
    {
        $viewer = $viewer ?? Auth::user();

        if (!$viewer instanceof User || $viewer->isAdmin()) {
            return $query;
        }

        return $query->whereIn(
            $this->qualifyColumn($this->getTeamOwnerColumn()),
            $viewer->visibleTeamUserIds($this->getTeamScopeModule())
        );
    }

    public function getTeamOwnerColumn(): string
    {
        return property_exists($this, 'teamOwnerColumn') ? $this->teamOwnerColumn : 'user_id';
    }

    public function getTeamScopeModule(): string
    {
        return property_exists($this, 'teamScopeModule') ? $this->teamScopeModule : 'products';
    }
}