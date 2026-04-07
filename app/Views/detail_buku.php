<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<style>
  @media (min-width: 992px) { 
    #bookInformationTitle, #synopsisTitle {
      font-size: 1.75rem;
    }

    #rfidUidsTitle {
      font-size: 1.25rem;
    }
  }
</style>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb mt-4 mt-lg-3">
    <li class="breadcrumb-item">
      <a href="<?= base_url() ?>" class="text-mobile-sm">Katalog</a>
    </li>
    <li class="breadcrumb-item active text-mobile-sm" aria-current="page">Detail</li>
  </ol>
</nav>

<h1 class="mb-4 h-mobile-lg">Detail Buku</h1>

<div class="card" style="border-style: dashed;">
  <div class="card-body">
    <div class="row">
      <div class="col-auto">
        <img 
          src="<?= !empty($book['image']) ? esc($book['image']) : base_url('uploads/default-book.jpg') ?>" 
          class="img-fluid rounded shadow" 
          alt="<?= esc($book['title'] ?? 'Gambar Buku') ?>"
          style="max-width: 300px; max-height: 450px; object-fit: cover;"
          onerror="this.src='https://placehold.co/300x450?text=No+Image'">
      </div>
      <div class="col">
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
          <h2 class="mb-0 text-mobile-xl" id="bookInformationTitle">Informasi Buku</h2>
          <?php if (!empty($book['available'])): ?>
            <span class="badge rounded-pill text-bg-success badge-mobile-sm">
              <i class="bi bi-check-circle"></i> Available
            </span>
          <?php else: ?>
            <span class="badge rounded-pill text-bg-danger badge-mobile-sm">
              <i class="bi bi-x-circle"></i> Not Available
            </span>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Kode Sekolah</h3>
          <p class="mb-2 fw-bold text-mobile-xs"><?= esc($book['code'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Judul</h3>
          <p class="mb-2 fw-bold text-mobile-xs"><?= esc($book['title'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Kategori</h3>
          <p class="mb-2">
            <span class="badge bg-primary badge-mobile-sm"><?= esc($book['genre'] ?? '-') ?></span>
          </p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Pengarang</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['author'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Illustrator</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['illustrator'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Publisher</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['publisher'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Series</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['series'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">ISBN</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['isbn'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">DDC Number</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['ddc_number'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Tahun</h3>
          <p class="mb-2 text-mobile-xs"><?= esc($book['year'] ?? '-') ?></p>
        </div>

        <div class="mb-3">
          <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Jumlah</h3>
          <p class="mb-2 text-mobile-xs">
            <span class="badge bg-info badge-mobile-sm"><?= esc($book['quantity'] ?? '0') ?> eksemplar</span>
          </p>
        </div>

        <?php if (empty($book['is_in_class'])): ?>
          <div class="mb-3">
            <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Posisi Rak</h3>
            <p class="mb-2 text-mobile-xs"><?= esc($book['shelf_position'] ?? 'Tidak ditentukan') ?></p>
          </div>
        <?php endif; ?>

        <!-- ===== TABEL PEMINJAM ===== -->
        <?php if (!empty($borrowers)): ?>
        <div class="mb-3">
          <h3 class="fs-5 mb-2 text-muted text-mobile-xs">
            <i class="bi bi-person-check"></i> Dipinjam Oleh
          </h3>
          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="text-mobile-xs">Nama Peminjam</th>
                  <th class="text-mobile-xs">Tgl Pinjam</th>
                  <th class="text-mobile-xs">Due Date</th>
                  <th class="text-mobile-xs">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($borrowers as $item): ?>
                  <?php 
                    $tx = $item['transaction'];
                    $user = $item['user'];
                    $tanggal = $tx['tanggal'] ?? '-';
                    $dueDate = $tx['due_date'] ?? '-';
                    $nama = esc($user['nama'] ?? 'Unknown');
                    $isLate = strtotime(date('Y-m-d')) > strtotime($dueDate);
                    $statusClass = $isLate ? 'bg-danger' : 'bg-warning';
                    $statusText = $isLate ? 'TERLAMBAT' : 'AKTIF';
                  ?>
                  <tr>
                    <td class="text-mobile-xs"><?= $nama ?></td>
                    <td class="text-mobile-xs"><?= $tanggal ?></td>
                    <td class="text-mobile-xs"><?= $dueDate ?></td>
                    <td class="text-mobile-xs">
                      <span class="badge <?= $statusClass ?>">
                        <?= $statusText ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
          <i class="bi bi-info-circle"></i> Buku tidak sedang dipinjam siapapun
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (!empty($book['notes'])): ?>
          <div class="mb-3">
            <h3 class="fs-5 mb-1 text-muted text-mobile-xs">Catatan</h3>
            <p class="mb-2 text-mobile-xs"><?= esc($book['notes']) ?></p>
          </div>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <div class="card bg-light">
          <div class="card-body">
            <h2 class="mb-3 text-mobile-xl" id="synopsisTitle">Sinopsis</h2>
            <p class="text-justify text-mobile-xs"><?= nl2br(esc($book['synopsis'] ?? 'Tidak ada sinopsis tersedia.')) ?></p>
          </div>
        </div>

        <?php if (!empty($book['uid']) && is_array($book['uid'])): ?>
          <div class="card bg-light mt-3">
            <div class="card-body">
              <h3 class="mb-2 text-mobile-md" id="rfidUidsTitle">
                <i class="bi bi-credit-card"></i> RFID UIDs
              </h3>
              <ul class="list-unstyled mb-0">
                <?php foreach ($book['uid'] as $uid): ?>
                  <li class="mb-1">
                    <code class="bg-white px-2 py-1 rounded text-mobile-xs"><?= esc($uid) ?></code>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="mt-3 mb-5">
  <a href="<?= base_url() ?>" class="btn btn-secondary btn-mobile-sm">
    <i class="bi bi-arrow-left"></i> Kembali ke Katalog
  </a>
</div>

<?= $this->endSection() ?>