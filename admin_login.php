<?php
require "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST["username"] ?? "");
    $pass = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin["password_hash"])) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_name"] = $admin["username"];
        header("Location: admin.php");
        exit;
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="card">
        <h2>Administrator Login</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>