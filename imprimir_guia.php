<?php
include("conexion.php");
$id_guia = $_GET['id'];

// Obtener datos de la guía
$sql_guia = "SELECT * FROM guias_remito WHERE id_guia = $id_guia";
$res_guia = mysqli_fetch_assoc(mysqli_query($conn, $sql_guia));

// Obtener documentos asociados
$sql_docs = "SELECT d.*, des.nombre_despacho 
             FROM detalle_guia dg
             JOIN documentos d ON dg.codigo_documento = d.codigo_unico
             JOIN despachos des ON d.id_despacho = des.id_despacho
             WHERE dg.id_guia = $id_guia";
$res_docs = mysqli_query($conn, $sql_docs);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Remito <?php echo $res_guia['numero_guia']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; color: #393738; }
        .header { text-align: center; border-bottom: 3px solid #DC962A; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .firma { margin-top: 50px; display: flex; justify-content: space-around; }
        .espacio-firma { border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Imprimir Guía</button>
        <a href="bandeja_entrada.php">Volver</a>
    </div>

    <div class="header">
        <h1>GUÍA DE REMITO DE DOCUMENTOS</h1>
        <p>Número: <strong><?php echo $res_guia['numero_guia']; ?></strong> | Fecha: <?php echo $res_guia['fecha_emision']; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código Doc.</th>
                <th>Tipo</th>
                <th>Remitente</th>
                <th>Despacho Destino</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($res_docs)): ?>
            <tr>
                <td><?php echo $row['codigo_unico']; ?></td>
                <td><?php echo $row['tipo_documento']; ?></td>
                <td><?php echo $row['remitente']; ?></td>
                <td><?php echo $row['nombre_despacho']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="firma">
        <div class="espacio-firma"><br>Firma Mensajero</div>
        <div class="espacio-firma"><br>Sello/Firma Recepción</div>
    </div>
</body>
</html>