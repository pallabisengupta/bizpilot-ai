<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'user_id', 'generation_type', 'prompt', 'output', 'status', 'tokens_used', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
