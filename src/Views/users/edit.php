<?php $this->layout('layout/default', ['title' => 'Editar Usuário']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Usuário</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=users">Usuários</a>
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
            <form method="POST" action="?route=users/edit&id=<?php echo $user['id']; ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $user['name']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo $user['email']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Nova Senha</label>
                        <input type="password" class="form-control" id="password" 
                               name="password">
                        <small class="form-text text-muted">
                            Deixe em branco para manter a senha atual
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="password_confirm" 
                               name="password_confirm">
                    </div>

                    <div class="form-group">
                        <label for="role">Papel</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>
                                Usuário
                            </option>
                            <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>
                                Gerente
                            </option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                Administrador
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="active" 
                                   name="active" <?php echo $user['active'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="active">Ativo</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="?route=users" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div> 