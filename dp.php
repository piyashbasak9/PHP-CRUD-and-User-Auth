<?php
// Database connection file
$servername = "localhost";
$username = "root";
$password = "";
$database = "notes";

$com = mysqli_connect($servername, $username, $password, $database);
if (!$com) {
    die("Database connection faild for some error" . mysqli_connect_error());
}
?>
