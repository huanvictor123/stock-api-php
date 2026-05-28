<?php

namespace Models;

use Config\Database;
use PDO;

class Report
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function lowStock(): array
    {
        $stmt = $this->db->query("
            SELECT p.id, p.name, p.stock_quantity, p.min_stock,
                   c.name AS category,
                   (p.min_stock - p.stock_quantity) AS units_below_minimum
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.stock_quantity <= p.min_stock
            ORDER BY p.stock_quantity ASC
        ");
        return $stmt->fetchAll();
    }

    public function salesSummary(?string $start = null, ?string $end = null): array
    {
        $start = $start ?? date('Y-m-01');
        $end   = $end   ?? date('Y-m-t');

        $stmt = $this->db->prepare("
            SELECT
                COUNT(*)           AS total_sales,
                SUM(total_amount)  AS total_revenue,
                AVG(total_amount)  AS average_ticket,
                MIN(total_amount)  AS min_sale,
                MAX(total_amount)  AS max_sale,
                ? AS period_start,
                ? AS period_end
            FROM sales
            WHERE status != 'cancelled'
              AND created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start, $end, $start . ' 00:00:00', $end . ' 23:59:59']);
        return $stmt->fetch();
    }
}
