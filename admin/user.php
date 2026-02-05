<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('admin');

if (isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO user (username, password, nama, role) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $username, $password, $nama, $role);

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], "Menambah user: $username ($role)");
        $message = showAlert('success', 'User berhasil ditambahkan!');
    } else {
        $message = showAlert('danger', 'Gagal menambah user! Username mungkin sudah ada.');
    }
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET nama = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET nama = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $role, $id);
    }

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], "Mengedit user ID $id");
        $message = showAlert('success', 'User berhasil diupdate!');
    } else {
        $message = showAlert('danger', 'Gagal mengupdate user!');
    } 
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    if ($id !=$_SESSION['user_id']) {
        $conn->query("DELETE FROM user WHERE id = $id");
        logActivity($_SESSION['user_id'], "Menghapus user ID: $id");
        $message = showAlert('success', 'User berhasil dihapus!');
    } else {
        $message = showAlert('danger', 'Tidak bisa menghapus akun sendiri!');
    }
}

$users = $conn->query("SELECT * FROM user ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola User - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .role-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            color: white;
        }
        .role-admin { background: #dc3545; }
        .role-petugas { background: #ffc107; color: #000; }
        .role-peminjam { background: #28a745; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="user.php">Kelola User</a>
        <a href="alat.php">Kelola Alat</a>
        <a href="kategori.php">Kelola Kategori</a>
        <a href="peminjaman.php">Kelola Peminjaman</a>
        <a href="laporan.php">Laporan</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="content">
        <h2>Kelola User</h2>

        <?php if(isset($message)) echo $message; ?>

        <div class="card">
            <h3>Tambah User Baru</h3>
            <form method="POST" class="form">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
                <select name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="petugas">Petugas</option>
                    <option value="peminjam">Peminjam</option>
                </select>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah User</button>
            </form>
        </div>
        <div class="card">
            <h3>Daftar User</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $row['role']; ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($row['created_at']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editUser(
                                <?php echo $row['id']; ?>,
                                '<?php echo $row['username']; ?>',
                                '<?php echo $row['nama']; ?>',
                                '<?php echo $row['role']; ?>'
                            )">Edit</button>
                            <?php if($row['id'] != $_SESSION['user_id']):?>
                            <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return
                            confirm('Yakin menghapus user ini?')">Hapus</a>
                            <?php endif; ?>
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
            <h3>Edit User</h3>
            <form method="post" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="editUsername"  class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="editRole" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="petugas">Petugas</option>
                        <option value="peminjam">Peminjam</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <button type="submit" name="edit" class="btn btn-primary">Update User</button>
            </form>
        </div>                                                
    </div>

    <div style="margin-bottom: 20px;">
        <a href="../register.php?admin=1" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Tambah User Baru
        </a>
    </div>

    <script>
        function editUser(id, username, nama, role) {
            document.getElementedById('editId').value = id;
            document.getElementedById('editUsername').value = username;
            document.getElementedById('editNama').value = nama;
            document.getElementedById('editRole').value = role;
            document.getElementedById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementedById('editModal').style.display = 'none';
        }
    </script>  
</body>
</html>