<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'provider_id',
        'credentials',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the project that owns this credential.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the provider for this credential.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Scope to get only active credentials.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
