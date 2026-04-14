<?php
/**
 * SISTEMA DE REGISTRO Y DISTRIBUCIÓN DE DOCUMENTOS
 * Grupo 01 - Arquitectura de 3 Capas
 * * Controlador (API REST)
 * Este archivo centraliza el manejo de peticiones HTTP (GET/POST) desde el Frontend.
 * Procesa las reglas de negocio e interactúa con la base de datos, retornando
 * respuestas estructuradas en formato JSON.
 */

// Establecer cabecera para respuestas JSON
header('Content-Type: application/json');

// Importar la conexión a la base de datos
require 'conexion.php';

// Capturar la acción solicitada, por defecto cadena vacía
$action = $_GET['action'] ?? '';

switch ($action) {
    
    /**
     * @action get_despachos
     * @description Obtiene la lista completa de despachos para poblar los selectores (combobox).
     */
    case 'get_despachos':
        $sql = "SELECT * FROM despacho";
        $result = mysqli_query($conn, $sql);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) { 
            $data[] = $row; 
        }
        
        echo json_encode($data);
        break;

    /**
     * @action registrar_documento
     * @description Registra un nuevo documento en estado "Pendiente de entrega".
     * Incluye validación para evitar códigos duplicados.
     */
    case 'registrar_documento':
        // Limpieza de datos para prevenir Inyección SQL
        $codigo    = mysqli_real_escape_string($conn, $_POST['codigo']);
        $tipo      = mysqli_real_escape_string($conn, $_POST['tipo']);
        $fecha     = mysqli_real_escape_string($conn, $_POST['fecha']);
        $remitente = mysqli_real_escape_string($conn, $_POST['remitente']);
        $despacho  = (int)$_POST['despacho'];

        // 1. Validar que el código único no exista previamente
        $check = mysqli_query($conn, "SELECT id FROM documento WHERE codigo_unico = '$codigo'");
        if(mysqli_num_rows($check) > 0){
            echo json_encode(["status" => "error", "message" => "El código único '$codigo' ya existe."]);
            exit;
        }

        // 2. Insertar el documento
        $sql = "INSERT INTO documento (codigo_unico, tipo_documento, fecha_recepcion, remitente, id_despacho, estado) 
                VALUES ('$codigo', '$tipo', '$fecha', '$remitente', $despacho, 'Pendiente de entrega')";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode(["status" => "success", "message" => "Documento registrado con éxito."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al registrar el documento."]);
        }
        break;

    /**
     * @action listar_documentos
     * @description Devuelve la lista de documentos. Permite filtrado por código y/o despacho.
     */
    case 'listar_documentos':
        $busqueda = $_GET['search'] ?? '';
        $despachoFilter = $_GET['despacho'] ?? '';
        
        // Consulta base con JOIN para traer el nombre del despacho
        $sql = "SELECT d.*, des.nombre as nombre_despacho 
                FROM documento d 
                INNER JOIN despacho des ON d.id_despacho = des.id 
                WHERE 1=1";
        
        // Aplicar filtros dinámicos si existen
        if($busqueda) {
            $sql .= " AND d.codigo_unico LIKE '%$busqueda%'";
        }
        if($despachoFilter) {
            $sql .= " AND d.id_despacho = '$despachoFilter'";
        }
        
        $sql .= " ORDER BY d.id DESC"; // Mostrar los más recientes primero

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) { 
            $data[] = $row; 
        }
        
        echo json_encode($data);
        break;

    /**
     * @action generar_guia
     * @description Agrupa todos los documentos "Pendientes" de un despacho específico,
     * genera un número de guía único y cambia su estado a "Cargo de envío".
     * Utiliza Transacciones (Commit/Rollback) para asegurar la integridad de la base de datos.
     */
    case 'generar_guia':
        $id_despacho = (int)$_POST['id_despacho'];
        
        // 1. Buscar si hay documentos pendientes para el despacho seleccionado
        $sqlDocs = "SELECT id FROM documento WHERE id_despacho = $id_despacho AND estado = 'Pendiente de entrega'";
        $resDocs = mysqli_query($conn, $sqlDocs);
        
        if(mysqli_num_rows($resDocs) == 0){
            echo json_encode(["status" => "error", "message" => "No hay documentos pendientes para enviar a este despacho."]);
            exit;
        }

        // 2. Generar número de guía único (Ej: GUIA-00452)
        $num_guia = "GUIA-" . str_pad(rand(1, 99999), 5, "0", STR_PAD_LEFT);
        
        // 3. Iniciar Transacción
        mysqli_begin_transaction($conn);
        try {
            // Crear la cabecera de la guía
            $sqlGuia = "INSERT INTO guia_remito (numero_guia, id_despacho, estado) VALUES ('$num_guia', $id_despacho, 'Cargo de envío')";
            mysqli_query($conn, $sqlGuia);
            $id_guia = mysqli_insert_id($conn); // Obtener el ID insertado

            // Insertar el detalle por cada documento y actualizar su estado
            while($doc = mysqli_fetch_assoc($resDocs)){
                $id_doc = $doc['id'];
                
                // Asociar documento a la guía
                mysqli_query($conn, "INSERT INTO detalle_guia (id_guia, id_documento) VALUES ($id_guia, $id_doc)");
                
                // Actualizar estado para reflejar el flujo de procesos
                mysqli_query($conn, "UPDATE documento SET estado = 'Cargo de envío' WHERE id = $id_doc");
            }
            
            // Confirmar los cambios
            mysqli_commit($conn);
            echo json_encode(["status" => "success", "message" => "Éxito. Guía $num_guia generada. Documentos en estado de envío."]);
            
        } catch (Exception $e) {
            // Revertir cambios si hay error
            mysqli_rollback($conn);
            echo json_encode(["status" => "error", "message" => "Error crítico al generar la guía: " . $e->getMessage()]);
        }
        break;

    /**
     * @action actualizar_estado_documento
     * @description Actualiza el estado final de un documento (Entregado o Notificado).
     */
    case 'actualizar_estado_documento':
        $id_doc = (int)$_POST['id_documento'];
        $nuevo_estado = mysqli_real_escape_string($conn, $_POST['nuevo_estado']);
        
        $sql = "UPDATE documento SET estado = '$nuevo_estado' WHERE id = $id_doc";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode(["status" => "success", "message" => "Estado actualizado correctamente a: $nuevo_estado"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar el estado."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no reconocida."]);
        break;
}
?>