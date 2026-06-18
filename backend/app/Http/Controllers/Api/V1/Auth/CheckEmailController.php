<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckEmailRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class CheckEmailController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
    ) {
    }

    public function __invoke(CheckEmailRequest $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'exists' => $this->users->findByEmail($request->validated('email')) !== null,
            ],
        ]);
    }
}
