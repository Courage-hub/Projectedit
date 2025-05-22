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
  <meta charset="UTF-8">  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome local -->
  <link href="assets/css/fontawesome.min.css" rel="stylesheet">
  <link href="assets/css/solid.min.css" rel="stylesheet">
  <link href="assets/css/all.min.css" rel="stylesheet">
  
  <!-- Estilos globales -->
  <link href="assets/css/style.css" rel="stylesheet">  <style>
    /* Estilos específicos para admin.php (si fuera necesario) */
  </style>
</head>

<body class="body-animate">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
          <i class="fas fa-tasks fa-lg text-white"></i> Forvia
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
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
              <li class="nav-item">
                <a class="nav-link active" href="admin.php"><i class="fas fa-cogs me-1"></i>Administration</a>
              </li>
            <?php endif; ?>          </ul>
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
  <script src="assets/js/all.min.js"></script>
  <script>
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

    function confirmarEliminacion(id) {
      if (confirm('¿Estás seguro de eliminar este usuario permanentemente?')) {
        fetch('admin.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `ajax_delete=1&id=${id}`
        })
          .then(response => {
            if (!response.ok) throw new Error('Error en red');
            return response.json();
          })
          .then(data => {
            if (!data || data.success !== true) {
              throw new Error(data?.message || 'Error en servidor');
            }
            location.reload();
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar: ' + error.message);
          });
      }
    }
  </script>
</body>

</html>
