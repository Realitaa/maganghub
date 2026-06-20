<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 1 Administrator
        User::factory()->create([
            'name' => 'Administrator MagangHub',
            'email' => 'admin@maganghub.id',
            'role' => 'administrator',
            'password' => Hash::make('password'),
        ]);

        // 2. Create 1 Operator
        User::factory()->create([
            'name' => 'Operator MagangHub',
            'email' => 'operator@maganghub.id',
            'role' => 'operator',
            'password' => Hash::make('password'),
        ]);

        // 3. Create some students
        $studentNim = 10121001;

        $students = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@student.maganghub.id',
                'role' => 'student',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@student.maganghub.id',
                'role' => 'student',
                'is_active' => true,
            ],
            [
                'name' => 'Rian Hidayat',
                'email' => 'rian@student.maganghub.id',
                'role' => 'student',
                'is_active' => true,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@student.maganghub.id',
                'role' => 'student',
                'is_active' => true,
            ],
            [
                'name' => 'Adi Wijaya',
                'email' => 'adi@student.maganghub.id',
                'role' => 'student',
                'is_active' => true,
            ],
        ];

        foreach ($students as $student) {
            User::factory()->create(array_merge($student, [
                'nim' => $studentNim,
                'password' => Hash::make($studentNim),
            ]));

            $studentNim++;
        }
    }
}
