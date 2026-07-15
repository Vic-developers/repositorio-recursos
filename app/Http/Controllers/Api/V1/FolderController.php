<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $folders = Folder::inTenant()
            ->withCount('resources')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($request->tree || $request->with_children) {
            $folders = $this->buildTree($folders);
        } else {
            $folders = $folders->whereNull('parent_id')->values();
        }

        return ApiResponse::success($folders);
    }

    public function show(string $id): JsonResponse
    {
        $folder = Folder::inTenant()->with(['children', 'parent', 'creator'])->withCount('resources')->findOrFail($id);

        return ApiResponse::success($folder);
    }

    public function store(StoreFolderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = app('current_tenant')->id;
        $data['created_by'] = $request->user()->id;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);

        $folder = Folder::create($data);
        $folder->load(['children', 'creator']);

        return ApiResponse::success($folder, 'Folder created', 201);
    }

    public function update(UpdateFolderRequest $request, string $id): JsonResponse
    {
        $folder = Folder::inTenant()->findOrFail($id);
        $folder->update($request->validated());
        $folder->load(['children', 'parent', 'creator']);

        return ApiResponse::success($folder, 'Folder updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $folder = Folder::inTenant()->findOrFail($id);

        if ($folder->resources()->count() > 0) {
            return ApiResponse::error('Cannot delete folder with resources', 409);
        }

        Folder::where('parent_id', $id)->update(['parent_id' => $folder->parent_id]);
        $folder->delete();

        return ApiResponse::success(null, 'Folder deleted');
    }

    private function buildTree($folders, $parentId = null): array
    {
        $tree = [];
        foreach ($folders as $folder) {
            if ($folder->parent_id === $parentId) {
                $children = $this->buildTree($folders, $folder->id);
                $folder->children = $children;
                $tree[] = $folder;
            }
        }
        return $tree;
    }
}
