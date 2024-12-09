<?php
require '../connection.php';
session_start();
$error_message = ""; // Variabel untuk menyimpan pesan kesalahan

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk mencari pengguna berdasarkan email
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Menyimpan informasi session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Menyimpan role pengguna (misalnya, 'user')
            header("Location: ../index.php"); // Arahkan ke index.php setelah login
            exit;
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login UI</title>
    <link rel="stylesheet" href="../css/styleLogin.css" />
    <script>
    // Fungsi untuk menampilkan pop-up jika ada pesan kesalahan
    function showError(message) {
        alert(message);
    }
    </script>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <a href="../index.php" class="close-button">&times;</a>
            <h2>LOGIN</h2>

            <div class="avatar">
                <img src="../img/logo2.jpg" alt="User  Avatar" />
            </div>
            <!-- Form login yang disesuaikan -->
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" class="login-button">LOGIN</button>
            </form>
            <p>Don't have an Account? <a href="register.php">SignUp</a></p>
        </div>
    </div>

    <?php if ($error_message): ?>
    <script>
    // Tampilkan pop-up jika ada pesan kesalahan
    showError("<?php echo $error_message; ?>");
    </script>
    <?php endif; ?>
</body>

</html>