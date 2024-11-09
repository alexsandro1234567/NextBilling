<?php
$permissionsData = $this->getPermissionsData();
$permissions = $permissionsData['permissions'] ?? [];
$canContinue = $permissionsData['can_continue'] ?? false;

// Pegar o caminho base da instalação
$basePath = dirname(dirname(dirname(__FILE__))); // Volta 2 níveis para chegar na raiz
$basePath = str_replace('\\', '/', $basePath); // Normaliza barras para formato Unix
?>

<div class="permissions-page">
    <h2 class="text-center mb-4">
        <i class="fas fa-lock"></i>
        Verificação de Permissões de Diretórios
    </h2>

    <div class="permissions-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Diretório</th>
                    <th>Permissão Atual</th>
                    <th>Permissão Necessária</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $permission): ?>
                <tr>
                    <td><?php echo htmlspecialchars($permission['directory']); ?></td>
                    <td><?php echo htmlspecialchars($permission['current']); ?></td>
                    <td><?php echo htmlspecialchars($permission['required']); ?></td>
                    <td>
                        <?php if ($permission['writable']): ?>
                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="action-buttons">
        <a href="?step=requirements" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        
        <?php if (!$canContinue): ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="force_create_directories">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync"></i> Forçar Criação de Diretórios
                </button>
            </form>
        <?php endif; ?>

        <?php if ($canContinue): ?>
            <a href="?step=database" class="btn btn-primary">
                Próximo <i class="fas fa-arrow-right"></i>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!$canContinue): ?>
    <div class="alert alert-warning mt-4">
        <h5><i class="fas fa-exclamation-triangle"></i> Como corrigir as permissões?</h5>
        <p>Para corrigir todas as permissões, execute os seguintes comandos no terminal:</p>
        <pre class="command-block">
# Definir o proprietário correto para todos os arquivos e diretórios
sudo chown -R www-data:www-data <?php echo $basePath; ?>

# Definir permissões para diretórios (755)
sudo find <?php echo $basePath; ?> -type d -exec chmod 755 {} \;

# Definir permissões para arquivos (644)
sudo find <?php echo $basePath; ?> -type f -exec chmod 644 {} \;

# Garantir permissões específicas para diretórios que precisam de escrita
chmod -R 755 <?php echo $basePath; ?>/storage
chmod -R 755 <?php echo $basePath; ?>/public/uploads
chmod -R 755 <?php echo $basePath; ?>/config</pre>

        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i> <strong>Explicação das permissões:</strong>
            <ul class="mb-0">
                <li><code>755</code> para diretórios: permite leitura e execução para todos, mas escrita apenas para o proprietário</li>
                <li><code>644</code> para arquivos: permite leitura para todos, mas escrita apenas para o proprietário</li>
                <li>O usuário <code>www-data</code> é o padrão para servidores web Apache. Se você estiver usando Nginx ou outro servidor, ajuste conforme necessário</li>
            </ul>
        </div>

        <p class="mt-2">
            Se você estiver usando Windows, dê permissão total para os seguintes diretórios:
            <ul>
                <li><?php echo $basePath; ?>\storage</li>
                <li><?php echo $basePath; ?>\public\uploads</li>
                <li><?php echo $basePath; ?>\config</li>
            </ul>
            No Windows, certifique-se de que o usuário IUSR ou o pool de aplicativos do IIS tenha permissões de escrita nesses diretórios.
        </p>
    </div>
    <?php endif; ?>
</div>

<style>
.permissions-page {
    padding: 20px;
    max-width: 1000px;
    margin: 0 auto;
}

.permissions-table {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
    overflow-x: auto;
}

.command-block {
    background: #2d2d2d;
    color: #fff;
    padding: 15px;
    border-radius: 4px;
    margin: 10px 0;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.action-buttons {
    display: flex;
    gap: 10px;
    justify-content: space-between;
    margin-top: 20px;
}

.btn-warning {
    background: #ffc107;
    color: #000;
}
</style> 