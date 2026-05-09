<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_QUEUED,
        self::STATUS_PROCESSING,
        self::STATUS_PUBLISHED,
        self::STATUS_FAILED,
    ];

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'title',
        'content',
        'media_urls',
        'platform',
        'scheduled_at',
        'timezone',
        'status',
        'provider_post_id',
        'failure_reason',
        'attempts',
        'payload',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'media_urls' => 'array',
            'payload' => 'array',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
