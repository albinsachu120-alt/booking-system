<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$name = trim($_POST["customer_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$serviceId = filter_input(INPUT_POST, "service_id", FILTER_VALIDATE_INT);
$date = $_POST["appointment_date"] ?? "";

if (
    $name === ""
    || $phone === ""
    || !$serviceId
    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
    || $date < date("Y-m-d")
) {
    die("Invalid details. Please go back and try again.");
}

$serviceStmt = $pdo->prepare(
    "SELECT id FROM services WHERE id = ? AND is_active = 1"
);
$serviceStmt->execute([$serviceId]);

if (!$serviceStmt->fetch()) {
    die("Selected service is not available.");
}

try {
    $pdo->beginTransaction();

    $lastStmt = $pdo->prepare(
        "SELECT MAX(token_no) AS last_token
         FROM appointments
         WHERE service_id = ? AND appointment_date = ?
         FOR UPDATE"
    );
    $lastStmt->execute([$serviceId, $date]);
    $last = $lastStmt->fetch();
    $token = ((int)($last["last_token"] ?? 0)) + 1;

    $insert = $pdo->prepare(
        "INSERT INTO appointments
         (token_no, customer_name, phone, service_id, appointment_date)
         VALUES (?, ?, ?, ?, ?)"
    );
    $insert->execute([$token, $name, $phone, $serviceId, $date]);

    $bookingId = $pdo->lastInsertId();
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Booking could not be saved. Please try again.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="card">
        <h1>Booking Successful!</h1>
        <p>Your token number is:</p>
        <div class="token">#<?= (int)$token ?></div>
        <p>Booking ID: <?= (int)$bookingId ?></p>
        <p>Date: <?= htmlspecialchars($date) ?></p>
        <a class="button-link" href="status.php">View Live Queue</a>
        <a class="button-link secondary" href="index.php">Book Another</a>
    </main>
</body>
</html>