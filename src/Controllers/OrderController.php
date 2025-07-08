<?php

namespace AnalyticsSystem\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Predis\Client as Redis;
use AnalyticsSystem\DTO\OrderDTO;


class OrderController extends BaseController
{
    public function save()
    {
        $orderDTO = new OrderDTO(json_decode($this->request->getContent(), true) ?? []);

        if (! empty($orderDTO->getErrors())) {
            return new JsonResponse($orderDTO->getErrors(), 403);
        }

        $orderItems = $orderDTO->getValidated() ['products'];

        $this->pdo
            ->prepare("INSERT INTO orders (created_at) VALUES (datetime('now'))")
            ->execute();

        $orderId = $this->pdo->lastInsertId();

        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");

        foreach($orderItems as $item) {
            $stmt->execute([$orderId, $item['product_id'], $item['quantity']]);
        }

        if ($this->pdo->commit()) {

            $redis = new Redis();
            $redis->publish('broadcast', json_encode((new AnalyticsController)->index()));

            return new JsonResponse(['id' => $orderId] + $orderItems);
        }

        return (new JsonResponse(['error' => 'Server error!'], 500))->send();
    }
}
