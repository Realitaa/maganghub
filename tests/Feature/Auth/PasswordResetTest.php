<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset password link can be requested by student using NIM if email exists', function () {
    Notification::fake();

    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121999',
        'email' => 'student@example.com',
    ]);

    $response = $this->post(route('password.email'), ['email' => '10121999']);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('status', __('passwords.masked_sent', ['email' => 's*****t@example.com']));
    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password link cannot be requested by student using NIM if email is empty', function () {
    Notification::fake();

    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121999',
        'email' => null,
    ]);

    $response = $this->post(route('password.email'), ['email' => '10121999']);

    $response->assertSessionHasErrors('email');
    $response->assertSessionHasErrors(['email' => __('passwords.email_empty')]);
    Notification::assertNotSentTo($user, ResetPassword::class);
});

test('cannot update password via settings page if email is empty', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'nim' => '10121999',
        'email' => null,
    ]);

    $response = $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionHasErrors(['email' => __('passwords.email_empty_for_password_change')]);
});
