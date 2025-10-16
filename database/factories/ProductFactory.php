<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Companies;
use App\Models\Department;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'companies_id' => Companies::factory(),
            'department_id' => Department::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('DEP???')),
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'cost' => $this->faker->randomFloat(2, 0, 100),
            'base_unit' => $this->faker->randomElement(['unit', 'service','box','pack','pair','dozen','kg','gr','lb','oz','lt','ml','gal','m','cm','mm','inch','sqm','sqft','hour','day','service']),
            
        ];
    }
}
