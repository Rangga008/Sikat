<?php
require_once '../../connection.php'; // Koneksi ke database

// Mengecek apakah ada ID produk yang dikirim melalui GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data produk dari database
    $query = "SELECT * FROM produk WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Cek jika produk ditemukan
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Produk tidak ditemukan.";
        exit; // Menghentikan eksekusi jika produk tidak ditemukan
    }
}

// Proses form submit untuk memperbarui data produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $old_photo = $_POST['old_photo'];
    $photo = $_FILES['photo'];
    $category_id = $_POST['category_id']; // ID kategori yang dipilih

    // Periksa apakah category_id valid
    if (empty($category_id)) {
        echo "Kategori harus dipilih.";
        exit;
    }


    // Menangani foto produk
    if ($photo['error'] == UPLOAD_ERR_OK) {
        // Foto baru diunggah, simpan foto baru
        $photo_name = $photo['name'];
        $photo_tmp = $photo['tmp_name'];
        $photo_dest = '../../uploads/' . $photo_name;

        // Pindahkan foto ke direktori yang diinginkan
        move_uploaded_file($photo_tmp, $photo_dest);

        // Hapus foto lama jika ada
        if (!empty($old_photo) && file_exists('../../uploads/' . $old_photo)) {
            unlink('../../uploads/' . $old_photo);
        }
    } else {
        // Jika tidak ada foto baru, gunakan foto lama
        $photo_name = $old_photo;
    }

    // Update data produk di database
    $update_query = "UPDATE produk SET name = ?, description = ?, price = ?, photo = ? WHERE id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("ssisi", $name, $description, $price, $photo_name, $id);
    
    if ($stmt_update->execute()) {
        // Redirect ke halaman dashboard setelah berhasil update
        header("Location: ../dashboard.php");
        exit();
    } else {
        echo "Gagal memperbarui data produk.";
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

        <form action="update_product.php?id=<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="photo">Foto Produk:</label>
                <input type="file" id="photo" name="photo">
                <input type="hidden" name="old_photo" value="<?= htmlspecialchars($product['photo']) ?>">
            </div>
            <div class="input-group">
                <label for="name">Nama Produk:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="input-group">
                <label for="description">Deskripsi Produk:</label>
                <textarea id="description" name="description"
                    required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="input-group">
                <label>Masukkan Harga Produk</label>
                <input type="text" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>"
                    required />
            </div>
            <div class="input-group">
                <label>Pilih Kategori</label>
                <select name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>">
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
function handlePriceInput(event) {
    var priceInput = event.target;
    var formattedPrice = formatRupiah(priceInput.value, 'Rp. ');

    // Menghilangkan format saat menyimpan data
    priceInput.value = formattedPrice.replace(/[^0-9]/g, '');
}

document.addEventListener('DOMContentLoaded', function() {
    var priceInput = document.getElementById('price');
    priceInput.addEventListener('input', handlePriceInput);
});
</script>

</html>