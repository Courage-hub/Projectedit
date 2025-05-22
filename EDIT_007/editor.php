<?php
require_once("includes/functions.php");
verificarLogin(); // Verifica si el usuario está logueado
require_once("includes/conexion_access.php"); // Conexión Access

if (isset($_POST['editar'])) {
  $conexion_access = obtenerConexionAccess();
  $id = intval($_POST['id']);
  $titulo = str_replace("'", "''", $_POST['titulo']);
  $instruccion_html = $_POST['instruccion']; // HTML para la web
  $instruccion_texto_plano = str_replace("'", "''", strip_tags($instruccion_html)); // Solo texto plano para Access
  $user_id = intval($_SESSION['id']);

  // Verificar si el usuario actual es el creador del registro en Access
  $check_query_access = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
  $check_result_access = odbc_exec($conexion_access, $check_query_access);

  if (odbc_fetch_row($check_result_access)) {
    // Actualizar el registro en Access
    $sql_access = "UPDATE fpproject SET titulo='$titulo', instruccion='$instruccion_texto_plano' WHERE id=$id";
    $result_access = odbc_exec($conexion_access, $sql_access);

    // Regenerar el archivo HTML estático si la actualización fue exitosa
    if ($result_access) {
      // Obtener los datos actualizados
      $titulo_html = $titulo;
      $baseFolder = "datos";
      $editDataFolder = "$baseFolder/edit_data";
      $folderPath = "$editDataFolder/edit_$id";
      $filePath = "$folderPath/edit_$id.html";
      $contentFile = "$folderPath/content.html";

      if (!file_exists($baseFolder))
        mkdir($baseFolder, 0777, true);
      if (!file_exists($editDataFolder))
        mkdir($editDataFolder, 0777, true);
      if (!file_exists($folderPath))
        mkdir($folderPath, 0777, true);

      // Guardar el archivo HTML para visualización
      $htmlContent = "<!DOCTYPE html>\n<html lang='en'>\n<head>\n    <title>Forvia - Details</title>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <link href='../../../../assets/css/bootstrap.min.css' rel='stylesheet'>\n    <link href='../../../../assets/css/all.min.css' rel='stylesheet'>\n    <style>\n        @font-face { font-family: 'Poppins'; src: url('../../../../assets/fonts/poppins/Poppins-Regular.woff2') format('woff2'), url('../../../../assets/fonts/poppins/Poppins-Regular.woff') format('woff'), url('../../../../assets/fonts/poppins/Poppins-Regular.ttf') format('truetype'); }\n        body { font-family: 'Poppins', sans-serif; background-color: #f5f9ff; color: #333; }\n        .container-main { padding: 1rem; max-width: 1000px; margin: 2rem auto; background-color: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }\n        .title { font-size: 1.8rem; font-weight: 600; color: #2575fc; margin-bottom: 1rem; }\n    </style>\n</head>\n<body class='bg-light'>\n    <div class='container-main'>\n        <h1 class='title'>" . htmlspecialchars($titulo_html) . "</h1>\n        <div class='instruction'>" . htmlspecialchars_decode($instruccion_html) . "</div>\n    </div>\n    <script src='../../../../assets/js/bootstrap.bundle.min.js'></script>\n    <script src='../../../../assets/js/darkmode.js'></script>\n    <script src='../../../../assets/js/fontawesome.min.js'></script>\n</body>\n</html>";

      file_put_contents($filePath, $htmlContent);
      // Guardar el contenido HTML original en content.html
      file_put_contents($contentFile, $instruccion_html);

      echo "<script>alert('The data was updated correctly.');location.assign('index.php');</script>";
    } else {
      echo "<script>alert('ERROR: The data could not be updated.');</script>";
    }
  } else {
    echo "<script>alert('You do not have permission to edit this record.');
        location.assign('index.php');
        </script>";
  }

  // Liberar recursos
  odbc_free_result($check_result_access);
  // No cierres la conexión manualmente
}

if (isset($_GET['id'])) {
  $conexion_access = obtenerConexionAccess();
  $id = intval($_GET['id']);
  $user_id = intval($_SESSION['id']);

  // Verificar si el usuario actual es el creador del registro
  $sql = "SELECT * FROM fpproject WHERE id = $id AND user_id = $user_id";
  $result = odbc_exec($conexion_access, $sql);

  if (odbc_fetch_row($result)) {
    $row = [
      'id' => odbc_result($result, "id"),
      'titulo' => odbc_result($result, "titulo"),
      'instruccion' => odbc_result($result, "instruccion")
    ];

    // Leer el contenido HTML original del archivo edit_$id.html (extraer solo la instrucción)
    $editHtmlFile = __DIR__ . "/datos/edit_data/edit_{$id}/edit_{$id}.html";
    if (file_exists($editHtmlFile)) {
      $html = file_get_contents($editHtmlFile);
      // Extraer el contenido entre <div class='instruction'> y </div>
      if (preg_match("~<div class='instruction'>(.*?)</div>~s", $html, $matches)) {
        $row['instruccion'] = $matches[1];
      }
    }
  } else {
    echo "<script>alert('You do not have permission to access this record.');
        location.assign('index.php');
        </script>";
    exit();
  }

  // Cerrar el recurso de consulta
  odbc_free_result($result);

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forvia - Edit</title>

  <!-- Bootstrap CSS -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="assets/css/all.min.css" rel="stylesheet">
  <!-- SweetAlert2 local -->
  <link rel="stylesheet" href="assets/css/sweetalert2.min.css">

  <!-- Summernote resources -->
  <link href="summernote-0.8.18-dist/bootstrap.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/jquery-3.5.1.min.js"></script>
  <script src="summernote-0.8.18-dist/bootstrap.min.js"></script>
  <link href="summernote-0.8.18-dist/summernote.min.css" rel="stylesheet">
  <script src="summernote-0.8.18-dist/summernote.min.js"></script>
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

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light-color);
      color: #333;
    }

    html[data-bs-theme="dark"] body,
    html[data-bs-theme="dark"] .bg-light {
      background-color: #1a1a1a !important;
      color: #e0e0e0 !important;
    }

    html[data-bs-theme="dark"] .card,
    html[data-bs-theme="dark"] .card-header,
    html[data-bs-theme="dark"] .card-body {
      background-color: #2c2c2c !important;
      color: #e0e0e0 !important;
      border-color: #444 !important;
    }

    html[data-bs-theme="dark"] .form-control {
      background-color: #2c2c2c !important;
      color: #e0e0e0 !important;
      border: 1px solid #444 !important;
    }

    html[data-bs-theme="dark"] .form-control:focus {
      border-color: #2575fc !important;
      background-color: #2c2c2c !important;
      color: #e0e0e0 !important;
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
      background-color: #2c2c2c !important;
      color: #e0e0e0 !important;
      border: 1px solid #444 !important;
    }

    html[data-bs-theme="dark"] .note-toolbar {
      background-color: #2c2c2c !important;
      border-bottom: 1px solid #444 !important;
    }

    html[data-bs-theme="dark"] .note-editable {
      background-color: #2c2c2c !important;
      color: #e0e0e0 !important;
    }

    /* Botones estilos claros */
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
  </style>
  <script>
    // Forzar el modo dark si está en localStorage
    document.addEventListener('DOMContentLoaded', function () {
      const html = document.documentElement;
      if (localStorage.getItem('theme') === 'dark') {
        html.setAttribute('data-bs-theme', 'dark');
      }
    });
  </script>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow">
          <div class="card-header bg-white py-3">
            <h2 class="text-center fw-bold text-primary mb-0">Edit your instructions</h2>
          </div>
          <div class="card-body p-4">
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">

              <!-- Campo para el título -->
              <div class="mb-4">
                <label for="titulo" class="form-label fw-bold">Title</label>
                <input type="text" id="titulo" name="titulo" class="form-control"
                  value="<?= htmlspecialchars($row['titulo']) ?>" required>
              </div>

              <!-- Campo para las instrucciones -->
              <div class="mb-4">
                <label for="summernote" class="form-label fw-bold">Instructions</label>
                <textarea name="instruccion" id="summernote" class="input"></textarea>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <a href="index.php" class="btn btn-danger px-4 py-2">
                  <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <button type="submit" class="btn btn-success px-4 py-2" name="editar">
                  <i class="fas fa-sync-alt me-2"></i>Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/sweetalert2.min.js"></script>
  <script>
    $(document).ready(function () {
      // Función para limpiar el HTML antes de cargarlo
      function prepareHtmlForEditor(html) {
        // Asegurarse de que los saltos de línea se mantienen
        return html.replace(/\\n/g, '')
          .replace(/\\"/g, '"')
          .replace(/\\/g, '');
      }

      // Inicializar Summernote con configuración básica
      $('#summernote').summernote({
        placeholder: 'Edit your instruction here...',
        height: 300,
      });

      // Cargar el contenido HTML en el editor
      <?php if (isset($row['instruccion'])): ?>
        var htmlContent = prepareHtmlForEditor(<?= json_encode($row['instruccion']) ?>);
        $('#summernote').summernote('code', htmlContent);
        console.log('HTML cargado:', htmlContent); // Para depuración
      <?php endif; ?>

      // Confirmación moderna para eliminar (en editor, si aplica)
      document.querySelectorAll('.action-link.delete').forEach(link => {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          Swal.fire({
            title: 'Are you sure?',
            text: 'Are you sure you want to delete this record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
              popup: 'swal2-popup',
              confirmButton: 'swal2-confirm',
              cancelButton: 'swal2-cancel'
            }
          }).then((result) => {
            if (result.isConfirmed) {
              window.location = link.href;
            }
          });
        });
      });
    });
  </script>
</body>

</html>