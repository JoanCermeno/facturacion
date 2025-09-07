<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Customer
 * 
 * @property int $id
 * @property string $id_card
 * @property string $name
 * @property string|null $email
 * @property string|null $adres
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Customer extends Model
{
	protected $table = 'customers';

	protected $fillable = [
		'id_card',
		'name',
		'email',
		'adres',
		'phone'
	];
}
