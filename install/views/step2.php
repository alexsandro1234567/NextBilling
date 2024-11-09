<div class="step-content">
    <h2>Configuração do Banco de Dados</h2>
    
    <form method="POST" action="?step=2">
        <div class="form-group">
            <label>Host do Banco de Dados:</label>
            <input type="text" name="db_host" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label>Porta:</label>
            <input type="text" name="db_port" value="3306" required>
        </div>
        
        <div class="form-group">
            <label>Nome do Banco de Dados:</label>
            <input type="text" name="db_name" required>
        </div>
        
        <div class="form-group">
            <label>Usuário:</label>
            <input type="text" name="db_user" required>
        </div>
        
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="db_pass">
        </div>
        
        <div class="form-actions">
            <button type="submit">Continuar</button>
        </div>
    </form>
</div> 