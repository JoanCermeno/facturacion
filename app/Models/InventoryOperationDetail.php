<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOperationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'product_id',
        'product_unit_id',
        'quantity',
    ];

    public function operation()
    {
        return $this->belongsTo(InventoryOperation::class, 'operation_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
