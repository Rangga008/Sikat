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

// Mengecek apakah ada ID produk yang dikirim melalui GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data produk dari database
    $query = "SELECT * FROM produk WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Produk tidak ditemukan.";
        exit; // Hentikan eksekusi jika produk tidak ditemukan
    }
} else {
    echo "ID produk tidak ditemukan.";
    exit;
}

// Proses form submit untuk memperbarui data produk
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = str_replace(['Rp. ', '.', ','], '', $_POST['price']);
    $category_id = $_POST['category_id'];
    $photo = $_FILES['photo']['name'];
    $old_photo = $_POST['old_photo'];

    // Validasi kategori
    if (empty($category_id)) {
        $error_message = "Kategori harus dipilih.";
    }

    // Validasi file upload (jika ada file baru)
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!empty($photo)) {
        $file_extension = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            $error_message = "Format file tidak didukung.";
        }
    }

    if (empty($error_message)) {
        // Menangani foto produk
        if (!empty($photo)) {
            $photo_path = '../../img/' . basename($photo);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);

            // Hapus foto lama
            if (!empty($old_photo) && file_exists('../../img/' . $old_photo)) {
                unlink('../../img/' . $old_photo);
            }
        } else {
            $photo = $old_photo; // Gunakan foto lama jika tidak ada foto baru
        }

        // Query update produk
        $update_query = "UPDATE produk SET name = ?, description = ?, price = ?, photo = ?, category_id = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("ssdsii", $name, $description, $price, $photo, $category_id, $id);

        if ($stmt_update->execute()) {
            header("Location: ../dashboard.php");
            exit();
        } else {
            $error_message = "Gagal memperbarui data produk.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="../../css/styletambahp.css">
</head>

<body>
    <div class="container">
        <button class="back-btn" onclick="window.location.href='../dashboard.php';">Kembali</button>
        <h1>Edit Produk</h1>

        <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form action="update_product.php?id=<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Foto Produk</label>
                <input type="file" name="photo">
                <input type="hidden" name="old_photo" value="<?= htmlspecialchars($product['photo']) ?>">
            </div>
            <div class="input-group">
                <label>Nama Produk</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="input-group">
                <label>Deskripsi Produk</label>
                <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Harga Produk</label>
                <input type="text" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>
            <div class="input-group">
                <label>Kategori</label>
                <select name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>"
                        <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit">Simpan Perubahan</button>
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