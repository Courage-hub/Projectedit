<?php
session_start();
include("conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
  header("Location: login.php");
  exit();
}

// Cerrar sesión
if (isset($_POST['logout'])) {
  session_destroy();
  header("Location: login.php");
  exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : '';
$instruccion = isset($_GET['instruccion']) ? $_GET['instruccion'] : '';

$query = "SELECT * FROM fpproject";
if ($id || $instruccion) {
  $query .= " WHERE";
  if ($id) {
    $query .= " id = '$id'";
  }
  if ($id && $instruccion) {
    $query .= " AND";
  }
  if ($instruccion) {
    $query .= " instruccion LIKE '%$instruccion%'";
  }
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <title>Forvia - Dashboard</title>
  <meta charset="UTF-8">
  <!-- Bootstrap CSS -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="assets/css/all.min.css" rel="stylesheet">

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

    .user-greeting {
      background-color: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }

    .welcome-title {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .btn-primary-custom {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 8px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-primary-custom:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(37, 117, 252, 0.3);
    }

    .btn-outline-custom {
      border-color: var(--primary-color);
      color: var(--primary-color);
      border-radius: 8px;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-outline-custom:hover {
      background-color: var(--primary-color);
      color: white;
    }

    .search-form {
      background-color: white;
      border-radius: 10px;
      padding: 1.5rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }

    .form-control {
      border-radius: 8px;
      padding: 0.5rem 1rem;
      border: 1px solid #dee2e6;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
    }

    .table-container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      overflow: hidden;
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

    .action-link {
      color: var(--primary-color);
      text-decoration: none;
      margin-right: 1rem;
      transition: all 0.3s ease;
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
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
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
            <a class="nav-link active" href="ProyectoFP.html">Inicio</a>
          </li>
          <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin.php">Administración</a>
            </li>
          <?php endif; ?>
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
    <!-- Mensaje de bienvenida -->
    <div class="user-greeting">
      <h1 class="welcome-title">Client Task - Forvia</h1>
      <p class="lead">Bienvenido, <?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></p>
      <div class="d-flex gap-2 mt-3">
        <a href="agregar.php" class="btn btn-primary-custom">
          <i class="fas fa-plus me-1"></i> Nuevo Registro
        </a>
      </div>
    </div>

    <!-- Formulario de búsqueda -->
    <div class="search-form">
      <form action="" method="GET" class="row g-3">
        <div class="col-md-5">
          <input type="text" class="form-control" name="id" placeholder="Buscar por ID"
            value="<?php echo htmlspecialchars($id); ?>">
        </div>
        <div class="col-md-5">
          <input type="text" class="form-control" name="instruccion" placeholder="Buscar por Instrucción"
            value="<?php echo htmlspecialchars($instruccion); ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary-custom w-100">
            <i class="fas fa-search me-1"></i> Buscar
          </button>
        </div>
      </form>
    </div>

    <!-- Tabla de resultados -->
    <div class="table-container">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Instrucción</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($filas = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $filas['id'] ?></td>
                <td><?php echo htmlspecialchars($filas['instruccion']) ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($filas['date'])); ?></td>
                <td>
                  <a href="editor.php?id=<?php echo $filas['id']; ?>" class="action-link">
                    <i class="fas fa-edit me-1"></i> Editar
                  </a>
                  <a href="eliminar.php?id=<?php echo $filas['id']; ?>" class="action-link delete"
                    onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?')">
                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script>
    // Confirmación antes de eliminar
    document.querySelectorAll('.delete').forEach(link => {
      link.addEventListener('click', function (e) {
        if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) {
          e.preventDefault();
        }
      });
    });
  </script>
</body>

</html>
<?php
mysqli_close($conn);
?>