<?= $this->extend('layout', ['bodyClass' => 'login-page']) ?>
<?= $this->section('content') ?>

<style>
.login-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 67px);
    padding: 10px;
}
.login-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    border: none;
    overflow: hidden;
    width: 100%;
}
.login-body {
    padding: 40px 30px;
}
.login-body h4 {
    font-weight: 600;
    color: #333;
}
</style>

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-lg-4">
                <div class="login-card">
                    <div class="login-body">
                        <h4 class="text-center mb-1 h-mobile-lg" id="loginTitle">Login</h4>
                        <p class="text-center text-muted mb-4 text-mobile-sm">Selamat datang kembali</p>
                        
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?= base_url('login') ?>" id="loginForm">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label required text-mobile-sm">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-mobile" 
                                           id="username" 
                                           name="username" 
                                           placeholder="Masukkan username atau nama depan"
                                           autocomplete="username"
                                           required>
                                </div>
                                <small class="form-text text-muted text-mobile-xs">
                                    Gunakan nama depan Anda
                                </small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label required text-mobile-sm">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="form-control form-control-mobile" 
                                           placeholder="Masukkan password"
                                           autocomplete="current-password"
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted text-mobile-xs">
                                    Gunakan NISN/NIP, jika ini pertama kali login
                                </small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-mobile-sm w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Login
                            </button>
                            
                            <div class="text-center">
                                <a href="<?= base_url('forgot-password') ?>" class="text-decoration-none text-mobile-md">
                                    <i class="bi bi-question-circle me-1"></i>
                                    Lupa Password?
                                </a>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted text-mobile-xs">
                                Hak Cipta &copy; <?= date('Y') ?> Perpustakaan Binekas
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.className = 'bi bi-eye';
            } else {
                eyeIcon.className = 'bi bi-eye-slash';
            }
        });
    }
    
    // Form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Username dan password harus diisi!');
                return false;
            }
            
            // Show loading state
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        });
    }
    
    // TIMEOUT ALERTS
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    

    document.getElementById('username')?.focus();
});
</script>

<?= $this->endSection() ?>