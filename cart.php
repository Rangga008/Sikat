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
            cart.quantity,
            cart.condiments
          FROM cart 
          INNER JOIN produk ON cart.product_id = produk.id 
          WHERE cart.user_id = ?";
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
    <title>Keranjang</title>
    <link rel="stylesheet" href="css/stylekeranjang.css">
    <link rel="stylesheet" href="css/stylenavbar.css" />
</head>

<body>
    <div class="header">
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
            </div>
            <div class="header-icons">
                <a href="cart.php"><img src="img/cart.png" alt="Cart Icon" /></a>
                <a href="messages.php"><img src="img/chat.png" alt="Chat Icon" /></a>
                <a href="profile.php"><img src="img/setting.png" alt="Settings Icon" /></a>
                <?php if ($isLoggedIn && $role === 'admin'): ?>
                <a href="admin/dashboard.php"><img src="img/inbox.png" alt="Inbox Icon" /></a>
                <?php endif; ?>
            </div>
            <div class="payment-section">
                <a href="checkout.php" class="pay-now-header">Bayar Sekarang</a>
            </div>
            <div class="login">
                <?php if ($isLoggedIn): ?>
                <p><?php echo $_SESSION['username']; ?>!</p>
                <a href="auth/logout.php">Logout</a>
                <?php else: ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php">SignUp</a>
                <?php endif; ?>
            </div>
        </div>
        <h1>KERANJANG</h1>
    </div>
    <div class="container">


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

                        <!-- Condiments radio buttons -->
                        <br>
                        <div class="condiments">
                            <label>
                                <input type="radio" name="condiments" value="sambal"
                                    <?= !empty($row['condiments']) && strpos($row['condiments'], 'sambal') !== false ? 'checked' : '' ?>>
                                Sambal
                            </label>
                            <label>
                                <input type="radio" name="condiments" value="bawang"
                                    <?= !empty($row['condiments']) && strpos($row['condiments'], 'bawang') !== false ? 'checked' : '' ?>>
                                Sayuran
                            </label>
                            <label>
                                <input type="radio" name="condiments" value="lauk"
                                    <?= !empty($row['condiments']) && strpos($row['condiments'], 'lauk') !== false ? 'checked' : '' ?>>
                                Lauk Lainnya
                            </label>
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


            <!-- Tombol Bayar Sekarang -->
        </div>
</body>

</html>