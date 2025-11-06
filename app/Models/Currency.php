<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Currency
 * 
 * @property int $id
 * @property string $symbol
 * @property float $exchange_rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
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
        'conversion_type',
    ];

    public function company()
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // para convertir desde la base a otra moneda
    public function convertToBase(float $amount): float
    {
        if ($this->conversion_type === 'multiply') {
            return $amount * $this->exchange_rate;
        }

        if ($this->conversion_type === 'divide') {
            return $amount / $this->exchange_rate;
        }

        throw new \Exception("Tipo de conversión inválido: {$this->conversion_type}");
    }
    // para convertir desde otra moneda a la base
    public function convertFromBase(float $amount): float
    {
        if ($this->conversion_type === 'multiply') {
            return $amount / $this->exchange_rate;
        }

        if ($this->conversion_type === 'divide') {
            return $amount * $this->exchange_rate;
        }

        throw new \Exception("Tipo de conversión inválido: {$this->conversion_type}");
    }

    public function setExchangeRateAttribute($value)
    {
        $this->attributes['exchange_rate'] = $value;

        // Si es base → rate obligado a 1
        if (!empty($this->attributes['is_base']) && $this->attributes['is_base']) {
            $this->attributes['exchange_rate'] = 1;
            $this->attributes['conversion_type'] = 'multiply';
            return;
        }

        // No-base: decide multiply o divide
        // Si rate es mayor a 1 → divide (caso BTC, EUR contra USD)
        // Si rate es menor a 1 → multiply
        $this->attributes['conversion_type'] =
            $value >= 1 ? 'divide' : 'multiply';
    }

    public function setIsBaseAttribute($value)
    {
        $this->attributes['is_base'] = $value;

        if ($value == true) {
            // Base SIEMPRE tiene rate = 1 y multiply
            $this->attributes['exchange_rate'] = 1;
            $this->attributes['conversion_type'] = 'multiply';
        }
    }


}
