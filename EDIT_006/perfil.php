<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id"])) {
  die("Error: No has iniciado sesión.");
}

$id = intval($_SESSION["id"]);

// Obtener datos del usuario
$query = $conn->prepare("SELECT nombre, apellido, email, departamento, descripcion, foto FROM usuarios WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();

// Procesar cambios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $descripcion = isset($_POST['descripcion']) ? substr($_POST['descripcion'], 0, 150) : $user['descripcion'];
  $fotoNombre = $user['foto'];

  if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $fotoNombre = "perfil_" . $id . "_" . time() . ".jpg";
    move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $fotoNombre);
  }

  if (isset($_POST['foto_capturada']) && !empty($_POST['foto_capturada'])) {
    $fotoNombre = "uploads/perfil_" . $id . "_" . time() . ".png";
    file_put_contents($fotoNombre, base64_decode(explode(",", $_POST['foto_capturada'])[1]));
  }

  $updateQuery = $conn->prepare("UPDATE usuarios SET descripcion = ?, foto = ? WHERE id = ?");
  $updateQuery->bind_param("ssi", $descripcion, $fotoNombre, $id);
  $updateQuery->execute();
  $updateQuery->close();

  $user['descripcion'] = $descripcion;
  $user['foto'] = $fotoNombre;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>User Profile</title>
  <link rel="stylesheet" href="styles.css">
</head>
<style>
  .perfil-container {
    background: white;
    margin-top: 2rem;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 600px;
    text-align: center;
  }

  h2 {
    font-size: 2rem;
    color: #333;
  }

  .foto-perfil {
    width: 220px;
    height: 220px;
    margin-top: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #2575fc;
    margin-bottom: 2.5rem;
  }

  textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    resize: none;
    height: 9rem;
  }

  input[type="file"] {
    margin: 0.5rem 0;
  }

  button {
    width: 100%;
    padding: 0.75rem;
    background: #2575fc;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 1rem;
  }

  button:hover {
    background: #1a5bbf;
  }
  p{
    font-size: 1.2rem;
  }
  video {
    width: 100%;
    max-width: 400px;
    margin-top: 1rem;
    border-radius: 10px;
  }

  @font-face {
    font-family: 'Poppins';
    src: url('$ROHWJOX.woff2') format('woff2'),
      url('$ROHWJOX.woff2') format('woff');
    font-weight: normal;
    font-style: normal;
  }

  @font-face {
    font-family: 'Poppins';
    src: url('$ROHWJOX.woff2') format('woff2'),
      url('$ROHWJOX.woff2') format('woff');
    font-weight: bold;
    font-style: normal;
  }

  * {
    margin: 0;
    font-family: "Poppins", sans-serif;
    padding: 0;
    box-sizing: border-box;
  }

  #inicio {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    flex-direction: column;
  }

  body {
    font-family: "Poppins", sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    /* Mínimo alto de la ventana */
  }

  .navbar {
    width: 100%;
    box-shadow: 0 1px 4px rgb(146 161 176 / 15%);

  }

  .nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 62px;
  }

  .navbar .menu-items {
    display: flex;
  }

  .navbar .nav-container li {
    list-style: none;
  }

  .navbar .nav-container a {
    text-decoration: none;
    color: #310e29;
    font-weight: 500;
    font-size: 1.2rem;
    padding: 0.7rem;
  }

  .navbar .nav-container a:hover {
    font-weight: bolder;
  }

  .nav-container {
    display: block;
    position: relative;
    height: 60px;
  }

  .nav-container .checkbox {
    position: absolute;
    display: block;
    height: 32px;
    width: 32px;
    top: 20px;
    left: 20px;
    z-index: 5;
    opacity: 0;
    cursor: pointer;
  }

  .nav-container .hamburger-lines {
    display: block;
    height: 26px;
    width: 32px;
    position: absolute;
    top: 17px;
    left: 20px;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .nav-container .hamburger-lines .line {
    display: block;
    height: 4px;
    width: 100%;
    border-radius: 10px;
    background: #0e2431;
  }

  .nav-container .hamburger-lines .line1 {
    transform-origin: 0% 0%;
    transition: transform 0.4s ease-in-out;
  }

  .nav-container .hamburger-lines .line2 {
    transition: transform 0.2s ease-in-out;
  }

  .nav-container .hamburger-lines .line3 {
    transform-origin: 0% 100%;
    transition: transform 0.4s ease-in-out;
  }

  .navbar .menu-items {
    padding-top: 120px;
    background-color: white;
    height: 100vh;
    width: 100vh;
    transform: translate(-150%);
    display: flex;
    flex-direction: column;
    transition: transform 0.5s ease-in-out;
    text-align: center;
  }

  .navbar .menu-items li {
    margin-bottom: 1.2rem;
    font-size: 1.5rem;
    font-weight: 500;
  }

  .logo {
    position: absolute;
    top: 5px;
    right: 15px;
    font-size: 1.2rem;
    color: #0e2431;
  }

  .nav-container input[type="checkbox"]:checked~.menu-items {
    transform: translateX(0);
  }

  .nav-container input[type="checkbox"]:checked~.hamburger-lines .line1 {
    transform: rotate(45deg);
  }

  .nav-container input[type="checkbox"]:checked~.hamburger-lines .line2 {
    transform: scaleY(0);
  }

  .nav-container input[type="checkbox"]:checked~.hamburger-lines .line3 {
    transform: rotate(-45deg);
  }

  .nav-container input[type="checkbox"]:checked~.logo {
    display: none;
  }
</style>

<body>
  <nav>
    <div class="navbar">
      <div class="container nav-container">
        <input class="checkbox" type="checkbox" name="" id="" />
        <div class="hamburger-lines">
          <span class="line line1"></span>
          <span class="line line2"></span>
          <span class="line line3"></span>
        </div>
        <div class="logo">
          <h1>Forvia</h1>
        </div>
        <div class="menu-items">
          <li><a href="index.php">Home</a></li>
          <?php
          // Mostrar el enlace "Admin" solo si el usuario es administrador
          if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
            echo '<li><a href="admin.php">Admin</a></li>';
          }
          ?>
          <li><a href="perfil.php">Profile</a></li>
        </div>
      </div>
    </div>
  </nav>
  <div id="inicio">
    <div class="perfil-container">
      <h2>Profile of <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></h2>
      <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto de perfil" class="foto-perfil"
        id="foto_perfil_preview">
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Department:</strong> <?php echo htmlspecialchars($user['departamento']); ?></p>

      <form method="POST" enctype="multipart/form-data">
        <br>
        <label for="descripcion">Description:</label>
        <textarea name="descripcion" id="descripcion"
          maxlength="150"><?php echo htmlspecialchars($user['descripcion']); ?></textarea>
        <label for="foto">Upload photo:</label>
        <input type="file" name="foto" accept="image/*">

        <button type="button" onclick="abrirCamara()">Take Photo</button>
        <div id="camara" style="display: none;">
          <video id="video" autoplay></video>
          <button type="button" onclick="capturarFoto()">Capture</button>
          <canvas id="canvas" style="display: none;"></canvas>
          <input type="hidden" name="foto_capturada" id="foto_capturada">
        </div>

        <button type="submit">Save All Changes</button>
      </form>
    </div>

    <script>
      function abrirCamara() {
        document.getElementById('camara').style.display = 'block';
        navigator.mediaDevices.getUserMedia({ video: true })
          .then(stream => {
            document.getElementById('video').srcObject = stream;
          })
          .catch(err => console.log("Error al acceder a la cámara: ", err));
      }

      function capturarFoto() {
        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        let fotoBase64 = canvas.toDataURL('image/png');
        document.getElementById('foto_capturada').value = fotoBase64;
      }
    </script>
</body>

</html>