<?php
require 'fpdf/fpdf.php';
require 'config/database.php';

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo o título
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Reporte de Materiales por Empresa'), 0, 1, 'C');
        $this->Ln(10);

        // Agregar cabecera para el código de material
        $this->Cell(40, 10, 'Codigo del Material', 1, 0, 'C');
        $this->Cell(60, 10, 'Material', 1, 0, 'C');
        $this->Cell(40, 10, 'Cantidad', 1, 1, 'C');
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

if ($idEmpresa) {
    // Obtener datos de la base de datos
    $sql = "SELECT d.*, m.nombre_material, m.codigo
            FROM Detalles d
            INNER JOIN Materiales m ON d.id_material = m.id
            WHERE d.id_empresa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();
    $result = $stmt->get_result();

    // Llenar la tabla con los datos de la base de datos
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(40, 10, $row['codigo'], 1, 0, 'C'); // Agregar el código del material
        $pdf->Cell(60, 10, $row['nombre_material'], 1, 0, 'L');
        $pdf->Cell(40, 10, $row['cantidad'], 1, 1, 'C');
    }

    // Salida del PDF
    $pdf->Output();

    // Eliminar los registros de detalle_temp después de generar el PDF
    $eliminarRegistrosTemp = "DELETE FROM detalle_temp WHERE id_empresa = ?";
    $stmtEliminarRegistrosTemp = $conn->prepare($eliminarRegistrosTemp);
    $stmtEliminarRegistrosTemp->bind_param("i", $idEmpresa);
    $stmtEliminarRegistrosTemp->execute();
    $stmtEliminarRegistrosTemp->close();
} else {
    echo "ID de empresa no válido.";
}
