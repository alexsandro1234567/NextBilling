<?php
namespace App\Models;

use PDO;
use Exception;

class Sale 
{
    private $db;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }
    
    public function create(array $data): int 
    {
        try {
            $this->db->beginTransaction();
            
            // Gerar número sequencial da venda
            $number = $this->generateSaleNumber();
            
            // Inserir venda
            $stmt = $this->db->prepare("
                INSERT INTO sales (
                    number,
                    customer_id,
                    total,
                    discount,
                    final_total,
                    notes,
                    status,
                    payment_method,
                    payment_term,
                    created_by,
                    created_at
                ) VALUES (
                    :number,
                    :customer_id,
                    :total,
                    :discount,
                    :final_total,
                    :notes,
                    :status,
                    :payment_method,
                    :payment_term,
                    :created_by,
                    NOW()
                )
            ");
            
            $stmt->execute([
                'number' => $number,
                'customer_id' => $data['customer_id'],
                'total' => $this->formatMoney($data['total']),
                'discount' => $this->formatMoney($data['discount']),
                'final_total' => $this->formatMoney($data['final_total']),
                'notes' => $data['notes'],
                'status' => $data['status'] ?? 'pending',
                'payment_method' => $data['payment_method'],
                'payment_term' => $data['payment_term'],
                'created_by' => $_SESSION['user']['id']
            ]);
            
            $saleId = $this->db->lastInsertId();
            
            // Inserir itens da venda
            foreach ($data['items'] as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO sale_items (
                        sale_id,
                        product_id,
                        quantity,
                        price,
                        total
                    ) VALUES (
                        :sale_id,
                        :product_id,
                        :quantity,
                        :price,
                        :total
                    )
                ");
                
                $stmt->execute([
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $this->formatMoney($item['price']),
                    'total' => $this->formatMoney($item['total'])
                ]);
                
                // Atualizar estoque
                $stockModel = new Stock($this->db);
                $stockModel->create([
                    'product_id' => $item['product_id'],
                    'quantity' => -$item['quantity'],
                    'type' => 'sale',
                    'reference_id' => $saleId,
                    'notes' => "Venda #{$number}"
                ]);
            }
            
            // Criar contas a receber
            if ($data['payment_term'] > 0) {
                $accountModel = new Account($this->db);
                $accountModel->createReceivables($saleId, $data);
            }
            
            $this->db->commit();
            return $saleId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erro ao criar venda: ' . $e->getMessage());
        }
    }
    
    public function update(int $id, array $data): bool 
    {
        try {
            // Buscar dados antigos para log
            $oldData = $this->find($id);
            if ($oldData['status'] !== 'pending') {
                throw new Exception('Apenas vendas pendentes podem ser alteradas');
            }
            
            $this->db->beginTransaction();
            
            // Atualizar venda
            $stmt = $this->db->prepare("
                UPDATE sales SET
                    customer_id = :customer_id,
                    total = :total,
                    discount = :discount,
                    final_total = :final_total,
                    notes = :notes,
                    payment_method = :payment_method,
                    payment_term = :payment_term,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'customer_id' => $data['customer_id'],
                'total' => $this->formatMoney($data['total']),
                'discount' => $this->formatMoney($data['discount']),
                'final_total' => $this->formatMoney($data['final_total']),
                'notes' => $data['notes'],
                'payment_method' => $data['payment_method'],
                'payment_term' => $data['payment_term'],
                'updated_by' => $_SESSION['user']['id']
            ]);
            
            // Estornar movimentações de estoque
            $stockModel = new Stock($this->db);
            foreach ($oldData['items'] as $item) {
                $stockModel->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'type' => 'sale_reversal',
                    'reference_id' => $id,
                    'notes' => "Estorno Venda #{$oldData['number']}"
                ]);
            }
            
            // Excluir itens antigos
            $stmt = $this->db->prepare("DELETE FROM sale_items WHERE sale_id = :sale_id");
            $stmt->execute(['sale_id' => $id]);
            
            // Inserir novos itens
            foreach ($data['items'] as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO sale_items (
                        sale_id,
                        product_id,
                        quantity,
                        price,
                        total
                    ) VALUES (
                        :sale_id,
                        :product_id,
                        :quantity,
                        :price,
                        :total
                    )
                ");
                
                $stmt->execute([
                    'sale_id' => $id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $this->formatMoney($item['price']),
                    'total' => $this->formatMoney($item['total'])
                ]);
                
                // Atualizar estoque
                $stockModel->create([
                    'product_id' => $item['product_id'],
                    'quantity' => -$item['quantity'],
                    'type' => 'sale',
                    'reference_id' => $id,
                    'notes' => "Venda #{$oldData['number']}"
                ]);
            }
            
            // Atualizar contas a receber
            $accountModel = new Account($this->db);
            $accountModel->updateReceivables($id, $data);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erro ao atualizar venda: ' . $e->getMessage());
        }
    }
    
    public function cancel(int $id, string $reason): bool 
    {
        try {
            $sale = $this->find($id);
            if ($sale['status'] === 'canceled') {
                throw new Exception('Esta venda já está cancelada');
            }
            
            $this->db->beginTransaction();
            
            // Atualizar status da venda
            $stmt = $this->db->prepare("
                UPDATE sales SET
                    status = 'canceled',
                    cancel_reason = :reason,
                    canceled_by = :canceled_by,
                    canceled_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'reason' => $reason,
                'canceled_by' => $_SESSION['user']['id']
            ]);
            
            // Estornar movimentações de estoque
            $stockModel = new Stock($this->db);
            foreach ($sale['items'] as $item) {
                $stockModel->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'type' => 'sale_cancel',
                    'reference_id' => $id,
                    'notes' => "Cancelamento Venda #{$sale['number']}"
                ]);
            }
            
            // Cancelar contas a receber
            $accountModel = new Account($this->db);
            $accountModel->cancelReceivables($id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erro ao cancelar venda: ' . $e->getMessage());
        }
    }
    
    public function find(int $id): array 
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                c.name as customer_name,
                u1.name as created_by_name,
                u2.name as updated_by_name,
                u3.name as canceled_by_name
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            LEFT JOIN users u1 ON u1.id = s.created_by
            LEFT JOIN users u2 ON u2.id = s.updated_by
            LEFT JOIN users u3 ON u3.id = s.canceled_by
            WHERE s.id = :id
        ");
        
        $stmt->execute(['id' => $id]);
        $sale = $stmt->fetch();
        
        if (!$sale) {
            throw new Exception('Venda não encontrada');
        }
        
        // Buscar itens da venda
        $stmt = $this->db->prepare("
            SELECT 
                si.*,
                p.name as product_name,
                p.code as product_code,
                p.unit as product_unit
            FROM sale_items si
            LEFT JOIN products p ON p.id = si.product_id
            WHERE si.sale_id = :sale_id
        ");
        
        $stmt->execute(['sale_id' => $id]);
        $sale['items'] = $stmt->fetchAll();
        
        // Buscar contas a receber
        $stmt = $this->db->prepare("
            SELECT * FROM accounts_receivable 
            WHERE sale_id = :sale_id 
            ORDER BY due_date
        ");
        
        $stmt->execute(['sale_id' => $id]);
        $sale['receivables'] = $stmt->fetchAll();
        
        return $sale;
    }
    
    public function findAll(array $filters = [], int $page = 1, int $limit = 10): array 
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = "(s.number LIKE :search OR c.name LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['customer_id'])) {
            $where[] = "s.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "s.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(s.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(s.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Calcular offset para paginação
        $offset = ($page - 1) * $limit;
        
        // Buscar vendas
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                c.name as customer_name
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            WHERE {$whereClause}
            ORDER BY s.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $sales = $stmt->fetchAll();
        
        // Contar total para paginação
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM sales s
            LEFT JOIN customers c ON c.id = s.customer_id
            WHERE {$whereClause}
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        return [
            'data' => $sales,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    private function generateSaleNumber(): string 
    {
        $stmt = $this->db->query("
            SELECT COALESCE(MAX(CAST(number AS UNSIGNED)), 0) + 1 
            FROM sales 
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        
        $number = str_pad($stmt->fetchColumn(), 6, '0', STR_PAD_LEFT);
        return date('Y') . $number;
    }
    
    private function formatMoney(?string $value): ?float 
    {
        if (empty($value)) {
            return null;
        }
        return (float) str_replace(['.', ','], ['', '.'], $value);
    }
} 