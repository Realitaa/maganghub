<?php

use App\Models\InternshipGroup;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('model helpers compute completeness and password changes correctly', function () {
    // 1. Valid complete student user from factory
    $student = User::factory()->create(['role' => 'student']);
    expect($student->hasChangedPassword())->toBeTrue();
    expect($student->isProfileComplete())->toBeTrue();

    // 2. Student with default/null password changed at
    $student2 = User::factory()->create([
        'role' => 'student',
        'password_changed_at' => null,
    ]);
    expect($student2->hasChangedPassword())->toBeFalse();
    expect($student2->isProfileComplete())->toBeTrue();

    // 3. Student with incomplete profile data (missing semester)
    $student3 = User::factory()->create([
        'role' => 'student',
        'semester' => null,
    ]);
    expect($student3->hasChangedPassword())->toBeTrue();
    expect($student3->isProfileComplete())->toBeFalse();

    // 4. Non-student user is always complete
    $admin = User::factory()->create(['role' => 'administrator']);
    expect($admin->isProfileComplete())->toBeTrue();
});

test('inertia middleware shares requirements data correctly', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'password_changed_at' => null,
        'semester' => null,
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.requirements.password_changed', false)
            ->where('auth.requirements.profile_completed', false)
        );
});

test('student is blocked from creating a group if requirements are not met', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'password_changed_at' => null,
    ]);

    $response = $this
        ->actingAs($student)
        ->post(route('groups.store'));

    $response
        ->assertRedirect()
        ->assertInertiaFlash('toast.type', 'error')
        ->assertInertiaFlash('toast.message', 'Anda harus mengubah password default dan melengkapi biodata terlebih dahulu.');

    expect(InternshipGroup::count())->toBe(0);
});

test('student is blocked from joining a group if requirements are not met', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $group = InternshipGroup::create([
        'leader_id' => $leader->id,
        'code' => 'ABCDE12345',
        'status' => 'forming',
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'semester' => null, // incomplete profile
    ]);

    $response = $this
        ->actingAs($student)
        ->post(route('groups.join'), ['code' => 'ABCDE12345']);

    $response
        ->assertRedirect()
        ->assertInertiaFlash('toast.type', 'error')
        ->assertInertiaFlash('toast.message', 'Anda harus mengubah password default dan melengkapi biodata terlebih dahulu.');
});

test('updating password updates password_changed_at timestamp', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'password_changed_at' => null,
    ]);

    $response = $this
        ->actingAs($student)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

    $response->assertSessionHasNoErrors();

    $student->refresh();
    expect($student->hasChangedPassword())->toBeTrue();
    expect($student->password_changed_at)->not->toBeNull();
});

test('updating password to default sets password_changed_at to null', function () {
    $defaultNim = '123456789';
    $student = User::factory()->create([
        'nim' => $defaultNim,
        'password' => Hash::make($defaultNim),
        'role' => 'student',
        'password_changed_at' => now(),
    ]);

    $response = $this
        ->actingAs($student)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => $defaultNim,
            'password' => $defaultNim,
            'password_confirmation' => $defaultNim,
        ]);

    $response->assertSessionHasNoErrors();

    $student->refresh();
    expect($student->hasChangedPassword())->toBeFalse();
    expect($student->password_changed_at)->toBeNull();

    // change to other password but change back to nim resets the password_changed_at to null
    $response2 = $this
        ->actingAs($student)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => $defaultNim,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

    $response2->assertSessionHasNoErrors();

    $student->refresh();
    expect($student->hasChangedPassword())->toBeTrue();
    expect($student->password_changed_at)->not->toBeNull();

    $response3 = $this
        ->actingAs($student)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'new-secure-password',
            'password' => $defaultNim,
            'password_confirmation' => $defaultNim,
        ]);

    $response3->assertSessionHasNoErrors();

    $student->refresh();
    expect($student->hasChangedPassword())->toBeFalse();
    expect($student->password_changed_at)->toBeNull();
});

test('student profile validation enforces all student biodata fields', function () {
    $student = User::factory()->create(['role' => 'student']);

    // 1. Fail validation with missing required student fields
    $response = $this
        ->actingAs($student)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'nim' => '', // missing
            'gender' => 'X', // invalid option
            'semester' => 15, // too high
        ]);

    $response->assertSessionHasErrors(['nim', 'gender', 'semester']);

    // 2. Succeed validation with correct student details
    $response2 = $this
        ->actingAs($student)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'nim' => '10121999',
            'gender' => 'P',
            'phone' => '081234567890',
            'address' => 'Bandung, Indonesia',
            'semester' => 5,
        ]);

    $response2->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

    $student->refresh();
    expect($student->name)->toBe('New Name');
    expect($student->nim)->toBe('10121999');
    expect($student->gender)->toBe('P');
    expect($student->semester)->toBe(5);
});
