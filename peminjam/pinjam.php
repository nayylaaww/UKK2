<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('peminjam');

$user_id = $_SESSION['user_id'];

if (isset($_POST['ajukan'])) {
    $alat_id = $_POST['alat_id'];
    $jumlah = $_POST['jumlah'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];

    $stok = $conn->query("SELECT stok FROM alat WHERE id = $alat_id")->fetch_assoc()['stok'];

    if ($jumlah <= $stok) {
        $stmt = $conn->prepare("INSERT INTO peminjaman (alat_id, user_id, jumlah, tanggal_pinjam, status)
        VALUES (?,?,?,?, 'diajukan')");
        $stmt->bind_param("iiis", $alat_id, $user_id, $jumlah, $tanggal_pinjam);

        if ($stmt->execute()) {
            logActivity($user_id, "Mengajukan peminjaman alat ID: $alat_id");
            $message = showAlert('success', 'Pengajuan peminjaman berhasil! Menunggu persetujuan petugas.');
        } else {
            $message = showAlert('danger', 'Gagal mengajukan peminjaman!');
        }
    } else {
        $message = showAlert('danger', 'Stok tidak mencukupi! Stok tersedia: '. $stok);
    }
    }

    $alat = $conn->query("
        SELECT a.*, k.nama_kategori
        FROM alat a
        LEFT JOIN kategori k ON a.kategori_id = k.id
        WHERE a.stok > 0
        ORDER BY a.nama_alat
    ");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pinjam Alat - Peminjam</title>
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
        <h2>Pinjam Alat</h2>

        <?php if(isset($message)) echo $message; ?>

        <div class="card">
            <h3>Ajukan Peminjaman</h3>
            <form method="POST" class="form">
            <div class="form-group">
                <label>Pilih Alat</label>
                <select name="alat_id" class="form-control" required>
                    <option value="">-- Pilih Alat --</option>
                    <?php while($row = $alat->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo $row['nama_alat']; ?> (Stok:<?php echo $row['stok']; ?>)-<?php echo
                        $row['nama_kategori']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah</label>
                <input type="number" name="jumlah" class="form-control" min="1" required>
            </div>
            <div class="form-group">
                <label>Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <button type="submit" name="ajukan" class="btn btn-primary">Ajukan Peminjaman</button>
            </form>
        </div>

        <div class="card">
            <h3>Alat Tersedia</h3>
            <?php
            $alat->data_seek(0);
            if ($alat->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $alat->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['nama_alat']; ?></td>
                        <td><?php echo $row['nama_kategori']; ?></td>
                        <td><?php echo $row['stok']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>Tdiak ada alat yang tersedia untuk dipinjam.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>