<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashRegister extends Model
{
    use HasFactory;

    protected $table = 'cash_registers';

    protected $fillable = [
        'user_id',
        'companies_id',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // ─── Relaciones ─────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }

    public function details()
    {
        return $this->hasMany(CashRegisterDetail::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // ─── Helpers ────────────────────────────────────────
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
