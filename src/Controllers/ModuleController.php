<?php
namespace App\Controllers;

class ModuleController extends BaseController 
{
    private $moduleManager;
    
    public function __construct() 
    {
        parent::__construct();
        $this->moduleManager = new \App\Services\ModuleManager();
    }
    
    public function index() 
    {
        $modules = $this->moduleManager->getAllModules();
        return $this->view->render('modules/index', [
            'modules' => $modules
        ]);
    }
    
    public function toggle($id) 
    {
        try {
            $result = $this->moduleManager->toggleModule($id);
            return $this->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function configure($id) 
    {
        $module = $this->moduleManager->getModule($id);
        return $this->view->render('modules/configure', [
            'module' => $module
        ]);
    }
} 