<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->withCount('plans')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(100)
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => ['product' => new ProductResource($product)],
        ], 201);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => ['product' => new ProductResource($product->refresh())],
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
