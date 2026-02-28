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
                            <small>Siswa: <span id="studentCount">0</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4" id="guruClassInfo" style="<?= session('role') === 'guru' ? '' : 'display: none;' ?>">
        <strong>Kelas Anda: <span id="guruClassName">-</span></strong><br>
        <small>Siswa: <span id="guruStudentCount">0</span></small>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#exampleModal" 
                id="peminjamanBtn" disabled>
            <i class="bi bi-plus-circle"></i> Tambah Peminjaman
        </button>
    </div>

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
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchPeminjaman" class="form-control" 
                               placeholder="Cari Nama/Buku...">
                        <button class="btn btn-outline-success" type="button" id="cariPeminjaman">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

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
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchPengembalian" class="form-control" 
                               placeholder="Cari Nama/Buku...">
                        <button class="btn btn-outline-success" type="button" id="cariPengembalian">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

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
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

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
                <input type="hidden" id="selectedUserId" name="user_id">
                <input type="hidden" id="selectedBookId" name="book_id">
                
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
                            <small class="text-muted">Pilih buku yang ingin dipinjam</small>
                        </div>
                    </div>
                </div>
                
                <div id="pengembalianSection" style="display: none;">
                    <div class="mb-3">
                        <label for="searchSiswaReturn" class="form-label">Cari Nama Siswa</label>
                        <input type="text" class="form-control" id="searchSiswaReturn" name="searchSiswaReturn" placeholder="Ketik nama siswa...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Siswa & Buku untuk Dikembalikan</label>
                        <div id="checklistPengembalian" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                            <p class="text-muted text-center" id="noDataReturn">Memuat data...</p>
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
const ITEMS_PER_PAGE = 25;
let borrowingsData = [];
let returnsData = [];
let currentBorrowingsPage = 1;
let currentReturnsPage = 1;

function refreshBorrowingsTable(page) {
    currentBorrowingsPage = page;
    const tbody = $('#tbodyBorrowings');
    
    if (!borrowingsData || borrowingsData.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center">Belum ada data peminjaman.</td></tr>');
        renderBorrowingsPagination();
        return;
    }
    
    const start = (page - 1) * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;
    const pageData = borrowingsData.slice(start, end);
    
    let html = '';
    pageData.forEach((t, index) => {
        const statusClass = t.status === 'active' ? 'table-danger' : '';
        const rowNum = start + index + 1;
        
        html += `<tr class="${statusClass}">
            <th scope="row">${rowNum}</th>
            <td>${escapeHtml(t.user_name || t.nama || '-')}</td>
            <td>${escapeHtml(t.book_title || t.judul || '-')}</td>
            <td>${escapeHtml(t.tanggal)}</td>
        </tr>`;
    });
    
    tbody.html(html);
    renderBorrowingsPagination();
}

function renderBorrowingsPagination() {
    const paginationContainer = $('#paginationBorrowings');
    const totalPages = Math.ceil(borrowingsData.length / ITEMS_PER_PAGE);
    
    if (totalPages <= 1) {
        paginationContainer.html('');
        return;
    }
    
    let html = '';
    
    html += `<li class="page-item ${currentBorrowingsPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshBorrowingsTable(${currentBorrowingsPage - 1})">Previous</a>
    </li>`;
    
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentBorrowingsPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    if (startPage > 1) {
        html += '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="refreshBorrowingsTable(1)">1</a></li>';
        if (startPage > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentBorrowingsPage;
        html += `<li class="page-item ${isActive ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="refreshBorrowingsTable(${i})">${i}</a>
        </li>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="refreshBorrowingsTable(${totalPages})">${totalPages}</a></li>`;
    }
    
    html += `<li class="page-item ${currentBorrowingsPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshBorrowingsTable(${currentBorrowingsPage + 1})">Next</a>
    </li>`;
    
    paginationContainer.html(html);
}

function refreshReturnsTable(page) {
    currentReturnsPage = page;
    const tbody = $('#tbodyReturns');
    
    if (!returnsData || returnsData.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center">Belum ada data pengembalian.</td></tr>');
        renderReturnsPagination();
        return;
    }
    
    const start = (page - 1) * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;
    const pageData = returnsData.slice(start, end);
    
    let html = '';
    pageData.forEach((t, index) => {
        const rowNum = start + index + 1;
        
        html += `<tr>
            <th scope="row">${rowNum}</th>
            <td>${escapeHtml(t.user_name || t.nama || '-')}</td>
            <td>${escapeHtml(t.book_title || t.judul || '-')}</td>
            <td>${escapeHtml(t.tanggal)}</td>
        </tr>`;
    });
    
    tbody.html(html);
    renderReturnsPagination();
}

function renderReturnsPagination() {
    const paginationContainer = $('#paginationReturns');
    const totalPages = Math.ceil(returnsData.length / ITEMS_PER_PAGE);
    
    if (totalPages <= 1) {
        paginationContainer.html('');
        return;
    }
    
    let html = '';
    
    html += `<li class="page-item ${currentReturnsPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(${currentReturnsPage - 1})">Previous</a>
    </li>`;
    
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentReturnsPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    if (startPage > 1) {
        html += '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(1)">1</a></li>';
        if (startPage > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentReturnsPage;
        html += `<li class="page-item ${isActive ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(${i})">${i}</a>
        </li>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(${totalPages})">${totalPages}</a></li>`;
    }
    
    html += `<li class="page-item ${currentReturnsPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(${currentReturnsPage + 1})">Next</a>
    </li>`;
    
    paginationContainer.html(html);
}

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
    
    const userRole = "<?= session('role') ?>";
    const userClassId = "<?= session('class_id') ?>";
    
    let isFormSubmitting = false;
    let showClassSelectionToast = true;
    
    const classDataCache = {};
    const CACHE_DURATION = 5 * 60 * 1000;
    
    const bookSearchCache = {};
    const BOOK_SEARCH_CACHE_DURATION = 10 * 60 * 1000;

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

    function loadClassData(classId) {
        currentClassId = classId;
        
        const now = Date.now();
        const cachedData = classDataCache[classId];
        
        if (cachedData && (now - cachedData.timestamp) < CACHE_DURATION) {
            processClassData(cachedData.data);
            return;
        }
        
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
        
        $('#guruClassName').text(currentClassName);
        $('#guruStudentCount').text(classStudents.length);
        $('#guruBookCount').text(classBooks.length);
        
        peminjamanBtn.disabled = false;
        pengembalianBtn.disabled = false;
        
        setupStudentAutocomplete();
        setupBookAutocomplete();
        loadTransactions('borrow');
        loadTransactions('return');
        
        if (showClassSelectionToast) {
            showToast('Kelas ' + currentClassName + ' berhasil dipilih');
        }
        showClassSelectionToast = true;
    }

    function setupStudentAutocomplete() {
        $('#namaCari').autocomplete({
            source: function(request, response) {
                const results = classStudents
                    .map(s => ({
                        label: s.nama,
                        value: s.nama,
                        id: s.id
                    }))
                    .filter(item => item.label && item.label.toLowerCase().includes(request.term.toLowerCase()));
                response(results);
            },
            minLength: 1,
            select: function(event, ui) {
                $('#selectedUserId').val(ui.item.id);
                $('#namaCari').val(ui.item.value);
                setTimeout(() => $('#judulCari').focus(), 100);
                return false;
            }
        });
        
        $('#namaCari').on('input', function() {
            if (!$(this).val().trim()) {
                $('#selectedUserId').val('');
            }
        });
    }

    function searchBooksServer(searchTerm, callback) {
        const now = Date.now();
        const cacheKey = 'search_' + searchTerm.toLowerCase();
        
        if (bookSearchCache[cacheKey] && (now - bookSearchCache[cacheKey].timestamp) < BOOK_SEARCH_CACHE_DURATION) {
            callback(bookSearchCache[cacheKey].data);
            return;
        }
        
        $.get("<?= base_url('books/search-autocomplete') ?>", {
            search: searchTerm,
            limit: 50
        }, function(response) {
            if (response.success && Array.isArray(response.books)) {
                const books = response.books;
                
                bookSearchCache[cacheKey] = {
                    data: books,
                    timestamp: now
                };
                
                callback(books);
            } else {
                console.error('Invalid search response');
                callback([]);
            }
        }).fail(function(err) {
            console.error('Failed to search books:', err);
            callback([]);
        });
    }

    function setupBookAutocomplete() {
        window.allBooksSearchCache = {};
        
        $('#judulCari').autocomplete({
            source: function(request, response) {
                if (request.term.length < 1) {
                    response([]);
                    return;
                }
                
                searchBooksServer(request.term, function(books) {
                    window.allBooksSearchCache = {};
                    const results = books.map(b => ({
                        label: b.title,
                        value: b.title,
                        id: b.id,
                        uid: b.uid
                    }));
                    
                    books.forEach(b => {
                        window.allBooksSearchCache[b.title] = b;
                    });
                    
                    response(results);
                });
            },
            minLength: 1,
            select: function(event, ui) {
                $('#selectedBookId').val(ui.item.id);
                $('#judulCari').val(ui.item.value);

                const selectedBook = window.allBooksSearchCache[ui.item.value];
                if (selectedBook && selectedBook.uid && (
                    (Array.isArray(selectedBook.uid) && selectedBook.uid.length > 0) ||
                    (!Array.isArray(selectedBook.uid) && String(selectedBook.uid).trim() !== '')
                )) {
                    $('#uidInputSection').show();
                } else {
                    $('#uidInputSection').hide();
                }

                return false;
            }
        });

        $('#judulCari').on('input', function() {
            if (!$(this).val().trim()) {
                $('#selectedBookId').val('');
                $('#uidInputSection').hide();
            }
        });
    }

    function loadPengembalianChecklist() {
        const activeLoans = activeBorrowings || [];

        if (activeLoans.length === 0) {
            $('#checklistPengembalian').html('<p class="text-muted text-center">Tidak ada peminjaman aktif di kelas ini.</p>');
            return;
        }

        const groupedByStudent = {};
        activeLoans.forEach(loan => {
            const studentId = loan.user_id;
            if (!studentId) {
                return;
            }
            
            if (!groupedByStudent[studentId]) {
                const student = classStudents.find(s => s.id === studentId) || {};
                groupedByStudent[studentId] = {
                    student: student,
                    loans: []
                };
            }
            groupedByStudent[studentId].loans.push(loan);
        });

        let checklistHtml = '';
        const studentIds = Object.keys(groupedByStudent);
        
        if (studentIds.length === 0) {
            $('#checklistPengembalian').html('<p class="text-muted text-center">Tidak ada peminjaman aktif atau data siswa tidak cocok.</p>');
            return;
        }
        
        studentIds.forEach(studentId => {
            const { student, loans } = groupedByStudent[studentId];
            const studentName = escapeHtml(student.nama || 'Unknown Student');
            
            checklistHtml += `
            <div class="siswa-group mb-3 p-3" style="background-color: #f8f9fa; border-radius: 4px; border-left: 4px solid #007bff;">
                <div style="font-weight: 600; color: #333; margin-bottom: 10px;">${studentName}</div>
                <div class="siswa-books">
            `;
            
            loans.forEach(loan => {
                const loanId = loan.id || '';
                const bookTitle = escapeHtml(loan.book_title || loan.judul || 'Unknown Book');
                const borrowDate = escapeHtml(loan.tanggal || '-');
                
                checklistHtml += `
                    <div class="form-check mb-2">
                        <input class="form-check-input return-checkbox" type="checkbox" id="return_${loanId}" 
                               data-loan-id="${loanId}" data-user-id="${studentId}">
                        <label class="form-check-label" for="return_${loanId}" style="cursor: pointer;">
                            <span style="color: #666; font-size: 14px;">${bookTitle}</span>
                            <span style="color: #999; font-size: 12px; margin-left: 5px;">(${borrowDate})</span>
                        </label>
                    </div>
                `;
            });
            
            checklistHtml += `
                </div>
            </div>
            `;
        });

        $('#checklistPengembalian').html(checklistHtml);
        attachReturnChecklistListeners();
    }

    function attachReturnChecklistListeners() {
        $('#searchSiswaReturn').off('input').on('input', function() {
            const query = $(this).val().toLowerCase();
            const groups = $('#checklistPengembalian .siswa-group');
            
            groups.each(function() {
                const siswaName = $(this).find('> div:first').text().toLowerCase();
                if (siswaName.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

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
                } else if (type === 'return') {
                    renderReturnsTable(response.transactions);
                }
            }
        });
    }

    function renderBorrowingsTable(transactions) {
        borrowingsData = transactions || [];
        currentBorrowingsPage = 1;
        refreshBorrowingsTable(1);
    }

    function renderReturnsTable(transactions) {
        returnsData = transactions || [];
        currentReturnsPage = 1;
        refreshReturnsTable(1);
    }

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
        
        $('#namaCari, #judulCari').attr('required', 'required');
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
        
        $('#namaCari, #judulCari').removeAttr('required');
        
        $.get("<?= base_url('peminjaman-kelas/transactions') ?>", {
            class_id: currentClassId,
            type: 'borrow'
        }, function(response) {
            if (response.success) {
                activeBorrowings = response.transactions.filter(t => t.status === 'active');
                loadPengembalianChecklist();
            } else {
                console.error('Failed to fetch transactions:', response);
                showToast('Gagal memuat data peminjaman', 'error');
            }
        }).fail(function(error) {
            console.error('AJAX error:', error);
            showToast('Error: ' + error.statusText, 'error');
        });
    });

    transactionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isFormSubmitting) {
            return;
        }
        
        const formType = this.dataset.type;
        
        if (formType === 'borrow') {
            $('#namaCari, #judulCari').attr('required', 'required');
            handlePeminjamanAdd();
        } else {
            $('#namaCari, #judulCari').removeAttr('required');
            handlePengembalianAdd();
        }
    });

    function handlePeminjamanAdd() {
        const formData = new FormData(transactionForm);
        
        const studentName = formData.get('namaCari').trim();
        const bookTitle = formData.get('judulCari').trim();
        const userId = formData.get('user_id').trim();
        const bookId = formData.get('book_id').trim();
        
        if (!studentName || !bookTitle) {
            showToast('Nama Siswa dan Judul Buku harus diisi!', 'error');
            return;
        }
        
        if (!userId || !bookId) {
            showToast('Silakan pilih Siswa dan Buku dari daftar yang muncul', 'error');
            return;
        }
        
        isFormSubmitting = true;
        
        $.ajax({
            url: "<?= base_url('peminjaman-kelas/add') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                isFormSubmitting = false;
                
                if (response.success) {
                    $(modalElement).modal('hide');
                    showToast(response.message || 'Peminjaman berhasil ditambahkan!');
                    
                    delete classDataCache[currentClassId];
                    
                    showClassSelectionToast = false;
                    loadTransactions('borrow');
                    loadTransactions('return');
                    loadClassData(currentClassId);
                    
                    transactionForm.reset();
                    $('#selectedUserId').val('');
                    $('#selectedBookId').val('');
                    $('#uidInputSection').hide();
                } else {
                    showToast(response.message || 'Gagal menambahkan peminjaman', 'error');
                }
            },
            error: function(xhr) {
                isFormSubmitting = false;
                console.error('AJAX Error:', xhr.responseText);
                showToast('Terjadi kesalahan saat menambah peminjaman', 'error');
            }
        });
    }

    function handlePengembalianAdd() {
        const selectedLoans = [];
        $('#checklistPengembalian .return-checkbox:checked').each(function() {
            const loanId = $(this).data('loan-id');
            const userId = $(this).data('user-id');
            
            selectedLoans.push({
                loanId: loanId,
                userId: userId
            });
        });

        if (selectedLoans.length === 0) {
            showToast('Pilih minimal satu buku untuk dikembalikan!', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('class_id', currentClassId);
        formData.append('selectedLoans', JSON.stringify(selectedLoans));

        isFormSubmitting = true;
        
        $.ajax({
            url: "<?= base_url('peminjaman-kelas/return-multiple') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                isFormSubmitting = false;

                if (response.success) {
                    $(modalElement).modal('hide');
                    showToast(response.message || 'Pengembalian berhasil ditambahkan!');
                    
                    delete classDataCache[currentClassId];
                    
                    showClassSelectionToast = false;
                    loadTransactions('borrow');
                    loadTransactions('return');
                    loadClassData(currentClassId);
                    loadPengembalianChecklist();
                    
                    transactionForm.reset();
                    $('#searchSiswaReturn').val('');
                } else {
                    const errorMsg = response.message || 'Gagal menambahkan pengembalian';
                    showToast(errorMsg, 'error');
                }
            },
            error: function(xhr) {
                isFormSubmitting = false;
                console.error('AJAX Error Status:', xhr.status);
                console.error('AJAX Error Text:', xhr.responseText);
                
                let errorMsg = 'Terjadi kesalahan saat menambah pengembalian';
                if (xhr.status === 0) {
                    errorMsg = 'Koneksi terputus. Periksa jaringan Anda.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Hubungi administrator.';
                }
                
                showToast(errorMsg, 'error');
            }
        });
    }

    $(modalElement).on('hidden.bs.modal', function() {
        transactionForm.reset();
        $('#selectedUserId').val('');
        $('#selectedBookId').val('');
        $('#uidInputSection').hide();
        $('#searchSiswaReturn').val('');
        isFormSubmitting = false;
    });

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
    
    if (userRole === 'guru' && userClassId) {
        const guruClass = allClasses.find(c => c.id == userClassId);
        if (guruClass) {
            showClassSelectionToast = false;
            loadClassData(userClassId);
        } else {
            showToast('Kelas yang Anda ajar tidak ditemukan', 'error');
        }
    }
});
</script>

<?= $this->endSection() ?>