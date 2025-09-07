<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $pass
 * @property int $fk_company
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Company $company
 * @property Collection|Role[] $roles
 *
 * @package App\Models
 */
class User extends Model
{
	protected $table = 'users';

	protected $casts = [
		'fk_company' => 'int'
	];

	protected $fillable = [
		'name',
		'pass',
		'fk_company'
	];

	public function company()
	{
		return $this->belongsTo(Company::class, 'fk_company');
	}

	public function roles()
	{
		return $this->hasMany(Role::class, 'fk_user');
	}
}
