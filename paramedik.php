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

// Proses form tambah/edit paramedik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nama = $_POST['nama'];
    $gender = $_POST['gender'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $kategori = $_POST['kategori'];
    $telpon = $_POST['telpon'];
    $alamat = $_POST['alamat'];
    $unit_kerja_id = $_POST['unit_kerja_id'];
    
    // Validasi data
    $error = '';
    if (empty($nama) || empty($gender) || empty($tmp_lahir) || empty($tgl_lahir) || 
        empty($kategori) || empty($telpon) || empty($alamat) || empty($unit_kerja_id)) {
        $error = "Semua field harus diisi!";
    }
    
    if (empty($error)) {
        if (empty($id)) {
            // Masukkan data baru
            $sql = "INSERT INTO paramedik (nama, gender, tmp_lahir, tgl_lahir, kategori, telpon, alamat, unit_kerja_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $nama, $gender, $tmp_lahir, $tgl_lahir, $kategori, $telpon, $alamat, $unit_kerja_id);
        } else {
            //Perbarui data yang ada
            $sql = "UPDATE paramedik SET nama=?, gender=?, tmp_lahir=?, tgl_lahir=?, kategori=?, telpon=?, alamat=?, unit_kerja_id=? 
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssii", $nama, $gender, $tmp_lahir, $tgl_lahir, $kategori, $telpon, $alamat, $unit_kerja_id, $id);
        }
        
        if ($stmt->execute()) {
            header("Location: paramedik.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Proses hapus paramedik
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM paramedik WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: paramedik.php");
    exit();
}

// Dapatkan data paramedis untuk diedit
$edit_data = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM paramedik WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// Dapatkan unit kerja untuk dropdown
$unit_kerja_sql = "SELECT id, nama FROM unit_kerja";
$unit_kerja_result = $conn->query($unit_kerja_sql);
$unit_kerja_options = [];
if ($unit_kerja_result->num_rows > 0) {
    while ($row = $unit_kerja_result->fetch_assoc()) {
        $unit_kerja_options[$row['id']] = $row['nama'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Paramedik - Sistem Klinik</title>
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
        input[type="tel"],
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
        <h2><?php echo isset($edit_data) ? 'Edit Paramedik' : 'Tambah Paramedik Baru'; ?></h2>
        
        <?php if(isset($error) && !empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php if(isset($edit_data)): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nama">Nama Paramedik:</label>
                <input type="text" id="nama" name="nama" value="<?php echo isset($edit_data) ? $edit_data['nama'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gender">Jenis Kelamin:</label>
                <select id="gender" name="gender" required>
                    <option value="">--Pilih Jenis Kelamin--</option>
                    <option value="L" <?php echo (isset($edit_data) && $edit_data['gender'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="P" <?php echo (isset($edit_data) && $edit_data['gender'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="tmp_lahir">Tempat Lahir:</label>
                <input type="text" id="tmp_lahir" name="tmp_lahir" value="<?php echo isset($edit_data) ? $edit_data['tmp_lahir'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tgl_lahir">Tanggal Lahir:</label>
                <input type="date" id="tgl_lahir" name="tgl_lahir" value="<?php echo isset($edit_data) ? $edit_data['tgl_lahir'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="kategori">Kategori:</label>
                <select id="kategori" name="kategori" required>
                    <option value="">--Pilih Kategori--</option>
                    <option value="dokter" <?php echo (isset($edit_data) && $edit_data['kategori'] == 'dokter') ? 'selected' : ''; ?>>Dokter</option>
                    <option value="perawat" <?php echo (isset($edit_data) && $edit_data['kategori'] == 'perawat') ? 'selected' : ''; ?>>Perawat</option>
                    <option value="bidan" <?php echo (isset($edit_data) && $edit_data['kategori'] == 'bidan') ? 'selected' : ''; ?>>Bidan</option>
                    <option value="apoteker" <?php echo (isset($edit_data) && $edit_data['kategori'] == 'apoteker') ? 'selected' : ''; ?>>Apoteker</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="telpon">Nomor Telepon:</label>
                <input type="tel" id="telpon" name="telpon" value="<?php echo isset($edit_data) ? $edit_data['telpon'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat:</label>
                <textarea id="alamat" name="alamat" rows="3" required><?php echo isset($edit_data) ? $edit_data['alamat'] : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="unit_kerja_id">Unit Kerja:</label>
                <select id="unit_kerja_id" name="unit_kerja_id" required>
                    <option value="">--Pilih Unit Kerja--</option>
                    <?php foreach($unit_kerja_options as $id => $nama): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($edit_data) && $edit_data['unit_kerja_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo $nama; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Simpan</button>
                <a href="paramedik.php" class="btn btn-danger">Batal</a>
            </div>
        </form>
        
        <div class="table-container">
            <h2>Daftar Paramedik</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Gender</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Kategori</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Unit Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Dapatkan semua data paramedis dengan nama unit kerja
                    $sql = "SELECT p.*, u.nama as unit_kerja_nama FROM paramedik p
                            LEFT JOIN unit_kerja u ON p.unit_kerja_id = u.id
                            ORDER BY p.id DESC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row["id"] . "</td>
                                    <td>" . $row["nama"] . "</td>
                                    <td>" . ($row["gender"] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td>
                                    <td>" . $row["tmp_lahir"] . "</td>
                                    <td>" . $row["tgl_lahir"] . "</td>
                                    <td>" . ucfirst($row["kategori"]) . "</td>
                                    <td>" . $row["telpon"] . "</td>
                                    <td>" . $row["alamat"] . "</td>
                                    <td>" . $row["unit_kerja_nama"] . "</td>
                                    <td>
                                        <a href='paramedik.php?edit=" . $row["id"] . "' class='btn'>Edit</a>
                                        <a href='paramedik.php?delete=" . $row["id"] . "' class='btn btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\")'>Hapus</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>Tidak ada data paramedik.</td></tr>";
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