<?php
// Carregar arquivo da raiz
require_once dirname(__DIR__) . '/index.php';

// Carregar autoloader
require BASE_PATH . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Inicializar aplicação
$app = new \App\Application([
    'debug' => $_ENV['APP_DEBUG'] === 'true',
    'url' => $_ENV['APP_URL'],
    'timezone' => 'America/Sao_Paulo',
    'database' => [
        'driver' => $_ENV['DB_CONNECTION'],
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ]
]);

// Registrar handlers de erro
if ($app->config->get('debug')) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

// Inicializar sessão
session_start();

// Carregar template ativo
$templateManager = new \App\Templates\TemplateManager($app);
$activeTemplate = $templateManager->getActiveTemplate();

// Carregar extensões ativas
$extensionManager = new \App\Extensions\ExtensionManager($app);
$extensionManager->loadExtensions();

// Definir rotas
$router = new \App\Router($app);

// Rotas públicas
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@doRegister');
$router->get('/logout', 'AuthController@logout');

// Rotas do marketplace
$router->get('/marketplace', 'MarketplaceController@index');
$router->get('/marketplace/category/{slug}', 'MarketplaceController@category');
$router->get('/marketplace/search', 'MarketplaceController@search');
$router->get('/marketplace/item/{slug}', 'MarketplaceController@item');

// Rotas do desenvolvedor
$router->group('/developer', function($router) {
    $router->get('/', 'Developer\DashboardController@index');
    $router->get('/items', 'Developer\ItemController@index');
    $router->get('/items/create', 'Developer\ItemController@create');
    $router->post('/items/create', 'Developer\ItemController@store');
    $router->get('/items/{id}/edit', 'Developer\ItemController@edit');
    $router->post('/items/{id}/edit', 'Developer\ItemController@update');
    $router->get('/sales', 'Developer\SaleController@index');
    $router->get('/earnings', 'Developer\EarningController@index');
});

// Rotas do admin
$router->group('/admin', function($router) {
    $router->get('/', 'Admin\DashboardController@index');
    $router->get('/marketplace', 'Admin\MarketplaceController@index');
    $router->get('/marketplace/review/{id}', 'Admin\MarketplaceController@review');
    $router->post('/marketplace/review/{id}', 'Admin\MarketplaceController@saveReview');
    $router->get('/marketplace/settings', 'Admin\MarketplaceController@settings');
    $router->post('/marketplace/settings', 'Admin\MarketplaceController@saveSettings');
    $router->get('/extensions', 'Admin\ExtensionController@index');
    $router->get('/templates', 'Admin\TemplateController@index');
    $router->get('/users', 'Admin\UserController@index');
    $router->get('/settings', 'Admin\SettingController@index');
});

// Middleware de autenticação
$router->before('GET|POST', '/admin/.*', function() use ($app) {
    if (!$app->auth->check() || !$app->auth->user()->isAdmin()) {
        header('Location: /login');
        exit;
    }
});

$router->before('GET|POST', '/developer/.*', function() use ($app) {
    if (!$app->auth->check() || !$app->auth->user()->isDeveloper()) {
        header('Location: /login');
        exit;
    }
});

// Executar rota
try {
    $router->run();
} catch (\Exception $e) {
    if ($app->config->get('debug')) {
        throw $e;
    }
    
    // Log do erro
    error_log($e->getMessage());
    
    // Redirecionar para página de erro
    header('Location: /error');
    exit;
} 