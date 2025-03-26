<?php
// Incluir archivo de conexión
include("conexion.php");

// Verificar si se ha proporcionado un ID válido
if (isset($_GET['id'])) {
    // Obtener el ID del parámetro de la URL
    $id = $_GET['id'];

    // Consulta para eliminar el contenido con el ID proporcionado
    $query = "DELETE FROM fpproject WHERE id = $id";

    // Ejecutar la consulta
    if (mysqli_query($conn, $query)) {
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