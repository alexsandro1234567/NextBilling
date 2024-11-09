<?php $this->layout('layout/default', ['title' => 'Produtos']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Produtos</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($this->auth->checkPermission('products.create')): ?>
                        <a href="?route=products/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Novo Produto
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
                <form method="GET" action="?route=products" class="form-inline">
                    <div class="row w-100">
                        <div class="col-md-4">
                            <div class="form-group w-100">
                                <input type="text" name="search" class="form-control w-100" 
                                       placeholder="Buscar por código, nome ou código de barras..." 
                                       value="<?php echo $filters['search']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="category_id" class="form-control w-100">
                                <option value="">Categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $filters['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="brand_id" class="form-control w-100">
                                <option value="">Marca</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>" 
                                        <?php echo $filters['brand_id'] == $brand['id'] ? 'selected' : ''; ?>>
                                        <?php echo $brand['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select name="stock_status" class="form-control w-100">
                                <option value="">Status Estoque</option>
                                <option value="low" <?php echo $filters['stock_status'] === 'low' ? 'selected' : ''; ?>>
                                    Baixo
                                </option>
                                <option value="normal" <?php echo $filters['stock_status'] === 'normal' ? 'selected' : ''; ?>>
                                    Normal
                                </option>
                                <option value="high" <?php echo $filters['stock_status'] === 'high' ? 'selected' : ''; ?>>
                                    Alto
                                </option>
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
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Preço Venda</th>
                            <th>Estoque</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['code']; ?></td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo $product['category_name']; ?></td>
                                <td><?php echo $product['brand_name']; ?></td>
                                <td><?php echo $this->formatMoney($product['sale_price']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $this->getStockStatusBadgeClass($product); ?>">
                                        <?php echo $product['current_stock']; ?> <?php echo $product['unit']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $product['active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $product['active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($this->auth->checkPermission('products.view')): ?>
                                        <a href="?route=products/view&id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->auth->checkPermission('products.edit')): ?>
                                        <a href="?route=products/edit&id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->auth->checkPermission('products.delete')): ?>
                                        <a href="?route=products/delete&id=<?php echo $product['id']; ?>" 
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
                                   href="?route=products&page=<?php echo $i . $this->buildQueryString($filters); ?>">
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