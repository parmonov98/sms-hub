<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProviderToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'token_type',
        'token_value',
        'expires_at',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the provider that owns this token.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Scope to get only active tokens.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('token_type', $type);
    }

    /**
     * Scope to get non-expired tokens.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get valid tokens (active and not expired).
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the token is valid (active and not expired).
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Get the time until expiration.
     */
    public function timeUntilExpiration(): ?Carbon
    {
        if (!$this->expires_at) {
            return null;
        }

        return $this->expires_at->isFuture() ? $this->expires_at : null;
    }
}