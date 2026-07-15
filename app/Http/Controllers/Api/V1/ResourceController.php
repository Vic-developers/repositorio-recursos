<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Resource;
use App\Services\ScormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    private ScormService $scormService;

    public function __construct(ScormService $scormService)
    {
        $this->scormService = $scormService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Resource::query()
            ->inTenant()
            ->with(['categories', 'tags', 'folder', 'creator'])
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('name', 'like', "%{$v}%")
                      ->orWhere('description', 'like', "%{$v}%")
                      ->orWhere('author_name', 'like', "%{$v}%");
                });
            })
            ->when($request->category_id, fn($q, $v) => $q->whereHas('categories', fn($q) => $q->where('categories.id', $v)))
            ->when($request->tag_id, fn($q, $v) => $q->whereHas('tags', fn($q) => $q->where('tags.id', $v)))
            ->when($request->folder_id, fn($q, $v) => $q->where('folder_id', $v))
            ->when($request->sort === 'oldest', fn($q) => $q->oldest())
            ->when($request->sort === 'name', fn($q) => $q->orderBy('name'))
            ->when($request->sort === 'popular', fn($q) => $q->orderByDesc('view_count'))
            ->orderByDesc('created_at');

        $perPage = min((int) $request->per_page ?: 24, 100);
        $resources = $query->paginate($perPage);

        return ApiResponse::success($resources);
    }

    public function show(string $id): JsonResponse
    {
        $resource = Resource::inTenant()
            ->with(['categories', 'tags', 'folder', 'creator', 'versions', 'comments.user'])
            ->findOrFail($id);

        return ApiResponse::success($resource);
    }

    public function store(StoreResourceRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $tenantId = app('current_tenant')->id;
            $user = $request->user();

            $data = $request->validated();
            $data['tenant_id'] = $tenantId;
            $data['created_by'] = $user->id;
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
            $data['uuid'] = (string) Str::uuid();
            $data['status'] ??= 'Published';

            $file = $request->file('file');
            if ($file) {
                $path = $file->store("resources/{$data['uuid']}", 'public');
                $data['file_path'] = $path;
                $data['original_file_name'] = $file->getClientOriginalName();
                $data['mime_type'] = $file->getMimeType();
                $data['file_size_bytes'] = $file->getSize();
                $data['file_size_display'] = $this->formatBytes($file->getSize());

                // Use stored file path instead of temp path (Octane compat)
                $storedPath = storage_path('app/public/' . $path);
                if (file_exists($storedPath) && $this->scormService->isScormPackage($storedPath)) {
                    $data['type'] = 'SCORM';
                    $extractDir = $this->scormService->extractPackage($data['uuid'], $storedPath);
                }
            }

            $resource = Resource::create($data);

            if ($request->category_ids) {
                $resource->categories()->sync($request->category_ids);
            }
            if ($request->tag_ids) {
                $resource->tags()->sync($request->tag_ids);
            }

            $resource->load(['categories', 'tags', 'folder', 'creator']);

            return ApiResponse::success($resource, 'Resource created', 201);
        });
    }

    public function update(UpdateResourceRequest $request, string $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            $resource = Resource::inTenant()->findOrFail($id);

            $data = $request->validated();

            $file = $request->file('file');
            if ($file) {
                if ($resource->file_path) {
                    Storage::disk('public')->delete($resource->file_path);
                }
                $path = $file->store("resources/{$resource->uuid}", 'public');
                $data['file_path'] = $path;
                $data['original_file_name'] = $file->getClientOriginalName();
                $data['mime_type'] = $file->getMimeType();
                $data['file_size_bytes'] = $file->getSize();
                $data['file_size_display'] = $this->formatBytes($file->getSize());

                $storedPath = storage_path('app/public/' . $path);
                if (file_exists($storedPath) && $this->scormService->isScormPackage($storedPath)) {
                    $data['type'] = 'SCORM';
                    $this->scormService->extractPackage($resource->uuid, $storedPath);
                }
            }

            $resource->update($data);

            if ($request->has('category_ids')) {
                $resource->categories()->sync($request->category_ids ?? []);
            }
            if ($request->has('tag_ids')) {
                $resource->tags()->sync($request->tag_ids ?? []);
            }

            $resource->load(['categories', 'tags', 'folder', 'creator']);

            return ApiResponse::success($resource, 'Resource updated');
        });
    }

    public function destroy(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($id);
        $resource->delete();

        return ApiResponse::success(null, 'Resource moved to trash');
    }

    public function restore(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->onlyTrashed()->findOrFail($id);
        $resource->restore();

        return ApiResponse::success($resource, 'Resource restored');
    }

    public function forceDelete(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->onlyTrashed()->findOrFail($id);

        if ($resource->file_path) {
            Storage::disk('public')->deleteDirectory("resources/{$resource->uuid}");
        }

        $resource->categories()->detach();
        $resource->tags()->detach();
        $resource->forceDelete();

        return ApiResponse::success(null, 'Resource permanently deleted');
    }

    public function duplicate(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $original = Resource::inTenant()->findOrFail($id);
            $copy = $original->replicate(['uuid']);
            $copy->uuid = (string) Str::uuid();
            $copy->name = $original->name . ' (copia)';
            $copy->slug = Str::slug($copy->name) . '-' . Str::random(6);
            $copy->view_count = 0;
            $copy->download_count = 0;
            $copy->usage_count = 0;
            $copy->is_favorite = false;
            $copy->save();

            $copy->categories()->sync($original->categories->pluck('id'));
            $copy->tags()->sync($original->tags->pluck('id'));

            $copy->load(['categories', 'tags', 'folder', 'creator']);

            return ApiResponse::success($copy, 'Resource duplicated', 201);
        });
    }

    public function toggleFavorite(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($id);
        $resource->update(['is_favorite' => !$resource->is_favorite]);

        return ApiResponse::success([
            'is_favorite' => $resource->fresh()->is_favorite,
        ]);
    }

    public function incrementDownload(string $id): JsonResponse
    {
        $resource = Resource::inTenant()->findOrFail($id);
        $resource->increment('download_count');

        return ApiResponse::success(['download_count' => $resource->fresh()->download_count]);
    }

    public function uploadFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:512000',
        ]);

        $file = $request->file('file');
        $uuid = (string) Str::uuid();
        $path = $file->store("resources/{$uuid}", 'public');

        $result = [
            'uuid' => $uuid,
            'file_path' => $path,
            'original_file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size_bytes' => $file->getSize(),
            'file_size_display' => $this->formatBytes($file->getSize()),
        ];

        $storedPath = storage_path('app/public/' . $path);
        if (file_exists($storedPath) && $this->scormService->isScormPackage($storedPath)) {
            $result['is_scorm'] = true;
            $this->scormService->extractPackage($uuid, $storedPath);
            $result['scorm_launch_file'] = $this->scormService->getLaunchFilePath($uuid);
        }

        return ApiResponse::success($result, 'File uploaded', 201);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
