<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(5),
            'name' => fake()->company(),
            'subject' => fake()->word(),
            'teacher_id' => rand(2,4),
            'student_id' => rand(5,15),
        ];
    }
}
