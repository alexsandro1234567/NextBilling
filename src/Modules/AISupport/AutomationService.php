<?php
namespace App\Modules\AISupport;

class AutomationService 
{
    private $db;
    private $logger;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }
    
    public function checkPaymentIssues() 
    {
        $issues = $this->db->query("
            SELECT i.*, c.email 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            WHERE i.status = 'overdue' 
            AND i.automated_check = 0
        ")->fetchAll();
        
        foreach ($issues as $issue) {
            $this->resolvePaymentIssue($issue);
        }
    }
    
    private function resolvePaymentIssue($issue) 
    {
        // Verifica problemas comuns
        $resolution = $this->analyzeIssue($issue);
        
        // Envia email automatizado
        $this->sendAutomatedEmail($issue['email'], $resolution);
        
        // Registra a tentativa
        $this->logger->log('payment_automation', [
            'invoice_id' => $issue['id'],
            'resolution' => $resolution
        ]);
    }
} 