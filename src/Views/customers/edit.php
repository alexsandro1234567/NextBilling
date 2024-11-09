<?php $this->layout('layout/default', ['title' => 'Editar Cliente']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Cliente</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=customers">Clientes</a>
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
            <form method="POST" action="?route=customers/edit&id=<?php echo $customer['id']; ?>" 
                  id="customerForm">
                <div class="card-body">
                    <!-- Mesmos campos do formulário de criação -->
                    <?php include '_form.php'; ?>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="?route=customers" class="btn btn-default">Cancelar</a>
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