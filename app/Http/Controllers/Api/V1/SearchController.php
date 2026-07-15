<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        $q = $request->q;
        $tenantId = app('current_tenant')->id;

        $resources = Resource::inTenant($tenantId)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('author_name', 'like', "%{$q}%")
                    ->orWhere('competencies', 'like', "%{$q}%")
                    ->orWhere('learning_outcomes', 'like', "%{$q}%")
                    ->orWhere('area', 'like', "%{$q}%");
            })
            ->with(['categories', 'tags', 'folder', 'creator'])
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->when($request->level, fn($q, $v) => $q->where('level', $v))
            ->when($request->area, fn($q, $v) => $q->where('area', $v))
            ->when($request->language, fn($q, $v) => $q->where('language', $v))
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 WHEN name LIKE ? THEN 1 ELSE 2 END", ["{$q}%", "%{$q}%"])
            ->orderByDesc('view_count')
            ->paginate(min($request->per_page ?: 24, 100));

        return ApiResponse::success($resources);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1|max:50']);

        $q = $request->q;
        $tenantId = app('current_tenant')->id;

        $names = Resource::inTenant($tenantId)
            ->where('name', 'like', "{$q}%")
            ->limit(8)
            ->pluck('name');

        $tags = \App\Models\Tag::inTenant($tenantId)
            ->where('name', 'like', "{$q}%")
            ->limit(4)
            ->pluck('name');

        $authors = Resource::inTenant($tenantId)
            ->where('author_name', 'like', "{$q}%")
            ->whereNotNull('author_name')
            ->limit(4)
            ->distinct()
            ->pluck('author_name');

        return ApiResponse::success([
            'names' => $names,
            'tags' => $tags,
            'authors' => $authors,
        ]);
    }
}
