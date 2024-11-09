<?php
namespace App\Models;

use PDO;
use Exception;

class User 
{
    private $db;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }
    
    public function create(array $data): int 
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    name, email, password_hash, role, active, created_at
                ) VALUES (
                    :name, :email, :password, :role, :active, NOW()
                )
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'] ?? 'user',
                'active' => $data['active'] ?? 1
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            throw new Exception('Erro ao criar usuário: ' . $e->getMessage());
        }
    }
    
    public function update(int $id, array $data): bool 
    {
        try {
            $fields = [];
            $params = ['id' => $id];
            
            // Construir campos dinâmicamente
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . 
                   ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }
    
    public function delete(int $id): bool 
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar usuário: ' . $e->getMessage());
        }
    }
    
    public function find(int $id): ?array 
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, active, last_login, created_at 
            FROM users WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function findAll(array $filters = [], int $page = 1, int $limit = 10): array 
    {
        try {
            $where = [];
            $params = [];
            
            // Aplicar filtros
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE :search OR email LIKE :search)";
                $params['search'] = "%{$filters['search']}%";
            }
            
            if (isset($filters['role'])) {
                $where[] = "role = :role";
                $params['role'] = $filters['role'];
            }
            
            if (isset($filters['active'])) {
                $where[] = "active = :active";
                $params['active'] = $filters['active'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Calcular offset para paginação
            $offset = ($page - 1) * $limit;
            
            // Buscar usuários
            $stmt = $this->db->prepare("
                SELECT id, name, email, role, active, last_login, created_at 
                FROM users 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset
            ");
            
            // Bind dos parâmetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            // Contar total para paginação
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM users $whereClause
            ");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $total = $stmt->fetchColumn();
            
            return [
                'data' => $users,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar usuários: ' . $e->getMessage());
        }
    }
} 