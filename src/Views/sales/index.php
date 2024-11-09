<?php $this->layout('layout/default', ['title' => 'Vendas']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Vendas</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($this->auth->checkPermission('sales.create')): ?>
                        <a href="?route=sales/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nova Venda
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
                <form method="GET" action="?route=sales" class="form-inline">
                    <div class="row w-100">
                        <div class="col-md-3">
                            <div class="form-group w-100">
                                <input type="text" name="search" class="form-control w-100" 
                                       placeholder="Buscar por número ou cliente..." 
                                       value="<?php echo $filters['search']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="customer_id" class="form-control w-100">
                                <option value="">Cliente</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>" 
                                        <?php echo $filters['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                                        <?php echo $customer['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="status" class="form-control w-100">
                                <option value="">Status</option>
                                <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>
                                    Pendente
                                </option>
                                <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>
                                    Concluída
                                </option>
                                <option value="canceled" <?php echo $filters['status'] === 'canceled' ? 'selected' : ''; ?>>
                                    Cancelada
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <input type="date" name="start_date" class="form-control w-100" 
                                   value="<?php echo $filters['start_date']; ?>" 
                                   placeholder="Data Inicial">
                        </div>
                        
                        <div class="col-md-2">
                            <div class="input-group">
                                <input type="date" name="end_date" class="form-control" 
                                       value="<?php echo $filters['end_date']; ?>" 
                                       placeholder="Data Final">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['number']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($sale['created_at'])); ?></td>
                                <td><?php echo $sale['customer_name']; ?></td>
                                <td><?php echo $this->formatMoney($sale['final_total']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $this->getSaleStatusBadgeClass($sale['status']); ?>">
                                        <?php echo $this->getSaleStatusName($sale['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $this->getPaymentMethodName($sale['payment_method']); ?>
                                    <?php if ($sale['payment_term']): ?>
                                        <br>
                                        <small><?php echo $sale['payment_term']; ?>x</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($this->auth->checkPermission('sales.view')): ?>
                                        <a href="?route=sales/view&id=<?php echo $sale['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($sale['status'] === 'pending'): ?>
                                        <?php if ($this->auth->checkPermission('sales.edit')): ?>
                                            <a href="?route=sales/edit&id=<?php echo $sale['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($this->auth->checkPermission('sales.cancel')): ?>
                                            <a href="?route=sales/cancel&id=<?php echo $sale['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
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
                                   href="?route=sales&page=<?php echo $i . $this->buildQueryString($filters); ?>">
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