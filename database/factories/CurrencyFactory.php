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
        $currencies = [
            ['name' => 'Dólar Americano', 'symbol' => 'USD'],
            ['name' => 'Bolívar Venezolano', 'symbol' => 'VES'],
            ['name' => 'Peso Colombiano', 'symbol' => 'COP'],
            ['name' => 'Euro', 'symbol' => 'EUR'],
        ];

        $currency = $this->faker->randomElement($currencies);

        return [
            'name' => $currency['name'],
            'symbol' => $currency['symbol'],
            'exchange_rate' => $this->faker->randomFloat(2, 0.1, 5000),
            'is_base' => false,
            'companies_id' => \App\Models\Companies::factory(),
        ];
    }

}