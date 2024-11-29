<link rel="stylesheet" href="css/styleBeranda.css" />
<main>
    <div class="content">
        <!-- PHP untuk menampilkan semua produk -->
        <?php
        require 'connection.php'; // Koneksi ke database

        // Query untuk mendapatkan semua produk beserta informasi toko
        $query = "SELECT 
                    produk.id, 
                    produk.name, 
                    produk.photo, 
                    produk.price, 
                    produk.sales_count, 
                    produk.rating, 
                    users.nama_toko, 
                    users.rating_toko 
                  FROM produk 
                  INNER JOIN users ON produk.user_id = users.id";
        $result = $conn->query($query);

        // Menampilkan produk
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="content-box">';
                echo '<a href="deskripsi.php?id=' . $row['id'] . '">'; // Link ke deskripsi.php
                echo '<img src="img/' . htmlspecialchars($row['photo']) . '" alt="' . htmlspecialchars($row['name']) . '" class="product-img">';
                echo '<div class="content-text">';
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<p>Rp ' . number_format($row['price'], 0, ',', '.') . '</p>';
                echo '<p>Rating Produk: ' . (isset($row['rating']) ? number_format($row['rating'], 1) : 'N/A') . ' ⭐</p>';
                echo '<p>Terjual: ' . $row['sales_count'] . '</p>';
                echo '<p>Nama Toko: ' . htmlspecialchars($row['nama_toko']) . '</p>';
                echo '<p>Rating Toko: ' . (isset($row['rating_toko']) ? number_format($row['rating_toko'], 1) : 'N/A') . ' ⭐</p>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No products found.</p>';
        }
        ?>
    </div>
</main>