<?php

namespace AnalyticsSystem\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use AnalyticsSystem\DTO\ProductDTO;


class ProductController extends BaseController
{

    public function index(): JsonResponse
    {
        return (new JsonResponse([
            'products' => $this->pdo
                ->query("SELECT * FROM products")
                ->fetchAll(\PDO::FETCH_ASSOC)
        ]))->send();
    }

    public function show(int $id)
    {
        ($stmt = $this->pdo->prepare("SELECT * from products WHERE id = ?"))
            ->execute([$id]);

        return ($product = $stmt->fetch(\PDO::FETCH_ASSOC))
            ? (new JsonResponse($product))->send()
            : (new JsonResponse(['error' => 'product not found!'], 404))->send();
    }

    public function save()
    {
        $productDTO = new ProductDTO(json_decode($this->request->getContent(), true) ?? []);

        if (! empty($productDTO->getErrors())) {
            return (new JsonResponse($productDTO->getErrors(), 403))->send();
        }

        $data = $productDTO->getValidated();

        $this->pdo
            ->prepare("INSERT INTO products (name, price) VALUES (?, ?)")
            ->execute([$data['name'], $data['price']]);

        return (new JsonResponse($data + ['id' => $this->pdo->lastInsertId()]))->send();
    }

    public function update(int $id)
    {
        $productDTO = new ProductDTO(json_decode($this->request->getContent(), true) ?? []);

        if (! empty($productDTO->getErrors())) {
            return (new JsonResponse($productDTO->getErrors(), 403))->send();
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

        return (new JsonResponse(['message' => 'product updated!']))->send();
    }

    public function delete(int $id)
    {
        $res = $this->pdo
            ->prepare("DELETE FROM products WHERE id = ?")
            ->execute([$id]);

        return $res
            ? (new JsonResponse(['message'  => 'product deleted successfully'], 410))->send()
            : (new JsonResponse(['error'    => 'Server error!'], 500))->send();
    }
}
