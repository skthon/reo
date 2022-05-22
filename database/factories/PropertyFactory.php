<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'                 => $this->faker->name(),
            'address'              => $this->faker->address(),
            'price'                => $this->faker->numberBetween(100000, 10000000),
            'area'                 => $this->faker->numberBetween(100, 10000),
            'rooms'                => $this->faker->numberBetween(4, 25),
            'return_actual'        => $this->faker->randomFloat(2, 3, 99),
            'parking'              => $this->faker->boolean(),
            'year_of_construction' => $this->faker->numberBetween(1970, 2022),
        ];
    }
}
