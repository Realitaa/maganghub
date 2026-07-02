<?php

namespace Tests\Feature;

use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads_and_passes_statistics()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('statistics.total_students')
            ->has('statistics.total_groups')
            ->has('statistics.total_companies')
            ->has('statistics.pie_chart')
        );
    }

    public function test_landing_statistics_are_cached()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('landing.statistics', \Mockery::any(), \Mockery::type('Closure'))
            ->andReturn([
                'total_students' => 10,
                'total_groups' => 2,
                'total_companies' => 2,
                'pie_chart' => [
                    'multinational' => 2,
                    'national' => 1,
                    'startup' => 0,
                    'havenot' => 7,
                ],
            ]);

        $this->get('/');
    }

    public function test_landing_statistics_calculate_correctly()
    {
        Cache::flush();
        
        // Students
        $students = User::factory()->count(10)->create(['role' => 'student']);
        
        // Non-student
        User::factory()->create(['role' => 'administrator']);

        // Group 1: accepted at Multinational
        $group1 = InternshipGroup::factory()->create([
            'status' => 'accepted',
            'leader_id' => $students[0]->id,
        ]);
        InternshipSubmission::factory()->create([
            'group_id' => $group1->id,
            'status' => 'accepted',
            'company_name' => 'Google',
            'company_type' => 'Perusahaan Multinasional',
        ]);
        
        // 3 active members in group 1 (including leader)
        foreach ($students->take(3) as $student) {
            $group1->memberships()->create([
                'user_id' => $student->id,
                'status' => 'active',
                'role' => $student->id === $students[0]->id ? 'leader' : 'member',
            ]);
        }

        // Group 2: accepted at National
        $group2 = InternshipGroup::factory()->create([
            'status' => 'accepted',
            'leader_id' => $students[3]->id,
        ]);
        InternshipSubmission::factory()->create([
            'group_id' => $group2->id,
            'status' => 'accepted',
            'company_name' => 'Telkom',
            'company_type' => 'Perusahaan Nasional',
        ]);
        
        // 2 active members in group 2 (including leader)
        foreach ($students->skip(3)->take(2) as $student) {
            $group2->memberships()->create([
                'user_id' => $student->id,
                'status' => 'active',
                'role' => $student->id === $students[3]->id ? 'leader' : 'member',
            ]);
        }
        
        // Group 3: forming
        InternshipGroup::factory()->create([
            'status' => 'forming',
            'leader_id' => $students[5]->id,
        ]);

        $response = $this->get('/');
        
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('statistics.total_students', 10)
            ->where('statistics.total_groups', 3)
            ->where('statistics.total_companies', 2)
            ->where('statistics.pie_chart.multinational', 3)
            ->where('statistics.pie_chart.national', 2)
            ->where('statistics.pie_chart.startup', 0)
            ->where('statistics.pie_chart.havenot', 5)
        );
    }
}
