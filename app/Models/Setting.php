<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'module', 'key', 'value',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeInTenant($query, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? app('current_tenant')?->id;
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }
}
