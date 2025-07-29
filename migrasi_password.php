<?php

require_once 'config/db.php';
$gurus = $con->query("SELECT id_guru, password FROM tb_guru");
while ($guru = $gurus->fetch_assoc()) {
    $id = $guru['id_guru'];
    $password = $guru['password'];

    // Check if the password is already hashed
    if (!password_get_info($password)['algo']) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update the password in the database
        $con->query("UPDATE tb_guru SET password='$hashedPassword' WHERE id_guru='$id'");
        echo "Password for Guru ID $id has been updated.<br>";
    } else {
        echo "Guru ID $id already has a hashed password.<br>";
    }
}
?>