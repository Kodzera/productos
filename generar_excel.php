<?php
require 'config/database.php';

// Obtener el ID del proyecto seleccionado
$idProyecto = isset($_POST['id_proyecto']) ? $_POST['id_proyecto'] : null;

if ($idProyecto) {
    // Obtener los datos del proyecto desde la base de datos
    $sql = "SELECT d.*, m.nombre AS nombre_material
            FROM Detalles d
            INNER JOIN Materiales m ON d.id_material = m.id
            WHERE d.id_empresa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProyecto);
    $stmt->execute();
    $result = $stmt->get_result();

    // Configurar el tipo de contenido y el nombre del archivo
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="detalle_proyecto.csv"');
    header('Cache-Control: max-age=0');

    // Crear un puntero de archivo temporal (output stream)
    $output = fopen('php://output', 'w');

    // Escribir el encabezado en el archivo CSV

    // Escribir los datos en el archivo CSV
    while ($data = $result->fetch_assoc()) {
        fputcsv($output, array($data['id_material'], $data['nombre_material'], $data['cantidad']));
    }
    // Cerrar el puntero de archivo
    fclose($output);
} else {
    echo "ID de proyecto no válido.";
}
