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

	// 💡 BORRAMOS: public $timestamps = false; (Porque sí tienes created_at y updated_at)

	protected $fillable = [
		'companies_id', // Clave para el multitenant
		'name',
		'slug'
	];

	public function product_prices()
	{
		return $this->hasMany(ProductPrice::class);
	}
}