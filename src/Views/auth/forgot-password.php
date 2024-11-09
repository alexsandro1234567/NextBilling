<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - <?php echo $config['app']['name']; ?></title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Recuperar Senha</h1>
                <p>Digite seu email para receber as instruções</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="?route=forgot-password" class="auth-form">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required 
                           class="form-control" autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Enviar Instruções
                </button>
            </form>

            <div class="auth-footer">
                <a href="?route=login">Voltar para o login</a>
            </div>
        </div>
    </div>
</body>
</html> 