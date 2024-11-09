<?php
namespace App\Services;

class ModuleManager 
{
    private $db;
    private $modules = [
        'ai_support' => [
            'name' => 'Suporte com IA',
            'description' => 'Chatbot e automação de suporte com IA',
            'version' => '1.0',
            'is_core' => false,
            'dependencies' => [],
            'settings' => [
                'openai_api_key' => '',
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 150
            ]
        ],
        'dashboard' => [
            'name' => 'Dashboard Personalizável',
            'description' => 'Painéis dinâmicos com análise preditiva',
            'version' => '1.0',
            'is_core' => true,
            'dependencies' => []
        ],
        // ... outros módulos
    ];
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    public function installModule($slug) 
    {
        if (!isset($this->modules[$slug])) {
            throw new \Exception("Módulo não encontrado");
        }
        
        $module = $this->modules[$slug];
        
        // Verifica dependências
        foreach ($module['dependencies'] as $dep) {
            if (!$this->isModuleActive($dep)) {
                throw new \Exception("Dependência não atendida: {$dep}");
            }
        }
        
        // Instala o módulo
        $sql = "INSERT INTO modules (name, slug, description, version, is_core) 
                VALUES (:name, :slug, :description, :version, :is_core)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $module['name'],
            'slug' => $slug,
            'description' => $module['description'],
            'version' => $module['version'],
            'is_core' => $module['is_core']
        ]);
        
        return true;
    }
    
    public function toggleModule($id) 
    {
        $sql = "UPDATE modules SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
} 