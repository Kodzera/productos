<?php
session_start(); // Inicia la sesión

require 'fpdf/fpdf.php';
require 'config/database.php';

class PDF extends FPDF
{
    public $nombreEmpresa = '';

    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Image('images/encabezadoactual.png', 10, 10, 170); // Imagen de encabezado
        $this->Ln(15);

        // Convertir el nombre de la empresa a UTF-8 si es necesario
        $nombreEmpresa = mb_convert_encoding($this->nombreEmpresa, 'UTF-8');

        $this->Cell(0, 10, $nombreEmpresa, 0, 1, 'C');
        $this->Ln(10);

        $nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario Desconocido';
        setlocale(LC_TIME, 'es_ES.UTF-8'); // Establece la configuración regional a español
        $fechaActual = strftime('%d de %B de %Y'); // Formatea la fecha en español


        $this->Cell(0, 10, 'Tecnico: ' . $nombreUsuario . '   Fecha: ' . $fechaActual, 0, 1, 'L');
        $this->Ln(5);

        $this->Cell(20, 10, 'Codigo', 1, 0, 'C');
        $this->Cell(130, 10, 'Producto', 1, 0, 'C');
        $this->Cell(20, 10, 'Cantidad', 1, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$idEmpresa = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($idEmpresa <= 0) {
    die('ID de empresa no válido.');
}

// Consulta SQL para obtener el nombre de la empresa basado en el ID y el ID de usuario actual
$sqlEmpresa = "SELECT nombre_empresa FROM empresas WHERE id = ? AND id_usuario = ?";
$stmtEmpresa = $conn->prepare($sqlEmpresa);
$idUsuario = $_SESSION['id_usuario']; // Obtener el ID de usuario de la sesión
$stmtEmpresa->bind_param("ii", $idEmpresa, $idUsuario);
$stmtEmpresa->execute();
$stmtEmpresa->bind_result($nombreEmpresa);
$stmtEmpresa->fetch();
$stmtEmpresa->close();

$pdf->nombreEmpresa = $nombreEmpresa;

$sql = "SELECT d.*, m.nombre, m.id, d.fecha
        FROM Detalles d
        INNER JOIN Materiales m ON d.id_material = m.id
        WHERE d.id_empresa = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $pdf->Ln();
    $pdf->Cell(20, 10, $row['id_material'], 1, 0, 'C');
    $pdf->Cell(130, 10, $row['nombre'], 1, 0, 'L');
    $pdf->Cell(20, 10, $row['cantidad'], 1, 0, 'L');
}

$pdf->Output();
