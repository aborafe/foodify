<?php

use App\Contracts\SmsServiceInterface;
use App\Models\Otp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers an unverified user and requests a register otp', function (): void {
    $this->mock(SmsServiceInterface::class)
        ->shouldReceive('send')
        ->once()
        ->withArgs(fn (string $phone, string $message): bool => $phone === '+201001234567'
            && str_contains($message, 'Your Foodify OTP code is: '));

    $response = $this->postJson('/api/auth/register', [
        'full_name' => 'Foodify User',
        'phone' => '+201001234567',
        'email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.phone', '+201001234567')
        ->assertJsonStructure(['otp' => ['type', 'expires_at']])
        ->assertJsonMissingPath('otp.code');

    $this->assertDatabaseHas('users', [
        'phone' => '+201001234567',
        'phone_verified_at' => null,
    ]);

    $this->assertDatabaseHas('otps', [
        'phone' => '+201001234567',
        'type' => OtpService::REGISTER,
        'is_used' => false,
    ]);
});

it('blocks login until phone is verified', function (): void {
    User::factory()->unverified()->create([
        'phone' => '+201001234568',
        'password' => 'password123',
    ]);

    $this->postJson('/api/auth/login', [
        'phone' => '+201001234568',
        'password' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('phone');
});

it('logs in a verified active user and returns a sanctum token', function (): void {
    User::factory()->create([
        'phone' => '+201001234569',
        'password' => 'password123',
        'phone_verified_at' => now(),
        'is_active' => true,
    ]);

    $this->postJson('/api/auth/login', [
        'phone' => '+201001234569',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonStructure(['token', 'user']);

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('verifies register otp and marks phone as verified', function (): void {
    User::factory()->unverified()->create([
        'phone' => '+201001234570',
    ]);

    Otp::query()->create([
        'phone' => '+201001234570',
        'code' => '123456',
        'type' => OtpService::REGISTER,
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->postJson('/api/auth/verify-register-otp', [
        'phone' => '+201001234570',
        'code' => '123456',
    ])->assertOk();

    expect(User::query()->where('phone', '+201001234570')->value('phone_verified_at'))->not->toBeNull();

    $this->assertDatabaseHas('otps', [
        'phone' => '+201001234570',
        'code' => '123456',
        'is_used' => true,
    ]);
});

it('resets password only after forgot password otp is verified', function (): void {
    User::factory()->create([
        'phone' => '+201001234571',
        'password' => 'old-password',
    ]);

    Otp::query()->create([
        'phone' => '+201001234571',
        'code' => '654321',
        'type' => OtpService::FORGOT_PASSWORD,
        'expires_at' => now()->addMinutes(5),
        'verified_at' => now(),
        'is_used' => false,
    ]);

    $this->postJson('/api/auth/reset-password', [
        'phone' => '+201001234571',
        'code' => '654321',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    $this->postJson('/api/auth/login', [
        'phone' => '+201001234571',
        'password' => 'new-password',
    ])->assertOk();

    $this->assertDatabaseHas('otps', [
        'phone' => '+201001234571',
        'code' => '654321',
        'is_used' => true,
    ]);
});
