<?php
session_start();
require 'config/database.php';
require "config/partials/header.php";
$nombreUsuario = $_SESSION['nombre'] ?? '';
$usuarioId = $_SESSION['id'] ?? null;

// Obtener la lista de materiales disponibles
$query = "SELECT * FROM materiales";
$resultadoMateriales = mysqli_query($conn, $query);
// Obtener el nombre del proyecto desde la base de datos si se ha pasado por GET
$nombreProyecto = '';
if (isset($_GET['id'])) {
    $idProyecto = $_GET['id'];
    $sqlNombreProyecto = "SELECT nombre_empresa FROM empresas WHERE id = ?";
    $stmtNombreProyecto = $conn->prepare($sqlNombreProyecto);
    $stmtNombreProyecto->bind_param("i", $idProyecto);
    $stmtNombreProyecto->execute();
    $stmtNombreProyecto->store_result();
    if ($stmtNombreProyecto->num_rows > 0) {
        $stmtNombreProyecto->bind_result($nombreProyecto);
        $stmtNombreProyecto->fetch();
    }
    $stmtNombreProyecto->close();
}
// Procesamiento del formulario para agregar un nuevo material a detalle_temp
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['materiales']) && isset($_POST['cantidad']) && isset($_POST['id_proyecto']) && $usuarioId) {
    // Obtener los datos del formulario
    $materialId = $_POST['materiales'][0]; // Si solo se permite seleccionar un material
    $cantidad = $_POST['cantidad'];
    $idProyecto = $_POST['id_proyecto']; // Obtener el ID del proyecto

    // Obtener el nombre del material seleccionado
    $stmtNombreMaterial = $conn->prepare("SELECT nombre_material FROM materiales WHERE id = ?");
    $stmtNombreMaterial->bind_param("i", $materialId);
    $stmtNombreMaterial->execute();
    $resultNombreMaterial = $stmtNombreMaterial->get_result();
    $rowNombreMaterial = $resultNombreMaterial->fetch_assoc();
    $nombreMaterial = $rowNombreMaterial['nombre_material'];

    // Insertar los datos en la tabla detalle_temp
    $insertMaterial = "INSERT INTO detalle_temp (usuario_id, material_id, nombre_material, cantidad, id_empresa) VALUES (?, ?, ?, ?, ?)";
    $stmtInsertMaterial = $conn->prepare($insertMaterial);
    $stmtInsertMaterial->bind_param("iisii", $usuarioId, $materialId, $nombreMaterial, $cantidad, $idProyecto); // Agregar el id_proyecto
    $stmtInsertMaterial->execute();
    $stmtInsertMaterial->close();
}


// Manejar la solicitud POST para eliminar un registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_id'])) {
    $eliminarId = $_POST['eliminar_id'];

    // Eliminar el registro de la tabla detalle_temp
    $eliminarMaterial = "DELETE FROM detalle_temp WHERE id = ?";
    $stmtEliminarMaterial = $conn->prepare($eliminarMaterial);
    $stmtEliminarMaterial->bind_param("i", $eliminarId);
    $stmtEliminarMaterial->execute();
    $stmtEliminarMaterial->close();

    // Redireccionar para evitar el reenvío del formulario
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

?>

<body class="d-flex flex-column h-100">
    <img src="images/encabezadoactual.png" width="500">
    <div class="container py-3">
        <h2 class="text-center">Materiales</h2>
        <h3>Bienvenido, <?php echo $nombreUsuario; ?></h3>
        <a href="home.php" class="btn btn-warning">Volver</a>
        <hr>
        <?php if (isset($_SESSION['msg']) && isset($_SESSION['color'])) { ?>
            <div class="alert alert-<?= $_SESSION['color']; ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['msg']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php
            unset($_SESSION['color']);
            unset($_SESSION['msg']);
        }
        ?>
        <!-- Formulario para agregar nuevo proyecto -->

        <div class="form-group mb-3">
            <label for="nombreProyecto">Nombre del proyecto</label>
            <input type="text" class="form-control" id="nombreProyecto" value="<?php echo $nombreProyecto; ?>" readonly>

        </div>

        <!-- Formulario para agregar nuevo material -->
        <form action="" method="POST" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="materialExistente">Material</label>
                    <select class="form-control" id="materialExistente" name="materiales[]">
                        <option value="">Seleccione el material</option>
                        <?php while ($row = mysqli_fetch_assoc($resultadoMateriales)) { ?>
                            <option value="<?= $row['id']; ?>"><?= $row['nombre_material']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="cantidadMaterial">Cantidad</label>
                    <input type="number" class="form-control" id="cantidadMaterial" placeholder="Cantidad" name="cantidad" min="1" required>
                </div>
                <div class="form-group col-md-2 align-self-end mt-2">
                    <form action="" method="POST" class="mb-4">
                        <!-- Otros campos del formulario -->
                        <input type="hidden" name="id_proyecto" value="<?php echo $idProyecto; ?>">
                        <button type="submit" class="btn btn-primary btn-block">Agregar Material</button>
                    </form>

                </div>
            </div>
        </form>
        <!-- Agregar este formulario al final de tu página -->
        <form action="guardar_proyecto.php" method="post">
            <input type="hidden" name="id_proyecto" value="<?= $idProyecto ?>">
            <button type="submit" class="btn btn-success my-3">Guardar Proyecto</button>
        </form>

        <!-- Formulario para agregar nuevo material si no está en la lista -->
        <!-- <form action="" method="POST" class="mb-4">
            <div class="form-group">
                <label for="nuevoRegistro">Añadir Nuevo Material</label>
                <input type="text" class="form-control" id="nuevoRegistro" placeholder="Agregar un nuevo material" name="nuevoRegistro">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Registrar Nuevo Material</button>
        </form> -->

        <!-- Lista de materiales seleccionados -->
        <table class="table table-sm table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Material</th>
                    <th>Cantidad</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php

                // Obtener los materiales seleccionados por el usuario para el proyecto actual desde la tabla de historial
                // $querySelectedMaterials = "SELECT hm.id, hm.cantidad, m.nombre_material 
                //            FROM historial_materiales hm
                //            JOIN materiales m ON hm.id_material = m.id 
                //            WHERE hm.id_empresa = ?";
                // $stmtSelectedMaterials = $conn->prepare($querySelectedMaterials);
                // $stmtSelectedMaterials->bind_param("i", $idProyecto); // Filtrar por el proyecto actual
                // $stmtSelectedMaterials->execute();
                // $resultSelectedMaterials = $stmtSelectedMaterials->get_result();
                // Obtener los materiales seleccionados por el usuario para el proyecto actual
                
                $querySelectedMaterials = "SELECT * FROM detalle_temp WHERE usuario_id = ? AND id_empresa = ?";
                $stmtSelectedMaterials = $conn->prepare($querySelectedMaterials);
                $stmtSelectedMaterials->bind_param("ii", $usuarioId, $idProyecto); // Aquí se filtra por el proyecto actual
                $stmtSelectedMaterials->execute();
                $resultSelectedMaterials = $stmtSelectedMaterials->get_result();

                // Mostrar los materiales seleccionados
                while ($row = $resultSelectedMaterials->fetch_assoc()) {
                ?>
                    <tr>
                        <td><?= $row['nombre_material']; ?></td>
                        <td><?= $row['cantidad']; ?></td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="eliminar_id" value="<?= $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#eliminaModal"><i class="fa-solid fa-trash"></i> Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>

    <?php require "config/partials/footer.php"; ?>