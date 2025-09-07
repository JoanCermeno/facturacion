<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 * 
 * @property int $id
 * @property string $role
 * @property int $fk_user
 * @property string $phone
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Role extends Model
{
	protected $table = 'roles';
	public $timestamps = false;

	protected $casts = [
		'fk_user' => 'int'
	];

	protected $fillable = [
		'role',
		'fk_user',
		'phone'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'fk_user');
	}
}
