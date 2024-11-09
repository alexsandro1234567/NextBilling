<?php
namespace App\Modules\Finance\Services;

class BillingService 
{
    private $db;
    private $paymentGateway;
    private $taxService;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->paymentGateway = new PaymentGatewayManager();
        $this->taxService = new TaxService();
    }
    
    public function createInvoice($subscription) 
    {
        try {
            $this->db->beginTransaction();
            
            // Calcular impostos
            $taxes = $this->taxService->calculateTaxes($subscription);
            
            // Criar fatura
            $invoice = Invoice::create([
                'customer_id' => $subscription->customer_id,
                'subscription_id' => $subscription->id,
                'amount' => $subscription->amount,
                'tax_amount' => $taxes['total'],
                'total_amount' => $subscription->amount + $taxes['total'],
                'due_date' => $subscription->next_billing_date,
                'status' => 'pending'
            ]);
            
            // Adicionar itens da fatura
            $this->createInvoiceItems($invoice, $subscription, $taxes);
            
            // Processar pagamento automático se configurado
            if ($subscription->auto_pay) {
                $this->processAutomaticPayment($invoice);
            } else {
                // Enviar fatura por email
                $this->sendInvoiceEmail($invoice);
            }
            
            $this->db->commit();
            return $invoice;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
} 