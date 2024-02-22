<?php
session_start();
require "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta SQL para seleccionar el usuario por su nombre de usuario
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['contrasena'];

        // Verificar la contraseña
        if ($password === $stored_password) {
            $_SESSION['id_usuario'] = $row['id']; // Cambiamos 'id' por 'id_usuario'
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol'] = $row['rol']; // Agregar el rol del usuario a la sesión
            header("Location: home.php");
            exit();
        } else {
            echo "La contraseña no coincide";
        }
    } else {
        echo "Usuario no encontrado";
    }
}
require "config/partials/header.php";
?>


<body class="bg-primary">
    <style>
        .redinco {
            font-size: 500;
            border-bottom: 2px solid #1C3969;
        }
    </style>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container h-100">
                    <div class="row h-100 justify-content-center align-items-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="row justify-content-center"><img src="images/logo.png" width="250"></div>
                                <div class="card-body">
                                    <h3 class="text-center font-weight-light my-4">Bienvenido a <span class="redinco">Redinco</span></h3>
                                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                        <div class="form-group">
                                            <label class="small mb-1" for="inputUsuario">Usuario</label>
                                            <input class="form-control py-4" id="inputUsuario" name="usuario" type="text" placeholder="Enter username" autofocus />
                                        </div>
                                        <div class="form-group">
                                            <label class="small mb-1" for="inputPassword">Contraseña</label>
                                            <input class="form-control py-4" id="inputPassword" name="password" type="password" placeholder="Enter password" />
                                        </div>

                                        <div class="form-group d-flex align-items-center justify-content-center mt-4 mb-0">
                                            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- <div class="card-footer text-center">
                                    <div class="small"><a href="register.html">Need an account? Sign up!</a></div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>


</html>