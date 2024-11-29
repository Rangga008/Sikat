<?php
session_start();
require '../connection.php';

if (!isset($_SESSION['user_id'])) die("Unauthorized.");
$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);

$query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

header("Location: ../cart.php");
exit();
?>