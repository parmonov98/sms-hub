<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', // Optional in new system
        'provider_id',
        'provider_message_id',
        'to',
        'from',
        'text',
        'parts',
        'status',
        'error_code',
        'error_message',
        'price_decimal',
        'currency',
        'idempotency_key',
    ];

    protected $casts = [
        'parts' => 'integer',
        'price_decimal' => 'decimal:4',
    ];

    /**
     * Get the project that owns this message.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the provider used for this message.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Scope to get messages by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get messages for a specific project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
