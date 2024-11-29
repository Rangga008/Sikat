<?php
session_start(); // Memulai sesi
require_once '../connection.php';
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
// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Query untuk mengambil data pengguna
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form untuk update profil
    $username = $_POST['username'] ?? '';
    $nama_toko = $_POST['nama_toko'] ?? '';
    $kontak = $_POST['kontak'] ?? '';
    $email = $_POST['email'] ?? '';

    // Pastikan data ada sebelum lanjut
    if (empty($username) || empty($nama_toko) || empty($kontak) || empty($email)) {
        echo "Data tidak lengkap.";
    } else {
        // Update query
        $update_sql = "UPDATE users SET username = ?, nama_toko = ?, kontak = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $username, $nama_toko, $kontak, $email, $user_id);

        if ($stmt->execute()) {
            // Berhasil update, arahkan ke halaman profil setelah update
            header('Location: dashboard.php');
            exit;
        } else {
            echo "Gagal memperbarui profil: " . $stmt->error;
        }
    }
}

// Query untuk mengambil produk yang dimiliki oleh user
$query = "SELECT * FROM produk WHERE user_id = ? ORDER BY sales_count DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile Page</title>
    <link rel="stylesheet" href="../css/styledashboard.css" />
    <link rel="stylesheet" href="../css/stylenavbar.css" />
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo" href="index.php">
                <a href="../index.php"><img src="../img/logo.png" alt="Logo"></a>
            </div>
            <form action="search.php" method="GET">
                <div class="search-bar">
                    <input type="text" name="query" placeholder="Cari produk..."
                        value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" required>
                    <button type="submit"><img src="../img/search.png" alt="Search Icon" /></button>
                </div>
            </form>
            <div class="header-icons">
                <a href="../cart.php"><img src="../img/cart.png" alt="Cart Icon" /></a>
                <a href="../messages.php"><img src="../img/chat.png" alt="Chat Icon" /></a>
                <a href="../profile.php"><img src="../img/setting.png" alt="Settings Icon" /></a>
                <?php if ($isLoggedIn && $role === 'admin'): ?>
                <a href="dashboard.php"><img src="../img/inbox.png" alt="Settings Icon" /></a>
                <?php endif; ?>
            </div>
            <div class="login">
                <?php if ($isLoggedIn): ?>
                <p><?php echo $_SESSION['username']; ?>!</p>
                <a href="../auth/logout.php">Logout</a>
                <?php else: ?>
                <a href="../auth/login.php">Login</a>
                <a href="../auth/register.php">SignUp</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="../index.php"><button class="edit-buttonp">Kembali</button></a>
            <h1>PROFILE</h1>
            <a href="../index.php"><button class="edit-buttonp">Kembali</button></a>
        </div>

        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-card">
                <p><strong>Nama:</strong> <?= htmlspecialchars($user['username'] ?? 'Tidak Tersedia') ?></p>
                <p><strong>Nama Toko:</strong> <?= htmlspecialchars($user['nama_toko'] ?? 'Tidak Tersedia') ?></p>
                <p><strong>Rating Toko:</strong>
                    <?php 
                        $ratingToko = $user['rating_toko'] ?? 0; 
                        echo '★' . number_format($ratingToko, 2) . ' (' . $ratingToko . ')';
                    ?>
                </p>
                <br>
                <!-- Tombol Edit -->
                <button id="editBtn" class="edit-button">Edit Profil</button>
            </div>

            <div class="contact-card">
                <p><strong>Kontak:</strong></p>
                <p><?= htmlspecialchars($user['kontak'] ?? 'Tidak Tersedia') ?> (WhatsApp)</p>
                <p><?= htmlspecialchars($user['email'] ?? 'Tidak Tersedia') ?> (Email)</p>
            </div>
        </div>

        <!-- Product Section -->
        <div class="product-section">
            <h2>PRODUK</h2>

            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-item">
                <div class="product-info">
                    <img src="../img/<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"
                        class="product-img">
                    <p>Nama Produk: <?= htmlspecialchars($row['name']) ?></p>

                    <p>Rating:
                        <?php 
                    // Pastikan rating memiliki nilai yang valid
                    $rating = isset($row['rating']) && is_numeric($row['rating']) ? $row['rating'] : 0;
                    // Menggunakan floor untuk rating
                     echo str_repeat("★", floor($rating)) . str_repeat("☆", 5 - floor($rating));
                     ?>
                    </p>

                    <p>Terjual: <?= $row['sales_count'] ?></p>

                    <!-- Menambahkan informasi harga produk -->
                    <p>Harga: <?= 'Rp. ' . number_format($row['price'], 0, ',', '.') ?></p>
                </div>
                <div class="product-actions">
                    <a href="modules/update_product.php?id=<?= $row['id'] ?>"><button
                            class="edit-buttonp">EDIT</button></a>
                    <a href="modules/delete_product.php?id=<?= $row['id'] ?>"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                        <button class="delete-buttonp">HAPUS</button>
                    </a>
                </div>
            </div>

            <?php endwhile; ?>

            <!-- Add Product Button -->
            <div class="add-product">
                <a href="modules/add_product.php"><button class="add-button">+</button></a>
            </div>
        </div>
    </div>

    <!-- Modal untuk Edit Profil -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Edit Profil</h2>
            <form action="dashboard.php" method="POST">
                <label for="username">Nama:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                    required>

                <label for="nama_toko">Nama Toko:</label>
                <input type="text" id="nama_toko" name="nama_toko" value="<?= htmlspecialchars($user['nama_toko']) ?>"
                    required>

                <label for="kontak">Kontak:</label>
                <input type="text" id="kontak" name="kontak" value="<?= htmlspecialchars($user['kontak']) ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <button type="submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</body>

<script>
// Mendapatkan elemen-elemen modal dan tombol
var modal = document.getElementById("editModal");
var editBtn = document.getElementById("editBtn");
var closeModal = document.getElementById("closeModal");

// Ketika tombol Edit ditekan, tampilkan modal
editBtn.onclick = function() {
    modal.style.display = "block";
}

// Ketika tombol close (X) ditekan, sembunyikan modal
closeModal.onclick = function() {
    modal.style.display = "none";
}

// Ketika pengguna mengklik area di luar modal, tutup modal
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</html>