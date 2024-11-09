<?php
namespace App\Modules\Domains\Services;

class DNSManager 
{
    private $providers = [];
    private $cache;
    
    public function __construct() 
    {
        $this->loadProviders();
        $this->cache = new Cache();
    }
    
    public function addRecord($domain, $record) 
    {
        $provider = $this->getProviderForDomain($domain);
        
        $result = $provider->addRecord([
            'domain' => $domain->name,
            'type' => $record['type'],
            'name' => $record['name'],
            'content' => $record['content'],
            'ttl' => $record['ttl'] ?? 3600
        ]);
        
        // Salvar no banco
        DNSRecord::create([
            'domain_id' => $domain->id,
            'type' => $record['type'],
            'name' => $record['name'],
            'content' => $record['content'],
            'ttl' => $record['ttl'] ?? 3600,
            'provider_id' => $result['id']
        ]);
        
        // Limpar cache
        $this->cache->delete("dns_records:{$domain->name}");
        
        return $result;
    }
    
    public function updateNameservers($domain, $nameservers) 
    {
        $registrar = $this->getRegistrarForDomain($domain);
        
        return $registrar->updateNameservers([
            'domain' => $domain->name,
            'nameservers' => $nameservers
        ]);
    }
} 