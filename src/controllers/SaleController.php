<?php

namespace Controllers;

use Models\Sale;
use Helpers\Response;
use Helpers\Validator;

class SaleController
{
    private Sale $model;

    public function __construct()
    {
        $this->model = new Sale();
    }

    public function store(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($body);
        $v->required('customer_name')->string('customer_name', 200);

        if (empty($body['items']) || !is_array($body['items'])) {
            Response::error('At least one item is required', 422);
        }

        foreach ($body['items'] as $index => $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                Response::error(
                    "Item {$index}: product_id and quantity are required",
                    422
                );
            }
            if ((int)$item['quantity'] < 1) {
                Response::error("Item {$index}: quantity must be at least 1", 422);
            }
        }

        if ($v->fails()) {
            Response::error('Validation failed', 422, $v->errors());
        }

        try {
            $saleId = $this->model->create(
                ['customer_name' => $body['customer_name'],
                 'customer_email' => $body['customer_email'] ?? null],
                $body['items']
            );

            $sale = $this->model->findById($saleId);
            Response::created($sale, 'Sale created successfully');

        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            Response::error($e->getMessage(), $code);
        }
    }

    public function show(int $id): void
    {
        $sale = $this->model->findById($id);
        if (!$sale) {
            Response::notFound("Sale with id {$id} not found");
        }
        Response::success($sale);
    }
}
