<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Department
 * 
 * @property int $id
 * @property string $code
 * @property string $description
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Product[] $products
 *
 * @package App\Models
 */
class Department extends Model
{
	protected $table = 'departments';

	protected $fillable = [
		'code',
		'description',
		'type'
	];

	public function products()
	{
		return $this->hasMany(Product::class);
	}
}
