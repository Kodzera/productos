<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_proyecto'])) {
    $idProyecto = $_POST['id_proyecto'];

    // Eliminar los detalles relacionados
    $sqlDeleteDetalles = "DELETE FROM detalles WHERE id_empresa = ?";
    $stmtDeleteDetalles = $conn->prepare($sqlDeleteDetalles);
    $stmtDeleteDetalles->bind_param("i", $idProyecto);
    $stmtDeleteDetalles->execute();
    $stmtDeleteDetalles->close();

    // Eliminar la empresa
    $sqlDeleteEmpresa = "DELETE FROM empresas WHERE id = ?";
    $stmtDeleteEmpresa = $conn->prepare($sqlDeleteEmpresa);
    $stmtDeleteEmpresa->bind_param("i", $idProyecto);
    $stmtDeleteEmpresa->execute();
    $stmtDeleteEmpresa->close();

    // Redireccionar de vuelta al home.php
    header("Location: home.php");
    exit();
} else {
    // Si no se proporciona un ID de proyecto, redireccionar al home.php
    header("Location: home.php");
    exit();
}
