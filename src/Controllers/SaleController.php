<?php
namespace App\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Exception;

class SaleController 
{
    private $saleModel;
    private $view;
    private $auth;
    
    public function __construct(Sale $saleModel) 
    {
        $this->saleModel = $saleModel;
        $this->view = new \App\View();
        $this->auth = new \App\Auth\AuthManager();
    }
    
    public function index() 
    {
        try {
            if (!$this->auth->checkPermission('sales.view')) {
                throw new Exception('Acesso negado');
            }
            
            // Obter filtros da URL
            $filters = [
                'search' => $_GET['search'] ?? '',
                'customer_id' => $_GET['customer_id'] ?? null,
                'status' => $_GET['status'] ?? null,
                'start_date' => $_GET['start_date'] ?? null,
                'end_date' => $_GET['end_date'] ?? null
            ];
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            
            // Buscar vendas
            $result = $this->saleModel->findAll($filters, $page, $limit);
            
            // Buscar clientes para filtro
            $customerModel = new Customer($this->db);
            $customers = $customerModel->findAll();
            
            $this->view->render('sales/index', [
                'sales' => $result['data'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'filters' => $filters,
                'customers' => $customers
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('sales/index');
        }
    }
    
    public function create() 
    {
        try {
            if (!$this->auth->checkPermission('sales.create')) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateSaleData($_POST);
                
                $saleId = $this->saleModel->create($_POST);
                
                $this->view->setSuccess('Venda criada com sucesso');
                header('Location: ?route=sales/view&id=' . $saleId);
                exit;
            }
            
            // Buscar clientes e produtos para o formulário
            $customerModel = new Customer($this->db);
            $customers = $customerModel->findAll(['active' => true]);
            
            $productModel = new Product($this->db);
            $products = $productModel->findAll(['active' => true]);
            
            $this->view->render('sales/create', [
                'customers' => $customers,
                'products' => $products
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('sales/create', [
                'data' => $_POST,
                'customers' => $customers ?? [],
                'products' => $products ?? []
            ]);
        }
    }
    
    public function edit(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('sales.edit')) {
                throw new Exception('Acesso negado');
            }
            
            $sale = $this->saleModel->find($id);
            if (!$sale) {
                throw new Exception('Venda não encontrada');
            }
            
            if ($sale['status'] !== 'pending') {
                throw new Exception('Apenas vendas pendentes podem ser alteradas');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateSaleData($_POST);
                
                $this->saleModel->update($id, $_POST);
                
                $this->view->setSuccess('Venda atualizada com sucesso');
                header('Location: ?route=sales/view&id=' . $id);
                exit;
            }
            
            // Buscar clientes e produtos para o formulário
            $customerModel = new Customer($this->db);
            $customers = $customerModel->findAll(['active' => true]);
            
            $productModel = new Product($this->db);
            $products = $productModel->findAll(['active' => true]);
            
            $this->view->render('sales/edit', [
                'sale' => $sale,
                'customers' => $customers,
                'products' => $products
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            if (isset($sale)) {
                $this->view->render('sales/edit', [
                    'sale' => $sale,
                    'customers' => $customers ?? [],
                    'products' => $products ?? []
                ]);
            } else {
                header('Location: ?route=sales');
            }
        }
    }
    
    public function cancel(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('sales.cancel')) {
                throw new Exception('Acesso negado');
            }
            
            $sale = $this->saleModel->find($id);
            if (!$sale) {
                throw new Exception('Venda não encontrada');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (empty($_POST['reason'])) {
                    throw new Exception('Informe o motivo do cancelamento');
                }
                
                $this->saleModel->cancel($id, $_POST['reason']);
                
                $this->view->setSuccess('Venda cancelada com sucesso');
                header('Location: ?route=sales/view&id=' . $id);
                exit;
            }
            
            $this->view->render('sales/cancel', ['sale' => $sale]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=sales');
        }
    }
    
    public function view(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('sales.view')) {
                throw new Exception('Acesso negado');
            }
            
            $sale = $this->saleModel->find($id);
            if (!$sale) {
                throw new Exception('Venda não encontrada');
            }
            
            $this->view->render('sales/view', ['sale' => $sale]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=sales');
        }
    }
    
    private function validateSaleData(array $data): void 
    {
        $errors = [];
        
        if (empty($data['customer_id'])) {
            $errors[] = 'Cliente é obrigatório';
        }
        
        if (empty($data['items'])) {
            $errors[] = 'Adicione pelo menos um item à venda';
        }
        
        if (empty($data['payment_method'])) {
            $errors[] = 'Forma de pagamento é obrigatória';
        }
        
        if ($data['payment_method'] === 'term' && empty($data['payment_term'])) {
            $errors[] = 'Prazo de pagamento é obrigatório';
        }
        
        foreach ($data['items'] as $item) {
            if (empty($item['quantity']) || $item['quantity'] <= 0) {
                $errors[] = 'Quantidade inválida para o produto ' . $item['product_name'];
            }
            
            if (empty($item['price']) || $item['price'] <= 0) {
                $errors[] = 'Preço inválido para o produto ' . $item['product_name'];
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
    }
} 