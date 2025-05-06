<?php
// Variable estática para almacenar la conexión a Access
static $conexion_access = null;

// Función para obtener la conexión a Access
function obtenerConexionAccess() {
    global $conexion_access;
    if ($conexion_access === null) {
        $conexion_access = odbc_connect("MiAccessDSN", "", ""); // Cambia "MiAccessDSN" por tu DSN de Access
        if (!$conexion_access) {
            die("❌ Error de conexión Access: " . odbc_errormsg());
        }
    }
    return $conexion_access;
}

// Registrar función para cerrar la conexión al finalizar el script
register_shutdown_function(function () use (&$conexion_access) {
    if (is_resource($conexion_access)) {
        odbc_close($conexion_access);
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
