<?php

namespace Database\Factories;

use App\Models\InternshipSubmission;
use App\Models\SubmissionMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubmissionMembership>
 */
class SubmissionMembershipFactory extends Factory
{
    protected $model = SubmissionMembership::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => InternshipSubmission::factory(),
            'user_id' => User::factory(),
            'status' => 'pending',
        ];
    }
}
