<?php
include("conexion.php");

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$field = $data['field'];
$value = $data['value'];

// Actualizar el campo en la base de datos
$query = "UPDATE usuarios SET $field = '$value' WHERE id = $id";
if (mysqli_query($conn, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>