<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth();

if (isset($_GET['setujui'])) {
    $id = $_GET['setujui'];
    $conn->query("UPDATE peminjaman SET status = 'disetujui' WHERE id = $id");
    logActivity($_SESSION['user_id'], "Menyetujui peminjaman ID: $id");
    $message = showAlert('success', 'Peminjaman berhasil disetujui!');
}

if (isset($_GET['tolak'])) {
    $id = $_GET['tolak'];
    $conn->query("UPDATE peminjaman SET status = 'ditolak' WHERE id = $id");
    logActivity($_SESSION['user_id'], "Menolak peminjaman ID: $id");
    $message = showAlert('success', 'Peminjaman berhasil ditolak!');
}

$peminjaman = $conn->query("
    SELECT p.*, a.nama_alat, u.nama
    FROM peminjaman p
    JOIN alat a ON p.alat_id = a.id
    JOIN user u ON p.user_id = u.id
    WHERE p.status = 'diajukan'
    ORDER BY p.tanggal_pinjam DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Peminjaman - Petugas</title>
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
        <h2>Persetujuan Peminjaman</h2>

        <?php if(isset($message)) echo $message; ?>

        <?php if($peminjaman->num_rows > 0):?>
        <div class="card">
            <h3>Peminjaman menunggu persetujuan</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alat</th>
                        <th>Peminjam</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $peminjaman->fetch_assoc()):?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nama_alat']; ?></td>
                            <td><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['jumlah']; ?></td>
                            <td><?php echo formatDate($row['tanggal_pinjam']); ?></td>
                            <td>
                                <a href="?setujui=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" onclick="return
                                confirm('Setujui peminjaman?')">Setujui</a>

                                <a href="?tolak=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return
                                confirm('Tolak peminjaman?')">Tolak</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="card">
                <p>Tidak ada peminjaman yang menunggu persetujuan.</p>
            </div>
            <?php endif; ?>
    </div>
</body>
</html>