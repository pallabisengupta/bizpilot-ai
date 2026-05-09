<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    public function store(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            $request->validated(),
            $request,
            $request->input('device_name', 'api-token'),
        );

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $tokenId = $request->user()?->currentAccessToken()?->id;

        $this->auth->logout($request->user(), $tokenId);

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
