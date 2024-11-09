<?php $this->layout('layout/default', ['title' => 'Excluir Produto']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Excluir Produto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=products">Produtos</a>
                    </li>
                    <li class="breadcrumb-item active">Excluir</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <p>Tem certeza que deseja excluir o produto <strong><?php echo $product['name']; ?></strong>?</p>
                
                <p>Esta ação não poderá ser desfeita.</p>
                
                <?php if ($product['current_stock'] > 0): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Este produto possui saldo em estoque de 
                        <strong><?php echo $product['current_stock']; ?> <?php echo $product['unit']; ?></strong>.
                        Certifique-se de ajustar o estoque antes de excluí-lo.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card-footer">
                <form method="POST" action="?route=products/delete&id=<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    <a href="?route=products" class="btn btn-default">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div> 