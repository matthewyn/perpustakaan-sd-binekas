<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<style>
.page-item.active .page-link {
    background-color: #f4f4f4;
    border-color: #dee2e6;
    color: white;
}
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
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item"><a href="#">Form</a></li>
            <li class="breadcrumb-item active" aria-current="page">Peminjaman Otomatis</li>
        </ol>
    </nav>

    <!-- Form Scan -->
    <div class="card border-light shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Peminjaman / Pengembalian Otomatis</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> <strong>Cara Pakai:</strong><br>
                1. Scan kartu RFID <strong>user</strong> (siswa / guru)<br>
                2. Scan kartu RFID <strong>buku</strong><br>
                3. Sistem otomatis mendeteksi peminjaman atau pengembalian
            </div>

            <form id="formScan">
                <div class="mb-3">
                    <label for="user_uid" class="form-label required">
                        <i class="bi bi-person-badge"></i> UID Kartu User
                    </label>
                    <input type="text" id="user_uid" class="form-control"
                           placeholder="Tap kartu RFID user di sini"
                           autocomplete="off" required>
                    <small class="text-muted">Scan kartu RFID milik siswa atau guru</small>
                </div>

                <div class="mb-3">
                    <label for="uid" class="form-label required">
                        <i class="bi bi-upc-scan"></i> UID Kartu Buku
                    </label>
                    <input type="text" id="uid" class="form-control uid-input"
                           placeholder="Tap kartu RFID buku di sini"
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

    <!-- List Peminjaman -->
    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List peminjaman
            <i class="bi bi-chevron-down" id="chevronPeminjaman" type="button"
               data-bs-toggle="collapse" data-bs-target="#collapsePeminjaman"
               aria-expanded="false"></i>
        </div>
        <div class="card-body">
            <div class="collapse" id="collapsePeminjaman">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchPeminjaman" class="form-control" placeholder="Cari Nama/Buku...">
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
                            <th scope="col">Kelas</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyBorrowings">
                        <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
                <nav aria-label="Pagination untuk peminjaman">
                    <ul class="pagination" id="paginationBorrowings"></ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- List Pengembalian -->
    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List pengembalian
            <i class="bi bi-chevron-down" id="chevronPengembalian" type="button"
               data-bs-toggle="collapse" data-bs-target="#collapsePengembalian"
               aria-expanded="false"></i>
        </div>
        <div class="card-body">
            <div class="collapse" id="collapsePengembalian">
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchPengembalian" class="form-control" placeholder="Cari Nama/Buku...">
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
                            <th scope="col">Kelas</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyReturns">
                        <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
                <nav aria-label="Pagination untuk pengembalian">
                    <ul class="pagination" id="paginationReturns"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let usersByKey            = {};
    let currentBorrowingsPage = 1;
    let currentReturnsPage    = 1;
    let totalBorrowingsPages  = 1;
    let totalReturnsPages     = 1;
    const ITEMS_PER_PAGE      = 25;

    let borrowingsSearchQuery = '';
    let returnsSearchQuery    = '';
    let allBorrowingsCache    = null;
    let allReturnsCache       = null;

    function escapeHtml(text) {
        const map = { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    }

    function matchesQuery(item, query) {
        const user = usersByKey[item.user_id] || {};
        const nama = (user.nama || '').toLowerCase();
        const buku = (item.book_title || '').toLowerCase();
        return nama.includes(query) || buku.includes(query);
    }

    document.getElementById('collapsePeminjaman').addEventListener('show.bs.collapse', function () {
        document.getElementById('chevronPeminjaman').classList.replace('bi-chevron-down', 'bi-chevron-up');
    });
    document.getElementById('collapsePeminjaman').addEventListener('hide.bs.collapse', function () {
        document.getElementById('chevronPeminjaman').classList.replace('bi-chevron-up', 'bi-chevron-down');
    });
    document.getElementById('collapsePengembalian').addEventListener('show.bs.collapse', function () {
        document.getElementById('chevronPengembalian').classList.replace('bi-chevron-down', 'bi-chevron-up');
    });
    document.getElementById('collapsePengembalian').addEventListener('hide.bs.collapse', function () {
        document.getElementById('chevronPengembalian').classList.replace('bi-chevron-up', 'bi-chevron-down');
    });

    function fetchClassesData() {
        $.get("<?= base_url('classes/list') ?>", function (response) {
            if (response.success && Array.isArray(response.classes)) {
                window._classesById = {};
                response.classes.forEach(c => { window._classesById[c.id] = c; });
            }
        }).fail(() => { window._classesById = {}; });
    }

    function fetchUsersData() {
        $.get("<?= base_url('user/list/murid') ?>", function (response) {
            if (response.success && Array.isArray(response.users)) {
                response.users.forEach(u => { usersByKey[u.id] = u; });
            }
        });
        $.get("<?= base_url('user/list/guru') ?>", function (response) {
            if (response.success && Array.isArray(response.users)) {
                response.users.forEach(u => { usersByKey[u.id] = u; });
            }
        });
    }

    fetchClassesData();
    fetchUsersData();

    function renderBorrowingsRows(transactions, page) {
        let rows = '';
        let no = (page - 1) * ITEMS_PER_PAGE + 1;
        transactions.forEach(b => {
            const user        = usersByKey[b.user_id] || {};
            const bookTitle   = b.book_title || '-';
            const classId     = user.class_id || null;
            const className   = classId && window._classesById && window._classesById[classId]
                                ? window._classesById[classId].nama_kelas : '-';
            const statusClass = b.status === 'active' ? 'table-danger' : '';
            rows += `<tr class="${statusClass}">
                <th scope="row">${no++}</th>
                <td>${escapeHtml(user.nama || '-')}</td>
                <td>${escapeHtml(bookTitle)}</td>
                <td>${escapeHtml(className)}</td>
                <td>${escapeHtml(b.tanggal || '-')}</td>
            </tr>`;
        });
        return rows;
    }

    function refreshBorrowingsTable(page = 1) {
        currentBorrowingsPage = page;

        // Mode pencarian: filter & paginasi seluruh data di client
        if (borrowingsSearchQuery) {
            if (allBorrowingsCache === null) {
                $.get("<?= base_url('transaction/borrowings-all') ?>", { all: 1 }, function (response) {
                    if (response.success && Array.isArray(response.borrowings)) {
                        allBorrowingsCache = response.borrowings;
                        renderBorrowingsFromCache(page);
                    }
                });
            } else {
                renderBorrowingsFromCache(page);
            }
            return;
        }

        // Mode normal: server-side pagination (hemat memori)
        $.get("<?= base_url('transaction/borrowings-all') ?>", { page, limit: ITEMS_PER_PAGE }, function (response) {
            if (response.success && Array.isArray(response.borrowings)) {
                let rows = renderBorrowingsRows(response.borrowings, page);
                if (!rows) rows = `<tr><td colspan="5" class="text-center">Belum ada data peminjaman.</td></tr>`;
                $('#tbodyBorrowings').html(rows);
                totalBorrowingsPages = Math.ceil(response.totalCount / ITEMS_PER_PAGE);
                renderBorrowingsPagination();
            }
        });
    }

    function renderBorrowingsFromCache(page) {
        const filtered = allBorrowingsCache.filter(b => matchesQuery(b, borrowingsSearchQuery));

        totalBorrowingsPages = Math.max(1, Math.ceil(filtered.length / ITEMS_PER_PAGE));
        if (page > totalBorrowingsPages) page = totalBorrowingsPages;
        currentBorrowingsPage = page;

        const start = (page - 1) * ITEMS_PER_PAGE;
        const pageData = filtered.slice(start, start + ITEMS_PER_PAGE);

        let rows = renderBorrowingsRows(pageData, page);
        if (!rows) rows = `<tr><td colspan="5" class="text-center">Tidak ada hasil pencarian.</td></tr>`;
        $('#tbodyBorrowings').html(rows);
        renderBorrowingsPagination();
    }

    function renderReturnsRows(transactions, page) {
        let rows = '';
        let no = (page - 1) * ITEMS_PER_PAGE + 1;
        transactions.forEach(r => {
            const user      = usersByKey[r.user_id] || {};
            const bookTitle = r.book_title || '-';
            const classId   = user.class_id || null;
            const className = classId && window._classesById && window._classesById[classId]
                              ? window._classesById[classId].nama_kelas : '-';
            rows += `<tr>
                <th scope="row">${no++}</th>
                <td>${escapeHtml(user.nama || '-')}</td>
                <td>${escapeHtml(bookTitle)}</td>
                <td>${escapeHtml(className)}</td>
                <td>${escapeHtml(r.tanggal || '-')}</td>
            </tr>`;
        });
        return rows;
    }

    function refreshReturnsTable(page = 1) {
        currentReturnsPage = page;

        if (returnsSearchQuery) {
            if (allReturnsCache === null) {
                $.get("<?= base_url('transaction/returns-all') ?>", { all: 1 }, function (response) {
                    if (response.success && Array.isArray(response.returns)) {
                        allReturnsCache = response.returns;
                        renderReturnsFromCache(page);
                    }
                });
            } else {
                renderReturnsFromCache(page);
            }
            return;
        }

        $.get("<?= base_url('transaction/returns-all') ?>", { page, limit: ITEMS_PER_PAGE }, function (response) {
            if (response.success && Array.isArray(response.returns)) {
                let rows = renderReturnsRows(response.returns, page);
                if (!rows) rows = `<tr><td colspan="5" class="text-center">Belum ada data pengembalian.</td></tr>`;
                $('#tbodyReturns').html(rows);
                totalReturnsPages = Math.ceil(response.totalCount / ITEMS_PER_PAGE);
                renderReturnsPagination();
            }
        });
    }

    function renderReturnsFromCache(page) {
        const filtered = allReturnsCache.filter(r => matchesQuery(r, returnsSearchQuery));

        totalReturnsPages = Math.max(1, Math.ceil(filtered.length / ITEMS_PER_PAGE));
        if (page > totalReturnsPages) page = totalReturnsPages;
        currentReturnsPage = page;

        const start = (page - 1) * ITEMS_PER_PAGE;
        const pageData = filtered.slice(start, start + ITEMS_PER_PAGE);

        let rows = renderReturnsRows(pageData, page);
        if (!rows) rows = `<tr><td colspan="5" class="text-center">Tidak ada hasil pencarian.</td></tr>`;
        $('#tbodyReturns').html(rows);
        renderReturnsPagination();
    }

    // ── Pagination ────────────────────────────────────────────────────────────
    function renderBorrowingsPagination() {
        $('#paginationBorrowings').html(generatePaginationHTML(currentBorrowingsPage, totalBorrowingsPages, 'borrowings'));
        attachPaginationListeners('borrowings');
    }

    function renderReturnsPagination() {
        $('#paginationReturns').html(generatePaginationHTML(currentReturnsPage, totalReturnsPages, 'returns'));
        attachPaginationListeners('returns');
    }

    function generatePaginationHTML(currentPage, totalPages, type) {
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage   = Math.min(totalPages, startPage + maxPagesToShow - 1);
        if (endPage - startPage < maxPagesToShow - 1) startPage = Math.max(1, endPage - maxPagesToShow + 1);

        let html = '';

        html += currentPage > 1
            ? `<li class="page-item"><a href="#" class="page-link text-secondary pagination-prev" data-type="${type}" data-page="${currentPage - 1}">Previous</a></li>`
            : `<li class="page-item disabled"><a class="page-link text-secondary">Previous</a></li>`;

        if (startPage > 1) {
            html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-page" data-type="${type}" data-page="1">1</a></li>`;
            if (startPage > 2) html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
        }
        for (let i = startPage; i <= endPage; i++) {
            html += i === currentPage
                ? `<li class="page-item active"><a class="page-link text-secondary" href="#" aria-current="page">${i}</a></li>`
                : `<li class="page-item"><a href="#" class="page-link text-secondary pagination-page" data-type="${type}" data-page="${i}">${i}</a></li>`;
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
            html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-page" data-type="${type}" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        html += currentPage < totalPages
            ? `<li class="page-item"><a href="#" class="page-link text-secondary pagination-next" data-type="${type}" data-page="${currentPage + 1}">Next</a></li>`
            : `<li class="page-item disabled"><a class="page-link text-secondary">Next</a></li>`;

        return html;
    }

    function attachPaginationListeners(type) {
        const cap = type.charAt(0).toUpperCase() + type.slice(1);
        $(`#pagination${cap} .pagination-page, #pagination${cap} .pagination-prev, #pagination${cap} .pagination-next`)
            .on('click', function (e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                if (type === 'borrowings') refreshBorrowingsTable(page);
                else if (type === 'returns') refreshReturnsTable(page);
            });
    }

    function filterBorrowingsTable(searchValue) {
        borrowingsSearchQuery = searchValue.toLowerCase().trim();
        refreshBorrowingsTable(1);
    }

    function filterReturnsTable(searchValue) {
        returnsSearchQuery = searchValue.toLowerCase().trim();
        refreshReturnsTable(1);
    }

    document.getElementById('searchPeminjaman').addEventListener('input', function () {
        filterBorrowingsTable(this.value);
    });
    document.getElementById('cariPeminjaman').addEventListener('click', function () {
        filterBorrowingsTable(document.getElementById('searchPeminjaman').value);
    });
    document.getElementById('searchPengembalian').addEventListener('input', function () {
        filterReturnsTable(this.value);
    });
    document.getElementById('cariPengembalian').addEventListener('click', function () {
        filterReturnsTable(document.getElementById('searchPengembalian').value);
    });


    refreshBorrowingsTable(1);
    refreshReturnsTable(1);

    const formScan     = document.getElementById('formScan');
    const uidInput     = document.getElementById('uid');
    const userUidInput = document.getElementById('user_uid');
    const btnScan      = document.getElementById('btnScan');
    const btnReset     = document.getElementById('btnReset');

    userUidInput.addEventListener('blur', function () {
        if (this.value.trim()) uidInput.focus();
    });

    btnReset.addEventListener('click', function () {
        formScan.reset();
        document.getElementById('result').innerHTML = '';
        userUidInput.focus();
    });

    formScan.addEventListener('submit', function (e) {
        e.preventDefault();

        const uid     = uidInput.value.trim();
        const userUid = userUidInput.value.trim();

        if (!uid || !userUid) {
            showResult('error', 'UID buku dan UID user wajib diisi!');
            return;
        }

        btnScan.disabled = true;
        btnScan.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

        fetch('<?= base_url("automate/process") ?>', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    `uid=${encodeURIComponent(uid)}&user_uid=${encodeURIComponent(userUid)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const type          = data.type === 'return' ? 'Pengembalian' : 'Peminjaman';
                const trustInfo     = data.trust_score ? `<br><i class="bi bi-award"></i> Trust Score: ${data.trust_score}` : '';
                const maxBorrowInfo = data.max_borrow  ? ` (Max: ${data.max_borrow} buku)` : '';
                const dueDateInfo   = data.due_date    ? `<br><i class="bi bi-calendar"></i> Jatuh Tempo: ${data.due_date}` : '';

                showResult('success', `
                    <strong>${type} Berhasil!</strong><br>
                    <i class="bi bi-person"></i> User: ${data.user || '-'}${trustInfo}${maxBorrowInfo}<br>
                    <i class="bi bi-book"></i> Buku: ${data.book || '-'}${dueDateInfo}
                `);

                allBorrowingsCache = null;
                allReturnsCache = null;

                // Refresh kedua tabel agar langsung update
                refreshBorrowingsTable(currentBorrowingsPage);
                refreshReturnsTable(currentReturnsPage);

                setTimeout(() => { uidInput.value = ''; uidInput.focus(); }, 1500);
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

    function showResult(type, message) {
        const alertClass = type === 'success' ? 'result-success' : 'result-error';
        const icon       = type === 'success' ? 'bi-check-circle' : 'bi-x-circle';
        document.getElementById('result').innerHTML = `
            <div class="${alertClass}"><i class="bi ${icon}"></i> ${message}</div>`;
        setTimeout(() => { document.getElementById('result').innerHTML = ''; }, 5000);
    }

    // Focus saat load
    userUidInput.focus();
});
</script>

<?= $this->endSection() ?>