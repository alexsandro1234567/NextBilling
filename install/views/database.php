<?php 
$error = $data['error'] ?? null;
$old = $data['old'] ?? [];
$default = $data['default'] ?? [];
?>

<div class="database-config">
    <h4 class="text-center mb-4">
        <i class="fas fa-database text-primary"></i>
        Configuração do Banco de Dados
    </h4>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="?step=database" class="database-form">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="db_host">
                        <i class="fas fa-server mr-1"></i>
                        Host
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="db_host" 
                           name="db_host" 
                           value="<?php echo $old['db_host'] ?? $default['host']; ?>" 
                           required>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="db_port">
                        <i class="fas fa-plug mr-1"></i>
                        Porta
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="db_port" 
                           name="db_port" 
                           value="<?php echo $old['db_port'] ?? $default['port']; ?>" 
                           required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="db_name">
                <i class="fas fa-database mr-1"></i>
                Nome do Banco
            </label>
            <input type="text" 
                   class="form-control" 
                   id="db_name" 
                   name="db_name" 
                   value="<?php echo $old['db_name'] ?? $default['name']; ?>" 
                   required>
            <small class="form-text text-muted">
                Se o banco não existir, tentaremos criá-lo automaticamente.
            </small>
        </div>

        <div class="form-group">
            <label for="db_user">
                <i class="fas fa-user mr-1"></i>
                Usuário
            </label>
            <input type="text" 
                   class="form-control" 
                   id="db_user" 
                   name="db_user" 
                   value="<?php echo $old['db_user'] ?? $default['user']; ?>" 
                   required>
        </div>

        <div class="form-group">
            <label for="db_pass">
                <i class="fas fa-key mr-1"></i>
                Senha
            </label>
            <div class="input-group">
                <input type="password" 
                       class="form-control" 
                       id="db_pass" 
                       name="db_pass" 
                       value="<?php echo $old['db_pass'] ?? $default['pass']; ?>">
                <div class="input-group-append">
                    <button type="button" 
                            class="btn btn-outline-secondary" 
                            onclick="togglePassword('db_pass')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="row justify-content-between mt-4">
            <div class="col-auto">
                <a href="?step=permissions" class="btn btn-default">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    Testar e Continuar
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="alert alert-info mt-4">
        <h5>
            <i class="fas fa-info-circle mr-2"></i>
            Dicas:
        </h5>
        <ul class="mb-0">
            <li>Certifique-se que o usuário tem permissão para criar bancos de dados</li>
            <li>Para o XAMPP, geralmente o usuário é "root" sem senha</li>
            <li>Para hospedagens, use os dados fornecidos pelo seu provedor</li>
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