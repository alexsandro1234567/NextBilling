<?php
namespace App\Models;

use PDO;
use Exception;

class Log 
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
                INSERT INTO system_logs (
                    user_id, 
                    module,
                    action,
                    description,
                    entity_type,
                    entity_id,
                    old_data,
                    new_data,
                    ip_address,
                    user_agent,
                    created_at
                ) VALUES (
                    :user_id,
                    :module,
                    :action,
                    :description,
                    :entity_type,
                    :entity_id,
                    :old_data,
                    :new_data,
                    :ip_address,
                    :user_agent,
                    NOW()
                )
            ");
            
            $stmt->execute([
                'user_id' => $data['user_id'] ?? $_SESSION['user']['id'] ?? null,
                'module' => $data['module'],
                'action' => $data['action'],
                'description' => $data['description'],
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id' => $data['entity_id'] ?? null,
                'old_data' => isset($data['old_data']) ? json_encode($data['old_data']) : null,
                'new_data' => isset($data['new_data']) ? json_encode($data['new_data']) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            throw new Exception('Erro ao criar log: ' . $e->getMessage());
        }
    }
    
    public function findAll(array $filters = [], int $page = 1, int $limit = 50): array 
    {
        try {
            $where = [];
            $params = [];
            
            // Filtros
            if (!empty($filters['user_id'])) {
                $where[] = "l.user_id = :user_id";
                $params['user_id'] = $filters['user_id'];
            }
            
            if (!empty($filters['module'])) {
                $where[] = "l.module = :module";
                $params['module'] = $filters['module'];
            }
            
            if (!empty($filters['action'])) {
                $where[] = "l.action = :action";
                $params['action'] = $filters['action'];
            }
            
            if (!empty($filters['entity_type'])) {
                $where[] = "l.entity_type = :entity_type";
                $params['entity_type'] = $filters['entity_type'];
            }
            
            if (!empty($filters['date_start'])) {
                $where[] = "l.created_at >= :date_start";
                $params['date_start'] = $filters['date_start'] . ' 00:00:00';
            }
            
            if (!empty($filters['date_end'])) {
                $where[] = "l.created_at <= :date_end";
                $params['date_end'] = $filters['date_end'] . ' 23:59:59';
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Calcular offset para paginação
            $offset = ($page - 1) * $limit;
            
            // Buscar logs
            $sql = "
                SELECT 
                    l.*,
                    u.name as user_name,
                    u.email as user_email
                FROM system_logs l
                LEFT JOIN users u ON u.id = l.user_id
                $whereClause
                ORDER BY l.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind dos parâmetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $logs = $stmt->fetchAll();
            
            // Contar total para paginação
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM system_logs l
                $whereClause
            ");
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $total = $stmt->fetchColumn();
            
            return [
                'data' => $logs,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar logs: ' . $e->getMessage());
        }
    }
    
    public function getModules(): array 
    {
        $stmt = $this->db->query("
            SELECT DISTINCT module 
            FROM system_logs 
            ORDER BY module
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getActions(): array 
    {
        $stmt = $this->db->query("
            SELECT DISTINCT action 
            FROM system_logs 
            ORDER BY action
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getEntityTypes(): array 
    {
        $stmt = $this->db->query("
            SELECT DISTINCT entity_type 
            FROM system_logs 
            WHERE entity_type IS NOT NULL 
            ORDER BY entity_type
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} 