<!-- Tambah Buku: isolated copy of the catalog add-book workflow. -->
<div class="modal fade" id="managementAddBookModal" tabindex="-1" aria-labelledby="managementAddBookModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title text-mobile-xl" id="managementAddBookModalLabel">Tambah Buku</h1>
        <button type="button" class="btn-close text-mobile-sm" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="managementTambahSection" style="display: none;">
          <div class="row mb-3">
            <div class="col">
              <label for="managementKodeSekolah" class="form-label required text-mobile-sm">
                Kode Sekolah
                <span class="badge bg-info">Auto/Manual</span>
              </label>
              <div class="input-group">
                <input type="text" class="form-control form-control-mobile" id="managementKodeSekolah" placeholder="Auto-generate atau ketik manual">
                <button class="btn btn-outline-secondary" type="button" id="managementGenerateKodeBtn" title="Generate Kode Baru">
                  <i class="bi bi-arrow-clockwise"></i> Auto
                </button>
              </div>
              <small class="form-text text-muted text-mobile-xs">Format auto: {nomor}/YCB-CB/{bulan}/{tahun} atau ketik manual</small>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="managementJudul" class="form-label required text-mobile-sm">Judul</label>
              <input type="text" class="form-control form-control-mobile" id="managementJudul">
            </div>
            <div class="col">
              <label for="managementPengarang" class="form-label required text-mobile-sm">Pengarang</label>
              <input type="text" class="form-control form-control-mobile" id="managementPengarang">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="managementIllustrator" class="form-label text-mobile-sm">Illustrator</label>
              <input type="text" class="form-control form-control-mobile" id="managementIllustrator">
            </div>
            <div class="col">
              <label for="managementPublisher" class="form-label text-mobile-sm">Publisher</label>
              <input type="text" class="form-control form-control-mobile" id="managementPublisher">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="managementSeries" class="form-label text-mobile-sm">Series</label>
              <input type="text" class="form-control form-control-mobile" id="managementSeries">
            </div>
            <div class="col">
              <label for="managementKategori" class="form-label required text-mobile-sm">Kategori</label>
              <select class="form-select form-control-mobile" id="managementKategori" name="managementKategori">
                <option value="" selected disabled>Pilih kategori</option>
                <?php foreach ($genres as $genre): ?>
                  <option value="<?= esc($genre) ?>"><?= esc($genre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="managementIsbn" class="form-label text-mobile-sm">ISBN</label>
              <input type="text" class="form-control form-control-mobile" id="managementIsbn">
            </div>
            <div class="col">
              <label for="managementDdcNumber" class="form-label text-mobile-sm">DDC Number</label>
              <input type="text" class="form-control form-control-mobile" id="managementDdcNumber">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col">
              <label for="managementGambarLink" class="form-label required text-mobile-sm">Image</label>
              <ul class="nav nav-tabs nav-tabs-mobile mb-2" id="managementImageInputTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="management-url-tab" data-bs-toggle="tab" data-bs-target="#management-url-panel" type="button" role="tab">
                    <i class="bi bi-link-45deg"></i> URL
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="management-camera-tab" data-bs-toggle="tab" data-bs-target="#management-camera-panel" type="button" role="tab">
                    <i class="bi bi-camera"></i> Camera
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="management-upload-tab" data-bs-toggle="tab" data-bs-target="#management-upload-panel" type="button" role="tab">
                    <i class="bi bi-upload"></i> Upload
                  </button>
                </li>
              </ul>

              <div class="tab-content" id="managementImageInputTabContent">
                <div class="tab-pane fade show active" id="management-url-panel" role="tabpanel">
                  <div class="input-group">
                    <input type="text" class="form-control form-control-mobile" id="managementGambarLink" placeholder="Paste image URL here">
                    <button class="btn btn-primary" type="button" id="managementAnalyzeBtn">
                      <i class="bi bi-search"></i> Analyze
                    </button>
                  </div>
                </div>
                <div class="tab-pane fade" id="management-camera-panel" role="tabpanel">
                  <div class="camera-container">
                    <video id="managementCameraPreview" autoplay playsinline style="width: 100%; max-height: 300px; display: none; border-radius: 8px; background: #000;"></video>
                    <canvas id="managementCameraCanvas" style="display: none;"></canvas>
                    <div class="d-grid gap-2 mb-2">
                      <button class="btn btn-outline-primary btn-mobile-lg" type="button" id="managementStartCameraBtn">
                        <i class="bi bi-camera-video"></i> Start Camera
                      </button>
                      <button class="btn btn-success" type="button" id="managementCaptureBtn" style="display: none;">
                        <i class="bi bi-camera"></i> Capture Photo
                      </button>
                      <button class="btn btn-outline-secondary" type="button" id="managementStopCameraBtn" style="display: none;">
                        <i class="bi bi-stop-circle"></i> Stop Camera
                      </button>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="management-upload-panel" role="tabpanel">
                  <div class="input-group">
                    <input type="file" class="form-control form-control-mobile" id="managementFileUpload" accept="image/*">
                    <button class="btn btn-primary" type="button" id="managementAnalyzeUploadBtn">
                      <i class="bi bi-search"></i> Analyze
                    </button>
                  </div>
                  <small class="form-text text-muted">Accepted formats: JPG, PNG, WEBP</small>
                </div>
              </div>
            </div>
            <div class="col">
              <label for="managementQuantity" class="form-label required text-mobile-sm">Quantity</label>
              <input type="number" class="form-control form-control-mobile" id="managementQuantity" value="1">
            </div>
          </div>

          <div class="mb-3">
            <label for="managementSinopsis" class="form-label text-mobile-sm">Sinopsis</label>
            <textarea class="form-control form-control-mobile" id="managementSinopsis" rows="4" placeholder="Tuliskan sinopsis buku di sini..."></textarea>
          </div>
          <div class="mb-3">
            <img id="managementPreviewImage" src="" alt="Preview" style="max-width: 100%; max-height: 300px; display:none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-mobile-md" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary btn-mobile-md" id="managementSubmitBtn">Kirim</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="managementRfidModal" tabindex="-1" aria-labelledby="managementRfidModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-mobile-xl" id="managementRfidModalLabel">
          <i class="bi bi-credit-card-2-front"></i> Scan RFID Card
        </h5>
        <button type="button" class="btn-close btn-close-white text-mobile-sm" data-bs-dismiss="modal" aria-label="Close" id="managementRfidModalClose"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <i class="bi bi-upc-scan" style="font-size: 3rem; color: #0d6efd;"></i>
          <p class="mt-2 text-muted text-mobile-xs">Silakan scan kartu RFID sekarang</p>
        </div>
        <div class="mb-3">
          <label for="managementRfidUid" class="form-label fw-bold text-mobile-sm">RFID UID <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-lg text-center" id="managementRfidUid" placeholder="Scan atau ketik RFID UID" autocomplete="off" style="letter-spacing: 2px; font-family: monospace;">
          <div class="form-text text-mobile-xs">
            <i class="bi bi-info-circle"></i> RFID akan otomatis terdeteksi saat di-scan
          </div>
        </div>
        <div class="card bg-light">
          <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted text-mobile-xs">Ringkasan Buku:</h6>
            <p class="mb-1 text-mobile-xs"><strong>Judul:</strong> <span id="managementBookSummaryTitle">-</span></p>
            <p class="mb-1 text-mobile-xs"><strong>Pengarang:</strong> <span id="managementBookSummaryAuthor">-</span></p>
            <p class="mb-0 text-mobile-xs"><strong>Kode Sekolah:</strong> <span id="managementBookSummaryKode">-</span></p>
          </div>
        </div>
        <div id="managementProgressSteps" class="mt-3" style="display: none;">
          <h6 class="mb-2"><i class="bi bi-hourglass-split"></i> Progress:</h6>
          <div class="progress-step" id="managementStep1"><i class="bi bi-circle"></i> <span>Validating RFID...</span></div>
          <div class="progress-step" id="managementStep2"><i class="bi bi-circle"></i> <span>Uploading image to Cloudinary...</span></div>
          <div class="progress-step" id="managementStep3"><i class="bi bi-circle"></i> <span>Saving to database...</span></div>
        </div>
        <div id="managementCloudinaryResult" class="mt-3" style="display: none;">
          <div class="alert alert-success mb-0">
            <strong><i class="bi bi-check-circle"></i> Cloudinary Upload Successful!</strong>
            <div class="mt-2">
              <small class="text-muted">URL:</small>
              <div class="cloudinary-link" id="managementCloudinaryUrl"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-mobile-md" data-bs-dismiss="modal" id="managementRfidCancelBtn">
          <i class="bi bi-x-circle"></i> Batal
        </button>
        <button type="button" class="btn btn-primary btn-mobile-md" id="managementConfirmRfidBtn" disabled>
          <i class="bi bi-check-circle"></i> Konfirmasi & Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const addModalElement = document.getElementById('managementAddBookModal');
  const trigger = document.getElementById('managementTambahBuku');
  if (!addModalElement || !trigger) return;

  const addModal = bootstrap.Modal.getOrCreateInstance(addModalElement);
  const rfidModalElement = document.getElementById('managementRfidModal');
  const rfidModal = bootstrap.Modal.getOrCreateInstance(rfidModalElement);
  const tambahSection = document.getElementById('managementTambahSection');
  const previewImage = document.getElementById('managementPreviewImage');
  const cameraPreview = document.getElementById('managementCameraPreview');
  const cameraCanvas = document.getElementById('managementCameraCanvas');
  const startCameraBtn = document.getElementById('managementStartCameraBtn');
  const captureBtn = document.getElementById('managementCaptureBtn');
  const stopCameraBtn = document.getElementById('managementStopCameraBtn');
  const analyzeBtn = document.getElementById('managementAnalyzeBtn');
  const analyzeUploadBtn = document.getElementById('managementAnalyzeUploadBtn');
  const rfidInput = document.getElementById('managementRfidUid');
  const confirmRfidBtn = document.getElementById('managementConfirmRfidBtn');
  const progressSteps = document.getElementById('managementProgressSteps');
  const cloudinaryResult = document.getElementById('managementCloudinaryResult');
  const cloudinaryUrl = document.getElementById('managementCloudinaryUrl');

  let cameraStream = null;
  let capturedImageData = null;
  let pendingBookData = null;
  let saveCompleted = false;
  let awaitingRfid = false;

  const fieldIds = {
    kode_sekolah: 'managementKodeSekolah',
    judul: 'managementJudul',
    pengarang: 'managementPengarang',
    illustrator: 'managementIllustrator',
    publisher: 'managementPublisher',
    series: 'managementSeries',
    kategori: 'managementKategori',
    isbn: 'managementIsbn',
    ddcNumber: 'managementDdcNumber',
    gambar: 'managementGambarLink',
    quantity: 'managementQuantity',
    sinopsis: 'managementSinopsis'
  };

  const field = (name) => document.getElementById(fieldIds[name]);
  const CLOUDINARY_CONFIG = {
    cloud_name: 'dqx1ofl8j',
    upload_preset: 'ml_default'
  };

  function setProgressStep(stepId, status) {
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

  function resetProgressSteps() {
    ['managementStep1', 'managementStep2', 'managementStep3'].forEach((stepId) => {
      const step = document.getElementById(stepId);
      step.classList.remove('active', 'completed', 'failed');
      step.querySelector('i').className = 'bi bi-circle';
    });
  }

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

  async function uploadToCloudinary(imageData) {
    const formData = new FormData();
    let fileToUpload = imageData;
    if (imageData.startsWith('data:')) {
      const response = await fetch(imageData);
      fileToUpload = await response.blob();
    }
    const filename = `book_${Date.now()}_${Math.random().toString(36).substring(7)}`;
    formData.append('file', fileToUpload);
    formData.append('upload_preset', CLOUDINARY_CONFIG.upload_preset);
    formData.append('public_id', filename);
    formData.append('folder', 'books');

    const response = await fetch(
      `https://api.cloudinary.com/v1_1/${CLOUDINARY_CONFIG.cloud_name}/image/upload`,
      { method: 'POST', body: formData }
    );
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error?.message || `HTTP ${response.status}: Upload failed`);
    }
    return data.secure_url;
  }

  function stopCamera() {
    if (cameraStream) {
      cameraStream.getTracks().forEach((track) => track.stop());
      cameraStream = null;
    }
    cameraPreview.style.display = 'none';
    cameraPreview.srcObject = null;
    startCameraBtn.style.display = 'block';
    captureBtn.style.display = 'none';
    stopCameraBtn.style.display = 'none';
  }

  function clearForm() {
    addModalElement.querySelectorAll('input, select, textarea').forEach((input) => {
      if (input.type === 'file') {
        input.value = '';
      } else if (input.tagName === 'SELECT') {
        input.selectedIndex = 0;
      } else {
        input.value = '';
      }
    });
    field('quantity').value = '1';
    previewImage.src = '';
    previewImage.style.display = 'none';
    capturedImageData = null;
  }

  async function loadNextKodeSekolah() {
    field('kode_sekolah').value = 'Loading...';
    try {
      const response = await fetch('<?= base_url("books/next-kode") ?>');
      const data = await response.json();
      field('kode_sekolah').value = data.success ? data.kode_sekolah : 'Error';
    } catch (error) {
      field('kode_sekolah').value = 'Error';
    }
  }

  async function analyzeImage(imageData, type) {
    const originalText = analyzeBtn.innerHTML;
    analyzeBtn.disabled = true;
    analyzeUploadBtn.disabled = true;
    analyzeBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Analyzing...';
    try {
      const apiUrl = '<?= base_url("api/analyze-image") ?>';
      let response;
      if (type === 'url') {
        response = await fetch(`${apiUrl}?image_url=${encodeURIComponent(imageData)}`);
      } else {
        const base64String = imageData.startsWith('data:')
          ? imageData.split(',')[1]
          : imageData;
        response = await fetch(apiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ type: 'base64', image_data: base64String })
        });
      }
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.error) {
        alert('Gagal menganalisis gambar: ' + data.error);
        return data;
      }
      if (data.title === 'BUKAN BUKU' || !data.title) {
        alert('Gambar bukan sampul buku atau tidak dapat dianalisis');
        return data;
      }

      const values = {
        judul: data.title,
        pengarang: data.author,
        illustrator: data.illustrator,
        publisher: data.publisher,
        series: data.series,
        isbn: data.isbn,
        ddcNumber: data.ddcNumber || data.ddc,
        quantity: data.quantity || 1,
        sinopsis: data.synopsis
      };
      Object.entries(values).forEach(([name, value]) => {
        if (value && value !== 'NOT FOUND') field(name).value = value;
      });
      selectOrCreateCategoryOption(
        field('kategori'),
        data.category || data.genre
      );
      alert('Analisis berhasil! Field telah diisi otomatis.\n\nGambar akan diupload ke Cloudinary setelah RFID dikonfirmasi.');
      return data;
    } catch (error) {
      alert('Terjadi kesalahan saat menganalisis gambar: ' + error.message);
      throw error;
    } finally {
      analyzeBtn.disabled = false;
      analyzeUploadBtn.disabled = false;
      analyzeBtn.innerHTML = originalText;
    }
  }

  trigger.addEventListener('click', () => {
    saveCompleted = false;
    tambahSection.style.display = 'block';
    clearForm();
    setTimeout(loadNextKodeSekolah, 300);
  });

  document.getElementById('managementGenerateKodeBtn').addEventListener('click', loadNextKodeSekolah);

  field('gambar').addEventListener('input', () => {
    const url = field('gambar').value.trim();
    previewImage.src = url;
    previewImage.style.display = url ? 'block' : 'none';
  });

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
    } catch (error) {
      alert('Unable to access camera. Please check permissions.');
    }
  });

  captureBtn.addEventListener('click', async () => {
    const context = cameraCanvas.getContext('2d');
    const scale = Math.min(1, 1024 / cameraPreview.videoWidth);
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

  analyzeBtn.addEventListener('click', async () => {
    const imageUrl = field('gambar').value.trim();
    if (!imageUrl) {
      alert('Masukkan link gambar terlebih dahulu.');
      return;
    }
    previewImage.src = imageUrl;
    previewImage.style.display = 'block';
    await analyzeImage(imageUrl, 'url');
  });

  analyzeUploadBtn.addEventListener('click', () => {
    const file = document.getElementById('managementFileUpload').files[0];
    if (!file) {
      alert('Pilih file gambar terlebih dahulu.');
      return;
    }
    const reader = new FileReader();
    reader.onload = (event) => {
      const image = new Image();
      image.onload = async () => {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const scale = Math.min(1, 1024 / image.width);
        canvas.width = image.width * scale;
        canvas.height = image.height * scale;
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        capturedImageData = canvas.toDataURL('image/jpeg', 0.7);
        previewImage.src = capturedImageData;
        previewImage.style.display = 'block';
        await analyzeImage(capturedImageData, 'base64');
      };
      image.src = event.target.result;
    };
    reader.readAsDataURL(file);
  });

  document.getElementById('managementSubmitBtn').addEventListener('click', () => {
    const bookData = {
      kode_sekolah: field('kode_sekolah').value || '',
      judul: field('judul').value || '',
      pengarang: field('pengarang').value || '',
      illustrator: field('illustrator').value || '',
      publisher: field('publisher').value || '',
      series: field('series').value || '',
      kategori: field('kategori').value || '',
      isbn: field('isbn').value || '',
      ddcNumber: field('ddcNumber').value || '',
      gambar: capturedImageData || field('gambar').value || '',
      quantity: field('quantity').value,
      sinopsis: field('sinopsis').value || ''
    };
    if (!bookData.judul) {
      alert('Judul harus diisi!');
      field('judul').focus();
      return;
    }
    if (!bookData.pengarang) {
      alert('Pengarang harus diisi!');
      field('pengarang').focus();
      return;
    }
    if (!bookData.kode_sekolah || bookData.kode_sekolah === 'Error') {
      alert('Kode Sekolah harus di-generate terlebih dahulu!');
      field('kode_sekolah').focus();
      return;
    }
    if (!bookData.kategori) {
      alert('Kategori harus dipilih!');
      field('kategori').focus();
      return;
    }
    if (!bookData.quantity || Number.parseInt(bookData.quantity, 10) < 1) {
      alert('Quantity harus berupa angka dan minimal 1!');
      field('quantity').focus();
      return;
    }

    pendingBookData = bookData;
    document.getElementById('managementBookSummaryTitle').textContent = bookData.judul;
    document.getElementById('managementBookSummaryAuthor').textContent = bookData.pengarang;
    document.getElementById('managementBookSummaryKode').textContent = bookData.kode_sekolah;
    awaitingRfid = true;
    addModal.hide();
    rfidModal.show();
  });

  rfidInput.addEventListener('input', () => {
    confirmRfidBtn.disabled = rfidInput.value.trim().length === 0;
  });

  rfidInput.addEventListener('keypress', (event) => {
    if (event.key === 'Enter' && !confirmRfidBtn.disabled) {
      confirmRfidBtn.click();
    }
  });

  rfidModalElement.addEventListener('shown.bs.modal', () => rfidInput.focus());
  rfidModalElement.addEventListener('hidden.bs.modal', () => {
    rfidInput.value = '';
    rfidInput.disabled = false;
    confirmRfidBtn.disabled = true;
    progressSteps.style.display = 'none';
    cloudinaryResult.style.display = 'none';
    resetProgressSteps();
    pendingBookData = null;
    awaitingRfid = false;
    if (!saveCompleted) addModal.show();
  });

  confirmRfidBtn.addEventListener('click', async () => {
    if (!pendingBookData) {
      alert('Data buku tidak ditemukan');
      return;
    }
    const rfidValue = rfidInput.value.trim();
    if (!rfidValue) {
      alert('RFID UID harus diisi!');
      rfidInput.focus();
      return;
    }

    const cancelBtn = document.getElementById('managementRfidCancelBtn');
    const closeBtn = document.getElementById('managementRfidModalClose');
    rfidInput.disabled = true;
    confirmRfidBtn.disabled = true;
    cancelBtn.disabled = true;
    closeBtn.disabled = true;
    progressSteps.style.display = 'block';
    cloudinaryResult.style.display = 'none';

    try {
      setProgressStep('managementStep1', 'active');
      await new Promise((resolve) => setTimeout(resolve, 500));
      pendingBookData.rfid_uid = rfidValue;
      setProgressStep('managementStep1', 'completed');

      let uploadedImageUrl = null;
      if (capturedImageData || pendingBookData.gambar) {
        setProgressStep('managementStep2', 'active');
        uploadedImageUrl = await uploadToCloudinary(
          capturedImageData || pendingBookData.gambar
        );
        pendingBookData.gambar = uploadedImageUrl;
        cloudinaryUrl.textContent = uploadedImageUrl;
        cloudinaryResult.style.display = 'block';
        setProgressStep('managementStep2', 'completed');
      } else {
        setProgressStep('managementStep2', 'completed');
      }

      setProgressStep('managementStep3', 'active');
      const response = await fetch('<?= base_url("books/add") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(pendingBookData)
      });
      if (!response.ok) {
        const body = await response.text();
        if (response.status === 409) {
          throw new Error('Conflict: RFID atau data buku sudah ada di database');
        }
        throw new Error(`Server error: ${response.status} - ${body}`);
      }
      const data = await response.json();
      if (!data.success) {
        throw new Error(data.message || 'Gagal menyimpan ke database');
      }

      setProgressStep('managementStep3', 'completed');
      await new Promise((resolve) => setTimeout(resolve, 500));
      saveCompleted = true;
      rfidModal.hide();
      addModal.hide();
      let successMessage = `Buku berhasil ditambahkan!\n\nRFID: ${rfidValue}`;
      if (uploadedImageUrl) successMessage += `\nGambar: ${uploadedImageUrl}`;
      alert(successMessage);
      location.reload();
    } catch (error) {
      const activeStep = document.querySelector('#managementProgressSteps .progress-step.active');
      if (activeStep) setProgressStep(activeStep.id, 'failed');
      alert('Gagal menambahkan buku: ' + error.message);
    } finally {
      rfidInput.disabled = false;
      confirmRfidBtn.disabled = rfidInput.value.trim().length === 0;
      cancelBtn.disabled = false;
      closeBtn.disabled = false;
    }
  });

  addModalElement.addEventListener('hidden.bs.modal', () => {
    stopCamera();
    if (!awaitingRfid) clearForm();
  });
});
</script>
