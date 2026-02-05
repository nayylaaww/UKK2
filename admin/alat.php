<?php
session_start();
include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
}

if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_alat'];
    $kategori= $_POST['kategori_id'];
    $stok = $_POST['stok'];

    $stmt = $conn->prepare("INSERT INTO alat(nama_alat, kategori_id, stok) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $nama, $kategori, $stok);
    $stmt->execute();

    logActivity($_SESSION['user_id'], "Menambah alat: $nama");
    header('Location: alat.php?success=1');
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM alat WHERE id = $id");
    logActivity($_SESSION['user_id'], "Menghapus alat ID: $id");
    header('Location: alat.php?success=1');
}

$alat = $conn->query("SELECT a.*, k.nama_kategori FROM alat a LEFT JOIN kategori k ON a.kategori_id = k.id");
$kategori = $conn->query("SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Alat</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="sidebar">
        <h3>Menu Admin</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="user.php">Kelola User</a>
        <a href="alat.php">Kelola Alat</a>
        <a href="kategori.php">Kelola Kategori</a>
        <a href="peminjaman.php">Kelola Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="content">
        <h2>Kelola Alat</h2>

        <form method="POST" class="form">
            <h3>Tambah Alat Baru</h3>
            <input type="text" name="nama_alat" placeholder="Nama Alat" required>
            <select name="kategori_id" required>
                <option value="">Pilih Kategori</option>
                <?php while($row = $kategori->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_kategori']; ?></option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="stok" placeholder="Stok" required>
            <button type="submit" name="tambah">Tambah</button>
        </form>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Nama Alat</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $alat->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['nama_alat']; ?></td>
                <td><?php echo $row['nama_kategori']; ?></td>
                <td><?php echo $row['stok']; ?></td>
                <td>
                    <a href="?edit=<?php echo $row['id']; ?>">Edit</a>
                    <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>