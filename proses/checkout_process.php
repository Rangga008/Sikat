<?php
session_start();
require '../connection.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'];
$voucher_code = trim($_POST['voucher_code']);

// Hitung total harga
$query = "SELECT produk.id, produk.price, cart.quantity 
          FROM cart 
          INNER JOIN produk ON cart.product_id = produk.id 
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
$cart_items = []; // Initialize an array to hold cart items
while ($row = $result->fetch_assoc()) {
    $total_price += $row['price'] * $row['quantity'];
    $cart_items[] = [
        'product_id' => $row['id'],
        'quantity' => $row['quantity']
    ];
}

// Logika voucher (contoh sederhana)
$discount = 0;
if (!empty($voucher_code)) {
    $voucher_query = "SELECT discount FROM vouchers WHERE code = ?";
    $voucher_stmt = $conn->prepare($voucher_query);
    $voucher_stmt->bind_param("s", $voucher_code);
    $voucher_stmt->execute();
    $voucher_result = $voucher_stmt->get_result();

    if ($voucher_result->num_rows > 0) {
        $voucher = $voucher_result->fetch_assoc();
        $discount = $voucher['discount'];
        $total_price -= $discount;

        // Ensure total price does not go negative
        if ($total_price < 0) {
            $total_price = 0;
        }
    }
}

// Insert transaction
$insert_query = "INSERT INTO transactions (user_id, total_price, payment_method, created_at) 
                 VALUES (?, ?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("ids", $user_id, $total_price, $payment_method);

if ($insert_stmt->execute()) {
    // Get the transaction ID
    $transaction_id = $insert_stmt->insert_id;

    // Simpan detail barang ke `transaction_items` dan update sales_count
    foreach ($cart_items as $item) {
        // Insert ke transaction_items
        $item_query = "INSERT INTO transaction_items (transaction_id, product_id, quantity) 
                       VALUES (?, ?, ?)";
        $item_stmt = $conn->prepare($item_query);
        $item_stmt->bind_param("iii", $transaction_id, $item['product_id'], $item['quantity']);
        $item_stmt->execute();

        // Update sales_count di tabel produk
        $update_sales_query = "UPDATE produk SET sales_count = sales_count + ? WHERE id = ?";
        $update_sales_stmt = $conn->prepare($update_sales_query);
        $update_sales_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_sales_stmt->execute();
    }

    // Empty the cart after successful transaction
    $delete_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $delete_cart_stmt = $conn->prepare($delete_cart_query);
    $delete_cart_stmt->bind_param("i", $user_id);
    $delete_cart_stmt->execute();

    // Arahkan ke halaman Transaksi Berhasil
    header('Location: ../success.php');
    exit;
} else {
    die("Gagal menyimpan transaksi: " . $insert_stmt->error);
}
?>