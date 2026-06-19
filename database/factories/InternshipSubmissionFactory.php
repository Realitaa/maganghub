<?php

namespace Database\Factories;

use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternshipSubmission>
 */
class InternshipSubmissionFactory extends Factory
{
    protected $model = InternshipSubmission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => InternshipGroup::factory(),
            'company_name' => $this->faker->company(),
            'company_address' => $this->faker->address(),
            'company_contact' => $this->faker->phoneNumber(),
            'division' => $this->faker->jobTitle(),
            'start_date' => now()->addMonth()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'supporting_document' => null,
            'status' => 'draft',
        ];
    }

    /**
     * Mark the submission as submitted.
     */
    public function submitted(): static
    {
        return $this->state(['status' => 'submitted']);
    }
}
