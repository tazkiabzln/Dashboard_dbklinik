<?php
// Koneksi database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'db_klinik');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses form tambah/edit pemeriksaan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $tanggal = $_POST['tanggal'];
    $berat = $_POST['berat'];
    $tinggi = $_POST['tinggi'];
    $tensi = $_POST['tensi'];
    $keterangan = $_POST['keterangan'];
    $pasien_id = $_POST['pasien_id'];
    $dokter_id = $_POST['dokter_id'];
    
    // Validasi data
    $error = '';
    if (empty($tanggal) || empty($berat) || empty($tinggi) || empty($tensi) || 
        empty($keterangan) || empty($pasien_id) || empty($dokter_id)) {
        $error = "Semua field harus diisi!";
    }
    
    if (empty($error)) {
        if (empty($id)) {
            //Masukkan rekaman baru
            $sql = "INSERT INTO periksa (tanggal, berat, tinggi, tensi, keterangan, pasien_id, dokter_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sddssii", $tanggal, $berat, $tinggi, $tensi, $keterangan, $pasien_id, $dokter_id);
        } else {
            // Perbarui rekaman yang ada
            $sql = "UPDATE periksa SET tanggal=?, berat=?, tinggi=?, tensi=?, keterangan=?, pasien_id=?, dokter_id=? 
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sddssiis", $tanggal, $berat, $tinggi, $tensi, $keterangan, $pasien_id, $dokter_id, $id);
        }
        
        if ($stmt->execute()) {
            header("Location: periksa.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Proses hapus pemeriksaan
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM periksa WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: periksa.php");
    exit();
}

// Dapatkan data pemeriksaan untuk diedit
$edit_data = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM periksa WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

//Dapatkan pasien untuk dropdown
$pasien_sql = "SELECT id, nama, kode FROM pasien ORDER BY nama ASC";
$pasien_result = $conn->query($pasien_sql);
$pasien_options = [];
if ($pasien_result->num_rows > 0) {
    while ($row = $pasien_result->fetch_assoc()) {
        $pasien_options[$row['id']] = $row['kode'] . ' - ' . $row['nama'];
    }
}

// Dapatkan dokter untuk dropdown (hanya paramedik dengan kategori='dokter')
$dokter_sql = "SELECT id, nama FROM paramedik WHERE kategori='dokter' ORDER BY nama ASC";
$dokter_result = $conn->query($dokter_sql);
$dokter_options = [];
if ($dokter_result->num_rows > 0) {
    while ($row = $dokter_result->fetch_assoc()) {
        $dokter_options[$row['id']] = $row['nama'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pemeriksaan - Sistem Klinik</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --light-color: #f9f9f9;
            --dark-color: #333;
            --danger-color: #f44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-color);
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 20px;
        }

        nav {
            background-color: var(--dark-color);
            margin-bottom: 20px;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            justify-content: center;
        }

        nav ul li {
            padding: 15px 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        nav ul li a:hover {
            color: var(--primary-color);
        }

        .table-container {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 20px;
        }

        h2 {
            color: var(--dark-color);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: var(--light-color);
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 2px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        form {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            max-width: 600px;
            margin: 0 auto 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .error {
            color: var(--danger-color);
            margin-bottom: 15px;
        }

        footer {
            background-color: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Sistem Manajemen Klinik</h1>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.php">Beranda</a></li>
            <li><a href="pasien.php">Pasien</a></li>
            <li><a href="paramedik.php">Paramedik</a></li>
            <li><a href="periksa.php">Pemeriksaan</a></li>
            <li><a href="unit_kerja.php">Unit Kerja</a></li>
            <li><a href="kelurahan.php">Kelurahan</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h2><?php echo isset($edit_data) ? 'Edit Pemeriksaan' : 'Tambah Pemeriksaan Baru'; ?></h2>
        
        <?php if(isset($error) && !empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php if(isset($edit_data)): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="tanggal">Tanggal Pemeriksaan:</label>
                <input type="date" id="tanggal" name="tanggal" value="<?php echo isset($edit_data) ? $edit_data['tanggal'] : date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="pasien_id">Pasien:</label>
                <select id="pasien_id" name="pasien_id" required>
                    <option value="">--Pilih Pasien--</option>
                    <?php foreach($pasien_options as $id => $nama): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($edit_data) && $edit_data['pasien_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo $nama; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="dokter_id">Dokter:</label>
                <select id="dokter_id" name="dokter_id" required>
                    <option value="">--Pilih Dokter--</option>
                    <?php foreach($dokter_options as $id => $nama): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($edit_data) && $edit_data['dokter_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo $nama; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="berat">Berat Badan (kg):</label>
                <input type="number" id="berat" name="berat" step="0.1" value="<?php echo isset($edit_data) ? $edit_data['berat'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tinggi">Tinggi Badan (cm):</label>
                <input type="number" id="tinggi" name="tinggi" step="0.1" value="<?php echo isset($edit_data) ? $edit_data['tinggi'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tensi">Tensi Darah:</label>
                <input type="text" id="tensi" name="tensi" placeholder="contoh: 120/80" value="<?php echo isset($edit_data) ? $edit_data['tensi'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="keterangan">Keterangan:</label>
                <textarea id="keterangan" name="keterangan" rows="3" required><?php echo isset($edit_data) ? $edit_data['keterangan'] : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Simpan</button>
                <a href="periksa.php" class="btn btn-danger">Batal</a>
            </div>
        </form>
        
        <div class="table-container">
            <h2>Daftar Pemeriksaan</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Berat (kg)</th>
                        <th>Tinggi (cm)</th>
                        <th>Tensi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Dapatkan semua data pemeriksaan dengan info terkait
                    $sql = "SELECT p.*, ps.nama as pasien_nama, pm.nama as dokter_nama 
                            FROM periksa p
                            LEFT JOIN pasien ps ON p.pasien_id = ps.id
                            LEFT JOIN paramedik pm ON p.dokter_id = pm.id
                            ORDER BY p.tanggal DESC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row["id"] . "</td>
                                    <td>" . $row["tanggal"] . "</td>
                                    <td>" . $row["pasien_nama"] . "</td>
                                    <td>" . $row["dokter_nama"] . "</td>
                                    <td>" . $row["berat"] . "</td>
                                    <td>" . $row["tinggi"] . "</td>
                                    <td>" . $row["tensi"] . "</td>
                                    <td>" . $row["keterangan"] . "</td>
                                    <td>
                                        <a href='periksa.php?edit=" . $row["id"] . "' class='btn'>Edit</a>
                                        <a href='periksa.php?delete=" . $row["id"] . "' class='btn btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\")'>Hapus</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>Tidak ada data pemeriksaan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Klinik Sehat</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>