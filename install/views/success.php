<div class="step-content success">
    <h2>Instalação Concluída!</h2>
    
    <div class="success-message">
        <p>O sistema foi instalado com sucesso!</p>
        <p>Por questões de segurança, remova o diretório de instalação.</p>
    </div>
    
    <div class="login-info">
        <h3>Informações de Acesso</h3>
        <p>URL: <strong><?php echo $this->getBaseUrl(); ?></strong></p>
        <p>Email: <strong><?php echo $_SESSION['admin_config']['admin_email']; ?></strong></p>
    </div>
    
    <div class="form-actions">
        <a href="../" class="button">Ir para o Sistema</a>
    </div>
</div> 