<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Exception;

class ProductController 
{
    private $productModel;
    private $view;
    private $auth;
    
    public function __construct(Product $productModel) 
    {
        $this->productModel = $productModel;
        $this->view = new \App\View();
        $this->auth = new \App\Auth\AuthManager();
    }
    
    public function index() 
    {
        try {
            if (!$this->auth->checkPermission('products.view')) {
                throw new Exception('Acesso negado');
            }
            
            // Obter filtros da URL
            $filters = [
                'search' => $_GET['search'] ?? '',
                'category_id' => $_GET['category_id'] ?? null,
                'brand_id' => $_GET['brand_id'] ?? null,
                'active' => $_GET['active'] ?? null,
                'stock_status' => $_GET['stock_status'] ?? null
            ];
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            
            // Buscar produtos
            $result = $this->productModel->findAll($filters, $page, $limit);
            
            // Buscar categorias e marcas para filtros
            $categoryModel = new Category($this->db);
            $categories = $categoryModel->findAll();
            
            $brandModel = new Brand($this->db);
            $brands = $brandModel->findAll();
            
            $this->view->render('products/index', [
                'products' => $result['data'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'filters' => $filters,
                'categories' => $categories,
                'brands' => $brands
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('products/index');
        }
    }
    
    public function create() 
    {
        try {
            if (!$this->auth->checkPermission('products.create')) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateProductData($_POST);
                
                // Gerar código único se não informado
                if (empty($_POST['code'])) {
                    $_POST['code'] = $this->generateUniqueCode();
                }
                
                $productId = $this->productModel->create($_POST);
                
                $this->view->setSuccess('Produto criado com sucesso');
                header('Location: ?route=products/view&id=' . $productId);
                exit;
            }
            
            // Buscar categorias e marcas para o formulário
            $categoryModel = new Category($this->db);
            $categories = $categoryModel->findAll();
            
            $brandModel = new Brand($this->db);
            $brands = $brandModel->findAll();
            
            $this->view->render('products/create', [
                'categories' => $categories,
                'brands' => $brands
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('products/create', [
                'data' => $_POST,
                'categories' => $categories ?? [],
                'brands' => $brands ?? []
            ]);
        }
    }
    
    public function edit(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('products.edit')) {
                throw new Exception('Acesso negado');
            }
            
            $product = $this->productModel->find($id);
            if (!$product) {
                throw new Exception('Produto não encontrado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateProductData($_POST, $id);
                
                $this->productModel->update($id, $_POST);
                
                $this->view->setSuccess('Produto atualizado com sucesso');
                header('Location: ?route=products/view&id=' . $id);
                exit;
            }
            
            // Buscar categorias e marcas para o formulário
            $categoryModel = new Category($this->db);
            $categories = $categoryModel->findAll();
            
            $brandModel = new Brand($this->db);
            $brands = $brandModel->findAll();
            
            $this->view->render('products/edit', [
                'product' => $product,
                'categories' => $categories,
                'brands' => $brands
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            if (isset($product)) {
                $this->view->render('products/edit', [
                    'product' => $product,
                    'categories' => $categories ?? [],
                    'brands' => $brands ?? []
                ]);
            } else {
                header('Location: ?route=products');
            }
        }
    }
    
    public function delete(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('products.delete')) {
                throw new Exception('Acesso negado');
            }
            
            $product = $this->productModel->find($id);
            if (!$product) {
                throw new Exception('Produto não encontrado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->productModel->delete($id);
                
                $this->view->setSuccess('Produto excluído com sucesso');
                header('Location: ?route=products');
                exit;
            }
            
            $this->view->render('products/delete', ['product' => $product]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=products');
        }
    }
    
    public function view(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('products.view')) {
                throw new Exception('Acesso negado');
            }
            
            $product = $this->productModel->find($id);
            if (!$product) {
                throw new Exception('Produto não encontrado');
            }
            
            // Buscar movimentações de estoque
            $stockModel = new \App\Models\Stock($this->db);
            $stockMovements = $stockModel->findByProduct($id);
            
            // Buscar vendas do produto
            $salesModel = new \App\Models\Sale($this->db);
            $sales = $salesModel->findByProduct($id);
            
            $this->view->render('products/view', [
                'product' => $product,
                'stockMovements' => $stockMovements,
                'sales' => $sales
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=products');
        }
    }
    
    private function validateProductData(array $data, ?int $productId = null): void 
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($data['category_id'])) {
            $errors[] = 'Categoria é obrigatória';
        }
        
        if (!empty($data['code'])) {
            // Verificar código único
            if ($this->productModel->codeExists($data['code'], $productId)) {
                $errors[] = 'Este código já está em uso';
            }
        }
        
        if (!empty($data['barcode'])) {
            // Verificar código de barras único
            if ($this->productModel->barcodeExists($data['barcode'], $productId)) {
                $errors[] = 'Este código de barras já está em uso';
            }
        }
        
        if (empty($data['unit'])) {
            $errors[] = 'Unidade é obrigatória';
        }
        
        if (empty($data['cost_price'])) {
            $errors[] = 'Preço de custo é obrigatório';
        }
        
        if (empty($data['sale_price'])) {
            $errors[] = 'Preço de venda é obrigatório';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
    }
    
    private function generateUniqueCode(): string 
    {
        do {
            $code = strtoupper(substr(uniqid(), -6));
        } while ($this->productModel->codeExists($code));
        
        return $code;
    }
} 