<?php $this->layout('layout/default', ['title' => 'Visualizar Usuário']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Visualizar Usuário</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=users">Usuários</a>
                    </li>
                    <li class="breadcrumb-item active">Visualizar</li>
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
                        <h3 class="card-title">Informações do Usuário</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Nome:</dt>
                            <dd class="col-sm-8"><?php echo $user['name']; ?></dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8"><?php echo $user['email']; ?></dd>

                            <dt class="col-sm-4">Papel:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?php echo $this->getRoleBadgeClass($user['role']); ?>">
                                    <?php echo $this->getRoleName($user['role']); ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?php echo $user['active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $user['active'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Último Login:</dt>
                            <dd class="col-sm-8">
                                <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-'; ?>
                            </dd>

                            <dt class="col-sm-4">Criado em:</dt>
                            <dd class="col-sm-8">
                                <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                            </dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <?php if ($this->auth->checkPermission('users.edit')): ?>
                            <a href="?route=users/edit&id=<?php echo $user['id']; ?>" 
                               class="btn btn-primary">Editar</a>
                        <?php endif; ?>
                        <a href="?route=users" class="btn btn-default">Voltar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Atividades</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Ação</th>
                                        <th>Descrição</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                            <td><?php echo $log['action']; ?></td>
                                            <td><?php echo $log['description']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 