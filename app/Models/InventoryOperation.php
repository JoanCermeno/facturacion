<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_type',
        'operation_number',
        'operation_date',
        'reason',
        'user_id',
        'responsible',
        'company_id',
        'note',
    ];

    /**
     * Relación con el usuario que ejecuta la operación
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los detalles de la operación
     */
    public function details()
    {
        return $this->hasMany(InventoryOperationDetail::class, 'operation_id');
    }
}
