<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

	use HasFactory;

	protected $table = 'customers';

	protected $fillable = [
		'id_card',
		'name',
		'email',
		'address',
		'phone'
	];


	public function company()
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }
}
