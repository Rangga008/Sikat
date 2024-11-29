    <?php
    session_start();
    require '../connection.php';

    if (isset($_POST['cart_id']) && isset($_POST['action'])) {
        $cart_id = $_POST['cart_id'];
        $action = $_POST['action'];

        // Mendapatkan nilai condiments dari form
        $condiments = isset($_POST['condiments']) ? $_POST['condiments'] : NULL; // Menyimpan NULL jika tidak ada yang dipilih

        // Cek apakah cart_id valid
        $query = "SELECT cart.quantity, produk.stock, cart.condiments FROM cart
                JOIN produk ON cart.product_id = produk.id 
                WHERE cart.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $quantity = $row['quantity'];
            $stock = $row['stock']; // Jumlah stok produk

            // Tentukan perubahan kuantitas berdasarkan aksi
            if ($action === 'increase' && $quantity < $stock) {
                $quantity++;
            } elseif ($action === 'decrease' && $quantity > 1) {
                $quantity--;
            }

            // Mengambil nilai dari form
    // Mengambil nilai dari form
    if (isset($_POST['condiments']) && is_array($_POST['condiments'])) {
        $condiments = implode(',', $_POST['condiments']);  // Gabungkan array menjadi string
    } else {
        // Jika tidak ada yang dipilih, set NULL atau kosongkan
        $condiments = NULL;
    }

    // Update kuantitas dan pilihan condiments
    $update_query = "UPDATE cart SET quantity = ?, condiments = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("isi", $quantity, $condiments, $cart_id);
    $update_stmt->execute();
        }
    }

    header("Location: ../cart.php"); // Kembali ke halaman keranjang
    exit();
    ?>