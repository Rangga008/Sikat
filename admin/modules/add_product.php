<?php
// modules/add_product.php
require_once '../../connection.php'; // Koneksi ke database

// Mengecek apakah pengguna sudah login
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data produk dari form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price']; // harga akan diterima sebagai string tanpa format
    $rating = $_POST['rating'];
    $photo = $_FILES['photo']['name'];

    // Pastikan harga adalah angka murni
    $price = str_replace(['Rp. ', '.', ','], '', $price); // Menghapus simbol dan pemisah ribuan
    $price = floatval($price); // Pastikan tipe data adalah float atau decimal

    // Upload foto produk ke direktori
    move_uploaded_file($_FILES['photo']['tmp_name'], '../../img/' . $photo);

    // Query untuk menambah produk ke database
    $query = "INSERT INTO produk (user_id, name, description, price, rating, photo) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issdss", $_SESSION['user_id'], $name, $description, $price, $rating, $photo);
    $stmt->execute();
    
    // Redirect ke dashboard setelah berhasil menambah produk
    header('Location: ../dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="../../css/styletambahp.css" />
</head>

<body>
    <div class="container">
        <button class="back-btn" onclick="window.history.back();">Kembali</button>
        <h1>TAMBAH PRODUK</h1>

        <!-- Form untuk menambah produk -->
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Masukkan Gambar Produk</label>
                <input type="file" name="photo" required />
            </div>
            <div class="input-group">
                <label>Masukkan Nama Produk</label>
                <input type="text" name="name" placeholder="Nama Produk" required />
            </div>
            <div class="input-group">
                <label>Tambahkan Deskripsi Produk</label>
                <textarea name="description" placeholder="Deskripsi Produk" required></textarea>
            </div>
            <div class="input-group">
                <label>Masukkan Harga Produk</label>
                <input type="text" id="price" name="price" placeholder="Harga Produk" required />
            </div>
            <div class="input-group">
                <button type="submit" class="add-btn">Tambah Produk</button>
            </div>
        </form>
    </div>

    <script>
    // Fungsi untuk menambahkan format Rupiah pada input harga
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    // Event listener untuk menangani input harga
    var priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function(e) {
        e.target.value = formatRupiah(e.target.value, 'Rp. ');
    });

    // Menangani form submit untuk menghapus format Rupiah dan mengirimkan angka murni
    document.querySelector('form').addEventListener('submit', function(event) {
        var priceInput = document.getElementById('price');
        // Menghapus format Rupiah dan menyimpan hanya angka
        priceInput.value = priceInput.value.replace(/[^0-9]/g, '');
    });
    </script>
</body>

</html>