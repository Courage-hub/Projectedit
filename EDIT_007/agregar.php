<?php
require_once("includes/functions.php");
require_once("includes/conexion_access.php");
require_once("includes/conexion_mysql.php");
verificarLogin(); // Verifica si el usuario está logueado

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar'])) {
    try {
        $titulo = str_replace("'", "''", $_POST['titulo']);
        $instruccion = str_replace("'", "''", $_POST['instruccion']);
        $user_id = intval($_SESSION['id']);

        // Consulta para insertar en ambas bases de datos
        $query_access = "INSERT INTO fpproject (titulo, instruccion, user_id, [date]) 
                         VALUES ('$titulo', '$instruccion', $user_id, NOW())";
        $query_mysql = "INSERT INTO fpproject (titulo, instruccion, user_id, `date`) 
                        VALUES ('$titulo', '$instruccion', $user_id, NOW())";

        // Insertar en Access
        $conexion_access = obtenerConexionAccess();
        if (!odbc_exec($conexion_access, $query_access)) {
            throw new Exception("Error en Access: " . odbc_errormsg($conexion_access));
        }

        // Insertar en MySQL
        $conexion_mysql = obtenerConexionMySQL();
        if (!$conexion_mysql->query($query_mysql)) {
            throw new Exception("Error en MySQL: " . $conexion_mysql->error);
        }

        // Redirigir al index_access.php después de guardar exitosamente
        header("Location: index_access.php?success=1");
        exit();
    } catch (Exception $e) {
        $error = "Error saving instruction: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" width="device-width, initial-scale=1.0">
  <title>Forvia - Add</title>

  <!-- Bootstrap CSS -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="assets/css/all.min.css" rel="stylesheet">

  <!-- Summernote resources -->
  <link href="summernote-0.8.18-dist/bootstrap.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/jquery-3.5.1.min.js"></script>
  <script src="summernote-0.8.18-dist/bootstrap.min.js"></script>
  <link href="summernote-0.8.18-dist/summernote.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/summernote.min.js"></script>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow">
          <div class="card-header bg-white py-3">
            <h2 class="text-center fw-bold text-primary mb-0">Publish your instruction</h2>
          </div>
          <div class="card-body p-4">
            <?php if (isset($success)): ?>
              <div class="alert alert-success">
                <?= $success ?>
              </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
              <div class="alert alert-danger">
                <?= $error ?>
              </div>
            <?php endif; ?>
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
              <!-- Campo para el título -->
              <div class="mb-4">
                <label for="titulo" class="form-label fw-bold">Title</label>
                <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Enter the title"
                  required>
              </div>
              <!-- Campo para las instrucciones -->
              <div class="mb-4">
                <label for="summernote" class="form-label fw-bold">Instructions</label>
                <textarea id="summernote" name="instruccion"></textarea>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-success px-4 py-2" name="enviar">
                    <i class="fas fa-check me-2"></i>Submit
                </button>
                <a href="index.php" class="btn btn-danger px-4 py-2">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      $('#summernote').summernote({
        placeholder: 'Write your instruction here...',
        height: 300,
      });
    });
  </script>

  <script>
    // Alternar entre modo claro y oscuro
    document.addEventListener('DOMContentLoaded', function () {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlElement = document.documentElement;

        // Verificar si el modo oscuro está activado en localStorage
        if (localStorage.getItem('theme') === 'dark') {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>'; // Cambiar icono a sol
        } else {
            htmlElement.setAttribute('data-bs-theme', 'light');
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>'; // Cambiar icono a luna
        }

        // Alternar tema al hacer clic en el botón
        darkModeToggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
                htmlElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    });
  </script>

  <style>
    @font-face {
        font-family: "Poppins";
        src: url("assets/fonts/poppins/Poppins-Regular.woff2") format("woff2"),
            url("assets/fonts/poppins/Poppins-Regular.woff") format("woff"),
            url("assets/fonts/poppins/Poppins-Regular.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 300;
        src: url('assets/fonts/poppins/Poppins-Light.ttf') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url('assets/fonts/poppins/Poppins-Regular.ttf') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 500;
        src: url('assets/fonts/poppins/Poppins-Medium.ttf') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 600;
        src: url('assets/fonts/poppins/Poppins-SemiBold.ttf') format('truetype');
    }

    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 700;
        src: url('assets/fonts/poppins/Poppins-Bold.ttf') format('truetype');
    }

    :root {
        --primary-color: #2575fc;
        --secondary-color: #1a5bbf;
        --dark-color: #343a40;
        --light-color: #f8f9fa;
        --dark-bg: #1a1a1a;
        --dark-card-bg: #2c2c2c;
        --dark-border: #444;
        --dark-text: #e0e0e0;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-color);
        color: #333;
    }

    .card {
        border-radius: 10px;
        border: none;
        background-color: white;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        background-color: white;
        color: var(--primary-color);
        text-align: center;
    }

    .form-control {
        background-color: white;
        color: #333;
        border: 1px solid #ccc;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
    }

    .btn-success,
    .btn-danger {
        font-weight: 500;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        background-color: #0d8a52;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-danger:hover {
        background-color: #d32f2f;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Estilos para modo oscuro */
    html[data-bs-theme="dark"] body {
        background-color: #1a1a1a; /* Fondo general de la página */
        color: #e0e0e0;
    }

    html[data-bs-theme="dark"] .card {
        background-color: #2c2c2c; /* Fondo oscuro para la tarjeta */
        color: #e0e0e0;
        border: 1px solid #444;
    }

    html[data-bs-theme="dark"] .card-header {
        background-color: #2c2c2c; /* Fondo oscuro para el encabezado */
        color: #e0e0e0; /* Texto claro */
        border-bottom: 1px solid #444;
    }

    html[data-bs-theme="dark"] .form-control {
        background-color: #2c2c2c; /* Fondo oscuro para el campo de entrada */
        color: #e0e0e0; /* Texto claro */
        border: 1px solid #444;
    }

    html[data-bs-theme="dark"] .form-control::placeholder {
        color: #aaa; /* Color del texto del placeholder en modo oscuro */
    }

    html[data-bs-theme="dark"] .form-control:focus {
        border-color: #2575fc;
        box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
    }

    html[data-bs-theme="dark"] .btn-success {
        background-color: #198754;
        color: #fff;
    }

    html[data-bs-theme="dark"] .btn-success:hover {
        background-color: #157347;
    }

    html[data-bs-theme="dark"] .btn-danger {
        background-color: #dc3545;
        color: #fff;
    }

    html[data-bs-theme="dark"] .btn-danger:hover {
        background-color: #bb2d3b;
    }

    html[data-bs-theme="dark"] .note-editor {
        background-color: #2c2c2c; /* Fondo oscuro para el editor */
        color: #e0e0e0;
        border: 1px solid #444;
    }

    html[data-bs-theme="dark"] .note-editor .note-toolbar {
        background-color: #2c2c2c; /* Fondo oscuro para la barra de herramientas */
        border-bottom: 1px solid #444;
    }

    html[data-bs-theme="dark"] .note-editor .note-editable {
        background-color: #2c2c2c; /* Fondo oscuro para el área editable */
        color: #e0e0e0;
    }

    /* Sobrescribir clases problemáticas en modo oscuro */
    html[data-bs-theme="dark"] .bg-light {
        background-color: #1a1a1a !important; /* Fondo oscuro */
        color: #e0e0e0 !important; /* Texto claro */
    }

    html[data-bs-theme="dark"] .bg-white {
        background-color: #2c2c2c !important; /* Fondo oscuro */
        color: #e0e0e0 !important; /* Texto claro */
    }
  </style>
</body>

</html>