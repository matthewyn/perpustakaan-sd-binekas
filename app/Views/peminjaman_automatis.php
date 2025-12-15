<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<style>
.result-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 1rem;
    animation: slideIn 0.3s ease;
}
.result-error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 1rem;
    animation: slideIn 0.3s ease;
}
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.scan-history {
    max-height: 300px;
    overflow-y: auto;
}

.list-group-item {
    transition: background-color 0.2s ease;
}
</style>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item">
                <a href="<?= base_url() ?>">Katalog</a>
            </li>
            <li class="breadcrumb-item"><a href="#">Form</a></li>
            <li class="breadcrumb-item active" aria-current="page">Peminjaman Otomatis</li>
        </ol>
    </nav>

    <div class="scan-card">
        <div class="card border-light shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Peminjaman / Pengembalian Otomatis</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i> <strong>Cara Pakai:</strong><br>
                    1. Masukkan NISN/NIP user<br>
                    2. Scan kartu RFID buku<br>
                    3. Sistem otomatis mendeteksi peminjaman atau pengembalian
                </div>

                <form id="formScan">
                    <div class="mb-3">
                        <label for="nisn" class="form-label required">
                            <i class="bi bi-person-badge"></i> NISN / NIP
                        </label>
                        <input type="text" id="nisn" class="form-control" 
                               placeholder="Ketik atau scan NISN/NIP user"
                               autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                        <label for="uid" class="form-label required">
                            <i class="bi bi-upc-scan"></i> Scan UID Buku
                        </label>
                        <input type="text" id="uid" class="form-control uid-input" 
                               placeholder="Tap buku di sini"
                               autocomplete="off" required>
                        <small class="text-muted">Focus akan otomatis kembali ke field ini setelah scan</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="btnScan" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Proses Scan
                        </button>
                        <button type="button" id="btnReset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset Form
                        </button>
                    </div>
                </form>

                <div id="result"></div>
            </div>
        </div>

        <!-- Riwayat Scan -->
        <div class="card border-light shadow-sm mt-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock-history"></i> Riwayat Scan Terakhir</span>
                <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" 
                   data-bs-target="#collapseHistory" aria-expanded="true"></i>
            </div>
            <div class="collapse show" id="collapseHistory">
                <div class="card-body scan-history" id="scanHistory">
                    <p class="text-muted text-center">Belum ada riwayat scan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let scanHistory = [];

document.addEventListener('DOMContentLoaded', function() {
    const formScan = document.getElementById('formScan');
    const uidInput = document.getElementById('uid');
    const nisnInput = document.getElementById('nisn');
    const btnScan = document.getElementById('btnScan');
    const btnReset = document.getElementById('btnReset');

    // Auto focus UID setelah input NISN
    nisnInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            uidInput.focus();
        }
    });

    // Reset form
    btnReset.addEventListener('click', function() {
        formScan.reset();
        document.getElementById('result').innerHTML = '';
        nisnInput.focus();
    });

    // Submit form
    formScan.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const uid = uidInput.value.trim();
        const nisn = nisnInput.value.trim();

        if (!uid || !nisn) {
            showResult('error', 'UID dan NISN/NIP wajib diisi!');
            return;
        }

        // Disable button
        btnScan.disabled = true;
        btnScan.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

        fetch('<?= base_url("automate/process") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `uid=${encodeURIComponent(uid)}&nisn=${encodeURIComponent(nisn)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const type = data.type === 'return' ? 'Pengembalian' : 'Peminjaman';
                const trustInfo = data.trust_score ? `<br><i class="bi bi-award"></i> Trust Score: ${data.trust_score}` : '';
                const maxBorrowInfo = data.max_borrow ? ` (Max: ${data.max_borrow} buku)` : '';
                const dueDateInfo = data.due_date ? `<br><i class="bi bi-calendar"></i> Jatuh Tempo: ${data.due_date}` : '';
                
                const message = `
                    <strong>${type} Berhasil!</strong><br>
                    <i class="bi bi-person"></i> User: ${data.user || '-'}${trustInfo}${maxBorrowInfo}<br>
                    <i class="bi bi-book"></i> Buku: ${data.book || '-'}${dueDateInfo}
                `;
                showResult('success', message);
                
                // Tambah ke history
                addToHistory(type, data.user, data.book);
                
                // Auto clear UID dan focus
                setTimeout(() => {
                    uidInput.value = '';
                    uidInput.focus();
                }, 1500);
            } else {
                showResult('error', data.message || 'Terjadi kesalahan');
            }
        })
        .catch(err => {
            console.error(err);
            showResult('error', 'Terjadi kesalahan koneksi');
        })
        .finally(() => {
            btnScan.disabled = false;
            btnScan.innerHTML = '<i class="bi bi-check-circle"></i> Proses Scan';
        });
    });

    // Auto clear result
    function showResult(type, message) {
        const resultDiv = document.getElementById('result');
        const alertClass = type === 'success' ? 'result-success' : 'result-error';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-x-circle';
        
        resultDiv.innerHTML = `
            <div class="${alertClass}">
                <i class="bi ${icon}"></i> ${message}
            </div>
        `;

        setTimeout(() => {
            resultDiv.innerHTML = '';
        }, 5000);
    }

    function addToHistory(type, user, book) {
        const now = new Date().toLocaleString('id-ID');
        scanHistory.unshift({
            type: type,
            user: user,
            book: book,
            time: now
        });

        // Batas history maksimal 10
        if (scanHistory.length > 10) {
            scanHistory = scanHistory.slice(0, 10);
        }

        renderHistory();
    }

    function renderHistory() {
        const historyDiv = document.getElementById('scanHistory');
        
        if (scanHistory.length === 0) {
            historyDiv.innerHTML = '<p class="text-muted text-center">Belum ada riwayat scan</p>';
            return;
        }

        let html = '<div class="list-group list-group-flush">';
        scanHistory.forEach((item, index) => {
            const badgeClass = item.type === 'Peminjaman' ? 'bg-primary' : 'bg-success';
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="badge ${badgeClass}">${item.type}</span>
                        <small class="text-muted">${item.time}</small>
                    </div>
                    <div class="mt-1">
                        <small><i class="bi bi-person"></i> ${item.user}</small><br>
                        <small><i class="bi bi-book"></i> ${item.book}</small>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        historyDiv.innerHTML = html;
    }

    // Focus ke NISN saat load
    nisnInput.focus();

    // Initialize collapse for history
    const collapse = document.querySelector('[data-bs-target="#collapseHistory"]');
    const collapseDiv = document.getElementById('collapseHistory');
    
    collapseDiv.addEventListener('show.bs.collapse', function () {
        collapse.classList.remove('bi-chevron-down');
        collapse.classList.add('bi-chevron-up');
    });
    
    collapseDiv.addEventListener('hide.bs.collapse', function () {
        collapse.classList.remove('bi-chevron-up');
        collapse.classList.add('bi-chevron-down');
    });
});
</script>

<?= $this->endSection() ?>