<?php
namespace Extensions\Analytics;

use App\Extensions\BaseExtension;
use App\Extensions\ExtensionType;

class GoogleAnalyticsExtension extends BaseExtension 
{
    public function getName(): string 
    {
        return 'Google Analytics';
    }
    
    public function getDescription(): string 
    {
        return 'Integração com Google Analytics para análise de dados e relatórios';
    }
    
    public function getType(): ExtensionType 
    {
        return ExtensionType::ANALYTICS;
    }
    
    public function getSettings(): array 
    {
        return [
            'tracking' => [
                'title' => 'Configurações de Rastreamento',
                'fields' => [
                    'tracking_id' => [
                        'type' => 'text',
                        'label' => 'ID de Rastreamento',
                        'placeholder' => 'UA-XXXXX-Y'
                    ],
                    'domain' => [
                        'type' => 'text',
                        'label' => 'Domínio',
                        'placeholder' => 'exemplo.com'
                    ]
                ]
            ]
        ];
    }
} 