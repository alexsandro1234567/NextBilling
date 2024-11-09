<?php
namespace App\Marketplace;

class MarketplaceManager 
{
    private $app;
    private $apiUrl = 'https://marketplace.exemplo.com/api/v1';
    
    public function __construct($app) 
    {
        $this->app = $app;
    }
    
    public function getCategories(): array 
    {
        return $this->apiRequest('GET', '/categories');
    }
    
    public function getExtensions(array $filters = []): array 
    {
        return $this->apiRequest('GET', '/extensions', $filters);
    }
    
    public function getExtension(string $slug): array 
    {
        return $this->apiRequest('GET', "/extensions/{$slug}");
    }
    
    public function purchase(string $slug): array 
    {
        return $this->apiRequest('POST', "/extensions/{$slug}/purchase", [
            'license_key' => $this->app->config->get('license_key'),
            'domain' => $this->app->config->get('domain')
        ]);
    }
    
    public function download(string $slug): string 
    {
        $response = $this->apiRequest('GET', "/extensions/{$slug}/download", [
            'license_key' => $this->app->config->get('license_key'),
            'domain' => $this->app->config->get('domain')
        ]);
        
        if (!empty($response['download_url'])) {
            // Download do arquivo
            $zipFile = tempnam(sys_get_temp_dir(), 'extension_');
            file_put_contents($zipFile, file_get_contents($response['download_url']));
            return $zipFile;
        }
        
        throw new \Exception('Erro ao baixar extensão');
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