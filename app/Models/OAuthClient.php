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
     * Automatically hash the secret when it's set
     */
    public function setSecretAttribute($value)
    {
        // Only hash if the value is not already hashed (doesn't start with $2y$)
        if ($value && !str_starts_with($value, '$2y$')) {
            $this->attributes['secret'] = bcrypt($value);
        } else {
            $this->attributes['secret'] = $value;
        }
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
