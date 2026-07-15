<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $resources = Resource::inTenant()
            ->onlyTrashed()
            ->with(['categories', 'tags', 'folder', 'creator'])
            ->orderByDesc('deleted_at')
            ->paginate(min($request->per_page ?: 24, 100));

        return ApiResponse::success($resources);
    }

    public function restore(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->onlyTrashed()->findOrFail($id);
        $resource->restore();

        return ApiResponse::success($resource, 'Resource restored from trash');
    }

    public function forceDelete(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->onlyTrashed()->findOrFail($id);
        $resource->categories()->detach();
        $resource->tags()->detach();
        $resource->forceDelete();

        return ApiResponse::success(null, 'Resource permanently deleted');
    }

    public function empty(Request $request): JsonResponse
    {
        $count = Resource::inTenant()
            ->onlyTrashed()
            ->where('created_by', $request->user()->id)
            ->forceDelete();

        return ApiResponse::success(['deleted_count' => $count], 'Trash emptied');
    }
}
