<?php
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

class Installer 
{
    private $steps = [
        'welcome' => [
            'title' => 'Bem-vindo',
            'icon' => 'fa-home'
        ],
        'requirements' => [
            'title' => 'Requisitos',
            'icon' => 'fa-server'
        ],
        'permissions' => [
            'title' => 'Permissões',
            'icon' => 'fa-lock'
        ],
        'database' => [
            'title' => 'Banco de Dados',
            'icon' => 'fa-database'
        ],
        'settings' => [
            'title' => 'Configurações',
            'icon' => 'fa-cog'
        ],
        'install' => [
            'title' => 'Instalação',
            'icon' => 'fa-check'
        ]
    ];
    
    private $step = 'welcome';
    
    public function __construct() 
    {
        if (isset($_GET['step']) && array_key_exists($_GET['step'], $this->steps)) {
            $this->step = $_GET['step'];
        }
    }
    
    public function run() 
    {
        $data = $this->getStepData();
        include __DIR__ . '/views/layout.php';
    }
    
    public function getSteps() 
    {
        return $this->steps;
    }
    
    public function getCurrentStep() 
    {
        return $this->step;
    }
    
    private function getStepData() 
    {
        $method = 'get' . ucfirst($this->step) . 'Data';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return [];
    }
    
    private function getRequirementsData() 
    {
        $requirements = [
            'PHP Version >= 8.1' => [
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'current' => PHP_VERSION
            ],
            'PDO Extension' => [
                'status' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Instalado' : 'Não instalado'
            ],
            'PDO MySQL Extension' => [
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Instalado' : 'Não instalado'
            ],
            'Mbstring Extension' => [
                'status' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Instalado' : 'Não instalado'
            ],
            'JSON Extension' => [
                'status' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Instalado' : 'Não instalado'
            ],
            'CURL Extension' => [
                'status' => extension_loaded('curl'),
                'current' => extension_loaded('curl') ? 'Instalado' : 'Não instalado'
            ],
            'ZIP Extension' => [
                'status' => extension_loaded('zip'),
                'current' => extension_loaded('zip') ? 'Instalado' : 'Não instalado'
            ],
            'GD Extension' => [
                'status' => extension_loaded('gd'),
                'current' => extension_loaded('gd') ? 'Instalado' : 'Não instalado'
            ],
            'allow_url_fopen' => [
                'status' => ini_get('allow_url_fopen'),
                'current' => ini_get('allow_url_fopen') ? 'Ativado' : 'Desativado'
            ]
        ];
        
        $can_continue = !in_array(false, array_column($requirements, 'status'));
        
        return [
            'requirements' => $requirements,
            'can_continue' => $can_continue
        ];
    }

    private function getPermissionsData() 
    {
        $directories = [
            'storage' => [
                'path' => BASE_PATH . '/storage',
                'required' => '0755'
            ],
            'storage/logs' => [
                'path' => BASE_PATH . '/storage/logs',
                'required' => '0755'
            ],
            'storage/cache' => [
                'path' => BASE_PATH . '/storage/cache',
                'required' => '0755'
            ],
            'storage/uploads' => [
                'path' => BASE_PATH . '/storage/uploads',
                'required' => '0755'
            ],
            'storage/templates' => [
                'path' => BASE_PATH . '/storage/templates',
                'required' => '0755'
            ],
            'public/uploads' => [
                'path' => BASE_PATH . '/public/uploads',
                'required' => '0755'
            ],
            'config' => [
                'path' => BASE_PATH . '/config',
                'required' => '0755'
            ]
        ];

        $permissions = [];
        foreach ($directories as $name => $info) {
            // Criar diretório se não existir
            if (!file_exists($info['path'])) {
                @mkdir($info['path'], octdec($info['required']), true);
            }

            // Verificar permissões
            $permissions[$name] = [
                'directory' => $name,
                'path' => $info['path'],
                'required' => $info['required'],
                'current' => substr(sprintf('%o', fileperms($info['path'])), -4),
                'writable' => is_writable($info['path'])
            ];
        }

        $can_continue = !in_array(false, array_column($permissions, 'writable'));

        return [
            'permissions' => $permissions,
            'can_continue' => $can_continue
        ];
    }

    private function getDatabaseData() 
    {
        // Se POST, testar conexão
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $host = $_POST['db_host'] ?? '';
            $port = $_POST['db_port'] ?? '3306';
            $name = $_POST['db_name'] ?? '';
            $user = $_POST['db_user'] ?? '';
            $pass = $_POST['db_pass'] ?? '';

            try {
                $dsn = "mysql:host={$host};port={$port}";
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Tentar criar o banco se não existir
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}`");
                
                // Testar conexão com o banco
                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass);
                
                // Salvar dados na sessão
                $_SESSION['db_config'] = [
                    'host' => $host,
                    'port' => $port,
                    'name' => $name,
                    'user' => $user,
                    'pass' => $pass
                ];
                
                // Redirecionar para próxima etapa
                header('Location: ?step=settings');
                exit;
                
            } catch (PDOException $e) {
                return [
                    'error' => 'Erro ao conectar: ' . $e->getMessage(),
                    'old' => $_POST
                ];
            }
        }

        return [
            'default' => [
                'host' => 'localhost',
                'port' => '3306',
                'name' => 'marketplace',
                'user' => 'root',
                'pass' => ''
            ]
        ];
    }

    private function getSettingsData() 
    {
        // Se POST, validar e salvar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            // Validar nome da aplicação
            if (empty($_POST['app_name'])) {
                $errors[] = 'O nome da aplicação é obrigatório';
            }
            
            // Validar URL
            if (empty($_POST['app_url'])) {
                $errors[] = 'A URL do site é obrigatória';
            } elseif (!filter_var($_POST['app_url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'A URL do site é inválida';
            }
            
            // Validar email
            if (empty($_POST['admin_email'])) {
                $errors[] = 'O email do administrador é obrigatório';
            } elseif (!filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'O email do administrador é inválido';
            }
            
            // Validar senha
            if (empty($_POST['admin_password'])) {
                $errors[] = 'A senha do administrador é obrigatória';
            } elseif (strlen($_POST['admin_password']) < 6) {
                $errors[] = 'A senha deve ter no mínimo 6 caracteres';
            }
            
            if (empty($errors)) {
                // Salvar na sessão
                $_SESSION['settings'] = [
                    'app_name' => $_POST['app_name'],
                    'app_url' => rtrim($_POST['app_url'], '/'),
                    'admin_email' => $_POST['admin_email'],
                    'admin_password' => password_hash($_POST['admin_password'], PASSWORD_DEFAULT)
                ];
                
                // Redirecionar para instalação
                header('Location: ?step=install');
                exit;
            }
            
            return [
                'errors' => $errors,
                'old' => $_POST
            ];
        }

        // Valores padrão
        return [
            'default' => [
                'app_name' => 'Marketplace',
                'app_url' => 'http://' . $_SERVER['HTTP_HOST'],
                'admin_email' => '',
                'admin_password' => ''
            ]
        ];
    }

    private function getInstallData() 
    {
        try {
            // Verificar se temos todas as configurações
            if (!isset($_SESSION['db_config']) || !isset($_SESSION['settings'])) {
                throw new Exception('Configurações incompletas. Por favor, volte e preencha todos os dados.');
            }

            $db = $_SESSION['db_config'];
            $settings = $_SESSION['settings'];

            // Conectar ao banco
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']}";
            $pdo = new PDO($dsn, $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Importar SQL
            $sql = file_get_contents(__DIR__ . '/database.sql');
            if (empty($sql)) {
                throw new Exception('Arquivo SQL não encontrado ou vazio.');
            }

            // Executar queries
            $pdo->exec($sql);

            // Criar admin
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, status) 
                VALUES ('Administrador', ?, ?, 'admin', 'active')
            ");
            $stmt->execute([$settings['admin_email'], $settings['admin_password']]);

            // Criar arquivo de configuração
            $config = [
                'app_name' => $settings['app_name'],
                'app_url' => $settings['app_url'],
                'db' => [
                    'host' => $db['host'],
                    'port' => $db['port'],
                    'name' => $db['name'],
                    'user' => $db['user'],
                    'pass' => $db['pass']
                ]
            ];

            $configPath = BASE_PATH . '/config/config.php';
            $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
            
            if (!file_put_contents($configPath, $configContent)) {
                throw new Exception('Não foi possível criar o arquivo de configuração.');
            }

            // Limpar sessão
            unset($_SESSION['db_config'], $_SESSION['settings']);

            return [
                'success' => true,
                'admin_email' => $settings['admin_email'],
                'app_url' => $settings['app_url']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

$installer = new Installer();
$installer->run();