<?php
include 'includes/config.php'

$sql = "ALTER TABLE user
    ADD COLUMN email VARCHAR(100) AFTER nama,
    ADD COLUMN no_hp VARCHAR(20) AFTER email,
    ADD COLUMN alamat TEXT AFTER no_hp,
    ADD COLUMN verification_code VARCHAR(100) AFTER role,
    ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER verification_code,
    ADD COLUMN last_login TIMESTAMP NULL AFTER is_verified";

if ($conn->multi_query($sql)) {
    echo "Database update successfully!";
} else {
    echo "Error updating database:".$conn->error;
}
?>