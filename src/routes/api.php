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

// OpenAPI spec (public)
if ($uri === '/openapi.yaml') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    $file = __DIR__ . '/../../openapi.yaml';
    if (file_exists($file)) {
        header('Content-Type: text/yaml; charset=utf-8');
        readfile($file);
        exit();
    }
}

// Root health check (public)
if ($uri === '/' || $uri === '') {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'text/html')) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Stock API</title>';
        echo '<style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#1a1a2e;color:#eee}';
        echo '.card{text-align:center;padding:2rem;border-radius:12px;background:#16213e;box-shadow:0 4px 20px rgba(0,0,0,.3)}';
        echo 'h1{color:#0f3460;margin:0 0 .5rem}.badge{display:inline-block;padding:4px 12px;border-radius:20px;background:#e94560;color:#fff;font-size:12px;margin:4px}';
        echo '.status{color:#4ecca3;font-size:14px}.endpoints{text-align:left;margin-top:1rem;font-size:13px;color:#aaa}';
        echo 'code{background:#0f3460;padding:2px 6px;border-radius:4px;color:#4ecca3}</style></head><body>';
        echo '<div class="card"><h1>Stock API</h1>';
        echo '<div><span class="badge">PHP 8.2</span><span class="badge">MySQL 8.0</span><span class="badge">Docker</span></div>';
        echo '<p class="status">✅ Running — v1.0.0</p>';
        echo '<div class="endpoints">';
        echo '<p><code>POST /auth/login</code> — Autenticação</p>';
        echo '<p><code>GET /products</code> — Produtos</p>';
        echo '<p><code>POST /sales</code> — Vendas</p>';
        echo '<p><code>GET /reports/low-stock</code> — Relatórios</p>';
        echo '</div></div></body></html>';
        exit();
    }
    echo json_encode(['message' => 'Stock API is running', 'version' => '1.0.0']);
}
