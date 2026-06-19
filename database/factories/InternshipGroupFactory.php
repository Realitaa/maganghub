<?php

namespace Database\Factories;

use App\Models\InternshipGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InternshipGroup>
 */
class InternshipGroupFactory extends Factory
{
    protected $model = InternshipGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'leader_id' => User::factory(),
            'code' => strtoupper(Str::random(10)),
            'status' => 'forming',
        ];
    }

    /**
     * Mark the group as submitted.
     */
    public function submitted(): static
    {
        return $this->state(['status' => 'submitted']);
    }

    /**
     * Mark the group as under review.
     */
    public function underReview(): static
    {
        return $this->state(['status' => 'under_review']);
    }

    /**
     * Mark the group as company-rejected.
     */
    public function companyRejected(): static
    {
        return $this->state(['status' => 'company_rejected']);
    }
}
