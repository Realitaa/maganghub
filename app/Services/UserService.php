<?php

namespace App\Services;

use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class UserService
{
    /**
     * Resolve or create student class by ID or Name.
     */
    protected function resolveStudentClassId(mixed $classInput): ?int
    {
        if (empty($classInput) || $classInput === 'none') {
            return null;
        }

        if (is_numeric($classInput)) {
            $exists = StudentClass::where('id', $classInput)->exists();
            if ($exists) {
                return (int) $classInput;
            }
        }

        $studentClass = StudentClass::firstOrCreate(['name' => $classInput]);

        return $studentClass->id;
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        $role = $data['role'];

        // Handle password defaulting for student role if empty
        if ($role === 'student' && empty($data['password'])) {
            $data['password'] = Hash::make($data['nim']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($role === 'student' && isset($data['student_class_id'])) {
            $data['student_class_id'] = $this->resolveStudentClassId($data['student_class_id']);
        }

        return User::create($data);
    }

    /**
     * Update an existing user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data): User
    {
        $role = $data['role'] ?? $user->role;

        if ($role !== 'student') {
            $data['nim'] = null;
            $data['student_class_id'] = null;
        } elseif (isset($data['student_class_id'])) {
            $data['student_class_id'] = $this->resolveStudentClassId($data['student_class_id']);
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    /**
     * Delete a user.
     *
     * @throws ValidationException
     */
    public function deleteUser(User $user, User $currentUser): bool
    {
        if ($currentUser->id === $user->id) {
            throw ValidationException::withMessages([
                'error' => 'Anda tidak dapat menghapus akun administrator Anda sendiri.',
            ]);
        }

        return $user->delete();
    }

    /**
     * Toggle the active status of a user.
     *
     * @throws ValidationException
     */
    public function toggleActiveStatus(User $user, User $currentUser): User
    {
        if ($currentUser->id === $user->id) {
            throw ValidationException::withMessages([
                'error' => 'Anda tidak dapat menonaktifkan akun administrator Anda sendiri.',
            ]);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return $user;
    }

    /**
     * Import users from a CSV/XLSX file.
     *
     * @return int The number of users imported.
     *
     * @throws ValidationException
     */
    public function importUsers(UploadedFile $file): int
    {
        try {
            $data = Excel::toArray(new class implements ToArray
            {
                public function array(array $array): void {}
            }, $file);

            $sheet = $data[0] ?? [];
            if (empty($sheet)) {
                throw ValidationException::withMessages([
                    'file' => ['File sheet kosong.'],
                ]);
            }

            $headers = array_map(fn ($val) => strtolower(trim((string) $val)), array_shift($sheet));

            $nameIdx = array_search('name', $headers);
            $nimIdx = array_search('nim', $headers);
            $emailIdx = array_search('email', $headers);
            $kelasIdx = array_search('kelas', $headers);

            if ($nameIdx === false || $nimIdx === false || $kelasIdx === false) {
                throw ValidationException::withMessages([
                    'file' => ['File harus mengandung kolom "name", "nim", dan "kelas".'],
                ]);
            }

            $importedCount = 0;
            foreach ($sheet as $row) {
                $name = trim((string) ($row[$nameIdx] ?? ''));
                $nim = trim((string) ($row[$nimIdx] ?? ''));
                $email = $emailIdx !== false && ! empty($row[$emailIdx])
                    ? trim((string) $row[$emailIdx])
                    : null;
                $kelasVal = trim((string) ($row[$kelasIdx] ?? ''));

                if (empty($name) || empty($nim) || empty($kelasVal)) {
                    continue;
                }

                $studentClass = StudentClass::firstOrCreate(['name' => $kelasVal]);

                User::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'name' => $name,
                        'email' => $email,
                        'role' => 'student',
                        'password' => Hash::make($nim),
                        'is_active' => true,
                        'student_class_id' => $studentClass->id,
                    ]
                );
                $importedCount++;
            }

            return $importedCount;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file' => ['Gagal mengimpor file: '.$e->getMessage()],
            ]);
        }
    }
}
