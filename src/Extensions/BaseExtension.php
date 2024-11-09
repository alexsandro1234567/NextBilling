<?php
namespace App\Extensions;

abstract class BaseExtension 
{
    protected $app;
    protected $config;
    
    public function __construct($app) 
    {
        $this->app = $app;
        $this->config = $this->loadConfig();
    }
    
    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function getVersion(): string;
    abstract public function getAuthor(): string;
    abstract public function getType(): ExtensionType;
    
    public function getIcon(): string 
    {
        return 'fas fa-puzzle-piece';
    }
    
    public function getRequirements(): array 
    {
        return [];
    }
    
    public function getDependencies(): array 
    {
        return [];
    }
    
    public function getScreenshots(): array 
    {
        return [];
    }
    
    public function getDocumentationUrl(): ?string 
    {
        return null;
    }
    
    public function getSupportUrl(): ?string 
    {
        return null;
    }
    
    public function getChangelogUrl(): ?string 
    {
        return null;
    }
    
    public function getLicense(): string 
    {
        return 'MIT';
    }
    
    public function getPrice(): ?float 
    {
        return null;
    }
    
    public function install(): bool 
    {
        return true;
    }
    
    public function uninstall(): bool 
    {
        return true;
    }
    
    public function activate(): bool 
    {
        return true;
    }
    
    public function deactivate(): bool 
    {
        return true;
    }
    
    public function update(): bool 
    {
        return true;
    }
    
    public function getSettings(): array 
    {
        return [];
    }
    
    public function validateSettings(array $settings): bool 
    {
        return true;
    }
    
    public function getMenuItems(): array 
    {
        return [];
    }
    
    public function getPermissions(): array 
    {
        return [];
    }
    
    protected function loadConfig(): array 
    {
        $configFile = $this->getPath() . '/config.php';
        return file_exists($configFile) ? require $configFile : [];
    }
    
    public function getPath(): string 
    {
        $reflection = new \ReflectionClass($this);
        return dirname($reflection->getFileName());
    }
} 