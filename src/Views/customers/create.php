<?php $this->layout('layout/default', ['title' => 'Novo Cliente']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Novo Cliente</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=customers">Clientes</a>
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
            <form method="POST" action="?route=customers/create" id="customerForm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Tipo de Pessoa *</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="PF" <?php echo ($data['type'] ?? '') === 'PF' ? 'selected' : ''; ?>>
                                        Pessoa Física
                                    </option>
                                    <option value="PJ" <?php echo ($data['type'] ?? '') === 'PJ' ? 'selected' : ''; ?>>
                                        Pessoa Jurídica
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Nome/Razão Social *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $data['name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="document" id="documentLabel">CPF/CNPJ *</label>
                                <input type="text" class="form-control" id="document" name="document" 
                                       value="<?php echo $data['document'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $data['email'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Telefone</label>
                                <input type="text" class="form-control phone-mask" id="phone" name="phone" 
                                       value="<?php echo $data['phone'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="mobile">Celular</label>
                                <input type="text" class="form-control phone-mask" id="mobile" name="mobile" 
                                       value="<?php echo $data['mobile'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zipcode">CEP</label>
                                <input type="text" class="form-control cep-mask" id="zipcode" name="zipcode" 
                                       value="<?php echo $data['zipcode'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Endereço</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo $data['address'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address_number">Número</label>
                                <input type="text" class="form-control" id="address_number" name="address_number" 
                                       value="<?php echo $data['address_number'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="complement">Complemento</label>
                                <input type="text" class="form-control" id="complement" name="complement" 
                                       value="<?php echo $data['complement'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="neighborhood">Bairro</label>
                                <input type="text" class="form-control" id="neighborhood" name="neighborhood" 
                                       value="<?php echo $data['neighborhood'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="city">Cidade</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo $data['city'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="state">Estado</label>
                                <select name="state" id="state" class="form-control">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($this->states as $uf => $state): ?>
                                        <option value="<?php echo $uf; ?>" 
                                            <?php echo ($data['state'] ?? '') === $uf ? 'selected' : ''; ?>>
                                            <?php echo $state; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="notes">Observações</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php 
                                    echo $data['notes'] ?? ''; 
                                ?></textarea>
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
                    <a href="?route=customers" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
$(document).ready(function() {
    // Máscaras
    $('.phone-mask').mask('(00) 0000-00009');
    $('.cep-mask').mask('00000-000');
    
    // Alterar máscara do documento conforme tipo de pessoa
    $('#type').change(function() {
        var type = $(this).val();
        if (type === 'PF') {
            $('#documentLabel').text('CPF *');
            $('#document').mask('000.000.000-00');
        } else if (type === 'PJ') {
            $('#documentLabel').text('CNPJ *');
            $('#document').mask('00.000.000/0000-00');
        } else {
            $('#documentLabel').text('CPF/CNPJ *');
            $('#document').unmask();
        }
    }).trigger('change');
    
    // Buscar endereço pelo CEP
    $('#zipcode').blur(function() {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            $.get('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                if (!data.erro) {
                    $('#address').val(data.logradouro);
                    $('#neighborhood').val(data.bairro);
                    $('#city').val(data.localidade);
                    $('#state').val(data.uf);
                }
            });
        }
    });
});
</script>
<?php $this->end() ?> 