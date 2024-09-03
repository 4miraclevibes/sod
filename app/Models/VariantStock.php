<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantStock extends Model
{
    use HasFactory;
    protected $fillable = ['variant_id', 'stock', 'capital_price', 'price'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
