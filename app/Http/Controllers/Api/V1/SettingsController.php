<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::inTenant()->get()->groupBy('module')->map(function ($items) {
            return $items->pluck('value', 'key');
        });

        return ApiResponse::success($settings);
    }

    public function show(string $module): JsonResponse
    {
        $settings = Setting::inTenant()
            ->where('module', $module)
            ->get()
            ->pluck('value', 'key');

        return ApiResponse::success($settings);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $tenantId = app('current_tenant')->id;
        $module = $request->input('module');

        foreach ($request->input('settings') as $key => $value) {
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'module' => $module, 'key' => $key],
                ['value' => $value ?? '']
            );
        }

        $settings = Setting::inTenant()
            ->where('module', $module)
            ->get()
            ->pluck('value', 'key');

        return ApiResponse::success($settings, 'Settings updated');
    }
}
