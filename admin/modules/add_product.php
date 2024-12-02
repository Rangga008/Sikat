<?php
require_once '../../connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Ambil data kategori untuk form
$categories_query = "SELECT id, name FROM categories";
$categories_result = $conn->query($categories_query);

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = str_replace(['Rp. ', '.', ','], '', $_POST['price']);
    $rating = $_POST['rating'];
    $category_id = $_POST['category_id'];
    $photo = $_FILES['photo']['name'];

    // Validasi kategori
    if (empty($category_id)) {
        $error_message = "Kategori harus dipilih.";
    }

    // Validasi file upload
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        $error_message = "Format file tidak didukung.";
    }

    if (empty($error_message)) {
        // Upload file
        move_uploaded_file($_FILES['photo']['tmp_name'], '../../img/' . basename($photo));

        // Query simpan produk
        $query = "INSERT INTO produk (user_id, name, description, price, rating, photo, category_id)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdssi", $_SESSION['user_id'], $name, $description, $price, $rating, $photo, $category_id);
        $stmt->execute();

        header('Location: ../dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="../../css/styletambahp.css">
</head>

<body>
    <div class="container">
        <button class="back-btn" onclick="window.history.back();">Kembali</button>
        <h1>TAMBAH PRODUK</h1>
        <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Masukkan Gambar Produk</label>
                <input type="file" name="photo" required>
            </div>
            <div class="input-group">
                <label>Masukkan Nama Produk</label>
                <input type="text" name="name" placeholder="Nama Produk" required>
            </div>
            <div class="input-group">
                <label>Tambahkan Deskripsi Produk</label>
                <textarea name="description" placeholder="Deskripsi Produk" required></textarea>
            </div>
            <div class="input-group">
                <label>Masukkan Harga Produk</label>
                <input type="text" id="price" name="price" placeholder="Harga Produk" required>
            </div>
            <div class="input-group">
                <label>Pilih Kategori</label>
                <select name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group">
                <button type="submit" class="add-btn">Tambah Produk</button>
            </div>
        </form>
    </div>
</body>
<script>
var priceInput = document.getElementById('price');
priceInput.addEventListener('input', function(e) {
    var value = e.target.value.replace(/[^,\d]/g, '').toString();
    e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".").replace(/^/, "Rp. ");
});
</script>

</html>