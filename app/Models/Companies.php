<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller;

class Companies extends Model
{
    use HasFactory;

    // Si tu tabla se llama 'companys' y no 'companies', debes especificarlo
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'address', // Asegúrate de añadir esta columna a tu tabla 'companys'
        'phone',
        'email',
        'rif',
        'invoice_sequence',
    ];

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'companies_id');
    }

    /**
     * Get the products for the company.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'companies_id');
    }

    /**
     * Get the sellers for the company.
     */
    public function sellers()
    {
        return $this->hasMany(Seller::class, 'companies_id');
    }
}