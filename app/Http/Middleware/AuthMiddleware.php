<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Authenticate the token
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                //In case the token has expired
                try {
                    $newToken = JWTAuth::parseToken()->refresh();
                    $request->headers->set('Authorization', 'Bearer ' . $newToken);
                } catch (Exception $e) {
                    return response()->json([
                        "message" => "Token Expired"
                    ], 401);
                }
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                //Invalid Token
                return response()->json([
                    "error" => "Invalid Token"
                ], 401);
            } else {
                return response()->json([
                    "error" => "Authorization Token not found"
                ], 401);
            }
        }
        return $next($request);
    }
}