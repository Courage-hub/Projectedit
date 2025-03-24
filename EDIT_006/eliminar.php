<?php
// Iniciar la sesión al principio del script
session_start();
include("conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
    // Si no está logueado, redirigir al login
    header("Location: login.php");
    exit();
}

// Verificar si se ha proporcionado un ID válido
if (isset($_GET['id'])) {
    // Obtener el ID del parámetro de la URL
    $id = $_GET['id'];

    // Consulta para eliminar el contenido con el ID proporcionado
    $query = "DELETE FROM fpproject WHERE id = $id";

    // Ejecutar la consulta
    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "SET @count = 0;");
        mysqli_query($conn, "UPDATE fpproject SET id = @count:= @count + 1;");
        mysqli_query($conn, "ALTER TABLE fpproject AUTO_INCREMENT = 1;");
        // Redirigir a la página principal con un mensaje de éxito
        header("Location: index.php?success=1");
        exit();
    } else {
        // En caso de error, redirigir con un mensaje de error
        header("Location: index.php?error=1");
        exit();
    }
} else {
    // Si no se proporciona un ID válido, redirigir con un mensaje de error
    header("Location: index.php?error=1");
    exit();
}
?>