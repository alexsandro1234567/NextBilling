<?php
namespace App\Modules\International\Services;

class CurrencyService 
{
    private $db;
    private $exchangeRateProvider;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->exchangeRateProvider = new ExchangeRateProvider();
    }
    
    public function convertAmount($amount, $fromCurrency, $toCurrency) 
    {
        // Obter taxa de câmbio atual
        $rate = $this->exchangeRateProvider->getRate($fromCurrency, $toCurrency);
        
        // Aplicar taxa de conversão
        $convertedAmount = $amount * $rate;
        
        // Registrar conversão para auditoria
        $this->logCurrencyConversion([
            'original_amount' => $amount,
            'original_currency' => $fromCurrency,
            'converted_amount' => $convertedAmount,
            'target_currency' => $toCurrency,
            'rate' => $rate
        ]);
        
        return $convertedAmount;
    }
    
    public function updateExchangeRates() 
    {
        $currencies = $this->getActiveCurrencies();
        
        foreach ($currencies as $currency) {
            try {
                $rates = $this->exchangeRateProvider->getRatesForCurrency($currency->code);
                $this->saveExchangeRates($currency->code, $rates);
            } catch (\Exception $e) {
                $this->logError('exchange_rate_update', $e->getMessage());
            }
        }
    }
} 