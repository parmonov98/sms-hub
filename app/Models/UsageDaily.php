<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageDaily extends Model
{
    use HasFactory;

    protected $table = 'usage_daily';

    protected $fillable = [
        'project_id',
        'date',
        'messages',
        'parts',
        'cost_decimal',
        'currency',
    ];

    protected $casts = [
        'date' => 'date',
        'messages' => 'integer',
        'parts' => 'integer',
        'cost_decimal' => 'decimal:4',
    ];

    /**
     * Get the project that owns this usage record.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope to get usage for a specific date range.
     */
    public function scopeForDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope to get usage for a specific project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
