<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \App\Models\Companies;


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
 * @property Companies $company
 * @property Department|null $department
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	use HasFactory;

	protected $casts = [
		'cost' => 'float',
		'companies_id' => 'int',
		'department_id' => 'int',
		'stock' => 'float'
	];

	protected $fillable = [
		'code',
		'name',
		'description',
		'cost',
		'base_unit',
		'stock',
		'companies_id',
		'department_id'
	];
	//Comprobamos si la empresa tieene seteado el campo de auto generar codigos del producto. 
	protected static function booted()
    {
        static::creating(function ($product) {
           $company = \App\Models\Companies::find($product->companies_id);


            if ($company && $company->auto_code_products && empty($product->code)) {
                $prefix = $company->product_code_prefix ?? 'PRD-';
                $lastId = self::where('companies_id', $company->id)->max('id') + 1;
                $product->code = $prefix . str_pad($lastId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

	public function company()
	{
		return $this->belongsTo(Companies::class);
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

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

}
