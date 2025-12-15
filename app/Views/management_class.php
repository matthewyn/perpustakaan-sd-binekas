<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<style>
    .page-item.active .page-link {
        background-color: #f4f4f4;
        border-color: #dee2e6;
        color: white;
    }
    .selection-box {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        max-height: 300px;
        overflow-y: auto;
    }
    .selection-item {
        padding: 8px;
        margin-bottom: 5px;
        background: #f8f9fa;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }
    .selection-item:hover {
        background: #e9ecef;
    }
    .selection-item input[type="checkbox"] {
        cursor: pointer;
    }
    .selection-item input[type="number"] {
        width: 70px;
        margin-left: auto;
    }
    .required::after {
        content: "*";
        color: red;
        margin-left: 2px;
    }
</style>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mt-3">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Katalog</a></li>
            <li class="breadcrumb-item active">Manajemen Kelas</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-primary" id="btnTambahKelas">
            <i class="bi bi-plus"></i> Tambah Kelas
        </button>
    </div>

    <div class="card border-light mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            List Kelas
            <i class="bi bi-chevron-down" type="button" data-bs-toggle="collapse" 
               data-bs-target="#collapseKelas"></i>
        </div>
        <div class="card-body">
            <div class="collapse show" id="collapseKelas">
                <div class="input-group input-group-sm mb-3 justify-content-end">
                    <input type="text" id="searchKelas" class="form-control" 
                           placeholder="Cari Nama Kelas" style="max-width: 250px;">
                    <button class="btn btn-success" type="button">Cari</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Kelas</th>
                                <th>Jumlah Siswa</th>
                                <th>Jumlah Buku</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kelasTableBody">
                            <?php $i=1; foreach($classes as $class): ?>
                                <tr data-class-id="<?= $class['id'] ?>">
                                    <td><?= $i++ ?></td>
                                    <td><?= esc($class['nama_kelas'] ?? '-') ?></td>
                                    <td><?= $class['student_count'] ?? 0 ?> siswa</td>
                                    <td><?= $class['book_count'] ?? 0 ?> buku</td>
                                    <td><?= isset($class['created_at']) ? date('d/m/Y', strtotime($class['created_at'])) : '-' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-view-class">Detail</button>
                                        <button class="btn btn-sm btn-warning btn-edit-class">Edit</button>
                                        <button class="btn btn-sm btn-danger btn-delete-class">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="addKelasModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="kelasForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Kelas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="namaKelas" class="form-label required">Nama Kelas</label>
            <input type="text" name="nama_kelas" id="namaKelas" class="form-control" 
                   placeholder="Contoh: Kelas 7A" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Kelas -->
<div class="modal fade" id="editKelasModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <form id="editKelasForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Kelas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editKelasId" name="id">
        
        <div class="mb-3">
            <label for="editNamaKelas" class="form-label required">Nama Kelas</label>
            <input type="text" name="nama_kelas" id="editNamaKelas" class="form-control" required>
        </div>

        <div class="mb-4">
            <h6 class="mb-3">Atur Siswa</h6>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Siswa Tersedia</label>
                    <input type="text" id="searchAvailableStudents" class="form-control form-control-sm mb-2" placeholder="Cari siswa...">
                    <div class="selection-box" id="availableStudents"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Siswa di Kelas Ini</label>
                    <input type="text" id="searchAssignedStudents" class="form-control form-control-sm mb-2" placeholder="Cari siswa...">
                    <div class="selection-box" id="assignedStudents"></div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <h6 class="mb-3">Atur Buku</h6>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Buku Tersedia</label>
                    <input type="text" id="searchAvailableBooks" class="form-control form-control-sm mb-2" placeholder="Cari buku...">
                    <div class="selection-box" id="availableBooks"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Buku di Kelas Ini</label>
                    <input type="text" id="searchAssignedBooks" class="form-control form-control-sm mb-2" placeholder="Cari buku...">
                    <div class="selection-box" id="assignedBooks"></div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Detail Kelas -->
<div class="modal fade" id="detailKelasModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Kelas: <span id="detailNamaKelas"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Daftar Siswa (<span id="detailStudentCount">0</span>)</h6>
                <div class="list-group" id="detailStudentsList"></div>
            </div>
            <div class="col-md-6">
                <h6>Daftar Buku (<span id="detailBookCount">0</span>)</h6>
                <div class="list-group" id="detailBooksList"></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addModal = new bootstrap.Modal(document.getElementById('addKelasModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editKelasModal'));
    const detailModal = new bootstrap.Modal(document.getElementById('detailKelasModal'));
    let currentClassId = null;

    // Tambah Kelas
    document.getElementById('btnTambahKelas').addEventListener('click', () => {
        document.getElementById('kelasForm').reset();
        addModal.show();
    });

    document.getElementById('kelasForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch("<?= base_url('management-class/add') ?>", {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                addModal.hide();
                showToast(data.message || 'Kelas berhasil ditambahkan', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Gagal menambahkan kelas', 'error');
            }
        })
        .catch(err => showToast('Terjadi kesalahan', 'error'));
    });

    // Edit Kelas
    document.querySelectorAll('.btn-edit-class').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            currentClassId = row.dataset.classId;
            
            fetch(`<?= base_url('management-class/getClassMembers') ?>/${currentClassId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editKelasId').value = currentClassId;
                        document.getElementById('editNamaKelas').value = data.class.nama_kelas;
                        
                        loadStudentsForEdit(data.students);
                        loadBooksForEdit(data.books);
                        
                        editModal.show();
                    }
                });
        });
    });

    function loadStudentsForEdit(assignedStudents) {
        fetch("<?= base_url('management-class/getUnassignedStudents') ?>")
            .then(r => r.json())
            .then(data => {
                const available = data.students || [];
                renderStudentBoxes(available, assignedStudents);
            });
    }

    function loadBooksForEdit(assignedBooks) {
        fetch("<?= base_url('management-class/getUnassignedBooks') ?>")
            .then(r => r.json())
            .then(data => {
                const available = data.books || [];
                renderBookBoxes(available, assignedBooks);
            });
    }

    function renderStudentBoxes(available, assigned) {
        const availableBox = document.getElementById('availableStudents');
        const assignedBox = document.getElementById('assignedStudents');
        
        availableBox.innerHTML = available.map(s => `
            <div class="selection-item">
                <input type="checkbox" value="${s.id}" class="student-checkbox-available">
                <span>${s.nama} (${s.nisn})</span>
            </div>
        `).join('') || '<div class="text-muted">Tidak ada siswa tersedia</div>';
        
        assignedBox.innerHTML = assigned.map(s => `
            <div class="selection-item">
                <input type="checkbox" value="${s.id}" class="student-checkbox-assigned" checked>
                <span>${s.nama} (${s.nisn})</span>
            </div>
        `).join('') || '<div class="text-muted">Belum ada siswa</div>';
        
        attachStudentListeners();
    }

    function renderBookBoxes(available, assigned) {
        const availableBox = document.getElementById('availableBooks');
        const assignedBox = document.getElementById('assignedBooks');
        
        availableBox.innerHTML = available.map(b => `
            <div class="selection-item">
                <input type="checkbox" value="${b.id}" class="book-checkbox-available" data-max="${b.available_quantity || b.quantity}">
                <span>${b.title} (tersedia: ${b.available_quantity || b.quantity})</span>
            </div>
        `).join('') || '<div class="text-muted">Tidak ada buku tersedia</div>';
        
        assignedBox.innerHTML = assigned.map(b => `
            <div class="selection-item">
                <input type="checkbox" value="${b.id}" class="book-checkbox-assigned" checked>
                <span>${b.title}</span>
                <input type="number" min="1" max="${b.available_quantity}" value="${b.class_quantity}" class="form-control form-control-sm book-quantity" data-book-id="${b.id}">
            </div>
        `).join('') || '<div class="text-muted">Belum ada buku</div>';
        
        attachBookListeners();
    }

    function attachStudentListeners() {
        document.querySelectorAll('.student-checkbox-available').forEach(cb => {
            cb.onchange = function() {
                if (this.checked) moveToAssigned(this, 'student');
            };
        });

        document.querySelectorAll('.student-checkbox-assigned').forEach(cb => {
            cb.onchange = function() {
                if (!this.checked) moveToAvailable(this, 'student');
            };
        });
    }

    function attachBookListeners() {
        document.querySelectorAll('.book-checkbox-available').forEach(cb => {
            cb.onchange = function() {
                if (this.checked) moveToAssigned(this, 'book');
            };
        });

        document.querySelectorAll('.book-checkbox-assigned').forEach(cb => {
            cb.onchange = function() {
                if (!this.checked) moveToAvailable(this, 'book');
            };
        });
    }

    function moveToAssigned(checkbox, type) {
        const item = checkbox.closest('.selection-item');
        const target = type === 'student' ? document.getElementById('assignedStudents') : document.getElementById('assignedBooks');

        const clone = item.cloneNode(true);
        const input = clone.querySelector('input[type="checkbox"]');
        if (input) {
            if (type === 'student') {
                input.className = 'student-checkbox-assigned';
            } else {
                input.className = 'book-checkbox-assigned';
                const maxQty = input.dataset.max || 1;
                const qtyInput = document.createElement('input');
                qtyInput.type = 'number';
                qtyInput.min = 1;
                qtyInput.max = maxQty;
                qtyInput.value = 1;
                qtyInput.className = 'form-control form-control-sm book-quantity';
                qtyInput.dataset.bookId = input.value;
                clone.appendChild(qtyInput);
            }
            input.checked = true;
        }

        if (target.querySelector('.text-muted')) target.innerHTML = '';

        item.remove();
        target.appendChild(clone);

        if (type === 'student') attachStudentListeners(); else attachBookListeners();
    }

    function moveToAvailable(checkbox, type) {
        const item = checkbox.closest('.selection-item');
        const target = type === 'student' ? document.getElementById('availableStudents') : document.getElementById('availableBooks');

        const clone = item.cloneNode(true);
        const input = clone.querySelector('input[type="checkbox"]');
        if (input) {
            if (type === 'student') {
                input.className = 'student-checkbox-available';
            } else {
                input.className = 'book-checkbox-available';
                const qtyInput = clone.querySelector('.book-quantity');
                if (qtyInput) qtyInput.remove();
            }
            input.checked = false;
        }

        if (target.querySelector('.text-muted')) target.innerHTML = '';

        item.remove();
        target.appendChild(clone);

        if (type === 'student') attachStudentListeners(); else attachBookListeners();
    }

    document.getElementById('editKelasForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const studentIds = Array.from(document.querySelectorAll('.student-checkbox-assigned:checked')).map(cb => cb.value);
        
        const bookData = {};
        document.querySelectorAll('.book-checkbox-assigned:checked').forEach(cb => {
            const bookId = cb.value;
            const qtyInput = document.querySelector(`.book-quantity[data-book-id="${bookId}"]`);
            bookData[bookId] = qtyInput ? parseInt(qtyInput.value) : 1;
        });
        
        const formData = new FormData();
        formData.append('nama_kelas', document.getElementById('editNamaKelas').value);
        formData.append('student_ids', JSON.stringify(studentIds));
        formData.append('book_data', JSON.stringify(bookData));
        
        fetch(`<?= base_url('management-class/update') ?>/${currentClassId}`, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                editModal.hide();
                showToast(data.message || 'Kelas berhasil diupdate', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Gagal mengupdate kelas', 'error');
            }
        });
    });

    // Detail Kelas
    document.querySelectorAll('.btn-view-class').forEach(btn => {
        btn.addEventListener('click', function() {
            const classId = this.closest('tr').dataset.classId;
            
            fetch(`<?= base_url('management-class/getClassMembers') ?>/${classId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('detailNamaKelas').textContent = data.class.nama_kelas;
                        document.getElementById('detailStudentCount').textContent = data.students.length;
                        document.getElementById('detailBookCount').textContent = data.books.length;
                        
                        document.getElementById('detailStudentsList').innerHTML = data.students.map(s => 
                            `<div class="list-group-item">${s.nama} (${s.nisn})</div>`
                        ).join('') || '<div class="text-muted">Tidak ada siswa</div>';
                        
                        document.getElementById('detailBooksList').innerHTML = data.books.map(b => 
                            `<div class="list-group-item">${b.title} <span class="badge bg-primary">${b.class_quantity} buku</span></div>`
                        ).join('') || '<div class="text-muted">Tidak ada buku</div>';
                        
                        detailModal.show();
                    }
                });
        });
    });

    // Delete Kelas
    document.querySelectorAll('.btn-delete-class').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Yakin ingin menghapus kelas ini?')) return;
            
            const classId = this.closest('tr').dataset.classId;
            
            fetch(`<?= base_url('management-class/delete') ?>/${classId}`, { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Kelas berhasil dihapus', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Gagal menghapus kelas', 'error');
                    }
                });
        });
    });

    // Search functionality
    ['Students', 'Books'].forEach(type => {
        ['Available', 'Assigned'].forEach(status => {
            const searchId = `search${status}${type}`;
            const boxId = `${status.toLowerCase()}${type}`;
            
            document.getElementById(searchId)?.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                document.querySelectorAll(`#${boxId} .selection-item`).forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    });

    // Table search
    document.getElementById('searchKelas').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#kelasTableBody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
});
</script>

<?php if (session()->getFlashdata('message')): ?>
<script>
    showToast("<?= esc(session()->getFlashdata('message'), 'js') ?>");
</script>
<?php endif; ?>

<?= $this->endSection() ?>