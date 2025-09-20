<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClientCredentialsAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Decode JWT to get the jti (JWT ID) field
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json(['message' => 'Invalid token format.'], 401);
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        if (!$payload || !isset($payload['jti'])) {
            return response()->json(['message' => 'Invalid token payload.'], 401);
        }
        
        // Check if the token exists in the database using the jti
        $accessToken = \Laravel\Passport\Token::where('id', $payload['jti'])->first();
        
        if (!$accessToken || $accessToken->revoked) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Check if token is expired
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json(['message' => 'Token expired.'], 401);
        }

        // For client credentials, we don't need a user, just verify the client
        $request->attributes->set('oauth_client_id', $accessToken->client_id);
        
        return $next($request);
    }

    /**
     * Get the token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request)
    {
        $header = $request->header('Authorization');
        
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return substr($header, 7);
    }
}
