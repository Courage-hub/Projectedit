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
    $id = intval($_GET['id']);
    $user_id = $_SESSION['id'];

    // Verificar si el usuario es el creador del registro
    $query = "SELECT * FROM fpproject WHERE id = '$id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Eliminar el registro
        $deleteQuery = "DELETE FROM fpproject WHERE id = $id";
        mysqli_query($conn, $deleteQuery);

        // Reorganizar los IDs
        $reorderQuery = "SET @row_number = 0";
        mysqli_query($conn, $reorderQuery);

        $updateIdsQuery = "UPDATE fpproject SET id = (@row_number := @row_number + 1) ORDER BY id";
        mysqli_query($conn, $updateIdsQuery);

        // Reiniciar el AUTO_INCREMENT
        $resetAutoIncrement = "ALTER TABLE fpproject AUTO_INCREMENT = 1";
        mysqli_query($conn, $resetAutoIncrement);

        // Redirigir a la página principal con un mensaje de éxito
        header("Location: index.php?success=1");
        exit();
    } else {
        echo "No tienes permiso para eliminar este registro.";
    }
} else {
    // Si no se proporciona un ID válido, redirigir con un mensaje de error
    header("Location: index.php?error=1");
    exit();
}
?>