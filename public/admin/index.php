<?php
session_start();

// Definir caminho base
define('BASE_PATH', dirname(dirname(__DIR__)));

// Carregar configurações
$config = require BASE_PATH . '/config/config.php';

// Verificar se está logado
if (!isset($_SESSION['admin_user'])) {
    // Se não estiver logado e não estiver na página de login, redirecionar
    if (!isset($_GET['page']) || $_GET['page'] !== 'login') {
        header('Location: /public/admin/index.php?page=login');
        exit;
    }
}

// Definir página atual
$page = $_GET['page'] ?? 'dashboard';

// Layout padrão
$layout = 'layouts/app.php';

// Rotas do admin
$routes = [
    'login' => [
        'view' => __DIR__ . '/views/auth/login.php',
        'layout' => 'layouts/auth.php'
    ],
    'dashboard' => [
        'view' => __DIR__ . '/views/dashboard/index.php'
    ],
    'marketplace' => [
        'view' => __DIR__ . '/views/marketplace/index.php'
    ],
    'settings' => [
        'view' => __DIR__ . '/views/settings/index.php'
    ]
];

// Verificar se a página existe
if (!isset($routes[$page])) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/views/errors/404.php';
    exit;
}

// Usar layout específico da rota se definido
if (isset($routes[$page]['layout'])) {
    $layout = $routes[$page]['layout'];
}

// Incluir o layout
include __DIR__ . "/views/{$layout}";