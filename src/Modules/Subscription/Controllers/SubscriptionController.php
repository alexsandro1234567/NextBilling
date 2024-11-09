<?php
namespace App\Modules\Subscription\Controllers;

class SubscriptionController extends BaseController 
{
    private $subscriptionManager;
    
    public function __construct() 
    {
        parent::__construct();
        $this->subscriptionManager = new SubscriptionManager();
    }
    
    public function create() 
    {
        try {
            $data = $this->validateSubscriptionData($_POST);
            $subscription = $this->subscriptionManager->createSubscription($data);
            
            return $this->json([
                'success' => true,
                'subscription' => $subscription->toArray()
            ]);
            
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $e->getErrors()
            ], 422);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erro ao criar assinatura'
            ], 500);
        }
    }
    
    public function upgrade($id) 
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $newPlan = Plan::findOrFail($_POST['plan_id']);
            
            $this->subscriptionManager->handleUpgrade($subscription, $newPlan);
            
            return $this->json([
                'success' => true,
                'message' => 'Upgrade realizado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 