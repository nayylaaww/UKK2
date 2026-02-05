<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth();

$alat = $conn->query("
    SELECT a.*, k.nama_kategori
    FROM alat a
    LEFT JOIN kategori k ON a.kategori_id = k.id
    ORDER BY a.nama_alat
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Alat - Petugas</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="sidebar">
        <h3>Menu Petugas</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="alat.php">Data Alat</a>
        <a href="peminjaman.php">Peminjaman</a>
        <a href="pengembalian.php">pengembalian</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="content">
        <h2>Data Alat</h2>
        <div class="card">
            <h3>Daftar Alat Tersedia</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $alat->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nama_alat']; ?></td>
                            <td><?php echo $row['nama_kategori'] ?? '-'; ?></td>
                            <td><?php echo $row['stok']; ?></td>
                            <td>
                                <?php if($row['stok'] > 0): ?>
                                    <span class="badge badge-success">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>