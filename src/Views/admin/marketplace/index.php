<?php $this->layout('admin/layout', ['title' => 'Marketplace']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Marketplace</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <?php if ($this->auth->checkPermission('marketplace.settings')): ?>
                        <a href="?route=admin/marketplace/settings" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configurações
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Filtros</h3>
                
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <form method="GET" action="?route=admin/marketplace">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Buscar..." value="<?php echo $filters['search']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="type" class="form-control">
                                <option value="">Tipo</option>
                                <option value="extension" <?php echo $filters['type'] === 'extension' ? 'selected' : ''; ?>>
                                    Extensão
                                </option>
                                <option value="template" <?php echo $filters['type'] === 'template' ? 'selected' : ''; ?>>
                                    Template
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <select name="category" class="form-control">
                                <option value="">Categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $filters['category'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Items -->
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo $item['screenshot']; ?>" class="card-img-top" alt="Screenshot">
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo $item['name']; ?>
                                
                                <?php if ($item['price'] > 0): ?>
                                    <span class="float-right badge badge-success">
                                        R$ <?php echo number_format($item['price'], 2, ',', '.'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="float-right badge badge-info">Grátis</span>
                                <?php endif; ?>
                            </h5>
                            
                            <p class="card-text"><?php echo $item['short_description']; ?></p>
                            
                            <div class="mb-2">
                                <span class="badge badge-primary"><?php echo $item['type']; ?></span>
                                <span class="badge badge-secondary"><?php echo $item['category']; ?></span>
                                <span class="badge badge-info">v<?php echo $item['version']; ?></span>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <a href="?route=admin/marketplace/view&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-primary btn-block">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </a>
                                </div>
                                
                                <?php if ($item['status'] === 'pending'): ?>
                                    <div class="col-sm-6">
                                        <a href="?route=admin/marketplace/review&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-warning btn-block">
                                            <i class="fas fa-check"></i> Revisar
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <small class="text-muted">
                                Por <?php echo $item['vendor']; ?> | 
                                <?php echo $item['downloads']; ?> downloads |
                                <?php echo $item['rating']; ?> ⭐
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Paginação -->
        <?php if ($pages > 1): ?>
            <div class="text-center mt-4">
                <div class="btn-group">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?route=admin/marketplace&page=<?php echo $i; ?>" 
                           class="btn btn-default <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
