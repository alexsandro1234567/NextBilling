<?php $this->layout('layout/default', ['title' => 'Usuários']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Usuários</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($this->auth->checkPermission('users.create')): ?>
                        <a href="?route=users/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Novo Usuário
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
                <form method="GET" action="?route=users" class="form-inline">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar..." value="<?php echo $filters['search']; ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <select name="role" class="form-control ml-2">
                        <option value="">Todos os papéis</option>
                        <option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>
                            Administrador
                        </option>
                        <option value="manager" <?php echo $filters['role'] === 'manager' ? 'selected' : ''; ?>>
                            Gerente
                        </option>
                        <option value="user" <?php echo $filters['role'] === 'user' ? 'selected' : ''; ?>>
                            Usuário
                        </option>
                    </select>
                    
                    <select name="active" class="form-control ml-2">
                        <option value="">Todos os status</option>
                        <option value="1" <?php echo $filters['active'] === '1' ? 'selected' : ''; ?>>
                            Ativo
                        </option>
                        <option value="0" <?php echo $filters['active'] === '0' ? 'selected' : ''; ?>>
                            Inativo
                        </option>
                    </select>
                </form>
            </div>
            
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Papel</th>
                            <th>Status</th>
                            <th>Último Login</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $this->getRoleBadgeClass($user['role']); ?>">
                                        <?php echo $this->getRoleName($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-'; ?>
                                </td>
                                <td>
                                    <?php if ($this->auth->checkPermission('users.view')): ?>
                                        <a href="?route=users/view&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->auth->checkPermission('users.edit')): ?>
                                        <a href="?route=users/edit&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->auth->checkPermission('users.delete')): ?>
                                        <a href="?route=users/delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Deletar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
                                <a class="page-link" href="?route=users&page=<?php echo $i; ?>">
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