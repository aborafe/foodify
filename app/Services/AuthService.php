<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\OtpServiceInterface;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(private readonly OtpServiceInterface $otpService) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create($data);
            $otp = $this->otpService->generate($user->phone, OtpServiceInterface::REGISTER);

            return [$user, $otp];
        });
    }

    public function verifyRegistrationOtp(string $phone, string $code): User
    {
        $user = User::query()->where('phone', $phone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['No user exists for this phone number.'],
            ]);
        }

        DB::transaction(function () use ($phone, $code, $user): void {
            $this->otpService->verify($phone, $code, OtpServiceInterface::REGISTER);

            $user->forceFill([
                'phone_verified_at' => now(),
            ])->save();
        });

        return $user->fresh();
    }

    public function login(string $phone, string $password): array
    {
        $user = User::query()->where('phone', $phone)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone' => ['This account is inactive.'],
            ]);
        }

        if (! $user->phone_verified_at) {
            throw ValidationException::withMessages([
                'phone' => ['Phone number is not verified.'],
            ]);
        }

        return [
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
        ];
    }

    public function createPasswordResetOtp(string $phone): Otp
    {
        return $this->otpService->generate($phone, OtpServiceInterface::FORGOT_PASSWORD);
    }

    public function verifyPasswordResetOtp(string $phone, string $code): void
    {
        $this->otpService->verify($phone, $code, OtpServiceInterface::FORGOT_PASSWORD, false);
    }

    public function resetPassword(string $phone, string $code, string $password): void
    {
        $otp = $this->otpService->getVerifiedPasswordResetOtp($phone, $code);

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => ['Password reset OTP is not verified or has expired.'],
            ]);
        }

        DB::transaction(function () use ($phone, $password, $otp): void {
            User::query()
                ->where('phone', $phone)
                ->firstOrFail()
                ->forceFill(['password' => $password])
                ->save();

            $otp->forceFill(['is_used' => true])->save();
        });
    }
}
