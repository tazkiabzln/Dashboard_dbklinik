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

// Proses form tambah/edit kelurahan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nama = $_POST['nama'];
    $kec_id = $_POST['kec_id'];
    
    // Validasi data
    $error = '';
    if (empty($nama) || empty($kec_id)) {
        $error = "Semua field harus diisi!";
    }
    
    if (empty($error)) {
        if (empty($id)) {
            // Masukkan data baru
            $sql = "INSERT INTO kelurahan (nama, kec_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nama, $kec_id);
        } else {
            // Perbarui data yang ada
            $sql = "UPDATE kelurahan SET nama=?, kec_id=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $nama, $kec_id, $id);
        }
        
        if ($stmt->execute()) {
            header("Location: kelurahan.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Proses hapus kelurahan
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    
    //Periksa apakah kelurahan tersebut digunakan oleh pasien
    $check_sql = "SELECT COUNT(*) as count FROM pasien WHERE kelurahan_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['count'] > 0) {
        
        header("Location: kelurahan.php?error=Kelurahan sedang digunakan oleh pasien. Tidak dapat dihapus.");
        exit();
    } else {
        $sql = "DELETE FROM kelurahan WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: kelurahan.php");
        exit();
    }
}

// data kelurahan untuk diedit
$edit_data = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM kelurahan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// List kecamatan 
$kecamatan_options = [
    1 => 'Bogor Barat',
    2 => 'Bogor Timur',
    3 => 'Bogor Utara',
    4 => 'Bogor Selatan',
    5 => 'Bogor Tengah',
    6 => 'Tanah Sareal'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelurahan - Sistem Klinik</title>
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
        select {
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
        <h2><?php echo isset($edit_data) ? 'Edit Kelurahan' : 'Tambah Kelurahan Baru'; ?></h2>
        
        <?php if(isset($error) && !empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?php echo $_GET['error']; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php if(isset($edit_data)): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nama">Nama Kelurahan:</label>
                <input type="text" id="nama" name="nama" value="<?php echo isset($edit_data) ? $edit_data['nama'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="kec_id">Kecamatan:</label>
                <select id="kec_id" name="kec_id" required>
                    <option value="">--Pilih Kecamatan--</option>
                    <?php foreach($kecamatan_options as $id => $nama): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($edit_data) && $edit_data['kec_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo $nama; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Simpan</button>
                <a href="kelurahan.php" class="btn btn-danger">Batal</a>
            </div>
        </form>
        
        <div class="table-container">
            <h2>Daftar Kelurahan</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kelurahan</th>
                        <th>Kecamatan</th>
                        <th>Jumlah Pasien</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // data kelurahan dengan jumlah pasien
                    $sql = "SELECT k.*, COUNT(p.id) as jumlah_pasien 
                            FROM kelurahan k
                            LEFT JOIN pasien p ON k.id = p.kelurahan_id
                            GROUP BY k.id
                            ORDER BY k.nama ASC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $kecamatan_nama = isset($kecamatan_options[$row['kec_id']]) ? $kecamatan_options[$row['kec_id']] : 'Tidak diketahui';
                            
                            echo "<tr>
                                    <td>" . $row["id"] . "</td>
                                    <td>" . $row["nama"] . "</td>
                                    <td>" . $kecamatan_nama . "</td>
                                    <td>" . $row["jumlah_pasien"] . "</td>
                                    <td>
                                        <a href='kelurahan.php?edit=" . $row["id"] . "' class='btn'>Edit</a>
                                        <a href='kelurahan.php?delete=" . $row["id"] . "' class='btn btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\")'>Hapus</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Tidak ada data kelurahan.</td></tr>";
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