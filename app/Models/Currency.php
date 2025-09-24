<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function company()
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }
}
