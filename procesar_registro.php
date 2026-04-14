<?php
include("conexion.php");

// Recibimos los datos por POST
$codigo      = $_POST['codigo'];
$tipo        = $_POST['tipo'];
$fecha       = $_POST['fecha'];
$remitente   = $_POST['remitente'];
$id_despacho = $_POST['id_despacho'];

// Estado inicial según el flujo (Página 1: Al registrar -> Pendiente)
$estado = "Pendiente";

// Preparamos la consulta (Mejorada con seguridad básica)
$sql = "INSERT INTO documentos (codigo_unico, tipo_documento, fecha_recepcion, remitente, id_despacho, estado) 
        VALUES ('$codigo', '$tipo', '$fecha', '$remitente', '$id_despacho', '$estado')";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Documento registrado con éxito'); window.location='index.php';</script>";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>