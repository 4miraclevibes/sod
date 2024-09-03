<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'name', 'price', 'is_visible', 'capital_price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function variantStocks()
    {
        return $this->hasMany(VariantStock::class);
    }
}
