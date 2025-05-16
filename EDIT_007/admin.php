<?php
session_start();

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Buffer de salida para evitar contenido no deseado
ob_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
  if (isset($_POST['ajax_update']) || isset($_POST['ajax_delete'])) {
    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
  }
  header("Location: index.php");
  exit();
}

if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: esvlc.edit.home.html");
  exit();
}

require_once("includes/conexion_access.php"); // Cambiar include por require_once

// Obtener la conexión
$conexion_access = obtenerConexionAccess();
if (!$conexion_access) {
    die("❌ Error de conexión a la base de datos: " . odbc_errormsg());
}

// Verificar consultas
$query_pendientes = "SELECT * FROM usuarios WHERE aprobado = FALSE";
$result_pendientes = odbc_exec($conexion_access, $query_pendientes);
if (!$result_pendientes) {
    die("❌ Error al ejecutar la consulta de pendientes: " . odbc_errormsg($conexion_access));
}

$query_usuarios = "SELECT * FROM usuarios";
$result_usuarios = odbc_exec($conexion_access, $query_usuarios);
if (!$result_usuarios) {
    die("❌ Error al ejecutar la consulta de usuarios: " . odbc_errormsg($conexion_access));
}

// Función segura para respuestas JSON
function sendJsonResponse($success, $message = '')
{
  while (ob_get_level())
    ob_end_clean();
  header('Content-Type: application/json');
  http_response_code($success ? 200 : 400);
  exit(json_encode(['success' => $success, 'message' => $message]));
}

// Procesar solicitudes AJAX primero
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Manejar actualización de usuario
  if (isset($_POST['ajax_update'])) {
    try {
      if (!isset($_POST['id'], $_POST['field'], $_POST['value'])) {
        sendJsonResponse(false, 'Datos incompletos');
      }

      $id = intval($_POST['id']);
      $field = preg_replace('/[^a-z_]/i', '', $_POST['field']);
      $value = str_replace("'", "''", $_POST['value']);

      $allowedFields = ['nombre', 'apellido', 'departamento', 'email', 'rol'];
      if (!in_array($field, $allowedFields)) {
        sendJsonResponse(false, 'Campo no permitido');
      }

      $query = "UPDATE usuarios SET $field = '$value' WHERE id = $id";
      $result = odbc_exec($conexion_access, $query);
      if ($result) {
        sendJsonResponse(true);
      } else {
        sendJsonResponse(false, 'Error en la base de datos: ' . odbc_errormsg($conexion_access));
      }
    } catch (Exception $e) {
      sendJsonResponse(false, 'Error inesperado: ' . $e->getMessage());
    }
  }

  // Manejar eliminación de usuario
  if (isset($_POST['ajax_delete'])) {
    try {
      if (!isset($_POST['id'])) {
        sendJsonResponse(false, 'ID no proporcionado');
      }

      $id = intval($_POST['id']);

      if (isset($_SESSION['id_usuario']) && $id == $_SESSION['id_usuario']) {
        sendJsonResponse(false, 'No puedes eliminarte a ti mismo');
      }

      $query = "DELETE FROM usuarios WHERE id = $id";
      $result = odbc_exec($conexion_access, $query);
      if ($result) {
        sendJsonResponse(true);
      } else {
        sendJsonResponse(false, 'Error al eliminar: ' . odbc_errormsg($conexion_access));
      }
    } catch (Exception $e) {
      sendJsonResponse(false, 'Error inesperado: ' . $e->getMessage());
    }
  }

  // Manejar aprobación/denegación de registros
  if (isset($_POST['id'], $_POST['accion'])) {
    try {
      $id = intval($_POST['id']);
      $accion = $_POST['accion'];

      // Desactivar warnings temporalmente
      $error_reporting = error_reporting(0);
      
      if ($accion == 'aprobar') {
        $query = "UPDATE usuarios SET aprobado = TRUE WHERE id = $id";
        $result = @odbc_exec($conexion_access, $query);
        if ($result) {
          $success = "Usuario aprobado correctamente.";
        }
      } elseif ($accion == 'denegar') {
        $query = "DELETE FROM usuarios WHERE id = $id";
        $result = @odbc_exec($conexion_access, $query);
        if ($result) {
          $success = "Usuario denegado correctamente.";
        }
      }
      
      // Restaurar error reporting
      error_reporting($error_reporting);
      
      if (!$result) {
        throw new Exception("Error al procesar la solicitud");
      }
      
    } catch (Exception $e) {
      $error = "Error: " . $e->getMessage();
    }
  }
}

// Limpiar buffer antes de HTML
if (ob_get_length()) {
    ob_end_clean();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Forvia - Administration</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome local -->
  <link href="assets/css/fontawesome.min.css" rel="stylesheet">
  <link href="assets/css/solid.min.css" rel="stylesheet">

  <!-- SweetAlert2 local -->
  <link rel="stylesheet" href="assets/css/sweetalert2.min.css">

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
      --danger-color: #dc3545;
      --success-color: #28a745;
      --dark-color: #343a40;
      --light-color: #f8f9fa;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f9ff;
      color: #333;
    }

    /* Animación de entrada para el body (fade + scale + slide up) */
    .body-animate {
      animation: fadeInScale 0.7s cubic-bezier(.4,0,.2,1);
    }
    @keyframes fadeInScale {
      from {
        opacity: 0;
        transform: scale(0.97) translateY(40px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    /* --- NAVBAR UNIFICADA --- */
    .navbar {
      background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      box-shadow: 0 4px 18px rgba(37, 117, 252, 0.10);
      padding: 1rem 2rem;
      border-radius: 0 0 18px 18px;
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.7rem;
      color: #fff !important;
      letter-spacing: 1px;
      text-shadow: 0 2px 8px rgba(37,117,252,0.15);
    }
    .nav-link {
      color: #e0e0e0 !important;
      font-weight: 500;
      padding: 0.5rem 1.2rem;
      border-radius: 8px;
      margin-right: 0.3rem;
      transition: background 0.2s, color 0.2s;
    }
    .nav-link.active, .nav-link:hover {
      background: rgba(255,255,255,0.13);
      color: #fff !important;
    }
    html[data-bs-theme="dark"] .navbar {
      background: linear-gradient(90deg, #1a5bbf 0%, #2575fc 100%) !important;
      box-shadow: 0 2px 10px rgba(0,0,0,0.18);
    }
    html[data-bs-theme="dark"] .navbar-brand {
      color: #fff !important;
      text-shadow: 0 2px 8px rgba(37,117,252,0.25);
    }
    html[data-bs-theme="dark"] .nav-link {
      color: #e0e0e0 !important;
    }
    html[data-bs-theme="dark"] .nav-link.active, html[data-bs-theme="dark"] .nav-link:hover {
      background: rgba(255,255,255,0.10);
      color: #fff !important;
    }
    /* --- FIN NAVBAR UNIFICADA --- */

    .nav-tabs .nav-link.active {
      color: var(--primary-color) !important;
      font-weight: 600;
      border-bottom: 3px solid var(--primary-color);
    }

    .tab-content {
      background-color: white;
      border-radius: 0 0 10px 10px;
      padding: 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .table-container {
      margin-top: 1.5rem;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background-color: var(--primary-color);
      color: white;
      border-bottom: none;
      padding: 1rem;
    }

    .table tbody tr {
      transition: all 0.3s ease;
    }

    .table tbody tr:hover {
      background-color: rgba(37, 117, 252, 0.05);
    }

    .btn-success-custom {
      background-color: var(--success-color);
      border-color: var(--success-color);
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-success-custom:hover {
      background-color: #218838;
      transform: translateY(-2px);
    }

    .btn-danger-custom {
      background-color: var(--danger-color);
      border-color: var(--danger-color);
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-danger-custom:hover {
      background-color: #c82333;
      transform: translateY(-2px);
    }

    .container-main {
      padding: 1rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .admin-title {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .no-records {
      text-align: center;
      padding: 2rem;
      color: #6c757d;
    }

    .editable-field {
      cursor: pointer;
      padding: 0.375rem 0.75rem;
      border-radius: 0.25rem;
      transition: all 0.2s;
      display: inline-block;
      min-width: 50px;
    }

    .editable-field:hover {
      background-color: #f0f8ff;
      box-shadow: 0 0 0 1px #2575fc;
    }

    .editable-field:focus {
      outline: none;
      background-color: white;
      box-shadow: 0 0 0 2px #2575fc;
    }

    .loading-spinner {
      display: inline-block;
      width: 1rem;
      height: 1rem;
      border: 2px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top-color: #2575fc;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    @media (max-width: 768px) {
      .container-main {
        padding: 1rem;
      }
      .table-responsive {
        overflow-x: auto;
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
    html[data-bs-theme="dark"] .tab-content{
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

    html[data-bs-theme="dark"] .form-control:focus {
      background-color: #4a4a4a;
      color: #e0e0e0;
    }
  </style>
</head>

<body class="body-animate">
 <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="fas fa-tasks fa-lg text-primary"></i> Forvia
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php"><i class="fas fa-user me-1"></i>Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mytasks.php"><i class="fas fa-list-check me-1"></i>My Tasks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php"><i class="fas fa-cogs me-1"></i>Administration</a>
                    </li>
                </ul>

                <form method="POST" class="d-flex align-items-center gap-2">
                    <button id="darkModeToggle" class="btn btn-outline-secondary" type="button" title="Toggle dark mode">
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
    <?php if (isset($success)): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($success ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($error ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <h1 class="admin-title">Administration Panel</h1>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes"
          type="button" role="tab">
          Pending Records
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button"
          role="tab">
          User Management
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="adminTabsContent">
      <!-- Pending Records Tab -->
      <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
        <?php
        $hasPendientes = false;
        // Desactivar warnings temporalmente
        $error_reporting = error_reporting(0);
        while ($row = @odbc_fetch_array($result_pendientes)) {
          // Restaurar error reporting
          error_reporting($error_reporting);
          // Saltar registros inválidos o eliminados
          if (!is_array($row) || empty($row['id']) || strpos(serialize($row), '*') !== false) continue;
          
          if (!$hasPendientes) {
            $hasPendientes = true;
            echo '<div class="table-responsive table-container"><table class="table table-hover"><thead><tr>
                  <th>ID</th><th>Name</th><th>Last Name</th><th>Department</th><th>Email</th><th>Actions</th>
                  </tr></thead><tbody>';
          }
          ?>
          <tr>
            <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nombre'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['apellido'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['departamento'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
                <button type="submit" name="accion" value="aprobar" class="btn btn-success-custom me-2">
                  <i class="fas fa-check me-1"></i> Approve
                </button>
              </form>
              <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
                <button type="submit" name="accion" value="denegar" class="btn btn-danger-custom"
                  onclick="return confirm('Are you sure you want to deny this user?')">
                  <i class="fas fa-times me-1"></i> Deny
                </button>
              </form>
            </td>
          </tr>
        <?php }
        if ($hasPendientes) {
          echo '</tbody></table></div>';
        } else { ?>
          <div class="no-records">
            <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--success-color);"></i>
            <h4>No pending records for approval</h4>
          </div>
        <?php } ?>
      </div>

      <!-- User Management Tab -->
      <div class="tab-pane fade" id="usuarios" role="tabpanel">
        <div class="table-responsive table-container">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Last Name</th>
                <th>Department</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Desactivar temporalmente los warnings
              $error_reporting = error_reporting(0);
              
              // Reiniciar el puntero y obtener registros
              odbc_fetch_row($result_usuarios, 0);
              while (true) {
                  $usuario = odbc_fetch_array($result_usuarios);
                  // Restaurar configuración de errores
                  error_reporting($error_reporting);
                  
                  if ($usuario === false || !is_array($usuario) || empty($usuario['id'])) break;
                  
                  // Skip deleted records
                  if (strpos(serialize($usuario), '*') !== false) continue;
              ?>
                <tr>
                  <td><?= htmlspecialchars($usuario['id'] ?? '') ?></td>
                  <td>
                    <span class="editable-field" data-id="<?= htmlspecialchars($usuario['id'] ?? '') ?>" data-field="nombre"
                      contenteditable="true">
                      <?= htmlspecialchars($usuario['nombre'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" data-id="<?= htmlspecialchars($usuario['id'] ?? '') ?>" data-field="apellido"
                      contenteditable="true">
                      <?= htmlspecialchars($usuario['apellido'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" data-id="<?= htmlspecialchars($usuario['id'] ?? '') ?>" data-field="departamento"
                      contenteditable="true">
                      <?= htmlspecialchars($usuario['departamento'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" data-id="<?= htmlspecialchars($usuario['id'] ?? '') ?>" data-field="email"
                      contenteditable="true">
                      <?= htmlspecialchars($usuario['email'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" data-id="<?= htmlspecialchars($usuario['id'] ?? '') ?>" data-field="rol"
                      contenteditable="true">
                      <?= htmlspecialchars($usuario['rol'] ?? '') ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($usuario['aprobado']): ?>
                      <span class="badge bg-success">Approved</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-danger"
                      onclick="confirmarEliminacion(<?= htmlspecialchars($usuario['id'] ?? '') ?>)">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts locales -->
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/darkmode.js"></script>
  <script src="assets/js/fontawesome.min.js"></script>
  <script src="assets/js/sweetalert2.min.js"></script>
  <script>
    // Animación de entrada para el body al cargar la página
    document.addEventListener('DOMContentLoaded', function () {
      document.body.classList.add('body-animate');
    });

    // Función para manejar la edición de campos
    document.querySelectorAll('.editable-field').forEach(field => {
      let originalValue = field.textContent.trim();

      field.addEventListener('focus', function () {
        originalValue = this.textContent.trim();
        const range = document.createRange();
        range.selectNodeContents(this);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
      });

      field.addEventListener('blur', function () {
        const newValue = this.textContent.trim();
        if (newValue !== originalValue && newValue !== '') {
          updateField(this);
        } else {
          this.textContent = originalValue;
        }
      });

      field.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          this.blur();
        }
      });
    });

    function updateField(element) {
      const id = element.getAttribute('data-id');
      const field = element.getAttribute('data-field');
      const value = element.textContent.trim();
      const originalContent = element.innerHTML;

      element.innerHTML = `<span class="loading-spinner"></span>`;

      fetch('admin.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax_update=1&id=${id}&field=${field}&value=${encodeURIComponent(value)}`
      })
        .then(response => {
          if (!response.ok) throw new Error('Error en red');
          return response.json();
        })
        .then(data => {
          if (!data || data.success !== true) {
            throw new Error(data?.message || 'Error en servidor');
          }
          element.textContent = value;
        })
        .catch(error => {
          console.error('Error:', error);
          element.innerHTML = originalContent;
          alert('Error al actualizar: ' + error.message);
        });
    }

    // Confirmación moderna para eliminar usuario (admin)
    function confirmarEliminacion(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to delete this user permanently?',
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
          fetch('admin.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `ajax_delete=1&id=${id}`
          })
            .then(response => {
              if (!response.ok) throw new Error('Network error');
              return response.json();
            })
            .then(data => {
              if (!data || data.success !== true) {
                throw new Error(data?.message || 'Server error');
              }
              location.reload();
            })
            .catch(error => {
              Swal.fire('Error', 'Error deleting: ' + error.message, 'error');
            });
        }
      });
    }
  </script>
</body>

</html>
<?php
// No cierres la conexión manualmente al final del archivo
?>