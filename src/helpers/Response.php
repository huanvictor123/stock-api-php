<?php

namespace Helpers;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    public static function success(mixed $data, string $message = 'Success', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function created(mixed $data, string $message = 'Created'): void
    {
        self::success($data, $message, 201);
    }

    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        $body = ['success' => false, 'message' => $message];
        if (!empty($errors)) {
            $body['errors'] = $errors;
        }
        self::json($body, $status);
    }

    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }
}
