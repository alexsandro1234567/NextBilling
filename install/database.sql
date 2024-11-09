-- Criar tabelas principais
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','developer','user') NOT NULL DEFAULT 'user',
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text,
    `parent_id` int(11) DEFAULT NULL,
    `order` int(11) NOT NULL DEFAULT '0',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `type` enum('extension','template') NOT NULL,
    `category_id` int(11) NOT NULL,
    `description` text NOT NULL,
    `short_description` varchar(255) NOT NULL,
    `version` varchar(20) NOT NULL,
    `price` decimal(10,2) NOT NULL DEFAULT '0.00',
    `status` enum('pending','active','rejected') NOT NULL DEFAULT 'pending',
    `downloads` int(11) NOT NULL DEFAULT '0',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `user_id` (`user_id`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `item_versions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `item_id` int(11) NOT NULL,
    `version` varchar(20) NOT NULL,
    `changelog` text NOT NULL,
    `file` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `item_id` (`item_id`),
    CONSTRAINT `item_versions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchases` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `item_id` int(11) NOT NULL,
    `transaction_id` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `status` enum('pending','completed','refunded') NOT NULL DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `item_id` (`item_id`),
    CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir categorias padrão
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Extensões', 'extensions', 'Todas as extensões'),
('Templates', 'templates', 'Todos os templates');

-- Inserir subcategorias de extensões
INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`) VALUES
('Contabilidade & Finanças', 'accounting-finance', 'Extensões para contabilidade e finanças', 1),
('Análise & Relatórios', 'analytics-reports', 'Extensões para análise e relat��rios', 1),
('Faturamento', 'billing-invoicing', 'Extensões para faturamento', 1),
('Ferramentas de Desenvolvimento', 'developer-tools', 'Ferramentas para desenvolvedores', 1),
('Registradores de Domínio', 'domain-registrars', 'Integração com registradores', 1),
('Email & Notificações', 'email-notifications', 'Extensões para email e notificações', 1),
('Localização', 'localization', 'Extensões de localização', 1),
('Ferramentas de Migração', 'migration-tools', 'Ferramentas de migração', 1),
('Gestão de Pedidos', 'order-management', 'Gestão de pedidos', 1),
('Gateways de Pagamento', 'payment-gateways', 'Gateways de pagamento', 1),
('Performance & Segurança', 'performance-security', 'Performance e segurança', 1),
('Produtividade', 'productivity', 'Ferramentas de produtividade', 1),
('Módulos de Provisionamento', 'provisioning', 'Módulos de provisionamento', 1),
('Ferramentas de Suporte', 'support-tools', 'Ferramentas de suporte', 1),
('Utilitários', 'utilities', 'Utilitários diversos', 1);

-- Inserir subcategorias de templates
INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`) VALUES
('Admin', 'admin-templates', 'Templates para área administrativa', 2),
('Cliente', 'client-templates', 'Templates para área do cliente', 2),
('Landing Pages', 'landing-templates', 'Templates para landing pages', 2),
('Emails', 'email-templates', 'Templates para emails', 2); 