<?php
include("conexion.php");

if(isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    
    // Actualizamos al estado final según el flujo de la práctica
    $sql = "UPDATE documentos SET estado = 'Cargo devuelto entregado' WHERE codigo_unico = '$codigo'";
    
    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('Documento finalizado con éxito'); window.location='consultas.php';</script>";
    }
}
?>