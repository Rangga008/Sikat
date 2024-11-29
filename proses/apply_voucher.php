<?php
session_start();
require '../connection.php';

$voucher_code = $_POST['voucher_code'];

// Cek apakah voucher ada dan valid
$query = "SELECT * FROM vouchers WHERE code = ? AND valid_until >= CURDATE()";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $voucher_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $voucher = $result->fetch_assoc();
    $_SESSION['voucher'] = $voucher; // Simpan voucher di session
    header("Location: ../cart.php"); // Redirect kembali ke keranjang
} else {
    echo "Voucher tidak valid.";
}
?>