<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * Class Product
 * 
 * @property int $id
 * @property string $code
 * @property string $description
 * @property float $price
 * @property string $unit_type
 * @property int $stock
 * @property int $company_id
 * @property int|null $department_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Company $company
 * @property Department|null $department
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	use HasFactory;

	protected $casts = [
		'cost_usd' => 'float',
		'companies_id' => 'int',
		'department_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name',
		'description',
		'cost_usd',
		'base_unit',
		'companies_id',
		'department_id'
	];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function department()
	{
		return $this->belongsTo(Department::class);
	}

	public function prices()
	{
   		return $this->hasMany(ProductPrice::class);
	}
	public function units()
	{
		return $this->hasMany(ProductUnit::class);
	}

	
}
