<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'content',
        'status',
        'provider_template_id',
        'variables',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the provider that owns the template.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Scope to get templates by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get approved templates.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending templates.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if template is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if template is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if template is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
