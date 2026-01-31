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
                            <small>Siswa: <span id="studentCount">0</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GURU CLASS INFO -->
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
                            <small class="text-muted">Pilih buku yang ingin dipinjam</small>
                        </div>
                    </div>
                </div>
                
                <!-- Tambah pengembalian -->
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
// Pagination variables (global scope)
const ITEMS_PER_PAGE = 25;
let borrowingsData = [];
let returnsData = [];
let currentBorrowingsPage = 1;
let currentReturnsPage = 1;

// Pagination functions (global scope)
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
            <td>${escapeHtml(t.nama)}</td>
            <td>${escapeHtml(t.judul)}</td>
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
    
    // Previous button
    html += `<li class="page-item ${currentBorrowingsPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshBorrowingsTable(${currentBorrowingsPage - 1})">Previous</a>
    </li>`;
    
    // Page numbers
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
    
    // Next button
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
            <td>${escapeHtml(t.nama)}</td>
            <td>${escapeHtml(t.judul)}</td>
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
    
    // Previous button
    html += `<li class="page-item ${currentReturnsPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(${currentReturnsPage - 1})">Previous</a>
    </li>`;
    
    // Page numbers
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
    
    // Next button
    html += `<li class="page-item ${currentReturnsPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="refreshReturnsTable(${currentReturnsPage + 1})">Next</a>
    </li>`;
    
    paginationContainer.html(html);
}

// Utility function (global scope)
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
    
    // Get current user info
    const userRole = "<?= session('role') ?>";
    const userClassId = "<?= session('class_id') ?>";
    
    let isFormSubmitting = false;
    let showClassSelectionToast = true; // Flag to control toast display
    
    // Cache for class data
    const classDataCache = {};
    const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes
    
    // Cache for book searches (local only)
    const bookSearchCache = {};
    const BOOK_SEARCH_CACHE_DURATION = 10 * 60 * 1000; // 10 minutes

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

    // Server-side book search function with client-side caching
    function searchBooksServer(searchTerm, callback) {
        const now = Date.now();
        const cacheKey = 'search_' + searchTerm.toLowerCase();
        
        // Check cache
        if (bookSearchCache[cacheKey] && (now - bookSearchCache[cacheKey].timestamp) < BOOK_SEARCH_CACHE_DURATION) {
            console.log('Using cached book search:', cacheKey);
            callback(bookSearchCache[cacheKey].data);
            return;
        }
        
        // Fetch from server
        $.get("<?= base_url('books/search-autocomplete') ?>", {
            search: searchTerm,
            limit: 50
        }, function(response) {
            if (response.success && Array.isArray(response.books)) {
                const books = response.books;
                
                // Cache the results
                bookSearchCache[cacheKey] = {
                    data: books,
                    timestamp: now
                };
                
                console.log('Books fetched from server:', books.length);
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

    // Setup autocomplete for books (SERVER-SIDE)
    function setupBookAutocomplete() {
        $('#judulCari').autocomplete({
            source: function(request, response) {
                if (request.term.length < 1) {
                    response([]);
                    return;
                }
                
                searchBooksServer(request.term, function(books) {
                    const titles = books.map(b => b.title).filter(Boolean);
                    response(titles);
                });
            },
            minLength: 1,
            select: function(event, ui) {
                $(this).val(ui.item.value);

                // Find the selected book from cache
                let selectedBook = null;
                for (const cacheKey in bookSearchCache) {
                    const books = bookSearchCache[cacheKey].data;
                    selectedBook = books.find(b => b.title === ui.item.value);
                    if (selectedBook) break;
                }

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
                $('#uidInputSection').hide();
            }
        });
    }

    // autocomplete - REMOVED for pengembalian checklist
    // Return checklist functions
    function loadPengembalianChecklist() {
        const activeLoans = activeBorrowings || [];
        
        console.log('Loading checklist with:', {
            totalBorrowings: activeBorrowings.length,
            activeLoans: activeLoans.length,
            classStudents: classStudents.length
        });

        if (activeLoans.length === 0) {
            $('#checklistPengembalian').html('<p class="text-muted text-center">Tidak ada peminjaman aktif.</p>');
            return;
        }

        // Group by student
        const groupedByStudent = {};
        activeLoans.forEach(loan => {
            const studentId = loan.user_id;
            if (!groupedByStudent[studentId]) {
                const student = classStudents.find(s => s.id === studentId) || {};
                groupedByStudent[studentId] = {
                    student: student,
                    loans: []
                };
            }
            groupedByStudent[studentId].loans.push(loan);
        });

        // Build checklist HTML
        let checklistHtml = '';
        Object.keys(groupedByStudent).forEach(studentId => {
            const { student, loans } = groupedByStudent[studentId];
            const studentName = escapeHtml(student.nama || 'Unknown');
            
            checklistHtml += `
            <div class="siswa-group mb-3 p-3" style="background-color: #f8f9fa; border-radius: 4px; border-left: 4px solid #007bff;">
                <div style="font-weight: 600; color: #333; margin-bottom: 10px;">${studentName}</div>
                <div class="siswa-books">
            `;
            
            loans.forEach(loan => {
                const bookTitle = escapeHtml(loan.judul || 'Unknown Book');
                const loanId = escapeHtml(loan.id || '');
                
                checklistHtml += `
                    <div class="form-check mb-2">
                        <input class="form-check-input return-checkbox" type="checkbox" id="return_${loanId}" 
                               data-loan-id="${loanId}" data-user-id="${studentId}">
                        <label class="form-check-label" for="return_${loanId}" style="cursor: pointer;">
                            <span style="color: #666; font-size: 14px;">${bookTitle}</span>
                            <span style="color: #999; font-size: 12px; margin-left: 5px;">(${escapeHtml(loan.tanggal || '-')})</span>
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
                } else if (type === 'return') {
                    renderReturnsTable(response.transactions);
                }
            }
        });
    }

    // Render borrowings table
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
        
        // Load checklist directly - no need to wait
        loadPengembalianChecklist();
    });

    // Form submit handler
    transactionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (isFormSubmitting) {
            return;
        }
        
        const formType = this.dataset.type;
        
        if (formType === 'borrow') {
            handlePeminjamanAdd();
        } else {
            handlePengembalianAdd();
        }
    });

    function handlePeminjamanAdd() {
        const formData = new FormData(transactionForm);
        
        if (!formData.get('namaCari').trim() || !formData.get('judulCari').trim()) {
            showToast('Nama Siswa dan Judul Buku harus diisi!', 'error');
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
            selectedLoans.push({
                loanId: $(this).data('loan-id'),
                userId: $(this).data('user-id')
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
                    
                    // Clear cache for this class
                    delete classDataCache[currentClassId];
                    
                    // Reload transactions and class data without showing class selection toast
                    showClassSelectionToast = false;
                    loadTransactions('borrow');
                    loadTransactions('return');
                    loadClassData(currentClassId);
                    loadPengembalianChecklist();
                    
                    // Reset form
                    transactionForm.reset();
                    $('#searchSiswaReturn').val('');
                } else {
                    showToast(response.message || 'Gagal menambahkan pengembalian', 'error');
                }
            },
            error: function(xhr) {
                isFormSubmitting = false;
                console.error('AJAX Error:', xhr.responseText);
                showToast('Terjadi kesalahan saat menambah pengembalian', 'error');
            }
        });
    }

    $(modalElement).on('hidden.bs.modal', function() {
        transactionForm.reset();
        $('#uidInputSection').hide();
        $('#searchSiswaReturn').val('');
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