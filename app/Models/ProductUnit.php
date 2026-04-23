<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
    /**
     * Intercepta el 'unit_type' antes de guardarlo y lo estandariza.
     */
    protected function unitType(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucfirst(strtolower(trim($value))),
        );
    }
}
