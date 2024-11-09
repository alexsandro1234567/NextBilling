<?php
namespace App\Extensions;

class ExtensionManager 
{
    private $app;
    private $extensions = [];
    private $activeExtensions = [];
    
    public function __construct($app) 
    {
        $this->app = $app;
        $this->loadExtensions();
    }
    
    public function loadExtensions() 
    {
        // Carregar extensões ativas do banco de dados
        $stmt = $this->app->db->query("
            SELECT * FROM extensions WHERE active = 1
        ");
        
        while ($extension = $stmt->fetch()) {
            $this->activeExtensions[$extension['name']] = $extension;
            
            $className = $extension['class'];
            if (class_exists($className)) {
                $this->extensions[$extension['name']] = new $className($this->app);
            }
        }
    }
    
    public function getExtension(string $name) 
    {
        return $this->extensions[$name] ?? null;
    }
    
    public function getActiveExtensions(): array 
    {
        return $this->extensions;
    }
    
    public function installExtension(string $name): bool 
    {
        $extension = $this->getExtension($name);
        if (!$extension) {
            return false;
        }
        
        if ($extension->install()) {
            $stmt = $this->app->db->prepare("
                INSERT INTO extensions (
                    name, 
                    class, 
                    version, 
                    active, 
                    created_at
                ) VALUES (
                    :name,
                    :class,
                    :version,
                    1,
                    NOW()
                )
            ");
            
            return $stmt->execute([
                'name' => $name,
                'class' => get_class($extension),
                'version' => $extension->getVersion()
            ]);
        }
        
        return false;
    }
    
    public function uninstallExtension(string $name): bool 
    {
        $extension = $this->getExtension($name);
        if (!$extension) {
            return false;
        }
        
        if ($extension->uninstall()) {
            $stmt = $this->app->db->prepare("
                DELETE FROM extensions WHERE name = :name
            ");
            
            return $stmt->execute(['name' => $name]);
        }
        
        return false;
    }
} 