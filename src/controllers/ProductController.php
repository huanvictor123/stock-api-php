<?php

namespace Controllers;

use Models\Product;
use Helpers\Response;
use Helpers\Validator;

class ProductController
{
    private Product $model;

    public function __construct()
    {
        $this->model = new Product();
    }

    public function index(): void
    {
        $products = $this->model->findAll();
        Response::success($products);
    }

    public function show(int $id): void
    {
        $product = $this->model->findById($id);

        if (!$product) {
            Response::notFound("Product with id {$id} not found");
        }

        Response::success($product);
    }

    public function store(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($body);
        $v->required('name')->string('name', 200)
          ->required('category_id')->integer('category_id', 1)
          ->required('supplier_id')->integer('supplier_id', 1)
          ->required('unit_price')->numeric('unit_price', 0)
          ->integer('stock_quantity', 0);

        if ($v->fails()) {
            Response::error('Validation failed', 422, $v->errors());
        }

        $id      = $this->model->create($body);
        $product = $this->model->findById($id);
        Response::created($product, 'Product created successfully');
    }

    public function update(int $id): void
    {
        if (!$this->model->findById($id)) {
            Response::notFound("Product with id {$id} not found");
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($body);
        $v->required('name')->required('category_id')
          ->required('supplier_id')->required('unit_price')
          ->numeric('unit_price', 0)->integer('stock_quantity', 0);

        if ($v->fails()) {
            Response::error('Validation failed', 422, $v->errors());
        }

        $this->model->update($id, $body);
        Response::success($this->model->findById($id), 'Product updated successfully');
    }

    public function destroy(int $id): void
    {
        if (!$this->model->findById($id)) {
            Response::notFound("Product with id {$id} not found");
        }

        $this->model->delete($id);
        Response::success(null, 'Product deleted successfully');
    }
}
