<?php
session_start();
require '../connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan product_id ada dan valid
    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
    } else {
        die("Product ID is required.");
    }

    // Cek apakah product_id ada di tabel produk
    $query_check = "SELECT id FROM produk WHERE id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $product_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        die("Product ID does not exist.");
    }

    // Pastikan product_rating ada dan valid
    if (isset($_POST['product_rating']) && is_numeric($_POST['product_rating'])) {
        $product_rating = $_POST['product_rating'];
    } else {
        die("Invalid product rating.");
    }

    // Dapatkan transaction_id jika relevan
    // Check if the transaction_id is set, if not, pass NULL
    $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : null;

    // Check if transaction_id is not required and allow null if it's not available
    if ($transaction_id === null) {
        // If transaction_id is optional, set the default value or allow it to be NULL
        // Ensure the database allows null values for transaction_id if necessary
    }

    // Insert rating produk ke dalam tabel product_ratings
    $query = "INSERT INTO product_ratings (product_id, user_id, rating, transaction_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Make sure we bind NULL for the transaction_id if it's not provided
    if ($transaction_id === null) {
        $stmt->bind_param("iiid", $product_id, $user_id, $product_rating, $transaction_id);  // bind NULL for transaction_id
    } else {
        $stmt->bind_param("iiid", $product_id, $user_id, $product_rating, $transaction_id);  // bind the provided transaction_id
    }

    if (!$stmt->execute()) {
        die("Error: " . $stmt->error);
    }

    // Hitung rata-rata rating produk
    $query_avg = "SELECT AVG(rating) AS avg_rating FROM product_ratings WHERE product_id = ?";
    $stmt_avg = $conn->prepare($query_avg);
    $stmt_avg->bind_param("i", $product_id);
    $stmt_avg->execute();
    $result_avg = $stmt_avg->get_result();
    $row_avg = $result_avg->fetch_assoc();
    $average_rating = $row_avg['avg_rating'];

    // Update rating produk di tabel produk
    $query_update = "UPDATE produk SET rating = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("di", $average_rating, $product_id);
    $stmt_update->execute();

    header("Location: ../messages.php"); // Redirect kembali ke halaman riwayat
    exit;
}
?>