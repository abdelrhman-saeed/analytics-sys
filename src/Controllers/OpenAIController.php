<?php

namespace AnalyticsSystem\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};


class OpenAIController extends BaseController
{
    public function index()
    {
        $products = $this->pdo
            ->query(
                "SELECT product_id, quantity, products.price FROM order_items
                JOIN products ON products.id = order_items.product_id
                JOIN orders ON order_items.order_id = orders.id
                ORDER BY created_at DESC")
            ->fetchAll(\PDO::FETCH_ASSOC);

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant that suggests which products to promote to increase sales.'],
            ['role' => 'user',   'content' => "Given this sales data, what products should we promote for higher revenue?\n\n" . json_encode($products)]
        ];

        $payload = json_encode([
            'model'         => 'gpt-3.5-turbo',
            'messages'      => $messages,
            'temperature'   => 0.7,
        ]);

        curl_setopt_array( $ch = curl_init($_ENV['OPENAI_K']), [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => ["Content-Type: application/json", "Authorization: Bearer {$_ENV['OPENAI_K']}"],
            CURLOPT_POSTFIELDS      => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return (new JsonResponse($data['choices'][0]['message']['content'] ?? 'No recommendation returned', status: 422))->send();
    }
}
