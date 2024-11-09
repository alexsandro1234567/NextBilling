<?php 
$permissions = $data['permissions'] ?? [];
$can_continue = $data['can_continue'] ?? false;
?>

<div class="permissions-check">
    <h4 class="text-center mb-4">
        <i class="fas fa-lock text-primary"></i>
        Verificação de Permissões de Diretórios
    </h4>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Diretório</th>
                    <th>Permissão Atual</th>
                    <th>Permissão Necessária</th>
                    <th class="text-center" width="150">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $dir): ?>
                    <tr>
                        <td>
                            <i class="fas fa-folder text-warning mr-2"></i>
                            <strong><?php echo $dir['directory']; ?></strong>
                            <small class="d-block text-muted"><?php echo $dir['path']; ?></small>
                        </td>
                        <td>
                            <code><?php echo $dir['current']; ?></code>
                        </td>
                        <td>
                            <code><?php echo $dir['required']; ?></code>
                        </td>
                        <td class="text-center">
                            <?php if ($dir['writable']): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Gravável
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times"></i> Não Gravável
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="row justify-content-between mt-4">
        <div class="col-auto">
            <a href="?step=requirements" class="btn btn-default">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
        <div class="col-auto">
            <?php if ($can_continue): ?>
                <a href="?step=database" class="btn btn-primary">
                    Próximo
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            <?php else: ?>
                <button class="btn btn-danger" disabled>
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Corrija as Permissões
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$can_continue): ?>
        <div class="alert alert-warning mt-4">
            <h5>
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Como corrigir as permissões?
            </h5>
            <p class="mb-0">
                Para corrigir as permissões, execute os seguintes comandos no terminal:
            </p>
            <pre class="mt-2 bg-light p-3 rounded"><code>chmod -R 755 <?php echo BASE_PATH; ?>/storage
chmod -R 755 <?php echo BASE_PATH; ?>/public/uploads
chmod -R 755 <?php echo BASE_PATH; ?>/config</code></pre>
            <p class="mt-2 mb-0">
                Se você estiver usando Windows, certifique-se de que o usuário do servidor web (geralmente IUSR ou o usuário do PHP) 
                tenha permissões de escrita nos diretórios listados acima.
            </p>
        </div>
    <?php endif; ?> 