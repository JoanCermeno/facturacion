<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller;

class Companies extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'address', 
        'phone',
        'email',
        'rif',
        'invoice_sequence',
        'auto_code_products',
        'auto_code_departments',
        'product_code_prefix',
        'department_code_prefix',
        'logo_path',
        'profit_formula'
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