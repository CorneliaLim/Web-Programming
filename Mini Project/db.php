<?php
    $db_host = "sql112.infinityfree.com";
    $db_user = "if0_39196045";
    $db_pwd  = "a30610";
    $db_name = "if0_39196045_miniproject";

    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $db_name);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }   
?>