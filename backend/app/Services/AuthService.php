<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
    ) {
    }

    public function register(array $data, string $tokenName = 'api-token'): array
    {
        $user = $this->users->create($data);

        event(new Registered($user));

        return $this->tokenResponse($user, $tokenName);
    }

    public function login(array $data, Request $request, string $tokenName = 'api-token'): array
    {
        $this->ensureIsNotRateLimited($request);

        $user = $this->users->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey($request));

            event(new Failed('sanctum', $user, [
                'email' => $data['email'],
            ]));

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account is not active.'],
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $this->users->updateLastLogin($user);

        event(new Login('sanctum', $user, false));

        return $this->tokenResponse($user->fresh(), $tokenName);
    }

    public function logout(User $user, ?int $tokenId = null): void
    {
        if ($tokenId) {
            $user->tokens()->whereKey($tokenId)->delete();
        } else {
            $user->tokens()->delete();
        }

        event(new Logout('sanctum', $user));
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink([
            'email' => strtolower($email),
        ]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset(
            [
                'email' => strtolower($data['email']),
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $this->users->updatePassword($user, $password);
                $user->setRememberToken(Str::random(60));
                $user->save();
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );
    }

    public function verifyEmail(User $user, string $hash): bool
    {
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException('Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if ($user instanceof MustVerifyEmail && $user->markEmailAsVerified()) {
            event(new Verified($user));

            return true;
        }

        return false;
    }

    public function sendEmailVerification(User $user): void
    {
        if (! $user instanceof MustVerifyEmail || $user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }

    private function tokenResponse(User $user, string $tokenName): array
    {
        $token = $user->createToken($tokenName);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
