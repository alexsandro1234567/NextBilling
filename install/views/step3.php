<div class="step-content">
    <h2>Configuração do Administrador</h2>
    
    <form method="POST" action="?step=3">
        <div class="form-group">
            <label>Nome do Administrador:</label>
            <input type="text" name="admin_name" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="admin_email" required>
        </div>
        
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="admin_password" required>
            <small>Mínimo de 8 caracteres</small>
        </div>
        
        <div class="form-group">
            <label>Confirmar Senha:</label>
            <input type="password" name="admin_password_confirm" required>
        </div>
        
        <div class="form-actions">
            <button type="submit">Continuar</button>
        </div>
    </form>
</div> 