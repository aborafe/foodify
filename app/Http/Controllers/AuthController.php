<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceInterface $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            [$user, $otp] = $this->authService->register($request->validated());
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => 'Unable to send OTP SMS.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'message' => 'Registered successfully. Please verify your phone number with the OTP sent by SMS.',
            'user' => $user,
            'otp' => $this->otpResponse($otp),
        ], 201);
    }

    public function verifyRegisterOtp(OtpVerificationRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $user = $this->authService->verifyRegistrationOtp($data['phone'], $data['code']);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'code' => [$exception->getMessage()],
            ]);
        }

        return response()->json([
            'message' => 'Phone number verified successfully.',
            'user' => $user,
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $login = $this->authService->login($data['phone'], $data['password']);

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $login['token'],
            'token_type' => 'Bearer',
            'user' => $login['user'],
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $otp = $this->authService->createPasswordResetOtp($data['phone']);

            return response()->json([
                'message' => 'Password reset OTP sent by SMS successfully.',
                'otp' => $this->otpResponse($otp),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => 'Unable to send OTP SMS.',
                'error' => $exception->getMessage(),
            ], 502);
        }
    }

    public function verifyForgotPasswordOtp(OtpVerificationRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $this->authService->verifyPasswordResetOtp($data['phone'], $data['code']);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'code' => [$exception->getMessage()],
            ]);
        }

        return response()->json([
            'message' => 'Password reset OTP verified successfully.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->authService->resetPassword($data['phone'], $data['code'], $data['password']);

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
            'type' => $otp->type,
            'expires_at' => $otp->expires_at,
        ];
    }
}
