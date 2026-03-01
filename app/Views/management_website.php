<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<style>
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
        border-radius: 4px;
        object-fit: contain;
    }
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .required::after {
        content: " *";
        color: red;
    }
</style>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen Website</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-gear"></i> Konfigurasi Website</h5>
        </div>
        <div class="card-body">
            <form id="websiteForm" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-section">
                    <h6 class="mb-3"><i class="bi bi-info-circle"></i> Informasi Dasar</h6>
                    
                    <div class="mb-3">
                        <label for="site_name" class="form-label required">Nama Website</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="<?= esc($websiteConfig['site_name'] ?? '') ?>" required>
                        <small class="form-text text-muted">Nama yang ditampilkan di navbar dan title halaman</small>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="mb-3"><i class="bi bi-image"></i> Manajemen Gambar</h6>

                    <div class="mb-4">
                        <label for="navbar_logo" class="form-label">Logo Navbar</label>
                        <input type="file" class="form-control" id="navbar_logo" name="navbar_logo" 
                               accept="image/*">
                        <small class="form-text text-muted">Ukuran recommended: 50x50px. Format: PNG, JPG, atau WebP</small>
                        <?php if (!empty($websiteConfig['navbar_logo'])): ?>
                            <div>
                                <p class="mt-2 mb-1"><strong>Gambar saat ini:</strong></p>
                                <img src="<?= base_url($websiteConfig['navbar_logo']) ?>" alt="Navbar Logo" class="image-preview">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="homepage_logo" class="form-label">Logo Homepage</label>
                        <input type="file" class="form-control" id="homepage_logo" name="homepage_logo" 
                               accept="image/*">
                        <small class="form-text text-muted">Logo yang ditampilkan di halaman utama. Format: PNG, JPG, atau WebP</small>
                        <?php if (!empty($websiteConfig['homepage_logo'])): ?>
                            <div>
                                <p class="mt-2 mb-1"><strong>Gambar saat ini:</strong></p>
                                <img src="<?= base_url($websiteConfig['homepage_logo']) ?>" alt="Homepage Logo" class="image-preview">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="login_background_image" class="form-label">Background Login</label>
                        <input type="file" class="form-control" id="login_background_image" name="login_background_image" 
                               accept="image/*">
                        <small class="form-text text-muted">Gambar latar belakang halaman login. Format: PNG, JPG, atau WebP</small>
                        <?php if (!empty($websiteConfig['login_background_image'])): ?>
                            <div>
                                <p class="mt-2 mb-1"><strong>Gambar saat ini:</strong></p>
                                <img src="<?= base_url($websiteConfig['login_background_image']) ?>" alt="Login Background" class="image-preview">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                    <a href="<?= base_url() ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('websiteForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

    fetch('<?= base_url('management-website/update') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

<?= $this->endSection() ?>
