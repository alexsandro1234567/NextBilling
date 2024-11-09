<?php
namespace App\Controllers\Admin;

use App\Marketplace\MarketplaceManager;

class MarketplaceController 
{
    private $app;
    private $marketplace;
    private $view;
    
    public function __construct($app) 
    {
        $this->app = $app;
        $this->marketplace = new MarketplaceManager($app);
        $this->view = new \App\View();
    }
    
    public function index() 
    {
        // Listar extensões e templates
        $filters = [
            'search' => $_GET['search'] ?? '',
            'type' => $_GET['type'] ?? '',
            'category' => $_GET['category'] ?? '',
            'status' => $_GET['status'] ?? 'active'
        ];
        
        $page = (int)($_GET['page'] ?? 1);
        
        $items = $this->marketplace->getItems($filters, $page);
        $categories = $this->marketplace->getCategories();
        
        $this->view->render('admin/marketplace/index', [
            'items' => $items['data'],
            'total' => $items['total'],
            'pages' => $items['pages'],
            'current_page' => $page,
            'filters' => $filters,
            'categories' => $categories
        ]);
    }
    
    public function review() 
    {
        if (!$this->app->auth->checkPermission('marketplace.review')) {
            $this->app->session->setFlash('error', 'Acesso negado');
            header('Location: ?route=admin/marketplace');
            exit;
        }
        
        $id = (int)$_GET['id'];
        $item = $this->marketplace->getItem($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->marketplace->reviewItem($id, $_POST);
                $this->app->session->setFlash('success', 'Item revisado com sucesso');
                header('Location: ?route=admin/marketplace');
                exit;
                
            } catch (\Exception $e) {
                $this->app->session->setFlash('error', $e->getMessage());
            }
        }
        
        $this->view->render('admin/marketplace/review', [
            'item' => $item
        ]);
    }
    
    public function settings() 
    {
        if (!$this->app->auth->checkPermission('marketplace.settings')) {
            $this->app->session->setFlash('error', 'Acesso negado');
            header('Location: ?route=admin/marketplace');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->marketplace->saveSettings($_POST);
                $this->app->session->setFlash('success', 'Configurações salvas com sucesso');
                header('Location: ?route=admin/marketplace/settings');
                exit;
                
            } catch (\Exception $e) {
                $this->app->session->setFlash('error', $e->getMessage());
            }
        }
        
        $settings = $this->marketplace->getSettings();
        
        $this->view->render('admin/marketplace/settings', [
            'settings' => $settings
        ]);
    }
} 