<?php 
$error = $data['error'] ?? null;
$old = $data['old'] ?? [];
$default = [
    'host' => 'localhost',
    'port' => '3306',
    'name' => '',
    'user' => 'root',
    'pass' => ''
];
$default = array_merge($default, $data['default'] ?? []);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextBilling - Instalação</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
        }
        .card {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Configuração do Banco de Dados</h2>
            </div>
            <div class="card-body">
                <form id="database-form" method="POST">
                    <div class="form-group">
                        <label for="host">Host</label>
                        <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label for="port">Porta</label>
                        <input type="text" class="form-control" id="port" name="port" value="3306" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Nome do Banco</label>
                        <input type="text" class="form-control" id="name" name="name" value="nextbilling" required>
                    </div>

                    <div class="form-group">
                        <label for="user">Usuário</label>
                        <input type="text" class="form-control" id="user" name="user" value="root" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha">
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Voltar</button>
                        <button type="submit" class="btn btn-primary">Testar e Continuar</button>
                    </div>
                </form>

                <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                <div id="success-message" class="alert alert-success mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.getElementById('database-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const errorDiv = document.getElementById('error-message');
        const successDiv = document.getElementById('success-message');
        const submitButton = this.querySelector('button[type="submit"]');
        
        // Limpa mensagens anteriores
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';
        
        // Desabilita botão
        submitButton.disabled = true;
        submitButton.textContent = 'Testando...';
        
        // Envia dados
        fetch('?step=database', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                successDiv.textContent = data.message;
                successDiv.style.display = 'block';
                setTimeout(() => {
                    window.location.href = '?step=settings';
                }, 1000);
            } else {
                errorDiv.textContent = data.error;
                errorDiv.style.display = 'block';
            }
        })
        .catch(error => {
            errorDiv.textContent = 'Erro ao processar a requisição: ' + error.message;
            errorDiv.style.display = 'block';
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Testar e Continuar';
        });
    });
    </script>
</body>
</html>

<style>
.message-container {
    margin-bottom: 20px;
}

.alert {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert-danger {
    background-color: #fff2f2;
    border-left: 4px solid #dc3545;
    padding: 1.25rem;
}

.alert-danger i {
    color: #dc3545;
    margin-right: 1rem;
}

.alert-danger hr {
    border-top-color: #f5c6cb;
    margin: 1rem 0;
}

.alert-danger ul {
    color: #666;
    margin-bottom: 0;
    padding-left: 1.25rem;
}

.alert-heading {
    color: #dc3545;
    margin-bottom: 0.5rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate__fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style> 