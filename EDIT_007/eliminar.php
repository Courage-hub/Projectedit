<?php
require_once("includes/functions.php");
verificarLogin(); // Verifica si el usuario está logueado
require_once("includes/conexion_access.php"); // Conexión Access
require_once("includes/conexion_mysql.php"); // Conexión MySQL
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forvia - Delete</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php
    if (isset($_GET['id'])) {
        $conexion_access = obtenerConexionAccess();
        $conexion_mysql = obtenerConexionMySQL();
        $id = intval($_GET['id']);
        $user_id = intval($_SESSION['id']);

        // Verificar si el usuario actual es el creador del registro en Access
        $check_query_access = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
        $check_result_access = odbc_exec($conexion_access, $check_query_access);

        // Verificar si el usuario actual es el creador del registro en MySQL
        $check_query_mysql = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
        $check_result_mysql = $conexion_mysql->query($check_query_mysql);

        if (odbc_fetch_row($check_result_access) && $check_result_mysql->num_rows > 0) {
            // Eliminar el registro en Access
            $sql_access = "DELETE FROM fpproject WHERE id=$id";
            $result_access = odbc_exec($conexion_access, $sql_access);

            // Eliminar el registro en MySQL
            $sql_mysql = "DELETE FROM fpproject WHERE id=$id";
            $result_mysql = $conexion_mysql->query($sql_mysql);

            if ($result_access && $result_mysql) {
                echo "<script>alert('The record was deleted successfully from both databases.');
                location.assign('index.php');
                </script>";
            } else {
                echo "<script>alert('ERROR: The record could not be deleted from one or both databases.');</script>";
            }
        } else {
            echo "<script>alert('You do not have permission to delete this record.');
            location.assign('index.php');
            </script>";
        }

        // Liberar recursos y cerrar conexiones
        odbc_free_result($check_result_access);
        odbc_close($conexion_access);
        $check_result_mysql->free();
        $conexion_mysql->close();
    } else {
        echo "<script>alert('Invalid request.');
        location.assign('index.php');
        </script>";
    }
    ?>
</body>
</html>