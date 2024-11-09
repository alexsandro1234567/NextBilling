<?php $this->layout('layout/default', ['title' => 'Novo Produto']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Novo Produto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=products">Produtos</a>
                    </li>
                    <li class="breadcrumb-item active">Novo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <form method="POST" action="?route=products/create" id="productForm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Código</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       value="<?php echo $data['code'] ?? ''; ?>" 
                                       placeholder="Deixe em branco para gerar automaticamente">
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Nome *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $data['name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Descrição</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                    echo $data['description'] ?? ''; 
                                ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Categoria *</label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($data['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="brand_id">Marca</label>
                                <select name="brand_id" id="brand_id" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" 
                                            <?php echo ($data['brand_id'] ?? '') == $brand['id'] ? 'selected' : ''; ?>>
                                            <?php echo $brand['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Unidade *</label>
                                <select name="unit" id="unit" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="UN" <?php echo ($data['unit'] ?? '') === 'UN' ? 'selected' : ''; ?>>
                                        Unidade
                                    </option>
                                    <option value="KG" <?php echo ($data['unit'] ?? '') === 'KG' ? 'selected' : ''; ?>>
                                        Quilograma
                                    </option>
                                    <option value="MT" <?php echo ($data['unit'] ?? '') === 'MT' ? 'selected' : ''; ?>>
                                        Metro
                                    </option>
                                    <option value="LT" <?php echo ($data['unit'] ?? '') === 'LT' ? 'selected' : ''; ?>>
                                        Litro
                                    </option>
                                    <option value="CX" <?php echo ($data['unit'] ?? '') === 'CX' ? 'selected' : ''; ?>>
                                        Caixa
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="cost_price">Preço de Custo *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money-mask" id="cost_price" 
                                           name="cost_price" value="<?php echo $data['cost_price'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="sale_price">Preço de Venda *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money-mask" id="sale_price" 
                                           name="sale_price" value="<?php echo $data['sale_price'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="min_stock">Estoque Mínimo</label>
                                <input type="number" class="form-control" id="min_stock" name="min_stock" 
                                       value="<?php echo $data['min_stock'] ?? '0'; ?>" min="0" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label for="max_stock">Estoque Máximo</label>
                                <input type="number" class="form-control" id="max_stock" name="max_stock" 
                                       value="<?php echo $data['max_stock'] ?? '0'; ?>" min="0" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label for="location">Localização</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo $data['location'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="barcode">Código de Barras</label>
                                <input type="text" class="form-control" id="barcode" name="barcode" 
                                       value="<?php echo $data['barcode'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" 
                                   <?php echo !isset($data['active']) || $data['active'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="active">Ativo</label>
                        </div>
                    </div>
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
$(document).ready(function() {
    // Máscara para valores monetários
    $('.money-mask').mask('#.##0,00', {reverse: true});
    
    // Validação do formulário
    $('#productForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 3
            },
            category_id: {
                required: true
            },
            unit: {
                required: true
            },
            cost_price: {
                required: true
            },
            sale_price: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Por favor, informe o nome do produto',
                minlength: 'O nome deve ter pelo menos 3 caracteres'
            },
            category_id: {
                required: 'Por favor, selecione uma categoria'
            },
            unit: {
                required: 'Por favor, selecione uma unidade'
            },
            cost_price: {
                required: 'Por favor, informe o preço de custo'
            },
            sale_price: {
                required: 'Por favor, informe o preço de venda'
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });
});
</script>
<?php $this->end() ?> 