<?php
// Variable estática para almacenar la conexión a MySQL
static $conexion_mysql = null;

// Función para obtener la conexión a MySQL
function obtenerConexionMySQL() {
    global $conexion_mysql;
    if ($conexion_mysql === null || !($conexion_mysql instanceof mysqli)) {
        $host = 'localhost';
        $usuario = 'root';
        $clave = '';
        $base_datos = 'edit_0007';

        $conexion_mysql = @new mysqli($host, $usuario, $clave, $base_datos);

        if ($conexion_mysql->connect_error) {
            die("❌ Error de conexión MySQL: " . $conexion_mysql->connect_error);
        }
    }
    return $conexion_mysql;
}
?>
