<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<style>
    .page-item.active .page-link {
        background-color: #f4f4f4;
        border-color: #dee2e6;
        color: white;
    }
    .ui-autocomplete {
        z-index: 2000 !important;
    }
    .required::after {
        content: "*";
        color: red;
        margin-left: 2px;
    }
</style>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item"><a href="#">Form</a></li>
            <li class="breadcrumb-item"><a href="#">Peminjaman Manual</a></li>
            <li class="breadcrumb-item active" aria-current="page">Peminjaman Kelas</li>
        </ol>
    </nav>

    <!-- HEADER KELAS -->
    <div class="card border-light shadow-sm mt-4" id="headerKelasCard" style="<?= session('role') === 'guru' ? 'display: none;' : '' ?>">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label for="CariKelas" class="form-label required fw-semibold">Pilih Kelas:</label>
                    <input type="text" class="form-control" id="CariKelas" name="CariKelas" 
                           placeholder="Contoh: Gumujeng" required>
                    <small class="text-muted">Transaksi untuk kelas yang dipilih</small>
                </div>
                <div class="col-md-6">
                    <div id="classInfo" class="d-none">
                        <div class="alert alert-info mb-0">
                            <strong>Kelas: <span id="selectedClassName"></span></strong><br>
                            <small>Siswa: <span id="studentCount">0</span> | Buku: <span id="bookCount">0</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GURU CLASS INFO -->
    <div class="alert alert-info mt-4" id="guruClassInfo" style="<?= session('role') === 'guru' ? '' : 'display: none;' ?>">
        <strong>Kelas Anda: <span id="guruClassName">-</span></strong><br>
        <small>Siswa: <span id="guruStudentCount">0</span> | Buku: <span id="guruBookCount">0</span></small>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#exampleModal" 
                id="peminjamanBtn" disabled>
            <i class="bi bi-plus-circle"></i> Tambah Peminjaman
        </button>
    </div>

    <!-- LIST PEMINJAMAN -->
    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List peminjaman
            <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" 
               data-bs-target="#collapsePeminjamanExample" aria-expanded="false" 
               aria-controls="collapsePeminjamanExample">
            </i>
        </div>
        <div class="card-body">
            <div class="collapse" id="collapsePeminjamanExample">
                <!-- SEARCH -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchPeminjaman" class="form-control" 
                               placeholder="Cari Nama/Buku...">
                        <button class="btn btn-outline-success" type="button" id="cariPeminjaman">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Buku</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyBorrowings">
                        <tr>
                            <td colspan="4" class="text-center">
                                Pilih kelas terlebih dahulu untuk melihat data peminjaman
                            </td>
                        </tr>
                    </tbody>
                </table>
                <nav aria-label="Pagination untuk peminjaman">
                    <ul class="pagination" id="paginationBorrowings">
                        <!-- Generated dynamically by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" 
                id="pengembalianBtn" disabled>
            <i class="bi bi-arrow-left-circle"></i> Tambah Pengembalian
        </button>
    </div>

    <!-- LIST PENGEMBALIAN -->
    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List pengembalian
            <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" 
               data-bs-target="#collapsePengembalianExample" aria-expanded="false" 
               aria-controls="collapsePengembalianExample">
            </i>
        </div>
        <div class="card-body">
            <div class="collapse" id="collapsePengembalianExample">
                <!-- SEARCH -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchPengembalian" class="form-control" 
                               placeholder="Cari Nama/Buku...">
                        <button class="btn btn-outline-success" type="button" id="cariPengembalian">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Buku</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyReturns">
                        <tr>
                            <td colspan="4" class="text-center">
                                Pilih kelas terlebih dahulu untuk melihat data pengembalian
                            </td>
                        </tr>
                    </tbody>
                </table>
                <nav aria-label="Pagination untuk pengembalian">
                    <ul class="pagination" id="paginationReturns">
                        <!-- Generated dynamically by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Form Peminjaman</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="transactionForm">
            <div class="modal-body">
                <input type="hidden" id="selectedClassId" name="class_id">
                
                <!-- Tambah peminjaman -->
                <div id="peminjamanSection">
                    <div class="row mb-3">
                        <div class="col siswa-select">
                            <label for="namaCari" class="form-label required">Cari Nama Siswa</label>
                            <input type="text" class="form-control" id="namaCari" name="namaCari" 
                                   placeholder="Ketik nama siswa">
                            <small class="text-muted">Siswa yang terdaftar di kelas ini</small>
                        </div>
                        <div class="col">
                            <label for="judulCari" class="form-label required">Cari Judul Buku</label>
                            <input type="text" class="form-control" id="judulCari" name="judulCari" 
                                   placeholder="Ketik judul">
                            <small class="text-muted">Buku yang tersedia di kelas ini</small>
                        </div>
                    </div>
                </div>
                
                <!-- Tambah pengembalian -->
                <div id="pengembalianSection" style="display: none;">
                    <div class="row mb-3">
                        <div class="col siswa-select">
                            <label for="namaCariPengembalian" class="form-label required">Cari Nama Siswa</label>
                            <input type="text" class="form-control" id="namaCariPengembalian" 
                                   name="namaCariPengembalian" placeholder="Ketik nama siswa">
                            <small class="text-muted">Siswa yang memiliki peminjaman aktif</small>
                        </div>
                        <div class="col">
                            <label for="judulCariPengembalian" class="form-label required">Cari Judul Buku</label>
                            <input type="text" class="form-control" id="judulCariPengembalian" 
                                   name="judulCariPengembalian" placeholder="Ketik judul">
                            <small class="text-muted">Buku yang dipinjam siswa</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('exampleModal'));
    const modalElement = document.getElementById('exampleModal');
    const modalTitle = modalElement.querySelector('.modal-title');
    const peminjamanBtn = document.getElementById('peminjamanBtn');
    const pengembalianBtn = document.getElementById('pengembalianBtn');
    const peminjamanSection = document.getElementById('peminjamanSection');
    const pengembalianSection = document.getElementById('pengembalianSection');
    const transactionForm = document.getElementById('transactionForm');
    
    let currentClassId = null;
    let currentClassName = null;
    let classStudents = [];
    let classBooks = [];
    let activeBorrowings = [];
    let allClasses = <?= json_encode($classes) ?>;
    
    // Get current user info
    const userRole = "<?= session('role') ?>";
    const userClassId = "<?= session('class_id') ?>";
    
    let isFormSubmitting = false;
    let showClassSelectionToast = true; // Flag to control toast display
    
    // Cache for class data
    const classDataCache = {};
    const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

    // Collapse icon toggle
    const collapsePeminjaman = document.getElementById('collapsePeminjamanExample');
    const chevronPeminjaman = document.querySelector('[data-bs-target="#collapsePeminjamanExample"]');
    const collapsePengembalian = document.getElementById('collapsePengembalianExample');
    const chevronPengembalian = document.querySelector('[data-bs-target="#collapsePengembalianExample"]');

    if (collapsePeminjaman && chevronPeminjaman) {
        collapsePeminjaman.addEventListener('show.bs.collapse', function () {
            chevronPeminjaman.classList.remove('bi-chevron-down');
            chevronPeminjaman.classList.add('bi-chevron-up');
        });
        collapsePeminjaman.addEventListener('hide.bs.collapse', function () {
            chevronPeminjaman.classList.remove('bi-chevron-up');
            chevronPeminjaman.classList.add('bi-chevron-down');
        });
    }

    if (collapsePengembalian && chevronPengembalian) {
        collapsePengembalian.addEventListener('show.bs.collapse', function () {
            chevronPengembalian.classList.remove('bi-chevron-down');
            chevronPengembalian.classList.add('bi-chevron-up');
        });
        collapsePengembalian.addEventListener('hide.bs.collapse', function () {
            chevronPengembalian.classList.remove('bi-chevron-up');
            chevronPengembalian.classList.add('bi-chevron-down');
        });
    }

    // Utility function
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    }

    // Autocomplete for class
    $('#CariKelas').autocomplete({
        source: function(request, response) {
            const results = allClasses
                .map(c => c.nama_kelas)
                .filter(nama => nama && nama.toLowerCase().includes(request.term.toLowerCase()));
            response(results);
        },
        minLength: 1,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            
            const selectedClass = allClasses.find(c => c.nama_kelas === ui.item.value);
            if (selectedClass) {
                loadClassData(selectedClass.id);
            }
            return false;
        }
    });

    // CACHING
    function loadClassData(classId) {
        currentClassId = classId;
        
        const now = Date.now();
        const cachedData = classDataCache[classId];
        
        if (cachedData && (now - cachedData.timestamp) < CACHE_DURATION) {
            console.log('Using cached data for class', classId);
            processClassData(cachedData.data);
            return;
        }
        
        // Fetch from server
        $.get("<?= base_url('peminjaman-kelas/class-data') ?>", { class_id: classId }, function(response) {
            if (response.success) {
                classDataCache[classId] = {
                    data: response,
                    timestamp: Date.now()
                };
                
                processClassData(response);
            } else {
                showToast(response.message || 'Gagal memuat data kelas', 'error');
            }
        }).fail(function() {
            showToast('Terjadi kesalahan saat memuat data kelas', 'error');
        });
    }

    function processClassData(response) {
        currentClassName = response.class.nama_kelas;
        classStudents = response.students || [];
        classBooks = response.books || [];
        
        $('#CariKelas').val(currentClassName);
        $('#selectedClassName').text(currentClassName);
        $('#studentCount').text(classStudents.length);
        $('#bookCount').text(classBooks.length);
        $('#classInfo').removeClass('d-none');
        
        // Update guru class info
        $('#guruClassName').text(currentClassName);
        $('#guruStudentCount').text(classStudents.length);
        $('#guruBookCount').text(classBooks.length);
        
        peminjamanBtn.disabled = false;
        pengembalianBtn.disabled = false;
        
        setupStudentAutocomplete();
        setupBookAutocomplete();
        setupReturnStudentAutocomplete();
        setupReturnBookAutocomplete();
        loadTransactions('borrow');
        loadTransactions('return');
        
        // Only show toast if this is a fresh class selection, not a data refresh
        if (showClassSelectionToast) {
            showToast('Kelas ' + currentClassName + ' berhasil dipilih');
        }
        showClassSelectionToast = true; // Reset flag for next selection
    }

    // Setup autocomplete for student
    function setupStudentAutocomplete() {
        $('#namaCari').autocomplete({
            source: function(request, response) {
                const results = classStudents
                    .map(s => s.nama)
                    .filter(nama => nama && nama.toLowerCase().includes(request.term.toLowerCase()));
                response(results);
            },
            minLength: 1,
            select: function(event, ui) {
                setTimeout(() => $('#judulCari').focus(), 100);
                return true;
            }
        });
    }

    // Setup autocomplete for books
    function setupBookAutocomplete() {
        $('#judulCari').autocomplete({
            source: function(request, response) {
                const results = classBooks
                    .filter(b => b.class_quantity > 0) // Only show books with available quantity
                    .map(b => ({
                        label: `${b.title} (${b.class_quantity} tersedia)`,
                        value: b.title,
                        book: b
                    }))
                    .filter(item => item.label.toLowerCase().includes(request.term.toLowerCase()));
                response(results);
            },
            minLength: 1,
            select: function(event, ui) {
                const selectedBook = ui.item.book;
                
                // Show UID input
                if (selectedBook && selectedBook.uid && (
                    (Array.isArray(selectedBook.uid) && selectedBook.uid.length > 0) ||
                    (!Array.isArray(selectedBook.uid) && String(selectedBook.uid).trim() !== '')
                )) {
                    $('#uidInputSection').show();
                } else {
                    $('#uidInputSection').hide();
                }
                
                return true;
            }
        });

        $('#judulCari').on('input', function() {
            if (!$(this).val().trim()) {
                $('#uidInputSection').hide();
            }
        });
    }

    // autocomplete
    function setupReturnStudentAutocomplete() {
        $('#namaCariPengembalian').autocomplete({
            source: function(request, response) {
                const studentsWithBorrows = activeBorrowings
                    .map(b => {
                        const student = classStudents.find(s => s.id === b.user_id);
                        return student ? student.nama : null;
                    })
                    .filter(nama => nama && nama.toLowerCase().includes(request.term.toLowerCase()));
                
                const uniqueNames = [...new Set(studentsWithBorrows)];
                response(uniqueNames);
            },
            minLength: 1,
            select: function(event, ui) {
                updateAvailableBooksForReturn(ui.item.value);
                return true;
            }
        });

        $('#namaCariPengembalian').on('change', function() {
            const nama = $(this).val();
            if (nama) {
                updateAvailableBooksForReturn(nama);
            }
        });
    }

    // Setup autocomplete for return books
    function setupReturnBookAutocomplete() {
        $('#judulCariPengembalian').autocomplete({
            source: [],
            minLength: 1
        });
    }

    function updateAvailableBooksForReturn(nama) {
        const student = classStudents.find(s => s.nama === nama);
        if (!student) {
            $('#judulCariPengembalian').autocomplete('option', 'source', []);
            return;
        }
        
        const studentBorrowings = activeBorrowings.filter(b => b.user_id === student.id);
        const borrowedTitles = studentBorrowings.map(b => b.judul).filter(Boolean);
        
        $('#judulCariPengembalian').autocomplete('option', 'source', borrowedTitles);
        
        if (borrowedTitles.length > 0) {
            $('#judulCariPengembalian').autocomplete('search', '');
        } else {
            showToast('Siswa ini tidak memiliki peminjaman aktif', 'warning');
        }
    }

    // Load transactions for selected class
    function loadTransactions(type) {
        if (!currentClassId) return;
        
        $.get("<?= base_url('peminjaman-kelas/transactions') ?>", {
            class_id: currentClassId,
            type: type
        }, function(response) {
            if (response.success) {
                if (type === 'borrow') {
                    renderBorrowingsTable(response.transactions);
                    activeBorrowings = response.transactions.filter(t => t.status === 'active');
                    setupReturnStudentAutocomplete();
                } else if (type === 'return') {
                    renderReturnsTable(response.transactions);
                }
            }
        });
    }

    // Render borrowings table
    function renderBorrowingsTable(transactions) {
        const tbody = $('#tbodyBorrowings');
        
        if (!transactions || transactions.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">Belum ada data peminjaman.</td></tr>');
            return;
        }
        
        let html = '';
        transactions.forEach((t, index) => {
            const statusClass = t.status === 'active' ? 'table-danger' : '';
            
            html += `<tr class="${statusClass}">
                <th scope="row">${index + 1}</th>
                <td>${escapeHtml(t.nama)}</td>
                <td>${escapeHtml(t.judul)}</td>
                <td>${escapeHtml(t.tanggal)}</td>
            </tr>`;
        });
        
        tbody.html(html);
    }

    function renderReturnsTable(transactions) {
        const tbody = $('#tbodyReturns');
        
        if (!transactions || transactions.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">Belum ada data pengembalian.</td></tr>');
            return;
        }
        
        let html = '';
        transactions.forEach((t, index) => {
            html += `<tr>
                <th scope="row">${index + 1}</th>
                <td>${escapeHtml(t.nama)}</td>
                <td>${escapeHtml(t.judul)}</td>
                <td>${escapeHtml(t.tanggal)}</td>
            </tr>`;
        });
        
        tbody.html(html);
    }

    // Modal handlers
    peminjamanBtn.addEventListener('click', function() {
        if (!currentClassId) {
            showToast('Pilih kelas terlebih dahulu', 'error');
            return;
        }
        
        modalTitle.textContent = 'Form Peminjaman';
        peminjamanSection.style.display = 'block';
        pengembalianSection.style.display = 'none';
        transactionForm.dataset.type = 'borrow';
        $('#selectedClassId').val(currentClassId);
        
        $('#namaCariPengembalian, #judulCariPengembalian').removeAttr('required');
        $('#namaCari, #judulCari').attr('required', 'required');
        // IGNORE UID
        $('#uidCari').removeAttr('required');
    });

    pengembalianBtn.addEventListener('click', function() {
        if (!currentClassId) {
            showToast('Pilih kelas terlebih dahulu', 'error');
            return;
        }
        
        modalTitle.textContent = 'Form Pengembalian';
        peminjamanSection.style.display = 'none';
        pengembalianSection.style.display = 'block';
        transactionForm.dataset.type = 'return';
        $('#selectedClassId').val(currentClassId);
        
        $('#namaCari, #judulCari, #uidCari').removeAttr('required');
        $('#namaCariPengembalian, #judulCariPengembalian').attr('required', 'required');
    });

    // Form submit handler
    transactionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isFormSubmitting) {
            return;
        }
        
        const formType = this.dataset.type;
        const formData = new FormData(this);
        
        let url;
        if (formType === 'borrow') {
            url = "<?= base_url('peminjaman-kelas/add') ?>";
        } else {
            url = "<?= base_url('peminjaman-kelas/return') ?>";
        }
        
        isFormSubmitting = true;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                isFormSubmitting = false;
                
                if (response.success) {
                    $(modalElement).modal('hide');
                    showToast(response.message || 'Transaksi berhasil!');
                    
                    // Clear cache for this class
                    delete classDataCache[currentClassId];
                    
                    // Reload transactions and class data without showing class selection toast
                    showClassSelectionToast = false;
                    loadTransactions('borrow');
                    loadTransactions('return');
                    loadClassData(currentClassId);
                    
                    // Reset form
                    transactionForm.reset();
                    $('#uidInputSection').hide();
                } else {
                    showToast(response.message || 'Gagal menyimpan transaksi', 'error');
                }
            },
            error: function(xhr) {
                isFormSubmitting = false;
                console.error('AJAX Error:', xhr.responseText);
                showToast('Terjadi kesalahan saat menyimpan transaksi', 'error');
            }
        });
    });

    $(modalElement).on('hidden.bs.modal', function() {
        transactionForm.reset();
        $('#uidInputSection').hide();
        $('#namaCari, #judulCari, #namaCariPengembalian, #judulCariPengembalian, #uidCari').removeAttr('required');
        isFormSubmitting = false;
    });

    // Search functionality
    function filterTable(searchId, tbodyId) {
        const query = document.getElementById(searchId).value.toLowerCase();
        const rows = document.querySelectorAll('#' + tbodyId + ' tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    const searchPeminjamanInput = document.getElementById('searchPeminjaman');
    const cariPeminjamanBtn = document.getElementById('cariPeminjaman');
    const searchPengembalianInput = document.getElementById('searchPengembalian');
    const cariPengembalianBtn = document.getElementById('cariPengembalian');

    if (searchPeminjamanInput) {
        searchPeminjamanInput.addEventListener('input', function() {
            filterTable('searchPeminjaman', 'tbodyBorrowings');
        });
    }
    
    if (cariPeminjamanBtn) {
        cariPeminjamanBtn.addEventListener('click', function() {
            filterTable('searchPeminjaman', 'tbodyBorrowings');
        });
    }
    
    if (searchPengembalianInput) {
        searchPengembalianInput.addEventListener('input', function() {
            filterTable('searchPengembalian', 'tbodyReturns');
        });
    }
    
    if (cariPengembalianBtn) {
        cariPengembalianBtn.addEventListener('click', function() {
            filterTable('searchPengembalian', 'tbodyReturns');
        });
    }
    
    // Auto-load class for guru
    if (userRole === 'guru' && userClassId) {
        const guruClass = allClasses.find(c => c.id == userClassId);
        if (guruClass) {
            console.log('Auto-loading class for guru:', guruClass);
            showClassSelectionToast = false; // Don't show toast for auto-load
            loadClassData(userClassId);
        } else {
            console.error('Guru class not found:', userClassId, allClasses);
            showToast('Kelas yang Anda ajar tidak ditemukan', 'error');
        }
    }
});
</script>

<?= $this->endSection() ?>