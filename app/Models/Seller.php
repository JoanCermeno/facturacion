<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Seller
 * 
 * @property int $id
 * @property string $name
 * @property string $ci
 * @property float $commission
 * @property int $company_id
 * 
 * @property Company $company
 *
 * @package App\Models
 */
class Seller extends Model
{
	protected $table = 'sellers';
	public $timestamps = false;

	protected $casts = [
		'commission' => 'float',
		'companies_id' => 'int'
	];

	protected $fillable = [
		'name',
		'ci',
		'phone',
		'commission',
		'companies_id'
	];

	public function company()
	{
		return $this->belongsTo(Company::class, 'companies_id');
	}
}
