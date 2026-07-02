<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Otp;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthServiceInterface $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            [$user, $otp] = $this->authService->register($request->validated());
        } catch (RuntimeException $exception) {
            return $this->error('Unable to send OTP SMS.', 502, [
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->created([
            'message' => 'Registered successfully. Please verify your phone number with the OTP sent by SMS.',
            'user' => new UserResource($user),
            'otp' => $this->otpResponse($otp),
        ]);
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

        return $this->success([
            'message' => 'Phone number verified successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $login = $this->authService->login($data['phone'], $data['password']);

        return $this->success([
            'message' => 'Logged in successfully.',
            'token' => $login['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($login['user']),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $otp = $this->authService->createPasswordResetOtp($data['phone']);

            return $this->success([
                'message' => 'Password reset OTP sent by SMS successfully.',
                'otp' => $this->otpResponse($otp),
            ]);
        } catch (RuntimeException $exception) {
            return $this->error('Unable to send OTP SMS.', 502, [
                'error' => $exception->getMessage(),
            ]);
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

        return $this->success([
            'message' => 'Password reset OTP verified successfully.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->authService->resetPassword($data['phone'], $data['code'], $data['password']);

        return $this->success([
            'message' => 'Password reset successfully.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success([
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
