<?php

function checkAuth($requiredRole = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
    if ($requiredRole && $_SESSION['role'] != $requiredRole) {
        header('Location: ../index.php?error=unauthorized');
        exit();
    }
    return true;
}
function isAdmin() 
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}
function isPetugas()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'petugas' || $_SESSION['role'] == 'admin';
}
function isPeminjam()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'peminjam';
}
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}
function getCurrentUsername()
{
    return $_SESSION['username'] ?? null;
}
function getCurrentRole()
{
    return $_SESSION['role'] ?? null;
}
?>