<?php
namespace App\Modules\International\Services;

class TaxService 
{
    private $db;
    private $vatService;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->vatService = new VATService();
    }
    
    public function calculateInternationalTaxes($invoice) 
    {
        $customer = $invoice->customer;
        $taxes = [];
        
        // Verificar localização do cliente
        if ($customer->isEUBased()) {
            $taxes['vat'] = $this->calculateEUVAT($invoice);
        }
        
        // Impostos específicos por país
        $countryTaxes = $this->getCountrySpecificTaxes($customer->country_code);
        foreach ($countryTaxes as $tax) {
            $taxes[$tax->code] = $this->calculateTax($invoice, $tax);
        }
        
        return $taxes;
    }
    
    private function calculateEUVAT($invoice) 
    {
        $vatRate = $this->vatService->getVATRate($invoice->customer->country_code);
        return [
            'rate' => $vatRate,
            'amount' => $invoice->subtotal * ($vatRate / 100),
            'details' => [
                'vat_number' => $invoice->customer->vat_number,
                'validated' => $this->vatService->validateVATNumber($invoice->customer->vat_number)
            ]
        ];
    }
} 