<?php

namespace Database\Factories;

use App\Models\StudentClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentClass>
 */
class StudentClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<StudentClass>
     */
    protected $model = StudentClass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('PSIK##?'),
        ];
    }
}
