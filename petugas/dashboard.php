<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth();

$total_alat = $conn->query("SELECT COUNT(*) as total FROM alat")->fetch_assoc()['total'];
$total_peminjaman_hari_ini = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE DATE(tanggal_pinjam) = CURDATE()")->fetch_assoc()['total'];
$peminjaman_menunggu = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'diajukan'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Petugas</title>
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
        <h2>Dashboard Petugas</h2>
        <p>Selamat Datang, <?php echo $_SESSION['nama'] ?? $_SESSION['username']; ?>!</p>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Alat</h3>
                <p><?php echo $total_alat; ?></p>
            </div>

            <div class="stat-card">
                <h3>Peminjaman Hari Ini</h3>
                <p><?php echo $total_peminjaman_hari_ini; ?></p>
            </div>

            <div class="stat-card">
                <h3>Menunggu Persetujuan</h3>
                <p><?php echo $peminjaman_menunggu; ?></p>
            </div>
        </div>

            <div class="card">
                <h3>Peminjam Terbaru</h3>
                <?php
                $recent = $conn->query("
                    SELECT p.*, a.nama_alat, u.nama
                    FROM peminjaman p
                    JOIN alat a ON p.alat_id = a.id
                    JOIN user u ON p.user_id = u.id
                    ORDER BY p.tanggal_pinjam DESC
                    LIMIT 5
                ");
                
                if($recent->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Alat</th>
                            <th>Peminjam</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent->fetch_assoc()):?>
                            <tr>
                                <td><?php echo $row['nama_alat']; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo formatDate($row['tanggal_pinjam']); ?></td>
                                <td><?php echo ucfirst($row['status']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>Tidak ada data peminjaman.</p>
                    <?php endif; ?>
        </div>
    </div>
</body>
</html>