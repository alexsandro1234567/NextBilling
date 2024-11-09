<?php $this->layout('layout/default', ['title' => 'Clientes']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Clientes</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($this->auth->checkPermission('customers.create')): ?>
                        <a href="?route=customers/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Novo Cliente
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
                <form method="GET" action="?route=customers" class="form-inline">
                    <div class="row w-100">
                        <div class="col-md-4">
                            <div class="form-group w-100">
                                <input type="text" name="search" class="form-control w-100" 
                                       placeholder="Buscar por nome, documento, email ou telefone..." 
                                       value="<?php echo $filters['search']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="type" class="form-control w-100">
                                <option value="">Tipo</option>
                                <option value="PF" <?php echo $filters['type'] === 'PF' ? 'selected' : ''; ?>>
                                    Pessoa Física
                                </option>
                                <option value="PJ" <?php echo $filters['type'] === 'PJ' ? 'selected' : ''; ?>>
                                    Pessoa Jurídica
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="city" class="form-control w-100">
                                <option value="">Cidade</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo $city; ?>" 
                                        <?php echo $filters['city'] === $city ? 'selected' : ''; ?>>
                                        <?php echo $city; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="state" class="form-control w-100">
                                <option value="">Estado</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo $state; ?>" 
                                        <?php echo $filters['state'] === $state ? 'selected' : ''; ?>>
                                        <?php echo $state; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Cidade/UF</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo $customer['name']; ?></td>
                                <td>
                                    <?php echo $customer['type'] === 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica'; ?>
                                </td>
                                <td><?php echo $customer['document']; ?></td>
                                <td><?php echo $customer['email']; ?></td>
                                <td>
                                    <?php echo $customer['phone']; ?>
                                    <?php if ($customer['mobile']): ?>
                                        <br>
                                        <?php echo $customer['mobile']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $customer['city']; ?>/<?php echo $customer['state']; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $customer['active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $customer['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($this->auth->checkPermission('customers.view')): ?>
                                        <a href="?route=customers/view&id=<?php echo $customer['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->auth->checkPermission('customers.edit')): ?>
                                        <a href="?route=customers/edit&id=<?php echo $customer['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->auth->checkPermission('customers.delete')): ?>
                                        <a href="?route=customers/delete&id=<?php echo $customer['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Excluir">
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
                                <a class="page-link" 
                                   href="?route=customers&page=<?php echo $i . $this->buildQueryString($filters); ?>">
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