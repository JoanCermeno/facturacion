<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    // Si tu tabla se llama 'companys' y no 'companies', debes especificarlo
    protected $table = 'companys';

    protected $fillable = [
        'name',
        'address', // Asegúrate de añadir esta columna a tu tabla 'companys'
        'phone',
        'email',
        'invoice_sequence',
    ];

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'fk_company');
    }

    /**
     * Get the products for the company.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    // Si tienes una tabla 'sellers' separada y no la unificas con 'users'
    // public function sellers()
    // {
    //     return $this->hasMany(Seller::class, 'fk_company');
    // }
}