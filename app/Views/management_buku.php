<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
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

    /* Toast Notification Styles */
    #toastContainer {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }

    .custom-toast {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
        border-left: 4px solid;
    }

    .custom-toast.success {
        border-left-color: #28a745;
        background: #d4edda;
        color: #155724;
    }

    .custom-toast.error {
        border-left-color: #dc3545;
        background: #f8d7da;
        color: #721c24;
    }

    .custom-toast.info {
        border-left-color: #0d6efd;
        background: #d1ecf1;
        color: #0c5460;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(400px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>

<!-- Toast Container -->
<div id="toastContainer"></div>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manajemen Buku</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" id="managementTambahBuku" class="btn btn-primary btn-mobile-md" data-bs-toggle="modal" data-bs-target="#managementAddBookModal">
            <i class="bi bi-plus"></i> Tambah Buku
        </button>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Import JSON
        </button>
        <a href="<?= base_url(
            "management-buku/export-csv"
        ) ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

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
                            <?php
                            $i = 1;
                            foreach ($books as $book): ?>
                                <tr data-book='<?= json_encode(
                                    $book,
                                    JSON_HEX_QUOT |
                                        JSON_HEX_APOS |
                                        JSON_UNESCAPED_UNICODE
                                ) ?>'>
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($book["code"] ?? "-") ?></td>
                                    <td><?= esc($book["title"] ?? "-") ?></td>
                                    <td><?= esc($book["author"] ?? "-") ?></td>
                                    <td><?= esc(
                                        $book["publisher"] ?? "-"
                                    ) ?></td>
                                    <td><?= esc($book["year"] ?? "-") ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-detail-buku" type="button" title="Lihat Detail"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-warning btn-edit-buku" type="button">Edit</button>
                                        <a href="<?= base_url(
                                            "management-buku/delete?code=" .
                                                urlencode($book["code"])
                                        ) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach;
                            ?>
                            <?php if (empty($books)): ?>
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

<?= $this->include("partials/management_add_book") ?>

<!-- ===== EDIT MODAL ===== -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="bukuForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Buku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editId" id="editId">

        <div class="row mb-3">
            <div class="col">
                <label for="code" class="form-label required">Kode</label>
                <input type="text" name="code" class="form-control" id="code" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="title" class="form-label required">Judul</label>
                <input type="text" name="title" class="form-control" id="title" required>
            </div>
            <div class="col">
                <label for="author" class="form-label required">Penulis</label>
                <input type="text" name="author" class="form-control" id="author">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="illustrator" class="form-label">Illustrator</label>
                <input type="text" name="illustrator" class="form-control" id="illustrator">
            </div>
            <div class="col">
                <label for="publisher" class="form-label">Penerbit</label>
                <input type="text" name="publisher" class="form-control" id="publisher">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="series" class="form-label">Series</label>
                <input type="text" name="series" class="form-control" id="series">
            </div>
            <div class="col">
                <label for="genre" class="form-label required">Genre</label>
                <select class="form-select" id="genre" name="genre">
                    <option selected disabled>Pilih genre</option>
                    <option value="Al-Quran">Al-Quran</option>
                    <option value="Bedah Soal Dan Materi">Bedah Soal Dan Materi</option>
                    <option value="Biografi">Biografi</option>
                    <option value="Buku Agama">Buku Agama</option>
                    <option value="Buku Pelajaran">Buku Pelajaran</option>
                    <option value="Buku Orang Tua">Buku Orang Tua</option>
                    <option value="Buku Paket Guru">Buku Paket Guru</option>
                    <option value="Buku Panduan">Buku Panduan</option>
                    <option value="Buku Sumber">Buku Sumber</option>
                    <option value="Cerita Anak">Cerita Anak</option>
                    <option value="Cerita Anak Fiksi">Cerita Anak Fiksi</option>
                    <option value="Cerita Anak Fiksi English">Cerita Anak Fiksi English</option>
                    <option value="Cerita Anak Fiksi Islami">Cerita Anak Fiksi Islami</option>
                    <option value="Cerita Anak Hewan">Cerita Anak Hewan</option>
                    <option value="Cerita Anak Islami">Cerita Anak Islami</option>
                    <option value="Cerita Anak Psikologi">Cerita Anak Psikologi</option>
                    <option value="Cerita Anak Sains">Cerita Anak Sains</option>
                    <option value="English Book">English Book</option>
                    <option value="Ensiklopedia Anak">Ensiklopedia Anak</option>
                    <option value="Hard Cover">Hard Cover</option>
                    <option value="Komik Anak">Komik Anak</option>
                    <option value="Novel">Novel</option>
                    <option value="Novel Anak">Novel Anak</option>
                    <option value="Novel English">Novel English</option>
                    <option value="Novel Guru">Novel Guru</option>
                    <option value="Novel Komik">Novel Komik</option>
                    <option value="Panduan Guru">Panduan Guru</option>
                    <option value="Referensi Guru">Referensi Guru</option>
                    <option value="Umum">Umum</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control" id="isbn">
            </div>
            <div class="col">
                <label for="ddcNumber" class="form-label">DDC Number</label>
                <input type="text" name="ddcNumber" class="form-control" id="ddcNumber">
            </div>
            <div class="col">
                <label for="year" class="form-label">Tahun</label>
                <input type="number" name="year" class="form-control" id="year">
            </div>
            <div class="col">
                <label for="quantity" class="form-label required">Quantity</label>
                <input type="number" name="quantity" class="form-control" id="quantity" min="1" value="1" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label required">Gambar (URL)</label>
            <input type="text" name="image" class="form-control" id="imageLink" placeholder="Paste image URL here">
            <small class="text-muted" id="currentImageText"></small>
        </div>

        <div class="mb-3">
            <img id="previewImageMgmt" src="" alt="Preview" style="max-width: 100%; max-height: 300px; display:none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>

        <div class="mb-3">
            <label for="synopsis" class="form-label">Sinopsis</label>
            <textarea name="synopsis" class="form-control" id="synopsis" rows="3"></textarea>
        </div>

        <div class="mb-3" id="uidSection">
            <label class="form-label fw-bold required">
                <i class="bi bi-credit-card"></i> UID RFID
            </label>
            <div class="uid-container" id="uidContainer"></div>
            <button class="btn btn-sm btn-primary mt-1" type="button" id="btnAddUid">
                <i class="bi bi-plus"></i> Tambah UID Manual
            </button>
        </div>

        <div class="mb-3">
            <div class="d-flex gap-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="isOneDayBook" name="isOneDayBook">
                    <label class="form-check-label" for="isOneDayBook">Buku 1 Hari</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="available" name="available">
                    <label class="form-check-label" for="available">Tersedia</label>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="submitBukuBtn">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== DETAIL MODAL ===== -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <h1 class="modal-title fs-5 mb-0" id="exampleModalLabel">Detail Buku</h1>
          <span id="availabilityBadge"></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Informasi Dasar Buku -->
        <div class="mb-4">
          <h5 class="border-bottom pb-2 mb-3">
            <i class="bi bi-book"></i> Informasi Buku
          </h5>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="text-muted small">Kode</label>
              <p class="fw-bold" id="detailKode">-</p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">ISBN</label>
              <p class="fw-bold" id="detailIsbn">-</p>
            </div>
          </div>

          <div class="mb-3">
            <label class="text-muted small">Judul</label>
            <p class="fw-bold" id="detailJudul">-</p>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="text-muted small">Pengarang</label>
              <p id="detailPenulis">-</p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Illustrator</label>
              <p id="detailIllustrator">-</p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="text-muted small">Penerbit</label>
              <p id="detailPenerbit">-</p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Tahun</label>
              <p id="detailTahun">-</p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="text-muted small">Genre</label>
              <p id="detailGenre">-</p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Series</label>
              <p id="detailSeries">-</p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="text-muted small">DDC Number</label>
              <p id="detailDdc">-</p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Tipe Buku</label>
              <p id="detailTipe">-</p>
            </div>
          </div>

          <div class="mb-3">
            <label class="text-muted small">Posisi Rak</label>
            <p id="detailShelfPosition">-</p>
          </div>
        </div>

        <!-- Sinopsis -->
        <div class="mb-4" id="synopsisSection" style="display: none;">
          <h5 class="border-bottom pb-2 mb-3">
            <i class="bi bi-file-text"></i> Sinopsis
          </h5>
          <p id="detailSinopsis" style="font-size: 0.9rem; line-height: 1.6;">-</p>
        </div>

        <!-- Status Ketersediaan -->
        <div class="mb-4 p-3 rounded" id="stockStatusSection" style="background-color: #f8f9fa;">
          <h5 class="mb-3">
            <i class="bi bi-box"></i> Status Ketersediaan
          </h5>
          <div class="row text-center">
            <div class="col-md-4">
              <label class="text-muted small d-block">Total Exemplar</label>
              <h4 class="fw-bold text-secondary" id="detailTotalQty">-</h4>
            </div>
            <div class="col-md-4">
              <label class="text-muted small d-block">Sedang Dipinjam</label>
              <h4 class="fw-bold text-secondary" id="detailBorrowedQty">-</h4>
            </div>
            <div class="col-md-4">
              <label class="text-muted small d-block">Tersedia</label>
              <h4 class="fw-bold text-secondary" id="detailAvailableQty">-</h4>
            </div>
          </div>
        </div>

        <!-- Data Peminjam -->
        <div id="borrowersSection" style="display: none;">
          <h5 class="border-bottom pb-2 mb-3">
            <i class="bi bi-person-check"></i> Sedang Dipinjam Oleh
          </h5>
          <div id="borrowersList" class="table-responsive">
            <table class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Nama Peminjam</th>
                  <th>Kelas</th>
                  <th>Tgl Pinjam</th>
                  <th>Jatuh Tempo</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody id="borrowersBody">
              </tbody>
            </table>
          </div>
          <p id="noBorrowersMsg" class="text-muted text-center py-3" style="display: none;">
            <i class="bi bi-info-circle"></i> Tidak ada peminjam saat ini
          </p>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center py-4" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted mt-2">Memuat informasi...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== IMPORT MODAL ===== -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="<?= base_url(
        "management-buku/importJson"
    ) ?>" method="post" enctype="multipart/form-data" class="modal-content">
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
// Global Toast Function
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;

    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : type === 'info' ? 'ℹ' : '!';
    toast.innerHTML = `
        <div style="margin-right: 12px; font-size: 20px; font-weight: bold;">${icon}</div>
        <div style="flex: 1;">${message}</div>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchBuku');
    const tableBody = document.getElementById('bukuTableBody');
    const rowsPerPage = 25;
    let currentPage = 1;
    const bukuForm = document.getElementById('bukuForm');
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));

    let sortState = {
        column: null,
        direction: 'asc'
    };

    const columnHeaders = {
        'kode': 1,
        'judul': 2,
        'penulis': 3,
        'penerbit': 4,
        'tahun': 5
    };

    Object.entries(columnHeaders).forEach(([colName, colIndex]) => {
        const th = document.querySelector(`thead tr th:nth-child(${colIndex + 1})`);
        if (th && colIndex > 0) {
            th.style.cursor = 'pointer';
            th.style.userSelect = 'none';
            th.innerHTML += ' <i class="bi bi-arrow-down-up" style="font-size: 0.8rem; opacity: 0.5;"></i>';

            th.addEventListener('click', function() {
                const icon = th.querySelector('i');

                document.querySelectorAll('thead tr th').forEach(header => {
                    const i = header.querySelector('i');
                    if (i && header !== th) {
                        i.className = 'bi bi-arrow-down-up';
                        i.style.opacity = '0.5';
                    }
                });

                if (sortState.column === colName) {
                    sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    sortState.column = colName;
                    sortState.direction = 'asc';
                }

                if (sortState.column === colName) {
                    icon.className = sortState.direction === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
                    icon.style.opacity = '1';
                }

                currentPage = 1;
                filterTable();
            });
        }
    });

    function sortRows(rows, column, direction) {
        const columnIndex = columnHeaders[column];

        return rows.sort((rowA, rowB) => {
            let valueA = rowA.children[columnIndex]?.textContent.trim() || '';
            let valueB = rowB.children[columnIndex]?.textContent.trim() || '';

            if (!isNaN(valueA) && valueA !== '') valueA = parseFloat(valueA);
            if (!isNaN(valueB) && valueB !== '') valueB = parseFloat(valueB);

            if (typeof valueA === 'string') valueA = valueA.toLowerCase();
            if (typeof valueB === 'string') valueB = valueB.toLowerCase();

            if (direction === 'asc') {
                return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
            } else {
                return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
            }
        });
    }

    // ===== EDIT BOOK =====

    function resetForm() {
        bukuForm.reset();
        document.getElementById('editId').value = '';
        document.getElementById('currentImageText').textContent = '';
        document.getElementById('previewImageMgmt').style.display = 'none';
        document.getElementById('uidContainer').innerHTML = '';
    }

    async function submitBookEdit(bookData) {
        try {
            const editId = document.getElementById('editId').value;

            if (!editId) {
                showToast('ID buku tidak ditemukan', 'error');
                return;
            }

            const response = await fetch("<?= base_url(
                "management-buku/edit/"
            ) ?>" + editId, {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookData)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} - ${errorText}`);
            }

            const data = await response.json();

            if (data.success) {
                editModal.hide();
                showToast('Buku berhasil diupdate!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Gagal mengupdate buku: ' + data.message, 'error');
            }

        } catch (error) {
            showToast('Terjadi kesalahan saat mengupdate buku: ' + error.message, 'error');
        }
    }

    document.getElementById('btnAddUid').addEventListener('click', () => {
        const container = document.getElementById('uidContainer');
        const inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-2';
        inputGroup.innerHTML = `
            <input type="text" name="uid[]" class="form-control" placeholder="Masukkan UID">
            <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(inputGroup);
    });

    document.querySelectorAll('.btn-edit-buku').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const tr = btn.closest('tr');
            const book = JSON.parse(tr.getAttribute('data-book'));
            resetForm();

            bukuForm.querySelector('[name="code"]').value = book.code || '';
            bukuForm.querySelector('[name="title"]').value = book.title || '';
            bukuForm.querySelector('[name="author"]').value = book.author || '';
            bukuForm.querySelector('[name="publisher"]').value = book.publisher || '';
            bukuForm.querySelector('[name="genre"]').value = book.genre || '';
            bukuForm.querySelector('[name="isbn"]').value = book.isbn || '';
            bukuForm.querySelector('[name="ddcNumber"]').value = book.ddc_number || '';
            bukuForm.querySelector('[name="year"]').value = book.year || '';
            bukuForm.querySelector('[name="illustrator"]').value = book.illustrator || '';
            bukuForm.querySelector('[name="series"]').value = book.series || '';
            bukuForm.querySelector('[name="synopsis"]').value = book.synopsis || '';
            bukuForm.querySelector('[name="quantity"]').value = book.quantity || 1;

            document.getElementById('isOneDayBook').checked = book.is_one_day_book || false;
            document.getElementById('available').checked = book.available !== false;

            if (book.image) {
                document.getElementById('imageLink').value = book.image;
                document.getElementById('previewImageMgmt').src = book.image;
                document.getElementById('previewImageMgmt').style.display = 'block';
                document.getElementById('currentImageText').textContent = 'Gambar saat ini: ' + book.image;
            }

            const uidContainer = document.getElementById('uidContainer');
            uidContainer.innerHTML = '';

            const uids = (book.uid && Array.isArray(book.uid) && book.uid.length > 0)
                ? book.uid.filter(Boolean)
                : [''];

            uids.forEach(uid => {
                const inputGroup = document.createElement('div');
                inputGroup.className = 'input-group mb-2';
                inputGroup.innerHTML = `
                    <input type="text" name="uid[]" class="form-control" placeholder="Masukkan UID" value="${uid}">
                    <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                uidContainer.appendChild(inputGroup);
            });

            document.getElementById('editId').value = book.id;

            editModal.show();
        });
    });

    document.getElementById('submitBukuBtn').addEventListener('click', function(e) {
        e.preventDefault();

        const bookData = {
            code:          bukuForm.querySelector('[name="code"]').value,
            uid:           Array.from(bukuForm.querySelectorAll('[name="uid[]"]')).map(el => el.value).filter(v => v),
            title:         bukuForm.querySelector('[name="title"]').value,
            author:        bukuForm.querySelector('[name="author"]').value,
            illustrator:   bukuForm.querySelector('[name="illustrator"]').value,
            publisher:     bukuForm.querySelector('[name="publisher"]').value,
            series:        bukuForm.querySelector('[name="series"]').value,
            genre:         bukuForm.querySelector('[name="genre"]').value,
            isbn:          bukuForm.querySelector('[name="isbn"]').value,
            ddcNumber:     bukuForm.querySelector('[name="ddcNumber"]').value,
            year:          bukuForm.querySelector('[name="year"]').value,
            quantity:      bukuForm.querySelector('[name="quantity"]').value,
            synopsis:      bukuForm.querySelector('[name="synopsis"]').value,
            image:         document.getElementById('imageLink').value || '',
            is_one_day_book: document.getElementById('isOneDayBook').checked,
            available:     document.getElementById('available').checked
        };

        if (!bookData.code)  { showToast('Kode harus diisi!', 'error');      return; }
        if (!bookData.title) { showToast('Judul harus diisi!', 'error');      return; }
        if (!bookData.author){ showToast('Pengarang harus diisi!', 'error');  return; }
        if (!bookData.genre) { showToast('Genre harus dipilih!', 'error');    return; }
        if (!bookData.quantity || bookData.quantity < 1) { showToast('Quantity harus lebih dari 0!', 'error'); return; }
        if (bookData.uid.length === 0) { showToast('Setidaknya satu UID harus diisi!', 'error'); return; }

        submitBookEdit(bookData);
    });

    // ===== DETAIL MODAL =====

    function openDetailModal(book) {
        const loadingSpinner = document.getElementById('loadingSpinner');
        loadingSpinner.style.display = 'block';
        document.getElementById('borrowersSection').style.display = 'none';
        document.getElementById('synopsisSection').style.display = 'none';

        document.getElementById('detailKode').textContent         = book.code || '-';
        document.getElementById('detailJudul').textContent        = book.title || '-';
        document.getElementById('detailPenulis').textContent      = book.author || '-';
        document.getElementById('detailIllustrator').textContent  = book.illustrator || '-';
        document.getElementById('detailPenerbit').textContent     = book.publisher || '-';
        document.getElementById('detailTahun').textContent        = book.year || '-';
        document.getElementById('detailGenre').textContent        = book.genre || '-';
        document.getElementById('detailSeries').textContent       = book.series || '-';
        document.getElementById('detailIsbn').textContent         = book.isbn || '-';
        document.getElementById('detailDdc').textContent          = book.ddc_number || '-';
        document.getElementById('detailShelfPosition').textContent = book.shelf_position || '-';
        document.getElementById('detailTipe').textContent         = book.is_one_day_book ? 'Buku 1 Hari' : 'Buku Reguler';

        if (book.synopsis && book.synopsis.trim()) {
            document.getElementById('detailSinopsis').textContent = book.synopsis;
            document.getElementById('synopsisSection').style.display = 'block';
        }

        fetch("<?= base_url("management-buku/get-book-borrowers") ?>?book_id=" + book.id)
            .then(response => response.json())
            .then(data => {
                loadingSpinner.style.display = 'none';

                if (data.error) {
                    showToast('Gagal memuat data peminjam: ' + data.error, 'error');
                    return;
                }

                document.getElementById('detailTotalQty').textContent     = data.total_quantity;
                document.getElementById('detailBorrowedQty').textContent  = data.borrowed_count;
                document.getElementById('detailAvailableQty').textContent = data.available_quantity;

                const badge = document.getElementById('availabilityBadge');
                if (data.is_out_of_stock) {
                    badge.innerHTML = '<span class="badge bg-danger"><i class="bi bi-exclamation-circle"></i> HABIS</span>';
                } else if (data.available_quantity <= 2) {
                    badge.innerHTML = '<span class="badge bg-warning"><i class="bi bi-info-circle"></i> Stok Terbatas</span>';
                } else {
                    badge.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Tersedia</span>';
                }

                const borrowersSection = document.getElementById('borrowersSection');
                const borrowersBody    = document.getElementById('borrowersBody');
                const noBorrowersMsg   = document.getElementById('noBorrowersMsg');

                if (data.borrowers && data.borrowers.length > 0) {
                    borrowersBody.innerHTML = '';
                    data.borrowers.forEach((borrower) => {
                        const row = document.createElement('tr');
                        const statusBadge = borrower.status === 'TERLAMBAT'
                            ? '<span class="badge bg-danger">TERLAMBAT</span>'
                            : '<span class="badge bg-warning">AKTIF</span>';
                        row.innerHTML = `
                            <td><strong>${borrower.pic_name || '-'}</strong></td>
                            <td>${borrower.pic_class || '-'}</td>
                            <td>${borrower.borrow_date || '-'}</td>
                            <td>${borrower.due_date || '-'}</td>
                            <td class="text-center">${statusBadge}</td>
                        `;
                        borrowersBody.appendChild(row);
                    });
                    borrowersSection.style.display = 'block';
                    noBorrowersMsg.style.display = 'none';
                } else {
                    borrowersBody.innerHTML = '';
                    borrowersSection.style.display = 'block';
                    noBorrowersMsg.style.display = 'block';
                }
            })
            .catch(err => {
                loadingSpinner.style.display = 'none';
                console.error('Error fetching borrowers:', err);
                showToast('Terjadi kesalahan memuat data peminjam', 'error');
            });

        new bootstrap.Modal(document.getElementById('exampleModal')).show();
    }

    // ===== TABLE INTERACTIONS =====

    document.querySelectorAll('.btn-detail-buku').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const book = JSON.parse(btn.closest('tr').getAttribute('data-book'));
            openDetailModal(book);
        });
    });

    document.querySelectorAll('#bukuTableBody tr').forEach(function(row) {
        row.addEventListener('dblclick', function() {
            openDetailModal(JSON.parse(row.getAttribute('data-book')));
        });
    });

    // ===== SEARCH & PAGINATION =====

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
        let rows = Array.from(tableBody.querySelectorAll('tr'));

        const filtered = rows.filter(row => {
            const code  = row.children[1]?.textContent.toLowerCase() || '';
            const title = row.children[2]?.textContent.toLowerCase() || '';
            return code.includes(query) || title.includes(query);
        });

        if (sortState.column) {
            sortRows(filtered, sortState.column, sortState.direction);
        }

        rows.forEach(row => row.style.display = 'none');

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filtered.slice(start, end).forEach(row => row.style.display = '');

        renderPagination(filtered.length);
    }

    searchInput.addEventListener('input', () => { currentPage = 1; filterTable(); });
    document.getElementById('cariBuku').addEventListener('click', () => { currentPage = 1; filterTable(); });

    filterTable();
});
</script>

<?php if (session()->getFlashdata("message")): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("<?= esc(session()->getFlashdata("message"), "js") ?>");
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>
