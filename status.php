<?php
require "config.php";

$date = $_GET["date"] ?? date("Y-m-d");

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date("Y-m-d");
}

$sql = "SELECT
            a.token_no,
            a.status,
            s.service_name,
            s.avg_minutes
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE a.appointment_date = ?
        ORDER BY s.service_name, a.token_no";

$stmt = $pdo->prepare($sql);
$stmt->execute([$date]);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="15">
    <title>Live Queue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Live Queue Status</h1>
        <nav>
            <a href="index.php">Book Token</a>
            <a href="admin_login.php">Admin</a>
        </nav>
    </header>

    <main class="card wide">
        <h2>Queue for <?= htmlspecialchars($date) ?></h2>
        <p class="muted">This page refreshes every 15 seconds.</p>

        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Token</th>
                    <th>Status</th>
                    <th>Estimated wait</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="4">No bookings found for this date.</td>
                </tr>
            <?php else: ?>
                <?php
                $waitingByService = [];
                foreach ($rows as $row):
                    $key = $row["service_name"];

                    if (!isset($waitingByService[$key])) {
                        $waitingByService[$key] = 0;
                    }

                    $wait = $row["status"] === "Waiting"
                        ? $waitingByService[$key] * (int)$row["avg_minutes"]
                        : 0;

                    if ($row["status"] === "Waiting") {
                        $waitingByService[$key]++;
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row["service_name"]) ?></td>
                        <td>#<?= (int)$row["token_no"] ?></td>
                        <td><?= htmlspecialchars($row["status"]) ?></td>
                        <td>
                            <?= $row["status"] === "Waiting"
                                ? (int)$wait . " min (approx.)"
                                : "—"
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>