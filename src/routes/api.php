<?php

use Controllers\ProductController;
use Controllers\CategoryController;
use Controllers\SaleController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Products
if (preg_match('#^/products/?$#', $uri)) {
    $controller = new ProductController();
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    }
} elseif (preg_match('#^/products/(\d+)$#', $uri, $matches)) {
    $controller = new ProductController();
    $id = (int) $matches[1];
    if ($method === 'GET') {
        $controller->show($id);
    } elseif ($method === 'PUT') {
        $controller->update($id);
    } elseif ($method === 'DELETE') {
        $controller->destroy($id);
    }
}

// Categories
if (preg_match('#^/categories/?$#', $uri)) {
    $controller = new CategoryController();
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    }
} elseif (preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    $controller = new CategoryController();
    $id = (int) $matches[1];
    if ($method === 'GET') {
        $controller->show($id);
    } elseif ($method === 'DELETE') {
        $controller->destroy($id);
    }
}

// Sales
if (preg_match('#^/sales/?$#', $uri)) {
    $controller = new SaleController();
    if ($method === 'POST') {
        $controller->store();
    }
} elseif (preg_match('#^/sales/(\d+)$#', $uri, $matches)) {
    $controller = new SaleController();
    $id = (int) $matches[1];
    if ($method === 'GET') {
        $controller->show($id);
    }
}

// Root health check
if ($uri === '/' || $uri === '') {
    echo json_encode(['message' => 'Stock API is running', 'version' => '1.0.0']);
}
