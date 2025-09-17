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
		'exchange_rate' => 'float'
	];

	protected $fillable = ['name', 'symbol', 'exchange_rate'];
}
