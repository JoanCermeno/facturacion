<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Companies;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'companies_id' => Companies::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('PAY????')),
            'description' => ucfirst($this->faker->words(2, true)),
            'currency_id' => Currency::factory(),
            'status' => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}
