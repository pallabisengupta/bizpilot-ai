<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        $verified = $this->auth->verifyEmail(
            $request->user(),
            (string) $request->route('hash'),
        );

        return response()->json([
            'message' => $verified ? 'Email verified successfully.' : 'Email already verified.',
        ]);
    }
}
