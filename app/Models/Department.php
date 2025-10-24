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
    //Comprobamos si la empresa tieene seteado el campo de auto generar codigos del departamento.
    protected static function booted()
    {
        static::creating(function ($department) {
            $company = \App\Models\Companies::find($department->companies_id);

            if ($company && $company->auto_code_departments && empty($department->code)) {
                $prefix = $company->department_code_prefix ?? 'DPT-';
                $lastId = self::where('companies_id', $company->companies_id)->max('id') + 1;
                $department->code = $prefix . str_pad($lastId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

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
