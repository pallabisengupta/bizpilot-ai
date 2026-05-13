<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'campaign_id', 'created_by_user_id', 'title', 'content', 'platform', 'media_urls', 'status'];

    protected function casts(): array
    {
        return ['media_urls' => 'array'];
    }
}
