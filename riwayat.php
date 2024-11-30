<?php
session_start();
require 'connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil riwayat transaksi
$query = "SELECT 
            transactions.id AS transaction_id, 
            transactions.total_price, 
            transactions.payment_method, 
            transactions.created_at,
            produk.photo, 
            produk.name
          FROM transactions
          INNER JOIN transaction_items ON transactions.id = transaction_items.transaction_id
          INNER JOIN produk ON transaction_items.product_id = produk.id
          WHERE transactions.user_id = ?
          ORDER BY transactions.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>
    <link rel="stylesheet" href="css/styleriwayat.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <button class="back-button" onclick="history.back()">← Kembali</button>
            <h1>RIWAYAT TRANSAKSI</h1>
        </div>
        <div class="content">
            <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="transaction">
                <img src="img/<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <p><strong>Nama Produk:</strong> <?= htmlspecialchars($row['name']) ?></p>
                <p><strong>Tanggal Transaksi:</strong> <?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></p>
                <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($row['payment_method']) ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($row['total_price'], 0, ',', '.') ?></p>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p>Belum ada riwayat transaksi.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>