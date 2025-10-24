<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Companies;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'companies_id' => Companies::factory(),
            'name' => $this->faker->name(),
            'id_card' => $this->faker->numerify('V########'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->address(),
        ];
    }
}
