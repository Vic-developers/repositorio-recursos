<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Resource;
use App\Models\ResourceComment;
use App\Models\ResourceShare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = app('current_tenant')->id;
        $userId = $request->user()->id;

        $totalResources = Resource::inTenant($tenantId)->count();
        $myResources = Resource::inTenant($tenantId)->where('created_by', $userId)->count();
        $favorites = Resource::inTenant($tenantId)->where('is_favorite', true)->where('created_by', $userId)->count();
        $trashed = Resource::inTenant($tenantId)->onlyTrashed()->count();

        $totalViews = Resource::inTenant($tenantId)->sum('view_count');
        $totalDownloads = Resource::inTenant($tenantId)->sum('download_count');
        $totalStorage = Resource::inTenant($tenantId)->sum('file_size_bytes');

        $storageUsed = Resource::inTenant($tenantId)
            ->select(DB::raw('SUM(file_size_bytes) as total, type'))
            ->groupBy('type')
            ->get()
            ->map(fn($item) => [
                'type' => $item->type,
                'bytes' => (int) $item->total,
                'display' => $item->total ? $this->formatBytes((int) $item->total) : '0 B',
            ]);

        $byType = Resource::inTenant($tenantId)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        $recentResources = Resource::inTenant($tenantId)
            ->with('creator')
            ->latest()
            ->limit(10)
            ->get();

        $popularResources = Resource::inTenant($tenantId)
            ->with('creator')
            ->orderByDesc('view_count')
            ->limit(5)
            ->get();

        $recentActivity = $this->getRecentActivity($tenantId, $userId);
        $sharesByType = $this->getSharesByType($tenantId);

        return ApiResponse::success([
            'counts' => [
                'total_resources' => $totalResources,
                'my_resources' => $myResources,
                'favorites' => $favorites,
                'trashed' => $trashed,
            ],
            'usage' => [
                'total_views' => $totalViews,
                'total_downloads' => $totalDownloads,
                'total_storage_bytes' => $totalStorage,
                'total_storage_display' => $this->formatBytes((int) $totalStorage),
                'storage_by_type' => $storageUsed,
            ],
            'by_type' => $byType,
            'recent_resources' => $recentResources,
            'popular_resources' => $popularResources,
            'recent_activity' => $recentActivity,
            'shares_by_type' => $sharesByType,
        ]);
    }

    private function getRecentActivity(string $tenantId, string $userId): array
    {
        $recentResources = Resource::inTenant($tenantId)
            ->where('created_by', $userId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'type' => 'resource_created',
                'label' => 'Recurso creado',
                'resource_name' => $r->name,
                'resource_id' => $r->id,
                'created_at' => $r->created_at,
            ]);

        return $recentResources->toArray();
    }

    private function getSharesByType(string $tenantId): array
    {
        return ResourceShare::whereHas('resource', fn($q) => $q->where('tenant_id', $tenantId))
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->toArray();
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
