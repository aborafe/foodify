<?php

namespace App\Contracts;

use App\Models\Otp;

interface OtpServiceInterface
{
    public const REGISTER = 'register';

    public const FORGOT_PASSWORD = 'forgot_password';

    public function generate(string $phone, string $type): Otp;

    public function verify(string $phone, string $code, string $type, bool $markAsUsed = true): Otp;

    public function getVerifiedPasswordResetOtp(string $phone, string $code): ?Otp;
}
