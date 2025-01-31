<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user->role !== "admin") {
                return response()->json([
                    "message" => "Unauthorized"
                ], 403);
            }
        } catch (Exception $e) {
            // Mensajes de error para los Token
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    "message" => "Invalid Token"
                ], 401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                try {
                    $newToken = JWTAuth::parseToken()->refresh();
                    $request->headers->set('Authorization', 'Bearer ' . $newToken);
                } catch (Exception $e) {
                    return response()->json([
                        "message" => "Token Expired"
                    ], 401);
                }
            } else {
                return response()->json([
                    "message" => "Authorization Token not found"
                ], 401);
            }
        }

        return $next($request);
    }
}