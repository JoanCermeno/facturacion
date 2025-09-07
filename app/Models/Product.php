<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

	protected $casts = [
		'price' => 'float',
		'stock' => 'int',
		'company_id' => 'int',
		'department_id' => 'int'
	];

	protected $fillable = [
		'code',
		'description',
		'price',
		'unit_type',
		'stock',
		'company_id',
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
}
