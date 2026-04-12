<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Sale
 * 
 * @property int $id
 * @property int|null $cash_register_id
 * @property int $user_id
 * @property int $client_id
 * @property int|null $seller_id
 * @property int $company_id
 * @property int $invoice_id
 * @property float $commission_percentage
 * @property float $total
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Sale extends Model
{
	use HasFactory;

	protected $table = 'sales';

	protected $casts = [
		'user_id' => 'int',
		'client_id' => 'int',
		'seller_id' => 'int',
		'company_id' => 'int',
		'invoice_id' => 'int',
		'cash_register_id' => 'int',
		'commission_percentage' => 'float',
		'total' => 'float',
	];

	protected $fillable = [
		'user_id',
		'client_id',
		'seller_id',
		'company_id',
		'invoice_id',
		'cash_register_id',
		'commission_percentage',
		'total',
		'status',
	];

	// ─── Relaciones existentes ──────────────────────────
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class, 'client_id');
	}

	public function seller()
	{
		return $this->belongsTo(Seller::class);
	}

	public function company()
	{
		return $this->belongsTo(Companies::class, 'company_id');
	}

	public function invoice()
	{
		return $this->belongsTo(Invoice::class);
	}

	// ─── Relaciones nuevas (POS) ────────────────────────
	public function cashRegister()
	{
		return $this->belongsTo(CashRegister::class);
	}

	public function items()
	{
		return $this->hasMany(SaleItem::class);
	}

	public function payments()
	{
		return $this->hasMany(SalePayment::class);
	}
}
