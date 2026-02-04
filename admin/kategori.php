<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('admin');

if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_kategori'];

    $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
    $stmt->bind_param("s", $nama);

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], "Menambah kaegori: $nama");
        $message = showAlert('success', 'Kategori berhasil ditambahkan!');
    } else {
        $message = showAlert('danger', 'Gagal menambah kategori!');
    }
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_kategori'];

    $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
    $stmt->bind_param("si", $nama, $id);

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], "Mengedit kategori ID $id: $nama");
        $message = showAlert('success', 'Kategori berhasil diupdate!');
    } else {
        $message = showAlert('danger', 'Gagal mengupdate kategori!');
    }
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $check = $conn->query("SELECT COUNT(*) as total FROM alat WHERE kategori_id = $id");
    $count = $check->fetch_assoc()['total'];

    if ($count == 0) {
        $conn->query("DELETE FROM kategori WHERE id = $id");
        logActivity($_SESSION['user_id'], "Menghapus kategori ID: $id");
        $message = showAlert('success', 'Kategori berhasil dihapus!');
    } else {
        $message = showAlert('danger', 'Kategori tidak bisa dihapus karena masih digunakan!');
    }
}

$kategori = $conn->query("SELECT * FROM kategori ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Kategori - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
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
        <h2>kelola Kategori Alat</h2>

        <?php if(isset($message)) echo $message; ?>

        <div class="card">
            <h3>Tambah Kategori Baru</h3>
            <form method="POST" class="form-inline">
                <input type="text" name="nama_kategori" placeholder="Nama Kategori" required>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
            </form>
        </div>
        <div class="card">
            <h3>Daftar Kategori</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Alat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $kategori->fetch_assoc()):
                        $count = $conn->query("SELECT COUNT(*) as total FROM alat WHERE kategori_id = {$row['id']}")->
                        fetch_assoc()['total'];
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nama_kategori']; ?></td>
                            <td><?php echo $count; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editKategori(<?php echo $row['id']; ?>,
                                '<?php echo $row['nama_kategori']; ?>')">Edit</button>
                                <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return
                                confirm('Yakin menghapus kategori ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Edit Kategori</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="editNama" class="form-control" required>
                </div>
                <button type="submit" name="edit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <script>
        function editKategori(id, nama) {
            document.getElementedById('editId').value = id;
            document.getElementedById('editNama').value = nama;
            document.getElementedById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementedById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>