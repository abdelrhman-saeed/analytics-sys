<?php

namespace AnalyticsSystem\Tests\Endpoints;

use AnalyticsSystem\Tests\BaseTestCase;


class ProductTest extends BaseTestCase
{

    private function addProduct(array $product = []): array
    {
        $product = $product + ['name' => 'product_1', 'price' => 500];

        return $this->request('post', '/products', json_encode($product))['body'];
    }

    public function testStoreProduct(): void
    {

        $response = $this->addProduct($product = ['name' => 'prodcut_1', 'price' => 400]);

        $this->assertSame($product + ['id' => '1'], $response);
    }

    public function testGetProducts(): void
    {
        $products = [];

        for ($i = 1; $i < 11; $i++) {
            $this->addProduct($products[] = ['id' => $i, 'name' => "product_$i", 'price' => 500 + $i]);
        }

        $response = $this->request('get', '/products')['body']['products'];

        $this->assertSame($products, $response);
    }

    public function testGetSingleProduct(): void
    {
        $this->addProduct($product = ['name' => 'product_1', 'price' => 504]);
        $this->assertSame(['id' => 1] + $product, $this->request('get', '/products/1')['body']);
    }

    public function testDeleteProduct(): void
    {
        $this->addProduct();
        $this->assertSame(410, $this->request('delete', '/products/1')['status']);
    }
}