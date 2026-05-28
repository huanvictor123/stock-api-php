<?php

namespace Models;

use Config\Database;
use PDO;

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        return $this->db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO categories (name, description) VALUES (?, ?)"
        );
        $stmt->execute([$data['name'], $data['description'] ?? null]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasProducts(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM products WHERE category_id = ?"
        );
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
