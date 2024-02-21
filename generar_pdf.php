<?php
session_start(); // Inicia la sesión

require 'fpdf/fpdf.php';
require 'config/database.php';
// require 'config/partials/header.php'; // Incluye el archivo de configuración y encabezado

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo o título
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Reporte de Materiales por Empresa'), 0, 1, 'C');
        $this->Ln(10);

        // Agregar nombre de usuario
        $nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario Desconocido'; // Si no está definido, usar 'Usuario Desconocido'
        $this->Cell(0, 10, 'Usuario: ' . $nombreUsuario, 0, 1, 'L');

        // Agregar cabecera para el código de material
        $this->Cell(40, 10, 'Codigo', 1, 0, 'C');
        $this->Cell(100, 10, 'Material', 1, 0, 'C'); // Aumentar el ancho de la celda
        $this->Cell(40, 10, 'Cantidad', 1, 0, 'C');
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1.5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Crear instancia de FPDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Obtener el ID de la empresa seleccionada
$idEmpresa = isset($_GET['id']) ? $_GET['id'] : null;

// Obtener datos de la base de datos
$sql = "SELECT d.*, m.nombre, m.id
        FROM Detalles d
        INNER JOIN Materiales m ON d.id_material = m.id
        WHERE d.id_empresa = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();

// Llenar la tabla con los datos de la base de datos
while ($row = $result->fetch_assoc()) {
    $pdf->Ln(); // Agregar un salto de línea para pasar a la siguiente fila

    $pdf->Cell(40, 10, $row['id_material'], 1, 0, 'C'); // Agregar el código del material
    $pdf->Cell(100, 10, $row['nombre'], 1, 0, 'L'); // Agregar el nombre del material
    $pdf->Cell(40, 10, $row['cantidad'], 1, 0, 'L'); // Agregar la cantidad del material
}

// Salida del PDF
$pdf->Output();
