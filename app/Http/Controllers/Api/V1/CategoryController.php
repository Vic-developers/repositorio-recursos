<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->inTenant()
            ->withCount('resources')
            ->when($request->parent_id === 'null', fn($q) => $q->whereNull('parent_id'))
            ->when($request->parent_id && $request->parent_id !== 'null', fn($q) => $q->where('parent_id', $request->parent_id))
            ->orderBy('sort_order')
            ->orderBy('name');

        $categories = $query->get();

        if ($request->tree) {
            $categories = $this->buildTree($categories);
        }

        return ApiResponse::success($categories);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::inTenant()->with(['parent', 'children'])->withCount('resources')->findOrFail($id);

        return ApiResponse::success($category);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = app('current_tenant')->id;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);

        $category = Category::create($data);

        return ApiResponse::success($category, 'Category created', 201);
    }

    public function update(StoreCategoryRequest $request, string $id): JsonResponse
    {
        $category = Category::inTenant()->findOrFail($id);
        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        }
        $category->update($data);

        return ApiResponse::success($category, 'Category updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::inTenant()->findOrFail($id);
        Category::where('parent_id', $id)->update(['parent_id' => $category->parent_id]);
        $category->resources()->detach();
        $category->delete();

        return ApiResponse::success(null, 'Category deleted');
    }

    private function buildTree($categories, $parentId = null): array
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $children = $this->buildTree($categories, $category->id);
                $category->children = $children;
                $tree[] = $category;
            }
        }
        return $tree;
    }
}
