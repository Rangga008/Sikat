<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sikat"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
if (!defined('BASE_URL')) {
    define('BASE_URL', __DIR__ . '/'); // Menentukan jalur penuh ke root aplikasi
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/pw2024_Xrpl2/sikat/');
}
?>