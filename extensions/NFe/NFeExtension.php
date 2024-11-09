<?php
namespace Extensions\NFe;

use App\Extensions\BaseExtension;
use App\Extensions\ExtensionType;

class NFeExtension extends BaseExtension 
{
    public function getName(): string 
    {
        return 'Nota Fiscal Eletrônica';
    }
    
    public function getDescription(): string 
    {
        return 'Emissão e gerenciamento de Notas Fiscais Eletrônicas (NF-e e NFC-e)';
    }
    
    public function getType(): ExtensionType 
    {
        return ExtensionType::BILLING;
    }
    
    public function getSettings(): array 
    {
        return [
            'certificado' => [
                'title' => 'Certificado Digital',
                'fields' => [
                    'arquivo' => [
                        'type' => 'file',
                        'label' => 'Certificado A1',
                        'accept' => '.pfx'
                    ],
                    'senha' => [
                        'type' => 'password',
                        'label' => 'Senha do Certificado'
                    ]
                ]
            ],
            'ambiente' => [
                'title' => 'Ambiente',
                'fields' => [
                    'producao' => [
                        'type' => 'boolean',
                        'label' => 'Ambiente de Produção',
                        'default' => false
                    ]
                ]
            ]
        ];
    }
    
    public function getMenuItems(): array 
    {
        return [
            [
                'title' => 'Notas Fiscais',
                'icon' => 'fas fa-file-invoice',
                'children' => [
                    [
                        'title' => 'Emitir NF-e',
                        'route' => 'nfe/create'
                    ],
                    [
                        'title' => 'Consultar NF-e',
                        'route' => 'nfe/index'
                    ],
                    [
                        'title' => 'Cancelamentos',
                        'route' => 'nfe/cancellations'
                    ]
                ]
            ]
        ];
    }
} 