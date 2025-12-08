<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if(!$token) {
            return response()->json([
                'status' => 1,
                'msg' => 'Token missing!'
            ], 401);
        }

        $decodedToken = jwtdecode($token);
        if(!$decodedToken) {
            return response()->json([
                'status' => 1,
                'msg' => 'Invalid or expired!'
            ], 401);
        }

        return $next($request);
    }
}
