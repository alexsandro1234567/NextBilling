<?php
session_start();

// Carregar configurações
$config = require dirname(dirname(__DIR__)) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Conectar ao banco usando as configurações corretas
        $dsn = "mysql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']}";
        $pdo = new PDO(
            $dsn, 
            $config['db']['user'], 
            $config['db']['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Buscar usuário (usando a coluna 'password' em vez de 'password_hash')
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE email = ? 
            AND role = 'admin' 
            AND status = 'active' 
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug
        error_log("Tentativa de login para: " . $email);
        error_log("Usuário encontrado: " . ($user ? 'Sim' : 'Não'));
        
        // Verificar se o usuário existe e a senha está correta
        if ($user && password_verify($password, $user['password'])) { // Alterado de password_hash para password
            // Login bem sucedido
            $_SESSION['admin_user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            // Atualizar último login
            $stmt = $pdo->prepare("
                UPDATE users 
                SET updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
            
            // Redirecionar para dashboard
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Login falhou
        $_SESSION['login_error'] = 'Email ou senha inválidos';
        error_log("Login falhou - senha inválida");
        
    } catch (PDOException $e) {
        error_log("Erro de banco de dados: " . $e->getMessage());
        $_SESSION['login_error'] = 'Erro ao conectar ao banco de dados';
    }
    
    header('Location: index.php?page=login');
    exit;
}

// Se não for POST, redirecionar para login
header('Location: index.php?page=login');
exit;
header('Location: /public/admin/index.php?page=login');
exit; 