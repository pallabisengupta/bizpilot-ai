<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->where('is_active', true)
                ->withCount(['plans' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }
}
