<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceStatistic extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'resource_id',
        'views_count',
        'unique_users',
        'total_courses',
        'total_institutions',
        'average_time_seconds',
        'last_access_at',
        'views_by_day',
        'views_by_device',
        'views_by_browser',
        'views_by_country',
        'views_by_os',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'unique_users' => 'integer',
        'total_courses' => 'integer',
        'total_institutions' => 'integer',
        'average_time_seconds' => 'float',
        'last_access_at' => 'datetime',
        'views_by_day' => 'array',
        'views_by_device' => 'array',
        'views_by_browser' => 'array',
        'views_by_country' => 'array',
        'views_by_os' => 'array',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
