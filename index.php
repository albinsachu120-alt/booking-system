<?php
require "config.php";

$services = $pdo->query(
    "SELECT id, service_name
     FROM services
     WHERE is_active = 1
     ORDER BY service_name"
)->fetchAll();

$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Queue - Book Token</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Smart Queue</h1>
        <nav>
            <a href="index.php">Book Token</a>
            <a href="status.php"> live dashboard</a>
            <a href="admin_login.php">Admin</a>
        </nav>
    </header>

    <main class="card">
        <h2>Book an Appointment / Token</h2>
        <p>Enter your details and select the service and date.</p>

        <form action="book.php" method="POST">
            <label for="customer_name">Full Name</label>
            <input type="text" id="customer_name" name="customer_name" maxlength="100" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" maxlength="20" required>

            <label for="service_id">Select Service</label>
            <select id="service_id" name="service_id" required>
                <option value="">-- Choose service --</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= (int)$service['id'] ?>">
                        <?= htmlspecialchars($service['service_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="appointment_date">Appointment Date</label>
            <input
                type="date"
                id="appointment_date"
                name="appointment_date"
                min="<?= htmlspecialchars($today) ?>"
                value="<?= htmlspecialchars($today) ?>"
                required
            >

            <button type="submit">Book Token</button>
        </form>
    </main>
</body>
</html>