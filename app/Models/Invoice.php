<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invoice
 * 
 * @property int $id
 * @property int $company_id
 * @property int $invoice_number
 * @property string $type
 * @property Carbon $created_at
 * 
 * @property Company $company
 * @property Collection|Sale[] $sales
 *
 * @package App\Models
 */
class Invoice extends Model
{
	protected $table = 'invoices';
	public $timestamps = false;

	protected $casts = [
		'company_id' => 'int',
		'invoice_number' => 'int'
	];

	protected $fillable = [
		'company_id',
		'invoice_number',
		'type'
	];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function sales()
	{
		return $this->hasMany(Sale::class);
	}
}
