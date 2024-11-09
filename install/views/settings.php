<?php 
$errors = $data['errors'] ?? [];
$old = $data['old'] ?? [];
$default = $data['default'] ?? [];
?>

<div class="settings-config">
    <h4 class="text-center mb-4">
        <i class="fas fa-cog text-primary"></i>
        Configurações do Sistema
    </h4>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Corrija os erros abaixo:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="?step=settings" class="settings-form">
        <div class="form-group">
            <label for="app_name">
                <i class="fas fa-store mr-1"></i>
                Nome da Aplicação
            </label>
            <input type="text" 
                   class="form-control <?php echo isset($errors['app_name']) ? 'is-invalid' : ''; ?>" 
                   id="app_name" 
                   name="app_name" 
                   value="<?php echo $old['app_name'] ?? $default['app_name']; ?>" 
                   required>
            <small class="form-text text-muted">
                Este nome será exibido no título do site e emails.
            </small>
        </div>

        <div class="form-group">
            <label for="app_url">
                <i class="fas fa-globe mr-1"></i>
                URL do Site
            </label>
            <input type="url" 
                   class="form-control <?php echo isset($errors['app_url']) ? 'is-invalid' : ''; ?>" 
                   id="app_url" 
                   name="app_url" 
                   value="<?php echo $old['app_url'] ?? $default['app_url']; ?>" 
                   required>
            <small class="form-text text-muted">
                URL completa do seu site, sem barra no final.
            </small>
        </div>

        <div class="form-group">
            <label for="admin_email">
                <i class="fas fa-envelope mr-1"></i>
                Email do Administrador
            </label>
            <input type="email" 
                   class="form-control <?php echo isset($errors['admin_email']) ? 'is-invalid' : ''; ?>" 
                   id="admin_email" 
                   name="admin_email" 
                   value="<?php echo $old['admin_email'] ?? $default['admin_email']; ?>" 
                   required>
            <small class="form-text text-muted">
                Este será o email de acesso ao painel administrativo.
            </small>
        </div>

        <div class="form-group">
            <label for="admin_password">
                <i class="fas fa-key mr-1"></i>
                Senha do Administrador
            </label>
            <div class="input-group">
                <input type="password" 
                       class="form-control <?php echo isset($errors['admin_password']) ? 'is-invalid' : ''; ?>" 
                       id="admin_password" 
                       name="admin_password" 
                       value="<?php echo $old['admin_password'] ?? $default['admin_password']; ?>" 
                       required>
                <div class="input-group-append">
                    <button type="button" 
                            class="btn btn-outline-secondary" 
                            onclick="togglePassword('admin_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <small class="form-text text-muted">
                Mínimo de 6 caracteres. Use uma senha forte!
            </small>
        </div>

        <div class="row justify-content-between mt-4">
            <div class="col-auto">
                <a href="?step=database" class="btn btn-default">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    Instalar Sistema
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="alert alert-info mt-4">
        <h5>
            <i class="fas fa-info-circle mr-2"></i>
            Importante:
        </h5>
        <ul class="mb-0">
            <li>Guarde o email e senha em um local seguro</li>
            <li>Você usará estas credenciais para acessar o painel</li>
            <li>A URL deve ser a mesma que você usa para acessar o site</li>
        </ul>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const type = input.type === 'password' ? 'text' : 'password';
    input.type = type;
    
    const icon = event.currentTarget.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}
</script> 