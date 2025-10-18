<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthClient extends Model
{
    protected $table = 'oauth_clients';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'owner_type',
        'owner_id',
        'name',
        'secret',
        'provider',
        'redirect_uris',
        'grant_types',
        'revoked',
    ];

    protected $casts = [
        'revoked' => 'boolean',
        'redirect_uris' => 'array',
        'grant_types' => 'array',
    ];

    /**
     * Store OAuth client secrets as plain text (Laravel Passport requirement)
     * OAuth clients need plain text secrets for authentication to work properly
     */
    public function setSecretAttribute($value)
    {
        // Don't hash OAuth client secrets - Laravel Passport expects plain text
        // The secret is already generated as a secure random string in CreateOAuthClient
        $this->attributes['secret'] = $value;
    }

    /**
     * Override the secret getter to return plain text for OAuth clients
     * This ensures compatibility with Laravel Passport's ClientRepository
     */
    public function getSecretAttribute($value)
    {
        // For OAuth clients, always return the secret as stored (plain text)
        // This prevents any automatic hashing from interfering with OAuth authentication
        return $value;
    }

    protected $hidden = [
        'secret',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function accessTokens()
    {
        return $this->hasMany(OAuthAccessToken::class, 'client_id');
    }

    public function refreshTokens()
    {
        return $this->hasMany(OAuthRefreshToken::class, 'client_id');
    }

    // Helper methods for compatibility
    public function getPersonalAccessClientAttribute()
    {
        return in_array('personal_access', $this->grant_types ?? []);
    }

    public function getPasswordClientAttribute()
    {
        return in_array('password', $this->grant_types ?? []);
    }

    public function getRedirectAttribute()
    {
        return $this->redirect_uris ? ($this->redirect_uris[0] ?? null) : null;
    }

    public function getUserIdAttribute()
    {
        return $this->owner_id;
    }
}
