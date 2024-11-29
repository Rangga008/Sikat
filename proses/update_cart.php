<?php
session_start();
require '../connection.php';

if (isset($_POST['cart_id']) && isset($_POST['action'])) {
    $cart_id = $_POST['cart_id'];
    $action = $_POST['action'];

    // Cek apakah cart_id valid
    $query = "SELECT cart.quantity, produk.stock FROM cart
              JOIN produk ON cart.product_id = produk.id 
              WHERE cart.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $quantity = $row['quantity'];
        $stock = $row['stock']; // Jumlah stok produk

        // Tentukan perubahan kuantitas berdasarkan aksi
        if ($action === 'increase' && $quantity < $stock) {
            $quantity++;
        } elseif ($action === 'decrease' && $quantity > 1) {
            $quantity--;
        }

        // Update kuantitas di keranjang
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $quantity, $cart_id);
        $update_stmt->execute();

        // Jika stok produk habis (kuantitas menjadi 0), hapus produk dari keranjang
        if ($quantity === 0) {
            $delete_query = "DELETE FROM cart WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $cart_id);
            $delete_stmt->execute();
        }
    }
}
header("Location: ../cart.php"); // Kembali ke halaman keranjang
exit();
?>