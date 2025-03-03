<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forvia - Add</title>
  <script src="https://cdn.ckeditor.com/4.16.0/full-all/ckeditor.js"></script>
</head>

<body>
  <?php
  if (isset($_POST['enviar'])) {
    $titulo = $_POST['instruccion'];
    $sql = "insert into fpproject (instruccion) values ('" . $titulo . "')";
    include 'conexion.php';
    $result = mysqli_query($conn, $sql);

    if ($result) {
      echo " <script language='JavaScript'>
                        alert ('The data were correctly entered into the database');
                        location.assign ('index.php');
                        </script>";
    } else {
      echo " <script language='JavaScript'>
                alert ('ERROR: The data were not correctly entered into the database.');
                location.assign ('index.php');
                </script>";
    }
    mysqli_close($conn);
  }
  ?>

  <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
  <br><br>
    <center><h1>Publish your instruction<h1></center> <br><br>
    <textarea name="instruccion" class="input" ></textarea>
    <br>
  <button type="submit" class="success" name="enviar">✔ Submit</button>
  </form>
  <a href="index.php"><button class="danger">← Back</button></a>
  <script>
    CKEDITOR.replace('instruccion');
  </script>
  
</body>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

h1{
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
.success {background-color: #04AA6D;} 
.success:hover {background-color: #46a049;}

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
.danger {background-color: #f44336;} 
.danger:hover {background: #da190b;}

</style>
</html>
