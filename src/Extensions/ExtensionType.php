<?php
namespace App\Extensions;

enum ExtensionType: string 
{
    case ACCOUNTING = 'accounting';
    case ANALYTICS = 'analytics';
    case BILLING = 'billing';
    case DEVELOPER = 'developer';
    case DOMAIN = 'domain';
    case EMAIL = 'email';
    case LOCALIZATION = 'localization';
    case MIGRATION = 'migration';
    case ORDER = 'order';
    case PAYMENT = 'payment';
    case SECURITY = 'security';
    case PRODUCTIVITY = 'productivity';
    case PROVISIONING = 'provisioning';
    case SUPPORT = 'support';
    case TEMPLATE = 'template';
    case UTILITY = 'utility';
    case HOSTING = 'hosting';
    case OTHER = 'other';
    
    public function getLabel(): string 
    {
        return match($this) {
            self::ACCOUNTING => 'Contabilidade & Finanças',
            self::ANALYTICS => 'Analytics & Relatórios',
            self::BILLING => 'Faturamento & Notas Fiscais',
            self::DEVELOPER => 'Ferramentas para Desenvolvedores',
            self::DOMAIN => 'Registradores de Domínio',
            self::EMAIL => 'Email & Notificações',
            self::LOCALIZATION => 'Localização',
            self::MIGRATION => 'Ferramentas de Migração',
            self::ORDER => 'Gestão de Pedidos',
            self::PAYMENT => 'Gateways de Pagamento',
            self::SECURITY => 'Performance & Segurança',
            self::PRODUCTIVITY => 'Produtividade',
            self::PROVISIONING => 'Módulos de Provisionamento',
            self::SUPPORT => 'Ferramentas de Suporte',
            self::TEMPLATE => 'Templates & Temas',
            self::UTILITY => 'Utilitários',
            self::HOSTING => 'Hospedagem Web',
            self::OTHER => 'Outros'
        };
    }
} 