<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $resources = Resource::inTenant()
            ->where('is_favorite', true)
            ->where('created_by', $request->user()->id)
            ->with(['categories', 'tags', 'folder', 'creator'])
            ->orderByDesc('updated_at')
            ->paginate(min($request->per_page ?: 24, 100));

        return ApiResponse::success($resources);
    }
}
