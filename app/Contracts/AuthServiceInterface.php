<?php

namespace App\Contracts;

use App\Models\Otp;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{0: User, 1: Otp}
     */
    public function register(array $data): array;

    public function verifyRegistrationOtp(string $phone, string $code): User;

    /**
     * @return array{token: string, user: User}
     */
    public function login(string $phone, string $password): array;

    public function createPasswordResetOtp(string $phone): Otp;

    public function verifyPasswordResetOtp(string $phone, string $code): void;

    public function resetPassword(string $phone, string $code, string $password): void;
}
