<?php

namespace AnalyticsSystem\Tests;

use PHPUnit\Framework\TestCase;
use AnalyticsSystem\DB\Connection;


abstract class BaseTestCase extends TestCase
{
    protected string $host = "http://127.0.0.1:8000";


    protected function setUp(): void
    {
        $pdo = (new Connection)->pdo;

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec('DELETE FROM order_items');
        $pdo->exec('DELETE FROM products');
        $pdo->exec('DELETE FROM orders');

        $pdo->exec('DELETE FROM sqlite_sequence;');
    }

    protected function request(string $method, string $path, string $body): array
    {
        $url    = $this->host . $path;
        $method = strtoupper($method);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($ch);
        $statusCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $statusCode,
            'body'   => json_decode($responseBody, true)
        ];
    }
}
