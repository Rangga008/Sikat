<?php
session_start(); // Untuk mendapatkan ID user dari session
require 'connection.php'; // Koneksi ke database

// Mengecek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $username = $_SESSION['username'];  // Menampilkan username pengguna
    $role = $_SESSION['role'];  // Menampilkan role pengguna
} else {
    $isLoggedIn = false;
    // Arahkan pengguna ke halaman login jika tidak login
    header('Location: auth/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Query untuk mendapatkan isi keranjang
$query = "SELECT 
            cart.id AS cart_id, 
            produk.id AS product_id, 
            produk.name, 
            produk.photo, 
            produk.price, 
            produk.stock,
            cart.quantity 
          FROM cart 
          INNER JOIN produk ON cart.product_id = produk.id 
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Mengambil daftar voucher
$voucherQuery = "SELECT * FROM vouchers WHERE valid_until >= CURDATE()";
$voucherStmt = $conn->prepare($voucherQuery);
$voucherStmt->execute();
$voucherResult = $voucherStmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <link rel="stylesheet" href="css/stylekeranjang.css">
    <link rel="stylesheet" href="css/stylenavbar.css" />
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-container">
                <div class="logo">
                    <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
                </div>
                <!-- Other header elements remain the same -->
            </div>
        </div>

        <div class="content">
            <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="cart-item">
                <img src="img/<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <div class="item-info">
                    <h2><?= htmlspecialchars($row['name']) ?></h2>
                    <p>Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                    <p><strong>Stok:</strong> <?= $row['stock'] ?> unit</p>
                </div>
                <div class="actions">
                    <form method="POST" action="proses/update_cart.php">
                        <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                        <div class="quantity">
                            <button name="action" value="decrease">-</button>
                            <span><?= $row['quantity'] ?></span>
                            <button name="action" value="increase">+</button>
                        </div>
                    </form>
                    <form method="POST" action="proses/delete_cart.php">
                        <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                        <button type="submit" class="delete-button">HAPUS</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p>Keranjang Anda kosong.</p>
            <?php endif; ?>
        </div>

        <!-- Form untuk metode pembayaran dan voucher -->
        <div class="payment-section">
            <button class="btn" id="paymentBtn">Pilih Metode Pembayaran</button>
            <form method="POST" action="proses/apply_voucher.php">
                <h3>Pilih Voucher</h3>
                <select name="voucher_code">
                    <option value="">Pilih Voucher</option>
                    <?php while ($voucher = $voucherResult->fetch_assoc()): ?>
                    <option value="<?= $voucher['code'] ?>"><?= $voucher['code'] ?> - Diskon
                        <?= $voucher['discount_percentage'] ?>%</option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn">Gunakan Voucher</button>
            </form>
        </div>

        <!-- Pop-up untuk metode pembayaran -->
        <div class="popup" id="popup">
            <div class="popup-content">
                <button class="close-btn" id="closeButton">&times;</button>
                <h2>Opsi Pembayaran</h2>
                <div class="payment-options">
                    <button class="payment-btn">
                        <img src="img/kartu.png" alt="Kartu"> Kartu Kredit
                    </button>
                    <button class="payment-btn">
                        <img src="img/dompet.png" alt="Dompet"> E-Wallet
                    </button>
                    <button class="payment-btn">
                        <img src="img/tunai.png" alt="Tunai"> Tunai
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>