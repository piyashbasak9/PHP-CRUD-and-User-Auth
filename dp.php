<?php
// Database connection file
$servername = "localhost";
$username = "root";
$password = "";
$database = "notes";

$com = mysqli_connect($servername, $username, $password, $database);
if (!$com) {
    die("Sorry we failed to connect: " . mysqli_connect_error());
}
?>
