<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="summernote-0.8.18-dist/bootstrap.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/jquery-3.5.1.min.js"></script>
  <script src="summernote-0.8.18-dist/bootstrap.min.js"></script>

  <link href="summernote-0.8.18-dist/summernote.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/summernote.min.js"></script>
  <title>Forvia - Add</title>
</head>

<body>
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

  <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <br><br>
    <center>
      <h1>Publish your instruction<h1>
    </center> <br><br>

    <textarea id="summernote" name="instruccion"></textarea>
    <br>
    <button type="submit" class="success" name="enviar">✔ Submit</button>
  </form>
  <a href="index.php"><button class="danger">← Back</button></a>
  <script>
    $(document).ready(function() {
      $('#summernote').summernote();
    });
  </script>

</body>
<style>
  @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

  h1 {
    font-family: "Poppins", sans-serif;
  }

  .success {
    float: left;
    font-family: "Poppins", sans-serif;
    border: none;
    color: white;
    width: 125px;
    text-align: center;
    padding: 14px 28px;
    cursor: pointer;
    border-radius: 5px;
    display: inline-flex;
  }

  .success {
    background-color: #04AA6D;
  }

  .success:hover {
    background-color: #46a049;
  }

  .danger {
    float: right;
    font-family: "Poppins", sans-serif;
    border: none;
    color: white;
    padding: 14px 28px;
    cursor: pointer;
    border-radius: 5px;
    display: inline-flex;
  }

  .danger {
    background-color: #f44336;
  }

  .danger:hover {
    background: #da190b;
  }
</style>

</html>
