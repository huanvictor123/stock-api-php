<?php

namespace Controllers;

use Config\Database;
use Helpers\Response;
use Firebase\JWT\JWT;

class AuthController
{
    public function login(): void
    {
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = $body['email'] ?? '';
        $pass  = $body['password'] ?? '';

        if (empty($email) || empty($pass)) {
            Response::error('Email and password are required', 400);
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($pass, $user['password'])) {
            Response::error('Invalid credentials', 401);
        }

        $payload = [
            'iss'  => 'stock-api',
            'iat'  => time(),
            'exp'  => time() + 3600,
            'sub'  => $user['id'],
            'name' => $user['name'],
        ];

        $secret = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET');
        $token  = JWT::encode($payload, $secret, 'HS256');

        Response::success([
            'token'      => $token,
            'expires_in' => 3600,
            'user'       => ['id' => $user['id'], 'name' => $user['name']],
        ], 'Login successful');
    }
}
