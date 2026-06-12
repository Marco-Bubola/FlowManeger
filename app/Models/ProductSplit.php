<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSplit extends Model
{
    protected $fillable = ['user_id', 'source_product_id', 'source_action', 'distributed_stock'];

    protected $casts = ['distributed_stock' => 'boolean'];

    public function items()
    {
        return $this->hasMany(ProductSplitItem::class, 'split_id');
    }

    public function source()
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }
}
