<?php

namespace AnalyticsSystem\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};


class AnalyticsController extends BaseController
{


    public function index()
    {
        return new JsonResponse($this->getAnalytics());
    }

    public function getAnalytics(): array
    {
        return [

            'total_revenue'                 => $this->getTotalRevenue(),
            'top_products'                  => $this->getTopProductsBySales(),
            'revenue_in_last_minute'        => $this->getRevenueInLastMinute(),
            'orders_count_in_last_minute'   => $this->getOrdersCountInLastMinute()
        ];
    }

    private function getTotalRevenue(): int
    {
        $revenue = $this->pdo->query("
                SELECT
                    SUM(products.price * order_items.quantity) as total_revenue
                FROM order_items

                JOIN products ON products.id = order_items.product_id")
            ->fetchColumn();

        return number_format($revenue / 100, 2);
    }

    private function getRevenueInLastMinute(): int
    {
        $revenue = $this->pdo->query("
            SELECT
                SUM(oi.quantity * p.price) AS revenue_last_minute
            FROM orders o

            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id

            WHERE o.created_at >= datetime('now', '-1 minute')
            ")
        ->fetchColumn();

        return number_format($revenue / 100, 2);
    }

    private function getTopProductsBySales(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                p.id, p.name, SUM(oi.quantity) AS total_quantity_sold
            FROM order_items oi

            JOIN products p ON p.id = oi.product_id

            GROUP BY p.id, p.name
            ORDER BY total_quantity_sold DESC

            LIMIT 10
            ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getOrdersCountInLastMinute(): int
    {
        return $this->pdo->query("
            SELECT
                count(id) FROM orders
            WHERE orders.created_at >= datetime('now', '-1 minute')")
        ->fetchColumn();
    }

}
