<?php
namespace App\Updates;

class UpdateManager 
{
    private $app;
    private $apiUrl = 'https://updates.exemplo.com/api/v1';
    
    public function __construct($app) 
    {
        $this->app = $app;
    }
    
    public function checkUpdates(): array 
    {
        $extensions = $this->app->extensions->getActiveExtensions();
        $updates = [];
        
        foreach ($extensions as $extension) {
            try {
                $response = $this->apiRequest('GET', "/extensions/{$extension->getName()}/version", [
                    'current_version' => $extension->getVersion(),
                    'license_key' => $this->app->config->get('license_key'),
                    'domain' => $this->app->config->get('domain')
                ]);
                
                if (!empty($response['update_available'])) {
                    $updates[] = [
                        'extension' => $extension->getName(),
                        'current_version' => $extension->getVersion(),
                        'new_version' => $response['version'],
                        'changelog' => $response['changelog'],
                        'download_url' => $response['download_url']
                    ];
                }
                
            } catch (\Exception $e) {
                // Log do erro
                continue;
            }
        }
        
        return $updates;
    }
    
    public function update(string $extensionName): bool 
    {
        $extension = $this->app->extensions->getExtension($extensionName);
        if (!$extension) {
            throw new \Exception('Extensão não encontrada');
        }
        
        // Baixar nova versão
        $response = $this->apiRequest('GET', "/extensions/{$extensionName}/download", [
            'license_key' => $this->app->config->get('license_key'),
            'domain' => $this->app->config->get('domain')
        ]);
        
        if (empty($response['download_url'])) {
            throw new \Exception('Erro ao baixar atualização');
        }
        
        // Fazer backup da versão atual
        $this->backup($extension);
        
        try {
            // Baixar e extrair nova versão
            $zipFile = tempnam(sys_get_temp_dir(), 'update_');
            file_put_contents($zipFile, file_get_contents($response['download_url']));
            
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === true) {
                $zip->extractTo($this->app->getBasePath() . '/extensions/' . $extensionName);
                $zip->close();
                
                // Executar migrations se necessário
                if (method_exists($extension, 'update')) {
                    $extension->update();
                }
                
                return true;
            }
            
            throw new \Exception('Erro ao extrair atualização');
            
        } catch (\Exception $e) {
            // Restaurar backup em caso de erro
            $this->restore($extension);
            throw $e;
        }
    }
    
    private function backup($extension): void 
    {
        $source = $extension->getPath();
        $backup = $source . '_backup_' . date('YmdHis');
        
        if (!is_dir($source)) {
            throw new \Exception('Diretório da extensão não encontrado');
        }
        
        // Criar backup
        $this->copyDirectory($source, $backup);
    }
    
    private function restore($extension): void 
    {
        $source = $extension->getPath();
        $pattern = $source . '_backup_*';
        
        // Encontrar último backup
        $backups = glob($pattern);
        if (empty($backups)) {
            throw new \Exception('Backup não encontrado');
        }
        
        $lastBackup = end($backups);
        
        // Restaurar backup
        if (is_dir($source)) {
            $this->removeDirectory($source);
        }
        
        $this->copyDirectory($lastBackup, $source);
    }
    
    private function copyDirectory($source, $destination): void 
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $relativePath = substr($filePath, strlen($source) + 1);
            $destinationPath = $destination . '/' . $relativePath;
            
            if (!is_dir($file->getPath())) {
                mkdir($file->getPath(), 0755, true);
            }
            
            copy($filePath, $destinationPath);
        }
    }
    
    private function removeDirectory($directory): void 
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($directory);
    }
    
    private function apiRequest(string $method, string $endpoint, array $data = []): array 
    {
        $ch = curl_init($this->apiUrl . $endpoint);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->app->config->get('marketplace_token'),
            'Accept: application/json'
        ]);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($statusCode === 200) {
            return json_decode($response, true);
        }
        
        throw new \Exception('Erro na requisição: ' . $response);
    }
} 