<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Sale
 * 
 * @property int $id
 * @property int $user_id
 * @property int $client_id
 * @property int|null $seller_id
 * @property int $company_id
 * @property int $invoice_id
 * @property float $commission_percentage
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 * @property Customer $customer
 * @property Seller|null $seller
 * @property Company $company
 * @property Invoice $invoice
 *
 * @package App\Models
 */
class Sale extends Model
{
	protected $table = 'sales';

	protected $casts = [
		'user_id' => 'int',
		'client_id' => 'int',
		'seller_id' => 'int',
		'company_id' => 'int',
		'invoice_id' => 'int',
		'commission_percentage' => 'float'
	];

	protected $fillable = [
		'user_id',
		'client_id',
		'seller_id',
		'company_id',
		'invoice_id',
		'commission_percentage'
	];

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
		return $this->belongsTo(Company::class);
	}

	public function invoice()
	{
		return $this->belongsTo(Invoice::class);
	}
}
