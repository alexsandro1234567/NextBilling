<?php
// Obter os dados do instalador
$requirementsData = $this->getRequirementsData();
$requirements = $requirementsData['requirements'] ?? [];
$canContinue = $requirementsData['can_continue'] ?? false;
?>

<div class="requirements-page">
    <h2 class="text-center mb-4">
        <i class="fas fa-server"></i>
        Verificação de Requisitos do Sistema
    </h2>

    <div class="requirements-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Requisito</th>
                    <th>Valor Atual</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requirements)): ?>
                    <?php foreach ($requirements as $name => $requirement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($name); ?></td>
                        <td><?php echo htmlspecialchars($requirement['current']); ?></td>
                        <td>
                            <?php if ($requirement['status']): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Nenhum requisito encontrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="action-buttons">
        <a href="?step=welcome" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <?php if ($canContinue): ?>
            <a href="?step=permissions" class="btn btn-primary">
                Próximo <i class="fas fa-arrow-right"></i>
            </a>
        <?php else: ?>
            <button class="btn btn-danger" id="checkRequirements">
                Corrigir os requisitos
            </button>
        <?php endif; ?>
    </div>

    <?php if (!$canContinue): ?>
    <div class="alert alert-warning mt-4">
        <i class="fas fa-exclamation-triangle"></i>
        Atenção: Seu servidor não atende a todos os requisitos necessários. Por favor, corrija os itens marcados em vermelho antes de continuar.
    </div>
    <?php endif; ?>
</div> 