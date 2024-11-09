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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'create_directories') {
                try {
                    $base_path = dirname(dirname(__DIR__));
                    
                    // Lista de diretórios para criar
                    $directories = [
                        '/storage',
                        '/storage/logs',
                        '/storage/cache',
                        '/storage/uploads',
                        '/storage/templates',
                        '/public/uploads',
                        '/config'
                    ];
                    
                    foreach ($directories as $dir) {
                        $full_path = $base_path . $dir;
                        if (!file_exists($full_path)) {
                            mkdir($full_path, 0755, true);
                        }
                        chmod($full_path, 0755);
                    }
                    
                    // Redirecionar para o próximo passo
                    header('Location: ?step=database');
                    exit;
                    
                } catch (Exception $e) {
                    $this->errors[] = "Erro ao criar diretórios: " . $e->getMessage();
                }
            }
        }
        
        switch ($this->step) {
            case 'welcome':
                // Criar diretórios automaticamente ao iniciar
                $result = $this->createDirectories();
                if ($result['success']) {
                    $this->step = 'database';
                }
                break;
                
            case 'database':
                // Configuração do banco de dados
                if ($this->testDatabaseConnection($_POST)) {
                    $_SESSION['db_config'] = $_POST;
                    $this->step = 'settings';
                }
                break;
                
            case 'settings':
                // Configuração do administrador
                if ($this->validateAdminData($_POST)) {
                    $_SESSION['admin_config'] = $_POST;
                    $this->step = 'final';
                }
                break;
                
            case 'final':
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
            // Criar diretórios necessários
            if (!$this->createDirectories()) {
                throw new Exception("Falha ao criar diretórios necessários");
            }
            
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
            
            $this->log("Database structure created");
            
            // Criar usuário administrador
            $adminConfig = $_SESSION['admin_config'];
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
            $stmt->execute([
                $adminConfig['admin_name'],
                $adminConfig['admin_email'],
                password_hash($adminConfig['admin_password'], PASSWORD_DEFAULT)
            ]);
            
            $this->log("Admin user created");
            
            // Criar arquivo de configuração
            $config = [
                'app_name' => $_SESSION['settings']['app_name'],
                'app_url' => $_SESSION['settings']['app_url'],
                'app_version' => '1.0.0',
                'app_environment' => 'production',
                
                'db' => [
                    'host' => $dbConfig['host'],
                    'port' => $dbConfig['port'],
                    'name' => $dbConfig['name'],
                    'user' => $dbConfig['user'],
                    'pass' => $dbConfig['pass'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci'
                ],
                
                // Email opcional
                'mail' => [
                    'enabled' => false,
                    'driver' => 'smtp',
                    'host' => '',
                    'port' => 587,
                    'username' => '',
                    'password' => '',
                    'encryption' => 'tls',
                    'from_name' => $_SESSION['settings']['app_name'],
                    'from_email' => ''
                ],
                
                // Diretórios do sistema
                'paths' => [
                    'uploads' => 'public/uploads',
                    'cache' => 'storage/cache',
                    'logs' => 'storage/logs'
                ],
                
                // Configurações de segurança
                'security' => [
                    'encryption_key' => bin2hex(random_bytes(16)),
                    'session_lifetime' => 120,
                    'password_timeout' => 10800
                ]
            ];
            
            $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
            
            if (!file_put_contents(BASE_PATH . '/config/config.php', $configContent)) {
                throw new Exception('Não foi possível criar o arquivo de configuração.');
            }
            
            $this->log("Configuration file created");
            
            return true;
            
        } catch (Exception $e) {
            $this->log("Installation failed: " . $e->getMessage(), 'ERROR');
            $this->errors[] = "Erro na instalação: " . $e->getMessage();
            return false;
        }
    }
    
    private function getBaseUrl(): string 
    {
        // Detecta se está usando HTTPS
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
                    $_SERVER['SERVER_PORT'] == 443 || 
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
                    ? 'https' : 'http';
        
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname(dirname($_SERVER['SCRIPT_NAME']));
        
        // Remove barras duplicadas
        $path = rtrim($path, '/');
        
        return "$protocol://$host$path";
    }
    
    public function render() 
    {
        $data = [];
        
        switch ($this->step) {
            case 'permissions':
                $data = $this->checkPermissions();
                break;
            // ... outros cases ...
        }
        
        include "views/header.php";
        
        if ($this->success) {
            include "views/success.php";
        } else {
            include "views/step{$this->step}.php";
        }
        
        include "views/footer.php";
    }
    
    private function createDirectories(): array 
    {
        $base_path = dirname(dirname(__DIR__)); // Volta para a raiz do projeto
        $directories = [
            'storage' => [
                'path' => $base_path . '/storage',
                'required' => '0755'
            ],
            'storage/logs' => [
                'path' => $base_path . '/storage/logs',
                'required' => '0755'
            ],
            'storage/cache' => [
                'path' => $base_path . '/storage/cache',
                'required' => '0755'
            ],
            'storage/uploads' => [
                'path' => $base_path . '/storage/uploads',
                'required' => '0755'
            ],
            'storage/templates' => [
                'path' => $base_path . '/storage/templates',
                'required' => '0755'
            ],
            'public/uploads' => [
                'path' => $base_path . '/public/uploads',
                'required' => '0755'
            ],
            'config' => [
                'path' => $base_path . '/config',
                'required' => '0755'
            ]
        ];

        $results = [];
        $success = true;

        foreach ($directories as $name => $info) {
            try {
                // Criar diretório com permissões
                if (!file_exists($info['path'])) {
                    mkdir($info['path'], octdec($info['required']), true);
                }
                
                // Garantir permissões mesmo se o diretório já existir
                chmod($info['path'], octdec($info['required']));
                
                // Criar .htaccess para diretórios sensíveis
                if (strpos($name, 'storage') !== false) {
                    $htaccess = $info['path'] . '/.htaccess';
                    if (!file_exists($htaccess)) {
                        file_put_contents($htaccess, "Deny from all\n");
                    }
                }

                $results[] = [
                    'directory' => $name,
                    'path' => $info['path'],
                    'status' => true,
                    'message' => 'Diretório criado com sucesso'
                ];

            } catch (Exception $e) {
                $success = false;
                $results[] = [
                    'directory' => $name,
                    'path' => $info['path'],
                    'status' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        if ($success) {
            $this->step = 'database'; // Avança para o próximo passo
            $this->log("Diretórios criados com sucesso");
        } else {
            $this->errors[] = "Falha ao criar alguns diretórios";
            $this->log("Falha ao criar diretórios", 'ERROR');
        }

        return [
            'success' => $success,
            'results' => $results
        ];
    }
    
    private function checkPermissions(): array 
    {
        $permissions = [];
        $base_path = dirname(dirname(__DIR__)); // Volta para a raiz do projeto
        
        // Lista de diretórios que precisam ser verificados
        $directories = [
            'storage' => [
                'path' => $base_path . '/storage',
                'required' => '0755'
            ],
            'storage/logs' => [
                'path' => $base_path . '/storage/logs',
                'required' => '0755'
            ],
            'storage/cache' => [
                'path' => $base_path . '/storage/cache',
                'required' => '0755'
            ],
            'storage/uploads' => [
                'path' => $base_path . '/storage/uploads',
                'required' => '0755'
            ],
            'storage/templates' => [
                'path' => $base_path . '/storage/templates',
                'required' => '0755'
            ],
            'public/uploads' => [
                'path' => $base_path . '/public/uploads',
                'required' => '0755'
            ],
            'config' => [
                'path' => $base_path . '/config',
                'required' => '0755'
            ]
        ];
        
        foreach ($directories as $name => $info) {
            // Criar diretório se não existir
            if (!file_exists($info['path'])) {
                @mkdir($info['path'], octdec($info['required']), true);
            }
            
            // Verificar permissões
            $current = substr(sprintf('%o', fileperms($info['path'])), -4);
            
            $permissions[] = [
                'directory' => $name,
                'path' => $info['path'],
                'current' => $current,
                'required' => $info['required'],
                'writable' => is_writable($info['path'])
            ];
        }
        
        // Verificar se todas as permissões estão corretas
        $can_continue = !in_array(false, array_column($permissions, 'writable'));
        
        return [
            'permissions' => $permissions,
            'can_continue' => $can_continue
        ];
    }

    private function createAdminUser($pdo) 
    {
        try {
            // Verificar se já existe um usuário admin
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$_SESSION['admin_config']['admin_email']]);
            
            if ($stmt->fetchColumn() > 0) {
                return true; // Usuário já existe
            }

            // Criar usuário admin
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    name, 
                    email, 
                    password, 
                    role, 
                    status,
                    created_at
                ) VALUES (?, ?, ?, 'admin', 1, NOW())
            ");

            $password = password_hash($_SESSION['admin_config']['admin_password'], PASSWORD_DEFAULT);

            $stmt->execute([
                $_SESSION['admin_config']['admin_name'],
                $_SESSION['admin_config']['admin_email'],
                $password
            ]);

            $this->log("Usuário admin criado com sucesso");
            return true;

        } catch (Exception $e) {
            $this->log("Erro ao criar usuário admin: " . $e->getMessage(), 'ERROR');
            $this->errors[] = "Erro ao criar usuário admin: " . $e->getMessage();
            return false;
        }
    }
}

$installer = new Installer();
$installer->render();

private function log($message, $level = 'INFO') 
{
    $logFile = '../logs/installer.log';
    $date = date('Y-m-d H:i:s');
    $logMessage = "$date - $level - $message\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Add logs in the following methods

private function processStep() 
{
    $this->log("Processing step $this->step");
    
    switch ($this->step) {
        case 'welcome':
            // Criar diretórios automaticamente ao iniciar
            $result = $this->createDirectories();
            if ($result['success']) {
                $this->step = 'database';
            }
            break;
            
        case 'database':
            // Configuração do banco de dados
            if ($this->testDatabaseConnection($_POST)) {
                $_SESSION['db_config'] = $_POST;
                $this->step = 'settings';
                $this->log("Database configuration successful");
            } else {
                $this->log("Database configuration failed", 'ERROR');
            }
            break;
            
        case 'settings':
            // Configuração do administrador
            if ($this->validateAdminData($_POST)) {
                $_SESSION['admin_config'] = $_POST;
                $this->step = 'final';
                $this->log("Admin configuration successful");
            } else {
                $this->log("Admin configuration failed", 'ERROR');
            }
            break;
            
        case 'final':
            // Instalação final
            if ($this->install()) {
                $this->success = true;
                $this->log("Installation successful");
            } else {
                $this->log("Installation failed", 'ERROR');
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
        
        $this->log("Database connection successful");
        return true;
    } catch (PDOException $e) {
        $this->log("Database connection failed: " . $e->getMessage(), 'ERROR');
        $this->errors[] = "Erro na conexão com o banco de dados: " . $e->getMessage();
        return false;
    }
}

private function install(): bool 
{
    try {
        // Criar diretórios necessários
        if (!$this->createDirectories()) {
            throw new Exception("Falha ao criar diretórios necessários");
        }
        
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
        
        $this->log("Database structure created");
        
        // Criar usuário administrador
        $adminConfig = $_SESSION['admin_config'];
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
        $stmt->execute([
            $adminConfig['admin_name'],
            $adminConfig['admin_email'],
            password_hash($adminConfig['admin_password'], PASSWORD_DEFAULT)
        ]);
        
        $this->log("Admin user created");
        
        // Criar arquivo de configuração
        $config = [
            'app_name' => $_SESSION['settings']['app_name'],
            'app_url' => $_SESSION['settings']['app_url'],
            'app_version' => '1.0.0',
            'app_environment' => 'production',
            
            'db' => [
                'host' => $dbConfig['host'],
                'port' => $dbConfig['port'],
                'name' => $dbConfig['name'],
                'user' => $dbConfig['user'],
                'pass' => $dbConfig['pass'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ],
            
            // Email opcional
            'mail' => [
                'enabled' => false,
                'driver' => 'smtp',
                'host' => '',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_name' => $_SESSION['settings']['app_name'],
                'from_email' => ''
            ],
            
            // Diretórios do sistema
            'paths' => [
                'uploads' => 'public/uploads',
                'cache' => 'storage/cache',
                'logs' => 'storage/logs'
            ],
            
            // Configurações de segurança
            'security' => [
                'encryption_key' => bin2hex(random_bytes(16)),
                'session_lifetime' => 120,
                'password_timeout' => 10800
            ]
        ];
        
        $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
        
        if (!file_put_contents(BASE_PATH . '/config/config.php', $configContent)) {
            throw new Exception('Não foi possível criar o arquivo de configuração.');
        }
        
        $this->log("Configuration file created");
        
        return true;
        
    } catch (Exception $e) {
        $this->log("Installation failed: " . $e->getMessage(), 'ERROR');
        $this->errors[] = "Erro na instalação: " . $e->getMessage();
        return false;
    }
}