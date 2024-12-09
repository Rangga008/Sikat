<?php
session_start();
require '../connection.php';

// Check if the user is logged in and has the role of owner
if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
} else {
    header('Location: auth/login.php');
    exit;
}

// Function to get all user data
function getAllUsers($conn) {
    $query = "SELECT id, username, email, role FROM users";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Create a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    $stmt->execute();
    header("Location: manager.php");
    exit;
}

// Delete a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Check if the user is trying to delete their own account
    if ($user_id == $_SESSION['user_id']) {
        // Optionally, set an error message
        $_SESSION['error'] = "You cannot delete your own account.";
    } else {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        // Optionally, set a success message
        $_SESSION['message'] = "User  deleted successfully!";
    }

    header("Location: manager.php");
    exit;
}
// Reset password directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    // Validate password strength
    if (strlen($new_password) < 1) {
        $error_message = "Password harus memiliki minimal 8 karakter.";
    } else {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $new_password_hashed, $user_id);
        $stmt->execute();
        header("Location: manager.php");
        exit;
    }
}

// Update user role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    $query = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
    header("Location: manager.php");
    exit;
}

$users = getAllUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/stylemanager.css" />
    <link rel="stylesheet" href="../css/stylenavbar.css" />
    <title>Manage Users</title>
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="../index.php"><img src="../img/logo.png" alt="Logo"></a>
            </div>
            <div class="header-icons">
                <h1 style="padding-bottom: 10px;">Manage Users</h1>
                <a href="../cart.php"><img src="../img/cart.png" alt="Cart Icon" /></a>
                <a href="../messages.php"><img src="../img/chat.png" alt="Chat Icon" /></a>
                <a href="../profile.php"><img src="../img/setting.png" alt="Settings Icon" /></a>
                <?php if ($isLoggedIn): ?>
                <?php if ($role === 'admin' || $role === 'owner'): ?>
                <a href="dashboard.php"><img src="../img/inbox.png" alt="Inbox Icon" /></ a>
                    <?php endif; ?>
                    <?php if ($role === 'owner'): ?>
                    <a href="manager.php"><img src="../img/manager.png" alt="Manager Icon" /></a>
                    <?php endif; ?>
                    <?php endif; ?>
            </div>
            <div class="login">
                <?php if ($isLoggedIn): ?>
                <p><?php echo htmlspecialchars($username); ?>!</p>
                <a href="../auth/logout.php">Logout</a>
                <?php else: ?>
                <a href="../auth/login.php">Login</a>
                <a href="../auth/register.php">SignUp</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <!-- Form Tambah User -->
        <h2>Create User</h2>
        <form method="POST">
            <input type="hidden" name="create_user" value="1">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <label>Role:</label>
            <select name="role" required>
                <option value="user">User </option>
                <option value="admin">Admin</option>
                <option value="owner">Owner</option>
            </select>
            <button type="submit">Create</button>
        </form>

        <!-- Tabel Data User -->
        <h2>All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <!-- Form Hapus User -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_user" value="1">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="delete">Delete</button>
                        </form>

                        <!-- Form Reset Password -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="reset_password" value="1">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <label>New Password:</label>
                            <input type="password" name="new_password" required>
                            <button type="submit" class="reset">Reset Password</button>
                        </form>

                        <!-- Form Update Role -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="update_role" value="1">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role" required>
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User </option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="owner" <?= $user['role'] === 'owner' ? 'selected' : '' ?>>Owner</option>
                            </select>
                            <button type="submit" class="update-role">Update Role</button>
                        </form>
                    </td>
                </tr>
                <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
                <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>