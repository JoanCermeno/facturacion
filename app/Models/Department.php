<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 


class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'companies_id',
        'code',
        'description',
        'type'
    ];

    // 🔗 Relación: cada departamento pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // 🔗 Relación: un departamento puede tener muchos productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
