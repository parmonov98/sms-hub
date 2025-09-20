<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthRefreshToken extends Model
{
    protected $table = 'oauth_refresh_tokens';
    
    protected $fillable = [
        'id',
        'access_token_id',
        'revoked',
        'expires_at',
    ];

    protected $casts = [
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function accessToken()
    {
        return $this->belongsTo(OAuthAccessToken::class, 'access_token_id');
    }
}
