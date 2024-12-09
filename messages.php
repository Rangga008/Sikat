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
    produk.id AS product_id, -- Tambahkan ini
    produk.photo, 
    produk.name,
    users.id AS store_id, 
    users.nama_toko,
    product_ratings.rating AS product_rating,
    store_ratings.rating AS store_rating
FROM transactions
INNER JOIN transaction_items ON transactions.id = transaction_items.transaction_id
INNER JOIN produk ON transaction_items.product_id = produk.id
INNER JOIN users ON produk.user_id = users.id
LEFT JOIN product_ratings ON product_ratings.transaction_id = transactions.id
LEFT JOIN store_ratings ON store_ratings.transaction_id = transactions.id
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
            <button class="back-button" onclick="window.location.href='index.php'">Kembali</button>
            <h1>RIWAYAT RATING</h1>
        </div>
        <div class="content">
            <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="transaction">
                <img src="<?= htmlspecialchars(!empty($row['photo']) ? "img/{$row['photo']}" : "img/default.jpg") ?>"
                    alt="<?= htmlspecialchars($row['name']) ?>"
                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                <p><strong>Nama Produk:</strong> <?= htmlspecialchars($row['name']) ?></p>
                <p><strong>Tanggal Transaksi:</strong> <?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></p>
                <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($row['payment_method']) ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($row['total_price'], 0, ',', '.') ?></p>

                <!-- Rating Produk -->
                <?php if (empty($row['product_rating'])): ?>
                <form action="proses/rate_product.php" method="POST">
                    <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>"> <!-- Corrected here -->
                    <label for="product_rating"><strong>Rating Produk:</strong></label>
                    <select name="product_rating" required>
                        <option value="">Pilih Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit">Kirim Rating Produk</button>
                </form>
                <?php else: ?>
                <p><strong>Rating Produk:</strong> <?= $row['product_rating'] ?> ⭐</p>
                <?php endif; ?>

                <!-- Rating Toko -->
                <?php if (empty($row['store_rating'])): ?>
                <form action="proses/rate_store.php" method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
                    <input type="hidden" name="store_id" value="<?= $row['store_id'] ?>">
                    <label for="store_rating"><strong>Rating Toko:</strong></label>
                    <select name="store_rating" required>
                        <option value="">Pilih Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit">Kirim Rating Toko</button>
                </form>
                <?php else: ?>
                <p><strong>Rating Toko:</strong> <?= $row['store_rating'] ?> ⭐</p>
                <?php endif; ?>
            </div>

            <?php endwhile; ?>
            <?php else: ?>
            <p>Belum ada riwayat transaksi.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>