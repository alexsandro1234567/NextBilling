<?php
namespace App\Modules\Marketplace;

class CloudIntegration 
{
    private $providers = [
        'aws' => AWSProvider::class,
        'gcloud' => GoogleCloudProvider::class,
        'azure' => AzureProvider::class
    ];
    
    public function connect($provider, $credentials) 
    {
        if (!isset($this->providers[$provider])) {
            throw new \Exception('Provedor não suportado');
        }
        
        $providerClass = $this->providers[$provider];
        return new $providerClass($credentials);
    }
} 