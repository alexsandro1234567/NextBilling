<?php
session_start();

// Carregar configurações
require dirname(dirname(__DIR__)) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Conectar ao banco
        $dsn = "mysql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}";
        $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar usuário
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login bem sucedido
            $_SESSION['admin_user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];
            
            // Redirecionar para dashboard
            header('Location: /public/admin/index.php?page=dashboard');
            exit;
        }
        
        // Login falhou
        $_SESSION['login_error'] = 'Email ou senha inválidos';
        header('Location: /public/admin/index.php?page=login');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Erro ao conectar ao banco de dados';
        header('Location: /public/admin/index.php?page=login');
        exit;
    }
}

// Se não for POST, redirecionar para login
header('Location: /public/admin/index.php?page=login');
exit; 