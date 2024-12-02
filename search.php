<?php 
require 'connection.php'; // Koneksi ke database
$query = $_GET['query'] ?? '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$searchQuery = "%$query%";

if ($query || $category) {
    $sql = "SELECT 
                produk.id, 
                produk.name, 
                produk.photo, 
                produk.price, 
                produk.sales_count, 
                produk.rating, 
                users.nama_toko, 
                users.rating_toko 
            FROM produk 
            INNER JOIN users ON produk.user_id = users.id 
            WHERE produk.name LIKE ?";

    // Tambahkan filter kategori jika dipilih
    if ($category) {
        $sql .= " AND produk.category_id = ?";
    }

    $sql .= " ORDER BY produk.sales_count DESC, produk.rating DESC";

    $stmt = $conn->prepare($sql);

    if ($category) {
        $stmt->bind_param("si", $searchQuery, $category);
    } else {
        $stmt->bind_param("s", $searchQuery);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else { 
    // Jika tidak ada pencarian, tampilkan semua produk
    $sql = "SELECT 
                produk.id, 
                produk.name, 
                produk.photo, 
                produk.price, 
                produk.sales_count, 
                produk.rating, 
                users.nama_toko, 
                users.rating_toko 
            FROM produk 
            INNER JOIN users ON produk.user_id = users.id 
            ORDER BY produk.sales_count DESC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hasil Pencarian</title>
    <link rel="stylesheet" href="css/stylesearch.css" />
    <link rel="stylesheet" href="css/styleBeranda.css" />
</head>

<body>
    <?php 
    require 'layout/navbar.php';
    ?>
    <main>
        <div class="content">
            <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="content-box">';
                echo '<a href="deskripsi.php?id=' . $row['id'] . '">';
                echo '<img src="img/' . htmlspecialchars($row['photo']) . '" alt="' . htmlspecialchars($row['name']) . '" class="product-img">';
                echo '<div class="content-text">';
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<p>Rp ' . number_format($row['price'], 0, ',', '.') . '</p>';
                echo '<p>Nama Toko: ' . htmlspecialchars($row['nama_toko']) . '</p>';
                echo '<p>Rating Toko: ' . (isset($row['rating_toko']) ? number_format($row['rating_toko'], 1) : 'N/A') . ' ⭐</p>';
                echo '<p>Rating: ' . $row['rating'] . ' ⭐</p>';
                echo '<p>Terjual: ' . $row['sales_count'] . '</p>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<p>Tidak ada produk yang ditemukan.</p>';
        }
        ?>
        </div>
    </main>
    <footer>
        <div class="footer-container">
            <p>08**-*******</p>
            <a href="#">WhatsApp</a>
            <a href="#">@duka_makan.com</a>
        </div>
    </footer>
</body>

</html>