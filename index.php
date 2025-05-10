<?php
require_once 'config.php';

// Periksa apakah pengguna sudah masuk
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirect("login.php");
}
$stats = [
    'pasien' => 0,
    'paramedik' => 0,
    'periksa' => 0,
    'unit_kerja' => 0
];

// Total pasien
$query = "SELECT COUNT(*) as total FROM pasien";
if ($result = mysqli_query($conn, $query)) {
    $stats['pasien'] = mysqli_fetch_assoc($result)['total'];
}

// Total paramediK
$query = "SELECT COUNT(*) as total FROM paramedik";
if ($result = mysqli_query($conn, $query)) {
    $stats['paramedik'] = mysqli_fetch_assoc($result)['total'];
}

// Hitung total pemeriksaan
$query = "SELECT COUNT(*) as total FROM periksa";
if ($result = mysqli_query($conn, $query)) {
    $stats['periksa'] = mysqli_fetch_assoc($result)['total'];
}

// total unit kerja
$query = "SELECT COUNT(*) as total FROM unit_kerja";
if ($result = mysqli_query($conn, $query)) {
    $stats['unit_kerja'] = mysqli_fetch_assoc($result)['total'];
}

// pemeriksaan
$recent_checkups = [];
$query = "SELECT p.tanggal, ps.nama as pasien_nama, pr.nama as dokter_nama 
          FROM periksa p 
          JOIN pasien ps ON p.pasien_id = ps.id 
          JOIN paramedik pr ON p.dokter_id = pr.id 
          ORDER BY p.tanggal DESC LIMIT 5";
if ($result = mysqli_query($conn, $query)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_checkups[] = $row;
    }
}

include 'header.php';
?>

<h1 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>

<div class="row">
    <div class="col-md-3">
        <div class="card dashboard-card text-center bg-primary text-white">
            <div class="card-body">
                <h1><i class="fas fa-users"></i></h1>
                <h3><?php echo $stats['pasien']; ?></h3>
                <p class="mb-0">Total Pasien</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card text-center bg-success text-white">
            <div class="card-body">
                <h1><i class="fas fa-user-md"></i></h1>
                <h3><?php echo $stats['paramedik']; ?></h3>
                <p class="mb-0">Total Paramedik</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card text-center bg-info text-white">
            <div class="card-body">
                <h1><i class="fas fa-stethoscope"></i></h1>
                <h3><?php echo $stats['periksa']; ?></h3>
                <p class="mb-0">Total Pemeriksaan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card text-center bg-warning text-dark">
            <div class="card-body">
                <h1><i class="fas fa-hospital"></i></h1>
                <h3><?php echo $stats['unit_kerja']; ?></h3>
                <p class="mb-0">Total Unit Kerja</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-list me-2"></i>Pemeriksaan Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (count($recent_checkups) > 0): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Pasien</th>
                            <th scope="col">Dokter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_checkups as $checkup): ?>
                        <tr>
                            <td><?php echo date('d-m-Y', strtotime($checkup['tanggal'])); ?></td>
                            <td><?php echo $checkup['pasien_nama']; ?></td>
                            <td><?php echo $checkup['dokter_nama']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center py-3">Belum ada data pemeriksaan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>Informasi</h5>
            </div>
            <div class="card-body">
                <p>Latar Belakang : </p>
                <ul>
                    <li>Di era digital saat ini, kebutuhan akan sistem informasi berbasis web semakin meningkat, termasuk dalam bidang pelayanan kesehatan. Klinik sebagai fasilitas kesehatan memerlukan tampilan web yang informatif dan mudah diakses untuk mempermudah pengelolaan data pasien, jadwal dokter, serta layanan administrasi. Oleh karena itu, pembuatan tampilan web klinik ini dilakukan sebagai bagian dari tugas mata kuliah Pemrograman Web, guna mengaplikasikan konsep-konsep pemrograman dalam membangun antarmuka yang fungsional, user-friendly, dan mendukung digitalisasi layanan klinik.</li>
                    <li>Nama : Tazkiah Badzlina</li> 
                    <li>Nim  : 0110124102</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>