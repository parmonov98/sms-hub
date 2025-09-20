<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthClient extends Model
{
    protected $table = 'oauth_clients';
    
    protected $fillable = [
        'user_id',
        'name',
        'secret',
        'provider',
        'redirect',
        'personal_access_client',
        'password_client',
        'revoked',
    ];

    protected $casts = [
        'personal_access_client' => 'boolean',
        'password_client' => 'boolean',
        'revoked' => 'boolean',
    ];

    protected $hidden = [
        'secret',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessTokens()
    {
        return $this->hasMany(OAuthAccessToken::class, 'client_id');
    }

    public function refreshTokens()
    {
        return $this->hasMany(OAuthRefreshToken::class, 'client_id');
    }
}
