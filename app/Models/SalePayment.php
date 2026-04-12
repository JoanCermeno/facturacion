<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalePayment extends Model
{
    use HasFactory;

    protected $table = 'sale_payments';

    protected $fillable = [
        'sale_id',
        'payment_method_id',
        'amount',
        'currency_id',
        'amount_in_base',
    ];

    protected $casts = [
        'amount' => 'float',
        'amount_in_base' => 'float',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
