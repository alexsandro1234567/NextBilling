<?php
namespace Extensions\PagSeguro;

use App\Extensions\Payment\BasePaymentExtension;
use App\Extensions\ExtensionType;

class PagSeguroExtension extends BasePaymentExtension 
{
    public function getName(): string 
    {
        return 'PagSeguro';
    }
    
    public function getDescription(): string 
    {
        return 'Integração completa com PagSeguro para pagamentos via cartão, boleto e PIX';
    }
    
    public function getVersion(): string 
    {
        return '1.0.0';
    }
    
    public function getAuthor(): string 
    {
        return 'Seu Nome';
    }
    
    public function getType(): ExtensionType 
    {
        return ExtensionType::PAYMENT;
    }
    
    public function getIcon(): string 
    {
        return 'fas fa-credit-card';
    }
    
    public function getRequirements(): array 
    {
        return [
            'php' => '>=8.1',
            'extensions' => [
                'curl',
                'json'
            ]
        ];
    }
    
    public function getScreenshots(): array 
    {
        return [
            'checkout.png' => 'Tela de Checkout',
            'config.png' => 'Configurações do Módulo'
        ];
    }
    
    public function getDocumentationUrl(): ?string 
    {
        return 'https://docs.exemplo.com/pagseguro';
    }
    
    public function getPrice(): ?float 
    {
        return 99.90;
    }
    
    public function getSettings(): array 
    {
        return [
            'credentials' => [
                'title' => 'Credenciais',
                'fields' => [
                    'email' => [
                        'type' => 'text',
                        'label' => 'E-mail',
                        'required' => true
                    ],
                    'token' => [
                        'type' => 'password',
                        'label' => 'Token',
                        'required' => true
                    ]
                ]
            ],
            'config' => [
                'title' => 'Configurações',
                'fields' => [
                    'sandbox' => [
                        'type' => 'boolean',
                        'label' => 'Ambiente de Testes',
                        'default' => false
                    ],
                    'methods' => [
                        'type' => 'multiselect',
                        'label' => 'Métodos de Pagamento',
                        'options' => [
                            'credit' => 'Cartão de Crédito',
                            'boleto' => 'Boleto',
                            'pix' => 'PIX'
                        ],
                        'default' => ['credit', 'boleto', 'pix']
                    ]
                ]
            ]
        ];
    }
} 