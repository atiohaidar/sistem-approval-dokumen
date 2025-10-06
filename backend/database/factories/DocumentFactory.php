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
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'file_path' => 'documents/' . $this->faker->uuid() . '.pdf',
            'file_name' => $this->faker->word() . '.pdf',
            'file_size' => $this->faker->numberBetween(1000, 10000000), // 1KB to 10MB
            'mime_type' => 'application/pdf',
            'template_id' => null, // Default to null, can be overridden
            'status' => $this->faker->randomElement(['draft', 'pending', 'in_progress', 'completed', 'rejected', 'cancelled']),
            'created_by' => \App\Models\User::factory(),
            'submitted_at' => $this->faker->optional(0.3)->dateTime(), // 30% chance of being submitted
            'completed_at' => $this->faker->optional(0.2)->dateTime(), // 20% chance of being completed
            'current_step' => $this->faker->numberBetween(0, 5),
            'total_steps' => $this->faker->numberBetween(0, 5),
        ];
    }

    /**
     * Indicate that the document is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the document is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'submitted_at' => now(),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the document is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
