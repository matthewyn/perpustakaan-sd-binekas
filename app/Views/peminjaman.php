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
</style>
<div class="container mt-4">
    <!-- Main -->
    <nav aria-label="breadcrumb">
    <ol class="breadcrumb mt-3">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
        <li class="breadcrumb-item"><a href="#">Form</a></li>
        <li class="breadcrumb-item"><a href="#">Peminjaman Manual</a></li>
        <li class="breadcrumb-item active" aria-current="page">Peminjaman Perpustakaan</li>
    </ol>
    </nav>
    <div class="row g-3">
        <div class="col">
            <div class="card border-light">
                <div class="card-header">
                    Buku yang Dipinjam
                </div>
                <div class="card-body">
                    <h2><?= esc($totalBorrowed) ?></h2>
                    <div class="d-flex align-items-center">
                        <span>Buku baru yang dipinjam</span>
                        <span class="ms-auto <?= ($totalBorrowedPercent < 0 ? 'text-danger' : 'text-success') ?>">
                            <?= ($totalBorrowedPercent >= 0 ? '+' : '') . esc($totalBorrowedPercent) ?>%
                            <i class="bi <?= ($totalBorrowedPercent < 0 ? 'bi-caret-down-fill text-danger' : 'bi-caret-up-fill text-success') ?>"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-light">
                <div class="card-header">
                    Buku yang Dikembalikan
                </div>
                <div class="card-body">
                    <h2><?= esc($totalReturned) ?></h2>
                    <div class="d-flex align-items-center">
                        <span>Buku baru yang dikembalikan</span>
                        <span class="ms-auto <?= ($totalReturnedPercent < 0 ? 'text-danger' : 'text-success') ?>">
                            <?= ($totalReturnedPercent >= 0 ? '+' : '') . esc($totalReturnedPercent) ?>%
                            <i class="bi <?= ($totalReturnedPercent < 0 ? 'bi-caret-down-fill text-danger' : 'bi-caret-up-fill text-success') ?>"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-light">
                <div class="card-header">
                    Total Buku
                </div>
                <div class="card-body">
                    <h2><?= esc($totalAvailable) ?></h2>
                    <div class="d-flex align-items-center">
                        <span>Total buku yang bertambah</span>
                        <span class="ms-auto text-success">
                            <?= ($totalAvailablePercent >= 0 ? '+' : '') . esc($totalAvailablePercent) ?>%
                            <i class="bi bi-caret-up-fill text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            Statistik Peminjaman Buku
            <div class="float-end">
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm">Hari</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm">Bulan</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm">Tahun</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="flot-chart">
                                <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <ul class="stat-list">
                                <li>
                                    <h2 class="fs-4 m-0" id="stat-borrowed"><?= esc($chartData['harian']['borrowings'][date('Y-m-d')] ?? 0) ?></h2>
                                    <small>Buku yang dipinjam</small>
                                    <div class="stat-percent text-success" id="stat-borrowed-percent">0% <i class="bi bi-caret-up-fill"></i></div>
                                    <div class="progress progress-mini">
                                        <div id="stat-borrowed-bar" style="width: 0%;" class="progress-bar bg-success"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="fs-4 m-0" id="stat-returned"><?= esc($chartData['harian']['returns'][date('Y-m-d')] ?? 0) ?></h2>
                                    <small>Buku yang dikembalikan</small>
                                    <div class="stat-percent text-success" id="stat-returned-percent">0% <i class="bi bi-caret-up-fill"></i></div>
                                    <div class="progress progress-mini">
                                        <div id="stat-returned-bar" style="width: 0%;" class="progress-bar bg-success"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="fs-4 m-0" id="stat-total"><?= esc($totalAvailable) ?></h2>
                                    <small>Total buku</small>
                                    <div class="stat-percent text-success" id="stat-total-percent">0% <i class="bi bi-caret-up-fill"></i></div>
                                    <div class="progress progress-mini">
                                        <div id="stat-total-bar" style="width: 0%;" class="progress-bar bg-success"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-info" id="exportStats">
            <i class="bi bi-download"></i> Export Statistik
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="peminjaman">
            <i class="bi bi-plus-circle"></i> Tambah Peminjaman
        </button>
    </div>

    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List peminjaman
            <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePeminjamanExample" aria-expanded="false" aria-controls="collapsePeminjamanExample">
            </i>
        </div>
        <div class="card-body">
            <div class="collapse" id="collapsePeminjamanExample">
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
                        <?php if (!empty($borrowings)): ?>
                            <?php $no = 1; foreach ($borrowings as $row): ?>
                                <?php $statusClass = (isset($row['status']) && $row['status'] === 'active') ? 'table-danger' : ''; ?>
                                <tr class="<?= $statusClass ?>">
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= esc($row['nama']) ?></td>
                                    <td><?= esc($row['judul']) ?></td>
                                    <td><?= esc($row['class']) ?></td>
                                    <td><?= esc($row['tanggal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data peminjaman.</td>
                            </tr>
                        <?php endif; ?>
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
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="pengembalian">
            <i class="bi bi-arrow-left-circle"></i> Tambah Pengembalian
        </button>
    </div>

    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List pengembalian
            <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePengembalianExample" aria-expanded="false" aria-controls="collapsePengembalianExample">
            </i>
        </div>
        <div class="card-body">
            <div class="collapse" id="collapsePengembalianExample">
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
                        <?php if (!empty($returns)): ?>
                            <?php $no = 1; foreach ($returns as $row): ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= esc($row['nama']) ?></td>
                                    <td><?= esc($row['judul']) ?></td>
                                    <td><?= esc($row['class']) ?></td>
                                    <td><?= esc($row['tanggal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data pengembalian.</td>
                            </tr>
                        <?php endif; ?>
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
        <form>
            <div class="modal-body">
                <!-- Tambah peminjaman -->
                <div id="peminjamanSection">
                    <div class="row mb-3">
                        <div class="col siswa-select">
                            <label for="namaCari" class="form-label required">Cari Nama Siswa</label>
                            <input type="text" class="form-control" id="namaCari" name="namaCari" placeholder="Ketik nama" required>
                        </div>
                        <div class="col">
                            <label for="judulCari" class="form-label required">Cari Judul Buku</label>
                            <input type="text" class="form-control" id="judulCari" name="judulCari" placeholder="Ketik judul" required>
                        </div>
                    </div>
                    <div class="mb-3" id="uidInputSection" style="display: none;">
                        <label for="uidCari" class="form-label required">UID</label>
                        <input type="text" class="form-control" id="uidCari" name="uidCari" placeholder="Ketik/tap UID">
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
                <button type="button" class="btn btn-primary" id="submitForm">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var collapse = document.getElementById('collapsePeminjamanExample');
    var chevronIcon = document.querySelector('[data-bs-target="#collapsePeminjamanExample"]');
    const modal = document.getElementById('exampleModal');
    const modalTitle = modal.querySelector('.modal-title');
    const peminjamanBtn = document.getElementById('peminjaman');
    const pengembalianBtn = document.getElementById('pengembalian');
    const peminjamanSection = document.getElementById('peminjamanSection');
    const pengembalianSection = document.getElementById('pengembalianSection');
    let siswaList = [];
    let guruList = [];
    let bookTitles = [];
    let borrowingsList = [];
    let returnsList = [];
    let usersByKey = {};
    let dataReady = { siswa: false, borrowings: false, books: false, guru: false };
    let fetchTimeout;
    const DATA_LOAD_TIMEOUT = 10000;

    // Pagination state variables
    let currentBorrowingsPage = 1;
    let currentReturnsPage = 1;
    let totalBorrowingsPages = 1;
    let totalReturnsPages = 1;
    const ITEMS_PER_PAGE = 10;

    // Fetch data awal
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

    function startDataLoadTimeout() {
        fetchTimeout = setTimeout(() => {
            const failed = Object.keys(dataReady).filter(key => !dataReady[key]);
            if (failed.length > 0) {
                console.warn('Data loading timeout:', failed);
                showToast(`Gagal memuat beberapa data: ${failed.join(', ')}`);
            }
        }, DATA_LOAD_TIMEOUT);
    }

    function checkAllDataReady() {
        if (dataReady.siswa && dataReady.borrowings && dataReady.books) {
            clearTimeout(fetchTimeout);
            enablePengembalianAutocomplete();
            // Initialize tables with pagination on first load
            refreshBorrowingsTable(1);
            refreshReturnsTable(1);
        }
    }

    // fungsi colapse
    collapse.addEventListener('show.bs.collapse', function () {
        chevronIcon.classList.remove('bi-chevron-down');
        chevronIcon.classList.add('bi-chevron-up');
    });
    collapse.addEventListener('hide.bs.collapse', function () {
        chevronIcon.classList.remove('bi-chevron-up');
        chevronIcon.classList.add('bi-chevron-down');
    });

    // ===== CHART DATA =====
    const chartData = <?= json_encode($chartData) ?>;

    function toTimestamp(dateStr, type) {
        if (type === 'harian') {
            const d = new Date(dateStr);
            return d.getTime();
        } else if (type === 'bulanan') {
            const [y, m] = dateStr.split('-');
            return new Date(y, m - 1, 1).getTime();
        } else if (type === 'tahunan') {
            return new Date(dateStr, 0, 1).getTime();
        }
        return 0;
    }

    function getFlotData(type) {
        const borrowings = chartData[type]['borrowings'] || {};
        const returns = chartData[type]['returns'] || {};
        const borrowKeys = Object.keys(borrowings).sort();
        const returnKeys = Object.keys(returns).sort();
        const dataBorrow = borrowKeys.map(date => [toTimestamp(date, type), borrowings[date]]);
        const dataReturn = returnKeys.map(date => [toTimestamp(date, type), returns[date]]);
        return { borrow: dataBorrow, return: dataReturn };
    }

    var currentType = 'harian';

    function renderFlotChart(type) {
        const flotData = getFlotData(type);
        var dataset = [
            { label: "Pinjam", data: flotData.borrow, color: "#1ab394", bars: { show: true, align: "center", barWidth: 24*60*60*600, lineWidth:0 } },
            { label: "Kembali", data: flotData.return, yaxis: 2, color: "#1C84C6", lines: { lineWidth:1, show: true, fill: true } }
        ];
        var options = {
            xaxis: { mode: "time", tickSize: [1, "day"], tickLength: 0 },
            yaxes: [{ position: "left" }, { position: "right" }],
            legend: { noColumns: 1, position: "nw" },
            grid: { hoverable: false, borderWidth: 0 }
        };
        $.plot($("#flot-dashboard-chart"), dataset, options);
    }

    renderFlotChart(currentType);

    function updateStatList(type) {
        let borrowings = chartData[type]['borrowings'] || {};
        let returns = chartData[type]['returns'] || {};
        let nowKey;
        if (type === 'harian') nowKey = new Date().toISOString().slice(0,10);
        else if (type === 'bulanan') { const d = new Date(); nowKey = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; }
        else nowKey = String(new Date().getFullYear());
        const borrowedNow = borrowings[nowKey] || 0;
        const returnedNow = returns[nowKey] || 0;
        
        function calcPercent(now, prev) {
            if (prev === 0) return now > 0 ? 100 : 0;
            return Math.round(((now - prev)/prev)*100);
        }
        
        let prevKey;
        if (type === 'harian') { const d = new Date(nowKey); d.setDate(d.getDate()-1); prevKey = d.toISOString().slice(0,10); }
        else if (type === 'bulanan') { const d = new Date(nowKey + '-01'); d.setMonth(d.getMonth()-1); prevKey = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; }
        else prevKey = String(Number(nowKey)-1);
        
        const borrowedPrev = borrowings[prevKey] || 0;
        const returnedPrev = returns[prevKey] || 0;
        const borrowedPercent = calcPercent(borrowedNow, borrowedPrev);
        const returnedPercent = calcPercent(returnedNow, returnedPrev);
        const borrowedBar = Math.min(100, borrowedPercent < 0 ? 0 : borrowedPercent);
        const returnedBar = Math.min(100, returnedPercent < 0 ? 0 : returnedPercent);
        const borrowedIcon = borrowedPercent < 0 ? `<i class="bi bi-caret-down-fill text-danger"></i>` : `<i class="bi bi-caret-up-fill text-success"></i>`;
        const borrowedPercentClass = borrowedPercent < 0 ? 'text-danger' : 'text-success';
        const returnedIcon = returnedPercent < 0 ? `<i class="bi bi-caret-down-fill text-danger"></i>` : `<i class="bi bi-caret-up-fill text-success"></i>`;
        const returnedPercentClass = returnedPercent < 0 ? 'text-danger' : 'text-success';
        
        $('#stat-borrowed').text(borrowedNow);
        $('#stat-borrowed-percent').attr('class', `stat-percent ${borrowedPercentClass}`).html(`${borrowedPercent}% ${borrowedIcon}`);
        $('#stat-borrowed-bar').css('width', borrowedBar + '%');
        $('#stat-returned').text(returnedNow);
        $('#stat-returned-percent').attr('class', `stat-percent ${returnedPercentClass}`).html(`${returnedPercent}% ${returnedIcon}`);
        $('#stat-returned-bar').css('width', returnedBar + '%');
        $('#stat-total').text(<?= json_encode($totalAvailable) ?>);
        $('#stat-total-percent').attr('class','stat-percent text-success').html('0% <i class="bi bi-caret-up-fill text-success"></i>');
        $('#stat-total-bar').css('width','0%');
    }

    updateStatList(currentType);

    document.querySelectorAll('.btn-outline-secondary.btn-sm').forEach(btn => {
        btn.addEventListener('click', function() {
            const label = this.textContent.trim().toLowerCase();
            if (label === 'hari') currentType = 'harian';
            else if (label === 'bulan') currentType = 'bulanan';
            else if (label === 'tahun') currentType = 'tahunan';
            renderFlotChart(currentType);
            updateStatList(currentType);
        });
    });

    // fetch data
    function fetchSiswaData() {
        $.get("<?= base_url('user/list/murid') ?>", function(response) {
            if (response.success && Array.isArray(response.users)) {
                siswaList = response.users.map(u => ({
                    ...u,
                    key: u.key ?? u.id ?? null
                }));
                usersByKey = {};
                siswaList.forEach(u => { if (u.key) usersByKey[u.key] = u; });
                dataReady.siswa = true;
                checkAllDataReady();
            }
        }).fail(() => {
            console.error('Failed to fetch siswa data');
            dataReady.siswa = true;
            checkAllDataReady();
        });
    }

    function fetchGuruData() {
        $.get("<?= base_url('user/list/guru') ?>", function(response) {
            if (response.success && Array.isArray(response.users)) {
                guruList = response.users.map(u => ({ ...u, key: u.key ?? u.id ?? null }));
                dataReady.guru = true;
            }
        }).fail(() => {
            console.error('Failed to fetch guru data');
            dataReady.guru = true;
        });
    }

    function fetchBorrowingsData() {
        $.get("<?= base_url('api/borrowings') ?>", function(response) {
            if (response.success && Array.isArray(response.borrowings)) {
                borrowingsList = response.borrowings;
                dataReady.borrowings = true;
                checkAllDataReady();
            }
        }).fail(() => {
            console.error('Failed to fetch borrowings data');
            dataReady.borrowings = true;
            checkAllDataReady();
        });
    }

    function fetchBookData() {
        $.get("<?= base_url('books/all-key') ?>", function(response) {
            console.log('Book data response:', response);
            
            if (response && typeof response.books === 'object') {
                let arr = [];
                
                // Handle object format
                if (!Array.isArray(response.books)) {
                    arr = Object.entries(response.books).map(([key, book]) => ({ 
                        ...book, 
                        key: book.key || book.id || key 
                    }));
                } else {
                    // Handle array format
                    arr = response.books.map(b => ({
                        ...b,
                        key: b.key || b.id
                    }));
                }
                
                bookTitles = arr.map(b => b.title).filter(Boolean);
                window._allBooks = arr;
                
                console.log('Books loaded successfully:', arr.length, 'books');
                console.log('First book structure:', arr[0]);
                
                dataReady.books = true;
                checkAllDataReady();
            } else {
                console.error('Invalid books response format');
                bookTitles = [];
                window._allBooks = [];
                dataReady.books = true;
                checkAllDataReady();
            }
        }).fail(function(err) {
            console.error('Failed to fetch books data:', err);
            bookTitles = [];
            window._allBooks = [];
            dataReady.books = true;
            checkAllDataReady();
        });
    }

    function fetchReturnsData(callback) {
        $.get("<?= base_url('api/returns') ?>", function(response) {
            if (response.success && Array.isArray(response.returns)) {
                returnsList = response.returns;
            } else {
                returnsList = [];
            }
            if (typeof callback === 'function') callback();
        }).fail(() => {
            console.error('Failed to fetch returns data');
            returnsList = [];
            if (typeof callback === 'function') callback();
        });
    }

    // ===== INITIAL DATA FETCHING =====
    startDataLoadTimeout();
    fetchReturnsData(function() {
        fetchSiswaData();
        fetchBorrowingsData();
        fetchBookData();
        fetchGuruData();
    });

    // ===== TABLE SEARCH FUNCTIONS =====
    function filterPeminjamanTable() {
        const query = document.getElementById('searchPeminjaman').value.toLowerCase();
        const rows = document.querySelectorAll('#tbodyBorrowings tr');
        
        rows.forEach(row => {
            const nama = row.cells[1]?.textContent.toLowerCase() || '';
            const buku = row.cells[2]?.textContent.toLowerCase() || '';
            
            if (nama.includes(query) || buku.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterPengembalianTable() {
        const query = document.getElementById('searchPengembalian').value.toLowerCase();
        const rows = document.querySelectorAll('#tbodyReturns tr');
        
        rows.forEach(row => {
            const nama = row.cells[1]?.textContent.toLowerCase() || '';
            const buku = row.cells[2]?.textContent.toLowerCase() || '';
            
            if (nama.includes(query) || buku.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Attach event listeners to search inputs and buttons
    document.getElementById('searchPeminjaman').addEventListener('input', filterPeminjamanTable);
    document.getElementById('cariPeminjaman').addEventListener('click', filterPeminjamanTable);
    
    document.getElementById('searchPengembalian').addEventListener('input', filterPengembalianTable);
    document.getElementById('cariPengembalian').addEventListener('click', filterPengembalianTable);

    // ===== AUTOCOMPLETE FUNCTIONS =====
    function enablePengembalianAutocomplete() {
        $('#namaCariPengembalian').autocomplete({
            source: function(request, response) {
                const userIdsWithActiveBorrow = Array.from(
                    new Set(borrowingsList.filter(b => b.status === 'active').map(b => b.user_id))
                );
                const names = userIdsWithActiveBorrow
                    .map(uid => {
                        const user = siswaList.find(u => u.key == uid || u.id == uid || u.firebase_key == uid);
                        return user ? user.nama : null;
                    })
                    .filter(nama => nama && nama.toLowerCase().includes(request.term.toLowerCase()));
                response(names);
            },
            minLength: 1,
            select: function(event, ui) {
                $(this).val(ui.item.value);
                updateAvailableBooks(ui.item.value);
                return false;
            }
        });

        $('#judulCariPengembalian').autocomplete({
            source: [],
            minLength: 0,
            select: function(event, ui) {
                $(this).val(ui.item.value);
                return false;
            }
        });

        $('#namaCariPengembalian').on('autocompleteselect', function(event, ui) {
            updateAvailableBooks(ui.item.value);
        });

        $('#namaCariPengembalian').on('change', function() {
            const nama = $(this).val();
            if (siswaList.some(u => u.nama === nama)) {
                updateAvailableBooks(nama);
            } else {
                $('#judulCariPengembalian').autocomplete('option', 'source', []);
            }
        });
    }

    $('#namaCari').autocomplete({
        source: function(request, response) {
            const results = siswaList.map(u => u.nama).filter(nama => nama && nama.toLowerCase().includes(request.term.toLowerCase()));
            response(results);
        },
        minLength: 1,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            return false;
        }
    });

    $('#judulCari').autocomplete({
        source: function(request, response) {
            const results = bookTitles.filter(title => title && title.toLowerCase().includes(request.term.toLowerCase()));
            response(results);
        },
        minLength: 1,
        select: function(event, ui) {
            $(this).val(ui.item.value);

            const selectedBook = (window._allBooks || []).find(b => b.title === ui.item.value);

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

    function updateAvailableBooks(nama) {
        const user = siswaList.find(u => u.nama === nama);
        if (!user) {
            $('#judulCariPengembalian').autocomplete('option','source',[]);
            return;
        }

        const userBorrowings = borrowingsList.filter(b =>
            (b.user_id === user.key || b.user_id === user.id || b.user_id === user.firebase_key) &&
            b.status === 'active'
        );

        const borrowedTitles = userBorrowings.map(b => {
            const book = (window._allBooks || []).find(book =>
                book.id == b.book_id || book.key == b.book_id || book.firebase_key == b.book_id
            );
            return book ? book.title : null;
        }).filter(Boolean);

        $('#judulCariPengembalian').autocomplete('option','source', borrowedTitles);
        if (borrowedTitles.length > 0) {
            $('#judulCariPengembalian').autocomplete('search','');
        } else {
            showToast('Riwayat peminjaman tidak ditemukan untuk siswa ini.');
        }
    }

    // ===== MODAL HANDLERS =====
    peminjamanBtn.addEventListener('click', function() {
        modalTitle.textContent = 'Form Peminjaman';
        peminjamanSection.style.display = 'block';
        pengembalianSection.style.display = 'none';
    });

    pengembalianBtn.addEventListener('click', function() {
        modalTitle.textContent = 'Form Pengembalian';
        peminjamanSection.style.display = 'none';
        pengembalianSection.style.display = 'block';
        
        // Ensure data is ready before loading checklist
        if (!dataReady.books || !window._allBooks || !Array.isArray(window._allBooks)) {
            console.warn('Books data not ready yet, waiting...');
            $('#checklistPengembalian').html('<p class="text-muted text-center">Memuat data buku...</p>');
            
            let waitCount = 0;
            const checkInterval = setInterval(() => {
                waitCount++;
                if (dataReady.books && window._allBooks && Array.isArray(window._allBooks)) {
                    clearInterval(checkInterval);
                    console.log('Books data ready, loading checklist');
                    loadPengembalianChecklist();
                } else if (waitCount > 50) { // 5 second timeout (50 * 100ms)
                    clearInterval(checkInterval);
                    console.error('Timeout waiting for books data');
                    $('#checklistPengembalian').html('<p class="text-danger text-center">Gagal memuat data buku. Silakan refresh halaman.</p>');
                }
            }, 100);
        } else {
            loadPengembalianChecklist();
        }
    });

    $('#exampleModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $('#uidInputSection').hide();
        $('#namaCari, #judulCari, #namaCariPengembalian, #judulCariPengembalian').val('');
        $('#searchSiswaReturn').val('');
    });

    // ===== PENGEMBALIAN CHECKLIST FUNCTIONS =====
    function loadPengembalianChecklist() {
        // Double-check that data is available
        if (!window._allBooks || !Array.isArray(window._allBooks)) {
            console.error('Books data is not available:', window._allBooks);
            $('#checklistPengembalian').html('<p class="text-danger text-center">Error: Data buku tidak tersedia. Silakan refresh halaman.</p>');
            return;
        }

        const activeLoans = borrowingsList.filter(b => b.status === 'active');
        
        console.log('Loading checklist with:', {
            activeLoans: activeLoans.length,
            allBooks: window._allBooks.length,
            siswaList: siswaList.length
        });

        if (activeLoans.length === 0) {
            $('#checklistPengembalian').html('<p class="text-muted text-center">Tidak ada peminjaman aktif.</p>');
            return;
        }

        // Group by user
        const groupedByUser = {};
        activeLoans.forEach(loan => {
            const userId = loan.user_id;
            if (!groupedByUser[userId]) {
                const user = siswaList.find(u => u.key == userId || u.id == userId || u.firebase_key == userId) || {};
                groupedByUser[userId] = {
                    user: user,
                    loans: []
                };
            }
            groupedByUser[userId].loans.push(loan);
        });

        // Build checklist HTML
        let checklistHtml = '';
        Object.keys(groupedByUser).forEach(userId => {
            const { user, loans } = groupedByUser[userId];
            const userName = escapeHtml(user.nama || 'Unknown');
            
            checklistHtml += `
            <div class="siswa-group mb-3 p-3" style="background-color: #f8f9fa; border-radius: 4px; border-left: 4px solid #007bff;">
                <div style="font-weight: 600; color: #333; margin-bottom: 10px;">${userName}</div>
                <div class="siswa-books">
            `;
            
            loans.forEach(loan => {
                let book = null;
                let debugInfo = {
                    loanBookId: loan.book_id,
                    loanBookIdType: typeof loan.book_id
                };
                
                // Find matching book with detailed logging
                if (window._allBooks && Array.isArray(window._allBooks)) {
                    book = window._allBooks.find(b => {
                        const bookId = String(loan.book_id).trim().toLowerCase();
                        const bId = String(b.id || '').trim().toLowerCase();
                        const bKey = String(b.key || '').trim().toLowerCase();
                        const bFirebaseKey = String(b.firebase_key || '').trim().toLowerCase();
                        
                        const matches = bookId === bId || bookId === bKey || bookId === bFirebaseKey;
                        
                        if (!matches && !book) {
                            debugInfo.searchedId = bId;
                            debugInfo.searchedKey = bKey;
                            debugInfo.searchedFirebaseKey = bFirebaseKey;
                        }
                        
                        return matches;
                    });
                }
                
                if (!book) {
                    console.warn('Book not found for loan:', loan, 'Debug:', debugInfo);
                    book = {};
                }
                
                const bookTitle = escapeHtml(book.title || 'Unknown Book');
                const loanId = escapeHtml(loan.id || loan.key || '');
                
                checklistHtml += `
                    <div class="form-check mb-2">
                        <input class="form-check-input return-checkbox" type="checkbox" id="return_${loanId}" 
                               data-loan-id="${loanId}" data-user-id="${userId}" data-book-id="${loan.book_id}">
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

    // ===== FORM SUBMIT HANDLERS =====
    $('#submitForm').on('click', function(e) {
        e.preventDefault();
        if ($('#peminjamanSection').is(':visible')) handlePeminjamanAdd();
        else if ($('#pengembalianSection').is(':visible')) handlePengembalianAdd();
    });

    function handlePeminjamanAdd() {
        const peminjamanForm = modal.querySelector('form');
        const nama = peminjamanForm.querySelector('[name="namaCari"]').value.trim();
        const judul = peminjamanForm.querySelector('[name="judulCari"]').value.trim();
        
        const uidInput = peminjamanForm.querySelector('[name="uidCari"]');
        const uid = uidInput && uidInput.offsetParent !== null ? uidInput.value.trim() : '';

        if (!nama || !judul) { 
            showToast('Nama Siswa dan Judul Buku harus diisi!'); 
            return; 
        }

        const formData = new FormData();
        formData.append('namaCari', nama);
        formData.append('judulCari', judul);
        if (uid) formData.append('uidCari', uid);

        $.ajax({
            url: "<?= base_url('peminjaman/add') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#exampleModal').modal('hide');
                    showToast('Peminjaman berhasil ditambahkan!');
                    refreshBorrowingsTable(1);
                    fetchBorrowingsData();
                } else {
                    showToast(response.message || 'Gagal menambahkan peminjaman.');
                }
            },
            error: function(xhr, status, error) {
                showToast('Terjadi kesalahan saat menambah peminjaman!');
            }
        });
    }

    function handlePengembalianAdd() {
        const selectedLoans = [];
        $('#checklistPengembalian .return-checkbox:checked').each(function() {
            selectedLoans.push({
                loanId: $(this).data('loan-id'),
                userId: $(this).data('user-id'),
                bookId: $(this).data('book-id')
            });
        });

        if (selectedLoans.length === 0) {
            showToast('Pilih minimal satu buku untuk dikembalikan!');
            return;
        }

        const formData = new FormData();
        formData.append('selectedLoans', JSON.stringify(selectedLoans));

        $.ajax({
            url: "<?= base_url('peminjaman/return-multiple') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#exampleModal').modal('hide');
                    showToast('Pengembalian berhasil ditambahkan!');
                    refreshBorrowingsTable(1);
                    refreshReturnsTable(1);
                    fetchBorrowingsData();
                    loadPengembalianChecklist();
                } else {
                    showToast(response.message || 'Gagal menambahkan pengembalian.');
                }
            },
            error: function(xhr, status, error) {
                showToast('Terjadi kesalahan saat menambah pengembalian!');
            }
        });
    }

    // ===== TABLE REFRESH FUNCTIONS =====
    function refreshBorrowingsTable(page = 1) {
        currentBorrowingsPage = page;
        
        $.get("<?= base_url('api/borrowings-all') ?>", {
            page: page,
            limit: ITEMS_PER_PAGE
        }, function(response) {
            if (response.success && Array.isArray(response.borrowings)) {
                let rows = '';
                let no = (page - 1) * ITEMS_PER_PAGE + 1;
                response.borrowings.forEach(b => {
                    const user = usersByKey[b.user_id] || {};
                    const book = (window._allBooks || []).find(book => 
                        book.id == b.book_id || book.key == b.book_id || book.firebase_key == b.book_id
                    ) || {};
                    
                    const classId = user.class_id || null;
                    const className = classId ? (window._classesById && window._classesById[classId] ? window._classesById[classId].nama_kelas : '-') : '-';
                    
                    const statusClass = b.status === 'active' ? 'table-danger' : '';
                    
                    rows += `<tr class="${statusClass}">
                        <th scope="row">${no++}</th>
                        <td>${escapeHtml(user.nama || '-')}</td>
                        <td>${escapeHtml(book.title || '-')}</td>
                        <td>${escapeHtml(className)}</td>
                        <td>${escapeHtml(b.tanggal || '-')}</td>
                    </tr>`;
                });
                if (!rows) rows = `<tr><td colspan="5" class="text-center">Belum ada data peminjaman.</td></tr>`;
                $('#tbodyBorrowings').html(rows);
                
                totalBorrowingsPages = Math.ceil(response.totalCount / ITEMS_PER_PAGE);
                renderBorrowingsPagination();
            }
        });
    }

    function refreshReturnsTable(page = 1) {
        currentReturnsPage = page;
        
        $.get("<?= base_url('api/returns-all') ?>", {
            page: page,
            limit: ITEMS_PER_PAGE
        }, function(response) {
            if (response.success && Array.isArray(response.returns)) {
                let rows = '';
                let no = (page - 1) * ITEMS_PER_PAGE + 1;
                response.returns.forEach(r => {
                    const user = usersByKey[r.user_id] || {};
                    const book = (window._allBooks || []).find(book => 
                        book.id == r.book_id || book.key == r.book_id || book.firebase_key == r.book_id
                    ) || {};
                    
                    const classId = user.class_id || null;
                    const className = classId ? (window._classesById && window._classesById[classId] ? window._classesById[classId].nama_kelas : '-') : '-';
                    
                    rows += `<tr>
                        <th scope="row">${no++}</th>
                        <td>${escapeHtml(user.nama || '-')}</td>
                        <td>${escapeHtml(book.title || '-')}</td>
                        <td>${escapeHtml(className)}</td>
                        <td>${escapeHtml(r.tanggal || '-')}</td>
                    </tr>`;
                });
                if (!rows) rows = `<tr><td colspan="5" class="text-center">Belum ada data pengembalian.</td></tr>`;
                $('#tbodyReturns').html(rows);
                
                totalReturnsPages = Math.ceil(response.totalCount / ITEMS_PER_PAGE);
                renderReturnsPagination();
            }
        });
    }

    function fetchClassesData() {
        $.get("<?= base_url('management-class/list') ?>", function(response) {
            if (response.success && Array.isArray(response.classes)) {
                window._classesById = {};
                response.classes.forEach(c => {
                    window._classesById[c.id] = c;
                });
                console.log('Classes loaded successfully:', response.classes.length, 'classes');
            }
        }).fail(() => {
            console.error('Failed to fetch classes data');
            window._classesById = {};
        });
    }

    startDataLoadTimeout();
    fetchReturnsData(function() {
        fetchSiswaData();
        fetchBorrowingsData();
        fetchBookData();
        fetchGuruData();
        fetchClassesData();
    });

    // ===== PAGINATION RENDERING FUNCTIONS =====
    function renderBorrowingsPagination() {
        const paginationHtml = generatePaginationHTML(currentBorrowingsPage, totalBorrowingsPages, 'borrowings');
        $('#paginationBorrowings').html(paginationHtml);
        attachPaginationListeners('borrowings');
    }

    function renderReturnsPagination() {
        const paginationHtml = generatePaginationHTML(currentReturnsPage, totalReturnsPages, 'returns');
        $('#paginationReturns').html(paginationHtml);
        attachPaginationListeners('returns');
    }

    function generatePaginationHTML(currentPage, totalPages, type) {
        let html = '';
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        
        if (endPage - startPage < maxPagesToShow - 1) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        // Previous button
        if (currentPage > 1) {
            html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-prev" data-type="${type}" data-page="${currentPage - 1}">Previous</a></li>`;
        } else {
            html += `<li class="page-item disabled"><a class="page-link text-secondary">Previous</a></li>`;
        }

        // Page numbers
        if (startPage > 1) {
            html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-page" data-type="${type}" data-page="1">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += `<li class="page-item active"><a class="page-link text-secondary" href="#" aria-current="page">${i}</a></li>`;
            } else {
                html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-page" data-type="${type}" data-page="${i}">${i}</a></li>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
            }
            html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-page" data-type="${type}" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Next button
        if (currentPage < totalPages) {
            html += `<li class="page-item"><a href="#" class="page-link text-secondary pagination-next" data-type="${type}" data-page="${currentPage + 1}">Next</a></li>`;
        } else {
            html += `<li class="page-item disabled"><a class="page-link text-secondary">Next</a></li>`;
        }

        return html;
    }

    function attachPaginationListeners(type) {
        $(`#pagination${type.charAt(0).toUpperCase() + type.slice(1)} .pagination-page, #pagination${type.charAt(0).toUpperCase() + type.slice(1)} .pagination-prev, #pagination${type.charAt(0).toUpperCase() + type.slice(1)} .pagination-next`).on('click', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (type === 'borrowings') {
                refreshBorrowingsTable(page);
            } else if (type === 'returns') {
                refreshReturnsTable(page);
            }
        });
    }

    // Export statistics function with HTML report including chart
    function exportStatistics() {
        const totalBorrowed = document.querySelector('.card-body h2')?.textContent || '0';
        const totalBorrowedPercent = document.querySelectorAll('.card-body')[0]?.querySelector('.ms-auto')?.textContent.split('%')[0].trim() || '0';
        const totalReturned = document.querySelectorAll('.card-body h2')[1]?.textContent || '0';
        const totalReturnedPercent = document.querySelectorAll('.card-body')[1]?.querySelector('.ms-auto')?.textContent.split('%')[0].trim() || '0';
        const totalAvailable = document.querySelectorAll('.card-body h2')[2]?.textContent || '0';
        const totalAvailablePercent = document.querySelectorAll('.card-body')[2]?.querySelector('.ms-auto')?.textContent.split('%')[0].trim() || '0';

        const timestamp = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
        const chartImageData = document.getElementById('flot-dashboard-chart').innerHTML;
        
        // Get current chart from DOM
        const chartCanvas = document.querySelector('#flot-dashboard-chart canvas');
        let chartImageBase64 = '';
        if (chartCanvas) {
            chartImageBase64 = chartCanvas.toDataURL('image/png');
        }

        // Build HTML table data for all time ranges
        let tablesHtml = '';
        const timeRanges = ['harian', 'bulanan', 'tahunan'];
        const timeLabels = { 'harian': 'Harian (Per Hari)', 'bulanan': 'Bulanan (Per Bulan)', 'tahunan': 'Tahunan (Per Tahun)' };

        timeRanges.forEach(type => {
            const borrowings = chartData[type]['borrowings'] || {};
            const returns = chartData[type]['returns'] || {};
            const allKeys = [...new Set([...Object.keys(borrowings), ...Object.keys(returns)])].sort();
            
            let tableRows = '';
            allKeys.forEach(key => {
                const borrowCount = borrowings[key] || 0;
                const returnCount = returns[key] || 0;
                tableRows += `<tr><td>${key}</td><td style="text-align:center;">${borrowCount}</td><td style="text-align:center;">${returnCount}</td></tr>`;
            });

            tablesHtml += `
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">${timeLabels[type]}</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 10px; text-align: left; border: 1px solid #dee2e6;">Periode</th>
                            <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Peminjaman</th>
                            <th style="padding: 10px; text-align: center; border: 1px solid #dee2e6;">Pengembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>
            </div>`;
        });

        // Create comprehensive HTML report
        const htmlContent = `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Statistik Perpustakaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #007bff; padding-bottom: 20px; }
        .header h1 { color: #007bff; font-size: 28px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .card { background: #f8f9fa; border-left: 4px solid #007bff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card.borrowed { border-left-color: #1ab394; }
        .card.returned { border-left-color: #1C84C6; }
        .card.available { border-left-color: #f8ac59; }
        .card h3 { color: #666; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; }
        .card .value { font-size: 32px; font-weight: bold; color: #007bff; }
        .card .change { font-size: 12px; margin-top: 5px; }
        .card .change.positive { color: #1ab394; }
        .card .change.negative { color: #d9534f; }
        .chart-section { margin-bottom: 40px; }
        .chart-section h2 { color: #333; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .chart-container { text-align: center; }
        .chart-container img { max-width: 100%; height: auto; border: 1px solid #dee2e6; border-radius: 5px; }
        .data-tables { margin-top: 40px; }
        .data-tables h2 { color: #333; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background-color: #f8f9fa; }
        th { padding: 10px; text-align: left; border: 1px solid #dee2e6; font-weight: 600; }
        td { padding: 10px; border: 1px solid #dee2e6; }
        tbody tr:nth-child(odd) { background-color: #ffffff; }
        tbody tr:nth-child(even) { background-color: #f8f9fa; }
        tbody tr:hover { background-color: #e7f3ff; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666; font-size: 12px; }
        @media print { body { background: white; } .container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Laporan Statistik Perpustakaan</h1>
            <p>Tanggal Laporan: ${timestamp}</p>
        </div>

        <div class="summary-cards">
            <div class="card borrowed">
                <h3>Buku yang Dipinjam</h3>
                <div class="value">${totalBorrowed}</div>
                <div class="change ${totalBorrowedPercent >= 0 ? 'positive' : 'negative'}">
                    ${totalBorrowedPercent >= 0 ? '📈' : '📉'} ${totalBorrowedPercent}% dari periode sebelumnya
                </div>
            </div>
            <div class="card returned">
                <h3>Buku yang Dikembalikan</h3>
                <div class="value">${totalReturned}</div>
                <div class="change ${totalReturnedPercent >= 0 ? 'positive' : 'negative'}">
                    ${totalReturnedPercent >= 0 ? '📈' : '📉'} ${totalReturnedPercent}% dari periode sebelumnya
                </div>
            </div>
            <div class="card available">
                <h3>Total Buku</h3>
                <div class="value">${totalAvailable}</div>
                <div class="change positive">
                    📈 ${totalAvailablePercent}% dari periode sebelumnya
                </div>
            </div>
        </div>

        <div class="chart-section">
            <h2>📈 Grafik Statistik Peminjaman Buku</h2>
            <div class="chart-container">
                ${chartImageBase64 ? `<img src="${chartImageBase64}" alt="Grafik Peminjaman" />` : '<p style="color: #999;">Grafik tidak tersedia</p>'}
            </div>
        </div>

        <div class="data-tables">
            <h2>📋 Detail Data Statistik</h2>
            ${tablesHtml}
        </div>

        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Perpustakaan</p>
            <p>© ${new Date().getFullYear()} - Semua Hak Dilindungi</p>
        </div>
    </div>
</body>
</html>`;

        // Create blob and download
        const blob = new Blob([htmlContent], { type: 'text/html;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        const fileName = `Laporan_Statistik_Perpustakaan_${new Date().toISOString().slice(0, 10)}.html`;
        
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast('Laporan statistik berhasil diunduh!');
    }

    // Attach export button listener
    const exportStatsBtn = document.getElementById('exportStats');
    if (exportStatsBtn) {
        exportStatsBtn.addEventListener('click', exportStatistics);
    }
});
</script>

<?= $this->endSection() ?>