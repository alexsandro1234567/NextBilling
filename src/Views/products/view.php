<?php $this->layout('layout/default', ['title' => 'Visualizar Produto']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Visualizar Produto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=products">Produtos</a>
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
                        <h3 class="card-title">Informações do Produto</h3>
                        
                        <div class="card-tools">
                            <?php if ($this->auth->checkPermission('products.edit')): ?>
                                <a href="?route=products/edit&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Código:</dt>
                            <dd class="col-sm-8"><?php echo $product['code']; ?></dd>

                            <dt class="col-sm-4">Nome:</dt>
                            <dd class="col-sm-8"><?php echo $product['name']; ?></dd>

                            <?php if ($product['description']): ?>
                                <dt class="col-sm-4">Descrição:</dt>
                                <dd class="col-sm-8"><?php echo nl2br($product['description']); ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">Categoria:</dt>
                            <dd class="col-sm-8"><?php echo $product['category_name']; ?></dd>

                            <?php if ($product['brand_name']): ?>
                                <dt class="col-sm-4">Marca:</dt>
                                <dd class="col-sm-8"><?php echo $product['brand_name']; ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">Unidade:</dt>
                            <dd class="col-sm-8"><?php echo $product['unit']; ?></dd>

                            <dt class="col-sm-4">Preço de Custo:</dt>
                            <dd class="col-sm-8"><?php echo $this->formatMoney($product['cost_price']); ?></dd>

                            <dt class="col-sm-4">Preço de Venda:</dt>
                            <dd class="col-sm-8"><?php echo $this->formatMoney($product['sale_price']); ?></dd>

                            <dt class="col-sm-4">Estoque Atual:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?php echo $this->getStockStatusBadgeClass($product); ?>">
                                    <?php echo $product['current_stock']; ?> <?php echo $product['unit']; ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Estoque Mínimo:</dt>
                            <dd class="col-sm-8">
                                <?php echo $product['min_stock']; ?> <?php echo $product['unit']; ?>
                            </dd>

                            <dt class="col-sm-4">Estoque Máximo:</dt>
                            <dd class="col-sm-8">
                                <?php echo $product['max_stock']; ?> <?php echo $product['unit']; ?>
                            </dd>

                            <?php if ($product['location']): ?>
                                <dt class="col-sm-4">Localização:</dt>
                                <dd class="col-sm-8"><?php echo $product['location']; ?></dd>
                            <?php endif; ?>

                            <?php if ($product['barcode']): ?>
                                <dt class="col-sm-4">Código de Barras:</dt>
                                <dd class="col-sm-8"><?php echo $product['barcode']; ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?php echo $product['active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $product['active'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Cadastrado em:</dt>
                            <dd class="col-sm-8">
                                <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?>
                                <br>
                                por <?php echo $product['created_by_name']; ?>
                            </dd>

                            <?php if ($product['updated_at']): ?>
                                <dt class="col-sm-4">Última atualização:</dt>
                                <dd class="col-sm-8">
                                    <?php echo date('d/m/Y H:i', strtotime($product['updated_at'])); ?>
                                    <br>
                                    por <?php echo $product['updated_by_name']; ?>
                                </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Movimentações de Estoque -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Movimentações de Estoque</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stockMovements as $movement): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($movement['created_at'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $this->getStockMovementBadgeClass($movement['type']); ?>">
                                                <?php echo $this->getStockMovementTypeName($movement['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($movement['quantity'] > 0): ?>
                                                <span class="text-success">
                                                    +<?php echo $movement['quantity']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-danger">
                                                    <?php echo $movement['quantity']; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php echo $product['unit']; ?>
                                        </td>
                                        <td><?php echo $movement['balance']; ?> <?php echo $product['unit']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Histórico de Vendas -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Vendas</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Nº Venda</th>
                                    <th>Cliente</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($sale['created_at'])); ?></td>
                                        <td><?php echo $sale['number']; ?></td>
                                        <td><?php echo $sale['customer_name']; ?></td>
                                        <td><?php echo $sale['quantity']; ?> <?php echo $product['unit']; ?></td>
                                        <td><?php echo $this->formatMoney($sale['price']); ?></td>
                                        <td><?php echo $this->formatMoney($sale['total']); ?></td>
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