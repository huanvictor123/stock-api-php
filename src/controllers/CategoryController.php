<?php

namespace Controllers;

use Models\Category;
use Helpers\Response;
use Helpers\Validator;

class CategoryController
{
    private Category $model;

    public function __construct()
    {
        $this->model = new Category();
    }

    public function index(): void
    {
        $categories = $this->model->findAll();
        Response::success($categories);
    }

    public function show(int $id): void
    {
        $category = $this->model->findById($id);

        if (!$category) {
            Response::notFound("Category with id {$id} not found");
        }

        Response::success($category);
    }

    public function store(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($body);
        $v->required('name')->string('name', 100);

        if ($v->fails()) {
            Response::error('Validation failed', 422, $v->errors());
        }

        $id       = $this->model->create($body);
        $category = $this->model->findById($id);
        Response::created($category, 'Category created successfully');
    }

    public function destroy(int $id): void
    {
        if (!$this->model->findById($id)) {
            Response::notFound("Category with id {$id} not found");
        }

        if ($this->model->hasProducts($id)) {
            Response::error(
                "Cannot delete category: it has associated products",
                409
            );
        }

        $this->model->delete($id);
        Response::success(null, 'Category deleted successfully');
    }
}
