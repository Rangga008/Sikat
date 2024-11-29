<?php
require '../connection.php';
session_start();

// Jika sudah login, arahkan ke halaman utama atau dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    // Validasi password dan konfirmasi password
    if ($password !== $password_confirmation) {
        echo "Password dan konfirmasi password tidak cocok!";
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Tentukan role sebagai 'user' secara default
    $role = 'user'; // Default role adalah 'user'

    // Query untuk memasukkan data user ke database
    $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
    
    if ($conn->query($sql)) {
        header("Location: login.php"); // Arahkan ke halaman login setelah registrasi berhasil
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register UI</title>
    <link rel="stylesheet" href="../css/styleRegis.css" />
</head>

<body>
    <div class="register-container">
        <div class="register-box">
            <!-- Tombol X untuk menutup atau kembali ke halaman index -->
            <a href="../index.php" class="close-button">&times;</a>
            <h2>REGISTER</h2>
            <!-- Form untuk registrasi dengan action ke register.php dan method POST -->
            <form method="POST" action="register.php">
                <input type="text" name="username" placeholder="USERNAME #char a-z or number 0-9" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password #please use a strong password" required />
                <input type="password" name="password_confirmation" placeholder="Ulangi Password" required />

                <!-- Role disembunyikan, hanya admin yang bisa mengubah -->
                <input type="hidden" name="role" value="user" />

                <button type="submit" class="register-button">DAFTAR</button>
            </form>
            <p>Have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>

</html>