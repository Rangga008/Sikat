<?php 
session_start(); // Memulai sesi

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
?>

<header>
    <div class="header-container">
        <div class="logo" href="index.php">
            <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
        </div>
        <form action="search.php" method="GET">
            <div class="search-bar">
                <input type="text" name="query" placeholder="Cari produk..."
                    value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" required>
                <button type="submit"><img src="img/search.png" alt="Search Icon" /></button>
            </div>
        </form>
        <div class="header-icons">
            <a href="cart.php"><img src="img/cart.png" alt="Cart Icon" /></a>
            <a href="messages.php"><img src="img/chat.png" alt="Chat Icon" /></a>
            <a href="profile.php"><img src="img/setting.png" alt="Settings Icon" /></a>
            <?php if ($isLoggedIn && $role === 'admin'): ?>
            <a href="admin/dashboard.php"><img src="img/inbox.png" alt="Settings Icon" /></a>
            <?php endif; ?>
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
</header>