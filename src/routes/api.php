<?php

use Controllers\ProductController;
use Controllers\CategoryController;
use Controllers\SaleController;
use Controllers\AuthController;
use Controllers\ReportController;
use Middleware\AuthMiddleware;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Auth (public)
if ($uri === '/auth/login' && $method === 'POST') {
    $controller = new AuthController();
    $controller->login();
}

// Products (protected)
if (preg_match('#^/products/?$#', $uri)) {
    AuthMiddleware::handle();
    $controller = new ProductController();
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    }
} elseif (preg_match('#^/products/(\d+)$#', $uri, $matches)) {
    AuthMiddleware::handle();
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

// Categories (protected)
if (preg_match('#^/categories/?$#', $uri)) {
    AuthMiddleware::handle();
    $controller = new CategoryController();
    if ($method === 'GET') {
        $controller->index();
    } elseif ($method === 'POST') {
        $controller->store();
    }
} elseif (preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    AuthMiddleware::handle();
    $controller = new CategoryController();
    $id = (int) $matches[1];
    if ($method === 'GET') {
        $controller->show($id);
    } elseif ($method === 'DELETE') {
        $controller->destroy($id);
    }
}

// Sales (protected)
if (preg_match('#^/sales/?$#', $uri)) {
    AuthMiddleware::handle();
    $controller = new SaleController();
    if ($method === 'POST') {
        $controller->store();
    }
} elseif (preg_match('#^/sales/(\d+)$#', $uri, $matches)) {
    AuthMiddleware::handle();
    $controller = new SaleController();
    $id = (int) $matches[1];
    if ($method === 'GET') {
        $controller->show($id);
    }
}

// Reports (protected)
if (preg_match('#^/reports/low-stock/?$#', $uri)) {
    AuthMiddleware::handle();
    $controller = new ReportController();
    if ($method === 'GET') {
        $controller->lowStock();
    }
} elseif (preg_match('#^/reports/sales-summary/?$#', $uri)) {
    AuthMiddleware::handle();
    $controller = new ReportController();
    if ($method === 'GET') {
        $controller->salesSummary();
    }
}

// Root health check (public)
if ($uri === '/' || $uri === '') {
    echo json_encode(['message' => 'Stock API is running', 'version' => '1.0.0']);
}
