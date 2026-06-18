<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicPlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlanResource::collection(
            Plan::query()
                ->with('product')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        );
    }

    public function product(string $url): JsonResponse
    {
        $product = Product::query()
            ->where('url', $url)
            ->where('is_active', true)
            ->firstOrFail();

        $plans = $product->plans()
            ->with('product')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => PlanResource::collection($plans),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'url' => $product->url,
                'description' => $product->description,
            ],
        ]);
    }
}
