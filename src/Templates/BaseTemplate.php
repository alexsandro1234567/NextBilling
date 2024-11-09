<?php
namespace App\Templates;

abstract class BaseTemplate 
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
    abstract public function getScreenshot(): string;
    
    public function getFeatures(): array 
    {
        return [];
    }
    
    public function getSettings(): array 
    {
        return [];
    }
    
    public function getLayouts(): array 
    {
        return [
            'default' => 'Default Layout',
            'full' => 'Full Width',
            'boxed' => 'Boxed Layout'
        ];
    }
    
    public function getColorSchemes(): array 
    {
        return [
            'light' => 'Light Theme',
            'dark' => 'Dark Theme'
        ];
    }
    
    public function getCustomCss(): string 
    {
        return '';
    }
    
    public function getCustomJs(): string 
    {
        return '';
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