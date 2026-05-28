<?php

namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
            $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
            $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
            $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
            $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$connection = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                exit();
            }
        }

        return self::$connection;
    }
}
