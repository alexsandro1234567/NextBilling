<?php 
$success = $data['success'] ?? false;
$error = $data['error'] ?? '';
$admin_email = $data['admin_email'] ?? '';
$app_url = $data['app_url'] ?? '';
?>

<div class="text-center">
    <?php if ($success): ?>
        <div class="success-message">
            <div class="success-icon mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            
            <h2 class="text-success mb-4">Instalação Concluída!</h2>
            
            <p class="lead text-muted mb-4">
                O sistema foi instalado com sucesso e está pronto para uso.
            </p>
            
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Informações de Acesso
                    </h5>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">URL do Admin:</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           value="<?php echo $app_url; ?>/admin" 
                                           readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" 
                                                type="button"
                                                onclick="copyToClipboard(this)">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Email:</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           value="<?php echo $admin_email; ?>" 
                                           readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" 
                                                type="button"
                                                onclick="copyToClipboard(this)">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Por segurança, <strong>exclua</strong> a pasta de instalação após acessar o painel.
            </div>
            
            <div class="mt-4">
                <a href="<?php echo $app_url; ?>/admin" class="btn btn-primary btn-lg">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Acessar Painel Admin
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="error-message">
            <div class="error-icon mb-4">
                <i class="fas fa-exclamation-circle text-danger" style="font-size: 5rem;"></i>
            </div>
            
            <h2 class="text-danger mb-4">Erro na Instalação</h2>
            
            <div class="alert alert-danger">
                <p class="mb-0">
                    <?php echo $error ?: 'Ocorreu um erro durante a instalação. Por favor, tente novamente.'; ?>
                </p>
            </div>
            
            <div class="mt-4">
                <a href="?step=settings" class="btn btn-danger btn-lg">
                    <i class="fas fa-redo mr-2"></i>
                    Tentar Novamente
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(button) {
    const input = button.parentElement.previousElementSibling;
    input.select();
    document.execCommand('copy');
    
    const icon = button.querySelector('i');
    icon.classList.remove('fa-copy');
    icon.classList.add('fa-check');
    
    setTimeout(() => {
        icon.classList.remove('fa-check');
        icon.classList.add('fa-copy');
    }, 2000);
}
</script> 