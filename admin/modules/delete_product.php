<?php
// modules/delete_product.php
require_once '../../connection.php'; // Koneksi ke database

// Mengecek apakah ada ID produk yang dikirim melalui GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk menghapus produk
    $query = "DELETE FROM produk WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Redirect ke dashboard setelah berhasil
    header('Location: ../dashboard.php');
    exit;
}