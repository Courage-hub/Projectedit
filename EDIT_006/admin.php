<?php
session_start();

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

include("conexion.php");

// Configuración para evitar salidas de error no controladas
ini_set('display_errors', 0);
error_reporting(0);

// Función segura para respuestas JSON
function sendJsonResponse($success, $message = '') {
    while (ob_get_level()) ob_end_clean();
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
            $field = mysqli_real_escape_string($conn, $_POST['field']);
            $value = mysqli_real_escape_string($conn, $_POST['value']);
            
            $allowedFields = ['nombre', 'apellido', 'departamento', 'email', 'rol'];
            if (!in_array($field, $allowedFields)) {
                sendJsonResponse(false, 'Campo no permitido');
            }
            
            $query = "UPDATE usuarios SET $field = '$value' WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                sendJsonResponse(true);
            } else {
                sendJsonResponse(false, 'Error en la base de datos: ' . mysqli_error($conn));
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
            if (mysqli_query($conn, $query)) {
                sendJsonResponse(true);
            } else {
                sendJsonResponse(false, 'Error al eliminar: ' . mysqli_error($conn));
            }
        } catch (Exception $e) {
            sendJsonResponse(false, 'Error inesperado: ' . $e->getMessage());
        }
    }
    
    // Manejar aprobación/denegación de registros
    if (isset($_POST['id'], $_POST['accion'])) {
        $id = intval($_POST['id']);
        $accion = $_POST['accion'];

        if ($accion == 'aprobar') {
            $query = "UPDATE usuarios SET aprobado = TRUE WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $success = "Usuario aprobado correctamente.";
            } else {
                $error = "Error al aprobar usuario: " . mysqli_error($conn);
            }
        } elseif ($accion == 'denegar') {
            $query = "DELETE FROM usuarios WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $success = "Usuario denegado correctamente.";
            } else {
                $error = "Error al denegar usuario: " . mysqli_error($conn);
            }
        }
    }
}

// Obtener registros pendientes
$query_pendientes = "SELECT * FROM usuarios WHERE aprobado = FALSE";
$result_pendientes = mysqli_query($conn, $query_pendientes);

// Obtener todos los usuarios
$query_usuarios = "SELECT * FROM usuarios";
$result_usuarios = mysqli_query($conn, $query_usuarios);

// Limpiar buffer antes de HTML
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <title>Forvia - Administración</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap local -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome local -->
  <link href="assets/css/fontawesome.min.css" rel="stylesheet">
  <link href="assets/css/solid.min.css" rel="stylesheet">
  <!-- Fuente Poppins desde Google Fonts (opcional, puede cargarse localmente también) -->
  <style>
    /* Estilos CSS originales se mantienen igual */
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
      padding: 2rem;
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
      border: 2px solid rgba(0,0,0,0.1);
      border-radius: 50%;
      border-top-color: #2575fc;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    @media (max-width: 768px) {
      .container-main {
        padding: 1rem;
      }
      
      .table-responsive {
        overflow-x: auto;
      }
    }
  </style>
</head>
<body>
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Forvia</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="admin.php">Administración</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="perfil.php">Perfil</a>
          </li>
        </ul>
        <form method="POST" class="d-flex">
          <button type="submit" name="logout" class="btn btn-outline-danger">
            <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
          </button>
        </form>
      </div>
    </div>
  </nav>

  <!-- Contenido principal -->
  <div class="container-main">
    <?php if(isset($success)): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    
    <h1 class="admin-title">Panel de Administración</h1>
    
    <!-- Pestañas -->
    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
          Registros Pendientes
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab">
          Gestión de Usuarios
        </button>
      </li>
    </ul>
    
    <!-- Contenido de las pestañas -->
    <div class="tab-content" id="adminTabsContent">
      <!-- Pestaña de registros pendientes -->
      <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
        <?php if (mysqli_num_rows($result_pendientes) > 0): ?>
          <div class="table-responsive table-container">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Departamento</th>
                  <th>Email</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_pendientes)): ?>
                  <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                    <td><?php echo htmlspecialchars($row['departamento']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="accion" value="aprobar" class="btn btn-success-custom me-2">
                          <i class="fas fa-check me-1"></i> Aprobar
                        </button>
                      </form>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="accion" value="denegar" class="btn btn-danger-custom"
                                onclick="return confirm('¿Estás seguro de denegar este usuario?')">
                          <i class="fas fa-times me-1"></i> Denegar
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="no-records">
            <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--success-color);"></i>
            <h4>No hay registros pendientes de aprobación</h4>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Pestaña de gestión de usuarios -->
      <div class="tab-pane fade" id="usuarios" role="tabpanel">
        <div class="table-responsive table-container">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Departamento</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($usuario = mysqli_fetch_assoc($result_usuarios)): ?>
                <tr>
                  <td><?php echo $usuario['id']; ?></td>
                  <td>
                    <span class="editable-field" 
                          data-id="<?php echo $usuario['id']; ?>" 
                          data-field="nombre"
                          contenteditable="true">
                      <?php echo htmlspecialchars($usuario['nombre']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" 
                          data-id="<?php echo $usuario['id']; ?>" 
                          data-field="apellido"
                          contenteditable="true">
                      <?php echo htmlspecialchars($usuario['apellido']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" 
                          data-id="<?php echo $usuario['id']; ?>" 
                          data-field="departamento"
                          contenteditable="true">
                      <?php echo htmlspecialchars($usuario['departamento']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" 
                          data-id="<?php echo $usuario['id']; ?>" 
                          data-field="email"
                          contenteditable="true">
                      <?php echo htmlspecialchars($usuario['email']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="editable-field" 
                          data-id="<?php echo $usuario['id']; ?>" 
                          data-field="rol"
                          contenteditable="true">
                      <?php echo htmlspecialchars($usuario['rol']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if($usuario['aprobado']): ?>
                      <span class="badge bg-success">Aprobado</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>)">
                      <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts locales -->
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/fontawesome.min.js"></script>
  <script>
    // Función para manejar la edición de campos
    document.querySelectorAll('.editable-field').forEach(field => {
      let originalValue = field.textContent.trim();
      
      field.addEventListener('focus', function() {
        originalValue = this.textContent.trim();
        const range = document.createRange();
        range.selectNodeContents(this);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
      });
      
      field.addEventListener('blur', function() {
        const newValue = this.textContent.trim();
        if (newValue !== originalValue && newValue !== '') {
          updateField(this);
        } else {
          this.textContent = originalValue;
        }
      });
      
      field.addEventListener('keydown', function(e) {
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
<?php
mysqli_close($conn);
?>