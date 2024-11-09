<?php
namespace App\Models;

use PDO;
use Exception;

class Customer 
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
                INSERT INTO customers (
                    name,
                    type,
                    document,
                    email,
                    phone,
                    mobile,
                    address,
                    address_number,
                    complement,
                    neighborhood,
                    city,
                    state,
                    zipcode,
                    notes,
                    active,
                    created_by,
                    created_at
                ) VALUES (
                    :name,
                    :type,
                    :document,
                    :email,
                    :phone,
                    :mobile,
                    :address,
                    :address_number,
                    :complement,
                    :neighborhood,
                    :city,
                    :state,
                    :zipcode,
                    :notes,
                    :active,
                    :created_by,
                    NOW()
                )
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'type' => $data['type'],
                'document' => $data['document'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'mobile' => $data['mobile'],
                'address' => $data['address'],
                'address_number' => $data['address_number'],
                'complement' => $data['complement'],
                'neighborhood' => $data['neighborhood'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zipcode' => $data['zipcode'],
                'notes' => $data['notes'],
                'active' => $data['active'] ?? 1,
                'created_by' => $_SESSION['user']['id']
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            throw new Exception('Erro ao criar cliente: ' . $e->getMessage());
        }
    }
    
    public function update(int $id, array $data): bool 
    {
        try {
            // Buscar dados antigos para log
            $oldData = $this->find($id);
            
            $stmt = $this->db->prepare("
                UPDATE customers SET
                    name = :name,
                    type = :type,
                    document = :document,
                    email = :email,
                    phone = :phone,
                    mobile = :mobile,
                    address = :address,
                    address_number = :address_number,
                    complement = :complement,
                    neighborhood = :neighborhood,
                    city = :city,
                    state = :state,
                    zipcode = :zipcode,
                    notes = :notes,
                    active = :active,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'type' => $data['type'],
                'document' => $data['document'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'mobile' => $data['mobile'],
                'address' => $data['address'],
                'address_number' => $data['address_number'],
                'complement' => $data['complement'],
                'neighborhood' => $data['neighborhood'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zipcode' => $data['zipcode'],
                'notes' => $data['notes'],
                'active' => $data['active'] ?? 1,
                'updated_by' => $_SESSION['user']['id']
            ]);
            
            // Registrar log de alteração
            if ($result) {
                $this->logChanges('update', $id, $oldData, $data);
            }
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar cliente: ' . $e->getMessage());
        }
    }
    
    public function delete(int $id): bool 
    {
        try {
            // Buscar dados antigos para log
            $oldData = $this->find($id);
            
            $stmt = $this->db->prepare("
                UPDATE customers 
                SET 
                    active = 0,
                    deleted_by = :deleted_by,
                    deleted_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $id,
                'deleted_by' => $_SESSION['user']['id']
            ]);
            
            // Registrar log de exclusão
            if ($result) {
                $this->logChanges('delete', $id, $oldData);
            }
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar cliente: ' . $e->getMessage());
        }
    }
    
    public function find(int $id): ?array 
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                uc.name as created_by_name,
                uu.name as updated_by_name,
                ud.name as deleted_by_name
            FROM customers c
            LEFT JOIN users uc ON uc.id = c.created_by
            LEFT JOIN users uu ON uu.id = c.updated_by
            LEFT JOIN users ud ON ud.id = c.deleted_by
            WHERE c.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function findAll(array $filters = [], int $page = 1, int $limit = 10): array 
    {
        try {
            $where = ['1=1'];
            $params = [];
            
            // Aplicar filtros
            if (!empty($filters['search'])) {
                $where[] = "(
                    name LIKE :search OR 
                    document LIKE :search OR 
                    email LIKE :search OR 
                    phone LIKE :search OR 
                    mobile LIKE :search
                )";
                $params['search'] = "%{$filters['search']}%";
            }
            
            if (!empty($filters['type'])) {
                $where[] = "type = :type";
                $params['type'] = $filters['type'];
            }
            
            if (isset($filters['active'])) {
                $where[] = "active = :active";
                $params['active'] = $filters['active'];
            }
            
            if (!empty($filters['city'])) {
                $where[] = "city = :city";
                $params['city'] = $filters['city'];
            }
            
            if (!empty($filters['state'])) {
                $where[] = "state = :state";
                $params['state'] = $filters['state'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Calcular offset para paginação
            $offset = ($page - 1) * $limit;
            
            // Buscar clientes
            $stmt = $this->db->prepare("
                SELECT 
                    c.*,
                    uc.name as created_by_name
                FROM customers c
                LEFT JOIN users uc ON uc.id = c.created_by
                WHERE {$whereClause}
                ORDER BY c.name ASC
                LIMIT :limit OFFSET :offset
            ");
            
            // Bind dos parâmetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $customers = $stmt->fetchAll();
            
            // Contar total para paginação
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM customers c
                WHERE {$whereClause}
            ");
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $total = $stmt->fetchColumn();
            
            return [
                'data' => $customers,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar clientes: ' . $e->getMessage());
        }
    }
    
    public function getCities(): array 
    {
        $stmt = $this->db->query("
            SELECT DISTINCT city 
            FROM customers 
            WHERE city IS NOT NULL 
            ORDER BY city
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getStates(): array 
    {
        $stmt = $this->db->query("
            SELECT DISTINCT state 
            FROM customers 
            WHERE state IS NOT NULL 
            ORDER BY state
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function logChanges(string $action, int $id, array $oldData, ?array $newData = null): void 
    {
        $log = new Log($this->db);
        
        $description = match($action) {
            'create' => "Cliente {$newData['name']} criado",
            'update' => "Cliente {$oldData['name']} atualizado",
            'delete' => "Cliente {$oldData['name']} excluído",
            default => "Ação {$action} realizada no cliente {$oldData['name']}"
        };
        
        $log->create([
            'module' => 'customers',
            'action' => $action,
            'description' => $description,
            'entity_type' => 'customer',
            'entity_id' => $id,
            'old_data' => $oldData,
            'new_data' => $newData
        ]);
    }
} 