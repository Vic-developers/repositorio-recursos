<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->header('X-Tenant')
            ?? $request->session()->get('tenant_slug');

        if ($slug) {
            $tenant = Tenant::where('slug', $slug)->first();
        } elseif ($request->user()) {
            $tenant = $request->user()->tenant;
        } else {
            return response()->json(['message' => 'Tenant not found'], 401);
        }

        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found'], 401);
        }

        app()->instance('current_tenant', $tenant);

        return $next($request);
    }
}
