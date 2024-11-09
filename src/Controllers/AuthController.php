<?php
namespace App\Controllers;

use App\Auth\AuthManager;
use Exception;

class AuthController 
{
    private $auth;
    private $view;
    
    public function __construct(AuthManager $auth) 
    {
        $this->auth = $auth;
        $this->view = new \App\View();
    }
    
    public function login() 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'] ?? '';
                
                $user = $this->auth->login($email, $password);
                
                $_SESSION['user'] = $user;
                $_SESSION['last_activity'] = time();
                
                header('Location: ?route=dashboard');
                exit;
                
            } catch (Exception $e) {
                $this->view->setError($e->getMessage());
            }
        }
        
        $this->view->render('auth/login');
    }
    
    public function logout() 
    {
        $this->auth->logout();
        header('Location: ?route=login');
        exit;
    }
    
    public function forgotPassword() 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                
                // Gerar token de reset
                $token = bin2hex(random_bytes(32));
                
                // Salvar token no banco
                $stmt = $this->db->prepare("
                    INSERT INTO password_resets (email, token, created_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$email, $token]);
                
                // Enviar email
                $resetLink = $this->config['app']['url'] . "?route=reset-password&token=" . $token;
                $this->sendResetEmail($email, $resetLink);
                
                $this->view->setSuccess('Email de recuperação enviado com sucesso');
                
            } catch (Exception $e) {
                $this->view->setError($e->getMessage());
            }
        }
        
        $this->view->render('auth/forgot-password');
    }
    
    public function resetPassword() 
    {
        $token = $_GET['token'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['password_confirm'] ?? '';
                
                if ($password !== $confirm) {
                    throw new Exception('As senhas não conferem');
                }
                
                // Verificar token
                $stmt = $this->db->prepare("
                    SELECT email FROM password_resets 
                    WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    AND used = 0
                ");
                $stmt->execute([$token]);
                $reset = $stmt->fetch();
                
                if (!$reset) {
                    throw new Exception('Token inválido ou expirado');
                }
                
                // Atualizar senha
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET password_hash = ? 
                    WHERE email = ?
                ");
                $stmt->execute([
                    password_hash($password, PASSWORD_DEFAULT),
                    $reset['email']
                ]);
                
                // Marcar token como usado
                $stmt = $this->db->prepare("
                    UPDATE password_resets 
                    SET used = 1 
                    WHERE token = ?
                ");
                $stmt->execute([$token]);
                
                $this->view->setSuccess('Senha redefinida com sucesso');
                
            } catch (Exception $e) {
                $this->view->setError($e->getMessage());
            }
        }
        
        $this->view->render('auth/reset-password');
    }
} 