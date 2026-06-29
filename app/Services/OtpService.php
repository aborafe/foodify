<?php

namespace App\Services;

use App\Contracts\OtpServiceInterface;
use App\Contracts\SmsServiceInterface;
use App\Models\Otp;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OtpService implements OtpServiceInterface
{
    public const REGISTER = 'register';

    public const FORGOT_PASSWORD = 'forgot_password';

    public function __construct(private readonly SmsServiceInterface $smsService) {}

    public function generate(string $phone, string $type): Otp
    {
        $this->ensureValidType($type);

        $otp = DB::transaction(function () use ($phone, $type): Otp {
            Otp::query()
                ->where('phone', $phone)
                ->where('type', $type)
                ->where('is_used', false)
                ->update(['is_used' => true]);

            return Otp::query()->create([
                'phone' => $phone,
                'code' => (string) random_int(100000, 999999),
                'type' => $type,
                'expires_at' => now()->addMinutes(5),
                'is_used' => false,
            ]);
        });

        try {
            $this->smsService->send($phone, "Your Foodify OTP code is: {$otp->code}");
        } catch (RuntimeException $exception) {
            $otp->forceFill(['is_used' => true])->save();

            throw $exception;
        }

        return $otp;
    }

    public function verify(string $phone, string $code, string $type, bool $markAsUsed = true): Otp
    {
        $this->ensureValidType($type);

        $otp = Otp::query()
            ->where('phone', $phone)
            ->where('code', $code)
            ->where('type', $type)
            ->where('is_used', false)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            throw new RuntimeException('Invalid or expired OTP code.');
        }

        $otp->forceFill([
            'verified_at' => now(),
            'is_used' => $markAsUsed,
        ])->save();

        return $otp;
    }

    public function getVerifiedPasswordResetOtp(string $phone, string $code): ?Otp
    {
        return Otp::query()
            ->where('phone', $phone)
            ->where('code', $code)
            ->where('type', self::FORGOT_PASSWORD)
            ->where('is_used', false)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    private function ensureValidType(string $type): void
    {
        if (! in_array($type, [self::REGISTER, self::FORGOT_PASSWORD], true)) {
            throw new RuntimeException('Invalid OTP type.');
        }
    }
}
