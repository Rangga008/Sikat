<?php
session_start();
require '../connection.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Pastikan role yang dipilih valid
    if (!in_array($new_role, ['admin', 'user'])) {
        echo "Role yang dipilih tidak valid!";
        exit;
    }

    // Update role pengguna
    $sql = "UPDATE users SET role='$new_role' WHERE id='$user_id'";

    if ($conn->query($sql)) {
        echo "Role berhasil diubah!";
        header("Location: manage-users.php"); // Redirect setelah berhasil
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Menampilkan daftar pengguna
$sql = "SELECT id, username, email, role FROM users";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
</head>

<body>
    <h2>Manage Users</h2>

    <!-- Tabel untuk menampilkan daftar pengguna -->
    <table border="1">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['role']; ?></td>
                <td>
                    <!-- Form untuk mengubah role -->
                    <form method="POST" action="manage-users.php">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>" />
                        <select name="role" required>
                            <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
                            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                        <button type="submit">Update Role</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>