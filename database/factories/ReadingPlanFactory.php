<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Book;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingPlan>
 */
class ReadingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => fake()->randomElement([
                'not_started',
                'reading',
                'completed',
                'overdue',
            ]),
            'target_date' => fake()->dateTimeBetween('today', '+30 days')->format('Y-m-d'),
        ];
    }
}
