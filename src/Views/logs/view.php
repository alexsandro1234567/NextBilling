<?php $this->layout('layout/default', ['title' => 'Detalhes do Log']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Detalhes do Log</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=logs">Logs</a>
                    </li>
                    <li class="breadcrumb-item active">Detalhes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informações Básicas</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Data/Hora:</dt>
                            <dd class="col-sm-8">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                            </dd>

                            <dt class="col-sm-4">Usuário:</dt>
                            <dd class="col-sm-8">
                                <?php echo $log['user_name']; ?> (<?php echo $log['user_email']; ?>)
                            </dd>

                            <dt class="col-sm-4">Módulo:</dt>
                            <dd class="col-sm-8"><?php echo $log['module']; ?></dd>

                            <dt class="col-sm-4">Ação:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?php echo $this->getActionBadgeClass($log['action']); ?>">
                                    <?php echo $log['action']; ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Descrição:</dt>
                            <dd class="col-sm-8"><?php echo $log['description']; ?></dd>

                            <?php if ($log['entity_type']): ?>
                                <dt class="col-sm-4">Tipo Entidade:</dt>
                                <dd class="col-sm-8"><?php echo $log['entity_type']; ?></dd>

                                <dt class="col-sm-4">ID Entidade:</dt>
                                <dd class="col-sm-8"><?php echo $log['entity_id']; ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">IP:</dt>
                            <dd class="col-sm-8"><?php echo $log['ip_address']; ?></dd>

                            <dt class="col-sm-4">User Agent:</dt>
                            <dd class="col-sm-8"><?php echo $log['user_agent']; ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <?php if ($log['old_data'] || $log['new_data']): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Alterações</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($log['old_data']): ?>
                                <h5>Dados Anteriores:</h5>
                                <pre><?php echo json_encode(json_decode($log['old_data']), JSON_PRETTY_PRINT); ?></pre>
                            <?php endif; ?>

                            <?php if ($log['new_data']): ?>
                                <h5>Novos Dados:</h5>
                                <pre><?php echo json_encode(json_decode($log['new_data']), JSON_PRETTY_PRINT); ?></pre>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 