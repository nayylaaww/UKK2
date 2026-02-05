<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth();

if (isset($_POST['kembalikan'])) {
    $id = $_POST['id'];
    $denda = $_POST['denda'] ?? 0;
    $tanggal_kembali = $_POST['tanggal_kembali'];

    $stmt = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = ?, denda = ? WHERE id = ?");
    $stmt->bind_param("sdi", $tanggal_kembali, $denda, $id);

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], "Mencatat pengembalian ID: $id");
        $message = showAlert('success', 'Pengembalian berhasil dicatat!');
    } else {
        $message = showAlert('danger', 'Gagal mencatat  pengembalian!');
    }
}

$peminjaman_aktif = $conn->query("
    SELECT p.*, a.nama_alat, u.nama
    FROM peminjaman p
    JOIN alat a ON p.alat_id = a.id
    JOIN user u ON p.user_id = u.id
    WHERE p.status = 'disetujui' AND p.tanggal_kembali IS NULL
    ORDER BY p.tanggal_pinjam
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengambilan Alat - Petugas</title>
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
        <h2>Pengembalian Alat</h2>

        <?php if(isset($message)) echo $message; ?>

        <?php if($peminjaman_aktif->num_rows > 0):?>
        <div class="card">
            <h3>Peminjaman Aktif</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alat</th>
                        <th>Peminjam</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Durasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $peminjaman_aktif->fetch_assoc()):
                        $durasi = (strtotime(date('Y-m-d'))-strtotime($row['tanggal_pinjam'])) / (60*60*24);
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nama_alat']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['jumlah']; ?></td>
                        <td><?php echo formatDate($row['tanggal_pinjam']); ?></td>
                        <td><?php echo floor($durasi); ?>hari</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="showKembalikanModal(<?php echo $row['id']; ?>)">Kembalikan</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p>Tidak ada peminjaman aktif.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="kembalikanModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">$times;</span>
            <h3>Catatan Pengembalian</h3>
            <form method="POST" id="kembalikanForm">
            <input type="hidden" name="id" id="kembalikanId">
            <div class="form-group">
                <label>Tangal Pengembalian</label>
                <input type="date" name="tanggal_kembali" id="tanggalKembali" class="form-control"
                value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Denda (Rp)</label>
                <input type="number" name="denda" id="denda" class="form-control" value="0" min="0">
            </div>
            <button type="submit" name="kembalikan" class="btn btn-primary">Simpan Pengembalian</button>
            </form>
        </div>
    </div>

    <script>
        function showKembalikanModal(id) {
            document.getElementedById('kembalikanId').value = id;
            document.getElementedById('kembalikanModal').style.display = 'block';
        }
        function closeMdoal() {
            document.getElementedById('kembalikanModal').style.display = 'none';
        }
    </script>
</body>
</html>