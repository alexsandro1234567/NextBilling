<?php $this->layout('layout/default', ['title' => 'Novo Usuário']); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Novo Usuário</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?route=users">Usuários</a>
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
            <form method="POST" action="?route=users/create">
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $data['name'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo $data['email'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" class="form-control" id="password" 
                               name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirmar Senha</label>
                        <input type="password" class="form-control" id="password_confirm" 
                               name="password_confirm" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Papel</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="user" <?php echo ($data['role'] ?? '') === 'user' ? 'selected' : ''; ?>>
                                Usuário
                            </option>
                            <option value="manager" <?php echo ($data['role'] ?? '') === 'manager' ? 'selected' : ''; ?>>
                                Gerente
                            </option>
                            <option value="admin" <?php echo ($data['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                Administrador
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="active" 
                                   name="active" <?php echo isset($data['active']) && $data['active'] ? 'checked' : ''; ?>>
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