<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir o caminho base corretamente
if (!defined('BASE_PATH')) {
    // Pega o caminho real do servidor
    $base_path = realpath(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
    define('BASE_PATH', $base_path);
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
    
    private $step;
    private $errors = [];
    
    public function __construct() 
    {
        $this->step = $_GET['step'] ?? 'welcome';

        // Sempre tentar criar os diretórios no início
        $this->createInitialDirectories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'force_create_directories') {
                $this->createInitialDirectories();
                header('Location: ?step=permissions');
                exit;
            }
        }
    }
    
    public function run() 
    {
        // Define o mapeamento de steps para views
        $views = [
            'welcome' => 'welcome.php',
            'requirements' => 'requirements.php',
            'permissions' => 'permissions.php',
            'database' => 'database.php',
            'settings' => 'settings.php',
            'install' => 'install.php'
        ];
        
        // Define qual view será carregada
        $viewFile = __DIR__ . '/views/' . ($views[$this->step] ?? 'welcome.php');
        
        // Verifica se o arquivo existe
        if (!file_exists($viewFile)) {
            die('View não encontrada: ' . $viewFile);
        }
        
        // Inclui o layout com a view correta
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
        
        $canContinue = !in_array(false, array_column($requirements, 'status'));
        
        return [
            'requirements' => $requirements,
            'can_continue' => $canContinue
        ];
    }

    private function getPermissionsData() 
    {
        $directories = [
            'storage' => BASE_PATH . '/storage',
            'storage/logs' => BASE_PATH . '/storage/logs',
            'storage/cache' => BASE_PATH . '/storage/cache',
            'storage/uploads' => BASE_PATH . '/storage/uploads',
            'storage/templates' => BASE_PATH . '/storage/templates',
            'public/uploads' => BASE_PATH . '/public/uploads',
            'config' => BASE_PATH . '/config'
        ];

        $permissions = [];
        foreach ($directories as $name => $path) {
            $permissions[] = [
                'directory' => $name,
                'path' => $path,
                'current' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : '0',
                'required' => '0755',
                'writable' => is_writable($path)
            ];
        }

        return [
            'permissions' => $permissions,
            'can_continue' => !in_array(false, array_column($permissions, 'writable')),
            'errors' => $this->errors
        ];
    }

    private function getDatabaseData() 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Pega dados do formulário
                $host = filter_input(INPUT_POST, 'host', FILTER_SANITIZE_STRING);
                $port = filter_input(INPUT_POST, 'port', FILTER_SANITIZE_STRING);
                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
                $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
                $pass = filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW);

                // Habilita exibição de erros para debug
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                // Tenta conectar sem o banco primeiro
                $dsn = "mysql:host={$host};port={$port}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];

                $pdo = new PDO($dsn, $user, $pass, $options);

                // Se conectou, tenta criar o banco
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}`");

                // Testa conexão com o banco criado
                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass, $options);

                return [
                    'success' => true,
                    'message' => 'Conexão estabelecida com sucesso!'
                ];

            } catch (PDOException $e) {
                error_log("Erro PDO: " . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Erro de conexão: ' . $e->getMessage()
                ];
            } catch (Exception $e) {
                error_log("Erro geral: " . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Erro: ' . $e->getMessage()
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Método inválido'
        ];
    }

    public function processDatabase() 
    {
        header('Content-Type: application/json');
        
        try {
            $result = $this->getDatabaseData();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao processar: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Adicione esta função para testar a conexão
    public function testDatabaseConnection($data) 
    {
        try {
            $dsn = "mysql:host={$data['host']};port={$data['port']}";
            $pdo = new PDO($dsn, $data['user'], $data['pass']);
            return true;
        } catch (PDOException $e) {
            return false;
        }
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

    private function createInitialDirectories() 
    {
        // Debug - verificar o caminho base
        error_log("BASE_PATH: " . BASE_PATH);
        
        $directories = [
            'storage',           // Removido a barra inicial
            'storage/logs',
            'storage/cache',
            'storage/uploads',
            'storage/templates',
            'public/uploads',
            'config'
        ];

        foreach ($directories as $dir) {
            $full_path = BASE_PATH . DIRECTORY_SEPARATOR . $dir;
            
            // Debug
            error_log("Tentando criar diretório: " . $full_path);
            
            // Criar diretório se não existir
            if (!file_exists($full_path)) {
                if (!@mkdir($full_path, 0755, true)) {
                    $error = error_get_last();
                    error_log("Erro ao criar diretório: " . $full_path . " - " . ($error['message'] ?? 'Erro desconhecido'));
                    $this->errors[] = "Não foi possível criar o diretório: " . $dir . " (" . ($error['message'] ?? '') . ")";
                    continue;
                }
                error_log("Diretório criado: " . $full_path);
            }
            
            // Forçar permissões
            if (!@chmod($full_path, 0755)) {
                $error = error_get_last();
                error_log("Erro ao definir permissões para: " . $full_path . " - " . ($error['message'] ?? 'Erro desconhecido'));
            }
            
            // Criar .htaccess para diretórios sensíveis
            if (strpos($dir, 'storage') !== false) {
                $htaccess = $full_path . DIRECTORY_SEPARATOR . '.htaccess';
                if (!file_exists($htaccess)) {
                    @file_put_contents($htaccess, "Deny from all\n");
                }
            }
        }

        if (!empty($this->errors)) {
            error_log("Erros encontrados durante a criação dos diretórios: " . print_r($this->errors, true));
        }
    }

    public function handleDatabaseStep() 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->getDatabaseData();
            
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            
            if ($result['success']) {
                $this->redirect('?step=settings');
            }
            
            $data = $result;
        } else {
            $data = $this->getDatabaseData();
        }
        
        return $this->view->render('database', $data);
    }

    private function isAjax() 
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

// Antes de instanciar o instalador, vamos verificar as permissões do diretório atual
error_log("Permissões do diretório atual: " . substr(sprintf('%o', fileperms(BASE_PATH)), -4));
error_log("Usuário atual do PHP: " . get_current_user());
error_log("Diretório atual: " . getcwd());

$installer = new Installer();
$installer->run();