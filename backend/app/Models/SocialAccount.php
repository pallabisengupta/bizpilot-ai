<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'connected_by_user_id',
        'provider',
        'provider_account_id',
        'display_name',
        'username',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'metadata',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'metadata' => 'array',
            'token_expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
