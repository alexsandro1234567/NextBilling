<?php 
$requirements = $data['requirements'] ?? [];
$can_continue = $data['can_continue'] ?? false;
?>

<div class="requirements-check">
    <h4 class="text-center mb-4">
        <i class="fas fa-server text-primary"></i>
        Verificação de Requisitos do Sistema
    </h4>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Requisito</th>
                    <th>Valor Atual</th>
                    <th class="text-center" width="150">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $requirement => $info): ?>
                    <tr>
                        <td>
                            <strong><?php echo $requirement; ?></strong>
                        </td>
                        <td>
                            <?php echo $info['current']; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($info['status']): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> OK
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times"></i> Erro
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
            <a href="?step=welcome" class="btn btn-default">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
        <div class="col-auto">
            <?php if ($can_continue): ?>
                <a href="?step=permissions" class="btn btn-primary">
                    Próximo
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            <?php else: ?>
                <button class="btn btn-danger" disabled>
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Corrija os requisitos
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$can_continue): ?>
        <div class="alert alert-warning mt-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Atenção:</strong> Seu servidor não atende a todos os requisitos necessários. 
            Por favor, corrija os itens marcados em vermelho antes de continuar.
        </div>
    <?php endif; ?>
</div> 