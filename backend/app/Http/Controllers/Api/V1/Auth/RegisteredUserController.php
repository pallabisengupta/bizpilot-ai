<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    public function store(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->register(
            $request->validated(),
            $request->input('device_name', 'api-token'),
        );

        return response()->json([
            'message' => 'Registration successful.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ],
        ], 201);
    }
}
