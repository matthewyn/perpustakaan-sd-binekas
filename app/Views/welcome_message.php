<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<style>
  /* ==================== AUTOCOMPLETE ==================== */
  .ui-autocomplete {
    z-index: 2000 !important;
  }

  /* ==================== CAMERA CONTAINER ==================== */
  .camera-container {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
  }

  #cameraPreview {
    border: 2px solid #dee2e6;
    margin-bottom: 1rem;
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

  #rfidModal .modal-body {
    padding: 2rem;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }

  #rfidModal .bi-upc-scan {
    animation: pulse 2s infinite;
  }

  /* ==================== UPLOAD PROGRESS ==================== */
  .upload-progress {
    margin-top: 1rem;
  }

  .cloudinary-link {
    word-break: break-all;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.875rem;
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

  /* ==================== BOOK IMAGES - SIDEBAR (KOLEKSI TERBARU) ==================== */
  .book-card-img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
    background: #f8f9fa;
  }

  .latest-book-img {
    width: 100%;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
    background: #f8f9fa;
  }

  /* ==================== BOOK IMAGES - MAIN LIST (TENGAH) ==================== */
  .book-thumbnail-img {
    width: 100%;
    object-fit: cover;
    border-radius: 8px;
    background: #f8f9fa;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #dee2e6;
  }

  .book-thumbnail-img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  }

  .book-thumbnail-img.img-error {
    object-fit: contain;
    padding: 20px;
    opacity: 0.6;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  }

  /* ==================== BOOK CARDS - MAIN LIST ==================== */
  #booksContainer .card {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
    height: 100%;
  }

  #booksContainer .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #0d6efd;
  }

  #booksContainer .card-body {
    background: #ffffff;
    transition: background 0.2s ease;
  }

  #booksContainer .card:hover .card-body {
    background: #f8f9fa;
  }

  /* ==================== ROW SPACING ==================== */
  #booksContainer .row.g-3 {
    margin-left: 0;
    margin-right: 0;
  }

  #booksContainer .col-6 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  /* ==================== FALLBACK IMAGE HANDLING ==================== */
  img[src=""], 
  img:not([src]),
  img[src*="placehold"],
  img[src*="placeholder"] {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6 !important;
    display: inline-block;
  }

  img[onerror] {
    min-height: 100px;
  }

  /* ==================== TEXT TRUNCATION ==================== */
  .truncate {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5;
  }

  /* ==================== BOOK INFO STYLING ==================== */
  #booksContainer h2 {
    margin-bottom: 0.5rem;
    line-height: 1.3;
    font-weight: 600;
  }

  /* ==================== BUTTON STYLING ==================== */
  #booksContainer .btn-secondary {
    transition: all 0.2s ease;
  }

  #booksContainer .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }

  /* ==================== RESPONSIVE ADJUSTMENTS ==================== */
  @media (max-width: 768px) {
    .book-thumbnail-img {
      height: 150px;
    }
    
    #booksContainer .col-6 {
      flex: 0 0 100%;
      max-width: 100%;
    }
    
    #booksContainer h2 {
      font-size: 1rem;
    }
  }

  @media (max-width: 576px) {
    .book-thumbnail-img {
      height: 120px;
    }
    
    #booksContainer .row.g-3 > .col-4 {
      flex: 0 0 40%;
      max-width: 40%;
    }
    
    #booksContainer .row.g-3 > .col-8 {
      flex: 0 0 60%;
      max-width: 60%;
    }
  }

  /* ==================== IMAGE LOADING STATE ==================== */
  img[loading="lazy"] {
    transition: opacity 0.3s ease;
  }

  /* ==================== SIDEBAR LATEST BOOKS ==================== */
  .card.border-light.mb-3.shadow-sm {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .card.border-light.mb-3.shadow-sm:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15) !important;
  }

  /* ==================== FORM REQUIRED LABELS ==================== */
  .form-label.required::after {
    content: " *";
    color: #dc3545;
  }

  /* ==================== PREVIEW IMAGE ==================== */
  #previewImage {
    border: 2px solid #dee2e6;
    transition: border-color 0.3s ease;
  }

  #previewImage:hover { 
    border-color: #0d6efd;
  }

  .latest-book-card {
    width: 100%;
  }

  .modal-dialog {
    --bs-modal-width: 800px;
  }

  @media (min-width: 992px) { 
    #logoImage {
      width: 100px;
    }

    #childrenImage {
      width: 170px;
      top: -120px;
    }

    #booksContainer h2, .modal-title {
      font-size: 1.25rem;
    }

    #booksContainer .text-muted {
      font-size: 0.9rem;
    }

    #booksContainer .badge {
      font-size: 0.85rem;
      padding: 0.35rem 0.65rem;
      font-weight: 500;
    }

    #booksContainer .btn-secondary {
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
    }

    .book-thumbnail-img {
      height: 200px;
    }

    #newBookTitle {
      font-size: 1.45rem;
    }

    .latest-book-img {
      height: 200px;
    }
  }
</style>

<!-- Main -->
<div class="d-flex justify-content-center align-items-center my-5 my-lg-4">
  <img src="<?= base_url('/pattern.png') ?>" alt="Logo" class="d-inline-block align-text-top me-2 img-mobile-lg" id="logoImage"/>
  <h1 class="h-mobile-xl" id="logoTitle">Katalog</h1>
</div>

<div class="card relative" style="border-style: dashed;">
  <img src="<?= base_url('/children.png') ?>" alt="Children" class="position-absolute end-0 z-n1 img-mobile-xl top-mobile-xl" id="childrenImage"/>
  <div class="card-body">
    <div class="row">
      <div class="col-lg-9">
        <div class="row mb-4 mb-lg-3">
          <nav aria-label="Book pagination" class="col-4">
            <ul class="pagination pagination-mobile mb-2" id="bookPagination" style="flex-wrap: wrap;">
              <!-- Generated dynamically by JavaScript -->
            </ul>
            <?php if (session('role') === 'admin'): ?>
              <div class="col-auto">
                <button type="button" id="tambah" class="btn btn-primary btn-mobile-md" data-bs-toggle="modal" data-bs-target="#exampleModal">
                  <i class="bi bi-plus"></i> Tambah Buku
                </button>
              </div>
            <?php endif; ?>
          </nav>
          <div class="col">
            <form id="searchForm" class="d-flex mb-2" role="search">
              <input class="form-control me-2 mobile-search" type="search" name="search" placeholder="Ketik Kata Kunci" aria-label="Search" value="<?= esc($search) ?>"/>
              <?php foreach ($selectedGenres as $selected): ?>
                <input type="hidden" name="genres[]" value="<?= esc($selected) ?>">
              <?php endforeach; ?>
              <button class="btn btn-outline-success btn-mobile-md" type="submit" id="btnSearch"><i class="bi bi-search"></i></button>
            </form>
            <form method="get" id="selectpickerForm" style="width: 50%; margin-left: auto;">
              <?php if ($search): ?>
                <input type="hidden" name="search" value="<?= esc($search) ?>">
              <?php endif; ?>
              <select id="genreSelectpicker" class="selectpicker form-control" name="genres[]" multiple data-live-search="true" data-actions-box="true">
                <?php foreach ($genres as $genre): ?>
                  <option value="<?= esc($genre) ?>" <?= in_array($genre, $selectedGenres) ? 'selected' : '' ?>>
                    <?= esc($genre) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>
        <?= $this->include('partials/book_list') ?>
      </div>
      <div class="col-lg-3">
        <h2 class="h-mobile-lg mb-lg-2 mb-4" id="newBookTitle">Koleksi Buku Terbaru</h2>
        <div class="row">
          <?php foreach ($latestBooks as $book): ?>
          <div class="col-6 col-lg-12">
            <div class="card border-light mb-3 shadow-sm" class="latest-book-card">
              <img src="<?= !empty($book['image']) ? esc($book['image']) : 'https://placehold.co/600x400?text=No+Image' ?>" 
                  class="card-img-top latest-book-img height-mobile-xl" 
                  alt="<?= esc($book['title'] ?? 'Gambar Buku') ?>"
                  onerror="this.src='https://placehold.co/600x400?text=Image+Error'">
              <div class="card-body">
                <h5 class="card-title card-title-mobile"><?= esc($book['title'] ?? 'Tanpa Judul') ?></h5>
                <p class="card-text card-text-mobile truncate"><?= esc($book['synopsis'] ?? 'Tidak ada sinopsis.') ?></p>
                <a href="<?= base_url('books/detail?title=' . urlencode($book['title'])) ?>" class="btn btn-secondary btn-mobile-md card-link">Detail <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah/Ubah Buku -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title text-mobile-xl" id="exampleModalLabel">Tambah buku</h1>
        <button type="button" class="btn-close text-mobile-sm" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <!-- TAMBAH BUKU -->
        <div id="tambahSection" style="display: none;">
          <!-- Kode Sekolah Only (NO RFID HERE) -->
          <div class="row mb-3">
            <div class="col">
              <label for="kode_sekolah" class="form-label required text-mobile-sm">
                Kode Sekolah 
                <span class="badge bg-info">Auto/Manual</span>
              </label>
              <div class="input-group">
                <input type="text" class="form-control form-control-mobile" id="kode_sekolah" placeholder="Auto-generate atau ketik manual">
                <button class="btn btn-outline-secondary" type="button" id="generateKodeBtn" title="Generate Kode Baru">
                  <i class="bi bi-arrow-clockwise"></i> Auto
                </button>
              </div>
              <small class="form-text text-muted text-mobile-xs">Format auto: {nomor}/YCB-CB/{bulan}/{tahun} atau ketik manual</small>
            </div>
          </div>
          
          <!-- Judul & Pengarang -->
          <div class="row mb-3">
            <div class="col">
              <label for="judul" class="form-label required text-mobile-sm">Judul</label>
              <input type="text" class="form-control form-control-mobile" id="judul">
            </div>
            <div class="col">
              <label for="pengarang" class="form-label required text-mobile-sm">Pengarang</label>
              <input type="text" class="form-control form-control-mobile" id="pengarang">
            </div>
          </div>

          <!-- Illustrator & Publisher -->
          <div class="row mb-3">
            <div class="col">
              <label for="illustrator" class="form-label required text-mobile-sm">Illustrator</label>
              <input type="text" class="form-control form-control-mobile" id="illustrator">
            </div>
            <div class="col">
              <label for="publisher" class="form-label required text-mobile-sm">Publisher</label>
              <input type="text" class="form-control form-control-mobile" id="publisher">
            </div>
          </div>

          <!-- Series & Category -->
          <div class="row mb-3">
            <div class="col">
              <label for="series" class="form-label required text-mobile-sm">Series</label>
              <input type="text" class="form-control form-control-mobile" id="series">
            </div>
            <div class="col">
              <label for="kategori" class="form-label required text-mobile-sm">Kategori</label>
              <select class="form-select form-control-mobile" id="kategori" name="kategori">
                <option selected disabled>Pilih kategori</option>
                <?php foreach ($genres as $genre): ?>
                  <option value="<?= esc($genre) ?>"><?= esc($genre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- ISBN & DDC -->
          <div class="row mb-3">
            <div class="col">
              <label for="isbn" class="form-label text-mobile-sm">ISBN</label>
              <input type="text" class="form-control form-control-mobile" id="isbn" placeholder="">
            </div>
            <div class="col">
              <label for="ddcNumber" class="form-label text-mobile-sm">DDC Number</label>
              <input type="text" class="form-control form-control-mobile" id="ddcNumber" placeholder="">
            </div>
          </div>

          <!-- Image & Quantity with Tabs -->
          <div class="row mb-3">
            <div class="col">
              <label for="gambarLink" class="form-label required text-mobile-sm">Image</label>
              
              <!-- Tab Navigation -->
              <ul class="nav nav-tabs nav-tabs-mobile mb-2" id="imageInputTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-panel" type="button" role="tab">
                    <i class="bi bi-link-45deg"></i> URL
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="camera-tab" data-bs-toggle="tab" data-bs-target="#camera-panel" type="button" role="tab">
                    <i class="bi bi-camera"></i> Camera
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-panel" type="button" role="tab">
                    <i class="bi bi-upload"></i> Upload
                  </button>
                </li>
              </ul>

              <!-- Tab Content -->
              <div class="tab-content" id="imageInputTabContent">
                <!-- URL Input -->
                <div class="tab-pane fade show active" id="url-panel" role="tabpanel">
                  <div class="input-group">
                    <input type="text" class="form-control form-control-mobile" id="gambarLink" placeholder="Paste image URL here">
                    <button class="btn btn-primary" type="button" id="analyzeBtn">
                      <i class="bi bi-search"></i> Analyze
                    </button>
                  </div>
                </div>

                <!-- Camera Capture -->
                <div class="tab-pane fade" id="camera-panel" role="tabpanel">
                  <div class="camera-container">
                    <video id="cameraPreview" autoplay playsinline style="width: 100%; max-height: 300px; display: none; border-radius: 8px; background: #000;"></video>
                    <canvas id="cameraCanvas" style="display: none;"></canvas>
                    
                    <div class="d-grid gap-2 mb-2">
                      <button class="btn btn-outline-primary btn-mobile-lg" type="button" id="startCameraBtn">
                        <i class="bi bi-camera-video"></i> Start Camera
                      </button>
                      <button class="btn btn-success" type="button" id="captureBtn" style="display: none;">
                        <i class="bi bi-camera"></i> Capture Photo
                      </button>
                      <button class="btn btn-outline-secondary" type="button" id="stopCameraBtn" style="display: none;">
                        <i class="bi bi-stop-circle"></i> Stop Camera
                      </button>
                    </div>
                  </div>
                </div>

                <!-- File Upload -->
                <div class="tab-pane fade" id="upload-panel" role="tabpanel">
                  <div class="input-group">
                    <input type="file" class="form-control form-control-mobile" id="fileUpload" accept="image/*">
                    <button class="btn btn-primary" type="button" id="analyzeUploadBtn">
                      <i class="bi bi-search"></i> Analyze
                    </button>
                  </div>
                  <small class="form-text text-muted">Accepted formats: JPG, PNG, WEBP</small>
                </div>
              </div>
            </div>
            
            <div class="col">
              <label for="quantity" class="form-label required text-mobile-sm">Quantity</label>
              <input type="number" class="form-control form-control-mobile" id="quantity" value="1">
            </div>
          </div>

          <!-- Sinopsis -->
          <div class="mb-3">
            <label for="sinopsis" class="form-label required text-mobile-sm">Sinopsis</label>
            <textarea class="form-control form-control-mobile" id="sinopsis" rows="4" placeholder="Tuliskan sinopsis buku di sini..."></textarea>
          </div>

          <!-- Image Preview -->
          <div class="mb-3">
            <img id="previewImage" src="" alt="Preview" style="max-width: 100%; max-height: 300px; display:none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-mobile-md" data-bs-dismiss="modal" id="closeBtn">Tutup</button>
        <button type="button" class="btn btn-primary btn-mobile-md" id="submitBtn">Kirim</button>
      </div>
    </div>
  </div>
</div>

<!-- RFID Confirmation Modal -->
<div class="modal fade" id="rfidModal" tabindex="-1" aria-labelledby="rfidModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-mobile-xl" id="rfidModalLabel">
          <i class="bi bi-credit-card-2-front"></i> Scan RFID Card
        </h5>
        <button type="button" class="btn-close btn-close-white text-mobile-sm" data-bs-dismiss="modal" aria-label="Close" id="rfidModalClose"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <i class="bi bi-upc-scan" style="font-size: 3rem; color: #0d6efd;"></i>
          <p class="mt-2 text-muted text-mobile-xs" id="rfidInstruction">Silakan scan kartu RFID sekarang</p>
        </div>
        
        <div class="mb-3">
          <label for="rfid_uid_confirm" class="form-label fw-bold text-mobile-sm">RFID UID <span class="text-danger">*</span></label>
          <input 
            type="text" 
            class="form-control form-control-lg text-center" 
            id="rfid_uid_confirm" 
            placeholder="Scan atau ketik RFID UID" 
            autocomplete="off"
            style="letter-spacing: 2px; font-family: monospace;">
          <div class="form-text text-mobile-xs">
            <i class="bi bi-info-circle"></i> RFID akan otomatis terdeteksi saat di-scan
          </div>
        </div>

        <!-- Book Summary -->
        <div class="card bg-light" id="bookSummaryCard">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted text-mobile-xs">Ringkasan Buku:</h6>
            <p class="mb-1 text-mobile-xs"><strong>Judul:</strong> <span id="bookSummaryTitle">-</span></p>
            <p class="mb-1 text-mobile-xs"><strong>Pengarang:</strong> <span id="bookSummaryAuthor">-</span></p>
            <p class="mb-0 text-mobile-xs"><strong>Kode Sekolah:</strong> <span id="bookSummaryKode">-</span></p>
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
        <button type="button" class="btn btn-secondary btn-mobile-md" data-bs-dismiss="modal" id="rfidCancelBtn">
          <i class="bi bi-x-circle"></i> Batal
        </button>
        <button type="button" class="btn btn-primary btn-mobile-md" id="confirmRfidBtn" disabled>
          <i class="bi bi-check-circle"></i> Konfirmasi & Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById('exampleModal');
  const modalTitle = modal.querySelector('.modal-title');
  const tambahSection = document.getElementById('tambahSection');
  const ubahSection = document.getElementById('ubahSection');
  const bookTitles = <?= json_encode($bookTitles ?? []) ?>;
  const books = <?= json_encode($allBooks ?? []) ?>;
  window.books = books;

  let cameraStream = null;
  let capturedImageData = null;

  // =============== CLOUDINARY CONFIGURATION ===============
  const CLOUDINARY_CONFIG = {
    cloud_name: 'dqx1ofl8j',
    upload_preset: 'ml_default'
  };

  // =============== CLOUDINARY UPLOAD FUNCTION ===============
  async function uploadToCloudinary(imageData) {
    console.log('📤 Starting Cloudinary upload...');
    
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

      console.log('📋 Uploading with filename:', filename);

      const uploadResponse = await fetch(
        `https://api.cloudinary.com/v1_1/${CLOUDINARY_CONFIG.cloud_name}/image/upload`,
        {
          method: 'POST',
          body: formData
        }
      );

      console.log('📡 Cloudinary response status:', uploadResponse.status);

      if (!uploadResponse.ok) {
        const errorData = await uploadResponse.json();
        console.error('❌ Cloudinary error:', errorData);
        throw new Error(errorData.error?.message || `HTTP ${uploadResponse.status}: ${errorData.message || 'Upload failed'}`);
      }

      const data = await uploadResponse.json();
      console.log('✅ Cloudinary upload successful:', data);

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

    // Show the main modal again when RFID modal is closed
    const mainModal = bootstrap.Modal.getInstance(modal);
    if (mainModal) {
      mainModal.show();
    }
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
      pendingBookData.rfid_uid = rfidValue;
      updateStep('step1', 'completed');

      let cloudinaryImageUrl = null;
      if (capturedImageData || pendingBookData.gambar) {
        updateStep('step2', 'active');
        
        try {
          const imageToUpload = capturedImageData || pendingBookData.gambar;
          cloudinaryImageUrl = await uploadToCloudinary(imageToUpload);
          
          cloudinaryUrl.textContent = cloudinaryImageUrl;
          cloudinaryResult.style.display = 'block';
          
          pendingBookData.gambar = cloudinaryImageUrl;
          
          updateStep('step2', 'completed');
          
          console.log('✅ Cloudinary URL:', cloudinaryImageUrl);
        } catch (error) {
          updateStep('step2', 'failed');
          
          let errorMessage = '❌ Upload ke Cloudinary gagal!\n\n';
          
          if (error.message.includes('Upload preset')) {
            errorMessage += 'Alasan: Upload preset tidak valid.\n';
            errorMessage += 'Solusi: Buat unsigned upload preset "ml_default" di Cloudinary dashboard.';
          } else if (error.message.includes('Invalid')) {
            errorMessage += 'Alasan: ' + error.message;
          } else {
            errorMessage += 'Alasan: ' + error.message;
          }
          
          alert(errorMessage);
          throw error;
        }
      } else {
        updateStep('step2', 'completed');
      }

      updateStep('step3', 'active');
      
      const response = await fetch("<?= base_url('books/add') ?>", {
        method: "POST",
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(pendingBookData)
      });

      console.log('Response status:', response.status);
      console.log('Response OK:', response.ok);

      if (!response.ok) {
        const errorText = await response.text();
        console.error('Server error response:', errorText);
        
        if (response.status === 409) {
          throw new Error('Conflict: RFID atau data buku sudah ada di database');
        }
        throw new Error(`Server error: ${response.status} - ${errorText}`);
      }

      const data = await response.json();
      console.log('Server response data:', data);

      if (data.success) {
        updateStep('step3', 'completed');
        
        await new Promise(resolve => setTimeout(resolve, 500));
        
        rfidModal.hide();
        $('#exampleModal').modal('hide');
        
        let successMsg = '✅ Buku berhasil ditambahkan!\n\n';
        successMsg += `RFID: ${rfidValue}\n`;
        if (cloudinaryImageUrl) {
          successMsg += `Gambar: ${cloudinaryImageUrl}`;
        }
        
        alert(successMsg);
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

  // =============== CAMERA FUNCTIONALITY ===============
  const cameraPreview = document.getElementById('cameraPreview');
  const cameraCanvas = document.getElementById('cameraCanvas');
  const startCameraBtn = document.getElementById('startCameraBtn');
  const captureBtn = document.getElementById('captureBtn');
  const stopCameraBtn = document.getElementById('stopCameraBtn');
  const previewImage = document.getElementById('previewImage');

  startCameraBtn.addEventListener('click', async () => {
    try {
      cameraStream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          facingMode: 'environment',
          width: { ideal: 1920 },
          height: { ideal: 1080 }
        } 
      });
      
      cameraPreview.srcObject = cameraStream;
      cameraPreview.style.display = 'block';
      startCameraBtn.style.display = 'none';
      captureBtn.style.display = 'block';
      stopCameraBtn.style.display = 'block';
      previewImage.style.display = 'none';
    } catch (err) {
      console.error('Camera access error:', err);
      alert('Unable to access camera. Please check permissions.');
    }
  });

  captureBtn.addEventListener('click', async () => {
    const context = cameraCanvas.getContext('2d');
    
    // Resize image to max 1024px width to reduce size
    const maxWidth = 1024;
    const scale = Math.min(1, maxWidth / cameraPreview.videoWidth);
    
    cameraCanvas.width = cameraPreview.videoWidth * scale;
    cameraCanvas.height = cameraPreview.videoHeight * scale;
    
    context.drawImage(cameraPreview, 0, 0, cameraCanvas.width, cameraCanvas.height);
    
    // Use lower quality (0.7) to further reduce size
    capturedImageData = cameraCanvas.toDataURL('image/jpeg', 0.7);
    
    console.log('📸 Captured image size:', Math.round(capturedImageData.length / 1024), 'KB');
    
    previewImage.src = capturedImageData;
    previewImage.style.display = 'block';
    
    stopCamera();
    
    await analyzeImage(capturedImageData, 'base64');
  });

  stopCameraBtn.addEventListener('click', stopCamera);

  function stopCamera() {
    if (cameraStream) {
      cameraStream.getTracks().forEach(track => track.stop());
      cameraStream = null;
    }
    cameraPreview.style.display = 'none';
    cameraPreview.srcObject = null;
    startCameraBtn.style.display = 'block';
    captureBtn.style.display = 'none';
    stopCameraBtn.style.display = 'none';
  }

  // =============== URL ANALYZE ===============
  document.getElementById('analyzeBtn').addEventListener('click', async () => {
    const imageUrl = document.getElementById('gambarLink').value.trim();
    
    if (!imageUrl) {
      alert('Masukkan link gambar terlebih dahulu.');
      return;
    }

    previewImage.src = imageUrl;
    previewImage.style.display = 'block';
    
    await analyzeImage(imageUrl, 'url');
  });

  // =============== FILE UPLOAD ANALYZE ===============
  document.getElementById('analyzeUploadBtn').addEventListener('click', async () => {
    const fileInput = document.getElementById('fileUpload');
    const file = fileInput.files[0];
    
    if (!file) {
      alert('Pilih file gambar terlebih dahulu.');
      return;
    }

    // Compress image before analyzing
    const reader = new FileReader();
    reader.onload = async (e) => {
      const img = new Image();
      img.onload = async () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Resize to max 1024px width
        const maxWidth = 1024;
        const scale = Math.min(1, maxWidth / img.width);
        
        canvas.width = img.width * scale;
        canvas.height = img.height * scale;
        
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        const compressedData = canvas.toDataURL('image/jpeg', 0.7);
        
        console.log('📦 Compressed image size:', Math.round(compressedData.length / 1024), 'KB');
        
        previewImage.src = compressedData;
        previewImage.style.display = 'block';
        
        capturedImageData = compressedData;
        
        await analyzeImage(compressedData, 'base64');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });

  // =============== UNIFIED ANALYZE FUNCTION ===============
  async function analyzeImage(imageData, type) {
  const analyzeBtn = document.getElementById('analyzeBtn');
  const analyzeUploadBtn = document.getElementById('analyzeUploadBtn');
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
      'judul': data.title,
      'pengarang': data.author,
      'illustrator': data.illustrator,
      'publisher': data.publisher,
      'series': data.series,
      'isbn': data.isbn,
      'ddcNumber': data.ddcNumber || data.ddc,
      'quantity': data.quantity || 1,
      'sinopsis': data.synopsis
    };

    for (const [fieldId, value] of Object.entries(fields)) {
      const element = document.getElementById(fieldId);
      if (element && value && value !== 'NOT FOUND') {
        element.value = value;
        console.log(`  ✔ Set ${fieldId}: ${value.substring(0, 50)}...`);
      }
    }

    // Auto-fill category dropdown
    const kategoriSelect = document.getElementById('kategori');
    if (kategoriSelect && (data.category || data.genre)) {
      const genreValue = (data.category || data.genre).toLowerCase().trim();
      console.log(`  🔎 Looking for category: ${genreValue}`);
      
      let found = false;
      
      // Try exact match first
      for (const option of kategoriSelect.options) {
        if (option.value.toLowerCase() === genreValue) {
          option.selected = true;
          found = true;
          console.log(`  ✅ Category exact match: ${option.value}`);
          break;
        }
      }
      
      // Try partial match if exact match not found
      if (!found) {
        for (const option of kategoriSelect.options) {
          const optionLower = option.value.toLowerCase();
          
          // Check if category contains the genre or vice versa
          if (optionLower.includes(genreValue) || genreValue.includes(optionLower)) {
            option.selected = true;
            found = true;
            console.log(`  ✅ Category partial match: ${option.value}`);
            break;
          }
        }
      }
      
      // Try keyword matching as last resort
      if (!found) {
        const keywords = genreValue.split(' ');
        for (const option of kategoriSelect.options) {
          const optionLower = option.value.toLowerCase();
          for (const keyword of keywords) {
            if (keyword.length > 3 && optionLower.includes(keyword)) {
              option.selected = true;
              found = true;
              console.log(`  ✅ Category keyword match: ${option.value} (keyword: ${keyword})`);
              break;
            }
          }
          if (found) break;
        }
      }
      
      if (!found) {
        console.log(`  ⚠️ Category "${genreValue}" not found in options`);
      }
    }

    // Broadcast to other connected clients
    if (window.formSync && window.formSync.channel) {
      console.log('📤 Broadcasting AI analysis results...');
      window.formSync.broadcastAIAnalysis(data);
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

// Make it globally accessible
window.analyzeImage = analyzeImage;

  
  // =============== KODE SEKOLAH AUTO-GENERATE ===============
  function loadNextKodeSekolah() {
    $('#kode_sekolah').val('Loading...');
    
    $.ajax({
      url: "<?= base_url('books/next-kode') ?>",
      type: "GET",
      dataType: "json",
      success: function(response) {
        if (response.success) {
          $('#kode_sekolah').val(response.kode_sekolah);
          console.log('✅ Kode sekolah generated:', response.kode_sekolah);
        } else {
          $('#kode_sekolah').val('Error');
          console.error('❌ Failed to generate kode:', response.message);
        }
      },
      error: function(xhr, status, error) {
        $('#kode_sekolah').val('Error');
        console.error('❌ AJAX Error:', error);
      }
    });
  }

  $('#generateKodeBtn').on('click', function() {
    loadNextKodeSekolah();
  });

  $('#tambah').on('click', function() {
    modalTitle.textContent = 'Tambah Buku';
    tambahSection.style.display = 'block';
    ubahSection.style.display = 'none';
    clearForm();
    
    setTimeout(() => {
      loadNextKodeSekolah();
    }, 300);
  });

  $('#gambarLink').on('input', function() {
    const url = $(this).val().trim();
    const preview = $('#previewImage');
    
    if (url) {
      preview.attr('src', url).show();
    } else {
      preview.hide();
    }
  });

  $('#submitBtn').on('click', function(e) {
    e.preventDefault();
    handleBookAdd();
  });

  function handleBookAdd() {
    const bookData = {
      kode_sekolah: $('#kode_sekolah').val() || '',
      judul: $('#judul').val() || '',
      pengarang: $('#pengarang').val() || '',
      illustrator: $('#illustrator').val() || '',
      publisher: $('#publisher').val() || '',
      series: $('#series').val() || '',
      kategori: $('#kategori').val() || '',
      isbn: $('#isbn').val() || '',
      ddcNumber: $('#ddcNumber').val() || '',
      gambar: capturedImageData || $('#gambarLink').val() || '',
      quantity: $('#quantity').val() || '1',
      sinopsis: $('#sinopsis').val() || ''
    };

    if (!bookData.judul) {
      alert('⚠️ Judul harus diisi!');
      $('#judul').focus();
      return;
    }

    if (!bookData.pengarang) {
      alert('⚠️ Pengarang harus diisi!');
      $('#pengarang').focus();
      return;
    }

    if (!bookData.kode_sekolah) {
      alert('⚠️ Kode Sekolah harus di-generate terlebih dahulu!');
      $('#generateKodeBtn').focus();
      return;
    }

    if (!bookData.kategori) {
      alert('⚠️ Kategori harus dipilih!');
      $('#kategori').focus();
      return;
    }

    pendingBookData = bookData;

    document.getElementById('bookSummaryTitle').textContent = bookData.judul;
    document.getElementById('bookSummaryAuthor').textContent = bookData.pengarang;
    document.getElementById('bookSummaryKode').textContent = bookData.kode_sekolah;

    // Hide the main modal and show RFID modal
    const mainModal = bootstrap.Modal.getInstance(modal);
    if (mainModal) {
      mainModal.hide();
    }
    rfidModal.show();
  }

  function clearForm() {
    const inputs = modal.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      if (input.type === 'file') {
        input.value = '';
      } else if (input.type === 'select-one') {
        input.selectedIndex = 0;
      } else {
        input.value = '';
      }
    });
    $('#previewImage').hide();
    capturedImageData = null;
  }

  $('#genreSelectpicker').on('changed.bs.select', function (e) {
    loadBooks();
  });

  let searchTimeout;
  $('input[name="search"]').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      loadBooks();
    }, 500);
  });

  // =============== PAGINATION VARIABLES ===============
  let currentPage = <?= $page ?>;
  let totalPages = <?= $totalPages ?>;
  const ITEMS_PER_PAGE = 10;

  // =============== PAGINATION RENDERING ===============
  function renderBookPagination() {
    const paginationHtml = generateBookPaginationHTML(currentPage, totalPages);
    $('#bookPagination').html(paginationHtml);
    attachBookPaginationListeners();
  }

  function generateBookPaginationHTML(currentPage, totalPages) {
    let html = '';
    const maxPagesToShow = 3;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    if (endPage - startPage < maxPagesToShow - 1) {
      startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    // Previous button
    if (currentPage > 1) {
      html += `<li class="page-item"><a href="#" class="page-link book-pagination-link" data-page="${currentPage - 1}">&laquo;</a></li>`;
    } else {
      html += `<li class="page-item disabled"><a class="page-link">&laquo;</a></li>`;
    }

    // Page numbers
    if (startPage > 1) {
      html += `<li class="page-item"><a href="#" class="page-link book-pagination-link" data-page="1">1</a></li>`;
      if (startPage > 2) {
        html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      if (i === currentPage) {
        html += `<li class="page-item active"><a class="page-link">` + i + `</a></li>`;
      } else {
        html += `<li class="page-item"><a href="#" class="page-link book-pagination-link" data-page="` + i + `">` + i + `</a></li>`;
      }
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
      }
      html += `<li class="page-item"><a href="#" class="page-link book-pagination-link" data-page="` + totalPages + `">` + totalPages + `</a></li>`;
    }

    // Next button
    if (currentPage < totalPages) {
      html += `<li class="page-item"><a href="#" class="page-link book-pagination-link" data-page="${currentPage + 1}">&raquo;</a></li>`;
    } else {
      html += `<li class="page-item disabled"><a class="page-link">&raquo;</a></li>`;
    }

    return html;
  }

  function attachBookPaginationListeners() {
    $('#bookPagination .book-pagination-link').on('click', function(e) {
      e.preventDefault();
      const page = parseInt($(this).data('page'));
      currentPage = page;
      loadBooks(page);
      window.scrollTo(0, 0);
    });
  }

  function loadBooks(page = 1) {
    let formData = $('#selectpickerForm').serialize();
    let searchValue = $('input[name="search"]').val();
    if (searchValue) {
      formData += '&search=' + encodeURIComponent(searchValue);
    }
    formData += '&page=' + page;

    $.ajax({
      url: "<?= base_url('books/filter') ?>",
      type: "GET",
      data: formData,
      success: function(response) {
        $('#booksContainer').html(response);
        renderBookPagination();
      }
    });
  }

  $('#selectpickerForm, #searchForm').on('submit', function(e) {
    e.preventDefault();
    currentPage = 1;
    loadBooks(1);
  });

  // Initialize pagination on page load
  $(document).ready(function() {
    renderBookPagination();
  });

  $('#exampleModal').on('hidden.bs.modal', function() {
    stopCamera();
    clearForm();
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

<script src="<?= base_url('js/supabase-config.js') ?>"></script>

<!-- =========================================
     LIVE FORM SYNC - Add to welcome_message.php
     Place AFTER the existing real-time code
     ========================================= -->

<script>
// ============================================
// ENHANCED FORM SYNC MANAGER WITH AI ANALYSIS BROADCASTING
// ============================================
class FormSyncManager {
  constructor() {
    this.channel = null;
    this.sessionId = this.generateSessionId();
    this.isTyping = false;
    this.typingTimeout = null;
    this.isSyncing = false; // Prevent infinite loops
    this.lastBroadcastData = null; // Track last broadcast to avoid duplicates
  }

  generateSessionId() {
    return 'session_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
  }

  init() {
    console.log('🔌 Initializing Enhanced Form Sync...');
    console.log('🆔 Session ID:', this.sessionId);

    this.channel = supabaseClient.channel('form-sync', {
      config: {
        broadcast: { self: false }
      }
    });

    this.channel
      .on('broadcast', { event: 'form-update' }, (payload) => {
        this.handleFormUpdate(payload);
      })
      .on('broadcast', { event: 'ai-analysis-complete' }, (payload) => {
        this.handleAIAnalysisUpdate(payload);
      })
      .on('broadcast', { event: 'kode-generated' }, (payload) => {
        this.handleKodeUpdate(payload);
      })
      .subscribe((status) => {
        if (status === 'SUBSCRIBED') {
          console.log('✅ Form sync connected!');
          this.showSyncStatus('ready');
          this.attachFormListeners();
          this.interceptAIAnalysis();
          this.interceptKodeGeneration();
        }
      });
  }

  attachFormListeners() {
    const formFields = {
      kode_sekolah: document.getElementById('kode_sekolah'),
      judul: document.getElementById('judul'),
      pengarang: document.getElementById('pengarang'),
      illustrator: document.getElementById('illustrator'),
      publisher: document.getElementById('publisher'),
      series: document.getElementById('series'),
      kategori: document.getElementById('kategori'),
      isbn: document.getElementById('isbn'),
      ddcNumber: document.getElementById('ddcNumber'),
      gambarLink: document.getElementById('gambarLink'),
      quantity: document.getElementById('quantity'),
      sinopsis: document.getElementById('sinopsis')
    };

    Object.keys(formFields).forEach(fieldName => {
      const field = formFields[fieldName];
      if (!field) return;

      field.addEventListener('input', (e) => {
        if (this.isSyncing) return;
        clearTimeout(this.typingTimeout);
        this.typingTimeout = setTimeout(() => {
          this.broadcastFormData();
        }, 300);
      });

      field.addEventListener('change', (e) => {
        if (this.isSyncing) return;
        this.broadcastFormData();
      });
    });

    console.log('👂 Form listeners attached');
  }

  // ============================================
  // INTERCEPT AI ANALYSIS RESULTS
  // ============================================
  interceptAIAnalysis() {
    console.log('🤖 Intercepting AI Analysis function...');
    
    // Store original analyzeImage function
    const originalAnalyzeImage = window.analyzeImage;
    
    // Override the analyzeImage function
    window.analyzeImage = async (imageData, type) => {
      console.log('🔍 AI Analysis started...');
      
      try {
        // Call original function
        const result = await originalAnalyzeImage.call(window, imageData, type);
        
        // After successful analysis, broadcast the results
        if (result && !result.error) {
          console.log('✅ AI Analysis complete, broadcasting results...');
          this.broadcastAIAnalysis(result);
        }
        
        return result;
      } catch (error) {
        console.error('❌ AI Analysis error:', error);
        throw error;
      }
    };
    
    // Also intercept the analyze button clicks
    this.attachAnalyzeButtonListeners();
  }

  attachAnalyzeButtonListeners() {
    const analyzeBtn = document.getElementById('analyzeBtn');
    const analyzeUploadBtn = document.getElementById('analyzeUploadBtn');
    
    if (analyzeBtn) {
      const originalClick = analyzeBtn.onclick;
      analyzeBtn.onclick = async (e) => {
        if (originalClick) await originalClick.call(analyzeBtn, e);
        
        // Broadcast after analysis completes
        setTimeout(() => {
          this.broadcastFormData('ai-analysis');
        }, 1000);
      };
    }
    
    if (analyzeUploadBtn) {
      const originalClick = analyzeUploadBtn.onclick;
      analyzeUploadBtn.onclick = async (e) => {
        if (originalClick) await originalClick.call(analyzeUploadBtn, e);
        
        setTimeout(() => {
          this.broadcastFormData('ai-analysis');
        }, 1000);
      };
    }
  }

  // ============================================
  // INTERCEPT KODE SEKOLAH GENERATION
  // ============================================
  interceptKodeGeneration() {
    console.log('🔢 Intercepting Kode Generation...');
    
    const generateBtn = document.getElementById('generateKodeBtn');
    if (!generateBtn) return;
    
    generateBtn.addEventListener('click', () => {
      // Wait for the kode to be generated
      setTimeout(() => {
        const kodeValue = document.getElementById('kode_sekolah')?.value;
        if (kodeValue && kodeValue !== 'Loading...' && kodeValue !== 'Error') {
          console.log('📤 Broadcasting generated kode:', kodeValue);
          this.broadcastKodeGeneration(kodeValue);
        }
      }, 1500); // Wait for AJAX to complete
    });
  }

  // ============================================
  // BROADCAST AI ANALYSIS RESULTS
  // ============================================
  broadcastAIAnalysis(analysisData) {
    const payload = {
      type: 'ai-analysis',
      sessionId: this.sessionId,
      timestamp: Date.now(),
      data: analysisData
    };

    this.channel.send({
      type: 'broadcast',
      event: 'ai-analysis-complete',
      payload: payload
    });

    console.log('📤 AI Analysis broadcasted:', payload);
    this.showSyncStatus('syncing');
  }

  // ============================================
  // HANDLE AI ANALYSIS UPDATE FROM OTHER CLIENTS
  // ============================================
  handleAIAnalysisUpdate(payload) {
    console.log('📥 Received AI Analysis update:', payload);
    
    if (payload.payload.sessionId === this.sessionId) {
      return; // Ignore own broadcasts
    }

    this.isSyncing = true;
    
    const data = payload.payload.data;
    
    // Fill all form fields with AI analysis results
    this.setFieldValue('judul', data.title || '');
    this.setFieldValue('pengarang', data.author || '');
    this.setFieldValue('illustrator', data.illustrator || '');
    this.setFieldValue('publisher', data.publisher || '');
    this.setFieldValue('series', data.series || '');
    this.setFieldValue('isbn', data.isbn || '');
    this.setFieldValue('ddcNumber', data.ddcNumber || data.ddc || '');
    this.setFieldValue('quantity', data.quantity || '1');
    this.setFieldValue('sinopsis', data.synopsis || '');
    
    // Handle category/genre
    if (data.category || data.genre) {
      this.setSelectValue('kategori', data.category || data.genre);
    }
    
    // Handle image
    if (data.image || data.gambar) {
      const imageUrl = data.image || data.gambar;
      this.setFieldValue('gambarLink', imageUrl);
      
      const previewImg = document.getElementById('previewImage');
      if (previewImg) {
        previewImg.src = imageUrl;
        previewImg.style.display = 'block';
      }
    }
    
    this.flashFormFields('ai-analysis');
    this.showNotification('🤖 AI Analysis results synced from another device', 'success');
    
    setTimeout(() => {
      this.isSyncing = false;
    }, 100);
  }

  // ============================================
  // BROADCAST KODE SEKOLAH GENERATION
  // ============================================
  broadcastKodeGeneration(kode) {
    const payload = {
      type: 'kode-generation',
      sessionId: this.sessionId,
      timestamp: Date.now(),
      kode: kode
    };

    this.channel.send({
      type: 'broadcast',
      event: 'kode-generated',
      payload: payload
    });

    console.log('📤 Kode generation broadcasted:', payload);
    this.showSyncStatus('syncing');
  }

  // ============================================
  // HANDLE KODE UPDATE FROM OTHER CLIENTS
  // ============================================
  handleKodeUpdate(payload) {
    console.log('📥 Received Kode update:', payload);
    
    if (payload.payload.sessionId === this.sessionId) {
      return;
    }

    this.isSyncing = true;
    
    const kode = payload.payload.kode;
    this.setFieldValue('kode_sekolah', kode);
    
    this.flashField('kode_sekolah');
    this.showNotification(`🔢 Kode Sekolah synced: ${kode}`, 'info');
    
    setTimeout(() => {
      this.isSyncing = false;
    }, 100);
  }

  // ============================================
  // BROADCAST REGULAR FORM DATA
  // ============================================
  broadcastFormData(source = 'manual') {
    const formData = {
      kode_sekolah: document.getElementById('kode_sekolah')?.value || '',
      judul: document.getElementById('judul')?.value || '',
      pengarang: document.getElementById('pengarang')?.value || '',
      illustrator: document.getElementById('illustrator')?.value || '',
      publisher: document.getElementById('publisher')?.value || '',
      series: document.getElementById('series')?.value || '',
      kategori: document.getElementById('kategori')?.value || '',
      isbn: document.getElementById('isbn')?.value || '',
      ddcNumber: document.getElementById('ddcNumber')?.value || '',
      gambarLink: document.getElementById('gambarLink')?.value || '',
      quantity: document.getElementById('quantity')?.value || '1',
      sinopsis: document.getElementById('sinopsis')?.value || '',
      sessionId: this.sessionId,
      timestamp: Date.now(),
      source: source
    };

    // Avoid broadcasting identical data
    if (JSON.stringify(formData) === JSON.stringify(this.lastBroadcastData)) {
      return;
    }
    
    this.lastBroadcastData = formData;

    this.channel.send({
      type: 'broadcast',
      event: 'form-update',
      payload: formData
    });

    console.log('📤 Broadcasting form data:', formData);
    this.showSyncStatus('syncing');
  }

  handleFormUpdate(payload) {
    console.log('📥 Received form update:', payload);

    if (payload.payload.sessionId === this.sessionId) {
      return;
    }

    this.isSyncing = true;

    const data = payload.payload;
    
    this.setFieldValue('kode_sekolah', data.kode_sekolah);
    this.setFieldValue('judul', data.judul);
    this.setFieldValue('pengarang', data.pengarang);
    this.setFieldValue('illustrator', data.illustrator);
    this.setFieldValue('publisher', data.publisher);
    this.setFieldValue('series', data.series);
    this.setSelectValue('kategori', data.kategori);
    this.setFieldValue('isbn', data.isbn);
    this.setFieldValue('ddcNumber', data.ddcNumber);
    this.setFieldValue('gambarLink', data.gambarLink);
    this.setFieldValue('quantity', data.quantity);
    this.setFieldValue('sinopsis', data.sinopsis);

    if (data.gambarLink) {
      const previewImg = document.getElementById('previewImage');
      if (previewImg) {
        previewImg.src = data.gambarLink;
        previewImg.style.display = 'block';
      }
    }

    this.flashFormFields(data.source);
    this.showSyncStatus('synced');

    setTimeout(() => {
      this.isSyncing = false;
    }, 100);
  }

  setFieldValue(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (!field || field.value === value) return;

    field.value = value;

    const event = new Event('change', { bubbles: true });
    field.dispatchEvent(event);
  }

  setSelectValue(fieldId, value) {
    const select = document.getElementById(fieldId);
    if (!select || !value) return;
    
    const normalizedValue = value.toLowerCase().trim();
    
    // Try exact match first
    for (const option of select.options) {
      if (option.value.toLowerCase() === normalizedValue) {
        option.selected = true;
        const event = new Event('change', { bubbles: true });
        select.dispatchEvent(event);
        console.log(`✅ Category matched: ${option.value}`);
        return;
      }
    }
    
    // Try partial match
    for (const option of select.options) {
      if (option.value.toLowerCase().includes(normalizedValue) || 
          normalizedValue.includes(option.value.toLowerCase())) {
        option.selected = true;
        const event = new Event('change', { bubbles: true });
        select.dispatchEvent(event);
        console.log(`✅ Category partial match: ${option.value}`);
        return;
      }
    }
    
    console.log(`⚠️ Category "${value}" not found in dropdown`);
  }

  flashFormFields(source = 'manual') {
    const modal = document.querySelector('#exampleModal .modal-body');
    if (!modal) return;
    
    const colors = {
      'ai-analysis': '#e7f1ff',
      'manual': '#fff3cd',
      'kode-generation': '#d1e7dd'
    };
    
    const color = colors[source] || '#f8f9fa';
    
    modal.style.transition = 'background-color 0.5s ease';
    modal.style.backgroundColor = color;
    
    setTimeout(() => {
      modal.style.backgroundColor = '';
    }, 1000);
  }

  flashField(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.style.transition = 'all 0.3s ease';
    field.style.backgroundColor = '#ffd700';
    field.style.transform = 'scale(1.02)';
    
    setTimeout(() => {
      field.style.backgroundColor = '';
      field.style.transform = '';
    }, 500);
  }

  showSyncStatus(status) {
    let indicator = document.getElementById('formSyncStatus');
    
    if (!indicator) {
      indicator = document.createElement('div');
      indicator.id = 'formSyncStatus';
      indicator.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 10px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        z-index: 9999;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
      `;
      document.body.appendChild(indicator);
    }

    if (status === 'ready') {
      indicator.style.background = '#d1e7dd';
      indicator.style.color = '#0f5132';
      indicator.innerHTML = '🟢 Form sync ready';
      
      setTimeout(() => {
        indicator.style.opacity = '0.5';
      }, 2000);
    } else if (status === 'syncing') {
      indicator.style.background = '#cfe2ff';
      indicator.style.color = '#084298';
      indicator.style.opacity = '1';
      indicator.innerHTML = '🔄 Syncing...';
    } else if (status === 'synced') {
      indicator.style.background = '#d1e7dd';
      indicator.style.color = '#0f5132';
      indicator.style.opacity = '1';
      indicator.innerHTML = '✅ Synced!';
      
      setTimeout(() => {
        indicator.style.opacity = '0.5';
      }, 1000);
    }
  }

  showNotification(message, type = 'info') {
    let toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toastContainer';
      toastContainer.style.cssText = `
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 9999;
      `;
      document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const bgColor = {
      'success': 'success',
      'info': 'info',
      'warning': 'warning',
      'error': 'danger'
    }[type] || 'primary';
    
    const toastHTML = `
      <div id="${toastId}" class="toast" role="alert" style="min-width: 250px;">
        <div class="toast-header bg-${bgColor} text-white">
          <strong class="me-auto">📡 Real-time Sync</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 4000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
      toastElement.remove();
    });
  }

  disconnect() {
    if (this.channel) {
      supabase.removeChannel(this.channel);
      console.log('👋 Form sync disconnected');
    }
  }
}

// Initialize on page load
let formSync = null;

document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    formSync = new FormSyncManager();
    formSync.init();
    window.formSync = formSync;
  }, 1000);
});

document.getElementById('exampleModal')?.addEventListener('shown.bs.modal', () => {
  if (formSync && !formSync.channel) {
    formSync.init();
  }
});

window.addEventListener('beforeunload', () => {
  if (formSync) {
    formSync.disconnect();
  }
});
</script>

<!-- Additional CSS for Form Sync -->
<style>
  /* Form sync status indicator */
  #formSyncStatus {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 10px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    z-index: 9999;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
  }

  /* Mobile adjustments */
  @media (max-width: 768px) {
    #formSyncStatus {
      bottom: 10px;
      right: 10px;
      font-size: 10px;
      padding: 8px 12px;
    }
  }

  /* Form flash animation */
  @keyframes formFlash {
    0%, 100% {
      background-color: transparent;
    }
    50% {
      background-color: #e7f1ff;
    }
  }

  .form-syncing {
    animation: formFlash 0.5s ease-in-out;
  }
</style>
<?= $this->endSection() ?>