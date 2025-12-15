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
    .table img {
        border-radius: 4px;
    }
    .table td {
        max-width: 140px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .table tbody tr {
        cursor: pointer;
    }
</style>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen Buku</li>
        </ol>
    </nav>

    <!-- Tombol Tambah & Import -->
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-primary" id="btnTambahBuku">
            <i class="bi bi-plus"></i> Tambah Buku
        </button>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Import JSON
        </button>
        <!-- Tombol Export CSV -->
        <a href="<?= base_url('management-buku/export-csv') ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    <!-- Tabel Buku -->
    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List buku
            <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBuku" aria-expanded="false" aria-controls="collapseBuku"></i>
        </div>
        <div class="card-body">
            <div class="collapse show" id="collapseBuku">
                <div class="input-group input-group-sm mb-3 justify-content-end">
                    <input type="text" id="searchBuku" class="form-control" placeholder="Cari dengan Kode/Judul" style="max-width: 250px;">
                    <button class="btn btn-success" type="button" id="cariBuku">Cari</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Penerbit</th>
                                <th>Tahun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bukuTableBody">
                            <?php $i=1; foreach($books as $book): ?>
                                <tr data-book='<?= json_encode($book, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>'>
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($book['code'] ?? '-') ?></td>
                                    <td><?= esc($book['title'] ?? '-') ?></td>
                                    <td><?= esc($book['author'] ?? '-') ?></td>
                                    <td><?= esc($book['publisher'] ?? '-') ?></td>
                                    <td><?= esc($book['year'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit-buku" type="button">Edit</button>
                                        <a href="<?= base_url('management-buku/delete?code='.urlencode($book['code'])) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($books)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Data buku kosong</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <nav aria-label="...">
                <ul class="pagination justify-content-center"></ul>
            </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Buku -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="bukuForm" action="<?= base_url('management-buku/add') ?>" method="post" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalBukuTitle">Tambah Buku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editId" id="editId">
        
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="uid" class="form-label required">UID</label>
                <div class="uid-container">
                    <input type="text" name="uid[]" class="form-control mb-1" placeholder="Masukkan UID">
                </div>
                <small class="text-muted">Tap RFID/Input Manual</small>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-add-uid">
                    + Tambah UID
                </button>
            </div>
            <div class="col-md-4">
                <label for="code" class="form-label required">Kode</label>
                <input type="text" name="code" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="quantity" class="form-label required">Quantity</label>
                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                <small class="text-muted">Masukkan jumlah buku (minimal 1)</small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="title" class="form-label required">Judul</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col">
                <label for="author" class="form-label required">Penulis</label>
                <input type="text" name="author" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="publisher" class="form-label required">Penerbit</label>
                <input type="text" name="publisher" class="form-control">
            </div>
            <div class="col">
                <label for="genre" class="form-label required">Genre</label>
                <input type="text" name="genre" class="form-control" required>
            </div>
        </div>
        <!-- Tambahkan field ISBN -->
        <div class="row mb-3">
            <div class="col">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control">
            </div>
            <div class="col">
                <label for="year" class="form-label required">Tahun</label>
                <input type="number" name="year" class="form-control">
            </div>
            <div class="col">
                <label for="illustrator" class="form-label required">Illustrator</label>
                <input type="text" name="illustrator" class="form-control">
            </div>
        </div>
        <!-- End ISBN -->
        <div class="row mb-3">
            <div class="col">
                <label for="series" class="form-label required">Seri</label>
                <input type="text" name="series" class="form-control">
            </div>
            <div class="col">
                <label for="notes" class="form-label">Catatan</label>
                <input type="text" name="notes" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Gambar</label>
            <input type="file" name="image" class="form-control">
            <small class="text-muted" id="currentImageText"></small>
        </div>
        <div class="mb-3">
            <div class="d-flex gap-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="isOneDayBook" name="isOneDayBook">
                    <label class="form-check-label" for="isOneDayBook">Buku 1 Hari</label>
                </div>
                <div class="form-check form-switch" id="availableSection" style="display: none;">
                    <input class="form-check-input" type="checkbox" role="switch" id="available" name="available">
                    <label class="form-check-label" for="available">Tersedia</label>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="synopsis" class="form-label required">Sinopsis</label>
            <textarea name="synopsis" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="submitBukuBtn">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Detail Buku -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Detail Buku</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table pe-none">
            <tr><th>Kode</th><td id="detailKode"></td></tr>
            <tr><th>Judul</th><td id="detailJudul"></td></tr>
            <tr><th>Penulis</th><td id="detailPenulis"></td></tr>
            <tr><th>Penerbit</th><td id="detailPenerbit"></td></tr>
            <tr><th>Tahun</th><td id="detailTahun"></td></tr>
            <tr><th>Genre</th><td id="detailGenre"></td></tr>
            <tr><th>Series</th><td id="detailSeries"></td></tr>
            <tr><th>Posisi Rak</th><td id="detailShelfPosition"></td></tr>
            <tr><th>Tersedia</th><td id="detailAvailable"></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Import JSON -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="<?= base_url('management-buku/importJson') ?>" method="post" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Buku dari JSON</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label for="json_file" class="form-label required">File JSON</label>
        <input type="file" name="json_file" class="form-control" accept=".json" required>
        <small class="text-muted">Format: Array of books dengan field: code, title, author, dll.</small>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Import</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchBuku');
    const tableBody = document.getElementById('bukuTableBody');
    const rowsPerPage = 25;
    let currentPage = 1;
    const bukuForm = document.getElementById('bukuForm');
    const addModal = new bootstrap.Modal(document.getElementById('addModal'));

    // Tombol tambah UID
    document.querySelectorAll('.btn-add-uid').forEach(btn => {
        btn.addEventListener('click', () => {
            const container = btn.closest('.col').querySelector('.uid-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'uid[]';
            input.className = 'form-control mb-1';
            input.placeholder = 'Masukkan UID';
            container.appendChild(input);
        });
    });

    // Handle Tambah Buku button
    document.getElementById('btnTambahBuku').addEventListener('click', function() {
        resetForm();
        document.getElementById('modalBukuTitle').textContent = 'Tambah Buku';
        bukuForm.action = "<?= base_url('management-buku/add') ?>";
        document.getElementById('availableSection').style.display = 'none';
        addModal.show();
    });

    // Reset form function
    function resetForm() {
        bukuForm.reset();
        document.getElementById('editId').value = '';
        document.getElementById('currentImageText').textContent = '';
        
        const uidContainer = document.querySelector('.uid-container');
        uidContainer.innerHTML = '<input type="text" name="uid[]" class="form-control mb-1" placeholder="Masukkan UID">';
        
        // Reset checkbox
        document.getElementById('isOneDayBook').checked = false;
        document.getElementById('available').checked = false;
        const qtyInput = document.querySelector('[name="quantity"]');
        if (qtyInput) qtyInput.value = 1;
    }

    // Handle Edit Buku button
    document.querySelectorAll('.btn-edit-buku').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const tr = btn.closest('tr');
            const book = JSON.parse(tr.getAttribute('data-book'));
            
            resetForm();
            document.getElementById('modalBukuTitle').textContent = 'Edit Buku';
            bukuForm.action = "<?= base_url('management-buku/edit/') ?>" + book.id;
            document.getElementById('availableSection').style.display = 'block';
            
            // Fill UID inputs
            const uidContainer = document.querySelector('.uid-container');
            uidContainer.innerHTML = '';
            if (book.uid && Array.isArray(book.uid) && book.uid.length > 0) {
                book.uid.forEach(u => {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'uid[]';
                    input.className = 'form-control mb-1';
                    input.placeholder = 'Masukkan UID';
                    input.value = u || '';
                    uidContainer.appendChild(input);
                });
            } else {
                uidContainer.innerHTML = '<input type="text" name="uid[]" class="form-control mb-1" placeholder="Masukkan UID">';
            }

            // Fill other fields
            document.querySelector('[name="code"]').value = book.code || '';
            document.querySelector('[name="title"]').value = book.title || '';
            document.querySelector('[name="author"]').value = book.author || '';
            document.querySelector('[name="publisher"]').value = book.publisher || '';
            document.querySelector('[name="genre"]').value = book.genre || '';
            // Isi ISBN jika ada
            document.querySelector('[name="isbn"]').value = book.isbn || '';
            document.querySelector('[name="year"]').value = book.year || '';
            document.querySelector('[name="illustrator"]').value = book.illustrator || '';
            document.querySelector('[name="series"]').value = book.series || '';
            document.querySelector('[name="notes"]').value = book.notes || '';
            document.querySelector('[name="synopsis"]').value = book.synopsis || '';
            const qtyInputEdit = document.querySelector('[name="quantity"]');
            if (qtyInputEdit) qtyInputEdit.value = (typeof book.quantity !== 'undefined' && book.quantity !== null) ? book.quantity : 1;
            
            // Set checkboxes
            document.getElementById('isOneDayBook').checked = book.is_one_day_book || false;
            document.getElementById('available').checked = book.available !== false;
            
            // Show current image
            if (book.image) {
                document.getElementById('currentImageText').textContent = 'Gambar saat ini: ' + book.image;
            }
            
            document.getElementById('editId').value = book.id;
            
            addModal.show();
        });
    });

    // Pagination functions
    function renderPagination(totalRows) {
        const pages = Math.ceil(totalRows / rowsPerPage);
        const paginationContainer = document.querySelector('.pagination');
        paginationContainer.innerHTML = '';

        if (pages <= 1) return;

        const createPageItem = (text, page, disabled = false, active = false) => {
            const li = document.createElement('li');
            li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = text;
            a.addEventListener('click', function(e) {
                e.preventDefault();
                if (!disabled && page >= 1 && page <= pages) {
                    currentPage = page;
                    filterTable();
                }
            });
            li.appendChild(a);
            return li;
        };

        paginationContainer.appendChild(createPageItem('Previous', currentPage - 1, currentPage === 1));

        const visiblePages = 5;
        let startPage = Math.max(currentPage - Math.floor(visiblePages / 2), 1);
        let endPage = startPage + visiblePages - 1;

        if (endPage > pages) {
            endPage = pages;
            startPage = Math.max(endPage - visiblePages + 1, 1);
        }

        if (startPage > 1) {
            paginationContainer.appendChild(createPageItem(1, 1));
            if (startPage > 2) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = `<span class="page-link">...</span>`;
                paginationContainer.appendChild(li);
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            paginationContainer.appendChild(createPageItem(p, p, false, p === currentPage));
        }

        if (endPage < pages) {
            if (endPage < pages - 1) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = `<span class="page-link">...</span>`;
                paginationContainer.appendChild(li);
            }
            paginationContainer.appendChild(createPageItem(pages, pages));
        }

        paginationContainer.appendChild(createPageItem('Next', currentPage + 1, currentPage === pages));
    }

    function filterTable() {
        const query = searchInput.value.toLowerCase();
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        
        const filtered = rows.filter(row => {
            const code = row.children[1]?.textContent.toLowerCase() || '';
            const title = row.children[2]?.textContent.toLowerCase() || '';
            return code.includes(query) || title.includes(query);
        });

        rows.forEach(row => row.style.display = 'none');

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filtered.slice(start, end).forEach(row => row.style.display = '');

        renderPagination(filtered.length);
    }

    // Double click for detail
    document.querySelectorAll('#bukuTableBody tr').forEach(function(row) {
        row.addEventListener('dblclick', function() {
            const book = JSON.parse(row.getAttribute('data-book'));
            document.getElementById('detailKode').textContent = book.code || '-';
            document.getElementById('detailJudul').textContent = book.title || '-';
            document.getElementById('detailPenulis').textContent = book.author || '-';
            document.getElementById('detailPenerbit').textContent = book.publisher || '-';
            document.getElementById('detailTahun').textContent = book.year || '-';
            document.getElementById('detailGenre').textContent = book.genre || '-';
            document.getElementById('detailSeries').textContent = book.series || '-';
            document.getElementById('detailShelfPosition').textContent = book.shelf_position || '-';
            document.getElementById('detailAvailable').textContent = book.available ? 'Ya' : 'Tidak';

            var detailModal = new bootstrap.Modal(document.getElementById('exampleModal'));
            detailModal.show();
        });
    });

    searchInput.addEventListener('input', () => { currentPage = 1; filterTable(); });
    document.getElementById('cariBuku').addEventListener('click', () => { currentPage = 1; filterTable(); });

    filterTable();
});
</script>

<?php if (session()->getFlashdata('message')): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("<?= esc(session()->getFlashdata('message'), 'js') ?>");
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>