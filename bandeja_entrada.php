<?php
include("conexion.php");
// Consultamos documentos pendientes con el nombre de su despacho
$sql = "SELECT d.*, des.nombre_despacho 
        FROM documentos d 
        JOIN despachos des ON d.id_despacho = des.id_despacho 
        WHERE d.estado = 'Pendiente'";
$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bandeja de Documentos - Universidad Continental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --naranja: #DC962A; --rojo: #AF3922; --gris: #393738; }
        .navbar { background-color: var(--naranja); }
        .btn-action { background-color: var(--rojo); color: white; }
        .table-thead { background-color: var(--gris); color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Sistema de Trámite - Fiscalía</span>
        </div>
    </nav>

    <div class="container">
        <h3>Documentos Pendientes de Despacho</h3>
        <form id="formGuia" action="generar_guia.php" method="POST">
            <table class="table table-hover shadow-sm">
                <thead class="table-thead">
                    <tr>
                        <th>Seleccionar</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Remitente</th>
                        <th>Despacho Destino</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><input type="checkbox" name="docs[]" value="<?php echo $row['codigo_unico']; ?>"></td>
                        <td><?php echo $row['codigo_unico']; ?></td>
                        <td><?php echo $row['tipo_documento']; ?></td>
                        <td><?php echo $row['remitente']; ?></td>
                        <td><?php echo $row['nombre_despacho']; ?></td>
                        <td><span class="badge bg-warning text-dark"><?php echo $row['estado']; ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-action">Generar Guía de Remito para Seleccionados</button>
        </form>
    </div>
    <script src="js/scripts.js"></script>
</body>
</html>