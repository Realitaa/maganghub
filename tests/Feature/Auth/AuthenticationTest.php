<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('students can authenticate using their NIM and are redirected to home', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121001',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => '10121001',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('students can authenticate using their email and are redirected to home', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'email' => 'student@example.com',
        'nim' => '10121001',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'student@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('administrators and operators can authenticate using their email and are redirected to dashboard', function () {
    // Test Administrator
    $admin = User::factory()->create([
        'role' => 'administrator',
        'email' => 'admin@example.com',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    // Logout
    $this->post(route('logout'));

    // Test Operator
    $operator = User::factory()->create([
        'role' => 'operator',
        'email' => 'operator@example.com',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'operator@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create([
        'role' => 'student',
        'nim' => '10121001',
    ]);

    $response = $this->post(route('login'), [
        'email' => '10121001',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121001',
    ]);

    $this->post(route('login.store'), [
        'email' => '10121001',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121001',
    ]);

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('welcome'));

    $this->assertGuest();
});

test('users are rate limited', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121001',
    ]);

    RateLimiter::increment(md5('login'.implode('|', ['10121001', '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'email' => '10121001',
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
