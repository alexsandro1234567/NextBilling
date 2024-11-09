<?php
namespace App\Auth;

class AuthMiddleware 
{
    private $auth;
    private $config;
    
    public function __construct(AuthManager $auth, array $config) 
    {
        $this->auth = $auth;
        $this->config = $config;
    }
    
    public function handle(): void 
    {
        // Verificar se é uma rota pública
        $currentRoute = $_GET['route'] ?? 'home';
        if (in_array($currentRoute, ['login', 'forgot-password', 'reset-password'])) {
            return;
        }
        
        // Verificar se usuário está logado
        if (!isset($_SESSION['user'])) {
            header('Location: ?route=login');
            exit;
        }
        
        // Verificar timeout da sessão
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > $this->config['security']['session_timeout'])) {
            $this->auth->logout();
            header('Location: ?route=login&expired=1');
            exit;
        }
        
        // Atualizar timestamp da última atividade
        $_SESSION['last_activity'] = time();
    }
} 