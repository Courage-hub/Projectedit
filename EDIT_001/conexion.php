<?php
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "fpproject";

  // Crear conexión
  $conn = new mysqli($servername, $username, $password, $database);
  // Verificar la conexión
  if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
  }
  // Seleccionar la base de datos
  mysqli_select_db($conn, $database);


  ?>