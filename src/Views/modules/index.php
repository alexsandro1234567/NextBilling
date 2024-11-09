<div class="content-wrapper">
    <div class="content-header">
        <h1>Gerenciador de Módulos</h1>
    </div>
    
    <div class="content">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <?php foreach ($modules as $module): ?>
                    <div class="col-md-4">
                        <div class="module-card">
                            <h3><?php echo $module['name']; ?></h3>
                            <p><?php echo $module['description']; ?></p>
                            <div class="module-actions">
                                <label class="switch">
                                    <input type="checkbox" 
                                           <?php echo $module['is_active'] ? 'checked' : ''; ?>
                                           onchange="toggleModule(<?php echo $module['id']; ?>)">
                                    <span class="slider round"></span>
                                </label>
                                <a href="?route=modules/configure&id=<?php echo $module['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    Configurar
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleModule(id) {
    fetch(`?route=modules/toggle&id=${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
        } else {
            toastr.error(data.message);
        }
    });
}
</script> 