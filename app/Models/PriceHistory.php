<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $table = 'price_histories';

    protected $fillable = [
        'product_price_id',
        'user_id',
        'old_price',
        'new_price',
        'old_profit_percentage',
        'new_profit_percentage',
        'change_reason',
    ];

    public function productPrice()
    {
        return $this->belongsTo(ProductPrice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
