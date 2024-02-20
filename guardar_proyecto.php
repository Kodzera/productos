<?php
session_start();
require 'config/database.php';

// Verificar si se ha enviado el formulario y el usuario está autenticado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['id'])) {
    $usuarioId = $_SESSION['id'];
    $idProyecto = $_POST['id_proyecto'];

    // Obtener los materiales seleccionados por el usuario
    $querySelectedMaterials = "SELECT * FROM detalle_temp WHERE usuario_id = ?";
    $stmtSelectedMaterials = $conn->prepare($querySelectedMaterials);
    $stmtSelectedMaterials->bind_param("i", $usuarioId);
    $stmtSelectedMaterials->execute();
    $resultSelectedMaterials = $stmtSelectedMaterials->get_result();

    // Insertar los materiales seleccionados en la tabla Detalles
    $insertDetalle = "INSERT INTO detalles (id_empresa, id_material, cantidad) VALUES (?, ?, ?)";
    $stmtInsertDetalle = $conn->prepare($insertDetalle);
    $stmtInsertDetalle->bind_param("iii", $idProyecto, $idMaterial, $cantidad);

    // Insertar los materiales seleccionados en la tabla de historial
    $insertHistorial = "INSERT INTO historial_materiales (id_empresa, id_material, cantidad) VALUES (?, ?, ?)";
    $stmtInsertHistorial = $conn->prepare($insertHistorial);
    $stmtInsertHistorial->bind_param("iii", $idProyecto, $idMaterial, $cantidad);

    while ($row = $resultSelectedMaterials->fetch_assoc()) {
        $idMaterial = $row['material_id'];
        $cantidad = $row['cantidad'];
        $stmtInsertDetalle->execute();
        $stmtInsertHistorial->execute(); // Guardar una copia en el historial
    }

    $stmtInsertHistorial->close();
    // Cerrar las consultas preparadas después de usarlas
    $stmtInsertDetalle->close();

    // Eliminar los registros de detalle_temp después de guardar el proyecto
    $deleteTempRecords = "DELETE FROM detalle_temp WHERE usuario_id = ?";
    $stmtDeleteTempRecords = $conn->prepare($deleteTempRecords);
    $stmtDeleteTempRecords->bind_param("i", $usuarioId);
    $stmtDeleteTempRecords->execute();
    $stmtDeleteTempRecords->close();

    // Redireccionar al usuario a una página de confirmación o a donde sea necesario
    header("Location: home.php");
    exit();
} else {
    // Si no se envió el formulario o el usuario no está autenticado, redirigir a la página de inicio
    header("Location: index.php");
    exit();
}
