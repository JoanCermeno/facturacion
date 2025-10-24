<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'companies_id',
        'code',
        'description',
        'currency_id',
        'status',
    ];

    // 🔗 Relaciones
    public function company()
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
