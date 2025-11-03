<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- agrega esta línea
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory; // <-- agrega HasFactory aquí

    protected $table = 'users';

    protected $casts = [
        'companies_id' => 'int'
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'companies_id',     
    ];

    protected $hidden = [
        'password',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'companies_id');
    }


    // Helpers para checkear rol
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }
}
