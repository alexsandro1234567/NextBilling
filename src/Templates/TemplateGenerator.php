<?php
namespace App\Templates;

class TemplateGenerator 
{
    private $basePath;
    private $templateName;
    private $templateData;
    
    public function __construct(string $basePath) 
    {
        $this->basePath = $basePath;
    }
    
    public function generate(string $templateName, array $data = []): bool 
    {
        $this->templateName = $templateName;
        $this->templateData = array_merge([
            'name' => $templateName,
            'description' => 'Template Description',
            'version' => '1.0.0',
            'author' => 'Your Name',
            'website' => 'https://example.com'
        ], $data);
        
        try {
            // Criar estrutura de diretórios
            $this->createDirectoryStructure();
            
            // Gerar arquivos base
            $this->generateBaseFiles();
            
            return true;
            
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar template: ' . $e->getMessage());
        }
    }
    
    private function createDirectoryStructure(): void 
    {
        $directories = [
            'assets/css',
            'assets/js',
            'assets/img',
            'assets/fonts',
            'layouts',
            'views/client',
            'views/admin',
            'views/client/home',
            'views/client/services',
            'views/client/invoices',
            'views/client/tickets',
            'views/client/downloads',
            'views/client/affiliates',
            'views/client/profile',
            'views/admin/settings',
            'widgets',
            'includes',
            'language/portuguese-br',
            'language/english'
        ];
        
        $templatePath = $this->basePath . '/templates/' . $this->templateName;
        
        foreach ($directories as $dir) {
            $path = $templatePath . '/' . $dir;
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Não foi possível criar o diretório: {$path}");
            }
        }
    }
    
    private function generateBaseFiles(): void 
    {
        // Gerar arquivo principal do template
        $this->generateTemplateClass();
        
        // Gerar arquivo de configuração
        $this->generateConfigFile();
        
        // Gerar README
        $this->generateReadme();
        
        // Gerar arquivos CSS base
        $this->generateCssFiles();
        
        // Gerar arquivos JS base
        $this->generateJsFiles();
        
        // Gerar layouts base
        $this->generateLayoutFiles();
        
        // Gerar arquivos de idioma
        $this->generateLanguageFiles();
    }
    
    private function generateTemplateClass(): void 
    {
        $content = <<<PHP
<?php
namespace Templates\\{$this->templateName};

use App\Templates\BaseTemplate;

class {$this->templateName}Template extends BaseTemplate 
{
    public function getName(): string 
    {
        return '{$this->templateData['name']}';
    }
    
    public function getDescription(): string 
    {
        return '{$this->templateData['description']}';
    }
    
    public function getVersion(): string 
    {
        return '{$this->templateData['version']}';
    }
    
    public function getAuthor(): string 
    {
        return '{$this->templateData['author']}';
    }
    
    public function getScreenshot(): string 
    {
        return 'assets/img/screenshot.png';
    }
    
    public function getFeatures(): array 
    {
        return [
            'responsive' => 'Design Responsivo',
            'rtl' => 'Suporte a RTL',
            'dark_mode' => 'Modo Escuro'
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
                        'label' => 'Logo'
                    ],
                    'layout' => [
                        'type' => 'select',
                        'label' => 'Layout',
                        'options' => [
                            'default' => 'Default',
                            'boxed' => 'Boxed'
                        ]
                    ]
                ]
            ]
        ];
    }
}
PHP;

        $file = $this->basePath . '/templates/' . $this->templateName . '/' . $this->templateName . 'Template.php';
        file_put_contents($file, $content);
    }
    
    private function generateConfigFile(): void 
    {
        $content = <<<PHP
<?php
return [
    'name' => '{$this->templateData['name']}',
    'description' => '{$this->templateData['description']}',
    'version' => '{$this->templateData['version']}',
    'author' => '{$this->templateData['author']}',
    'website' => '{$this->templateData['website']}',
    'requires' => [
        'php' => '>=8.1',
        'core' => '>=1.0.0'
    ],
    'settings' => [
        'layout' => 'default',
        'rtl' => false,
        'dark_mode' => false
    ]
];
PHP;

        $file = $this->basePath . '/templates/' . $this->templateName . '/config.php';
        file_put_contents($file, $content);
    }
    
    private function generateReadme(): void 
    {
        $content = <<<MD
# {$this->templateData['name']}

{$this->templateData['description']}

## Features

- Design Responsivo
- Suporte a RTL
- Modo Escuro
- Cores Personalizáveis
- Widgets Personalizáveis

## Instalação

1. Faça upload dos arquivos para a pasta `templates/{$this->templateName}`
2. Acesse o painel administrativo
3. Vá em Aparência > Templates
4. Ative o template

## Configuração

Acesse Aparência > Templates > Configurações para personalizar o template.

## Suporte

Para suporte, entre em contato através do site: {$this->templateData['website']}

## Licença

Este template está licenciado sob a licença MIT.
MD;

        $file = $this->basePath . '/templates/' . $this->templateName . '/README.md';
        file_put_contents($file, $content);
    }
    
    private function generateCssFiles(): void 
    {
        // style.css
        $css = <<<CSS
/* 
Template: {$this->templateData['name']}
Version: {$this->templateData['version']}
Author: {$this->templateData['author']}
*/

/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Layout */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Header */
.header {
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Footer */
.footer {
    background: #f8f9fa;
    padding: 40px 0;
}
CSS;

        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/assets/css/style.css',
            $css
        );
        
        // dark.css
        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/assets/css/dark.css',
            "/* Dark Mode Styles */\n"
        );
        
        // rtl.css
        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/assets/css/rtl.css',
            "/* RTL Styles */\n"
        );
    }
    
    private function generateJsFiles(): void 
    {
        $js = <<<JS
// Template: {$this->templateData['name']}
// Version: {$this->templateData['version']}

document.addEventListener('DOMContentLoaded', function() {
    // Template initialization
    console.log('{$this->templateData['name']} template initialized');
});
JS;

        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/assets/js/main.js',
            $js
        );
    }
    
    private function generateLayoutFiles(): void 
    {
        // default.php
        $layout = <<<PHP
<!DOCTYPE html>
<html lang="<?php echo \$language; ?>" dir="<?php echo \$rtl ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \$title; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo templateAsset('css/style.css'); ?>">
    <?php if (\$rtl): ?>
        <link rel="stylesheet" href="<?php echo templateAsset('css/rtl.css'); ?>">
    <?php endif; ?>
    <?php if (\$darkMode): ?>
        <link rel="stylesheet" href="<?php echo templateAsset('css/dark.css'); ?>">
    <?php endif; ?>
    
    <?php echo \$head; ?>
</head>
<body class="<?php echo \$bodyClass; ?>">
    <header class="header">
        <?php include templatePath('includes/header.php'); ?>
    </header>
    
    <main class="main">
        <div class="container">
            <?php echo \$content; ?>
        </div>
    </main>
    
    <footer class="footer">
        <?php include templatePath('includes/footer.php'); ?>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo templateAsset('js/main.js'); ?>"></script>
    <?php echo \$footer; ?>
</body>
</html>
PHP;

        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/layouts/default.php',
            $layout
        );
    }
    
    private function generateLanguageFiles(): void 
    {
        // Portuguese
        $pt = <<<PHP
<?php
return [
    'template_name' => '{$this->templateData['name']}',
    'settings' => 'Configurações',
    'general' => 'Geral',
    'layout' => 'Layout',
    'colors' => 'Cores',
    'footer' => 'Rodapé'
];
PHP;

        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/language/portuguese-br/main.php',
            $pt
        );
        
        // English
        $en = <<<PHP
<?php
return [
    'template_name' => '{$this->templateData['name']}',
    'settings' => 'Settings',
    'general' => 'General',
    'layout' => 'Layout',
    'colors' => 'Colors',
    'footer' => 'Footer'
];
PHP;

        file_put_contents(
            $this->basePath . '/templates/' . $this->templateName . '/language/english/main.php',
            $en
        );
    }
} 