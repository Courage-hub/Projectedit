<?php
// Archivo de bloqueo para controlar el acceso a la BD
// (Solo para Access ODBC)
define('LOCK_FILE', sys_get_temp_dir() . '/access_odbc_lock');
define('MAX_WAIT_TIME', 10); // Tiempo máximo de espera en segundos

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catch.php';

function obtenerConexionAccess() {
    static $conexion_access = null;

    // Si ya existe y es válida, la devolvemos
    if ($conexion_access !== null && is_resource($conexion_access)) {
        return $conexion_access;
    }

    // Si existe pero no es válida, cerramos
    if ($conexion_access !== null) {
        @odbc_close($conexion_access);
        $conexion_access = null;
    }

    // Conexión ODBC a Access
    $conexion_access = @odbc_connect(DSN_ACCESS, '', '');
    if (!$conexion_access) {
        mostrarErrorConexion();
    }
    return $conexion_access;
}

// Registrar función para cerrar la conexión al finalizar el script
register_shutdown_function(function () {
    static $conexion_access = null;
    if ($conexion_access !== null && is_resource($conexion_access)) {
        @odbc_close($conexion_access);
        $conexion_access = null;
    }
});
?>
<style>
  .date {
    font-size: small;
    text-align: center;
    color: gray;
    width: 10px;
  }
</style>
