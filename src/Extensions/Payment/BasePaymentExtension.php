<?php
namespace App\Extensions\Payment;

use App\Extensions\BaseExtension;

abstract class BasePaymentExtension extends BaseExtension 
{
    abstract public function getPaymentMethod(): string;
    abstract public function processPayment(array $data): array;
    abstract public function getConfigFields(): array;
    
    public function validateConfig(array $config): bool 
    {
        return true;
    }
    
    public function getCheckoutFields(): array 
    {
        return [];
    }
    
    public function validateCheckoutFields(array $data): bool 
    {
        return true;
    }
    
    public function getAdminTemplate(): string 
    {
        return '';
    }
    
    public function getCheckoutTemplate(): string 
    {
        return '';
    }
} 