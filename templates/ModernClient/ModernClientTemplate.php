<?php
namespace Templates\ModernClient;

use App\Templates\BaseTemplate;

class ModernClientTemplate extends BaseTemplate 
{
    public function getName(): string 
    {
        return 'Modern Client';
    }
    
    public function getDescription(): string 
    {
        return 'Template moderno e responsivo para área do cliente';
    }
    
    public function getVersion(): string 
    {
        return '1.0.0';
    }
    
    public function getAuthor(): string 
    {
        return 'Seu Nome';
    }
    
    public function getScreenshot(): string 
    {
        return 'screenshot.png';
    }
    
    public function getFeatures(): array 
    {
        return [
            'responsive' => 'Design Responsivo',
            'rtl' => 'Suporte a RTL',
            'dark_mode' => 'Modo Escuro',
            'custom_colors' => 'Cores Personalizáveis',
            'widgets' => 'Widgets Personalizáveis'
        ];
    }
    
    public function getSettings(): array 
    {
        return [
            'general' => [
                'title' => 'Configurações Gerais',
                'fields' => [
                    'logo' => [
                        'type' => 'file',
                        'label' => 'Logo',
                        'accept' => 'image/*'
                    ],
                    'favicon' => [
                        'type' => 'file',
                        'label' => 'Favicon',
                        'accept' => 'image/x-icon,image/png'
                    ],
                    'layout' => [
                        'type' => 'select',
                        'label' => 'Layout',
                        'options' => [
                            'default' => 'Default',
                            'boxed' => 'Boxed',
                            'fluid' => 'Fluid'
                        ]
                    ]
                ]
            ],
            'colors' => [
                'title' => 'Cores',
                'fields' => [
                    'primary_color' => [
                        'type' => 'color',
                        'label' => 'Cor Primária',
                        'default' => '#007bff'
                    ],
                    'secondary_color' => [
                        'type' => 'color',
                        'label' => 'Cor Secundária',
                        'default' => '#6c757d'
                    ]
                ]
            ],
            'footer' => [
                'title' => 'Rodapé',
                'fields' => [
                    'footer_text' => [
                        'type' => 'textarea',
                        'label' => 'Texto do Rodapé',
                        'rows' => 3
                    ],
                    'show_social' => [
                        'type' => 'boolean',
                        'label' => 'Mostrar Redes Sociais',
                        'default' => true
                    ]
                ]
            ]
        ];
    }
} 