<?php

namespace Middleware;

use Helpers\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware
{
    public static function handle(): object
    {
        $headers = getallheaders();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (empty($auth) || !str_starts_with($auth, 'Bearer ')) {
            Response::error('Authorization token required', 401);
        }

        $token  = substr($auth, 7);
        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            Response::error('Invalid or expired token', 401);
        }
    }
}
