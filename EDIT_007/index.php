<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit();
}

require_once("includes/conexion_access.php");
require_once("includes/functions.php"); // Asegúrate de que esta línea esté correcta

// Obtener la conexión
$conexion_access = obtenerConexionAccess();

// Cerrar sesión
if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: esvlc.edit.home.html");
  exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : '';
$titulo = isset($_GET['titulo']) ? $_GET['titulo'] : '';

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Contar total de tareas para paginación
$count_query = "SELECT COUNT(*) AS total FROM fpproject";
$where = [];
if ($id) {
    $where[] = "id = $id";
}
if ($titulo) {
    $titulo = str_replace("'", "''", $titulo);
    $where[] = "titulo LIKE '%$titulo%'";
}
if ($where) {
    $count_query .= " WHERE " . implode(" AND ", $where);
}
$count_result = odbc_exec($conexion_access, $count_query);
$total_registros = 0;
if ($count_result && odbc_fetch_row($count_result)) {
    $total_registros = odbc_result($count_result, 'total');
}
$total_pages = max(1, ceil($total_registros / $limit));

// Consulta paginada
$query = "SELECT * FROM (SELECT TOP $limit * FROM (SELECT TOP " . ($offset + $limit) . " * FROM fpproject";
if ($where) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY id DESC) AS t1 ORDER BY id ASC) AS t2 ORDER BY id DESC";

$result_access = odbc_exec($conexion_access, $query);
if (!$result_access) {
  die("Error al obtener datos de Access: " . odbc_errormsg($conexion_access));
}

$filas = [];
while ($fila = odbc_fetch_array($result_access)) {
  $filas[] = $fila;
}

// Liberar el recurso inmediatamente después de usarlo
odbc_free_result($result_access);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Forvia - Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="assets/css/all.min.css" rel="stylesheet">

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
      --dark-color: #343a40;
      --light-color: #f8f9fa;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f9ff;
      color: #333;
    }

    /* Animación de entrada alternativa para el body */
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

    .btn-primary-custom, .btn-outline-custom, .btn-outline-primary, .btn-primary {
      border-radius: 10px;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(37,117,252,0.08);
      letter-spacing: 0.5px;
    }

    .btn-primary-custom, .btn-primary {
      background: linear-gradient(90deg, var(--primary-color) 60%, var(--secondary-color) 100%);
      color: #fff;
      border: none;
    }

    .btn-primary-custom:hover, .btn-primary:hover {
      background: linear-gradient(90deg, var(--secondary-color) 0%, var(--primary-color) 100%);
      color: #fff;
      box-shadow: 0 4px 16px rgba(37,117,252,0.18);
      transform: translateY(-2px) scale(1.03);
    }

    .btn-outline-primary, .btn-outline-custom {
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
      background: transparent;
    }

    .btn-outline-primary:hover, .btn-outline-custom:hover {
      background: var(--primary-color);
      color: #fff;
      border-color: var(--primary-color);
    }

    .btn-outline-danger {
      border-radius: 10px;
      font-weight: 600;
      border-width: 2px;
    }

    .user-greeting {
      background: linear-gradient(90deg, #fff 60%, #f5f9ff 100%);
      border-radius: 18px;
      padding: 2.2rem 2rem 2rem 2rem;
      box-shadow: 0 6px 32px rgba(37,117,252,0.07);
      margin-bottom: 2.2rem;
      border: 1.5px solid #e3eafc;
    }

    .welcome-title {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1rem;
      letter-spacing: 0.5px;
    }

    .search-form {
      background: #fff;
      border-radius: 14px;
      padding: 1.5rem 2rem;
      box-shadow: 0 2px 12px rgba(37,117,252,0.06);
      margin-bottom: 2rem;
      border: 1.5px solid #e3eafc;
    }

    .form-control {
      border-radius: 8px;
      padding: 0.5rem 1rem;
      border: 1.5px solid #dee2e6;
      background: #f8faff;
      font-weight: 500;
      color: #333;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.13);
      background: #fff;
      color: #222;
    }

    .table-container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(37,117,252,0.07);
      overflow: hidden;
      border: 1.5px solid #e3eafc;
    }

    .table thead th {
      background: linear-gradient(90deg, var(--primary-color) 60%, var(--secondary-color) 100%);
      color: #fff;
      border-bottom: none;
      padding: 1.1rem 0.7rem;
      font-size: 1.08rem;
      letter-spacing: 0.5px;
    }

    .table tbody tr {
      transition: background 0.2s;
    }

    .table tbody tr:hover {
      background: rgba(37, 117, 252, 0.07);
    }

    .action-link {
      color: var(--primary-color);
      text-decoration: none;
      margin-right: 1.1rem;
      font-size: 1.1rem;
      transition: color 0.2s;
    }

    .action-link:hover {
      color: var(--secondary-color);
      text-decoration: underline;
    }

    .action-link.delete {
      color: #dc3545;
    }

    .action-link.delete:hover {
      color: #bb2d3b;
    }

    .container-main {
      padding: 1.5rem 0.5rem 2.5rem 0.5rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .pagination {
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(37,117,252,0.07);
      background: #fff;
      padding: 0.5rem 1rem;
      margin-bottom: 0.5rem;
    }

    .page-item.active .page-link {
      background: linear-gradient(90deg, var(--primary-color) 60%, var(--secondary-color) 100%);
      color: #fff;
      border: none;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(37,117,252,0.13);
    }

    .page-link {
      color: var(--primary-color);
      border-radius: 8px;
      margin: 0 2px;
      font-weight: 500;
      border: none;
      transition: background 0.2s, color 0.2s;
    }

    .page-link:hover {
      background: var(--primary-color);
      color: #fff;
    }

    .card {
      display: flex;
      flex-direction: column;
      height: 100%;
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(37,117,252,0.10);
      border: 1.5px solid #e3eafc;
      background: #fff;
      transition: background 0.2s, color 0.2s;
    }

    .card-body {
      display: flex;
      flex-direction: column;
      height: 100%;
      padding: 1.3rem 1.1rem 1.1rem 1.1rem;
    }

    .card-actions {
      margin-top: auto;
      display: flex;
      gap: 0.5rem;
    }

    .card:hover {
      background: linear-gradient(90deg, var(--primary-color) 60%, var(--secondary-color) 100%);
      color: #fff;
      box-shadow: 0 8px 32px rgba(37,117,252,0.18);
    }

    .card:hover .card-title,
    .card:hover .card-text {
      color: #fff;
    }

    /* Dark mode styles */
    html[data-bs-theme="dark"] body {
      background: #181c23;
      color: #e0e0e0;
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

    html[data-bs-theme="dark"] .user-greeting,
    html[data-bs-theme="dark"] .search-form,
    html[data-bs-theme="dark"] .table-container,
    html[data-bs-theme="dark"] .card,
    html[data-bs-theme="dark"] .pagination {
      background: #232a36;
      color: #e0e0e0;
      border-color: #2c3442;
    }

    html[data-bs-theme="dark"] .table thead th {
      background: linear-gradient(90deg, #1a5bbf 0%, #2575fc 100%);
      color: #fff;
    }

    html[data-bs-theme="dark"] .table tbody tr:hover {
      background: rgba(37, 117, 252, 0.13);
    }

    html[data-bs-theme="dark"] .form-control {
      background: #232a36;
      color: #e0e0e0;
      border-color: #2c3442;
    }

    html[data-bs-theme="dark"] .form-control:focus {
      background: #2c3442;
      color: #fff;
    }

    html[data-bs-theme="dark"] .card:hover {
      background: linear-gradient(90deg, #1a5bbf 0%, #2575fc 100%);
      color: #fff;
    }

    html[data-bs-theme="dark"] .page-link {
      background: #232a36;
      color: #fff;
      border: none;
    }

    html[data-bs-theme="dark"] .page-link:hover {
      background: var(--primary-color);
      color: #fff;
    }

    html[data-bs-theme="dark"] .page-item.active .page-link {
      background: linear-gradient(90deg, #1a5bbf 0%, #2575fc 100%);
      color: #fff;
    }

    html[data-bs-theme="dark"] .total-records-card {
      background: linear-gradient(90deg, #232a36 0%, #1a5bbf 100%) !important;
      /* Fondo azul oscuro en dark mode */
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
      .container-main {
        padding: 0.5rem;
      }
      .user-greeting, .search-form, .table-container, .card {
        padding: 1rem !important;
      }
      .table-responsive {
        overflow-x: auto;
      }
    }
  </style>
</head>

<body>
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
            <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="perfil.php"><i class="fas fa-user me-1"></i>Profile</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mytasks.php"><i class="fas fa-list-check me-1"></i>My Tasks</a>
          </li>
          <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin.php"><i class="fas fa-cogs me-1"></i>Administration</a>
            </li>
          <?php endif; ?>
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
    <!-- Welcome & Dashboard Row -->
    <div class="row g-3 mb-3 align-items-stretch">
      <div class="col-lg-8">
        <div class="user-greeting h-100 d-flex flex-column justify-content-center">
          <h1 class="welcome-title mb-2"><i class="fas fa-user-circle me-2"></i>Client Task - Forvia</h1>
          <p class="lead mb-2">Welcome, <span class="fw-bold text-primary"><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></span></p>
          <div class="d-flex gap-2 mt-2">
            <a href="agregar.php" class="btn btn-primary-custom">
              <i class="fas fa-plus me-1"></i> New Record
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-4 d-flex flex-column justify-content-between">
        <div class="row g-2 flex-grow-1">
          <div class="col-12 h-100 d-flex flex-column justify-content-between">
            <div class="card shadow-sm border-0 bg-gradient h-100 d-flex flex-column justify-content-center align-items-center total-records-card"
              style="background: linear-gradient(90deg, #f5f9ff 0%, #e3eafc 100%); min-height: 140px; box-shadow: 0 8px 32px rgba(37,117,252,0.18); border-radius: 18px;">
              <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2 p-4 w-100">
                <i class="fas fa-database fa-2x mb-2 gradient-text" style="background: linear-gradient(90deg, #2575fc 0%, #1a5bbf 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: transparent;"></i>
                <div class="text-center w-100">
                  <div class="fs-2 fw-bold mb-1 gradient-text" style="background: linear-gradient(90deg, #2575fc 0%, #1a5bbf 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: transparent; letter-spacing: 1px;">
                    <?php echo $total_registros; ?>
                  </div>
                  <div class="small gradient-text" style="background: linear-gradient(90deg, #2575fc 0%, #1a5bbf 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; color: transparent; font-size: 1.1rem; font-weight: 500;">Total Records</div>
                </div>
              </div>
            </div>
            <div class="d-flex flex-row justify-content-center gap-2 mt-3 w-100">
              <button id="table-view" class="btn btn-outline-primary btn-sm w-50 w-md-auto">
                <i class="fas fa-list"></i> Table View
              </button>
              <button id="card-view" class="btn btn-outline-primary btn-sm w-50 w-md-auto">
                <i class="fas fa-th"></i> Card View
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Row -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-8">
        <div class="search-form h-100">
          <form action="" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
              <input type="text" class="form-control" name="id" placeholder="Search by ID"
                value="<?php echo htmlspecialchars($id); ?>">
            </div>
            <div class="col-12 col-md-5">
              <input type="text" class="form-control" name="titulo" placeholder="Search by Title"
                value="<?php echo htmlspecialchars($titulo ?? ''); ?>">
            </div>
            <div class="col-12 col-md-2">
              <button type="submit" class="btn btn-primary-custom w-100">
                <i class="fas fa-search me-1"></i> Search
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="tab-content" id="mainTabsContent">
      <div class="tab-pane fade show active" id="access" role="tabpanel">
        <div id="access-table-container" class="table-container">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($filas as $fila): ?>
                  <tr>
                    <td class="fw-semibold text-primary"><?php echo $fila['id'] ?></td>
                    <td class="tit">
                      <a href="details.php?id=<?php echo $fila['id']; ?>" class="text-decoration-none text-black">
                        <?php echo htmlspecialchars($fila['titulo']); ?>
                      </a>
                    </td>
                    <td><?php echo !empty($fila['date']) ? date('d/m/Y H:i', strtotime($fila['date'])) : 'N/A'; ?></td>
                    <td>
                      <?php if ($fila['user_id'] == $_SESSION['id']): ?>
                        <a href="editor.php?id=<?php echo $fila['id']; ?>" class="action-link" title="Edit">
                          <i class="fas fa-edit me-1"></i>
                        </a>
                        <a href="eliminar.php?id=<?php echo $fila['id']; ?>" class="action-link delete" title="Delete"
                          onclick="return false;">
                          <i class="fas fa-trash-alt me-1"></i>
                        </a>
                      <?php else: ?>
                        <span class="text-muted">No actions available</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation example" class="mb-4">
          <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?<?php
                  $params = $_GET;
                  $params['page'] = $i;
                  echo http_build_query($params);
                ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>
        <div id="access-card-container" class="row g-3 mt-4 d-none">
          <?php foreach ($filas as $fila): ?>
            <div class="col-md-4">
              <a href="details.php?id=<?php echo $fila['id']; ?>" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0">
                  <div class="card-body">
                    <h5 class="card-title fw-bold text-primary"><?php echo htmlspecialchars($fila['titulo']); ?></h5>
                    <p class="card-text mb-2">
                      <span class="badge bg-primary bg-opacity-75 me-2"><i class="fas fa-hashtag"></i> <?php echo $fila['id']; ?></span><br>
                      <span class="text-muted small"><i class="fas fa-calendar-alt me-1"></i> <?php echo !empty($fila['date']) ? date('d/m/Y H:i', strtotime($fila['date'])) : 'N/A'; ?></span>
                    </p>
                    <div class="card-actions">
                      <?php if ($fila['user_id'] == $_SESSION['id']): ?>
                        <a href="editor.php?id=<?php echo $fila['id']; ?>" class="btn btn-primary btn-sm">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="eliminar.php?id=<?php echo $fila['id']; ?>" class="btn btn-danger btn-sm"
                          onclick="return false;">
                          <i class="fas fa-trash-alt me-1"></i> Delete
                        </a>
                      <?php else: ?>
                        <span class="text-muted">No actions available</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation example" class="mt-4">
          <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?<?php
                  $params = $_GET;
                  $params['page'] = $i;
                  echo http_build_query($params);
                ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>

    <script>
      // Toggle views for Access and guardar preferencia en localStorage
      function setView(view) {
        if (view === 'table') {
          document.querySelectorAll('#access-table-container').forEach(el => el.classList.remove('d-none'));
          document.querySelectorAll('#access-card-container').forEach(el => el.classList.add('d-none'));
        } else {
          document.querySelectorAll('#access-table-container').forEach(el => el.classList.add('d-none'));
          document.querySelectorAll('#access-card-container').forEach(el => el.classList.remove('d-none'));
        }
        localStorage.setItem('dashboardView', view);
      }

      document.getElementById('table-view').addEventListener('click', function () {
        setView('table');
      });
      document.getElementById('card-view').addEventListener('click', function () {
        setView('card');
      });

      // Al cargar la página, restaurar la vista guardada
      document.addEventListener('DOMContentLoaded', function () {
        const savedView = localStorage.getItem('dashboardView') || 'table';
        setView(savedView);
      });

      // Animación de entrada para el body al cargar la página
      document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('body-animate');
      });
    </script>

    <!-- Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/darkmode.js"></script>
    <script src="assets/js/fontawesome.min.js"></script>
    <script src="assets/js/sweetalert2.min.js"></script>
    <script>
      // Confirmación moderna para eliminar
      document.querySelectorAll('.btn-danger, .action-link.delete').forEach(link => {
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
              if (link.tagName === 'A') {
                window.location = link.href;
              } else if (link.closest('form')) {
                link.closest('form').submit();
              }
            }
          });
        });
      });

      // Mostrar toast si hay parámetros success o error en la URL
      (function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('success')) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Acción realizada correctamente',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
          });
        } else if (params.get('error')) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Ocurrió un error en la acción',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
          });
        }
      })();
    </script>
  </div>
</body>
</html>