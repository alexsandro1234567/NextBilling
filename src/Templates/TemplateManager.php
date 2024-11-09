<?php
namespace App\Templates;

class TemplateManager 
{
    private $app;
    private $activeTemplate;
    private $templates = [];
    
    public function __construct($app) 
    {
        $this->app = $app;
        $this->loadTemplates();
    }
    
    public function loadTemplates(): void 
    {
        // Carregar templates instalados
        $templatesDir = $this->app->getBasePath() . '/templates';
        if (!is_dir($templatesDir)) {
            return;
        }
        
        $dirs = glob($templatesDir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $templateFile = $dir . '/' . basename($dir) . 'Template.php';
            if (file_exists($templateFile)) {
                require_once $templateFile;
                
                $className = 'Templates\\' . basename($dir) . '\\' . basename($dir) . 'Template';
                if (class_exists($className)) {
                    $template = new $className($this->app);
                    $this->templates[basename($dir)] = $template;
                }
            }
        }
        
        // Carregar template ativo
        $activeTemplate = $this->app->config->get('active_template');
        if (isset($this->templates[$activeTemplate])) {
            $this->activeTemplate = $this->templates[$activeTemplate];
        }
    }
    
    public function getTemplate(string $name) 
    {
        return $this->templates[$name] ?? null;
    }
    
    public function getActiveTemplate() 
    {
        return $this->activeTemplate;
    }
    
    public function setActiveTemplate(string $name): bool 
    {
        if (!isset($this->templates[$name])) {
            throw new \Exception('Template não encontrado');
        }
        
        $this->app->config->set('active_template', $name);
        $this->activeTemplate = $this->templates[$name];
        
        return true;
    }
    
    public function getTemplates(): array 
    {
        return $this->templates;
    }
    
    public function installTemplate(string $zipFile): bool 
    {
        $tempDir = sys_get_temp_dir() . '/template_' . uniqid();
        
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Validar estrutura do template
            $validator = new TemplateValidator($this->app);
            $result = $validator->validate($tempDir);
            
            if (!$result['valid']) {
                throw new \Exception('Template inválido: ' . implode("\n", $result['errors']));
            }
            
            // Mover para diretório de templates
            $templateName = basename($tempDir);
            $targetDir = $this->app->getBasePath() . '/templates/' . $templateName;
            
            if (is_dir($targetDir)) {
                throw new \Exception('Template já instalado');
            }
            
            rename($tempDir, $targetDir);
            
            return true;
        }
        
        throw new \Exception('Erro ao extrair arquivo ZIP');
    }
} 