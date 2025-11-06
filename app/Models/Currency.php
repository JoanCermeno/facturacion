<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $casts = [
        'exchange_rate' => 'float',
        'is_base' => 'boolean',
    ];

    protected $fillable = [
        'companies_id',
        'name',
        'symbol',
        'exchange_rate',
        'is_base',
        'conversion_type',   // <-- ahora lo maneja SOLO el usuario
    ];

    public function company()
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ✅ Convertir desde la moneda hacia la BASE
    public function convertToBase(float $amount): float
    {
        if ($this->conversion_type === 'multiply') {
            return $amount / $this->exchange_rate; 
        }

        if ($this->conversion_type === 'divide') {
            return $amount * $this->exchange_rate;
        }

        throw new \Exception("Tipo de conversión inválido: {$this->conversion_type}");
    }

    // ✅ Convertir desde BASE → otra moneda
    public function convertFromBase(float $amount): float
    {
        if ($this->conversion_type === 'multiply') {
            return $amount * $this->exchange_rate; 
        }

        if ($this->conversion_type === 'divide') {
            return $amount / $this->exchange_rate;
        }

        throw new \Exception("Tipo de conversión inválido: {$this->conversion_type}");
    }
}
