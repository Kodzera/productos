<?php
session_start();

require_once 'config/dompdf/autoload.inc.php';
require 'config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);

$idEmpresa = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($idEmpresa <= 0) {
    die('ID de empresa no válido.');
}

// Obtener el nombre de la empresa del parámetro GET
$nombreEmpresa = isset($_GET['nombre_empresa']) ? $_GET['nombre_empresa'] : '';

// Contenido HTML para el PDF
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: Arial, sans-serif;
}
</style>
</head>
<body>
<img src="images/encabezadoactual.png" style="margin-bottom: 15px;"><br>
<p>' . $nombreEmpresa . '</p>
<p>Técnico: ' . $_SESSION['nombre'] . '</p>
<p>Fecha: ' . date('d de F de Y') . '</p>

<table border="1" cellspacing="0" cellpadding="5">
<tr>
<th>Código</th>
<th>Producto</th>
<th>Cantidad</th>
</tr>';

$sql = "SELECT d.*, m.nombre
        FROM Detalles d
        INNER JOIN Materiales m ON d.id_material = m.id
        WHERE d.id_empresa = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $html .= '
    <tr>
    <td>' . $row['id_material'] . '</td>
    <td>' . $row['nombre'] . '</td>
    <td>' . $row['cantidad'] . '</td>
    </tr>';
}

$html .= '
</table>
</body>
</html>';

// Cargar el contenido HTML en DOMPDF
$dompdf->loadHtml($html);

// Renderizar el PDF
$dompdf->render();

// Generar el PDF en la salida
$dompdf->stream();
