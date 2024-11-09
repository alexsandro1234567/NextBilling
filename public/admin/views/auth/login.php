<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="/public/admin" class="h1">
                <img src="/public/admin/assets/img/logo.png" alt="Logo" class="brand-image" style="height: 50px;">
            </a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Faça login para iniciar sua sessão</p>

            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['login_error'];
                    unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="/public/admin/auth.php" method="post">
                <div class="input-group mb-3">
                    <input type="email" 
                           class="form-control" 
                           placeholder="Email"
                           name="email"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" 
                           class="form-control" 
                           placeholder="Senha"
                           name="password"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">
                                Lembrar-me
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                    </div>
                </div>
            </form>

            <p class="mb-1 mt-3">
                <a href="/public/admin/forgot-password.php">Esqueci minha senha</a>
            </p>
        </div>
    </div>
</div> 