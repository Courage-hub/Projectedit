<?php
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "fpproject";

  // Crear conexió
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// Recibir los datos del editor Quill
$titulo = $_POST['titulo'];

// Insertar los datos en la base de datos
$sql = "INSERT INTO fpproject (titulo) VALUES ('$titulo')";


// Cerrar la conexión
$conn->close();
?>
