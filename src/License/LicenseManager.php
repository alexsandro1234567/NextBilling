<?php
namespace App\License;

class LicenseManager 
{
    private $app;
    private $apiUrl = 'https://license.exemplo.com/api/v1';
    
    public function __construct($app) 
    {
        $this->app = $app;
    }
    
    public function validate(): bool 
    {
        try {
            $response = $this->apiRequest('POST', '/validate', [
                'license_key' => $this->app->config->get('license_key'),
                'domain' => $this->app->config->get('domain'),
                'ip' => $_SERVER['SERVER_ADDR']
            ]);
            
            return $response['valid'] ?? false;
            
        } catch (\Exception $e) {
            // Log do erro
            return false;
        }
    }
    
    public function activate(string $licenseKey): bool 
    {
        $response = $this->apiRequest('POST', '/activate', [
            'license_key' => $licenseKey,
            'domain' => $this->app->config->get('domain'),
            'ip' => $_SERVER['SERVER_ADDR']
        ]);
        
        if ($response['success'] ?? false) {
            // Salvar chave de licença
            $this->app->config->set('license_key', $licenseKey);
            return true;
        }
        
        throw new \Exception($response['message'] ?? 'Erro ao ativar licença');
    }
    
    public function deactivate(): bool 
    {
        $response = $this->apiRequest('POST', '/deactivate', [
            'license_key' => $this->app->config->get('license_key'),
            'domain' => $this->app->config->get('domain')
        ]);
        
        if ($response['success'] ?? false) {
            $this->app->config->delete('license_key');
            return true;
        }
        
        throw new \Exception($response['message'] ?? 'Erro ao desativar licença');
    }
    
    private function apiRequest(string $method, string $endpoint, array $data = []): array 
    {
        $ch = curl_init($this->apiUrl . $endpoint);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($statusCode === 200) {
            return json_decode($response, true);
        }
        
        throw new \Exception('Erro na requisição: ' . $response);
    }
} 