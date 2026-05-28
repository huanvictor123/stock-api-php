<?php

namespace Models;

use Config\Database;
use PDO;

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("
            SELECT p.*, c.name AS category_name, s.name AS supplier_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN suppliers  s ON p.supplier_id  = s.id
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, s.name AS supplier_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN suppliers  s ON p.supplier_id  = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO products (category_id, supplier_id, name, description, unit_price, stock_quantity, min_stock)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['category_id'],
            $data['supplier_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['unit_price'],
            $data['stock_quantity'] ?? 0,
            $data['min_stock'] ?? 5,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products SET
                category_id    = ?,
                supplier_id    = ?,
                name           = ?,
                description    = ?,
                unit_price     = ?,
                stock_quantity = ?,
                min_stock      = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['category_id'],
            $data['supplier_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['unit_price'],
            $data['stock_quantity'],
            $data['min_stock'] ?? 5,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
