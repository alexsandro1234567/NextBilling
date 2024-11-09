<?php
namespace App\Marketplace\Vendor;

class VendorManager 
{
    private $app;
    
    public function __construct($app) 
    {
        $this->app = $app;
    }
    
    public function register(array $data): array 
    {
        // Validar dados
        $this->validateVendorData($data);
        
        // Criar conta do desenvolvedor
        $vendorId = $this->app->db->insert('marketplace_vendors', [
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'],
            'website' => $data['website'],
            'description' => $data['description'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Enviar email de confirmação
        $this->sendConfirmationEmail($vendorId, $data['email']);
        
        return [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso! Verifique seu email para confirmar a conta.'
        ];
    }
    
    public function submitExtension(int $vendorId, array $data): array 
    {
        // Validar dados da extensão
        $this->validateExtensionData($data);
        
        // Upload dos arquivos
        $files = $this->handleFileUploads($data['files']);
        
        // Criar extensão
        $extensionId = $this->app->db->insert('marketplace_extensions', [
            'vendor_id' => $vendorId,
            'name' => $data['name'],
            'slug' => $this->generateSlug($data['name']),
            'type' => $data['type'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'version' => $data['version'],
            'requires' => json_encode($data['requires']),
            'price' => $data['price'],
            'status' => 'pending_review',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Salvar arquivos
        foreach ($files as $type => $file) {
            $this->app->db->insert('marketplace_extension_files', [
                'extension_id' => $extensionId,
                'type' => $type,
                'filename' => $file['name'],
                'path' => $file['path'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Extensão enviada para revisão com sucesso!'
        ];
    }
    
    public function getDashboard(int $vendorId): array 
    {
        return [
            'extensions' => $this->getVendorExtensions($vendorId),
            'sales' => $this->getVendorSales($vendorId),
            'earnings' => $this->getVendorEarnings($vendorId),
            'reviews' => $this->getVendorReviews($vendorId)
        ];
    }
} 