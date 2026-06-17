<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class UserService
{
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
            $data['major'] = null;
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
            $majorIdx = array_search('major', $headers);
            $emailIdx = array_search('email', $headers);

            if ($nameIdx === false || $nimIdx === false || $majorIdx === false) {
                throw ValidationException::withMessages([
                    'file' => ['File harus mengandung kolom "name", "nim", dan "major".'],
                ]);
            }

            $importedCount = 0;
            foreach ($sheet as $row) {
                $name = trim((string) ($row[$nameIdx] ?? ''));
                $nim = trim((string) ($row[$nimIdx] ?? ''));
                $major = trim((string) ($row[$majorIdx] ?? ''));
                $email = $emailIdx !== false && ! empty($row[$emailIdx])
                    ? trim((string) $row[$emailIdx])
                    : $nim.'@student.maganghub.id';

                if (empty($name) || empty($nim) || empty($major)) {
                    continue;
                }

                User::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'name' => $name,
                        'email' => $email,
                        'major' => $major,
                        'role' => 'student',
                        'password' => Hash::make($nim),
                        'is_active' => true,
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
