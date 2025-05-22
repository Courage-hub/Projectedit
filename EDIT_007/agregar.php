<?php
require_once("includes/functions.php");
require_once("includes/conexion_access.php");
require_once("includes/catch.php");
verificarLogin(); // Verifica si el usuario está logueado

// Obtener todos los usuarios para el selector de asignación
$conexion_access = obtenerConexionAccess();
if (!$conexion_access) {
  mostrarErrorConexion();
}
$usuarios = [];
$result_usuarios = odbc_exec($conexion_access, "SELECT id, nombre, apellido FROM usuarios WHERE aprobado = TRUE");
while ($row = odbc_fetch_array($result_usuarios)) {
  $usuarios[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar'])) {
  try {
    $titulo = str_replace("'", "''", $_POST['titulo']);
    $instruccion_html = $_POST['instruccion']; // HTML original del formulario

    // Permitir iframes y algunos tags básicos
    $allowed_tags = '<p><br><b><strong><i><em><ul><ol><li><a><img><iframe><h1><h2><h3><h4><h5><h6><span><div>';
    $instruccion_html_limpio = strip_tags($instruccion_html, $allowed_tags);

    // Corregir src de iframes sin protocolo
    $instruccion_html_limpio = preg_replace(
      '/(<iframe[^>]+src=[\'"])(\/\/)/i',
      '$1https://',
      $instruccion_html_limpio
    );

    $instruccion_texto_plano = str_replace("'", "''", strip_tags($instruccion_html)); // Solo texto plano para Access
    $user_id = intval($_SESSION['id']);

    // Permitir seleccionar varias personas (user_id será una lista separada por comas)
    $asignados = isset($_POST['asignados']) ? array_filter(array_map('intval', $_POST['asignados'])) : [];
    if (empty($asignados)) {
      throw new Exception("Debes seleccionar al menos una persona para asignar la tarea.");
    }
    $user_ids = implode(',', $asignados);

    // Guardar todos los IDs asignados en el campo user_id (debe ser tipo texto en Access)
    $query_access = "INSERT INTO fpproject (titulo, instruccion, user_id, [date]) 
                         VALUES ('$titulo', '$instruccion_texto_plano', '$user_ids', NOW())";

    if (!odbc_exec($conexion_access, $query_access)) {
      throw new Exception("Error en Access: " . odbc_errormsg($conexion_access));
    }

    // Obtener el ID recién insertado de forma confiable
    $result_id = odbc_exec($conexion_access, "SELECT @@IDENTITY AS new_id");
    $new_id = 0;
    if ($result_id && odbc_fetch_row($result_id)) {
      $new_id = odbc_result($result_id, 'new_id');
    }
    if (!$new_id) {
      throw new Exception("No se pudo obtener el ID del nuevo registro.");
    }

    // Crear carpetas solo si no existen
    $baseFolder = "datos";
    $editDataFolder = "$baseFolder/edit_data";
    $folderPath = "$editDataFolder/edit_$new_id";
    $filePath = "$folderPath/edit_$new_id.html";
    if (!file_exists($baseFolder))
      mkdir($baseFolder, 0777, true);
    if (!file_exists($editDataFolder))
      mkdir($editDataFolder, 0777, true);
    if (!file_exists($folderPath))
      mkdir($folderPath, 0777, true);

    // Contenido HTML basado en details.php (sin botones de editar/eliminar)
    $htmlContent = "<!DOCTYPE html>\n<html lang='en'>\n<head>\n    <title>Forvia - Details</title>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <link href='../../../../assets/css/bootstrap.min.css' rel='stylesheet'>\n    <link href='../../../../assets/css/all.min.css' rel='stylesheet'>\n    <style>\n        @font-face { font-family: 'Poppins'; src: url('../../../../assets/fonts/poppins/Poppins-Regular.woff2') format('woff2'), url('../../../../assets/fonts/poppins/Poppins-Regular.woff') format('woff'), url('../../../../assets/fonts/poppins/Poppins-Regular.ttf') format('truetype'); }\n        body { font-family: 'Poppins', sans-serif; background-color: #f5f9ff; color: #333; }\n        .container-main { padding: 1rem; max-width: 1000px; margin: 2rem auto; background-color: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }\n        .title { font-size: 1.8rem; font-weight: 600; color: #2575fc; margin-bottom: 1rem; }\n    </style>\n</head>\n<body class='bg-light'>\n    <div class='container-main'>\n        <h1 class='title'>" . htmlspecialchars($titulo) . "</h1>\n        <div class='instruction'>" . $instruccion_html_limpio . "</div>\n    </div>\n    <script src='../../../../assets/js/bootstrap.bundle.min.js'></script>\n    <script src='../../../../assets/js/darkmode.js'></script>\n    <script src='../../../../assets/js/fontawesome.min.js'></script>\n</body>\n</html>";

    // Guardar o sobrescribir el archivo HTML siempre
    file_put_contents($filePath, $htmlContent);

    // Redirigir al index.php después de guardar exitosamente
    header("Location: index.php?success=1");
    exit();
  } catch (Exception $e) {
    mostrarErrorPersonalizado("Error al guardar la instrucción: " . $e->getMessage());
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
  <link href="assets/css/all.min.css" rel="stylesheet"> <!-- SweetAlert2 local -->
  <link rel="stylesheet" href="assets/css/sweetalert2.min.css">


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
              <!-- Apartado Asignar con búsqueda y selección múltiple -->
              <div class="mb-4">
                <label class="form-label fw-bold">Assign to</label>
                <input type="text" id="asignados_search" class="form-control mb-2"
                  placeholder="Type to search a person..." autocomplete="off">
                <div id="asignados_checkboxes" class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                  <!-- Las casillas se generan por JS -->
                </div>
                <div id="vista-previa-asignados" class="mt-2"></div>
              </div>
              <!-- Campo para las instrucciones -->
              <div class="mb-4">
                <label for="instruccion" class="form-label fw-bold">Instructions</label>
                <textarea id="summernote" name="instruccion" class="form-control" required></textarea>
              </div>
              <!-- Botones -->
              <div class="d-flex justify-content-between mt-4">
                <a href="index.php" class="btn btn-danger px-4">
                  <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <button type="submit" name="enviar" class="btn btn-success px-4">
                  <i class="fas fa-save me-2"></i>Save
                </button>
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
        codeviewFilter: false,
        codeviewIframeFilter: false,
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

    // Generar checkboxes dinámicamente y filtrar por búsqueda
    const usuarios = <?= json_encode($usuarios) ?>;
    let seleccionados = [];

    function renderCheckboxes() {
      const search = document.getElementById('asignados_search').value.toLowerCase();
      const cont = document.getElementById('asignados_checkboxes');
      cont.innerHTML = '';
      // Si no hay búsqueda y no hay seleccionados, no mostrar nada
      if (search.length === 0 && seleccionados.length === 0) {
        cont.style.display = 'none';
        document.getElementById('vista-previa-asignados').innerHTML = '';
        return;
      }
      // Mostrar siempre las personas seleccionadas, aunque no coincidan con la búsqueda
      usuarios.forEach(u => {
        const nombreCompleto = (u.nombre + ' ' + u.apellido).toLowerCase();
        const id = 'asignado_' + u.id;
        const checked = seleccionados.includes(String(u.id)) ? 'checked' : '';
        // Mostrar si coincide con búsqueda o si está seleccionado
        if ((search.length > 0 && nombreCompleto.includes(search)) || checked) {
          cont.innerHTML += `
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="${u.id}" id="${id}" name="asignados[]" onchange="actualizarSeleccionados(this)" ${checked}>
              <label class="form-check-label" for="${id}">${u.nombre} ${u.apellido}</label>
            </div>
          `;
        }
      });
      // Si no hay nada que mostrar, ocultar el contenedor
      cont.style.display = cont.innerHTML.trim() === '' ? 'none' : 'block';
      actualizarVistaPrevia();
    }

    function actualizarSeleccionados(checkbox) {
      if (checkbox.checked) {
        if (!seleccionados.includes(checkbox.value)) {
          seleccionados.push(checkbox.value);
        }
      } else {
        seleccionados = seleccionados.filter(id => id !== checkbox.value);
      }
      renderCheckboxes(); // Redibujar para mantener solo seleccionados visibles si no hay búsqueda
      actualizarVistaPrevia();
    }

    function actualizarVistaPrevia() {
      const preview = document.getElementById('vista-previa-asignados');
      let html = '';
      if (seleccionados.length > 0) {
        html = '<div class="alert alert-info mb-0"><strong>Assigned to:</strong><ul class="mb-0">';
        usuarios.forEach(u => {
          if (seleccionados.includes(String(u.id))) {
            html += `<li>${u.nombre} ${u.apellido}</li>`;
          }
        });
        html += '</ul></div>';
      }
      preview.innerHTML = html;
    }

    document.getElementById('asignados_search').addEventListener('input', renderCheckboxes);

    document.querySelector('form').addEventListener('submit', function () {
      document.querySelectorAll('#asignados_checkboxes input[type=checkbox]').forEach(cb => {
        cb.checked = seleccionados.includes(cb.value);
      });
    });

    document.addEventListener('DOMContentLoaded', function () {
      seleccionados = [];
      renderCheckboxes();
    });
  </script>

  <script src="assets/js/sweetalert2.min.js"></script>
  <script>
    // Confirmación moderna para eliminar (en agregar, si aplica)
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
</body>

</html>