<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashRegisterDetail extends Model
{
    use HasFactory;

    protected $table = 'cash_register_details';

    protected $fillable = [
        'cash_register_id',
        'payment_method_id',
        'initial_amount',
        'final_amount',
    ];

    protected $casts = [
        'initial_amount' => 'float',
        'final_amount' => 'float',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
