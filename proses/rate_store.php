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
    $transaction_id = $_POST['transaction_id'];  // Ensure you get the transaction_id from the form or session
    $store_id = $_POST['store_id'];
    $store_rating = $_POST['store_rating'];

    // Insert rating toko ke dalam tabel store_ratings
    $query = "INSERT INTO store_ratings (store_id, user_id, rating, transaction_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iids", $store_id, $user_id, $store_rating, $transaction_id);
    if (!$stmt->execute()) {
        die("Error: " . $stmt->error);
    }

    // Hitung rata-rata rating toko
    $query_avg = "SELECT AVG(rating) AS avg_rating FROM store_ratings WHERE store_id = ?";
    $stmt_avg = $conn->prepare($query_avg);
    $stmt_avg->bind_param("i", $store_id);
    $stmt_avg->execute();
    $result_avg = $stmt_avg->get_result();
    $row_avg = $result_avg->fetch_assoc();
    $average_rating = $row_avg['avg_rating'];

    // Update rating_toko di tabel users
    $query_update = "UPDATE users SET rating_toko = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("di", $average_rating, $store_id);
    $stmt_update->execute();

    header("Location: ../messages.php"); // Redirect kembali ke halaman riwayat
    exit;
}
?>