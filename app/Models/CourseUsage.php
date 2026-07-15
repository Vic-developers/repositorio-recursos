<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseUsage extends Model
{
    use HasUuids;

    protected $fillable = [
        'resource_id',
        'course_name',
        'institution',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
