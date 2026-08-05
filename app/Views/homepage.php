<?php
use App\Helpers\WebsiteConfigHelper;
$websiteConfig = WebsiteConfigHelper::getConfig();
if (!is_array($selectedGenres ?? null)) {
    $selectedGenres = [];
}
?>
<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<div class="d-flex justify-content-center align-items-center my-5 my-lg-4">
  <img src="<?= base_url(
      $websiteConfig->homepageLogo
  ) ?>" alt="Logo" class="d-inline-block align-text-top me-2 img-mobile-lg" id="logoImage" fetchpriority="high"/>
  <h1 class="h-mobile-xl" id="logoTitle"><?= esc(
      $websiteConfig->homepageTitle
  ) ?></h1>
</div>

<div class="card relative" style="border-style: dashed;">
  <img src="<?= base_url(
      $websiteConfig->homepageDecorativeImage
  ) ?>" alt="Children" class="position-absolute end-0 z-n1 img-mobile-xl top-mobile-xl" id="childrenImage" fetchpriority="high"/>
  <div class="card-body">
    <div class="row">
      <div class="col-lg-9">
        <div class="row mb-4 mb-lg-3">
          <nav aria-label="Book pagination" class="col-4">
            <ul class="pagination pagination-mobile mb-2" id="bookPagination" style="flex-wrap: wrap;">
            </ul>
            <?php if (session("role") === "admin"): ?>
              <div class="col-auto">
                <button type="button" id="tambah" class="btn btn-primary btn-mobile-md" data-bs-toggle="modal" data-bs-target="#exampleModal">
                  <i class="bi bi-plus"></i> Tambah Buku
                </button>
              </div>
            <?php endif; ?>
          </nav>
          <div class="col">
            <form id="searchForm" class="d-flex mb-2" role="search">
              <input class="form-control me-2 mobile-search" type="search" name="search" placeholder="Ketik Kata Kunci" aria-label="Search" value="<?= esc(
                  $search
              ) ?>"/>
              <?php foreach ($selectedGenres as $selected): ?>
                <input type="hidden" name="genres[]" value="<?= esc(
                    $selected
                ) ?>">
              <?php endforeach; ?>
              <button class="btn btn-outline-success btn-mobile-md" type="submit" id="btnSearch"><i class="bi bi-search"></i></button>
            </form>
            <form method="get" id="selectpickerForm" style="width: 50%; margin-left: auto;">
              <?php if ($search): ?>
                <input type="hidden" name="search" value="<?= esc($search) ?>">
              <?php endif; ?>
              <select id="genreSelectpicker" class="selectpicker form-control" name="genres[]" multiple data-live-search="true" data-actions-box="true">
                <?php foreach ($genres as $genre): ?>
                  <option value="<?= esc($genre) ?>" <?= in_array(
    $genre,
    $selectedGenres
)
    ? "selected"
    : "" ?>>
                    <?= esc($genre) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>
        <?= $this->include("partials/book_list") ?>
      </div>
      <div class="col-lg-3">
        <h2 class="h-mobile-lg mb-lg-2 mb-4" id="newBookTitle"><?= esc(
            $websiteConfig->latestBooksTitle
        ) ?></h2>
        <div class="row">
          <?php foreach ($latestBooks as $latestIndex => $book): ?>
          <div class="col-6 col-lg-12">
            <div class="card border-light mb-3 shadow-sm" class="latest-book-card">
              <img src="<?= !empty($book["image"])
                  ? esc($book["image"])
                  : "https://placehold.co/600x400?text=No+Image" ?>" 
                  class="card-img-top latest-book-img height-mobile-xl" 
                  alt="<?= esc($book["title"] ?? "Gambar Buku") ?>"
                  onerror="this.src='https://placehold.co/600x400?text=Image+Error'"
                  <?php if (
                      $latestIndex === 0
                  ): ?>fetchpriority="high"<?php else: ?>loading="lazy"<?php endif; ?>>
              <div class="card-body">
                <h5 class="card-title card-title-mobile"><?= esc(
                    $book["title"] ?? "Tanpa Judul"
                ) ?></h5>
                <p class="card-text card-text-mobile truncate"><?= esc(
                    $book["synopsis"] ?? "Tidak ada sinopsis."
                ) ?></p>
                <a href="<?= base_url(
                    "books/detail?title=" . urlencode($book["title"])
                ) ?>" class="btn btn-secondary btn-mobile-md card-link">Detail <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title text-mobile-xl" id="exampleModalLabel">Tambah buku</h1>
        <button type="button" class="btn-close text-mobile-sm" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="tambahSection" style="display: none;">
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

          <div class="row mb-3">
            <div class="col">
              <label for="illustrator" class="form-label text-mobile-sm">Illustrator</label>
              <input type="text" class="form-control form-control-mobile" id="illustrator">
            </div>
            <div class="col">
              <label for="publisher" class="form-label text-mobile-sm">Publisher</label>
              <input type="text" class="form-control form-control-mobile" id="publisher">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="series" class="form-label text-mobile-sm">Series</label>
              <input type="text" class="form-control form-control-mobile" id="series">
            </div>
            <div class="col">
              <label for="kategori" class="form-label required text-mobile-sm">Kategori</label>
              <select class="form-select form-control-mobile" id="kategori" name="kategori">
                <option value="" selected disabled>Pilih kategori</option>
                <?php foreach ($genres as $genre): ?>
                  <option value="<?= esc($genre) ?>"><?= esc($genre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

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

          <div class="row mb-3">
            <div class="col">
              <label for="gambarLink" class="form-label required text-mobile-sm">Image</label>
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

              <div class="tab-content" id="imageInputTabContent">
                <div class="tab-pane fade show active" id="url-panel" role="tabpanel">
                  <div class="input-group">
                    <input type="text" class="form-control form-control-mobile" id="gambarLink" placeholder="Paste image URL here">
                    <button class="btn btn-primary" type="button" id="analyzeBtn">
                      <i class="bi bi-search"></i> Analyze
                    </button>
                  </div>
                </div>
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
          <div class="mb-3">
            <label for="sinopsis" class="form-label text-mobile-sm">Sinopsis</label>
            <textarea class="form-control form-control-mobile" id="sinopsis" rows="4" placeholder="Tuliskan sinopsis buku di sini..."></textarea>
          </div>
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

        <div class="card bg-light" id="bookSummaryCard">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted text-mobile-xs">Ringkasan Buku:</h6>
            <p class="mb-1 text-mobile-xs"><strong>Judul:</strong> <span id="bookSummaryTitle">-</span></p>
            <p class="mb-1 text-mobile-xs"><strong>Pengarang:</strong> <span id="bookSummaryAuthor">-</span></p>
            <p class="mb-0 text-mobile-xs"><strong>Kode Sekolah:</strong> <span id="bookSummaryKode">-</span></p>
          </div>
        </div>

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
  const bookTitles = <?= json_encode($bookTitles ?? []) ?>;
  const books = <?= json_encode($allBooks ?? []) ?>;
  window.books = books;

  let cameraStream = null;
  let capturedImageData = null;

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
        throw new Error(errorData.error?.message || `HTTP ${uploadResponse.status}: ${errorData.message || 'Upload failed'}`);
      }

      const data = await uploadResponse.json();

      return data.secure_url;

    } catch (error) {
      console.error('❌ Cloudinary upload error:', error);
      throw error;
    }
  }

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

  const rfidModal = new bootstrap.Modal(document.getElementById('rfidModal'));
  const rfidInput = document.getElementById('rfid_uid_confirm');
  const confirmRfidBtn = document.getElementById('confirmRfidBtn');
  const progressSteps = document.getElementById('progressSteps');
  const cloudinaryResult = document.getElementById('cloudinaryResult');
  const cloudinaryUrl = document.getElementById('cloudinaryUrl');
  let pendingBookData = null;
  let saveCompleted = false;
  let awaitingRfid = false;

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
    awaitingRfid = false;
    
    const mainModal = bootstrap.Modal.getInstance(modal);
    if (mainModal && !saveCompleted) {
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
      
      const response = await fetch("<?= base_url("books/add") ?>", {
        method: "POST",
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(pendingBookData)
      });

      if (!response.ok) {
        const errorText = await response.text();
        
        if (response.status === 409) {
          throw new Error('Conflict: RFID atau data buku sudah ada di database');
        }
        throw new Error(`Server error: ${response.status} - ${errorText}`);
      }

      const data = await response.json();

      if (data.success) {
        updateStep('step3', 'completed');
        
        await new Promise(resolve => setTimeout(resolve, 500));
        
        saveCompleted = true;
        awaitingRfid = false;
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
      alert('Gagal menambahkan buku: ' + error.message);
    } finally {
      rfidInput.disabled = false;
      confirmRfidBtn.disabled = rfidInput.value.trim().length === 0;
      document.getElementById('rfidCancelBtn').disabled = false;
      document.getElementById('rfidModalClose').disabled = false;
    }
  });

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
    
    const maxWidth = 1024;
    const scale = Math.min(1, maxWidth / cameraPreview.videoWidth);
    
    cameraCanvas.width = cameraPreview.videoWidth * scale;
    cameraCanvas.height = cameraPreview.videoHeight * scale;
    
    context.drawImage(cameraPreview, 0, 0, cameraCanvas.width, cameraCanvas.height);
    
    capturedImageData = cameraCanvas.toDataURL('image/jpeg', 0.7);
    
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

  document.getElementById('analyzeUploadBtn').addEventListener('click', async () => {
    const fileInput = document.getElementById('fileUpload');
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
        
        previewImage.src = compressedData;
        previewImage.style.display = 'block';
        
        capturedImageData = compressedData;
        
        await analyzeImage(compressedData, 'base64');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });

  function selectOrCreateCategoryOption(select, value) {
    if (!select || !value) return false;

    const category = String(value).trim();
    if (!category) return false;

    const normalizedCategory = category.toLocaleLowerCase('id-ID');
    let matchingOption = null;

    for (const option of select.options) {
      if (option.value.trim().toLocaleLowerCase('id-ID') === normalizedCategory) {
        matchingOption = option;
        break;
      }
    }

    if (!matchingOption) {
      matchingOption = new Option(category, category, true, true);
      matchingOption.dataset.aiGenerated = 'true';
      select.add(matchingOption);
    } else {
      matchingOption.selected = true;
    }

    select.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
  }

  async function analyzeImage(imageData, type) {
    const analyzeBtn = document.getElementById('analyzeBtn');
    const analyzeUploadBtn = document.getElementById('analyzeUploadBtn');
    const originalText = analyzeBtn.innerHTML;
    
    analyzeBtn.disabled = true;
    analyzeUploadBtn.disabled = true;
    analyzeBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';

    try {
      let response;
      const apiUrl = '<?= base_url("api/analyze-image") ?>';
      
      if (type === 'url') {
        const fullUrl = `${apiUrl}?image_url=${encodeURIComponent(imageData)}`;
        response = await fetch(fullUrl, {
          method: 'GET'
        });
      } else {
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

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.error) {
        alert('Gagal menganalisis gambar: ' + data.error);
        return data;
      }

      if (data.title === 'BUKAN BUKU' || !data.title) {
        alert('⚠️ Gambar bukan sampul buku atau tidak dapat dianalisis');
        return data;
      }

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
        }
      }

      const kategoriSelect = document.getElementById('kategori');
      if (kategoriSelect && (data.category || data.genre)) {
        selectOrCreateCategoryOption(
          kategoriSelect,
          data.category || data.genre
        );
      }

      if (window.formSync && window.formSync.channel) {
        window.formSync.broadcastAIAnalysis(data);
      }

      alert('✅ Analisis berhasil! Field telah diisi otomatis.\n\n💡 Gambar akan diupload ke Cloudinary setelah RFID dikonfirmasi.');
      
      return data;

    } catch (err) {
      alert('Terjadi kesalahan saat menganalisis gambar: ' + err.message);
      throw err;
    } finally {
      analyzeBtn.disabled = false;
      analyzeUploadBtn.disabled = false;
      analyzeBtn.innerHTML = originalText;
    }
  }

  window.analyzeImage = analyzeImage;

  function loadNextKodeSekolah() {
    $('#kode_sekolah').val('Loading...');
    
    $.ajax({
      url: "<?= base_url("books/next-kode") ?>",
      type: "GET",
      dataType: "json",
      success: function(response) {
        if (response.success) {
          $('#kode_sekolah').val(response.kode_sekolah);
        } else {
          $('#kode_sekolah').val('Error');
        }
      },
      error: function(xhr, status, error) {
        $('#kode_sekolah').val('Error');
      }
    });
  }

  $('#generateKodeBtn').on('click', function() {
    loadNextKodeSekolah();
  });

  $('#tambah').on('click', function() {
    saveCompleted = false;
    awaitingRfid = false;
    modalTitle.textContent = 'Tambah Buku';
    tambahSection.style.display = 'block';
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
      quantity: $('#quantity').val(),
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

    if (!bookData.kode_sekolah || bookData.kode_sekolah === 'Error') {
      alert('⚠️ Kode Sekolah harus di-generate terlebih dahulu!');
      $('#generateKodeBtn').focus();
      return;
    }

    if (!bookData.kategori) {
      alert('⚠️ Kategori harus dipilih!');
      $('#kategori').focus();
      return;
    }

    if (!bookData.quantity || isNaN(bookData.quantity) || parseInt(bookData.quantity) < 1) {
      alert('⚠️ Quantity harus berupa angka dan minimal 1!');
      $('#quantity').focus();
      return;
    }

    pendingBookData = bookData;

    document.getElementById('bookSummaryTitle').textContent = bookData.judul;
    document.getElementById('bookSummaryAuthor').textContent = bookData.pengarang;
    document.getElementById('bookSummaryKode').textContent = bookData.kode_sekolah;

    const mainModal = bootstrap.Modal.getInstance(modal);
    awaitingRfid = true;
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

  let currentPage = <?= $page ?>;
  let totalPages = <?= $totalPages ?>;
  const ITEMS_PER_PAGE = 10;

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

    if (currentPage > 1) {
      html += `<li class="page-item"><a href="#" class="page-link book-pagination-link" data-page="${currentPage - 1}">&laquo;</a></li>`;
    } else {
      html += `<li class="page-item disabled"><a class="page-link">&laquo;</a></li>`;
    }

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

  const BASE_URL = "<?= base_url() ?>";

  function escHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function renderBooks(booksOnPage) {
    if (!booksOnPage || booksOnPage.length === 0) {
      return `<div class="col">
        <div class="alert alert-warning">Buku tidak ditemukan.</div>
      </div>`;
    }

    return booksOnPage.map(function(book, index) {
      const imageUrl = (book.image && book.image.match(/^https?:\/\//))
        ? escHtml(book.image)
        : (book.image ? BASE_URL + escHtml(book.image) : 'https://placehold.co/400x600/e9ecef/6c757d?text=No+Image');

      const fetchPriority = index === 0 ? 'fetchpriority="high"' : 'loading="lazy"';
      const detailUrl = BASE_URL + 'books/detail?title=' + encodeURIComponent(book.title);

      return `
      <div class="col-lg-6 mb-4">
        <div class="row g-3">
          <div class="col-4">
            <div class="card shadow-sm h-100">
              <div class="card-body p-2">
                <img
                  src="${imageUrl}"
                  class="img-fluid book-thumbnail-img height-mobile-xl"
                  alt="${escHtml(book.title ?? 'Gambar Buku')}"
                  onerror="this.onerror=null; this.src='https://placehold.co/400x600/e9ecef/6c757d?text=Image+Error'; this.classList.add('img-error');"
                  ${fetchPriority}>
              </div>
            </div>
          </div>
          <div class="col-8">
            <h2 class="text-uppercase text-primary mt-2 mb-2 h-mobile-md">${escHtml(book.title)}</h2>
            <p class="mb-1 text-muted text-mobile-lg"><i class="bi bi-person"></i> ${escHtml(book.author)}</p>
            <p class="mb-1"><span class="badge bg-secondary badge-mobile-md">${escHtml(book.genre)}</span></p>
            <p class="mb-2 text-muted text-mobile-lg"><i class="bi bi-calendar"></i> ${escHtml(book.year)}</p>
            <div class="d-grid gap-1 d-md-flex justify-content-md-start">
              <a href="${detailUrl}" class="btn btn-secondary btn-mobile-md">
                <i class="bi bi-eye"></i> Detail
              </a>
            </div>
          </div>
        </div>
      </div>`;
    }).join('');
  }

  function loadBooks(page = 1) {
    let formData = $('#selectpickerForm').serialize();
    const searchValue = $('input[name="search"]').val();
    if (searchValue) {
      formData += '&search=' + encodeURIComponent(searchValue);
    }
    formData += '&page=' + page;

    $.ajax({
      url: "<?= base_url("books/filter") ?>",
      type: "GET",
      data: formData,
      dataType: "json",
      success: function(response) {
        $('#booksContainer .row').html(renderBooks(response.booksOnPage));

        totalPages = response.totalPages;
        currentPage = response.page;

        if (response.genres && response.genres.length > 0) {
          const currentGenres = $('#genreSelectpicker option').map(function() {
            return $(this).val();
          }).get();

          if (currentGenres.length !== response.genres.length ||
              !currentGenres.every((v, i) => v === response.genres[i])) {
            updateGenreSelectPicker(response.genres);
          }
        }

        renderBookPagination();
      },
      error: function(xhr, status, error) {
        $('#booksContainer .row').html('<div class="col"><div class="alert alert-danger">Error loading books. Please try again.</div></div>');
      }
    });
  }

  function updateGenreSelectPicker(genres) {
    const genreSelect = $('#genreSelectpicker');
    const currentSelected = genreSelect.val() || [];
    
    if (genreSelect.data('selectpicker')) {
      genreSelect.selectpicker('destroy');
    }
    
    genreSelect.empty();
    
    genres.forEach(function(genre) {
      const isSelected = currentSelected.includes(genre);
      genreSelect.append(
        $('<option></option>')
          .attr('value', genre)
          .prop('selected', isSelected)
          .text(genre)
      );
    });
    
    genreSelect.selectpicker({
      liveSearch: true,
      actionsBox: true
    });
    genreSelect.selectpicker('refresh');
  }

  $('#selectpickerForm, #searchForm').on('submit', function(e) {
    e.preventDefault();
    currentPage = 1;
    loadBooks(1);
  });

  $(document).ready(function() {
    renderBookPagination();
  });

  $('#exampleModal').on('hidden.bs.modal', function() {
    stopCamera();
    if (!awaitingRfid) {
      clearForm();
    }
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

<script src="<?= base_url("js/supabase-config.js") ?>"></script>

<script>
// class FormSyncManager {
//   constructor() {
//     this.channel = null;
//     this.sessionId = this.generateSessionId();
//     this.isTyping = false;
//     this.typingTimeout = null;
//     this.isSyncing = false;
//     this.lastBroadcastData = null;
//   }

//   generateSessionId() {
//     return 'session_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
//   }

//   init() {
//     if (!window.supabase_client) {
//       setTimeout(() => this.init(), 100);
//       return;
//     }
    
//     this.channel = window.supabase_client.channel('form-sync', {
//       config: {
//         broadcast: { self: false }
//       }
//     });

//     this.channel
//       .on('broadcast', { event: 'form-update' }, (payload) => {
//         this.handleFormUpdate(payload);
//       })
//       .on('broadcast', { event: 'ai-analysis-complete' }, (payload) => {
//         this.handleAIAnalysisUpdate(payload);
//       })
//       .on('broadcast', { event: 'kode-generated' }, (payload) => {
//         this.handleKodeUpdate(payload);
//       })
//       .subscribe((status) => {
//         if (status === 'SUBSCRIBED') {
//           this.showSyncStatus('ready');
//           this.attachFormListeners();
//           this.interceptAIAnalysis();
//           this.interceptKodeGeneration();
//         }
//       });
//   }

//   attachFormListeners() {
//     const formFields = {
//       kode_sekolah: document.getElementById('kode_sekolah'),
//       judul: document.getElementById('judul'),
//       pengarang: document.getElementById('pengarang'),
//       illustrator: document.getElementById('illustrator'),
//       publisher: document.getElementById('publisher'),
//       series: document.getElementById('series'),
//       kategori: document.getElementById('kategori'),
//       isbn: document.getElementById('isbn'),
//       ddcNumber: document.getElementById('ddcNumber'),
//       gambarLink: document.getElementById('gambarLink'),
//       quantity: document.getElementById('quantity'),
//       sinopsis: document.getElementById('sinopsis')
//     };

//     Object.keys(formFields).forEach(fieldName => {
//       const field = formFields[fieldName];
//       if (!field) return;

//       field.addEventListener('input', (e) => {
//         if (this.isSyncing) return;
//         clearTimeout(this.typingTimeout);
//         this.typingTimeout = setTimeout(() => {
//           this.broadcastFormData();
//         }, 300);
//       });

//       field.addEventListener('change', (e) => {
//         if (this.isSyncing) return;
//         this.broadcastFormData();
//       });
//     });
//   }

//   interceptAIAnalysis() {
//     const originalAnalyzeImage = window.analyzeImage;
    
//     window.analyzeImage = async (imageData, type) => {
//       try {
//         const result = await originalAnalyzeImage.call(window, imageData, type);
        
//         if (result && !result.error) {
//           this.broadcastAIAnalysis(result);
//         }
        
//         return result;
//       } catch (error) {
//         console.error('❌ AI Analysis error:', error);
//         throw error;
//       }
//     };
    
//     this.attachAnalyzeButtonListeners();
//   }

//   attachAnalyzeButtonListeners() {
//     const analyzeBtn = document.getElementById('analyzeBtn');
//     const analyzeUploadBtn = document.getElementById('analyzeUploadBtn');
    
//     if (analyzeBtn) {
//       const originalClick = analyzeBtn.onclick;
//       analyzeBtn.onclick = async (e) => {
//         if (originalClick) await originalClick.call(analyzeBtn, e);
        
//         setTimeout(() => {
//           this.broadcastFormData('ai-analysis');
//         }, 1000);
//       };
//     }
    
//     if (analyzeUploadBtn) {
//       const originalClick = analyzeUploadBtn.onclick;
//       analyzeUploadBtn.onclick = async (e) => {
//         if (originalClick) await originalClick.call(analyzeUploadBtn, e);
        
//         setTimeout(() => {
//           this.broadcastFormData('ai-analysis');
//         }, 1000);
//       };
//     }
//   }

//   interceptKodeGeneration() {
//     const generateBtn = document.getElementById('generateKodeBtn');
//     if (!generateBtn) return;
    
//     generateBtn.addEventListener('click', () => {
//       setTimeout(() => {
//         const kodeValue = document.getElementById('kode_sekolah')?.value;
//         if (kodeValue && kodeValue !== 'Loading...' && kodeValue !== 'Error') {
//           this.broadcastKodeGeneration(kodeValue);
//         }
//       }, 1500);
//     });
//   }

//   broadcastAIAnalysis(analysisData) {
//     const payload = {
//       type: 'ai-analysis',
//       sessionId: this.sessionId,
//       timestamp: Date.now(),
//       data: analysisData
//     };

//     this.channel.send({
//       type: 'broadcast',
//       event: 'ai-analysis-complete',
//       payload: payload
//     });

//     this.showSyncStatus('syncing');
//   }

//   handleAIAnalysisUpdate(payload) {
//     if (payload.payload.sessionId === this.sessionId) {
//       return;
//     }

//     this.isSyncing = true;
    
//     const data = payload.payload.data;
    
//     this.setFieldValue('judul', data.title || '');
//     this.setFieldValue('pengarang', data.author || '');
//     this.setFieldValue('illustrator', data.illustrator || '');
//     this.setFieldValue('publisher', data.publisher || '');
//     this.setFieldValue('series', data.series || '');
//     this.setFieldValue('isbn', data.isbn || '');
//     this.setFieldValue('ddcNumber', data.ddcNumber || data.ddc || '');
//     this.setFieldValue('quantity', data.quantity || '1');
//     this.setFieldValue('sinopsis', data.synopsis || '');
    
//     if (data.category || data.genre) {
//       this.setSelectValue('kategori', data.category || data.genre);
//     }
    
//     if (data.image || data.gambar) {
//       const imageUrl = data.image || data.gambar;
//       this.setFieldValue('gambarLink', imageUrl);
      
//       const previewImg = document.getElementById('previewImage');
//       if (previewImg) {
//         previewImg.src = imageUrl;
//         previewImg.style.display = 'block';
//       }
//     }
    
//     this.flashFormFields('ai-analysis');
//     this.showNotification('🤖 AI Analysis results synced from another device', 'success');
    
//     setTimeout(() => {
//       this.isSyncing = false;
//     }, 100);
//   }

//   broadcastKodeGeneration(kode) {
//     const payload = {
//       type: 'kode-generation',
//       sessionId: this.sessionId,
//       timestamp: Date.now(),
//       kode: kode
//     };

//     this.channel.send({
//       type: 'broadcast',
//       event: 'kode-generated',
//       payload: payload
//     });

//     this.showSyncStatus('syncing');
//   }

//   handleKodeUpdate(payload) {
//     if (payload.payload.sessionId === this.sessionId) {
//       return;
//     }

//     this.isSyncing = true;
    
//     const kode = payload.payload.kode;
//     this.setFieldValue('kode_sekolah', kode);
    
//     this.flashField('kode_sekolah');
//     this.showNotification(`🔢 Kode Sekolah synced: ${kode}`, 'info');
    
//     setTimeout(() => {
//       this.isSyncing = false;
//     }, 100);
//   }

//   broadcastFormData(source = 'manual') {
//     const formData = {
//       kode_sekolah: document.getElementById('kode_sekolah')?.value || '',
//       judul: document.getElementById('judul')?.value || '',
//       pengarang: document.getElementById('pengarang')?.value || '',
//       illustrator: document.getElementById('illustrator')?.value || '',
//       publisher: document.getElementById('publisher')?.value || '',
//       series: document.getElementById('series')?.value || '',
//       kategori: document.getElementById('kategori')?.value || '',
//       isbn: document.getElementById('isbn')?.value || '',
//       ddcNumber: document.getElementById('ddcNumber')?.value || '',
//       gambarLink: document.getElementById('gambarLink')?.value || '',
//       quantity: document.getElementById('quantity')?.value || '1',
//       sinopsis: document.getElementById('sinopsis')?.value || '',
//       sessionId: this.sessionId,
//       timestamp: Date.now(),
//       source: source
//     };

//     if (JSON.stringify(formData) === JSON.stringify(this.lastBroadcastData)) {
//       return;
//     }
    
//     this.lastBroadcastData = formData;

//     this.channel.send({
//       type: 'broadcast',
//       event: 'form-update',
//       payload: formData
//     });

//     this.showSyncStatus('syncing');
//   }

//   handleFormUpdate(payload) {
//     if (payload.payload.sessionId === this.sessionId) {
//       return;
//     }

//     this.isSyncing = true;

//     const data = payload.payload;
    
//     this.setFieldValue('kode_sekolah', data.kode_sekolah);
//     this.setFieldValue('judul', data.judul);
//     this.setFieldValue('pengarang', data.pengarang);
//     this.setFieldValue('illustrator', data.illustrator);
//     this.setFieldValue('publisher', data.publisher);
//     this.setFieldValue('series', data.series);
//     this.setSelectValue('kategori', data.kategori);
//     this.setFieldValue('isbn', data.isbn);
//     this.setFieldValue('ddcNumber', data.ddcNumber);
//     this.setFieldValue('gambarLink', data.gambarLink);
//     this.setFieldValue('quantity', data.quantity);
//     this.setFieldValue('sinopsis', data.sinopsis);

//     if (data.gambarLink) {
//       const previewImg = document.getElementById('previewImage');
//       if (previewImg) {
//         previewImg.src = data.gambarLink;
//         previewImg.style.display = 'block';
//       }
//     }

//     this.flashFormFields(data.source);
//     this.showSyncStatus('synced');

//     setTimeout(() => {
//       this.isSyncing = false;
//     }, 100);
//   }

//   setFieldValue(fieldId, value) {
//     const field = document.getElementById(fieldId);
//     if (!field || field.value === value) return;

//     field.value = value;

//     const event = new Event('change', { bubbles: true });
//     field.dispatchEvent(event);
//   }

//   setSelectValue(fieldId, value) {
//     const select = document.getElementById(fieldId);
//     selectOrCreateCategoryOption(select, value);
//   }

//   flashFormFields(source = 'manual') {
//     const modal = document.querySelector('#exampleModal .modal-body');
//     if (!modal) return;
    
//     const colors = {
//       'ai-analysis': '#e7f1ff',
//       'manual': '#fff3cd',
//       'kode-generation': '#d1e7dd'
//     };
    
//     const color = colors[source] || '#f8f9fa';
    
//     modal.style.transition = 'background-color 0.5s ease';
//     modal.style.backgroundColor = color;
    
//     setTimeout(() => {
//       modal.style.backgroundColor = '';
//     }, 1000);
//   }

//   flashField(fieldId) {
//     const field = document.getElementById(fieldId);
//     if (!field) return;
    
//     field.style.transition = 'all 0.3s ease';
//     field.style.backgroundColor = '#ffd700';
//     field.style.transform = 'scale(1.02)';
    
//     setTimeout(() => {
//       field.style.backgroundColor = '';
//       field.style.transform = '';
//     }, 500);
//   }

//   showSyncStatus(status) {
//     let indicator = document.getElementById('formSyncStatus');
    
//     if (!indicator) {
//       indicator = document.createElement('div');
//       indicator.id = 'formSyncStatus';
//       indicator.style.cssText = `
//         position: fixed;
//         bottom: 20px;
//         right: 20px;
//         padding: 10px 16px;
//         border-radius: 20px;
//         font-size: 12px;
//         font-weight: 500;
//         z-index: 9999;
//         box-shadow: 0 2px 8px rgba(0,0,0,0.15);
//         transition: all 0.3s ease;
//       `;
//       document.body.appendChild(indicator);
//     }

//     if (status === 'ready') {
//       indicator.style.background = '#d1e7dd';
//       indicator.style.color = '#0f5132';
//       indicator.innerHTML = '🟢 Form sync ready';
      
//       setTimeout(() => {
//         indicator.style.opacity = '0.5';
//       }, 2000);
//     } else if (status === 'syncing') {
//       indicator.style.background = '#cfe2ff';
//       indicator.style.color = '#084298';
//       indicator.style.opacity = '1';
//       indicator.innerHTML = '🔄 Syncing...';
//     } else if (status === 'synced') {
//       indicator.style.background = '#d1e7dd';
//       indicator.style.color = '#0f5132';
//       indicator.style.opacity = '1';
//       indicator.innerHTML = '✅ Synced!';
      
//       setTimeout(() => {
//         indicator.style.opacity = '0.5';
//       }, 1000);
//     }
//   }

//   showNotification(message, type = 'info') {
//     let toastContainer = document.getElementById('toastContainer');
    
//     if (!toastContainer) {
//       toastContainer = document.createElement('div');
//       toastContainer.id = 'toastContainer';
//       toastContainer.style.cssText = `
//         position: fixed;
//         top: 70px;
//         right: 20px;
//         z-index: 9999;
//       `;
//       document.body.appendChild(toastContainer);
//     }
    
//     const toastId = 'toast-' + Date.now();
//     const bgColor = {
//       'success': 'success',
//       'info': 'info',
//       'warning': 'warning',
//       'error': 'danger'
//     }[type] || 'primary';
    
//     const toastHTML = `
//       <div id="${toastId}" class="toast" role="alert" style="min-width: 250px;">
//         <div class="toast-header bg-${bgColor} text-white">
//           <strong class="me-auto">📡 Real-time Sync</strong>
//           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
//         </div>
//         <div class="toast-body">
//           ${message}
//         </div>
//       </div>
//     `;
    
//     toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
//     const toastElement = document.getElementById(toastId);
//     const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 4000 });
//     toast.show();
    
//     toastElement.addEventListener('hidden.bs.toast', () => {
//       toastElement.remove();
//     });
//   }

//   disconnect() {
//     if (this.channel) {
//       window.supabase_client.removeChannel(this.channel);
//     }
//   }
// }

// let formSync = null;

// document.addEventListener('DOMContentLoaded', () => {
//   setTimeout(() => {
//     formSync = new FormSyncManager();
//     formSync.init();
//     window.formSync = formSync;
//   }, 1000);
// });

// document.getElementById('exampleModal')?.addEventListener('shown.bs.modal', () => {
//   if (formSync && !formSync.channel) {
//     formSync.init();
//   }
// });

// window.addEventListener('beforeunload', () => {
//   if (formSync) {
//     formSync.disconnect();
//   }
// });
</script>

<style>
  /* #formSyncStatus {
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

  @media (max-width: 768px) {
    #formSyncStatus {
      bottom: 10px;
      right: 10px;
      font-size: 10px;
      padding: 8px 12px;
    }
  }

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
  } */
</style>
<?= $this->endSection() ?>
