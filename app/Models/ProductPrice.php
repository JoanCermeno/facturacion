<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductPrice
 * 
 * @property int $id
 * @property int $product_id
 * @property int $price_type_id
 * @property float $amount
 * @property int $currency_id
 * 
 * @property Product $product
 * @property PriceType $price_type
 * @property Currency $currency
 *
 * @package App\Models
 */
class ProductPrice extends Model
{
	protected $table = 'product_prices';
	public $timestamps = false;

	protected $casts = [
		'product_id' => 'int',
		'price_type_id' => 'int',
		'amount' => 'float',
		'currency_id' => 'int'
	];

	protected $fillable = [
		'product_id',
		'price_type_id',
		'amount',
		'currency_id'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function price_type()
	{
		return $this->belongsTo(PriceType::class);
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}
}
