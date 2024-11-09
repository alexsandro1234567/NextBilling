<?php
namespace App\Modules\Subscription\Services;

class SubscriptionManager 
{
    private $db;
    private $billingService;
    private $notificationService;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->billingService = new BillingService();
        $this->notificationService = new NotificationService();
    }
    
    public function createSubscription(array $data): Subscription 
    {
        try {
            $this->db->beginTransaction();
            
            // Validar plano e cliente
            $plan = Plan::findOrFail($data['plan_id']);
            $customer = Customer::findOrFail($data['customer_id']);
            
            // Calcular valores proporcionais se necessário
            $proRatedAmount = $this->calculateProRatedAmount($plan, $data['start_date']);
            
            // Criar assinatura
            $subscription = new Subscription([
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'start_date' => $data['start_date'],
                'billing_cycle' => $plan->billing_cycle,
                'status' => 'active',
                'next_billing_date' => $this->calculateNextBillingDate($data['start_date'], $plan->billing_cycle),
                'amount' => $proRatedAmount ?: $plan->price
            ]);
            
            $subscription->save();
            
            // Criar primeira fatura
            $this->billingService->createInvoice($subscription);
            
            // Notificar cliente
            $this->notificationService->sendSubscriptionConfirmation($subscription);
            
            $this->db->commit();
            return $subscription;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function handleUpgrade(Subscription $subscription, Plan $newPlan) 
    {
        try {
            $this->db->beginTransaction();
            
            // Calcular créditos do plano atual
            $currentCredits = $this->calculateRemainingCredits($subscription);
            
            // Calcular valor proporcional do novo plano
            $newAmount = $this->calculateUpgradeAmount($newPlan, $currentCredits);
            
            // Atualizar assinatura
            $subscription->update([
                'plan_id' => $newPlan->id,
                'amount' => $newAmount,
                'upgraded_at' => now()
            ]);
            
            // Gerar fatura de upgrade se necessário
            if ($newAmount > 0) {
                $this->billingService->createUpgradeInvoice($subscription, $newAmount);
            }
            
            $this->db->commit();
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
} 