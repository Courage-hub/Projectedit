<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

<?php
  session_start();
  include("conexion.php");

  // Verificar si el usuario está logueado
  if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
    // Si no está logueado, redirigir al login
    header("Location: login.php");
    exit();
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $instruccion = mysqli_real_escape_string($conn, $_POST['instruccion']);
    $user_id = $_SESSION['id']; // ID del usuario actual
  
    $query = "INSERT INTO fpproject (instruccion, user_id) VALUES ('$instruccion', '$user_id')";
    if (mysqli_query($conn, $query)) {
      header("Location: index.php");
      exit();
    } else {
      echo "Error: " . mysqli_error($conn);
    }
  }
?>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow">
          <div class="card-header bg-white py-3">
            <h2 class="text-center fw-bold text-primary mb-0">Publish your instruction</h2>
          </div>
          <div class="card-body p-4">
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
              <div class="mb-4">
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
    $(document).ready(function() {
      $('#summernote').summernote({
        placeholder: 'Write your instruction here...',
        height: 300,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
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
    }
    body {
      font-family: 'Poppins', sans-serif;
    }
    
    .btn-success, .btn-danger {
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
    
    .card {
      border-radius: 10px;
      border: none;
    }
    
    .card-header {
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .note-editor {
      border-radius: 5px;
    }
  </style>
</body>
</html>