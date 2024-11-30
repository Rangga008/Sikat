<?php
session_start();
require 'connection.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dari cart
$query = "SELECT cart.id AS cart_id, produk.name, produk.price, cart.quantity 
          FROM cart 
          INNER JOIN produk ON cart.product_id = produk.id 
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total_price += $row['subtotal'];
    $cart_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/stylecheckout.css">
</head>

<body>
    <h1>Checkout</h1>
    <div class="cart-items">
        <?php foreach ($cart_items as $item): ?>
        <div class="cart-item">
            <p><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</p>
            <p>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <p><strong>Total Harga: </strong>Rp <?= number_format($total_price, 0, ',', '.') ?></p>

    <form method="POST" action="proses/checkout_process.php">
        <label for="payment-method">Metode Pembayaran:</label>
        <select name="payment_method" id="payment-method" required>
            <option value="cash">Cash</option>
            <option value="card">Kartu Kredit/Debit</option>
            <option value="e-wallet">E-Wallet</option>
        </select>

        <label for="voucher-code">Kode Voucher:</label>
        <input type="text" name="voucher_code" id="voucher-code">

        <button type="submit">Bayar Sekarang</button>
    </form>
</body>

</html>