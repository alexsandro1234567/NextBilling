<?php $this->layout('layout/default', ['title' => 'Editar Produto']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Produto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=products">Produtos</a>
                    </li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <form method="POST" action="?route=products/edit&id=<?php echo $product['id']; ?>" 
                  id="productForm">
                <div class="card-body">
                    <!-- Mesmos campos do formulário de criação -->
                    <?php include '_form.php'; ?>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="?route=products" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
// Mesmo JavaScript do formulário de criação
<?php include '_form_scripts.php'; ?>
</script>
<?php $this->end() ?> 