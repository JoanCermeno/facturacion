<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $casts = [
        'fk_company' => 'int'
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'fk_company',
    ];

    protected $hidden = [
        'password',
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
