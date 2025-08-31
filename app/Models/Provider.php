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
        'default_config',
        'is_enabled',
        'priority',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'default_config' => 'array',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the provider credentials for this provider.
     */
    public function credentials()
    {
        return $this->hasMany(ProviderCredential::class);
    }

    /**
     * Get the messages sent through this provider.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
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
