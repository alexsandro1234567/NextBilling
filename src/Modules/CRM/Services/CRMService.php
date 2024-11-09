<?php
namespace App\Modules\CRM\Services;

class CRMService 
{
    private $db;
    private $analyticsService;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->analyticsService = new CustomerAnalyticsService();
    }
    
    public function createLead($data) 
    {
        try {
            $this->db->beginTransaction();
            
            // Verificar duplicatas
            if ($this->checkDuplicate($data['email'])) {
                throw new \Exception('Lead já existe');
            }
            
            // Criar lead
            $lead = Lead::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'source' => $data['source'],
                'status' => 'new',
                'assigned_to' => $this->assignLeadToAgent()
            ]);
            
            // Registrar atividade
            $this->logActivity($lead->id, 'lead_created');
            
            // Iniciar workflow automático
            $this->startLeadWorkflow($lead);
            
            $this->db->commit();
            return $lead;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function analyzeCustomerSentiment($customerId) 
    {
        // Coletar todas as interações
        $interactions = $this->getCustomerInteractions($customerId);
        
        // Analisar sentimento usando IA
        $sentiment = $this->analyticsService->analyzeSentiment($interactions);
        
        // Atualizar perfil do cliente
        $this->updateCustomerProfile($customerId, ['sentiment_score' => $sentiment]);
        
        return $sentiment;
    }
} 