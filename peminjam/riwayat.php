<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('peminjam');

$user_id = $_SESSION['user_id'];

$riwayat = $conn->query("
    SELECT p.*, a.nama_alat
    FROM peminjaman p
    JOIN alat a ON p.alat_id = a.id
    WHERE p.user_id = $user_id
    ORDER BY p.tanggal_pinjam DESC
")
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pinjam - Peminjam</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            color: white;
        }
        .status-diajukan {background: #6c757d;}
        .status-disetujui {background: #28a745;}
        .status-ditolak {background: #dc3545;}
        .status-dikembalikan {background: #007bff;}
    </style>
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
        <h2>Riawayat peminjaman Alat</h2>

        <?php if($riwayat->num_rows > 0): ?>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alat</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $riwayat->fetch_assoc()):?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nama_alat']; ?></td>
                        <td><?php echo $row['jumlah']; ?></td>
                        <td><?php echo formatDate($row['tanggal_pinjam']); ?></td>
                        <td><?php echo $row['tanggal_kembali'] ? formatDate($row['tanggal_kembali']): '-'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>Rp <?php echo number_format($row['denda'], 0,',','.')?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="card">
            <p>Anda belm memiliki riwayat peminjaman.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>