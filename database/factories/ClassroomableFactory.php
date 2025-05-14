<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classroomable>
 */
class ClassroomableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => rand(1,15),
            'classroomable_id' => rand(5,15),
            'classroomable_type' => 'App\\Models\\User'
        ];
    }
}
