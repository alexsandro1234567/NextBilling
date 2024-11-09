<?php
namespace App\Models;

use PDO;
use Exception;

class Product 
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
                INSERT INTO products (
                    code,
                    name,
                    description,
                    category_id,
                    brand_id,
                    unit,
                    cost_price,
                    sale_price,
                    min_stock,
                    max_stock,
                    location,
                    barcode,
                    active,
                    created_by,
                    created_at
                ) VALUES (
                    :code,
                    :name,
                    :description,
                    :category_id,
                    :brand_id,
                    :unit,
                    :cost_price,
                    :sale_price,
                    :min_stock,
                    :max_stock,
                    :location,
                    :barcode,
                    :active,
                    :created_by,
                    NOW()
                )
            ");
            
            $stmt->execute([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'],
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'],
                'unit' => $data['unit'],
                'cost_price' => $this->formatMoney($data['cost_price']),
                'sale_price' => $this->formatMoney($data['sale_price']),
                'min_stock' => $data['min_stock'],
                'max_stock' => $data['max_stock'],
                'location' => $data['location'],
                'barcode' => $data['barcode'],
                'active' => $data['active'] ?? 1,
                'created_by' => $_SESSION['user']['id']
            ]);
            
            $productId = $this->db->lastInsertId();
            
            // Criar registro inicial no estoque
            $stockModel = new Stock($this->db);
            $stockModel->create([
                'product_id' => $productId,
                'quantity' => 0,
                'type' => 'initial',
                'notes' => 'Saldo inicial'
            ]);
            
            return $productId;
            
        } catch (Exception $e) {
            throw new Exception('Erro ao criar produto: ' . $e->getMessage());
        }
    }
    
    public function update(int $id, array $data): bool 
    {
        try {
            // Buscar dados antigos para log
            $oldData = $this->find($id);
            
            $stmt = $this->db->prepare("
                UPDATE products SET
                    code = :code,
                    name = :name,
                    description = :description,
                    category_id = :category_id,
                    brand_id = :brand_id,
                    unit = :unit,
                    cost_price = :cost_price,
                    sale_price = :sale_price,
                    min_stock = :min_stock,
                    max_stock = :max_stock,
                    location = :location,
                    barcode = :barcode,
                    active = :active,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $id,
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'],
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'],
                'unit' => $data['unit'],
                'cost_price' => $this->formatMoney($data['cost_price']),
                'sale_price' => $this->formatMoney($data['sale_price']),
                'min_stock' => $data['min_stock'],
                'max_stock' => $data['max_stock'],
                'location' => $data['location'],
                'barcode' => $data['barcode'],
                'active' => $data['active'] ?? 1,
                'updated_by' => $_SESSION['user']['id']
            ]);
            
            // Registrar log de alteração
            if ($result) {
                $this->logChanges('update', $id, $oldData, $data);
            }
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar produto: ' . $e->getMessage());
        }
    }
    
    public function delete(int $id): bool 
    {
        try {
            // Buscar dados antigos para log
            $oldData = $this->find($id);
            
            $stmt = $this->db->prepare("
                UPDATE products 
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
            throw new Exception('Erro ao deletar produto: ' . $e->getMessage());
        }
    }
    
    public function find(int $id): ?array 
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                c.name as category_name,
                b.name as brand_name,
                uc.name as created_by_name,
                uu.name as updated_by_name,
                ud.name as deleted_by_name,
                COALESCE(s.quantity, 0) as current_stock
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN users uc ON uc.id = p.created_by
            LEFT JOIN users uu ON uu.id = p.updated_by
            LEFT JOIN users ud ON ud.id = p.deleted_by
            LEFT JOIN (
                SELECT product_id, SUM(quantity) as quantity
                FROM stock_movements
                GROUP BY product_id
            ) s ON s.product_id = p.id
            WHERE p.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function findAll(array $filters = [], int $page = 1, int $limit = 10): array 
    {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (!empty($filters['search'])) {
                $where[] = "(
                    p.code LIKE :search OR 
                    p.name LIKE :search OR 
                    p.barcode LIKE :search
                )";
                $params['search'] = "%{$filters['search']}%";
            }
            
            if (!empty($filters['category_id'])) {
                $where[] = "p.category_id = :category_id";
                $params['category_id'] = $filters['category_id'];
            }
            
            if (!empty($filters['brand_id'])) {
                $where[] = "p.brand_id = :brand_id";
                $params['brand_id'] = $filters['brand_id'];
            }
            
            if (isset($filters['active'])) {
                $where[] = "p.active = :active";
                $params['active'] = $filters['active'];
            }
            
            if (isset($filters['stock_status'])) {
                switch ($filters['stock_status']) {
                    case 'low':
                        $where[] = "COALESCE(s.quantity, 0) <= p.min_stock";
                        break;
                    case 'normal':
                        $where[] = "COALESCE(s.quantity, 0) > p.min_stock AND COALESCE(s.quantity, 0) < p.max_stock";
                        break;
                    case 'high':
                        $where[] = "COALESCE(s.quantity, 0) >= p.max_stock";
                        break;
                }
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Calcular offset para paginação
            $offset = ($page - 1) * $limit;
            
            // Buscar produtos
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    c.name as category_name,
                    b.name as brand_name,
                    COALESCE(s.quantity, 0) as current_stock
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN brands b ON b.id = p.brand_id
                LEFT JOIN (
                    SELECT product_id, SUM(quantity) as quantity
                    FROM stock_movements
                    GROUP BY product_id
                ) s ON s.product_id = p.id
                WHERE {$whereClause}
                ORDER BY p.name ASC
                LIMIT :limit OFFSET :offset
            ");
            
            // Bind dos parâmetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            // Contar total para paginação
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM products p
                LEFT JOIN (
                    SELECT product_id, SUM(quantity) as quantity
                    FROM stock_movements
                    GROUP BY product_id
                ) s ON s.product_id = p.id
                WHERE {$whereClause}
            ");
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $total = $stmt->fetchColumn();
            
            return [
                'data' => $products,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar produtos: ' . $e->getMessage());
        }
    }
    
    private function formatMoney(?string $value): ?float 
    {
        if (empty($value)) {
            return null;
        }
        return (float) str_replace(['.', ','], ['', '.'], $value);
    }
    
    private function logChanges(string $action, int $id, array $oldData, ?array $newData = null): void 
    {
        $log = new Log($this->db);
        
        $description = match($action) {
            'create' => "Produto {$newData['name']} criado",
            'update' => "Produto {$oldData['name']} atualizado",
            'delete' => "Produto {$oldData['name']} excluído",
            default => "Ação {$action} realizada no produto {$oldData['name']}"
        };
        
        $log->create([
            'module' => 'products',
            'action' => $action,
            'description' => $description,
            'entity_type' => 'product',
            'entity_id' => $id,
            'old_data' => $oldData,
            'new_data' => $newData
        ]);
    }
} 