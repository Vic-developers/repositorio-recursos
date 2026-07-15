<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::query()
            ->inTenant()
            ->withCount('resources')
            ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->get();

        return ApiResponse::success($tags);
    }

    public function show(string $id): JsonResponse
    {
        $tag = Tag::inTenant()->withCount('resources')->findOrFail($id);

        return ApiResponse::success($tag);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = app('current_tenant')->id;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);

        $tag = Tag::create($data);

        return ApiResponse::success($tag, 'Tag created', 201);
    }

    public function update(StoreTagRequest $request, string $id): JsonResponse
    {
        $tag = Tag::inTenant()->findOrFail($id);
        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        }
        $tag->update($data);

        return ApiResponse::success($tag, 'Tag updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $tag = Tag::inTenant()->findOrFail($id);
        $tag->resources()->detach();
        $tag->delete();

        return ApiResponse::success(null, 'Tag deleted');
    }
}
