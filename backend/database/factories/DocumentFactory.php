<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate random nested approvers (1-3 levels, each level 1-3 users)
        $levels = $this->faker->numberBetween(1, 3);
        $approvers = [];
        for ($i = 0; $i < $levels; $i++) {
            $usersInLevel = $this->faker->numberBetween(1, 3);
            $levelApprovers = [];
            for ($j = 0; $j < $usersInLevel; $j++) {
                $levelApprovers[] = \App\Models\User::factory()->create()->id;
            }
            $approvers[] = $levelApprovers;
        }

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'file_path' => 'documents/' . $this->faker->uuid() . '.pdf',
            'file_name' => $this->faker->word() . '.pdf',
            'file_size' => $this->faker->numberBetween(1000, 10000000), // 1KB to 10MB
            'mime_type' => 'application/pdf',
            'template_id' => null, // Default to null, can be overridden
            'status' => $this->faker->randomElement(['draft', 'pending_approval', 'in_progress', 'completed', 'rejected', 'cancelled']),
            'created_by' => \App\Models\User::factory(),
            'submitted_at' => $this->faker->optional(0.3)->dateTime(), // 30% chance of being submitted
            'completed_at' => $this->faker->optional(0.2)->dateTime(), // 20% chance of being completed
            'approvers' => $approvers,
            'current_level' => 1,
            'level_progress' => null, // Will be initialized when needed
            'qr_x' => $this->faker->randomFloat(2, 0.1, 0.9), // Random coordinate between 0.1 and 0.9
            'qr_y' => $this->faker->randomFloat(2, 0.1, 0.9), // Random coordinate between 0.1 and 0.9
            'qr_page' => $this->faker->numberBetween(1, 5), // Random page between 1 and 5
            'qr_size' => $this->faker->randomFloat(2, 0.1, 0.3), // Relative width between 10% and 30%
        ];
    }

    /**
     * Indicate that the document is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the document is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the document is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
