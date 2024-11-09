<div class="step-content">
    <h2>Verificação de Requisitos</h2>
    
    <?php $requirements = $this->checkRequirements(); ?>
    
    <div class="requirements-list">
        <?php foreach ($requirements as $req): ?>
            <div class="requirement <?php echo $req['status'] ? 'success' : 'error'; ?>">
                <div class="req-name"><?php echo $req['name']; ?></div>
                <div class="req-details">
                    <span>Requerido: <?php echo $req['required']; ?></span>
                    <span>Atual: <?php echo $req['current']; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (array_column($requirements, 'status') === array_filter(array_column($requirements, 'status'))): ?>
        <form method="POST" action="?step=2">
            <button type="submit">Continuar</button>
        </form>
    <?php else: ?>
        <div class="alert alert-error">
            Corrija os requisitos acima antes de continuar.
        </div>
    <?php endif; ?>
</div> 