<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema ERP</title>
    <link rel="stylesheet" href="assets/css/install.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Instalação do Sistema ERP</h1>
            <div class="steps">
                <div class="step <?php echo $this->step >= 1 ? 'active' : ''; ?>">1. Requisitos</div>
                <div class="step <?php echo $this->step >= 2 ? 'active' : ''; ?>">2. Banco de Dados</div>
                <div class="step <?php echo $this->step >= 3 ? 'active' : ''; ?>">3. Administrador</div>
                <div class="step <?php echo $this->step >= 4 ? 'active' : ''; ?>">4. Finalização</div>
            </div>
        </header>
        
        <?php if (!empty($this->errors)): ?>
            <div class="errors">
                <?php foreach ($this->errors as $error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?> 