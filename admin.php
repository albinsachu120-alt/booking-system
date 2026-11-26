<?php
require "config.php";

if (empty($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "appointment_id", FILTER_VALIDATE_INT);
    $newStatus = $_POST["new_status"] ?? "";
    $allowed = ["Waiting", "Serving", "Completed", "Cancelled"];

    if ($id && in_array($newStatus, $allowed, true)) {
        $update = $pdo->prepare(
            "UPDATE appointments SET status = ? WHERE id = ?"
        );
        $update->execute([$newStatus, $id]);
    }

    header("Location: admin.php");
    exit;
}

$today = date("Y-m-d");
$counts = $pdo->prepare(
    "SELECT
        SUM(status = 'Waiting') AS waiting_count,
        SUM(status = 'Serving') AS serving_count,
        SUM(status = 'Completed') AS completed_count
     FROM appointments
     WHERE appointment_date = ?"
);
$counts->execute([$today]);
$summary = $counts->fetch();

$list = $pdo->prepare(
    "SELECT
        a.id,
        a.token_no,
        a.customer_name,
        a.phone,
        a.appointment_date,
        a.status,
        s.service_name
     FROM appointments a
     JOIN services s ON s.id = a.service_id
     WHERE a.appointment_date = ?
     ORDER BY a.created_at ASC"
);
$list->execute([$today]);
$appointments = $list->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="status.php">Public Queue</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="wide">
        <section class="stats">
            <div class="stat">
                <b><?= (int)($summary["waiting_count"] ?? 0) ?></b>
                <span>Waiting</span>
            </div>
            <div class="stat">
                <b><?= (int)($summary["serving_count"] ?? 0) ?></b>
                <span>Serving</span>
            </div>
            <div class="stat">
                <b><?= (int)($summary["completed_count"] ?? 0) ?></b>
                <span>Completed</span>
            </div>
        </section>

        <section class="card wide">
            <h2>Today's Appointments — <?= htmlspecialchars($today) ?></h2>

            <table>
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td>#<?= (int)$appointment["token_no"] ?></td>
                        <td><?= htmlspecialchars($appointment["customer_name"]) ?></td>
                        <td><?= htmlspecialchars($appointment["phone"]) ?></td>
                        <td><?= htmlspecialchars($appointment["service_name"]) ?></td>
                        <td><?= htmlspecialchars($appointment["status"]) ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden"
                                       name="appointment_id"
                                       value="<?= (int)$appointment["id"] ?>">
                                <select name="new_status">
                                    <?php foreach (["Waiting", "Serving", "Completed", "Cancelled"] as $status): ?>
                                        <option value="<?= $status ?>"
                                                <?= $appointment["status"] === $status ? "selected" : "" ?>>
                                            <?= $status ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$appointments): ?>
                    <tr>
                        <td colspan="6">No appointments today.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>