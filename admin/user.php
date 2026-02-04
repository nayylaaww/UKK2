<?php
session_start();

include'../includes/config.php';
include'../includes/auth.php';
include'../includes/functions.php';

checkAuth('admin');

if (isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = paassword_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO user (username, password, nama, role) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $username, $password, $nama, $role);

    if ($stmt->execute) {
        logActivity($_SESSION['user_id'], "Menambah user: $username($role)");
        $message = showAlert('success', 'User berhasil ditambahkan!');
    } else {
        $message = showAlert('danger', 'Gagal menambah user! Username mungkin sudah ada.');
    }
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET nama = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET nama = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $role, $id);
    }

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], "Mengedit user ID $id");
        $message = showAlert('success', 'User berhasil diupdate!');
    } else {
        $message = showAlert('danger', 'Gagal mengupdate user!');
    } 
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    if ($id !=$_SESSION['user_id']) {
        $conn->query("DELETE FROM user WHERE id = $id");
        logActivity($_SESSION['user_id'], "Menghapus user ID: $id");
        $message = showAlert('success', 'User berhasil dihapus!');
    } else {
        $message = showAlert('danger', 'Tidak bisa menghapus akun sendiri!');
    }
}
?>