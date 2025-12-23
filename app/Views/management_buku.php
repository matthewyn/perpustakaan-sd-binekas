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

    /* ==================== CAMERA CONTAINER ==================== */
    .camera-container {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
    }

    /* ==================== NAVIGATION TABS ==================== */
    .nav-tabs .nav-link {
        color: #6c757d;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        font-weight: 500;
    }

    /* ==================== RFID MODAL ==================== */
    #rfid_uid_confirm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 252, 0.25);
    }

    .progress-step {
        padding: 0.5rem;
        margin: 0.25rem 0;
        border-left: 3px solid #0d6efd;
        background: #f8f9fa;
    }

    .progress-step.active {
        background: #e7f1ff;
        font-weight: 500;
    }

    .progress-step.completed {
        border-left-color: #198754;
        background: #d1e7dd;
    }

    .progress-step.failed {
        border-left-color: #dc3545;
        background: #f8d7da;
    }

    .cloudinary-link {
        word-break: break-all;
        background: #f8f9fa;
        padding: 0.5rem;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.875rem;
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
        
        <!-- Kode ONLY (NO UID HERE) -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="code" class="form-label required">Kode</label>
                <div class="input-group">
                    <input type="text" name="code" class="form-control" id="code" required>
                    <button class="btn btn-outline-secondary" type="button" id="generateKodeBtn" title="Generate Kode Baru">
                        <i class="bi bi-arrow-clockwise"></i> Auto
                    </button>
                </div>
                <small class="form-text text-muted">Auto-generate atau ketik manual</small>
            </div>
        </div>

        <!-- Judul & Penulis -->
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

        <!-- Illustrator & Publisher -->
        <div class="row mb-3">
            <div class="col">
                <label for="illustrator" class="form-label required">Illustrator</label>
                <input type="text" name="illustrator" class="form-control" id="illustrator">
            </div>
            <div class="col">
                <label for="publisher" class="form-label required">Penerbit</label>
                <input type="text" name="publisher" class="form-control" id="publisher">
            </div>
        </div>

        <!-- Series & Genre -->
        <div class="row mb-3">
            <div class="col">
                <label for="series" class="form-label required">Series</label>
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
                    <option value="Buku Math">Buku Math</option>
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

        <!-- ISBN, Year, Quantity -->
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
                <label for="year" class="form-label required">Tahun</label>
                <input type="number" name="year" class="form-control" id="year">
            </div>
            <div class="col">
                <label for="quantity" class="form-label required">Quantity</label>
                <input type="number" name="quantity" class="form-control" id="quantity" min="1" value="1" required>
            </div>
        </div>

        <!-- Image with Tabs -->
        <div class="mb-3">
            <label class="form-label required">Gambar</label>
            
            <ul class="nav nav-tabs nav-tabs-mobile mb-2" id="imageInputTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="url-tab-mgmt" data-bs-toggle="tab" data-bs-target="#url-panel-mgmt" type="button" role="tab">
                        <i class="bi bi-link-45deg"></i> URL
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="camera-tab-mgmt" data-bs-toggle="tab" data-bs-target="#camera-panel-mgmt" type="button" role="tab">
                        <i class="bi bi-camera"></i> Camera
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upload-tab-mgmt" data-bs-toggle="tab" data-bs-target="#upload-panel-mgmt" type="button" role="tab">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="imageInputTabContent">
                <div class="tab-pane fade show active" id="url-panel-mgmt" role="tabpanel">
                    <div class="input-group">
                        <input type="text" class="form-control" id="imageLink" placeholder="Paste image URL here">
                        <button class="btn btn-primary" type="button" id="analyzeBtnMgmt">
                            <i class="bi bi-search"></i> Analyze
                        </button>
                    </div>
                </div>

                <div class="tab-pane fade" id="camera-panel-mgmt" role="tabpanel">
                    <div class="camera-container">
                        <video id="cameraPreviewMgmt" autoplay playsinline style="width: 100%; max-height: 300px; display: none; border-radius: 8px; background: #000;"></video>
                        <canvas id="cameraCanvasMgmt" style="display: none;"></canvas>
                        
                        <div class="d-grid gap-2 mb-2">
                            <button class="btn btn-outline-primary" type="button" id="startCameraBtnMgmt">
                                <i class="bi bi-camera-video"></i> Start Camera
                            </button>
                            <button class="btn btn-success" type="button" id="captureBtnMgmt" style="display: none;">
                                <i class="bi bi-camera"></i> Capture Photo
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="stopCameraBtnMgmt" style="display: none;">
                                <i class="bi bi-stop-circle"></i> Stop Camera
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="upload-panel-mgmt" role="tabpanel">
                    <div class="input-group">
                        <input type="file" class="form-control" id="fileUploadMgmt" accept="image/*">
                        <button class="btn btn-primary" type="button" id="analyzeUploadBtnMgmt">
                            <i class="bi bi-search"></i> Analyze
                        </button>
                    </div>
                    <small class="form-text text-muted">Accepted formats: JPG, PNG, WEBP</small>
                </div>
            </div>
        </div>

        <!-- Image Preview -->
        <div class="mb-3">
            <img id="previewImageMgmt" src="" alt="Preview" style="max-width: 100%; max-height: 300px; display:none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <small class="text-muted" id="currentImageText"></small>
        </div>

        <!-- Synopsis -->
        <div class="mb-3">
            <label for="synopsis" class="form-label required">Sinopsis</label>
            <textarea name="synopsis" class="form-control" id="synopsis" rows="3"></textarea>
        </div>

        <!-- Available -->
        <div class="mb-3">
            <div class="d-flex gap-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="isOneDayBook" name="isOneDayBook">
                    <label class="form-check-label" for="isOneDayBook">Buku 1 Hari</label>
                </div>
                <div class="form-check form-switch" id="availableSection">
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

<!-- RFID Confirmation Modal -->
<div class="modal fade" id="rfidModal" tabindex="-1" aria-labelledby="rfidModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="rfidModalLabel">
          <i class="bi bi-credit-card-2-front"></i> Scan RFID Card
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="rfidModalClose"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <i class="bi bi-upc-scan" style="font-size: 3rem; color: #0d6efd;"></i>
          <p class="mt-2 text-muted" id="rfidInstruction">Silakan scan kartu RFID sekarang</p>
        </div>
        
        <!-- UID Input -->
        <div class="mb-3">
          <label for="rfid_uid_confirm" class="form-label fw-bold">RFID UID <span class="text-danger">*</span></label>
          <input 
            type="text" 
            class="form-control form-control-lg text-center" 
            id="rfid_uid_confirm" 
            placeholder="Scan atau ketik RFID UID" 
            autocomplete="off"
            style="letter-spacing: 2px; font-family: monospace;">
        </div>

        <!-- Book Summary -->
        <div class="card bg-light" id="bookSummaryCard">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">Ringkasan Buku:</h6>
            <p class="mb-1"><strong>Judul:</strong> <span id="bookSummaryTitle">-</span></p>
            <p class="mb-1"><strong>Pengarang:</strong> <span id="bookSummaryAuthor">-</span></p>
            <p class="mb-0"><strong>Kode:</strong> <span id="bookSummaryKode">-</span></p>
          </div>
        </div>

        <!-- Progress Steps -->
        <div id="progressSteps" class="mt-3" style="display: none;">
          <h6 class="mb-2"><i class="bi bi-hourglass-split"></i> Progress:</h6>
          <div class="progress-step" id="step1">
            <i class="bi bi-circle"></i> <span>Validating RFID...</span>
          </div>
          <div class="progress-step" id="step2">
            <i class="bi bi-circle"></i> <span>Uploading image to Cloudinary...</span>
          </div>
          <div class="progress-step" id="step3">
            <i class="bi bi-circle"></i> <span>Saving to database...</span>
          </div>
        </div>

        <!-- Cloudinary Result -->
        <div id="cloudinaryResult" class="mt-3" style="display: none;">
          <div class="alert alert-success mb-0">
            <strong><i class="bi bi-check-circle"></i> Cloudinary Upload Successful!</strong>
            <div class="mt-2">
              <small class="text-muted">URL:</small>
              <div class="cloudinary-link" id="cloudinaryUrl"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="rfidCancelBtn">
          <i class="bi bi-x-circle"></i> Batal
        </button>
        <button type="button" class="btn btn-primary" id="confirmRfidBtn" disabled>
          <i class="bi bi-check-circle"></i> Konfirmasi & Simpan
        </button>
      </div>
    </div>
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
    
    // =============== SORTING STATE ===============
    let sortState = {
        column: null,
        direction: 'asc' // 'asc' or 'desc'
    };

    // =============== SETUP COLUMN HEADERS FOR SORTING ===============
    const columnHeaders = {
        'kode': 1,
        'judul': 2,
        'penulis': 3,
        'penerbit': 4,
        'tahun': 5
    };

    Object.entries(columnHeaders).forEach(([colName, colIndex]) => {
        const th = document.querySelector(`thead tr th:nth-child(${colIndex + 1})`);
        if (th && colIndex > 0) { // Skip # column
            th.style.cursor = 'pointer';
            th.style.userSelect = 'none';
            th.innerHTML += ' <i class="bi bi-arrow-down-up" style="font-size: 0.8rem; opacity: 0.5;"></i>';
            
            th.addEventListener('click', function() {
                const icon = th.querySelector('i');
                
                // Reset other columns
                document.querySelectorAll('thead tr th').forEach(header => {
                    const i = header.querySelector('i');
                    if (i && header !== th) {
                        i.className = 'bi bi-arrow-down-up';
                        i.style.opacity = '0.5';
                    }
                });
                
                // Toggle sort direction
                if (sortState.column === colName) {
                    sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    sortState.column = colName;
                    sortState.direction = 'asc';
                }
                
                // Update icon
                if (sortState.column === colName) {
                    icon.className = sortState.direction === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
                    icon.style.opacity = '1';
                }
                
                currentPage = 1;
                filterTable();
            });
        }
    });

    // =============== SORTING FUNCTION ===============
    function sortRows(rows, column, direction) {
        const columnIndex = columnHeaders[column];
        
        return rows.sort((rowA, rowB) => {
            let valueA = rowA.children[columnIndex]?.textContent.trim() || '';
            let valueB = rowB.children[columnIndex]?.textContent.trim() || '';
            
            // Try to convert to number if all digits
            if (!isNaN(valueA) && valueA !== '') valueA = parseFloat(valueA);
            if (!isNaN(valueB) && valueB !== '') valueB = parseFloat(valueB);
            
            // Convert to lowercase for string comparison
            if (typeof valueA === 'string') valueA = valueA.toLowerCase();
            if (typeof valueB === 'string') valueB = valueB.toLowerCase();
            
            if (direction === 'asc') {
                return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
            } else {
                return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
            }
        });
    }

    // =============== CAMERA & IMAGE HANDLING ===============
    let cameraStreamMgmt = null;
    let capturedImageDataMgmt = null;

    const cameraPreviewMgmt = document.getElementById('cameraPreviewMgmt');
    const cameraCanvasMgmt = document.getElementById('cameraCanvasMgmt');
    const startCameraBtnMgmt = document.getElementById('startCameraBtnMgmt');
    const captureBtnMgmt = document.getElementById('captureBtnMgmt');
    const stopCameraBtnMgmt = document.getElementById('stopCameraBtnMgmt');
    const previewImageMgmt = document.getElementById('previewImageMgmt');

    startCameraBtnMgmt.addEventListener('click', async () => {
        try {
            cameraStreamMgmt = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                } 
            });
            
            cameraPreviewMgmt.srcObject = cameraStreamMgmt;
            cameraPreviewMgmt.style.display = 'block';
            startCameraBtnMgmt.style.display = 'none';
            captureBtnMgmt.style.display = 'block';
            stopCameraBtnMgmt.style.display = 'block';
            previewImageMgmt.style.display = 'none';
        } catch (err) {
            console.error('Camera access error:', err);
            alert('Unable to access camera. Please check permissions.');
        }
    });

    captureBtnMgmt.addEventListener('click', async () => {
        const context = cameraCanvasMgmt.getContext('2d');
        
        const maxWidth = 1024;
        const scale = Math.min(1, maxWidth / cameraPreviewMgmt.videoWidth);
        
        cameraCanvasMgmt.width = cameraPreviewMgmt.videoWidth * scale;
        cameraCanvasMgmt.height = cameraPreviewMgmt.videoHeight * scale;
        
        context.drawImage(cameraPreviewMgmt, 0, 0, cameraCanvasMgmt.width, cameraCanvasMgmt.height);
        
        capturedImageDataMgmt = cameraCanvasMgmt.toDataURL('image/jpeg', 0.7);
        
        previewImageMgmt.src = capturedImageDataMgmt;
        previewImageMgmt.style.display = 'block';
        
        stopCameraMgmt();
        
        await analyzeImageMgmt(capturedImageDataMgmt, 'base64');
    });

    stopCameraBtnMgmt.addEventListener('click', stopCameraMgmt);

    function stopCameraMgmt() {
        if (cameraStreamMgmt) {
            cameraStreamMgmt.getTracks().forEach(track => track.stop());
            cameraStreamMgmt = null;
        }
        cameraPreviewMgmt.style.display = 'none';
        cameraPreviewMgmt.srcObject = null;
        startCameraBtnMgmt.style.display = 'block';
        captureBtnMgmt.style.display = 'none';
        stopCameraBtnMgmt.style.display = 'none';
    }

    // =============== URL ANALYZE ===============
    document.getElementById('analyzeBtnMgmt').addEventListener('click', async () => {
        const imageUrl = document.getElementById('imageLink').value.trim();
        
        if (!imageUrl) {
            alert('Masukkan link gambar terlebih dahulu.');
            return;
        }

        previewImageMgmt.src = imageUrl;
        previewImageMgmt.style.display = 'block';
        
        await analyzeImageMgmt(imageUrl, 'url');
    });

    // =============== FILE UPLOAD ANALYZE ===============
    document.getElementById('analyzeUploadBtnMgmt').addEventListener('click', async () => {
        const fileInput = document.getElementById('fileUploadMgmt');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('Pilih file gambar terlebih dahulu.');
            return;
        }

        const reader = new FileReader();
        reader.onload = async (e) => {
            const img = new Image();
            img.onload = async () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                const maxWidth = 1024;
                const scale = Math.min(1, maxWidth / img.width);
                
                canvas.width = img.width * scale;
                canvas.height = img.height * scale;
                
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                const compressedData = canvas.toDataURL('image/jpeg', 0.7);
                
                previewImageMgmt.src = compressedData;
                previewImageMgmt.style.display = 'block';
                
                capturedImageDataMgmt = compressedData;
                
                await analyzeImageMgmt(compressedData, 'base64');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // =============== UNIFIED ANALYZE FUNCTION ===============
    async function analyzeImageMgmt(imageData, type) {
        const analyzeBtn = document.getElementById('analyzeBtnMgmt');
        const analyzeUploadBtn = document.getElementById('analyzeUploadBtnMgmt');
        const originalText = analyzeBtn.innerHTML;
        
        analyzeBtn.disabled = true;
        analyzeUploadBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

        console.log('🔍 Starting analysis...');
        console.log('📋 Type:', type);
        console.log('📊 Data length:', imageData ? imageData.length : 0);

        try {
            let response;
            const apiUrl = '<?= base_url("api/analyze-image") ?>';
            
            if (type === 'url') {
                const fullUrl = `${apiUrl}?image_url=${encodeURIComponent(imageData)}`;
                console.log('🌐 Calling API (GET):', fullUrl.substring(0, 100) + '...');
                response = await fetch(fullUrl, {
                    method: 'GET'
                });
            } else {
                console.log('🌐 Calling API (POST):', apiUrl);
                console.log('📤 Sending base64 data...');
                
                let base64String = imageData;
                if (imageData.startsWith('data:')) {
                    base64String = imageData.split(',')[1];
                }
                
                response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'base64',
                        image_data: base64String
                    })
                });
            }

            console.log('📡 Response status:', response.status);
            console.log('📡 Response ok:', response.ok);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Server error:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 Response data:', data);

            if (data.error) {
                console.error('❌ API Error:', data.error);
                alert('Gagal menganalisis gambar: ' + data.error);
                return data;
            }

            if (data.title === 'BUKAN BUKU' || !data.title) {
                alert('⚠️ Gambar bukan sampul buku atau tidak dapat dianalisis');
                return data;
            }

            console.log('✅ Filling form fields...');

            // Fill form fields
            const fields = {
                'title': data.title,
                'author': data.author,
                'illustrator': data.illustrator,
                'publisher': data.publisher,
                'series': data.series,
                'isbn': data.isbn,
                'year': data.year,
                'synopsis': data.synopsis
            };

            for (const [fieldName, value] of Object.entries(fields)) {
                const element = bukuForm.querySelector(`[name="${fieldName}"]`);
                if (element && value && value !== 'NOT FOUND') {
                    element.value = value;
                    console.log(`  ✔ Set ${fieldName}: ${value.substring(0, 50)}...`);
                }
            }

            // Auto-fill genre dropdown
            const genreSelect = bukuForm.querySelector('[name="genre"]');
            if (genreSelect && (data.category || data.genre)) {
                const genreValue = (data.category || data.genre).toLowerCase().trim();
                console.log(`  🔎 Looking for genre: ${genreValue}`);
                
                let found = false;
                for (const option of genreSelect.options) {
                    if (option.value.toLowerCase() === genreValue) {
                        option.selected = true;
                        found = true;
                        console.log(`  ✅ Genre exact match: ${option.value}`);
                        break;
                    }
                }
                
                if (!found) {
                    for (const option of genreSelect.options) {
                        const optionLower = option.value.toLowerCase();
                        if (optionLower.includes(genreValue) || genreValue.includes(optionLower)) {
                            option.selected = true;
                            found = true;
                            console.log(`  ✅ Genre partial match: ${option.value}`);
                            break;
                        }
                    }
                }
                
                if (!found) {
                    console.log(`  ⚠️ Genre "${genreValue}" not found in options`);
                }
            }

            alert('✅ Analisis berhasil! Field telah diisi otomatis.\n\n💡 Gambar akan diupload ke Cloudinary setelah RFID dikonfirmasi.');
            console.log('✅ Analysis complete!');
            
            return data;

        } catch (err) {
            console.error('❌ Error details:', err);
            console.error('❌ Error stack:', err.stack);
            alert('Terjadi kesalahan saat menganalisis gambar: ' + err.message);
            throw err;
        } finally {
            analyzeBtn.disabled = false;
            analyzeUploadBtn.disabled = false;
            analyzeBtn.innerHTML = originalText;
            console.log('🏁 Analysis function finished');
        }
    }

    // =============== CLOUDINARY CONFIGURATION ===============
    const CLOUDINARY_CONFIG = {
        cloud_name: 'dqx1ofl8j',
        upload_preset: 'ml_default'
    };

    async function uploadToCloudinary(imageData) {
        try {
            const formData = new FormData();
            
            let fileToUpload = imageData;
            if (imageData.startsWith('data:')) {
                const response = await fetch(imageData);
                const blob = await response.blob();
                fileToUpload = blob;
            }

            const timestamp = Date.now();
            const randomStr = Math.random().toString(36).substring(7);
            const filename = `book_${timestamp}_${randomStr}`;

            formData.append('file', fileToUpload);
            formData.append('upload_preset', CLOUDINARY_CONFIG.upload_preset);
            formData.append('public_id', filename);
            formData.append('folder', 'books');

            const uploadResponse = await fetch(
                `https://api.cloudinary.com/v1_1/${CLOUDINARY_CONFIG.cloud_name}/image/upload`,
                {
                    method: 'POST',
                    body: formData
                }
            );

            if (!uploadResponse.ok) {
                const errorData = await uploadResponse.json();
                throw new Error(errorData.error?.message || 'Upload failed');
            }

            const data = await uploadResponse.json();
            return data.secure_url;

        } catch (error) {
            console.error('❌ Cloudinary upload error:', error);
            throw error;
        }
    }

    // =============== PROGRESS STEP HELPERS ===============
    function updateStep(stepId, status) {
        const step = document.getElementById(stepId);
        if (!step) return;

        step.classList.remove('active', 'completed', 'failed');
        
        const icon = step.querySelector('i');
        if (status === 'active') {
            step.classList.add('active');
            icon.className = 'bi bi-hourglass-split';
        } else if (status === 'completed') {
            step.classList.add('completed');
            icon.className = 'bi bi-check-circle-fill';
        } else if (status === 'failed') {
            step.classList.add('failed');
            icon.className = 'bi bi-x-circle-fill';
        }
    }

    function resetSteps() {
        ['step1', 'step2', 'step3'].forEach(stepId => {
            const step = document.getElementById(stepId);
            if (step) {
                step.classList.remove('active', 'completed', 'failed');
                const icon = step.querySelector('i');
                icon.className = 'bi bi-circle';
            }
        });
    }

    // =============== RFID MODAL FUNCTIONALITY ===============
    const rfidModal = new bootstrap.Modal(document.getElementById('rfidModal'));
    const rfidInput = document.getElementById('rfid_uid_confirm');
    const confirmRfidBtn = document.getElementById('confirmRfidBtn');
    const progressSteps = document.getElementById('progressSteps');
    const cloudinaryResult = document.getElementById('cloudinaryResult');
    const cloudinaryUrl = document.getElementById('cloudinaryUrl');
    let pendingBookData = null;

    rfidInput.addEventListener('input', function() {
        const hasValue = this.value.trim().length > 0;
        confirmRfidBtn.disabled = !hasValue;
    });

    document.getElementById('rfidModal').addEventListener('shown.bs.modal', function () {
        rfidInput.focus();
    });

    document.getElementById('rfidModal').addEventListener('hidden.bs.modal', function () {
        rfidInput.value = '';
        confirmRfidBtn.disabled = true;
        progressSteps.style.display = 'none';
        cloudinaryResult.style.display = 'none';
        resetSteps();
        pendingBookData = null;
    });

    rfidInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !confirmRfidBtn.disabled) {
            confirmRfidBtn.click();
        }
    });

    confirmRfidBtn.addEventListener('click', async function() {
        if (!pendingBookData) {
            alert('❌ Data buku tidak ditemukan');
            return;
        }

        const rfidValue = rfidInput.value.trim();
        if (!rfidValue) {
            alert('⚠️ RFID UID harus diisi!');
            rfidInput.focus();
            return;
        }

        rfidInput.disabled = true;
        confirmRfidBtn.disabled = true;
        document.getElementById('rfidCancelBtn').disabled = true;
        document.getElementById('rfidModalClose').disabled = true;

        progressSteps.style.display = 'block';
        cloudinaryResult.style.display = 'none';

        try {
            updateStep('step1', 'active');
            await new Promise(resolve => setTimeout(resolve, 500));
            pendingBookData.uid = rfidValue;
            updateStep('step1', 'completed');

            let cloudinaryImageUrl = null;
            if (capturedImageDataMgmt || pendingBookData.image) {
                updateStep('step2', 'active');
                
                try {
                    const imageToUpload = capturedImageDataMgmt || pendingBookData.image;
                    cloudinaryImageUrl = await uploadToCloudinary(imageToUpload);
                    
                    cloudinaryUrl.textContent = cloudinaryImageUrl;
                    cloudinaryResult.style.display = 'block';
                    
                    pendingBookData.image = cloudinaryImageUrl;
                    
                    updateStep('step2', 'completed');
                } catch (error) {
                    updateStep('step2', 'failed');
                    alert('❌ Upload ke Cloudinary gagal: ' + error.message);
                    throw error;
                }
            } else {
                updateStep('step2', 'completed');
            }

            updateStep('step3', 'active');
            
            const response = await fetch("<?= base_url('management-buku/add') ?>", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(pendingBookData)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} - ${errorText}`);
            }

            const data = await response.json();

            if (data.success) {
                updateStep('step3', 'completed');
                
                await new Promise(resolve => setTimeout(resolve, 500));
                
                rfidModal.hide();
                addModal.hide();
                
                alert('✅ Buku berhasil ditambahkan!');
                location.reload();
            } else {
                updateStep('step3', 'failed');
                alert('❌ Gagal menyimpan ke database: ' + data.message);
            }

        } catch (error) {
            console.error('❌ Process error:', error);
        } finally {
            rfidInput.disabled = false;
            confirmRfidBtn.disabled = false;
            document.getElementById('rfidCancelBtn').disabled = false;
            document.getElementById('rfidModalClose').disabled = false;
        }
    });

    // =============== MODAL HANDLERS ===============
    document.querySelectorAll('.btn-add-uid').forEach(btn => {
        btn.addEventListener('click', () => {
            const container = btn.closest('.row').querySelector('.uid-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'uid[]';
            input.className = 'form-control mb-1';
            input.placeholder = 'Masukkan UID';
            container.appendChild(input);
        });
    });

    // Generate Kode Button
    document.getElementById('generateKodeBtn').addEventListener('click', function() {
        const kodeInput = document.getElementById('code');
        kodeInput.value = 'Loading...';
        
        $.ajax({
            url: "<?= base_url('books/next-kode') ?>",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    kodeInput.value = response.kode_sekolah;
                } else {
                    kodeInput.value = 'Error';
                }
            },
            error: function() {
                kodeInput.value = 'Error';
            }
        });
    });

    document.getElementById('btnTambahBuku').addEventListener('click', function() {
        resetForm();
        document.getElementById('modalBukuTitle').textContent = 'Tambah Buku';
        bukuForm.action = "<?= base_url('management-buku/add') ?>";
        document.getElementById('code').value = '';
        
        setTimeout(() => {
            document.getElementById('generateKodeBtn').click();
        }, 300);
        
        addModal.show();
    });

    function resetForm() {
        bukuForm.reset();
        document.getElementById('editId').value = '';
        document.getElementById('currentImageText').textContent = '';
        
        document.getElementById('isOneDayBook').checked = false;
        document.getElementById('available').checked = false;
        previewImageMgmt.style.display = 'none';
        capturedImageDataMgmt = null;
    }

    document.querySelectorAll('.btn-edit-buku').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const tr = btn.closest('tr');
            const book = JSON.parse(tr.getAttribute('data-book'));
            
            resetForm();
            document.getElementById('modalBukuTitle').textContent = 'Edit Buku';
            bukuForm.action = "<?= base_url('management-buku/edit/') ?>" + book.id;
            
            // Fill all form fields
            bukuForm.querySelector('[name="code"]').value = book.code || '';
            bukuForm.querySelector('[name="title"]').value = book.title || '';
            bukuForm.querySelector('[name="author"]').value = book.author || '';
            bukuForm.querySelector('[name="publisher"]').value = book.publisher || '';
            bukuForm.querySelector('[name="genre"]').value = book.genre || '';
            bukuForm.querySelector('[name="isbn"]').value = book.isbn || '';
            bukuForm.querySelector('[name="ddcNumber"]').value = book.ddcNumber || book.ddc || '';
            bukuForm.querySelector('[name="year"]').value = book.year || '';
            bukuForm.querySelector('[name="illustrator"]').value = book.illustrator || '';
            bukuForm.querySelector('[name="series"]').value = book.series || '';
            bukuForm.querySelector('[name="synopsis"]').value = book.synopsis || '';
            bukuForm.querySelector('[name="quantity"]').value = book.quantity || 1;
            
            // Add DDC field (if exists in book data)
            const ddcField = bukuForm.querySelector('[name="ddcNumber"]');
            if (ddcField) {
                ddcField.value = book.ddcNumber || book.ddc || '';
            }
            
            document.getElementById('isOneDayBook').checked = book.is_one_day_book || false;
            document.getElementById('available').checked = book.available !== false;
            
            if (book.image) {
                document.getElementById('currentImageText').textContent = 'Gambar saat ini: ' + book.image;
            }
            
            document.getElementById('editId').value = book.id;
            
            addModal.show();
        });
    });

    document.getElementById('submitBukuBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        const bookData = {
            code: bukuForm.querySelector('[name="code"]').value,
            uid: Array.from(bukuForm.querySelectorAll('[name="uid[]"]')).map(el => el.value).filter(v => v),
            title: bukuForm.querySelector('[name="title"]').value,
            author: bukuForm.querySelector('[name="author"]').value,
            illustrator: bukuForm.querySelector('[name="illustrator"]').value,
            publisher: bukuForm.querySelector('[name="publisher"]').value,
            series: bukuForm.querySelector('[name="series"]').value,
            genre: bukuForm.querySelector('[name="genre"]').value,
            isbn: bukuForm.querySelector('[name="isbn"]').value,
            year: bukuForm.querySelector('[name="year"]').value,
            quantity: bukuForm.querySelector('[name="quantity"]').value,
            synopsis: bukuForm.querySelector('[name="synopsis"]').value,
            image: capturedImageDataMgmt || bukuForm.querySelector('[name="image"]')?.value || '',
            is_one_day_book: document.getElementById('isOneDayBook').checked,
            available: document.getElementById('available').checked
        };

        if (!bookData.code) {
            alert('⚠️ Kode harus diisi!');
            return;
        }

        if (!bookData.title) {
            alert('⚠️ Judul harus diisi!');
            return;
        }

        if (!bookData.author) {
            alert('⚠️ Pengarang harus diisi!');
            return;
        }

        if (!bookData.genre) {
            alert('⚠️ Genre harus dipilih!');
            return;
        }

        pendingBookData = bookData;

        document.getElementById('bookSummaryTitle').textContent = bookData.title;
        document.getElementById('bookSummaryAuthor').textContent = bookData.author;
        document.getElementById('bookSummaryKode').textContent = bookData.code;

        addModal.hide();
        rfidModal.show();
    });

    // =============== PAGINATION & TABLE FUNCTIONS ===============
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
        
        // Apply filter
        const filtered = rows.filter(row => {
            const code = row.children[1]?.textContent.toLowerCase() || '';
            const title = row.children[2]?.textContent.toLowerCase() || '';
            return code.includes(query) || title.includes(query);
        });

        // Apply sorting
        if (sortState.column) {
            sortRows(filtered, sortState.column, sortState.direction);
        }

        // Hide all rows
        rows.forEach(row => row.style.display = 'none');

        // Show paginated rows
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