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
 * @property float $commission_percentage
 * @property int $fk_company
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
		'commission_percentage' => 'float',
		'fk_company' => 'int'
	];

	protected $fillable = [
		'name',
		'ci',
		'commission_percentage',
		'fk_company'
	];

	public function company()
	{
		return $this->belongsTo(Company::class, 'fk_company');
	}
}
