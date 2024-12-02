<link rel="stylesheet" href="css/styleBeranda.css" />
<link rel="stylesheet" href="css/stylecontent.css" />
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
                  INNER JOIN users ON produk.user_id = users.id
                  ORDER BY produk.sales_count DESC 
                  LIMIT 3"; // Menampilkan 3 produk terlaris
$result = $conn->query($query);

// Menampilkan produk
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="partner-item">'; // Gunakan class .partner-item untuk setiap produk
        echo '<a href="deskripsi.php?id=' . $row['id'] . '">'; // Link ke deskripsi.php
        echo '<img src="img/' . htmlspecialchars($row['photo']) . '" alt="' . htmlspecialchars($row['name']) . '" class="product-img">'; // Gambar produk
        echo '<div class="content-text">'; // Div untuk teks
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>'; // Nama produk
        echo '<p>Rp ' . number_format($row['price'], 0, ',', '.') . '</p>'; // Harga produk
        echo '<p>Rating Produk: ' . (isset($row['rating']) ? number_format($row['rating'], 1) : 'N/A') . ' ⭐</p>'; // Rating produk
        echo '<p>Terjual: ' . $row['sales_count'] . '</p>'; // Jumlah terjual
        
        // Periksa apakah nama toko ada, jika tidak tampilkan "Tidak Diketahui"
        $nama_toko = isset($row['nama_toko']) ? htmlspecialchars($row['nama_toko']) : 'Tidak Diketahui';
        echo '<p>Nama Toko: ' . $nama_toko . '</p>'; // Nama toko
        
        // Periksa apakah rating toko ada
        $rating_toko = isset($row['rating_toko']) ? number_format($row['rating_toko'], 1) : 'N/A';
        echo '<p>Rating Toko: ' . $rating_toko . ' ⭐</p>'; // Rating toko
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