<?php
require 'connection.php';
session_start();

$userId = $_SESSION['user_id'] ?? 0;

// Ambil riwayat pembelian
$sql = "SELECT * FROM riwayat WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian</title>
</head>

<body>
    <h1>Riwayat Pembelian</h1>
    <?php if ($result->num_rows > 0): ?>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
        <li>
            <?= htmlspecialchars($row['product_name']) ?> -
            Rp <?= number_format($row['price'], 0, ',', '.') ?> -
            <?= htmlspecialchars($row['date']) ?>
        </li>
        <?php endwhile; ?>
    </ul>
    <?php else: ?>
    <p>Belum ada riwayat pembelian!</p>
    <?php endif; ?>
</body>

</html>