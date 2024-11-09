<?php
namespace App\Controllers;

use App\Models\Customer;
use Exception;

class CustomerController 
{
    private $customerModel;
    private $view;
    private $auth;
    
    public function __construct(Customer $customerModel) 
    {
        $this->customerModel = $customerModel;
        $this->view = new \App\View();
        $this->auth = new \App\Auth\AuthManager();
    }
    
    public function index() 
    {
        try {
            if (!$this->auth->checkPermission('customers.view')) {
                throw new Exception('Acesso negado');
            }
            
            // Obter filtros da URL
            $filters = [
                'search' => $_GET['search'] ?? '',
                'type' => $_GET['type'] ?? null,
                'active' => $_GET['active'] ?? null,
                'city' => $_GET['city'] ?? null,
                'state' => $_GET['state'] ?? null
            ];
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            
            // Buscar clientes
            $result = $this->customerModel->findAll($filters, $page, $limit);
            
            // Buscar opções para filtros
            $cities = $this->customerModel->getCities();
            $states = $this->customerModel->getStates();
            
            $this->view->render('customers/index', [
                'customers' => $result['data'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'filters' => $filters,
                'cities' => $cities,
                'states' => $states
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('customers/index');
        }
    }
    
    public function create() 
    {
        try {
            if (!$this->auth->checkPermission('customers.create')) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCustomerData($_POST);
                
                $customerId = $this->customerModel->create($_POST);
                
                $this->view->setSuccess('Cliente criado com sucesso');
                header('Location: ?route=customers/view&id=' . $customerId);
                exit;
            }
            
            $this->view->render('customers/create');
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('customers/create', ['data' => $_POST]);
        }
    }
    
    public function edit(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('customers.edit')) {
                throw new Exception('Acesso negado');
            }
            
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                throw new Exception('Cliente não encontrado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateCustomerData($_POST, $id);
                
                $this->customerModel->update($id, $_POST);
                
                $this->view->setSuccess('Cliente atualizado com sucesso');
                header('Location: ?route=customers/view&id=' . $id);
                exit;
            }
            
            $this->view->render('customers/edit', ['customer' => $customer]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            if (isset($customer)) {
                $this->view->render('customers/edit', ['customer' => $customer]);
            } else {
                header('Location: ?route=customers');
            }
        }
    }
    
    public function delete(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('customers.delete')) {
                throw new Exception('Acesso negado');
            }
            
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                throw new Exception('Cliente não encontrado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->customerModel->delete($id);
                
                $this->view->setSuccess('Cliente excluído com sucesso');
                header('Location: ?route=customers');
                exit;
            }
            
            $this->view->render('customers/delete', ['customer' => $customer]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=customers');
        }
    }
    
    public function view(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('customers.view')) {
                throw new Exception('Acesso negado');
            }
            
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                throw new Exception('Cliente não encontrado');
            }
            
            // Buscar vendas do cliente
            $salesModel = new \App\Models\Sale($this->db);
            $sales = $salesModel->findByCustomer($id);
            
            // Buscar histórico de pagamentos
            $paymentsModel = new \App\Models\Payment($this->db);
            $payments = $paymentsModel->findByCustomer($id);
            
            $this->view->render('customers/view', [
                'customer' => $customer,
                'sales' => $sales,
                'payments' => $payments
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=customers');
        }
    }
    
    private function validateCustomerData(array $data, ?int $customerId = null): void 
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($data['type'])) {
            $errors[] = 'Tipo é obrigatório';
        }
        
        if (empty($data['document'])) {
            $errors[] = 'Documento é obrigatório';
        } else {
            // Validar CPF/CNPJ
            if ($data['type'] === 'PF' && !$this->validateCPF($data['document'])) {
                $errors[] = 'CPF inválido';
            } elseif ($data['type'] === 'PJ' && !$this->validateCNPJ($data['document'])) {
                $errors[] = 'CNPJ inválido';
            }
            
            // Verificar documento único
            if ($this->customerModel->documentExists($data['document'], $customerId)) {
                $errors[] = 'Este documento já está cadastrado';
            }
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        if (!empty($data['zipcode']) && !preg_match('/^\d{5}-?\d{3}$/', $data['zipcode'])) {
            $errors[] = 'CEP inválido';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
    }
    
    private function validateCPF(string $cpf): bool 
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
        
        // Calcula os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$t] != $d) {
                return false;
            }
        }
        
        return true;
    }
    
    private function validateCNPJ(string $cnpj): bool 
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        
        // Calcula os dígitos verificadores
        $j = 5;
        $k = 6;
        $soma1 = 0;
        $soma2 = 0;
        
        for ($i = 0; $i < 13; $i++) {
            $j = $j == 1 ? 9 : $j;
            $k = $k == 1 ? 9 : $k;
            
            $soma2 += ($cnpj[$i] * $k);
            
            if ($i < 12) {
                $soma1 += ($cnpj[$i] * $j);
            }
            
            $k--;
            $j--;
        }
        
        $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
        $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
        
        return (($cnpj[12] == $digito1) && ($cnpj[13] == $digito2));
    }
} 