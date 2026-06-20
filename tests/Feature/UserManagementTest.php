<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Maatwebsite\Excel\Facades\Excel;

describe('administrator', function () {

    // --- Authentication & Authorization Edge Cases ---

    it('can view user management page if administrator', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this
            ->actingAs($admin)
            ->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
            );
    });

    it('can prevent student from viewing user management page', function () {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get(route('users.index'));

        $response->assertForbidden();
    });

    it('can prevent guest from viewing user management page', function () {
        $response = $this
            ->get(route('users.index'));

        $response->assertRedirect(route('login'));
    });

    // --- Listing, Filtering & Search ---

    it('can search users by name, email, or nim', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $user1 = User::factory()->create([
            'name' => 'SearchNameTarget',
            'email' => 'unique1@example.com',
            'nim' => '99990001',
            'role' => 'student',
        ]);
        $user2 = User::factory()->create([
            'name' => 'Another User',
            'email' => 'target_email@example.com',
            'nim' => '99990002',
            'role' => 'student',
        ]);

        // Search by Name
        $this->actingAs($admin)
            ->get(route('users.index', ['search' => 'SearchNameTarget']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.name', 'SearchNameTarget')
            );

        // Search by Email
        $this->actingAs($admin)
            ->get(route('users.index', ['search' => 'target_email@example.com']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.email', 'target_email@example.com')
            );

        // Search by NIM
        $this->actingAs($admin)
            ->get(route('users.index', ['search' => '99990001']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.nim', '99990001')
            );
    });

    it('can return all users when search query is empty', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        User::factory()->count(3)->create(['role' => 'student']);

        $this->actingAs($admin)
            ->get(route('users.index', ['search' => '']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 4)
            );
    });

    it('can filter users by role', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $operator = User::factory()->create(['role' => 'operator']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->get(route('users.index', ['role' => 'operator']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.role', 'operator')
            );
    });

    // Major filter and sorting tests removed because major has been removed from users schema.

    it('can paginate users list', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        User::factory()->count(15)->create(['role' => 'student']); // Total 16 users

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->where('users.per_page', 10)
                ->where('users.total', 16)
                ->has('users.data', 10)
            );
    });

    // --- Create & Edit Actions ---

    it('can create a student with a custom password', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Bob Student',
                'email' => 'bob@student.example.com',
                'role' => 'student',
                'nim' => '10121010',
                'gender' => 'L',
                'password' => 'custom-secret-password',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil membuat pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        $user = User::where('email', 'bob@student.example.com')->first();
        expect($user)->not->toBeNull();
        expect(Hash::check('custom-secret-password', $user->password))->toBeTrue();
    });

    it('can create a student without a password defaulting to nim', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Alice Student',
                'email' => 'alice@student.example.com',
                'role' => 'student',
                'nim' => '10121011',
                'gender' => 'P',
                'password' => '',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil membuat pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        $user = User::where('email', 'alice@student.example.com')->first();
        expect($user)->not->toBeNull();
        expect(Hash::check('10121011', $user->password))->toBeTrue();
    });

    it('can fail to create a student without a nim', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Invalid Student',
                'email' => 'invalid@student.example.com',
                'role' => 'student',
                'nim' => '',
                'password' => '',
            ]);

        $response->assertSessionHasErrors('nim');
    });

    it('can create an administrator with a required password', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New Admin',
                'email' => 'newadmin@example.com',
                'role' => 'administrator',
                'password' => 'adminpass123',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil membuat pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        $user = User::where('email', 'newadmin@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->role)->toBe('administrator');
        expect(Hash::check('adminpass123', $user->password))->toBeTrue();
    });

    it('can create an operator with a required password', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New Operator',
                'email' => 'newoperator@example.com',
                'role' => 'operator',
                'password' => 'operatorpass123',
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil membuat pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        $user = User::where('email', 'newoperator@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->role)->toBe('operator');
        expect(Hash::check('operatorpass123', $user->password))->toBeTrue();
    });

    it('can edit user details', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Old Name',
            'email' => 'old@student.example.com',
            'nim' => '10121012',
        ]);

        $oldPassword = $student->password;

        $this->actingAs($admin)
            ->patch(route('users.update', $student->id), [
                'name' => 'Updated Name',
                'email' => 'updated@student.example.com',
                'role' => 'student',
                'nim' => '10121012',
                'password' => '', // Empty means unchanged
            ])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil memperbarui pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        $student->refresh();
        expect($student->name)->toBe('Updated Name');
        expect($student->email)->toBe('updated@student.example.com');
        expect($student->password)->toBe($oldPassword);
    });

    it('can prevent editing email to an existing email', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $user1 = User::factory()->create(['email' => 'exist@example.com']);
        $user2 = User::factory()->create(['email' => 'other@example.com']);

        $response = $this
            ->actingAs($admin)
            ->patch(route('users.update', $user2->id), [
                'name' => $user2->name,
                'email' => 'exist@example.com',
                'role' => $user2->role,
            ]);

        $response->assertSessionHasErrors('email');
    });

    // --- Delete & Deactivate Actions ---

    it('can delete a user', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $student->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil menghapus pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        expect(User::find($student->id))->toBeNull();
    });

    it('can prevent deleting their own administrator account', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this
            ->actingAs($admin)
            ->delete(route('users.destroy', $admin->id));

        $response->assertSessionHasErrors('error');
        expect(User::find($admin->id))->not->toBeNull();
    });

    it('can deactivate an active user', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('users.toggle-active', $student->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil memperbarui status pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        expect($student->refresh()->is_active)->toBeFalse();
    });

    it('can activate a deactivated user', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $student = User::factory()->create(['role' => 'student', 'is_active' => false]);

        $this->actingAs($admin)
            ->patch(route('users.toggle-active', $student->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Berhasil memperbarui status pengguna.')
            ->assertInertiaFlash('toast.type', 'success');

        expect($student->refresh()->is_active)->toBeTrue();
    });

    // --- Import Actions ---

    it('can bulk import students from a CSV file using useHttp', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $csvContent = "name,nim,email\nCharlie Student,10121020,charlie@student.maganghub.id\nDiana Student,10121021,\n";
        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('users.import'), [
                'file' => $file,
            ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'Berhasil mengimpor 2 mahasiswa.',
        ]);

        $charlie = User::where('nim', '10121020')->first();
        expect($charlie)->not->toBeNull();
        expect($charlie->name)->toBe('Charlie Student');
        expect($charlie->email)->toBe('charlie@student.maganghub.id');
        expect(Hash::check('10121020', $charlie->password))->toBeTrue();

        $diana = User::where('nim', '10121021')->first();
        expect($diana)->not->toBeNull();
        expect($diana->name)->toBe('Diana Student');
        expect($diana->email)->toBeNull();
        expect(Hash::check('10121021', $diana->password))->toBeTrue();
    });

    it('can bulk import students from an XLSX file using useHttp', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        // Mock the Excel library response
        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([
                [
                    ['name', 'nim', 'email'],
                    ['Emily Student', '10121030', 'emily@student.maganghub.id'],
                ],
            ]);

        $file = UploadedFile::fake()->create('students.xlsx', 100); // 100 KB mock file

        $response = $this
            ->actingAs($admin)
            ->postJson(route('users.import'), [
                'file' => $file,
            ]);

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'Berhasil mengimpor 1 mahasiswa.',
        ]);

        $emily = User::where('nim', '10121030')->first();
        expect($emily)->not->toBeNull();
        expect($emily->name)->toBe('Emily Student');
        expect($emily->email)->toBe('emily@student.maganghub.id');
        expect(Hash::check('10121030', $emily->password))->toBeTrue();
    });

    it('can fail to import if required headers name or nim are missing', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        // Invalid headers: missing 'nim'
        $csvContent = "name,email\nJohn Doe,john@example.com\n";
        $file = UploadedFile::fake()->createWithContent('invalid.csv', $csvContent);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('users.import'), [
                'file' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    });

});

describe('operator', function () {

    it('can view user management page if operator', function () {
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this
            ->actingAs($operator)
            ->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
            );
    });

    it('cannot delete other user or themselves', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $student = User::factory()->create(['role' => 'student']);

        // Try to delete another user
        $response = $this
            ->actingAs($operator)
            ->delete(route('users.destroy', $student->id));
        $response->assertForbidden();

        // Try to delete themselves
        $response = $this
            ->actingAs($operator)
            ->delete(route('users.destroy', $operator->id));
        $response->assertForbidden();
    });

    it('cannot update other administrator', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.update', $admin->id), [
                'name' => 'Updated Name',
                'email' => 'updated@admin.example.com',
                'role' => 'administrator',
            ]);
        $response->assertForbidden();
    });

    it('cannot update other operator', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $otherOperator = User::factory()->create(['role' => 'operator']);

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.update', $otherOperator->id), [
                'name' => 'Updated Name',
                'email' => 'updated@operator.example.com',
                'role' => 'operator',
            ]);
        $response->assertForbidden();
    });

    it('can update student users', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Old Name',
            'email' => 'old@student.example.com',
            'nim' => '10121012',
        ]);

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.update', $student->id), [
                'name' => 'Updated Name',
                'email' => 'updated@student.example.com',
                'role' => 'student',
                'nim' => '10121012',
            ]);
        $response->assertRedirect();

        $student->refresh();
        expect($student->name)->toBe('Updated Name');
    });

    it('can update themselves', function () {
        $operator = User::factory()->create([
            'role' => 'operator',
            'name' => 'Operator Name',
            'email' => 'op@example.com',
        ]);

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.update', $operator->id), [
                'name' => 'Updated Op Name',
                'email' => 'op@example.com',
                'role' => 'operator',
            ]);
        $response->assertRedirect();

        $operator->refresh();
        expect($operator->name)->toBe('Updated Op Name');
    });

    it('cannot toggle active status of other administrator or operator', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $otherOperator = User::factory()->create(['role' => 'operator', 'is_active' => true]);

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.toggle-active', $admin->id));
        $response->assertForbidden();

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.toggle-active', $otherOperator->id));
        $response->assertForbidden();
    });

    it('can toggle active status of student users', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $response = $this
            ->actingAs($operator)
            ->patch(route('users.toggle-active', $student->id));
        $response->assertRedirect();

        expect($student->refresh()->is_active)->toBeFalse();
    });

});
