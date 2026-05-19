<?php
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'sounds';

    $conn = new mysqli(hostname: $servername, username: $username, password: $password, database: $dbname);

    if(!$conn)
    {
        die("Connection failed: " .mysqli_connect_error());
    }
?>