<?php
include("conexion.php");

if(!empty($_POST['docs'])) {
    $documentos = $_POST['docs'];
    $num_guia = "G-" . date("YmdHis"); // Generamos un número de guía único
    
    // 1. Crear la Guía
    $sql_guia = "INSERT INTO guias_remito (numero_guia, usuario_responsable) VALUES ('$num_guia', 'Admin_Sede_Huancayo')";
    mysqli_query($conn, $sql_guia);
    $id_guia_insertada = mysqli_insert_id($conn);

    foreach($documentos as $doc_id) {
        // 2. Insertar el detalle
        $sql_detalle = "INSERT INTO detalle_guia (id_guia, codigo_documento) VALUES ($id_guia_insertada, '$doc_id')";
        mysqli_query($conn, $sql_detalle);

        // 3. Actualizar estado del documento
        $sql_update = "UPDATE documentos SET estado = 'Enviado' WHERE codigo_unico = '$doc_id'";
        mysqli_query($conn, $sql_update);
    }

    echo "<script>alert('Guía $num_guia generada con éxito'); window.location='bandeja_entrada.php';</script>";
} else {
    echo "No seleccionaste ningún documento.";
}
?>