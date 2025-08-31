<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_user_id',
        'default_provider',
        'ip_allowlist',
        'is_active',
    ];

    protected $casts = [
        'ip_allowlist' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the owner of the project.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get the provider credentials for this project.
     */
    public function providerCredentials()
    {
        return $this->hasMany(ProviderCredential::class);
    }

    /**
     * Get the messages for this project.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the daily usage for this project.
     */
    public function usageDaily()
    {
        return $this->hasMany(UsageDaily::class);
    }

    /**
     * Check if IP is allowed for this project.
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->ip_allowlist)) {
            return true; // No restrictions
        }

        return in_array($ip, $this->ip_allowlist);
    }
}
