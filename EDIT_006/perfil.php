<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id"])) {
  die("Error: No has iniciado sesión.");
}
// Cerrar sesión
if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: esvlc.edit.home.html");
  exit();
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
    $fotoNombre = "uploads/perfil_" . $id . "_" . time() . ".jpg";
    move_uploaded_file($_FILES['foto']['tmp_name'], $fotoNombre);
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
<html lang="en">

<head>
  <title>Forvia - Profile</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap local -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome local -->
  <link href="assets/css/fontawesome.min.css" rel="stylesheet">
  <link href="assets/css/solid.min.css" rel="stylesheet">
  <!-- Fuente Poppins local -->
  <style>
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
      --danger-color: #dc3545;
      --success-color: #28a745;
      --dark-color: #343a40;
      --light-color: #f8f9fa;
      --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f9ff;
      color: #333;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .navbar {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 0.8rem 1rem;
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary-color) !important;
    }

    .nav-link {
      color: var(--dark-color) !important;
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }

    .nav-link:hover {
      color: var(--primary-color) !important;
    }

    .container-main {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-grow: 1;
      padding: 2rem;
    }

    .profile-card {
      background-color: white;
      border-radius: 15px;
      box-shadow: var(--card-shadow);
      width: 100%;
      max-width: 900px;
      display: flex;
      overflow: hidden;
    }

    .profile-content {
      flex: 1;
      padding: 2.5rem;
      display: flex;
      flex-direction: column;
    }

    .profile-image-container {
      width: 300px;
      background-color: #f8f9fa;
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
      align-items: center;
      padding: 2rem;
      border-left: 1px solid #eee;
    }

    .profile-title {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
    }

    .profile-img {
      width: 220px;
      height: 220px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid white;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
    }

    .profile-info {
      margin-bottom: 1.5rem;
    }

    .profile-info p {
      font-size: 1.1rem;
      margin-bottom: 0.8rem;
      display: flex;
    }

    .profile-info strong {
      color: var(--dark-color);
      min-width: 120px;
      display: inline-block;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }

    .btn-primary-custom {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 8px;
      padding: 0.6rem 1.2rem;
      width: auto;
      font-weight: 500;
      width: auto;
      transition: all 0.3s ease;
      color: white;
    }

    .btn-primary-custom:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
      color: white;
    }

    .btn-outline-primary-custom {
      border-color: var(--primary-color);
      color: var(--primary-color);
      border-radius: 8px;
      padding: 0.6rem 1.2rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-outline-primary-custom:hover {
      background-color: var(--primary-color);
      color: white;
    }

    #camara {
      display: none;
      margin-top: 1rem;
      text-align: center;
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 8px;
    }

    #video {
      width: 100%;
      max-width: 400px;
      border-radius: 8px;
      margin-bottom: 1rem;
      background: #000;
    }

    .camera-buttons {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .file-upload {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }

    .file-upload-btn {
      width: 100%;
      padding: 0.6rem 1.2rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
    }

    .file-upload input[type="file"] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .profile-card {
        flex-direction: column-reverse;
      }

      .profile-image-container {
        width: 100%;
        border-left: none;
        border-top: 1px solid #eee;
        padding: 1.5rem;
      }

      .profile-content {
        padding: 1.5rem;
      }

      .profile-img {
        width: 150px;
        height: 150px;
      }
    }

    /* Dark mode specific styles */
    html[data-bs-theme="dark"] body {
      background-color: #1a1a1a;
      color: #e0e0e0;
    }

    html[data-bs-theme="dark"] .navbar {
      background-color: #2c2c2c !important;
      box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
    }

    html[data-bs-theme="dark"] .user-greeting,
    html[data-bs-theme="dark"] .search-form,
    html[data-bs-theme="dark"] .table-container,
    html[data-bs-theme="dark"] .profile-content,
    html[data-bs-theme="dark"] .profile-image-container{
      background-color: #2c2c2c;
      color: #e0e0e0;
    }

    html[data-bs-theme="dark"] .table thead th {
      background-color: #1a5bbf;
      color: white;
    }

    html[data-bs-theme="dark"] .table tbody tr:hover {
      background-color: rgba(37, 117, 252, 0.2);
    }

    html[data-bs-theme="dark"] .form-control {
      background-color: #3a3a3a;
      color: #e0e0e0;
      border-color: #4a4a4a;
    }

    html[data-bs-theme="dark"] .form-control:focus,
    html[data-bs-theme="dark"] #camara{
      background-color: #4a4a4a;
      color: #e0e0e0;
    }
  </style>
</head>

<body>
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">Forvia</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="perfil.php">Profile</a>
          </li>
          <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin.php">Administration</a>
            </li>
          <?php endif; ?>
        </ul>
        <form method="POST" class="d-flex">
          <!-- Dark Mode Toggle -->
          <button id="darkModeToggle" class="btn btn-outline-secondary me-1">
            <i class="fas fa-moon"></i>
          </button>
          <button type="submit" name="logout" class="btn btn-outline-danger">
            <i class="fas fa-sign-out-alt me-1"></i> Log Out
          </button>
        </form>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container-main">
    <div class="profile-card">
      <div class="profile-content">
        <h1 class="profile-title">My Profile</h1>

        <div class="profile-info">
          <p><strong>Name:</strong> <?php echo htmlspecialchars($user['nombre']); ?></p>
          <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['apellido']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
          <p><strong>Department:</strong> <?php echo htmlspecialchars($user['departamento']); ?></p>
        </div>

        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="descripcion" class="form-label">Description</label>
            <textarea class="form-control" name="descripcion" id="descripcion"
              maxlength="150"><?php echo htmlspecialchars($user['descripcion']); ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Change Profile Picture</label>
            <div class="file-upload">
              <button type="button" class="btn btn-outline-primary-custom file-upload-btn">
                <i class="fas fa-upload me-2"></i>Select File
              </button>
              <input type="file" name="foto" accept="image/*" onchange="previewImage(this)">
            </div>
          </div>

          <button type="button" class="btn btn-outline-primary-custom" onclick="abrirCamara()">
            <i class="fas fa-camera me-2"></i>Take Photo with Camera
          </button>

          <div id="camara">
            <video id="video" autoplay></video>
            <div class="camera-buttons">
              <button type="button" class="btn btn-primary-custom" onclick="capturarFoto()">
                <i class="fas fa-camera me-2"></i>Capture
              </button>
              <button type="button" class="btn btn-outline-danger" onclick="cerrarCamara()">
                <i class="fas fa-times me-2"></i>Cancel
              </button>
            </div>
            <canvas id="canvas" style="display: none;"></canvas>
            <input type="hidden" name="foto_capturada" id="foto_capturada">
          </div>

          <button type="submit" class="btn btn-primary-custom">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
        </form>
      </div>

      <div class="profile-image-container">
        <img src="<?php echo htmlspecialchars($user['foto'] ?: 'assets/img/default-profile.jpg'); ?>"
          alt="Profile Picture" class="profile-img" id="foto_perfil_preview">
        <h4><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></h4>
        <p class="text-muted"><?php echo htmlspecialchars($user['departamento']); ?></p>
      </div>
    </div>
  </div>

  <!-- Local Scripts -->
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/darkmode.js"></script>
  <script src="assets/js/fontawesome.min.js"></script>
  <script>
    let mediaStream = null;

    function abrirCamara() {
      document.getElementById('camara').style.display = 'block';
      navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
          mediaStream = stream;
          document.getElementById('video').srcObject = stream;
        })
        .catch(err => {
          console.error("Error al acceder a la cámara: ", err);
          alert('No se pudo acceder a la cámara. Asegúrate de permitir el acceso.');
        });
    }

    function cerrarCamara() {
      if (mediaStream) {
        mediaStream.getTracks().forEach(track => track.stop());
        mediaStream = null;
      }
      document.getElementById('camara').style.display = 'none';
    }

    function capturarFoto() {
      const video = document.getElementById('video');
      const canvas = document.getElementById('canvas');
      const context = canvas.getContext('2d');

      // Detener la transmisión de video
      if (mediaStream) {
        mediaStream.getTracks().forEach(track => track.stop());
        mediaStream = null;
      }

      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      const fotoBase64 = canvas.toDataURL('image/png');
      document.getElementById('foto_capturada').value = fotoBase64;
      document.getElementById('foto_perfil_preview').src = fotoBase64;

      document.getElementById('camara').style.display = 'none';
    }

    // Mostrar vista previa al seleccionar archivo
    function previewImage(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (event) {
          document.getElementById('foto_perfil_preview').src = event.target.result;
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Asignar el evento click al botón de subir archivo
    document.querySelector('.file-upload-btn').addEventListener('click', function () {
      this.nextElementSibling.click();
    });
  </script>
</body>

</html>