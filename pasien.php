<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect("login.php");
}

// Message variable
$message = '';
$message_type = '';

// Delete patient
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = sanitize($_GET['delete']);
    
    $query = "DELETE FROM pasien WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $message = "Data pasien berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// Formulir proses pengiriman untuk penambahan pasien baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $kode = sanitize($_POST['kode']);
    $nama = sanitize($_POST['nama']);
    $tmp_lahir = sanitize($_POST['tmp_lahir']);
    $tgl_lahir = sanitize($_POST['tgl_lahir']);
    $gender = sanitize($_POST['gender']);
    $email = sanitize($_POST['email']);
    $alamat = sanitize($_POST['alamat']);
    $kelurahan_id = sanitize($_POST['kelurahan_id']);
    
    $query = "INSERT INTO pasien (kode, nama, tmp_lahir, tgl_lahir, gender, email, alamat, kelurahan_id) 
              VALUES ('$kode', '$nama', '$tmp_lahir', '$tgl_lahir', '$gender', '$email', '$alamat', $kelurahan_id)";
    
    if (mysqli_query($conn, $query)) {
        $message = "Data pasien berhasil ditambahkan!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

//Proses pengiriman formulir untuk pengeditan pasien 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = sanitize($_POST['id']);
    $kode = sanitize($_POST['kode']);
    $nama = sanitize($_POST['nama']);
    $tmp_lahir = sanitize($_POST['tmp_lahir']);
    $tgl_lahir = sanitize($_POST['tgl_lahir']);
    $gender = sanitize($_POST['gender']);
    $email = sanitize($_POST['email']);
    $alamat = sanitize($_POST['alamat']);
    $kelurahan_id = sanitize($_POST['kelurahan_id']);
    
    $query = "UPDATE pasien SET 
              kode = '$kode', 
              nama = '$nama', 
              tmp_lahir = '$tmp_lahir', 
              tgl_lahir = '$tgl_lahir', 
              gender = '$gender', 
              email = '$email', 
              alamat = '$alamat', 
              kelurahan_id = $kelurahan_id 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $message = "Data pasien berhasil diperbarui!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// Dapatkan semua data pasien
$patients = [];
$query = "SELECT p.*, k.nama as kelurahan_nama 
          FROM pasien p 
          LEFT JOIN kelurahan k ON p.kelurahan_id = k.id 
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
}

// Dapatkan semua kelurahan untuk dropdown
$kelurahans = [];
$query = "SELECT * FROM kelurahan ORDER BY nama";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kelurahans[] = $row;
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users me-2"></i>Data Pasien</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
        <i class="fas fa-plus me-2"></i>Tambah Pasien
    </button>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Tempat, Tanggal Lahir</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Email</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Kelurahan</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($patients) > 0): ?>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo $patient['kode']; ?></td>
                            <td><?php echo $patient['nama']; ?></td>
                            <td><?php echo $patient['tmp_lahir'] . ', ' . date('d-m-Y', strtotime($patient['tgl_lahir'])); ?></td>
                            <td><?php echo ($patient['gender'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td><?php echo $patient['email']; ?></td>
                            <td><?php echo $patient['alamat']; ?></td>
                            <td><?php echo $patient['kelurahan_nama'] ?? 'Tidak ada'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-btn" 
                                        data-id="<?php echo $patient['id']; ?>"
                                        data-kode="<?php echo $patient['kode']; ?>"
                                        data-nama="<?php echo $patient['nama']; ?>"
                                        data-tmp-lahir="<?php echo $patient['tmp_lahir']; ?>"
                                        data-tgl-lahir="<?php echo $patient['tgl_lahir']; ?>"
                                        data-gender="<?php echo $patient['gender']; ?>"
                                        data-email="<?php echo $patient['email']; ?>"
                                        data-alamat="<?php echo $patient['alamat']; ?>"
                                        data-kelurahan-id="<?php echo $patient['kelurahan_id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="#" onclick="return confirmDelete('pasien.php?delete=<?php echo $patient['id']; ?>')" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data pasien.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tambahkan Modal Pasien -->
<div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPatientModalLabel">Tambah Pasien Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kode" class="form-label">Kode Pasien</label>
                            <input type="text" class="form-control" id="kode" name="kode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tmp_lahir" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="tmp_lahir" name="tmp_lahir" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="kelurahan_id" class="form-label">Kelurahan</label>
                        <select class="form-select" id="kelurahan_id" name="kelurahan_id" required>
                            <?php foreach ($kelurahans as $kelurahan): ?>
                                <option value="<?php echo $kelurahan['id']; ?>"><?php echo $kelurahan['nama']; ?></option>
                            <?php endforeach; ?>
                            <?php if (count($kelurahans) == 0): ?>
                                <option value="">Tidak ada data kelurahan</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal Pasien -->
<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPatientModalLabel">Edit Data Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_kode" class="form-label">Kode Pasien</label>
                            <input type="text" class="form-control" id="edit_kode" name="kode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_tmp_lahir" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="edit_tmp_lahir" name="tmp_lahir" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="edit_tgl_lahir" name="tgl_lahir" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_gender" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="edit_gender" name="gender" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kelurahan_id" class="form-label">Kelurahan</label>
                        <select class="form-select" id="edit_kelurahan_id" name="kelurahan_id" required>
                            <?php foreach ($kelurahans as $kelurahan): ?>
                                <option value="<?php echo $kelurahan['id']; ?>"><?php echo $kelurahan['nama']; ?></option>
                            <?php endforeach; ?>
                            <?php if (count($kelurahans) == 0): ?>
                                <option value="">Tidak ada data kelurahan</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menangani klik tombol edit
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('editPatientModal'));
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const kode = this.getAttribute('data-kode');
                const nama = this.getAttribute('data-nama');
                const tmpLahir = this.getAttribute('data-tmp-lahir');
                const tglLahir = this.getAttribute('data-tgl-lahir');
                const gender = this.getAttribute('data-gender');
                const email = this.getAttribute('data-email');
                const alamat = this.getAttribute('data-alamat');
                const kelurahanId = this.getAttribute('data-kelurahan-id');
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_kode').value = kode;
                document.getElementById('edit_nama').value = nama;
                document.getElementById('edit_tmp_lahir').value = tmpLahir;
                document.getElementById('edit_tgl_lahir').value = tglLahir;
                document.getElementById('edit_gender').value = gender;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_alamat').value = alamat;
                document.getElementById('edit_kelurahan_id').value = kelurahanId;
                
                editModal.show();
            });
        });
    });
</script>

<?php include 'footer.php'; ?>