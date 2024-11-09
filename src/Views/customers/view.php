<?php $this->layout('layout/default', ['title' => 'Visualizar Cliente']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Visualizar Cliente</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=customers">Clientes</a>
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
                        <h3 class="card-title">Informações do Cliente</h3>
                        
                        <div class="card-tools">
                            <?php if ($this->auth->checkPermission('customers.edit')): ?>
                                <a href="?route=customers/edit&id=<?php echo $customer['id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Tipo:</dt>
                            <dd class="col-sm-8">
                                <?php echo $customer['type'] === 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica'; ?>
                            </dd>

                            <dt class="col-sm-4">Nome:</dt>
                            <dd class="col-sm-8"><?php echo $customer['name']; ?></dd>

                            <dt class="col-sm-4">
                                <?php echo $customer['type'] === 'PF' ? 'CPF:' : 'CNPJ:'; ?>
                            </dt>
                            <dd class="col-sm-8"><?php echo $customer['document']; ?></dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8"><?php echo $customer['email']; ?></dd>

                            <dt class="col-sm-4">Telefone:</dt>
                            <dd class="col-sm-8">
                                <?php echo $customer['phone']; ?>
                                <?php if ($customer['mobile']): ?>
                                    <br><?php echo $customer['mobile']; ?>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Endereço:</dt>
                            <dd class="col-sm-8">
                                <?php echo $customer['address']; ?>, 
                                <?php echo $customer['address_number']; ?>
                                <?php if ($customer['complement']): ?>
                                    - <?php echo $customer['complement']; ?>
                                <?php endif; ?>
                                <br>
                                <?php echo $customer['neighborhood']; ?>
                                <br>
                                <?php echo $customer['city']; ?>/<?php echo $customer['state']; ?>
                                <br>
                                CEP: <?php echo $customer['zipcode']; ?>
                            </dd>

                            <?php if ($customer['notes']): ?>
                                <dt class="col-sm-4">Observações:</dt>
                                <dd class="col-sm-8"><?php echo nl2br($customer['notes']); ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-<?php echo $customer['active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $customer['active'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Cadastrado em:</dt>
                            <dd class="col-sm-8">
                                <?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?>
                                <br>
                                por <?php echo $customer['created_by_name']; ?>
                            </dd>

                            <?php if ($customer['updated_at']): ?>
                                <dt class="col-sm-4">Última atualização:</dt>
                                <dd class="col-sm-8">
                                    <?php echo date('d/m/Y H:i', strtotime($customer['updated_at'])); ?>
                                    <br>
                                    por <?php echo $customer['updated_by_name']; ?>
                                </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
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
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($sale['created_at'])); ?></td>
                                        <td><?php echo $sale['number']; ?></td>
                                        <td><?php echo $this->formatMoney($sale['total']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $this->getSaleStatusBadgeClass($sale['status']); ?>">
                                                <?php echo $this->getSaleStatusName($sale['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?route=sales/view&id=<?php echo $sale['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Histórico de Pagamentos -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Pagamentos</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Forma</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($payment['date'])); ?></td>
                                        <td><?php echo $this->formatMoney($payment['amount']); ?></td>
                                        <td><?php echo $this->getPaymentMethodName($payment['method']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $this->getPaymentStatusBadgeClass($payment['status']); ?>">
                                                <?php echo $this->getPaymentStatusName($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?route=payments/view&id=<?php echo $payment['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
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