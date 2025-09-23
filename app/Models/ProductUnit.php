<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ProductUnit extends Model
{
    protected $table = 'product_units';

    protected $fillable = [
        'product_id',
        'unit_type',
        'conversion_factor',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'product_unit_id');
    }
}
