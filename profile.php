<?php
require 'connection.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Menangani form update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];

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
            $error = "Terjadi kesalahan saat memperbarui profil.";
        }
    }
}

// Ambil data pengguna untuk ditampilkan di form
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil</title>
</head>

<body>
    <h1>Pengaturan Profil</h1>

    <!-- Tampilkan pesan kesalahan atau sukses -->
    <?php if (isset($error)): ?>
    <div style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php elseif (isset($success)): ?>
    <div style="color: green;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Form untuk mengedit profil -->
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
        <button type="submit">Simpan</button>
    </form>

    <a href="index.php">Kembali ke Beranda</a>
</body>

</html>