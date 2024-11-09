<?php
namespace App\Modules\International\Controllers;

class InternationalController extends BaseController 
{
    private $currencyService;
    private $taxService;
    
    public function __construct() 
    {
        parent::__construct();
        $this->currencyService = new CurrencyService();
        $this->taxService = new TaxService();
    }
    
    public function convertCurrency() 
    {
        try {
            $amount = $_POST['amount'] ?? 0;
            $from = $_POST['from_currency'] ?? '';
            $to = $_POST['to_currency'] ?? '';
            
            if (!$amount || !$from || !$to) {
                throw new ValidationException('Dados inválidos');
            }
            
            $converted = $this->currencyService->convertAmount($amount, $from, $to);
            
            return $this->json([
                'success' => true,
                'amount' => $converted,
                'rate' => $this->currencyService->getRate($from, $to)
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function calculateTaxes() 
    {
        try {
            $invoiceId = $_POST['invoice_id'] ?? null;
            if (!$invoiceId) {
                throw new ValidationException('ID da fatura é obrigatório');
            }
            
            $invoice = Invoice::findOrFail($invoiceId);
            $taxes = $this->taxService->calculateInternationalTaxes($invoice);
            
            return $this->json([
                'success' => true,
                'taxes' => $taxes
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 