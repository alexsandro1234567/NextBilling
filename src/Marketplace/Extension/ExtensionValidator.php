<?php
namespace App\Marketplace\Extension;

class ExtensionValidator 
{
    private $app;
    private $requiredFiles = [
        'extension.php',
        'config.php',
        'README.md'
    ];
    
    public function __construct($app) 
    {
        $this->app = $app;
    }
    
    public function validate(string $zipFile): array 
    {
        $errors = [];
        
        // Extrair ZIP em diretório temporário
        $tempDir = sys_get_temp_dir() . '/extension_' . uniqid();
        $zip = new \ZipArchive();
        
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Verificar arquivos obrigatórios
            foreach ($this->requiredFiles as $file) {
                if (!file_exists($tempDir . '/' . $file)) {
                    $errors[] = "Arquivo obrigatório não encontrado: {$file}";
                }
            }
            
            // Validar estrutura da extensão
            if (file_exists($tempDir . '/extension.php')) {
                try {
                    require_once $tempDir . '/extension.php';
                    
                    // Encontrar classe da extensão
                    $files = glob($tempDir . '/*.php');
                    foreach ($files as $file) {
                        $content = file_get_contents($file);
                        if (preg_match('/class\s+(\w+)\s+extends\s+BaseExtension/i', $content, $matches)) {
                            $className = $matches[1];
                            
                            // Instanciar e validar extensão
                            $extension = new $className($this->app);
                            
                            if (!method_exists($extension, 'getName')) {
                                $errors[] = 'Método getName() não encontrado';
                            }
                            
                            if (!method_exists($extension, 'getVersion')) {
                                $errors[] = 'Método getVersion() não encontrado';
                            }
                            
                            // Validar versão
                            if (method_exists($extension, 'getVersion')) {
                                if (!preg_match('/^\d+\.\d+\.\d+$/', $extension->getVersion())) {
                                    $errors[] = 'Formato de versão inválido. Use: X.Y.Z';
                                }
                            }
                            
                            break;
                        }
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = 'Erro ao carregar extensão: ' . $e->getMessage();
                }
            }
            
            // Limpar diretório temporário
            $this->removeDirectory($tempDir);
            
        } else {
            $errors[] = 'Erro ao extrair arquivo ZIP';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    private function removeDirectory($dir) 
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
} 