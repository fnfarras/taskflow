<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'assignee_id' => null,
            'title'       => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status'      => fake()->randomElement(['todo', 'in_progress', 'done']),
            'priority'    => fake()->randomElement(['low', 'medium', 'high']),
            'due_date'    => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
        ];
    }
}
