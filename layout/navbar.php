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
                <select name="category" style="border-radius: 5px; padding: 8px; border: none; margin-right: 5px;">
                    <option value="">Semua Kategori</option>
                    <?php
            // Ambil kategori dari database
            $sqlCategories = "SELECT id, name FROM categories";
            $categoriesResult = $conn->query($sqlCategories);
            while ($categoryRow = $categoriesResult->fetch_assoc()) {
                $selected = (isset($_GET['category']) && $_GET['category'] == $categoryRow['id']) ? 'selected' : '';
                echo '<option value="' . $categoryRow['id'] . '" ' . $selected . '>' . htmlspecialchars($categoryRow['name']) . '</option>';
            }
            ?>
                </select>
                <input type="text" name="query" placeholder="Cari produk..."
                    value="<?= htmlspecialchars($_GET['query'] ?? '') ?>"
                    style="flex-grow: 1; padding: 8px; border: none; border-radius: 5px; margin-right: 5px;">
                <button type="submit"
                    style="padding: 0px; border: none; border-radius: 0px; background-color: #ffcc00;">
                    <img src="img/search.png" alt="Search Icon" style="width: 30px; height: 30px;">
                </button>
            </div>
        </form>
        <div class="header-icons">
            <a href="cart.php"><img src="img/cart.png" alt="Cart Icon" /></a>
            <a href="messages.php"><img src="img/chat.png" alt="Chat Icon" /></a>
            <a href="profile.php"><img src="img/setting.png" alt="Settings Icon" /></a>
            <?php if ($isLoggedIn): ?>
            <!-- Jika pengguna memiliki peran admin atau owner, tampilkan ikon inbox -->
            <?php if ($role === 'admin' || $role === 'owner'): ?>
            <a href="admin/dashboard.php"><img src="img/inbox.png" alt="Inbox Icon" /></a>
            <?php endif; ?>

            <!-- Jika pengguna memiliki peran hanya owner, tampilkan ikon manager -->
            <?php if ($role === 'owner'): ?>
            <a href="admin/manager.php"><img src="img/manager.png" alt="Manager Icon" /></a>
            <?php endif; ?>
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