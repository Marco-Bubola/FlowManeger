<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'filename',       // nome do arquivo em storage/products/
        'alt_text',
        'source',         // upload | mercadolivre
        'source_url',     // URL original do ML CDN
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = ['url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * URL pública da imagem.
     * - source=upload → storage/products/{path}
     * - source=mercadolivre → source_url direto (imagem hospeada no ML CDN)
     */
    public function getUrlAttribute(): string
    {
        if ($this->source === 'mercadolivre' && $this->source_url) {
            return $this->source_url;
        }

        return asset('storage/products/' . $this->filename);
    }
}
