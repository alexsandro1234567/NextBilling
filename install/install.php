<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Installer 
{
    private $step = 1;
    private $errors = [];
    private $success = false;
    
    public function __construct() 
    {
        $this->step = $_GET['step'] ?? 1;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processStep();
        }
    }
    
    private function checkRequirements(): array 
    {
        $requirements = [];
        
        // Verificar versão PHP
        $requirements['php_version'] = [
            'name' => 'Versão PHP',
            'required' => '8.1.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '8.1.0', '>=')
        ];
        
        // Verificar extensões
        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl'];
        foreach ($extensions as $ext) {
            $requirements['ext_' . $ext] = [
                'name' => 'Extensão ' . strtoupper($ext),
                'required' => 'Instalada',
                'current' => extension_loaded($ext) ? 'Instalada' : 'Não instalada',
                'status' => extension_loaded($ext)
            ];
        }
        
        // Verificar permissões de diretórios
        $directories = [
            '../config',
            '../logs',
            '../uploads',
            '../cache'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $requirements['dir_' . basename($dir)] = [
                'name' => 'Diretório ' . basename($dir),
                'required' => 'Gravável',
                'current' => is_writable($dir) ? 'Gravável' : 'Não gravável',
                'status' => is_writable($dir)
            ];
        }
        
        return $requirements;
    }
    
    private function processStep() 
    {
        switch ($this->step) {
            case 1:
                // Verificação de requisitos já é automática
                $this->step = 2;
                break;
                
            case 2:
                // Configuração do banco de dados
                if ($this->testDatabaseConnection($_POST)) {
                    $_SESSION['db_config'] = $_POST;
                    $this->step = 3;
                }
                break;
                
            case 3:
                // Configuração do administrador
                if ($this->validateAdminData($_POST)) {
                    $_SESSION['admin_config'] = $_POST;
                    $this->step = 4;
                }
                break;
                
            case 4:
                // Instalação final
                if ($this->install()) {
                    $this->success = true;
                }
                break;
        }
    }
    
    private function testDatabaseConnection($data): bool 
    {
        try {
            $pdo = new PDO(
                "mysql:host={$data['db_host']};port={$data['db_port']}",
                $data['db_user'],
                $data['db_pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Tentar criar o banco de dados
            $dbname = $data['db_name'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "Erro na conexão com o banco de dados: " . $e->getMessage();
            return false;
        }
    }
    
    private function validateAdminData($data): bool 
    {
        if (empty($data['admin_name']) || empty($data['admin_email']) || empty($data['admin_password'])) {
            $this->errors[] = "Todos os campos do administrador são obrigatórios";
            return false;
        }
        
        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Email inválido";
            return false;
        }
        
        if (strlen($data['admin_password']) < 8) {
            $this->errors[] = "A senha deve ter no mínimo 8 caracteres";
            return false;
        }
        
        return true;
    }
    
    private function install(): bool 
    {
        try {
            // Criar conexão com o banco
            $dbConfig = $_SESSION['db_config'];
            $pdo = new PDO(
                "mysql:host={$dbConfig['db_host']};port={$dbConfig['db_port']};dbname={$dbConfig['db_name']}",
                $dbConfig['db_user'],
                $dbConfig['db_pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Executar SQL de estrutura
            $sql = file_get_contents('sql/structure.sql');
            $pdo->exec($sql);
            
            // Criar usuário administrador
            $adminConfig = $_SESSION['admin_config'];
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
            $stmt->execute([
                $adminConfig['admin_name'],
                $adminConfig['admin_email'],
                password_hash($adminConfig['admin_password'], PASSWORD_DEFAULT)
            ]);
            
            // Criar arquivo de configuração
            $config = [
                'debug' => false,
                'db' => [
                    'host' => $dbConfig['db_host'],
                    'port' => $dbConfig['db_port'],
                    'name' => $dbConfig['db_name'],
                    'user' => $dbConfig['db_user'],
                    'pass' => $dbConfig['db_pass']
                ],
                'app' => [
                    'name' => 'Sistema ERP',
                    'version' => '1.0.0',
                    'url' => $this->getBaseUrl()
                ],
                'security' => [
                    'salt' => bin2hex(random_bytes(16)),
                    'session_timeout' => 3600
                ]
            ];
            
            file_put_contents(
                '../config/config.php',
                '<?php return ' . var_export($config, true) . ';'
            );
            
            return true;
            
        } catch (Exception $e) {
            $this->errors[] = "Erro na instalação: " . $e->getMessage();
            return false;
        }
    }
    
    private function getBaseUrl(): string 
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname(dirname($_SERVER['SCRIPT_NAME']));
        return "$protocol://$host$path";
    }
    
    public function render() 
    {
        include "views/header.php";
        
        if ($this->success) {
            include "views/success.php";
        } else {
            include "views/step{$this->step}.php";
        }
        
        include "views/footer.php";
    }
}

$installer = new Installer();
$installer->render(); 