<?php $this->layout('layout/default', ['title' => 'Logs do Sistema']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Logs do Sistema</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($this->auth->checkPermission('logs.export')): ?>
                        <a href="?route=logs/export<?php echo $this->buildQueryString($filters); ?>" 
                           class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exportar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="?route=logs" class="form-inline">
                    <div class="row w-100">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Data Início</label>
                                <input type="date" name="date_start" class="form-control w-100" 
                                       value="<?php echo $filters['date_start']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Data Fim</label>
                                <input type="date" name="date_end" class="form-control w-100" 
                                       value="<?php echo $filters['date_end']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Módulo</label>
                                <select name="module" class="form-control w-100">
                                    <option value="">Todos</option>
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?php echo $module; ?>" 
                                            <?php echo $filters['module'] === $module ? 'selected' : ''; ?>>
                                            <?php echo $module; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Ação</label>
                                <select name="action" class="form-control w-100">
                                    <option value="">Todas</option>
                                    <?php foreach ($actions as $action): ?>
                                        <option value="<?php echo $action; ?>" 
                                            <?php echo $filters['action'] === $action ? 'selected' : ''; ?>>
                                            <?php echo $action; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tipo Entidade</label>
                                <select name="entity_type" class="form-control w-100">
                                    <option value="">Todos</option>
                                    <?php foreach ($entityTypes as $type): ?>
                                        <option value="<?php echo $type; ?>" 
                                            <?php echo $filters['entity_type'] === $type ? 'selected' : ''; ?>>
                                            <?php echo $type; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Módulo</th>
                            <th>Ação</th>
                            <th>Descrição</th>
                            <th>IP</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td><?php echo $log['user_name']; ?></td>
                                <td><?php echo $log['module']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $this->getActionBadgeClass($log['action']); ?>">
                                        <?php echo $log['action']; ?>
                                    </span>
                                </td>
                                <td><?php echo $log['description']; ?></td>
                                <td><?php echo $log['ip_address']; ?></td>
                                <td>
                                    <a href="?route=logs/view&id=<?php echo $log['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pages > 1): ?>
                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                <a class="page-link" 
                                   href="?route=logs&page=<?php echo $i . $this->buildQueryString($filters); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 