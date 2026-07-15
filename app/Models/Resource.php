<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'folder_id',
        'name',
        'slug',
        'description',
        'type',
        'status',
        'version',
        'original_file_name',
        'mime_type',
        'file_path',
        'file_size_bytes',
        'file_size_display',
        'thumbnail_url',
        'duration',
        'language',
        'level',
        'area',
        'competencies',
        'learning_outcomes',
        'estimated_time_minutes',
        'author_name',
        'view_count',
        'download_count',
        'usage_count',
        'is_favorite',
        'uuid',
        'created_by',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'estimated_time_minutes' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'usage_count' => 'integer',
        'is_favorite' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'resource_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'resource_tag');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_resource');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResourceVersion::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ResourceShare::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ResourceComment::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ResourcePermission::class);
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(ResourceStatistic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isScorm(): bool
    {
        return in_array($this->type, ['SCORM', 'scorm', 'SCORM 1.2', 'SCORM 2004']);
    }

    public function isH5p(): bool
    {
        return $this->type === 'H5P';
    }

    public function scopeMine($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function scopeInTenant($query, ?string $tenantId = null)
    {
        $tenantId = $tenantId ?? app('current_tenant')?->id;
        return $tenantId ? $query->where('tenant_id', $tenantId) : $query;
    }
}
