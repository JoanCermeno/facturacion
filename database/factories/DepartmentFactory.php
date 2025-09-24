<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Companies;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition()
    {
        return [
            'companies_id' => Companies::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('DEP???')),
            'description' => $this->faker->word(),
            'type' => $this->faker->randomElement(['unit', 'service']),
        ];
    }
}
