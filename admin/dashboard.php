<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$total_users = $conn->query("SELECT COUNT(*) as total FROM user")->fetch_assoc()['total'];
$total_alat = $conn->query("SELECT COUNT(*) as total FROM alat")->fetch_assoc()['total'];
$total_peminjaman = $conn->query("SELECT COUNT(*) as total FROM peminjaman")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
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
        <h2>Dashboard Admin</h2>
        <div class="stats">
            <div class="stat-card">
                <h3>Total User</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Alat</h3>
                <p><?php echo $total_alat; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Peminjaman</h3>
                <p><?php echo $total_peminjaman; ?></p>
            </div>
        </div>
    </div>
</body>
</html>