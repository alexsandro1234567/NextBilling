<?php
namespace App\Modules\Dashboard;

class DashboardController extends BaseController 
{
    public function index() 
    {
        $widgets = $this->getUserWidgets();
        $availableWidgets = $this->getAvailableWidgets();
        
        return $this->view->render('dashboard/index', [
            'widgets' => $widgets,
            'available' => $availableWidgets
        ]);
    }
    
    public function saveLayout() 
    {
        $layout = $_POST['layout'] ?? [];
        
        try {
            $this->saveUserLayout($layout);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function getUserWidgets() 
    {
        return [
            'revenue' => [
                'title' => 'Receita',
                'type' => 'chart',
                'data' => $this->getRevenueData()
            ],
            'tickets' => [
                'title' => 'Tickets',
                'type' => 'counter',
                'data' => $this->getTicketsData()
            ],
            // Mais widgets...
        ];
    }
} 