<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma lista do catálogo de escolhas da loja (ex.: Estofados, Cores de ferragem). */
class ChoiceCatalog extends Model
{
    protected $fillable = ['user_id', 'name', 'kind', 'position'];

    protected $casts = ['position' => 'integer'];

    public function items()
    {
        return $this->hasMany(ChoiceItem::class)->orderBy('position')->orderBy('id');
    }
}
