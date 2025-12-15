<div id="booksContainer">
  <div class="row">
    <?php if (empty($booksOnPage)): ?>
      <div class="col">
        <div class="alert alert-warning">Buku tidak ditemukan.</div>
      </div>
    <?php else: ?>
      <?php foreach ($booksOnPage as $book): ?>
      <div class="col-lg-6 mb-4">
        <div class="row g-3">
          <div class="col-4">
            <div class="card shadow-sm h-100">
              <div class="card-body p-2">
                <?php
                  // Handle empty or invalid image URLs
                  $imageUrl = !empty($book['image']) ? $book['image'] : 'https://placehold.co/400x600/e9ecef/6c757d?text=No+Image';
                  
                  // Check if it's a relative URL and add base_url
                  if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    $imageUrl = base_url($imageUrl);
                  }
                ?>
                <img 
                  src="<?= esc($imageUrl) ?>" 
                  class="img-fluid book-thumbnail-img height-mobile-xl" 
                  alt="<?= esc($book['title'] ?? 'Gambar Buku') ?>"
                  onerror="this.onerror=null; this.src='https://placehold.co/400x600/e9ecef/6c757d?text=Image+Error'; this.classList.add('img-error');"
                  loading="lazy">
              </div>
            </div>
          </div>
          <div class="col-8">
            <h2 class="text-uppercase text-primary mt-2 mb-2 h-mobile-md"><?= esc($book['title']) ?></h2>
            <p class="mb-1 text-muted text-mobile-lg"><i class="bi bi-person"></i> <?= esc($book['author']) ?></p>
            <p class="mb-1"><span class="badge bg-secondary badge-mobile-md"><?= esc($book['genre']) ?></span></p>
            <p class="mb-2 text-muted text-mobile-lg"><i class="bi bi-calendar"></i> <?= esc($book['year']) ?></p>
            <div class="d-grid gap-1 d-md-flex justify-content-md-start">
              <a href="<?= base_url('books/detail?title=' . urlencode($book['title'])) ?>" class="btn btn-secondary btn-mobile-md">
                <i class="bi bi-eye"></i> Detail
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>