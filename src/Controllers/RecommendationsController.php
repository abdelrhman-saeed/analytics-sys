<?php

namespace AnalyticsSystem\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};


class RecommendationsController extends BaseController
{
    public function index()
    {
        return (new JsonResponse([

            'recommendations' => [

                'best_products' => $this->openAI(),
                'weather_temp'  => ($temp = $this->getWeatherTemp()) ?? 'Weather data unavalable',
                'weather_tip'   => $this->getWeatherBasedRecommendation($temp)
            ]
        ]))->send();
    }

    private function openAI()
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

        curl_setopt_array($ch = curl_init($_ENV['OPENAI_URL']), [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPHEADER      => ["Content-Type: application/json", "Authorization: Bearer {$_ENV['OPENAI_K']}"],
            CURLOPT_POSTFIELDS      => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true)['choices'][0]['message']['content'] ?? 'No recommendation returned';
    }

    private function getWeatherTemp(): ?float
    {
        curl_setopt_array($ch = curl_init($_ENV['OPEN_WEATHER_URL'] . $_ENV['OPEN_WEATHER_K']), [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 10
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return null;

        return json_decode($response, true) ['main']['temp'] ?? null;
    }

    private function getWeatherBasedRecommendation(?float $temp): string {

        return match (true) {

            is_null($temp)  => 'Weather is moderate. Focus on regular best-sellers.',

            $temp >= 30     => 'It is hot today. Promote cold drinks like iced tea or smoothies.' ,
            $temp <= 15     => 'It is cold today. Promote hot drinks like coffee or hot chocolate.',
        };
    }
}
