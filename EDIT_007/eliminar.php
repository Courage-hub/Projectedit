<?php
require_once("includes/functions.php");
require_once("includes/conexion_access.php");
verificarLogin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = intval($_SESSION['id']);
    
    try {
        $conexion_access = obtenerConexionAccess();
        if (!$conexion_access) {
            header("Location: index.php?error=db");
            exit();
        }

        // Verificar si el registro existe y pertenece al usuario
        $check_query = "SELECT user_id FROM fpproject WHERE id = $id";
        $result = odbc_exec($conexion_access, $check_query);
        $row = odbc_fetch_array($result);

        if ($row && $row['user_id'] == $user_id) {
            // Eliminar de Access
            $query_access = "DELETE FROM fpproject WHERE id = $id AND user_id = $user_id";
            if (!odbc_exec($conexion_access, $query_access)) {
                header("Location: index.php?error=access");
                exit();
            }
            // Eliminar carpeta y archivos estáticos
            $folderPath = __DIR__ . "/datos/edit_data/edit_$id";
            if (file_exists($folderPath)) {
                $files = glob($folderPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) unlink($file);
                }
                rmdir($folderPath);
            }
            header("Location: index.php?deleted=1");
            exit();
        } else {
            header("Location: index.php?error=unauthorized");
            exit();
        }
    } catch (Exception $e) {
        header("Location: index.php?error=exception");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}