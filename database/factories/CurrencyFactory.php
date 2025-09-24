<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->currencyCode(),
            'symbol' => $this->faker->currencySymbol(),
            'exchange_rate' => $this->faker->randomFloat(2, 1, 5000),
            'is_base' => $this->faker->boolean(10), // 10% chance of being the base currency
            'companies_id' => \App\Models\Companies::factory(),
        ];
    }
}