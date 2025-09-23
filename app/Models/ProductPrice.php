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

	protected $fillable = [
		'product_unit_id',
        'price_type_id',
        'price_usd',
        'profit_percentage',
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	 public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function priceType()
    {
        return $this->belongsTo(PriceType::class);
    }


}
