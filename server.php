<?php
    $servername = "localhost";
    $username_db = "root"; 
    $password_db = "";    
    $dbname = "register_db"; 

    $conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8mb4");
?>