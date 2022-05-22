<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SearchProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->name(),
            'max_price' => $this->faker->numberBetween(200000, 2000000),
            'min_price' => $this->faker->numberBetween(100000, 200000),
            'max_area'  => $this->faker->numberBetween(150, 1000),
            'min_area'  => $this->faker->numberBetween(50, 150),
            'max_year_of_construction' => $this->faker->numberBetween(1970, 2000),
            'min_year_of_construction' => $this->faker->numberBetween(2000, 2022),
            'max_rooms' => $this->faker->numberBetween(2, 5),
            'min_rooms' => $this->faker->numberBetween(5, 15),
            'max_return_actual' => $this->faker->randomFloat(2, -20, 20),
            'min_return_actual' => $this->faker->randomFloat(2, 20, 40),
        ];
    }
}
