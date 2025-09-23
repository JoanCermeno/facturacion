<?php

namespace Database\Factories;

use App\Models\Companies;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompaniesFactory extends Factory
{
    protected $model = Companies::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'rif' => strtoupper($this->faker->bothify('J#########')),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email'   => $this->faker->unique()->companyEmail,
        ];
    }
}
