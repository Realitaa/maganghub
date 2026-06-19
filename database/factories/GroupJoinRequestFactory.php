<?php

namespace Database\Factories;

use App\Models\GroupJoinRequest;
use App\Models\InternshipGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupJoinRequest>
 */
class GroupJoinRequestFactory extends Factory
{
    protected $model = GroupJoinRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => InternshipGroup::factory(),
            'user_id' => User::factory(),
            'status' => 'pending',
        ];
    }

    /**
     * Mark the request as approved.
     */
    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    /**
     * Mark the request as rejected.
     */
    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }

    /**
     * Mark the request as cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
