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
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
        </ol>
    </nav>

    <!-- SISWA -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h4>Data Siswa</h4>
        <div class="gap-2 d-flex">
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddSiswaModal()">
                <i class="bi bi-plus-circle"></i> Tambah Siswa
            </button>
            <button type="button" class="btn btn-warning btn-sm" onclick="openEditSiswaModal()">
                <i class="bi bi-pencil-square"></i> Edit Siswa
            </button>
        </div>
    </div>

    <div class="card border-light mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>List Siswa</span>
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" 
                   data-bs-target="#collapseSiswa" aria-expanded="true"></i>
            </div>
        </div>
        <div class="collapse show" id="collapseSiswa">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchSiswa" class="form-control" placeholder="Cari NISN/Nama...">
                    <button class="btn btn-outline-success" type="button" id="cariSiswa">
                        <i class="bi bi-search"></i>
                    </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th style="cursor: pointer; user-select: none;" onclick="sortSiswaTable('trust_score')">
                                    Bintang
                                    <i class="bi bi-star-fill text-warning" style="margin-left:2px;"></i>
                                    <i class="bi bi-arrow-down-up" style="font-size: 0.85rem; opacity: 0.6;"></i>
                                </th>
                                <th>Total Peminjaman</th>
                                <th>Maks Pinjam</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySiswa">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Total: <span id="totalSiswa">0</span> siswa</small>
                    <nav aria-label="Siswa pagination">
                        <ul class="pagination pagination-sm mb-0" id="paginationSiswa">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION GURU -->
    <div class="d-flex justify-content-between align-items-center mt-5">
        <h4>Data Guru</h4>
        <div class="gap-2 d-flex">
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddGuruModal()">
                <i class="bi bi-plus-circle"></i> Tambah Guru
            </button>
            <button type="button" class="btn btn-warning btn-sm" onclick="openEditGuruModal()">
                <i class="bi bi-pencil-square"></i> Edit Guru
            </button>
        </div>
    </div>

    <div class="card border-light mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>List Guru</span>
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" 
                   data-bs-target="#collapseGuru" aria-expanded="true"></i>
            </div>
        </div>
        <div class="collapse show" id="collapseGuru">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchGuru" class="form-control" placeholder="Cari NIP/Nama...">
                    <button class="btn btn-outline-success" type="button" id="cariGuru">
                        <i class="bi bi-search"></i>
                    </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGuru">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Total: <span id="totalGuru">0</span> guru</small>
                    <nav aria-label="Guru pagination">
                        <ul class="pagination pagination-sm mb-0" id="paginationGuru">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSiswaTitle">Form Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSiswa" onsubmit="handleSiswaSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="siswaMode" value="add">
                    <input type="hidden" id="siswaId" value="">

                    <!-- Cari Nama -->
                    <div id="siswaSearchSection" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label required">Cari Nama</label>
                            <input type="text" class="form-control" id="siswaSearch" 
                                   placeholder="Ketik nama untuk mencari...">
                            <small class="text-muted">Ketik nama siswa yang ingin diubah</small>
                        </div>
                        <hr>
                    </div>

                    <div id="siswaFormFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="siswaNisn" class="form-label required">NISN</label>
                                    <input type="text" class="form-control" id="siswaNisn" 
                                           name="nisn" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="siswaNama" class="form-label required">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="siswaNama" 
                                           name="nama" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="siswaKelas" class="form-label required">Kelas</label>
                                    <select class="form-select" id="siswaKelas" name="class_id" required>
                                        <option value="">-- Pilih Kelas --</option>
                                    </select>
                                    <small class="text-muted">Pilih kelas dari daftar yang tersedia</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="siswaMaxBorrow" class="form-label required">Maksimal Peminjaman</label>
                                    <input type="number" name="maxBorrow" class="form-control" id="siswaMaxBorrow" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Guru -->
<div class="modal fade" id="modalGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGuruTitle">Form Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formGuru" onsubmit="handleGuruSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="guruMode" value="add">
                    <input type="hidden" id="guruId" value="">

                    <!-- Search Nama (hanya untuk edit) -->
                    <div id="guruSearchSection" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label required">Cari Nama Guru</label>
                            <input type="text" class="form-control" id="guruSearch" 
                                   placeholder="Ketik nama guru untuk mencari...">
                            <small class="text-muted">Ketik nama guru yang ingin diubah</small>
                        </div>
                        <hr>
                    </div>

                    <!-- Form Fields - 2 Kolom -->
                    <div id="guruFormFields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guruNip" class="form-label required">NIP</label>
                                    <input type="text" class="form-control" id="guruNip" 
                                           name="nip" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guruNama" class="form-label required">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="guruNama" 
                                           name="nama" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guruKelas" class="form-label required">Kelas</label>
                                    <select class="form-select" id="guruKelas" name="class_id" required>
                                        <option value="">-- Pilih Kelas --</option>
                                    </select>
                                    <small class="text-muted">Pilih kelas dari daftar yang tersedia</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guruJabatan" class="form-label required">Jabatan</label>
                                    <input type="text" class="form-control" id="guruJabatan" name="jabatan" placeholder="Contoh: Guru Matematika" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global Variables
let siswaList = [];
let guruList = [];
let classList = [];
let modalSiswa, modalGuru;
let currentSiswaPage = 1;
let currentGuruPage = 1;
const itemsPerPage = 20;

// Sorting variables
let siswaSortField = null;
let siswaSortOrder = 'asc'; // 'asc' or 'desc'

// Initialize on DOM Load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap Modals
    modalSiswa = new bootstrap.Modal(document.getElementById('modalSiswa'));
    modalGuru = new bootstrap.Modal(document.getElementById('modalGuru'));
    
    // Load Data dan Automplete
    fetchClassData();
    fetchSiswaData();
    fetchGuruData();
    setupAutocomplete();
    
    // Setup Search
    const searchSiswaEl = document.getElementById('searchSiswa');
    if (searchSiswaEl) {
        searchSiswaEl.addEventListener('input', filterSiswaTable);
    }
    const searchGuruEl = document.getElementById('searchGuru');
    if (searchGuruEl) {
        searchGuruEl.addEventListener('input', filterGuruTable);
    }
});

// NOTIFIKASI
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : '!';
    toast.innerHTML = `
        <div style="margin-right: 12px; font-size: 20px; font-weight: bold;">
            ${icon}
        </div>
        <div style="flex: 1;">${message}</div>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ================== FETCH DATA ==================
function fetchClassData() {
    fetch("<?= base_url('management-class/list') ?>")
        .then(response => response.json())
        .then(data => { // <-- perbaiki di sini
            console.log('Class Data:', data);
            if (data.success && Array.isArray(data.classes)) {
                classList = data.classes;
                populateClassDropdown();
            } else {
                showToast('Gagal memuat data kelas', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat memuat data kelas', 'error');
        });
}

function populateClassDropdown() {
    const selectElement = document.getElementById('siswaKelas');
    selectElement.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    
    classList.forEach(cls => {
        const option = document.createElement('option');
        option.value = cls.id;
        option.textContent = cls.nama_kelas;
        selectElement.appendChild(option);
    });
}

function populateGuruClassDropdown() {
    const selectElement = document.getElementById('guruKelas');
    selectElement.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    
    classList.forEach(cls => {
        const option = document.createElement('option');
        option.value = cls.id;
        option.textContent = cls.nama_kelas;
        selectElement.appendChild(option);
    });
}

function getClassNameById(classId) {
    const cls = classList.find(c => c.id === classId);
    return cls ? cls.nama_kelas : '-';
}

function sortSiswaTable(field) {
    // If clicking the same field, toggle sort order
    if (siswaSortField === field) {
        siswaSortOrder = siswaSortOrder === 'asc' ? 'desc' : 'asc';
    } else {
        // If clicking a new field, set it to ascending
        siswaSortField = field;
        siswaSortOrder = 'asc';
    }
    
    // Sort the siswaList
    siswaList.sort((a, b) => {
        let aValue, bValue;
        
        if (field === 'trust_score') {
            aValue = parseFloat(a.trust_score || 0);
            bValue = parseFloat(b.trust_score || 0);
        }
        
        if (siswaSortOrder === 'asc') {
            return aValue - bValue;
        } else {
            return bValue - aValue;
        }
    });
    
    // Reset to first page and re-render
    currentSiswaPage = 1;
    renderSiswaTable(siswaList);
}

function fetchSiswaData() {
    fetch("<?= base_url('user/list/murid') ?>")
        .then(response => response.json())
        .then(data => {
            console.log('Siswa Data:', data);
            if (data.success && Array.isArray(data.users)) {
                siswaList = data.users;
                currentSiswaPage = 1; // Reset pagination
                renderSiswaTable(siswaList);
                setupAutocomplete();
            } else {
                showToast('Gagal memuat data siswa', 'error');
                document.getElementById('tbodySiswa').innerHTML = 
                    '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat memuat data siswa', 'error');
        });
}

function fetchGuruData() {
    fetch("<?= base_url('user/list/guru') ?>")
        .then(response => response.json())
        .then(data => {
            console.log('Guru Data:', data);
            if (data.success && Array.isArray(data.users)) {
                guruList = data.users;
                currentGuruPage = 1; // Reset pagination
                renderGuruTable(guruList);
                setupAutocomplete();
            } else {
                showToast('Gagal memuat data guru', 'error');
                document.getElementById('tbodyGuru').innerHTML = 
                    '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat memuat data guru', 'error');
        });
}

function renderSiswaTable(data) {
    const tbody = document.getElementById('tbodySiswa');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Belum ada data siswa</td></tr>';
        document.getElementById('totalSiswa').textContent = '0';
        document.getElementById('paginationSiswa').innerHTML = '';
        return;
    }
    
    document.getElementById('totalSiswa').textContent = data.length;
    
    // Calculate pagination
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const startIndex = (currentSiswaPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);
    
    let html = '';
    paginatedData.forEach((siswa, index) => {
        const actualIndex = startIndex + index + 1;
        const trustScore = parseInt(siswa.trust_score ?? 0);
        const maxBorrow = siswa.maxBorrow || siswa.max_borrow || 1;
        const className = getClassNameById(siswa.class_id);

        // Ambil total peminjaman dari field num_borrows
        let totalBorrow = typeof siswa.num_borrows !== 'undefined' ? siswa.num_borrows : '-';

        html += `
            <tr>
                <td>${actualIndex}</td>
                <td>${siswa.nisn || '-'}</td>
                <td>${siswa.nama || '-'}</td>
                <td>${className}</td>
                <td>
                    <span class="badge bg-warning text-dark" style="font-size:1em;">
                        <i class="bi bi-star-fill"></i> ${trustScore}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info text-dark" style="font-size:1em;">
                        <i class="bi bi-book"></i> ${totalBorrow}
                    </span>
                </td>
                <td>${maxBorrow} buku</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    renderSiswaPagination(totalPages);
}

function renderSiswaPagination(totalPages) {
    const paginationEl = document.getElementById('paginationSiswa');
    paginationEl.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentSiswaPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToSiswaPage(${currentSiswaPage - 1}); return false;">← Sebelumnya</a>`;
    paginationEl.appendChild(prevLi);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        // Show first page, last page, and pages around current page
        if (i === 1 || i === totalPages || (i >= currentSiswaPage - 1 && i <= currentSiswaPage + 1)) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentSiswaPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="goToSiswaPage(${i}); return false;">${i}</a>`;
            paginationEl.appendChild(li);
        } else if (i === currentSiswaPage - 2 || i === currentSiswaPage + 2) {
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = `<span class="page-link">...</span>`;
            paginationEl.appendChild(li);
        }
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentSiswaPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToSiswaPage(${currentSiswaPage + 1}); return false;">Berikutnya →</a>`;
    paginationEl.appendChild(nextLi);
}

function goToSiswaPage(page) {
    const totalPages = Math.ceil(siswaList.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentSiswaPage = page;
        renderSiswaTable(siswaList);
        window.scrollTo(0, 0);
    }
}

function renderGuruTable(data) {
    const tbody = document.getElementById('tbodyGuru');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Belum ada data guru</td></tr>';
        document.getElementById('totalGuru').textContent = '0';
        document.getElementById('paginationGuru').innerHTML = '';
        return;
    }
    
    document.getElementById('totalGuru').textContent = data.length;
    
    // Calculate pagination
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const startIndex = (currentGuruPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);
    
    let html = '';
    paginatedData.forEach((guru, index) => {
        const actualIndex = startIndex + index + 1;
        html += `
            <tr>
                <td>${actualIndex}</td>
                <td>${guru.nip || '-'}</td>
                <td>${guru.nama || '-'}</td>
                <td>${guru.jabatan || '-'}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    renderGuruPagination(totalPages);
}

function renderGuruPagination(totalPages) {
    const paginationEl = document.getElementById('paginationGuru');
    paginationEl.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentGuruPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToGuruPage(${currentGuruPage - 1}); return false;">← Sebelumnya</a>`;
    paginationEl.appendChild(prevLi);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        // Show first page, last page, and pages around current page
        if (i === 1 || i === totalPages || (i >= currentGuruPage - 1 && i <= currentGuruPage + 1)) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentGuruPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" onclick="goToGuruPage(${i}); return false;">${i}</a>`;
            paginationEl.appendChild(li);
        } else if (i === currentGuruPage - 2 || i === currentGuruPage + 2) {
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = `<span class="page-link">...</span>`;
            paginationEl.appendChild(li);
        }
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentGuruPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToGuruPage(${currentGuruPage + 1}); return false;">Berikutnya →</a>`;
    paginationEl.appendChild(nextLi);
}

function goToGuruPage(page) {
    const totalPages = Math.ceil(guruList.length / itemsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentGuruPage = page;
        renderGuruTable(guruList);
        window.scrollTo(0, 0);
    }
}

// Filter tabel
function filterSiswaTable() {
    const query = document.getElementById('searchSiswa').value.toLowerCase();
    currentSiswaPage = 1; // Reset to first page on search
    
    const filteredData = siswaList.filter(siswa => {
        const nisn = (siswa.nisn || '').toLowerCase();
        const nama = (siswa.nama || '').toLowerCase();
        return nisn.includes(query) || nama.includes(query);
    });
    
    renderSiswaTable(filteredData);
}

function filterGuruTable() {
    const query = document.getElementById('searchGuru').value.toLowerCase();
    currentGuruPage = 1; // Reset to first page on search
    
    const filteredData = guruList.filter(guru => {
        const nip = (guru.nip || '').toLowerCase();
        const nama = (guru.nama || '').toLowerCase();
        return nip.includes(query) || nama.includes(query);
    });
    
    renderGuruTable(filteredData);
}

// fungsi modal siswa
function openAddSiswaModal() {
    document.getElementById('modalSiswaTitle').textContent = 'Tambah Siswa Baru';
    document.getElementById('siswaMode').value = 'add';
    document.getElementById('siswaSearchSection').style.display = 'none';
    document.getElementById('siswaFormFields').style.display = 'block';
    
    // Reset form
    document.getElementById('formSiswa').reset();
    document.getElementById('siswaId').value = '';
    populateClassDropdown();
    
    modalSiswa.show();
}

function openEditSiswaModal() {
    document.getElementById('modalSiswaTitle').textContent = 'Edit Data Siswa';
    document.getElementById('siswaMode').value = 'edit';
    document.getElementById('siswaSearchSection').style.display = 'block';
    document.getElementById('siswaFormFields').style.display = 'none';
    
    // Reset form
    document.getElementById('formSiswa').reset();
    document.getElementById('siswaId').value = '';
    document.getElementById('siswaSearch').value = '';
    
    modalSiswa.show();
}

// fungsi modal guru
function openAddGuruModal() {
    document.getElementById('modalGuruTitle').textContent = 'Tambah Guru Baru';
    document.getElementById('guruMode').value = 'add';
    document.getElementById('guruSearchSection').style.display = 'none';
    document.getElementById('guruFormFields').style.display = 'block';
    
    // Reset form
    document.getElementById('formGuru').reset();
    document.getElementById('guruId').value = '';
    populateGuruClassDropdown();
    
    modalGuru.show();
}

function openEditGuruModal() {
    document.getElementById('modalGuruTitle').textContent = 'Edit Data Guru';
    document.getElementById('guruMode').value = 'edit';
    document.getElementById('guruSearchSection').style.display = 'block';
    document.getElementById('guruFormFields').style.display = 'none';
    
    // Reset form
    document.getElementById('formGuru').reset();
    document.getElementById('guruId').value = '';
    document.getElementById('guruSearch').value = '';
    
    modalGuru.show();
}

    // autocomplete setup
function setupAutocomplete() {
    // Autocomplete Nama Siswa
    if (typeof $ !== 'undefined' && $.fn.autocomplete) {
        $('#siswaSearch').autocomplete({
            source: function(request, response) {
                const results = siswaList
                    .filter(s => s.nama && s.nama.toLowerCase().includes(request.term.toLowerCase()))
                    .map(s => ({
                        label: `${s.nama} (${s.nisn})`,
                        value: s.nama,
                        data: s
                    }));
                response(results);
            },
            minLength: 1,
            select: function(event, ui) {
                fillSiswaForm(ui.item.data);
                return false;
            }
        });

        // Autocomplete Nama Guru
        $('#guruSearch').autocomplete({
            source: function(request, response) {
                const results = guruList
                    .filter(g => g.nama && g.nama.toLowerCase().includes(request.term.toLowerCase()))
                    .map(g => ({
                        label: `${g.nama} (${g.nip})`,
                        value: g.nama,
                        data: g
                    }));
                response(results);
            },
            minLength: 1,
            select: function(event, ui) {
                fillGuruForm(ui.item.data);
                return false;
            }
        });
    }
}

function fillSiswaForm(siswa) {
    document.getElementById('siswaFormFields').style.display = 'block';
    document.getElementById('siswaId').value = siswa.id;
    document.getElementById('siswaNisn').value = siswa.nisn || '';
    document.getElementById('siswaNama').value = siswa.nama || '';
    
    populateClassDropdown();
    document.getElementById('siswaKelas').value = siswa.class_id || '';
    
    const maxBorrowEl = document.getElementById('siswaMaxBorrow');
    if (maxBorrowEl) {
        maxBorrowEl.value = siswa.maxBorrow || siswa.max_borrow || 1;
    }
}

function fillGuruForm(guru) {
    document.getElementById('guruFormFields').style.display = 'block';
    document.getElementById('guruId').value = guru.id;
    document.getElementById('guruNip').value = guru.nip || '';
    document.getElementById('guruNama').value = guru.nama || '';
    document.getElementById('guruJabatan').value = guru.jabatan || '';
    
    populateGuruClassDropdown();
    document.getElementById('guruKelas').value = guru.class_id || '';
}

// submit handlers
function handleSiswaSubmit(event) {
    event.preventDefault();
    
    const mode = document.getElementById('siswaMode').value;
    const id = document.getElementById('siswaId').value;
    const nisn = document.getElementById('siswaNisn').value.trim();
    const nama = document.getElementById('siswaNama').value.trim();
    const classId = document.getElementById('siswaKelas').value;
    const maxBorrow = document.getElementById('siswaMaxBorrow').value.trim();
    
    if (!nisn || !nama || !classId || !maxBorrow) {
        showToast('Semua field wajib diisi!', 'warning');
        return;
    }
    
    const data = {
        nisn: nisn,
        nama: nama,
        class_id: classId,
        maxBorrow: maxBorrow
    };
    
    const url = mode === 'add' 
        ? "<?= base_url('user/add') ?>"
        : "<?= base_url('user/update') ?>/" + id;
    
    console.log('Sending data:', data);
    console.log('To URL:', url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Response:', result);
        if (result.success) {
            modalSiswa.hide();
            showToast(result.message || (mode === 'add' ? 'Berhasil menambah siswa' : 'Berhasil mengubah siswa'));
            fetchSiswaData();
        } else {
            showToast(result.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
    });
}

function handleGuruSubmit(event) {
    event.preventDefault();
    
    const mode = document.getElementById('guruMode').value;
    const id = document.getElementById('guruId').value;
    const nip = document.getElementById('guruNip').value.trim();
    const nama = document.getElementById('guruNama').value.trim();
    const jabatan = document.getElementById('guruJabatan').value.trim();
    const classId = document.getElementById('guruKelas').value;
    
    if (!nip || !nama || !jabatan || !classId) {
        showToast('Semua field wajib diisi!', 'warning');
        return;
    }
    
    const data = mode === 'add' 
        ? { namaGuru: nama, nip: nip, jabatan: jabatan, class_id: classId }
        : { namaGuruUbah: nama, nipUbah: nip, jabatanUbah: jabatan, classIdUbah: classId };
    
    const url = mode === 'add' 
        ? "<?= base_url('user/add-guru') ?>"
        : "<?= base_url('user/update-guru') ?>/" + id;
    
    console.log('Sending data:', data);
    console.log('To URL:', url);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Response:', result);
        if (result.success) {
            modalGuru.hide();
            showToast(result.message || (mode === 'add' ? 'Berhasil menambah guru' : 'Berhasil mengubah guru'));
            fetchGuruData();
        } else {
            showToast(result.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
    });
}
</script>

<?= $this->endSection() ?>