<?php
namespace App\Modules\Domains\Services;

class DomainManager 
{
    private $db;
    private $registrars = [];
    private $sslProvider;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->loadRegistrars();
        $this->sslProvider = new SSLProvider();
    }
    
    public function registerDomain($data) 
    {
        try {
            $this->db->beginTransaction();
            
            // Verificar disponibilidade
            if (!$this->checkDomainAvailability($data['domain'])) {
                throw new DomainException('Domínio não está disponível');
            }
            
            // Selecionar registrador com melhor preço
            $registrar = $this->selectBestRegistrar($data['domain']);
            
            // Registrar domínio
            $registrationResult = $registrar->registerDomain([
                'domain' => $data['domain'],
                'period' => $data['period'],
                'nameservers' => $data['nameservers'],
                'contact' => $data['contact']
            ]);
            
            // Salvar no banco
            $domain = Domain::create([
                'name' => $data['domain'],
                'customer_id' => $data['customer_id'],
                'registrar' => $registrar->getName(),
                'registration_date' => now(),
                'expiration_date' => now()->addYears($data['period']),
                'status' => 'active',
                'auto_renew' => $data['auto_renew'] ?? true
            ]);
            
            // Configurar DNS se solicitado
            if (!empty($data['dns_records'])) {
                $this->configureDNS($domain, $data['dns_records']);
            }
            
            // Instalar SSL se solicitado
            if ($data['auto_ssl']) {
                $this->installSSL($domain);
            }
            
            $this->db->commit();
            return $domain;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function installSSL($domain) 
    {
        // Verificar se domínio está apontando corretamente
        if (!$this->validateDomainPointing($domain)) {
            throw new SSLException('Domínio não está apontando corretamente');
        }
        
        // Gerar CSR e Chave
        $ssl = $this->sslProvider->generateCSR($domain);
        
        // Solicitar certificado
        $certificate = $this->sslProvider->requestCertificate($ssl['csr']);
        
        // Instalar certificado
        $this->installCertificate($domain, $certificate);
        
        // Salvar informações
        SSLCertificate::create([
            'domain_id' => $domain->id,
            'provider' => $this->sslProvider->getName(),
            'type' => 'auto',
            'issued_at' => now(),
            'expires_at' => now()->addMonths(3),
            'status' => 'active'
        ]);
    }
    
    private function loadRegistrars() 
    {
        $this->registrars = [
            new NamecheapRegistrar(config('registrars.namecheap')),
            new GoDaddyRegistrar(config('registrars.godaddy')),
            new ResellerClubRegistrar(config('registrars.resellerclub'))
        ];
    }
} 