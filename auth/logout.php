<?php
session_start();

// Menghapus session untuk logout
session_unset();
session_destroy();

// Mengarahkan pengguna ke halaman index setelah logout
header("Location: ../index.php");
exit;
?>