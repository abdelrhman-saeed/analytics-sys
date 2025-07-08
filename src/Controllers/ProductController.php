<?php

namespace AnalyticsSystem\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use AnalyticsSystem\DTO\ProductDTO;


class ProductController extends BaseController
{

    public function index(): JsonResponse
    {
        return new JsonResponse([
            'products' => $this->pdo
                ->query("SELECT * FROM products")
                ->fetchAll(\PDO::FETCH_ASSOC)
        ]);
    }

    public function show(int $id)
    {

        ($stmt = $this->pdo->prepare("SELECT * from products WHERE id = ?"))
            ->execute([$id]);

        return ($product = $stmt->fetch(\PDO::FETCH_ASSOC))
            ? new JsonResponse($product)
            : new Response(status: 404);
    }

    public function save()
    {
        $productDTO = new ProductDTO(json_decode($this->request->getContent(), true) ?? []);

        if (! empty($productDTO->getErrors())) {
            return new JsonResponse($productDTO->getErrors(), 403);
        }

        $data = $productDTO->getValidated();

        $this->pdo
            ->prepare("INSERT INTO products (name, price) VALUES (?, ?)")
            ->execute([$data['name'], $data['price']]);

        return new JsonResponse($data + ['id' => $this->pdo->lastInsertId()]);
    }

    public function update(int $id)
    {
        $productDTO = new ProductDTO(json_decode($this->request->getContent(), true) ?? []);

        if (! empty($productDTO->getErrors())) {
            return new JsonResponse($productDTO->getErrors(), 403);
        }

        $data   = $productDTO->getValidated();
        $query  = "UPDATE products SET ";

        foreach (array_keys($data) as $column) {
            $query .= "{$column} = ?,";
        }

        $query = rtrim($query, ',') . " WHERE id = ?";
        $data['id'] = $id;

        $this->pdo
            ->prepare($query)
            ->execute([...array_values($data)]);

        return new JsonResponse(['message' => 'product updated!']);
    }

    public function delete(int $id)
    {
        $res = $this->pdo
            ->prepare("DELETE FROM products WHERE id = ?")
            ->execute([$id]);

        return $res
            ? new Response(status: 410)
            : new JsonResponse(['error' => 'Server error!'], 500);
    }
}
