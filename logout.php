<?php
session_start();
include 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    $stmt = $conn->prepare("INSERT INTO log_aktivitas(user_id, aktivitas) VALUES (?, ?)");
    $aktivitas = "Logout dari sistem";
    $stmt->bind_param("is", $user_id, $aktivitas);
    $stmt->execute();
}

session_unset();
session_destroy();

header('Location: index.php?login=true');
exit();


?>