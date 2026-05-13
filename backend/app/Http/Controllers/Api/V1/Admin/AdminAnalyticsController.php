<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\Lead;
use App\Models\Schedule;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;

class AdminAnalyticsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_clients' => Tenant::query()->count(),
                'active_subscriptions' => Subscription::query()->where('status', 'active')->count(),
                'total_leads' => Lead::query()->count(),
                'scheduled_posts' => Schedule::query()->whereIn('status', ['queued', 'processing'])->count(),
                'ai_usage' => AiGeneration::query()->sum('tokens_used'),
            ],
        ]);
    }
}
