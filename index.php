<?php

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sikat</title>
    <link rel="stylesheet" href="css/styleBeranda.css" />
</head>

<body>
    <?php 
    
    require_once 'connection.php';
    require 'layout/navbar.php';
    ?>
    <?php 
    require 'layout/slider.php';
     require 'layout/content.php';
    ?>

    <footer>
        <div class="footer-container">
            <a href="#">WhatsApp 08**-*******</a>
            <a href="#">@duka_makan.com</a>
        </div>
    </footer>
</body>

</html>