<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceVersion extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'resource_id',
        'version',
        'file_path',
        'file_size_bytes',
        'notes',
        'changes',
        'created_by',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
