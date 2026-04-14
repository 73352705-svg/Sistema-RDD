<?php
$host = "localhost";
$user = "root";
$pass = ""; // Por defecto en XAMPP está vacío
$db   = "sistema_tramite";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Configurar para que acepte tildes y eñes de la base de datos
mysqli_set_charset($conn, "utf8");
?>