<?= $this->extend('layout', ['bodyClass' => 'login-page']) ?>
<?= $this->section('content') ?>
<style>
    .card-body {
        padding: 40px 30px;
    }

    @media (min-width: 992px) { 
        #resetPasswordTitle {
            font-size: 1.65rem;
        }
    }
</style>

<div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 67px);">
    <div class="col-8 col-lg-5">
        <div class="card" style="border-style: dashed; border-color: #ced4da;">
            <div class="card-body">
                <h1 class="h-mobile-lg text-center" id="resetPasswordTitle">Reset Password</h1>
                <p class="text-center mb-4 text-mobile-sm">Masukkan identitas dan password baru</p>

                <form id="verifyForm">
                    <div class="mb-3">
                        <label for="nama" class="text-mobile-sm">Nama</label>
                        <input type="text" class="form-control form-control-mobile" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_number" class="text-mobile-sm">NISN/NIP</label>
                        <input type="text" class="form-control form-control-mobile" id="id_number" name="id_number" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-mobile-sm w-100">Verifikasi Identitas</button>
                </form>

                <div id="resetSection" style="display:none; margin-top:20px;">
                    <div class="mb-3">
                        <label for="new_password" class="text-mobile-sm">Password Baru</label>
                        <input type="password" class="form-control form-control-mobile" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="text-mobile-sm">Konfirmasi Password</label>
                        <input type="password" class="form-control form-control-mobile" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="button" id="resetBtn" class="btn btn-success btn-mobile-sm w-100">Reset Password</button>
                </div>
                <a href="<?= base_url('login') ?>" class="d-block text-center text-mobile-md mt-3">Kembali ke halaman login</a>
                <div id="message" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
const verifyForm = document.getElementById('verifyForm');
const resetSection = document.getElementById('resetSection');
const messageDiv = document.getElementById('message');
const resetBtn = document.getElementById('resetBtn');

    verifyForm.addEventListener('submit', function(e){
        e.preventDefault();

        const nama = document.getElementById('nama').value.trim();
        const id_number = document.getElementById('id_number').value.trim();

        document.getElementById('nama').style.backgroundColor = '';
        document.getElementById('id_number').style.backgroundColor = '';

        verifyForm.addEventListener('submit', function(e){
            e.preventDefault();

            const nama = document.getElementById('nama').value.trim();
            const id_number = document.getElementById('id_number').value.trim();

            // Reset background
            ['nama','id_number','new_password','confirm_password'].forEach(id => {
                document.getElementById(id).style.backgroundColor = '';
            });

            // Reset password field
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            resetSection.style.display = 'none';

            // Reset message
            messageDiv.innerHTML = '';
        });


        fetch('<?= base_url("verify-user-binekas") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nama, id_number })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                messageDiv.innerHTML = '<span style="color:green;">Identitas valid, silakan masukkan password baru</span>';
                resetSection.style.display = 'block';
                document.getElementById('nama').style.backgroundColor = '#c8facc';
                document.getElementById('id_number').style.backgroundColor = '#c8facc';
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 5000);
            } else {
                messageDiv.innerHTML = '<span style="color:red;">Identitas tidak ditemukan</span>';
                resetSection.style.display = 'none';
                document.getElementById('nama').style.backgroundColor = '#f8cccc';
                document.getElementById('id_number').style.backgroundColor = '#f8cccc';
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 5000);
            }
        })
        .catch(err => console.error(err));
    });
    

    resetBtn.addEventListener('click', function(){
        const newPassword = document.getElementById('new_password').value.trim();
        const confirmPassword = document.getElementById('confirm_password').value.trim();

        if(newPassword !== confirmPassword){
            messageDiv.innerHTML = '<span style="color:red;">Password tidak sama</span>';

            ['new_password','confirm_password'].forEach(id => {
                const input = document.getElementById(id);
                input.style.backgroundColor = '#f8cccc'; // merah
                setTimeout(() => input.style.backgroundColor = '', 1500);
            });
            return;
        }

    fetch('<?= base_url("reset-password-binekas") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nama: document.getElementById('nama').value.trim(),
            id_number: document.getElementById('id_number').value.trim(),
            new_password: newPassword
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            showToast('Password berhasil diubah!');
            resetSection.style.display = 'none';

            // reset form dan background
            verifyForm.reset();
            ['nama','id_number','new_password','confirm_password'].forEach(id => {
                document.getElementById(id).style.backgroundColor = '';
            });

            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 5000);
        } else {
            messageDiv.innerHTML = '<span style="color:red;">'+data.message+'</span>';

            ['nama','id_number','new_password','confirm_password'].forEach(id => {
                const input = document.getElementById(id);
                input.style.backgroundColor = '#f8cccc';
                setTimeout(() => input.style.backgroundColor = '', 1500);
            });

            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 5000);
        }
    })
    .catch(err => console.error(err));
});

</script>

<?= $this->endSection() ?>