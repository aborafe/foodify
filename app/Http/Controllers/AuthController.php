<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{7,14}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        [$user, $otp] = DB::transaction(function () use ($data): array {
                $user = User::query()->create($data);
                $otp = $this->otpService->generate($user->phone, OtpService::REGISTER);

                return [$user, $otp];
            });

        return response()->json([
            'message' => 'Registered successfully. Please verify your phone number with the generated OTP.',
            'user' => $user,
            'otp' => $this->otpResponse($otp),
        ], 201);
    }

    public function verifyRegisterOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{7,14}$/'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::query()->where('phone', $data['phone'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['No user exists for this phone number.'],
            ]);
        }

        try {
            DB::transaction(function () use ($data, $user): void {
                $this->otpService->verify($data['phone'], $data['code'], OtpService::REGISTER);

                $user->forceFill([
                    'phone_verified_at' => now(),
                ])->save();
            });
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'code' => [$exception->getMessage()],
            ]);
        }

        return response()->json([
            'message' => 'Phone number verified successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{7,14}$/'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
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

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $user->createToken('mobile')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{7,14}$/', 'exists:users,phone'],
        ]);

        try {
            $otp = $this->otpService->generate($data['phone'], OtpService::FORGOT_PASSWORD);

            return response()->json([
                'message' => 'Password reset OTP generated successfully.',
                'otp' => $this->otpResponse($otp),
            ]);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'phone' => [$exception->getMessage()],
            ]);
        }
    }

    public function verifyForgotPasswordOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{7,14}$/'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $this->otpService->verify($data['phone'], $data['code'], OtpService::FORGOT_PASSWORD, false);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'code' => [$exception->getMessage()],
            ]);
        }

        return response()->json([
            'message' => 'Password reset OTP verified successfully.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+[1-9]\d{7,14}$/', 'exists:users,phone'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otp = $this->otpService->getVerifiedPasswordResetOtp($data['phone'], $data['code']);

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => ['Password reset OTP is not verified or has expired.'],
            ]);
        }

        DB::transaction(function () use ($data, $otp): void {
            User::query()
                ->where('phone', $data['phone'])
                ->firstOrFail()
                ->forceFill(['password' => $data['password']])
                ->save();

            $otp->forceFill(['is_used' => true])->save();
        });

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function otpResponse(Otp $otp): array
    {
        return [
            'code' => $otp->code,
            'type' => $otp->type,
            'expires_at' => $otp->expires_at,
        ];
    }
}
