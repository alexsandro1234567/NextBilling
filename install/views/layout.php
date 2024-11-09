<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do Sistema</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="install/assets/css/install.css">

    <style>
        .nav-steps {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            padding: 10px;
        }

        .nav-step {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            color: #666;
            border-radius: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .nav-step.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .nav-step i {
            font-size: 14px;
        }

        .nav-step.completed {
            background: #198754;
            color: white;
            border-color: #198754;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Logo -->
        <div class="text-center mb-4">
            <img src="install/assets/img/logo.png" alt="Logo" class="img-fluid" style="max-height: 100px;">
        </div>

        <!-- Menu de passos (não clicável) -->
        <div class="nav-steps">
            <div class="nav-step <?php echo $this->getCurrentStep() === 'welcome' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Bem-vindo</span>
            </div>
            <div class="nav-step <?php echo $this->getCurrentStep() === 'requirements' ? 'active' : ''; ?>">
                <i class="fas fa-server"></i>
                <span>Requisitos</span>
            </div>
            <div class="nav-step <?php echo $this->getCurrentStep() === 'permissions' ? 'active' : ''; ?>">
                <i class="fas fa-lock"></i>
                <span>Permissões</span>
            </div>
            <div class="nav-step <?php echo $this->getCurrentStep() === 'database' ? 'active' : ''; ?>">
                <i class="fas fa-database"></i>
                <span>Banco de Dados</span>
            </div>
            <div class="nav-step <?php echo $this->getCurrentStep() === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Configurações</span>
            </div>
            <div class="nav-step <?php echo $this->getCurrentStep() === 'install' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i>
                <span>Instalação</span>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="content-wrapper">
            <?php 
            $currentStep = $this->getCurrentStep();
            include __DIR__ . "/{$currentStep}.php";
            ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 