<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
    <link href="summernote-0.8.18-dist/bootstrap.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/jquery-3.5.1.min.js"></script>
  <script src="summernote-0.8.18-dist/bootstrap.min.js"></script>

  <link href="summernote-0.8.18-dist/summernote.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/summernote.min.js"></script>
</head>

<body>

</body>

</html>


<?php
include 'conexion.php';

if (isset($_POST['editar'])) {
    $id = $_POST['id'];
    $titulo = $_POST['instruccion'];

    $sql = "UPDATE fpproject SET instruccion='$titulo' WHERE id=$id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        echo "<script>alert('The data was updated correctly');
        location.assign ('index.php');
        </script>";
    } else {
        echo "<script>alert('ERROR: The data could not be updated.');</script>";
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM fpproject WHERE id=$id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
}
?>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    <br><br>
    <center>
        <h1>Edit your instructions</hi> <br><br>
    </center>
    <textarea name="instruccion" id="summernote" class="input"><?= $row['instruccion'] ?></textarea>
    <script>
              $(document).ready(function() {
        $('#summernote').summernote();
      });
    </script>
    <br>
    <button type="submit" class="success" name="editar">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="update" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
        </svg><span class="espacio">-</span>Update
    </button>
</form>
<a href="index.php"><button class="danger">← Back</button></a>


<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");
     .espacio{
        color: #04AA6D;
     }
    h1 {
        font-family: "Poppins", sans-serif;
    }
    .success {
        float: left;
        font-family: "Poppins", sans-serif;
        border: none;
        color: white;
        width: 130px;
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