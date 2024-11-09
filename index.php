<?php
/**
 * Marketplace
 * 
 * @package     Marketplace
 * @author      Seu Nome
 * @copyright   2024 Seu Nome
 * @license     MIT
 * @version     1.0.0
 */

// Definir constantes
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/src');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Verificar versão do PHP
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    die('É necessário PHP 8.1 ou superior para executar esta aplicação.');
}

// Verificar instalação
if (!file_exists('storage/installed.json')) {
    require 'install/index.php';
    exit;
}

// Redirecionar para public
header('Location: public/');
exit;