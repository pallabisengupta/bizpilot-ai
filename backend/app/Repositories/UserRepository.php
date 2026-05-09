<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function create(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'role' => $data['role'] ?? 'owner',
            'status' => $data['status'] ?? 'active',
            'preferences' => $data['preferences'] ?? [],
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', strtolower($email))
            ->first();
    }

    public function updateLastLogin(User $user): void
    {
        $user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();
    }
}
