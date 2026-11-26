<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Um item do catálogo de escolhas: tecido + cor (ou só a cor), amostra e valor a mais. */
class ChoiceItem extends Model
{
    protected $fillable = ['user_id', 'choice_catalog_id', 'material', 'name', 'code', 'hex', 'image', 'extra_price', 'active', 'position'];

    protected $casts = [
        'extra_price' => 'float',
        'active' => 'boolean',
        'position' => 'integer',
    ];

    public function catalog()
    {
        return $this->belongsTo(ChoiceCatalog::class, 'choice_catalog_id');
    }

    /** "Corino · Preto" (ou só "Preto"). */
    public function label(): string
    {
        return trim(($this->material ? $this->material . ' · ' : '') . $this->name);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
