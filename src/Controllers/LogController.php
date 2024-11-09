<?php
namespace App\Controllers;

use App\Models\Log;
use Exception;

class LogController 
{
    private $logModel;
    private $view;
    private $auth;
    
    public function __construct(Log $logModel) 
    {
        $this->logModel = $logModel;
        $this->view = new \App\View();
        $this->auth = new \App\Auth\AuthManager();
    }
    
    public function index() 
    {
        try {
            if (!$this->auth->checkPermission('logs.view')) {
                throw new Exception('Acesso negado');
            }
            
            // Obter filtros da URL
            $filters = [
                'user_id' => $_GET['user_id'] ?? null,
                'module' => $_GET['module'] ?? null,
                'action' => $_GET['action'] ?? null,
                'entity_type' => $_GET['entity_type'] ?? null,
                'date_start' => $_GET['date_start'] ?? null,
                'date_end' => $_GET['date_end'] ?? null
            ];
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 50);
            
            // Buscar logs
            $result = $this->logModel->findAll($filters, $page, $limit);
            
            // Buscar opções para filtros
            $modules = $this->logModel->getModules();
            $actions = $this->logModel->getActions();
            $entityTypes = $this->logModel->getEntityTypes();
            
            $this->view->render('logs/index', [
                'logs' => $result['data'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'filters' => $filters,
                'modules' => $modules,
                'actions' => $actions,
                'entityTypes' => $entityTypes
            ]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            $this->view->render('logs/index');
        }
    }
    
    public function view(int $id) 
    {
        try {
            if (!$this->auth->checkPermission('logs.view')) {
                throw new Exception('Acesso negado');
            }
            
            $log = $this->logModel->find($id);
            if (!$log) {
                throw new Exception('Log não encontrado');
            }
            
            $this->view->render('logs/view', ['log' => $log]);
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=logs');
            exit;
        }
    }
    
    public function export() 
    {
        try {
            if (!$this->auth->checkPermission('logs.export')) {
                throw new Exception('Acesso negado');
            }
            
            $filters = [
                'user_id' => $_GET['user_id'] ?? null,
                'module' => $_GET['module'] ?? null,
                'action' => $_GET['action'] ?? null,
                'entity_type' => $_GET['entity_type'] ?? null,
                'date_start' => $_GET['date_start'] ?? null,
                'date_end' => $_GET['date_end'] ?? null
            ];
            
            // Buscar todos os logs sem paginação
            $logs = $this->logModel->findAll($filters, 1, PHP_INT_MAX)['data'];
            
            // Gerar CSV
            $filename = 'logs_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Cabeçalho do CSV
            fputcsv($output, [
                'ID',
                'Data',
                'Usuário',
                'Módulo',
                'Ação',
                'Descrição',
                'Tipo Entidade',
                'ID Entidade',
                'IP',
                'User Agent'
            ]);
            
            // Dados
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['created_at'],
                    $log['user_name'],
                    $log['module'],
                    $log['action'],
                    $log['description'],
                    $log['entity_type'],
                    $log['entity_id'],
                    $log['ip_address'],
                    $log['user_agent']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            $this->view->setError($e->getMessage());
            header('Location: ?route=logs');
            exit;
        }
    }
} 