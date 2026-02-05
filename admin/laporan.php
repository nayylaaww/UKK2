<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('admin');

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$query = "SELECT p.*, a.nama_alat, u.nama as peminjam
    FROM peminjaman p
    JOIN alat a ON p.alat_id = a.id
    JOIN user u ON p.user_id = u.id
    WHERE p.tanggal_pinjam BETWEEN ? AND ?
    ORDER BY p.tanggal_pinjam DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$peminjaman = $stmt->get_result();

$total_peminjaman = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE tanggal_pinjam
BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'];

$total_denda = $conn->query("SELECT SUM(denda) as total FROM peminjaman WHERE tanggal_pinjam
BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'];
$alat_terpopuler = $conn->query("
    SELECT a.nama_alat, COUNT(p.id) as jumlah
    FROM peminjaman p
    JOIN alat a ON p.alat_id = a.id
    WHERE p.tanggal_pinjam BETWEEN '$start_date' AND '$end_date'
    GROUP BY p.alat_id
    ORDER BY jumlah DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan - Admin</title>
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
        <h2>Laporan Peminjaman</h2>

        <div class="card">
            <h3>Filter Laporan</h3>
            <form method="GET" class="form-inline">
                <label>Dari Tanggal:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>

                <label>Sampai Tanggal:</label>
                <input type="date" name="end_date" value="<?php echo $start_date; ?>" required>

                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="laporan.php" class="btn btn-secondary">Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-success">Cetak Laporan</button>
            </form>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Peminjaman</h3>
                <p><?php echo $total_peminjaman; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Deda</h3>
                <p><?php echo number_format($total_denda, 0,',','.'); ?></p>
            </div>
        </div>

        <div class="card">
            <h3>5 Alat Terpopuler</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Alat</th>
                        <th>Jumlah Peminjaman</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = $alat_terpopuler->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nama_alat']; ?></td>
                        <td><?php echo $row['jumlah']; ?>kali</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Detail Peminjaman</h3>
            <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Alat</th>
                    <th>Peminjam</th>
                    <th>Jumlah</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $peminjaman->fetch_assoc()): ?> 
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['nama_alat']; ?></td>
                    <td><?php echo $row['peminjam']; ?></td>
                    <td><?php echo $row['jumlah']; ?></td>
                    <td><?php echo formatDate($row['tanggal_pinjam']); ?></td>
                    <td><?php echo $row['tanggal_kembali'] ? formatDate($row['tanggal_kembali']):'-'; ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td>Rp<?php echo number_format($row['denda'],0,',','.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            </table>
        </div>
    </div>
</body>
</html>