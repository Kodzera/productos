<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
}
$nombre = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
require 'config/database.php';

// Procesamiento del formulario para creagener un nuevo proyecto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre_proyecto'])) {
    $nombreProyecto = trim($_POST['nombre_proyecto']);

    if (!empty($nombreProyecto)) {
        $insertEmpresa = "INSERT INTO empresas (nombre_empresa) VALUES (?)";
        $stmtEmpresa = $conn->prepare($insertEmpresa);
        $stmtEmpresa->bind_param("s", $nombreProyecto);
        $stmtEmpresa->execute();
        $stmtEmpresa->close();

        $_SESSION['msg'] = 'Proyecto creado correctamente.';
        $_SESSION['color'] = 'success';
    } else {
        $_SESSION['msg'] = 'El nombre del proyecto es requerido.';
        $_SESSION['color'] = 'danger';
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redireccionar para evitar reenvío de formulario
    exit();
}

$sqlPeliculas = "SELECT id, nombre_empresa AS nombre_proyecto FROM empresas ORDER BY id DESC";
$peliculas = $conn->query($sqlPeliculas);

require "config/partials/header.php";
?>


<body class="d-flex flex-column h-100">
    <img src="images/encabezadoactual.png" width="500">
    <div class="container py-3">

        <h2 class="text-center">Mis Proyectos </h2>
        <div class="text-center">
            Usuario:
            <?php echo $nombre; ?>
        </div>
        <?php if (isset($_SESSION['msg']) && isset($_SESSION['color'])) { ?>
            <div class="alert alert-<?= $_SESSION['color']; ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['msg']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

        <?php
            unset($_SESSION['color']);
            unset($_SESSION['msg']);
        } ?>

        <div class="row justify-content-end">

            <div>
                <!-- Formulario para crear un nuevo proyecto -->
                <form action="" method="post" accept-charset="utf-8">
                    <div class="form-group">
                        <label for="nombre_proyecto">Nuevo Proyecto</label>
                        <input type="text" class="form-control mb-3" id="nombre_proyecto" name="nombre_proyecto" placeholder="Nombre del nuevo proyecto">
                    </div>
                    <button type="submit" class="btn btn-primary">Crear Proyecto</button>
                </form>
            </div>

            <div class="col-auto">
                <a href="index.php" class="btn btn-primary">Salir</a>
            </div>

            <form action="index1.php" method="post" accept-charset="utf-8">

                <table class="table table-sm table-striped table-hover mt-4">
                    <thead class="table-dark">
                        <tr>
                            <th>Id</th>
                            <th>Nombre del Proyecto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $peliculas->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= $row['nombre_proyecto']; ?></td>
                                <td>
                                    <a href="index1.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="generar_pdf.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-info" target="_blank"><i class="fa-solid fa-file-pdf"></i> Generar PDF</a>


                                    <form action="eliminar_proyecto.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id_proyecto" value="<?= $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este proyecto?')">Eliminar</button>
                                    </form>
                                    <form action="generar_excel.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id_proyecto" value="<?= $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Generar Excel</button>
                                    </form>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </form>
        </div>

        <?php
        $sqlGenero = "SELECT id, nombre FROM materiales ";
        $generos = $conn->query($sqlGenero);
        ?>

        <?php require "config/partials/footer.php"; ?>