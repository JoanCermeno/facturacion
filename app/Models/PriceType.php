<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PriceType
 * 
 * @property int $id
 * @property string $name
 * 
 * @property Collection|ProductPrice[] $product_prices
 *
 * @package App\Models
 */
class PriceType extends Model
{
	protected $table = 'price_types';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];

	public function product_prices()
	{
		return $this->hasMany(ProductPrice::class);
	}
}
