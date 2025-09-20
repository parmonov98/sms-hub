<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthAccessToken extends Model
{
    protected $table = 'oauth_access_tokens';
    
    protected $fillable = [
        'id',
        'user_id',
        'client_id',
        'name',
        'scopes',
        'revoked',
        'expires_at',
    ];

    protected $casts = [
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(OAuthClient::class, 'client_id');
    }
}
