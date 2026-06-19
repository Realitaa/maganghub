<?php

namespace Database\Factories;

use App\Models\GroupMembership;
use App\Models\InternshipGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupMembership>
 */
class GroupMembershipFactory extends Factory
{
    protected $model = GroupMembership::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => InternshipGroup::factory(),
            'user_id' => User::factory(),
        ];
    }
}
