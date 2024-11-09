<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalação do Sistema</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="install/assets/css/install.css">
</head>
<body class="hold-transition login-page">
    <div class="login-box" style="width: 800px;">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="#" class="h1">
                    <img src="install/assets/img/logo.png" alt="Logo" class="brand-image" style="height: 50px;">
                </a>
            </div>
            <div class="card-body">
                <!-- Steps -->
                <div class="install-steps mb-4">
                    <?php 
                    $currentStepFound = false;
                    foreach ($this->getSteps() as $stepKey => $stepInfo): 
                        $isActive = $stepKey === $this->getCurrentStep();
                        $isCompleted = !$currentStepFound && !$isActive;
                        if ($isActive) $currentStepFound = true;
                    ?>
                        <div class="step <?php echo $isActive ? 'active' : ($isCompleted ? 'completed' : ''); ?>">
                            <div class="step-icon">
                                <i class="fas <?php echo $stepInfo['icon']; ?>"></i>
                            </div>
                            <div class="step-label"><?php echo $stepInfo['title']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Content -->
                <?php include __DIR__ . '/' . $this->getCurrentStep() . '.php'; ?>
            </div>
        </div>

        <div class="text-center mt-3 text-sm text-muted">
            &copy; <?php echo date('Y'); ?> Seu Nome. Todos os direitos reservados.
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- Custom JS -->
    <script src="install/assets/js/install.js"></script>
</body>
</html> 