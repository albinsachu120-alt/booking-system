<?php
require "config.php";

$username = "admin";
$plainPassword = "ChangeMe123!";
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO admins (username, password_hash) VALUES (?, ?)"
);

try {
    $stmt->execute([$username, $hash]);
    echo "Admin created. Username: admin | Password: ChangeMe123!<br>";
    echo "Delete setup_admin.php now.";
} catch (PDOException $e) {
    echo "Admin may already exist. Do not run setup repeatedly.";
}
?>
