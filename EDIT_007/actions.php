<?php
//autor:Courage 
session_start();
require_once("includes/conexion_access.php"); // Cambiar include por require_once

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
    header("Location: login.php");
    exit();
}

// Obtener la conexión
$conexion_access = obtenerConexionAccess();
if (!$conexion_access) {
    die("❌ Error de conexión a la base de datos: " . odbc_errormsg());
}

// Obtener el ID del registro
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar si el registro existe
$query = "SELECT titulo, instruccion FROM fpproject WHERE id = $id";
$result = odbc_exec($conexion_access, $query);
if (!$result) {
    die("❌ Error al ejecutar la consulta: " . odbc_errormsg($conexion_access));
}

// Consultar el registro por ID
$query = "SELECT titulo, instruccion FROM fpproject WHERE id = $id";
$result = odbc_exec($conexion_access, $query);
if (!$result) {
    die("❌ Error al ejecutar la consulta: " . odbc_errormsg($conexion_access));
}

// Verificar si se encontró el registro
if (odbc_fetch_row($result)) {
    $titulo = odbc_result($result, "titulo");
    $instruccion = odbc_result($result, "instruccion");

    // Eliminar etiquetas HTML
    $instruccion = strip_tags($instruccion);

    // Crear la carpeta y el archivo HTML si no existen
    $folderPath = "datos/edit_data/edit_$id";
    $filePath = "$folderPath/edit_$id.html";

    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    // Corregir los enlaces de los videos en la instrucción
    $titulo = htmlspecialchars($titulo);
    $cleanedInstruction = htmlspecialchars_decode($instruccion);
    $cleanedInstruction = preg_replace_callback(
        '/src="(\/\/www\.youtube\.com\/embed\/[a-zA-Z0-9_-]+)"/',
        function ($matches) {
            $url = $matches[1];
            // Asegurarse de que el enlace tenga el prefijo correcto
            if (strpos($url, 'https:') === false) {
                $url = 'https:' . $url;
            }
            return 'src="' . $url . '"';
        },
        $cleanedInstruction
    );

    // Crear el contenido del archivo HTML
    $htmlContent = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>$titulo</title>
        </head>
        <body>
            <h1>$titulo</h1>
            $cleanedInstruction
        </body>
        </html>
    ";

    // Guardar el archivo HTML
    file_put_contents($filePath, $htmlContent);

    // Redirigir al archivo generado
    header("Location: $filePath");
    exit();
} else {
    echo "Registro no encontrado.";
    exit();
}
?>