<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSplitItem extends Model
{
    protected $fillable = [
        'split_id', 'result_product_id', 'variation_parent_id',
        'mode', 'quantity', 'stock_assigned',
    ];

    public function split()
    {
        return $this->belongsTo(ProductSplit::class, 'split_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'result_product_id');
    }
}
