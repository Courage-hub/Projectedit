<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.1/dist/quill.snow.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.1/dist/quill.js"></script>
  <meta name="viewport" content="width=, initial-scale=1.0">
  <title> - Add</title>
</head>

<body>
  <?php
  if (isset($_POST['enviar'])) {
    $nombre = $_POST['usuario'];
    $titulo = $_POST['instruccion'];
    $sql = "insert into fpproject(usuario, instruccion) values ('" . $nombre . "', '" . $titulo . "')";
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
  } else {

  ?>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
      <div class="">Welcome</div>
      <div class="">Create your publication</div>
      <label>Name</label>
      <input name="nombre" class="input" type="text" placeholder=" " />
      </div>
      <div class="">
        <label name="titulo" class="" type="text">Title</label>
        <div id="editor">

        </div>
    </form>
    </div>
  <?php
  }
  ?>
  <button onclick="guardarContenido()" name="enviar">Guardar</button>
  <a href="index.php">
    <button class="submit2" class="submit"> Back </button>
  </a>
</body>


<script>
  // Inicializar Quill editor
  const quill = new Quill('#editor', {
    theme: 'snow'
  });

  // Función para guardar el contenido del editor
  function guardarContenido() {
    // Obtener el contenido del editor
    var contenido = quill.root.innerHTML;

    // Enviar el contenido al servidor PHP
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        alert(this.responseText);
      }
    };
    xhttp.open("POST", "conexion2.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("instruccion=" + encodeURIComponent(contenido));
  }
</script>

</html>