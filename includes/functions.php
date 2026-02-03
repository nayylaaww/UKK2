<?php
include 'config.php';

function logActivity($user_id, $aktivitas)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO log_aktivitas(user_id, aktivitas) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $aktivitas);
    $stmt->execute();
}
function formatDate($date, $format = 'd-m-Y')
{
    return date($format, strtotime($date));
}
function calculateDenda($tanggal_kembali, $tanggal_seharusnya, $denda_per_hari = 5000)
{
    $tgl_kembali = new DateTime($tanggal_kembali);
    $tgl_seharusnya = new DateTime($tanggal_seharusnya);

    if ($tgl_kembali > $tgl_seharusnya) {
        $selisih = $tgl_seharusnya->diff($tgl_kembali);
        $hari_terlambat = $selisih->days;
        return $hari_terlambat * $denda_per_hari;
    }
    return 0;
}

function getAlatInfo($alat_id)
{
    $stmt = $conn->prepare("SELECT nama_alat, stok FROM alat WHERE id = ?");
    $stmt->bind_param("i", $alat_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserInfo($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT username, nama FROM user WHERE id =?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function redirect($url, $delay = 0)
{
    if ($delay > 0) {
        echo "<meta http-equiv='refresh' content='$delay;url=$url'>"; 
    } else {
        header("Location: $url");
    }
    exit();
    
}

function showAlert($type, $message)
{
    $class = $type == 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $class'>$message</div>";
    
}
?>