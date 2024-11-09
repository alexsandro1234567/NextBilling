<?php
namespace App\Auth;

use PDO;
use Exception;

class AuthManager 
{
    private $db;
    private $config;
    
    public function __construct(PDO $db, array $config) 
    {
        $this->db = $db;
        $this->config = $config;
    }
    
    public function login(string $email, string $password): array 
    {
        try {
            // Buscar usuário
            $stmt = $this->db->prepare("
                SELECT id, name, email, password_hash, role, active 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !$user['active']) {
                throw new Exception('Credenciais inválidas ou usuário inativo');
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                throw new Exception('Senha incorreta');
            }
            
            // Atualizar último login
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_login = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
            
            // Registrar log de login
            $this->logLogin($user['id']);
            
            // Retornar dados do usuário (exceto senha)
            unset($user['password_hash']);
            return $user;
            
        } catch (Exception $e) {
            throw new Exception('Erro no login: ' . $e->getMessage());
        }
    }
    
    public function logout(): void 
    {
        if (isset($_SESSION['user'])) {
            $this->logLogout($_SESSION['user']['id']);
            session_destroy();
        }
    }
    
    public function checkPermission(string $permission): bool 
    {
        if (!isset($_SESSION['user'])) {
            return false;
        }
        
        // Administradores têm todas as permissões
        if ($_SESSION['user']['role'] === 'admin') {
            return true;
        }
        
        // Verificar permissões específicas do papel
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.name = ?
        ");
        $stmt->execute([$_SESSION['user']['role'], $permission]);
        return $stmt->fetchColumn() > 0;
    }
} 