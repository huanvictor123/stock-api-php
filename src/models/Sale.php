<?php

namespace Models;

use Config\Database;
use PDO;
use Exception;

class Sale
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $saleData, array $items): int
    {
        $this->db->beginTransaction();

        try {
            foreach ($items as $item) {
                $stmt = $this->db->prepare(
                    "SELECT id, name, stock_quantity FROM products WHERE id = ? FOR UPDATE"
                );
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch();

                if (!$product) {
                    throw new Exception("Product {$item['product_id']} not found", 404);
                }

                if ($product['stock_quantity'] < $item['quantity']) {
                    throw new Exception(
                        "Insufficient stock for '{$product['name']}'. " .
                        "Available: {$product['stock_quantity']}, Requested: {$item['quantity']}",
                        422
                    );
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO sales (customer_name, customer_email, total_amount, status)
                VALUES (?, ?, 0.00, 'confirmed')
            ");
            $stmt->execute([$saleData['customer_name'], $saleData['customer_email'] ?? null]);
            $saleId = (int) $this->db->lastInsertId();

            $totalAmount = 0.0;

            foreach ($items as $item) {
                $stmt = $this->db->prepare(
                    "SELECT unit_price FROM products WHERE id = ?"
                );
                $stmt->execute([$item['product_id']]);
                $price = (float) $stmt->fetchColumn();

                $subtotal     = $price * $item['quantity'];
                $totalAmount += $subtotal;

                $stmt = $this->db->prepare("
                    INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$saleId, $item['product_id'], $item['quantity'], $price, $subtotal]);

                $stmt = $this->db->prepare("
                    UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $stmt = $this->db->prepare("UPDATE sales SET total_amount = ? WHERE id = ?");
            $stmt->execute([$totalAmount, $saleId]);

            $this->db->commit();

            return $saleId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE id = ?");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();

        if (!$sale) return false;

        $stmt = $this->db->prepare("
            SELECT si.*, p.name AS product_name
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = ?
        ");
        $stmt->execute([$id]);
        $sale['items'] = $stmt->fetchAll();

        return $sale;
    }
}
