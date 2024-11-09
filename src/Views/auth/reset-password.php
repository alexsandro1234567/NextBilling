<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - <?php echo $config['app']['name']; ?></title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Redefinir Senha</h1>
                <p>Digite sua nova senha</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="?route=reset-password&token=<?php echo $token; ?>" 
                  class="auth-form">
                <div class="form-group">
                    <label for="password">Nova Senha:</label>
                    <input type="password" id="password" name="password" 
                           required class="form-control" autofocus>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar Senha:</label>
                    <input type="password" id="password_confirm" 
                           name="password_confirm" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Redefinir Senha
                </button>
            </form>

            <div class="auth-footer">
                <a href="?route=login">Voltar para o login</a>
            </div>
        </div>
    </div>
</body>
</html> 