<?
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('peminjam');

$user_id = $_SESSION['user_id'];

$total_peminjaman = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE user_id = $user_id")-
>fetch_assoc()['total'];

$pinjaman_aktif = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE user_id = $user_id 
AND status = 'disetujui' AND tanggal_kembali IS NULL")->fetch_assoc()['total'];

$total_denda = $conn->query("SELECT SUM(denda) as total FROM peminjaman WHERE user_id = $user_id")-
>fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Peminjaman</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="sidebar">
    <h3>Menu Peminjam</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="pinjam.php">Kelola Alat</a>
        <a href="riwayat.php">Riwayat Pinjam</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="content">
        <h2>Dashboard Peminjam</h2>
        <p>Selamat Datang, <?php echo $_SESSION['nama'] ?? $_SESSION['username']; ?>!</p>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Pinjaman</h3>
                <p><?php echo $total_pinjaman; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pinjaman Aktif</h3>
                <p><?php echo $pinjaman_aktif; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Denda</h3>
                <p>Rp<?php echo number_format($total_denda, 0,',','.'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>