<?php
require 'connection.php';
session_start();

$userId = $_SESSION['user_id'] ?? 0;

if ($userId == 0) {
    // Jika pengguna belum login, redirect ke halaman login
    header("Location: login.php");
    exit;
}

// Ambil semua notifikasi untuk pengguna
$sql = "SELECT * FROM notifications WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi</title>
</head>

<body>
    <h1>Notifikasi Anda</h1>
    <?php if (empty($notifications)): ?>
    <p>Tidak ada notifikasi baru.</p>
    <?php else: ?>
    <ul>
        <?php foreach ($notifications as $notification): ?>
        <li>
            <?= htmlspecialchars($notification['message']) ?>
            <?php if ($notification['is_read']): ?>
            <span>(Sudah dibaca)</span>
            <?php else: ?>
            <span>(Belum dibaca)</span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</body>

</html>