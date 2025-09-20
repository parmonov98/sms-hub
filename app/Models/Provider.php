<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
        'description',
        'capabilities',
        'is_enabled',
        'priority',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the provider tokens for this provider.
     */
    public function tokens()
    {
        return $this->hasMany(ProviderToken::class);
    }

    /**
     * Get the active access token for this provider.
     */
    public function accessToken()
    {
        return $this->hasOne(ProviderToken::class)
            ->where('token_type', 'access')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Get the messages sent through this provider.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the SMS templates for this provider.
     */
    public function templates()
    {
        return $this->hasMany(SmsTemplate::class);
    }

    /**
     * Scope to get only enabled providers.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to order by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}
