<?php

namespace AnalyticsSystem\Tests\Endpoints;

use AnalyticsSystem\Tests\BaseTestCase;


class ProductTest extends BaseTestCase
{

    public function testStoreProduct(): void
    {
        $response = $this->request('POST', '/products', json_encode($product = ['name' => 'hakona matata', 'price' => 400]));
        $response = $response['body'];

        unset($response['id']);

        $this->assertSame($product, $response);
    }
}