<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Company
 * 
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property int $invoice_sequence
 * 
 * @property Collection|Product[] $products
 * @property Collection|Seller[] $sellers
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Company extends Model
{
	protected $table = 'companys';
	public $timestamps = false;

	protected $casts = [
		'invoice_sequence' => 'int'
	];

	protected $fillable = [
		'name',
		'phone',
		'email',
		'invoice_sequence'
	];

	public function products()
	{
		return $this->hasMany(Product::class);
	}

	public function sellers()
	{
		return $this->hasMany(Seller::class, 'fk_company');
	}

	public function users()
	{
		return $this->hasMany(User::class, 'fk_company');
	}
}
