
<?php
require('./fpdf.php');
$id = $_GET['id'];

echo $id;

class PDF extends FPDF
{
  function Header()
  {
    //include '../../recursos/Recurso_conexion_bd.php';//llamamos a la conexion BD

    //$consulta_info = $conexion->query(" select *from hotel ");//traemos datos de la empresa desde BD
    //$dato_info = $consulta_info->fetch_object();
    $this->Image('encabezadoactual.png', 10, 8, 200); //logo de la empresa,moverDerecha,moverAbajo,tamañoIMG
    $this->SetFont('Arial', 'B', 16); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
    $this->Cell(45); // Movernos a la derecha
    $this->SetTextColor(0, 0, 0); //color
    //creamos una celda o fila
    $this->Ln(50); // Salto de línea
    $this->Cell(190, 8, utf8_decode('REQUERIMIENTOS DE MATERIALES'), 1, 1, 'C', 0); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
    $this->Ln(3); // Salto de línea
    $this->SetTextColor(103); //color

  }
  
  // Pie de página
  function Footer()
  {
    $this->SetY(-15); // Posición: a 1,5 cm del final
    $this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
    $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C'); //pie de pagina(numero de pagina)

    $this->SetY(-15); // Posición: a 1,5 cm del final
    $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
    $hoy = date('d/m/Y');
    $this->Cell(355, 10, utf8_decode($hoy), 0, 0, 'C'); // pie de pagina(fecha de pagina)
  }
}
require 'cn.php';

$consulta = "SELECT * FROM detalle ";
$resultado = $mysqli->query($consulta);


ob_end_clean();

// Crear el objeto PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(20, 5, 'Proyecto:', 1);
  $pdf->Cell(20, 5, 'Codigo:', 1);
  $pdf->Cell(60, 5, 'Nombre:', 1);
  $pdf->Cell(20, 5, 'Cantidad:', 1);
  
  while ($row = $resultado->fetch_assoc()) {
    $pdf->SetFont('Arial', 'I', 10); 
    $pdf->Ln(); // Salto de línea  
  
    $pdf->Cell(20, 5, $row['empresa'], 1, 0, 'C', 0);
    $pdf->Cell(20, 5, $row['codigo'], 1, 0, 'C', 0);
    $pdf->Cell(60, 5, $row['nombre'], 1, 0, 'C', 0);
    $pdf->Cell(20, 5, $row['cantidad'], 1, 0, 'C', 0);
    
  }
  



// Salida del PDF
$pdf->Output('Lista_Productos.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)
