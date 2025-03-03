<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=, initial-scale=1.0">
  <title>Foro - Add</title>
</head>
<style>
  @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

  body {
    align-items: center;
    background-color: #ffffff;
    display: flex;
    justify-content: center;
    height: 90vh;
    font-family: "Poppins", sans-serif;
  }

  .form {
    background-color: #15172b;
    border-radius: 20px;
    box-sizing: border-box;
    height: 500px;
    padding: 20px;
    width: 320px;
  }

  .title {
    color: #eee;
    font-size: 36px;
    font-weight: 600;
    margin-top: 30px;
  }

  .subtitle {
    color: #eee;
    font-size: 16px;
    font-weight: 600;
    margin-top: 10px;
  }

  .input-container {
    height: 50px;
    position: relative;
    width: 100%;
  }

  .ic1 {
    margin-top: 40px;
  }

  .ic2 {
    margin-top: 30px;
  }

  .input {
    background-color: #303245;
    border-radius: 12px;
    border: 0;
    box-sizing: border-box;
    color: #eee;
    font-size: 18px;
    height: 100%;
    outline: 0;
    padding: 4px 20px 0;
    width: 100%;
  }

  .cut {
    background-color: #15172b;
    border-radius: 10px;
    height: 20px;
    left: 20px;
    position: absolute;
    top: -20px;
    transform: translateY(0);
    transition: transform 200ms;
    width: 76px;
  }

  .cut-short {
    width: 50px;
  }

  .input:focus~.cut,
  .input:not(:placeholder-shown)~.cut {
    transform: translateY(8px);
  }

  .placeholder {
    color: #65657b;
    left: 20px;
    height: 30px;
    pointer-events: none;
    position: absolute;
    transform-origin: 0 50%;
    transition: transform 200ms, color 200ms;
    top: 20px;
  }

  .input:focus~.placeholder,
  .input:not(:placeholder-shown)~.placeholder {
    transform: translateY(-30px) translateX(10px) scale(0.75);
  }

  .input:not(:placeholder-shown)~.placeholder {
    color: #808097;
  }

  .input:focus~.placeholder {
    color: #dc2f55;
  }

  .submit {
    background-color: #08d;
    border-radius: 12px;
    border: 0;
    box-sizing: border-box;
    color: #eee;
    cursor: pointer;
    font-size: 18px;
    height: 50px;
    margin-top: 38px;
    outline: 0;
    text-align: center;
    width: 100%;
  }

  .submit2 {
    background-color: red;
    border-radius: 12px;
    border: 0;
    box-sizing: border-box;
    color: #eee;
    cursor: pointer;
    font-size: 18px;
    height: 50px;
    margin-top: 38px;
    outline: 0;
    text-align: center;
    width: 100%;
  }

  .submit:active {
    background-color: #06b;
  }
</style>

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
    <div class="form">
      <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <div class="title">Welcome</div>
        <div class="subtitle">Create your publication</div>
        <div class="input-container ic1">
          <input name="nombre" class="input" type="text" placeholder=" " />
          <div class="cut"></div>
          <label for="firstname" class="placeholder">Name</label>
        </div>
        <div class="input-container ic2">
          <input name="titulo" class="input" type="text" placeholder=" " />
          <div class="cut"></div>
          <label name="titulo" class="placeholder" type="text">Title</label>
        </div>
        <button type="submit" class="submit" name="enviar">Submit</button>

        <a href="html/">
          <button class="submit2" class="submit"> Back </button>
        </a>
      </form>
    </div>
  <?php
  }
  ?>

</body>

</html>