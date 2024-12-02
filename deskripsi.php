<?php
session_start();

require 'connection.php'; // Koneksi ke database

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil ID user dari sesi

// Ambil ID produk dari parameter URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mendapatkan data produk dan informasi toko berdasarkan ID produk
$query = "SELECT 
            produk.id, 
            produk.name, 
            produk.description, 
            produk.photo, 
            produk.price, 
            produk.sales_count, 
            produk.rating, 
            users.nama_toko, 
            users.rating_toko 
          FROM produk 
          INNER JOIN users ON produk.user_id = users.id 
          WHERE produk.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Periksa apakah produk ditemukan
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Produk tidak ditemukan.");
}

// Menangani request untuk menambahkan produk ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']); // Ambil ID produk dari form
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1; // Ambil jumlah produk

    // Validasi apakah product_id ada di tabel products
    $product_check_query = "SELECT id FROM produk WHERE id = ?";
    $product_check_stmt = $conn->prepare($product_check_query);
    $product_check_stmt->bind_param("i", $product_id);
    $product_check_stmt->execute();
    $product_check_result = $product_check_stmt->get_result();

    if ($product_check_result->num_rows === 0) {
        die("Produk tidak valid atau tidak ditemukan.");
    }

    // Periksa apakah produk sudah ada di keranjang
    $check_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update jumlah produk jika sudah ada
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;

        $update_query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        $update_stmt->execute();
    } else {
        // Tambahkan produk baru ke keranjang
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
    }

    // Redirect ke halaman keranjang
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deskripsi Produk</title>
    <link rel="stylesheet" href="css/styleDeskripsi.css">
    <link rel="stylesheet" href="css/styleopsi.css">
    <link rel="stylesheet" href="css/stylevoucher.css">
</head>

<body>
    <header>
        <div class="header-container">
            <button class="back-button" onclick="history.back()">← Kembali</button>
            <div class="settings-icon">
                <img src="img/setting.png" alt="Settings">
            </div>
        </div>
    </header>

    <main>
        <div class="product-container">
            <!-- Bagian Gambar dan Rating -->
            <div class="product-image">
                <img src="img/<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <!-- Bagian Rating -->
                <div class="rating-box">
                    <p>Rating</p>
                    <p><?= isset($row['rating']) ? number_format($row['rating'], 1, '.', '') : '0' ?>/5 <span
                            class="star-icon">⭐</span></p>
                </div>
            </div>

            <!-- Bagian Detail Produk -->
            <div class="product-details">
                <h1>&nbsp;<?= htmlspecialchars($row['name']) ?></h1>
                <p><strong>&nbsp;Harga:</strong> Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                <p><strong>&nbsp;Terjual:</strong> <?= $row['sales_count'] ?> unit</p>
                <p><strong>&nbsp;Nama Toko:</strong> <?= htmlspecialchars($row['nama_toko']) ?></p>
                <p><?= isset($row['rating']) ? number_format($row['rating_toko'], 1, '.', '') : '0' ?>/5 <span
                        class="star-icon">⭐</span></p>
                <div class="description">
                    <p><?= htmlspecialchars($row['description']) ?></p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <form method="POST">
                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn">Pesan</button>
            </form>
            <form method="POST">
                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn">Keranjang</button>
            </form>
        </div>

        <div class="popup" id="popup">
            <div class="popup-content">
                <button class="close-btn" id="closeButton">&times;</button>
                <h2>Opsi Pembayaran</h2>
                <div class="payment-options">
                    <button class="payment-btn">
                        <img src="img/kartu.png" alt="Kartu"> Kartu Kredit
                    </button>
                    <button class="payment-btn">
                        <img src="img/dompet.png" alt="Dompet"> E-Wallet
                    </button>
                    <button class="payment-btn">
                        <img src="img/tunai.png" alt="Tunai"> Tunai
                    </button>
                </div>
                <br>
                <h2>Pilih Tambahan</h2>
                <div class="add-ons">
                    <label><input type="checkbox"> Sambal Extra</label>
                    <label><input type="checkbox"> Minuman</label>
                    <label><input type="checkbox"> Nasi Tambahan</label>
                </div>
                <button class="btn confirm-btn">Konfirmasi</button>
                <button class="btn" id="openInnerPopup">Gunakan Voucher</button>
            </div>
        </div>

        <!-- Pop-up untuk voucher -->
        <div class="popup inner-popup" id="innerPopup">
            <div class="popup-content">
                <button class="close-btn" id="closeInnerPopup">&times;</button>
                <h3>Pilih Voucher</h3>
                <input type="text" id="voucherSearch" placeholder="Cari voucher...">
                <ul id="voucherList">
                    <li>Kode Voucher: ABCD12345678</li>
                    <li>Kode Voucher: EFGH87654321</li>
                    <li>Kode Voucher: IJKL11223344</li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
    const pesanButton = document.getElementById("pesanButton");
    const keranjangButton = document.getElementById("keranjangButton");
    const popup = document.getElementById("popup");
    const closeButton = document.getElementById("closeButton");
    const openInnerPopup = document.getElementById("openInnerPopup");
    const innerPopup = document.getElementById("innerPopup");
    const closeInnerPopup = document.getElementById("closeInnerPopup");

    // Tampilkan popup utama
    pesanButton.addEventListener("click", () => {
        popup.style.display = "flex";
    });

    keranjangButton.addEventListener("click", () => {
        popup.style.display = "flex";
    });

    closeButton.addEventListener("click", () => {
        popup.style.display = "none";
    });

    // Tampilkan popup voucher
    openInnerPopup.addEventListener("click", () => {
        innerPopup.style.display = "flex";
    });

    closeInnerPopup.addEventListener("click", () => {
        innerPopup.style.display = "none";
    });

    // Tutup popup saat klik di luar konten
    window.addEventListener("click", (event) => {
        if (event.target === popup || event.target === innerPopup) {
            popup.style.display = "none";
            innerPopup.style.display = "none";
        }
    });


    // kode untuk star rating
    document.addEventListener("DOMContentLoaded", function() {
        // Ambil semua elemen dengan class 'star-rating'
        const starRatings = document.querySelectorAll(".star-rating");

        starRatings.forEach(starRating => {
            // Ambil rating dari data-attribute
            const rating = parseFloat(starRating.getAttribute("data-rating")) || 0;

            // Hitung persentase untuk width
            const percentage = (rating / 5) * 100;

            // Cari elemen .stars-inner di dalam kontainer
            const starsInner = starRating.querySelector(".stars-inner");

            if (starsInner) {
                // Set lebar elemen sesuai persentase
                starsInner.style.width = `${percentage}%`;
            }
        });
    });
    </script>

</body>

</html>