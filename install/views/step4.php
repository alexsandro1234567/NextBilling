<div class="step-content">
    <h2>Instalação Final</h2>
    
    <div class="installation-progress">
        <div class="progress-bar">
            <div class="progress" style="width: 0%"></div>
        </div>
        <div class="progress-status">Iniciando instalação...</div>
    </div>
    
    <form method="POST" action="?step=4" id="install-form">
        <div class="form-actions">
            <button type="submit">Iniciar Instalação</button>
        </div>
    </form>
    
    <script>
    document.getElementById('install-form').onsubmit = function() {
        this.querySelector('button').disabled = true;
        var progress = document.querySelector('.progress');
        var status = document.querySelector('.progress-status');
        
        var steps = [
            'Criando banco de dados...',
            'Configurando tabelas...',
            'Criando usuário administrador...',
            'Configurando sistema...',
            'Finalizando instalação...'
        ];
        
        var current = 0;
        var interval = setInterval(function() {
            if (current >= steps.length) {
                clearInterval(interval);
                return;
            }
            
            progress.style.width = ((current + 1) / steps.length * 100) + '%';
            status.textContent = steps[current];
            current++;
        }, 1000);
    };
    </script>
</div> 