<?php
namespace App\Controllers;

use App\Models\User;
use Exception;

class UserController 
{
    private $userModel;
    private $view;
    private $auth;
    
    public function __construct(User $userModel) 
    {
        $this->userModel = $userModel;
        $this->view = new \App\View();
        $this->auth = new \App\Auth\AuthManager();
    }
    
    public function index() 
    {
        try {
            // Verificar permissão
            if (!$this->auth->checkPermission('users.view')) {
                throw new Exception('Acesso negado');
            }
            
            // Obter filtros da URL
            $filters = [
                'search' => $_GET['search'] ?? '',
                'role' => $_GET['role'] ?? null,
                'active' => $_GET['active'] ?? null
            ];
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            
            // Buscar usuários
            $result = $this->userModel->findAll($filters, $page, $limit);
            
            $this->view->render('users/index', [
                'users' => $result['data'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'filters' => $filters
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('users/index');
        }
    }
    
    public function create() 
    {
        try {
            if (!$this->auth->checkPermission('users.create')) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateUserData($_POST);
                
                $userId = $this->userModel->create([
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'role' => $_POST['role'],
                    'active' => isset($_POST['active'])
                ]);
                
                $this->view->setSuccess('Usuário criado com sucesso');
                header('Location: ?route=users/view&id=' . $userId);
                exit;
            }
            
            $this->view->render('users/create');
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('users/create', ['data' => $_POST]);
        }
    }
    
    public function edit(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('users.edit')) {
                throw new Exception('Acesso negado');
            }
            
            $user = $this->userModel->find($id);
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->validateUserData($_POST, $id);
                
                $data = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'role' => $_POST['role'],
                    'active' => isset($_POST['active'])
                ];
                
                // Atualizar senha apenas se fornecida
                if (!empty($_POST['password'])) {
                    $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                
                $this->userModel->update($id, $data);
                
                $this->view->setSuccess('Usuário atualizado com sucesso');
                header('Location: ?route=users/view&id=' . $id);
                exit;
            }
            
            $this->view->render('users/edit', ['user' => $user]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('users/edit', [
                'user' => $user ?? null,
                'data' => $_POST
            ]);
        }
    }
    
    public function delete(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('users.delete')) {
                throw new Exception('Acesso negado');
            }
            
            // Não permitir deletar o próprio usuário
            if ($id === $_SESSION['user']['id']) {
                throw new Exception('Não é possível deletar seu próprio usuário');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->userModel->delete($id);
                
                $this->view->setSuccess('Usuário deletado com sucesso');
                header('Location: ?route=users');
                exit;
            }
            
            $user = $this->userModel->find($id);
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            $this->view->render('users/delete', ['user' => $user]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=users');
            exit;
        }
    }
    
    public function view(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('users.view')) {
                throw new Exception('Acesso negado');
            }
            
            $user = $this->userModel->find($id);
            if (!$user) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Buscar logs do usuário
            $logs = $this->userModel->getUserLogs($id);
            
            $this->view->render('users/view', [
                'user' => $user,
                'logs' => $logs
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=users');
            exit;
        }
    }
    
    private function validateUserData(array $data, ?int $userId = null): void 
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        // Verificar email único
        if ($this->userModel->emailExists($data['email'], $userId)) {
            $errors[] = 'Este email já está em uso';
        }
        
        // Senha obrigatória apenas na criação
        if (!$userId && empty($data['password'])) {
            $errors[] = 'Senha é obrigatória';
        } elseif (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors[] = 'Senha deve ter no mínimo 8 caracteres';
            }
            if ($data['password'] !== ($data['password_confirm'] ?? '')) {
                $errors[] = 'As senhas não conferem';
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
    }
} 