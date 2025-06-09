<?php
// db_connect.php
$conn = new mysqli("localhost", "root", "", "transporte");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>