<?php
require 'connection.php';

// Start session and check login
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Menangani form update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Validasi input
    if (empty($username) || empty($email)) {
        $error = "Username dan email tidak boleh kosong!";
    } else {
        // Update data pengguna
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $userId);

        if ($stmt->execute()) {
            $success = "Profil berhasil diperbarui!";
        } else {
            $error = "Terjadi kesalahan saat memperbarui profil: " . htmlspecialchars($stmt->error);
        }
    }
}

// Ambil data pengguna untuk ditampilkan di form
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ambil data produk (contoh, sesuaikan dengan struktur database Anda)
$sqlProducts = "SELECT * FROM produk WHERE user_id = ?";
$stmtProducts = $conn->prepare($sqlProducts);
$stmtProducts->bind_param("i", $userId);
$stmtProducts->execute();
$products = $stmtProducts->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil</title>
    <link rel="stylesheet" href="css/styleprofile1.css" />
</head>

<body>
    <div class="header">
        <button class="back-button" onclick="window.location.href='index.php'">Kembali</button>
        <h1>Pengaturan Profil</h1>
        <button class="logout-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>
    <div class="container">

        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-card">
                <!-- Tampilkan pesan kesalahan atau sukses -->
                <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
                <?php elseif (isset($success)): ?>
                <div style="color: green; margin-bottom: 15px;"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Form untuk mengedit profil -->
                <form method="POST">
                    <p>
                        <strong>Username:</strong>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                            style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
                    </p>
                    <p>
                        <strong>Email:</strong>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                            style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
                    </p>
                    <button type="submit" style="
                        background-color: #4caf50;
                        color: white;
                        padding: 10px 15px;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        margin-top: 10px;
                    ">Simpan Perubahan</button>
                </form>
            </div>
            <div class="contact-card">

                <!-- Tambahan navigasi -->
                <div style="margin-top: 20px;">
                    <a href="riwayat.php" style="
                        display: block; 
                        background-color: #2196F3;
                        color: white;
                        padding: 10px;
                        text-align: center;
                        text-decoration: none;
                        border-radius: 5px;
                        margin-bottom: 10px;
                    ">Riwayat Transaksi</a>

                </div>
            </div>
        </div>


</body>

</html>