<?php
require_once("includes/functions.php");
verificarLogin(); // Verifica si el usuario está logueado
require_once("includes/conexion_access.php"); // Conexión Access
require_once("includes/conexion_mysql.php"); // Conexión MySQL

if (isset($_POST['editar'])) {
    $conexion_access = obtenerConexionAccess();
    $conexion_mysql = obtenerConexionMySQL();
    $id = intval($_POST['id']);
    $titulo = str_replace("'", "''", $_POST['titulo']);
    $instruccion = str_replace("'", "''", $_POST['instruccion']);
    $user_id = intval($_SESSION['id']);

    // Verificar si el usuario actual es el creador del registro en Access
    $check_query_access = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
    $check_result_access = odbc_exec($conexion_access, $check_query_access);

    // Verificar si el usuario actual es el creador del registro en MySQL
    $check_query_mysql = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
    $check_result_mysql = $conexion_mysql->query($check_query_mysql);

    if (odbc_fetch_row($check_result_access) && $check_result_mysql->num_rows > 0) {
        // Actualizar el registro en Access
        $sql_access = "UPDATE fpproject SET titulo='$titulo', instruccion='$instruccion' WHERE id=$id";
        $result_access = odbc_exec($conexion_access, $sql_access);

        // Actualizar el registro en MySQL
        $sql_mysql = "UPDATE fpproject SET titulo='$titulo', instruccion='$instruccion' WHERE id=$id";
        $result_mysql = $conexion_mysql->query($sql_mysql);

        if ($result_access && $result_mysql) {
            echo "<script>alert('The data was updated correctly in both databases.');
            location.assign('index.php');
            </script>";
        } else {
            echo "<script>alert('ERROR: The data could not be updated in one or both databases.');</script>";
        }
    } else {
        echo "<script>alert('You do not have permission to edit this record.');
        location.assign('index.php');
        </script>";
    }

    // Liberar recursos y cerrar conexiones
    odbc_free_result($check_result_access);
    odbc_close($conexion_access);
    $check_result_mysql->free();
    $conexion_mysql->close();
}

if (isset($_GET['id'])) {
    $conexion_access = obtenerConexionAccess();
    $id = intval($_GET['id']);
    $user_id = intval($_SESSION['id']);

    // Verificar si el usuario actual es el creador del registro
    $sql = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
    $result = odbc_exec($conexion_access, $sql);

    if (odbc_fetch_row($result)) {
        $row = [
            'id' => odbc_result($result, "id"),
            'titulo' => odbc_result($result, "titulo"),
            'instruccion' => odbc_result($result, "instruccion")
        ];
    } else {
        echo "<script>alert('You do not have permission to access this record.');
        location.assign('index.php');
        </script>";
        exit();
    }

    // Cerrar el recurso de consulta
    odbc_free_result($result);
    odbc_close($conexion_access);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forvia - Edit</title>
    
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/css/all.min.css" rel="stylesheet">
    
    <!-- Summernote resources -->
    <link href="summernote-0.8.18-dist/bootstrap.min.css" rel="stylesheet">
    <script src="summernote-0.8.18-dist/jquery-3.5.1.min.js"></script>
    <script src="summernote-0.8.18-dist/bootstrap.min.js"></script>
    <link href="summernote-0.8.18-dist/summernote.min.css" rel="stylesheet">
    <script src="summernote-0.8.18-dist/summernote.min.js"></script>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-white py-3">
                        <h2 class="text-center fw-bold text-primary mb-0">Edit your instructions</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            
                            <!-- Campo para el título -->
                            <div class="mb-4">
                                <label for="titulo" class="form-label fw-bold">Title</label>
                                <input type="text" id="titulo" name="titulo" class="form-control" 
                                       value="<?= htmlspecialchars($row['titulo']) ?>" required>
                            </div>
                            
                            <!-- Campo para las instrucciones -->
                            <div class="mb-4">
                                <label for="summernote" class="form-label fw-bold">Instructions</label>
                                <textarea name="instruccion" id="summernote" class="input"><?= htmlspecialchars($row['instruccion']) ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="submit" class="btn btn-success px-4 py-2" name="editar">
                                    <i class="fas fa-sync-alt me-2"></i>Update
                                </button>
                                <a href="index.php" class="btn btn-danger px-4 py-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Edit your instruction here...',
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>