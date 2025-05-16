<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("conexion_access.php"); // Cambiar include por require_once
require_once('config.php'); // Incluir configuración

function verificarLogin() {
    if (!isset($_SESSION['id'])) {
        if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
            header("Location: login.php");
            exit();
        }
    }
}

function verificarPermisos($rolRequerido) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != $rolRequerido) {
        header("Location: index.php");
        exit();
    }
}

function redirigir($url) {
    header("Location: $url");
    exit();
}

/**
 * Ejecuta una consulta en Access
 */
function ejecutarConsulta($query) {
    $conexion = obtenerConexionAccess();
    $resultado = odbc_exec($conexion, $query);
    if (!$resultado) {
        die("❌ Error en consulta Access: " . odbc_errormsg($conexion));
    }
    return $resultado;
}

/**
 * Obtiene una fila de resultados como array asociativo
 */
function obtenerFila($resultado) {
    return odbc_fetch_array($resultado);
}

/**
 * Ejecuta una consulta y devuelve todos los resultados como array
 * Libera el recurso de consulta inmediatamente
 */
function ejecutarConsultaObtenerTodo($query) {
    $conexion = obtenerConexionAccess();
    $resultado = odbc_exec($conexion, $query);
    if (!$resultado) {
        die("❌ Error en consulta Access: " . odbc_errormsg($conexion));
    }
    
    $filas = [];
    while ($fila = odbc_fetch_array($resultado)) {
        $filas[] = $fila;
    }
    
    // Liberar el recurso inmediatamente
    odbc_free_result($resultado);
    
    return $filas;
}
?>