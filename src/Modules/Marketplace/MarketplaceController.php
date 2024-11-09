<?php
namespace App\Modules\Marketplace;

class MarketplaceController extends BaseController 
{
    public function index() 
    {
        $plugins = $this->getAvailablePlugins();
        $installed = $this->getInstalledPlugins();
        
        return $this->view->render('marketplace/index', [
            'plugins' => $plugins,
            'installed' => $installed
        ]);
    }
    
    public function install($pluginId) 
    {
        try {
            $plugin = $this->getPluginDetails($pluginId);
            $this->validatePlugin($plugin);
            $this->installPlugin($plugin);
            
            return $this->json([
                'success' => true,
                'message' => 'Plugin instalado com sucesso'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 