<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("conexion_access.php"); // Cambiar include por require_once
require_once("conexion_mysql.php"); // Asegúrate de incluir la conexión a MySQL
require_once('config.php'); // Incluir configuración

/**
 * Verifica si el usuario está logueado.
 * Si no lo está, redirige a la página de inicio de sesión.
 */
function verificarLogin() {
    if (!isset($_SESSION['id'])) {
        if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
            header("Location: login.php");
            exit();
        }
    }
}

/**
 * Verifica si el usuario tiene un rol específico.
 * Si no lo tiene, redirige a la página principal.
 */
function verificarPermisos($rolRequerido) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != $rolRequerido) {
        header("Location: index.php");
        exit();
    }
}

/**
 * Redirige a una URL específica.
 */
function redirigir($url) {
    header("Location: $url");
    exit();
}

/**
 * Ejecuta una inserción en ambas bases de datos
 */
function insertarEnAmbasDB($query) {
    // Preparar consulta para Access (con corchetes)
    $query_access = $query;
    
    // Preparar consulta para MySQL (sin corchetes)
    $query_mysql = str_replace(['[', ']'], '`', $query);

    // Insertar en Access
    $conexion_access = obtenerConexionAccess();
    $result_access = odbc_exec($conexion_access, $query_access);
    if (!$result_access) {
        throw new Exception("Error en Access: " . odbc_errormsg($conexion_access));
    }

    // Insertar en MySQL
    $conexion_mysql = obtenerConexionMySQL();
    $result_mysql = $conexion_mysql->query($query_mysql);
    if (!$result_mysql) {
        throw new Exception("Error en MySQL: " . $conexion_mysql->error);
    }

    return true;
}

/**
 * Sincroniza una actualización en ambas bases de datos
 */
function actualizarEnAmbasDB($query_access, $query_mysql) {
    // Actualizar en Access
    $conexion_access = obtenerConexionAccess();
    if (!odbc_exec($conexion_access, $query_access)) {
        throw new Exception("Error en Access: " . odbc_errormsg($conexion_access));
    }

    // Actualizar en MySQL
    $conexion_mysql = obtenerConexionMySQL();
    if (!$conexion_mysql->query($query_mysql)) {
        throw new Exception("Error en MySQL: " . $conexion_mysql->error);
    }
}

/**
 * Ejecuta una consulta en la base de datos especificada
 */
function ejecutarConsulta($query, $db = 'both') {
    if ($db === 'both' && (stripos($query, 'INSERT') === 0)) {
        return insertarEnAmbasDB($query);
    }
    if ($db === 'access') {
        $conexion = obtenerConexionAccess();
        $resultado = odbc_exec($conexion, $query);
        if (!$resultado) {
            die("❌ Error en consulta Access: " . odbc_errormsg($conexion));
        }
        return ['tipo' => 'access', 'resultado' => $resultado];
    } elseif ($db === 'mysql') {
        $conexion = obtenerConexionMySQL();
        $resultado = $conexion->query($query);
        if (!$resultado) {
            die("❌ Error en consulta MySQL: " . $conexion->error);
        }
        return ['tipo' => 'mysql', 'resultado' => $resultado];
    }
    die("❌ Tipo de base de datos no soportado.");
}

/**
 * Obtiene una fila de resultados como array asociativo
 */
function obtenerFila($resultadoConsulta) {
    if ($resultadoConsulta['tipo'] === 'access') {
        return odbc_fetch_array($resultadoConsulta['resultado']);
    } else {
        return $resultadoConsulta['resultado']->fetch_assoc();
    }
}

/**
 * Sincroniza datos entre Access y MySQL.
 * @param string $tabla La tabla a sincronizar.
 */
function sincronizarBasesDeDatos($tabla) {
    $result_access = ejecutarConsulta("SELECT * FROM $tabla", 'access');
    $conexion_mysql = obtenerConexionMySQL();

    while ($fila = obtenerFila($result_access)) {
        $columnas = implode(", ", array_keys($fila));
        $valores = implode("', '", array_map('addslashes', array_values($fila)));
        $query_mysql = "REPLACE INTO $tabla ($columnas) VALUES ('$valores')";
        $conexion_mysql->query($query_mysql);
    }
}
?>