<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once("includes/conexion_access.php");
require_once("includes/functions.php");
require_once __DIR__ . '/includes/config.php';

$conexion_access = obtenerConexionAccess();

$user_id = $_SESSION['id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : '';
$titulo = isset($_GET['titulo']) ? $_GET['titulo'] : '';

// Marcar tarea como completada si se envía el formulario
if (isset($_POST['completar_id'])) {
    $completar_id = intval($_POST['completar_id']);
    $update_query = "UPDATE fpproject SET estado = 'completada' WHERE id = $completar_id AND (',' & user_id & ',' LIKE '%,$user_id,%')";
    odbc_exec($conexion_access, $update_query);
    header("Location: mytasks.php?success=1");
    exit();
}

// Marcar tarea como pendiente si se envía el formulario de deshacer
if (isset($_POST['deshacer_id'])) {
    $deshacer_id = intval($_POST['deshacer_id']);
    $update_query = "UPDATE fpproject SET estado = 'pendiente' WHERE id = $deshacer_id AND (',' & user_id & ',' LIKE '%,$user_id,%')";
    odbc_exec($conexion_access, $update_query);
    header("Location: mytasks.php?success=1");
    exit();
}

$where = [];
$where[] = "(',' & user_id & ',' LIKE '%,$user_id,%')";
if ($id) {
    $where[] = "id = $id";
}
if ($titulo) {
    $titulo = str_replace("'", "''", $titulo);
    $where[] = "titulo LIKE '%$titulo%'";
}

// --- Paginación ---
$limit = 10; // Tareas por página
$page_pendientes = isset($_GET['page_pendientes']) ? max(1, intval($_GET['page_pendientes'])) : 1;
$page_completadas = isset($_GET['page_completadas']) ? max(1, intval($_GET['page_completadas'])) : 1;
$offset_pendientes = ($page_pendientes - 1) * $limit;
$offset_completadas = ($page_completadas - 1) * $limit;

// Contar total de tareas para paginación
$count_pendientes_query = "SELECT COUNT(*) AS total FROM fpproject WHERE (',' & user_id & ',' LIKE '%,$user_id,%') AND (estado IS NULL OR estado = '' OR estado = 'pendiente')";
$count_completadas_query = "SELECT COUNT(*) AS total FROM fpproject WHERE (',' & user_id & ',' LIKE '%,$user_id,%') AND estado = 'completada'";
$count_pendientes_result = odbc_exec($conexion_access, $count_pendientes_query);
$total_pendientes = 0;
if ($count_pendientes_result && odbc_fetch_row($count_pendientes_result)) {
    $total_pendientes = odbc_result($count_pendientes_result, 'total');
}
$count_completadas_result = odbc_exec($conexion_access, $count_completadas_query);
$total_completadas = 0;
if ($count_completadas_result && odbc_fetch_row($count_completadas_result)) {
    $total_completadas = odbc_result($count_completadas_result, 'total');
}
$total_pages_pendientes = max(1, ceil($total_pendientes / $limit));
$total_pages_completadas = max(1, ceil($total_completadas / $limit));

// --- Consultas paginadas ---
$where_pendientes = $where;
$where_pendientes[] = "(estado IS NULL OR estado = '' OR estado = 'pendiente')";
$pendientes_sql = "SELECT * FROM (SELECT TOP $limit * FROM (SELECT TOP " . ($offset_pendientes + $limit) . " * FROM fpproject WHERE " . implode(" AND ", $where_pendientes) . " ORDER BY id DESC) AS t1 ORDER BY id ASC) AS t2 ORDER BY id DESC";
$result_pendientes = odbc_exec($conexion_access, $pendientes_sql);

$where_completadas = $where;
$where_completadas[] = "estado = 'completada'";
$completadas_sql = "SELECT * FROM (SELECT TOP $limit * FROM (SELECT TOP " . ($offset_completadas + $limit) . " * FROM fpproject WHERE " . implode(" AND ", $where_completadas) . " ORDER BY id DESC) AS t1 ORDER BY id ASC) AS t2 ORDER BY id DESC";
$result_completadas = odbc_exec($conexion_access, $completadas_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Tasks - Forvia</title>
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
            text-shadow: 0 2px 8px rgba(37, 117, 252, 0.15);
        }

        .nav-link {
            color: #e0e0e0 !important;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            margin-right: 0.3rem;
            transition: background 0.2s, color 0.2s;
        }

        .nav-link.active,
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.13);
            color: #fff !important;
        }

        html[data-bs-theme="dark"] .navbar {
            background: linear-gradient(90deg, #1a5bbf 0%, #2575fc 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
        }

        html[data-bs-theme="dark"] .navbar-brand {
            color: #fff !important;
            text-shadow: 0 2px 8px rgba(37, 117, 252, 0.25);
        }

        html[data-bs-theme="dark"] .nav-link {
            color: #e0e0e0 !important;
        }

        html[data-bs-theme="dark"] .nav-link.active,
        html[data-bs-theme="dark"] .nav-link:hover {
            background: rgba(255, 255, 255, 0.10);
            color: #fff !important;
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
            padding: 1rem;
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

        /* Dark mode specific styles */
        html[data-bs-theme="dark"] body {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }

        html[data-bs-theme="dark"] .user-greeting,
        html[data-bs-theme="dark"] .search-form,
        html[data-bs-theme="dark"] .table-container {
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

        html[data-bs-theme="dark"] .card.bg-light {
            background-color: #23272b !important;
            color: #e0e0e0 !important;
            opacity: 1 !important;
        }

        html[data-bs-theme="dark"] .card.bg-light .card-title {
            color: #6fdc8c !important;
        }

        html[data-bs-theme="dark"] .card.bg-light .text-muted {
            color: #b0b0b0 !important;
        }

        .instruction-cell {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            white-space: pre-wrap;
            /* Mantiene saltos de línea y espacios */
            word-wrap: break-word;
            /* Ajusta el texto si es muy largo */
        }

        .card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
        }

        .card:hover {
            background-color: var(--primary-color);
            color: white;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .card:hover .card-title,
        .card:hover .card-text {
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: black;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            border-radius: 6px;
        }

        .btn-action i {
            margin: 0;
        }

        /* Animación de entrada para el body (fade + scale + slide up) */
        .body-animate {
            animation: fadeInScale 0.7s cubic-bezier(.4, 0, .2, 1);
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
                        <a class="nav-link active" href="mytasks.php"><i class="fas fa-list-check me-1"></i>My Tasks</a>
                    </li>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php"><i class="fas fa-cogs me-1"></i>Administration</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <form method="POST" class="d-flex align-items-center gap-2">
                    <button id="darkModeToggle" class="btn btn-outline-secondary" type="button"
                        title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <button type="submit" name="logout" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-main">
        <!-- Resumen de tareas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-primary d-flex align-items-center justify-content-between"
                    style="border-radius: 12px;">
                    <div>
                        <h2 class="mb-0" style="font-weight:600;">My Tasks</h2>
                        <span class="fw-bold">Assigned to
                            <?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></span>
                    </div>
                    <div class="d-flex gap-4 align-items-center">
                        <span class="display-6 fw-bold text-primary"><i
                                class="fas fa-tasks me-2"></i><?php echo $total_pendientes; ?> pending</span>
                        <span class="display-6 fw-bold text-success"><i
                                class="fas fa-check-circle me-2"></i><?php echo $total_completadas; ?> completed</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Buscador compacto -->
        <form action="" method="GET" class="row g-2 mb-4 search-form p-3 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control" name="id" placeholder="Search by ID"
                    value="<?php echo htmlspecialchars($id); ?>">
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" name="titulo" placeholder="Search by Title"
                    value="<?php echo htmlspecialchars($titulo ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="fas fa-search me-1"></i> Search
                </button>
            </div>
        </form>
        <!-- Apartado tareas pendientes -->
        <h4 class="mb-3 text-primary"><i class="fas fa-hourglass-half me-2"></i>Pending Tasks</h4>
        <div class="row g-4 mb-5">
            <?php $hayPendientes = false;
            while ($fila = odbc_fetch_array($result_pendientes)):
                $hayPendientes = true; ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm h-100 border-0" style="border-radius: 16px;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <div class="badge bg-primary me-2" style="font-size:1rem;">#<?php echo $fila['id']; ?></div>
                                <h5 class="card-title mb-0 flex-grow-1" style="font-weight:600; color:#2575fc;">
                                    <?php echo htmlspecialchars($fila['titulo']); ?>
                                </h5>
                            </div>
                            <div class="mb-2 text-muted" style="font-size:0.95rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo !empty($fila['date']) ? date('d/m/Y H:i', strtotime($fila['date'])) : 'N/A'; ?>
                            </div>
                            <div class="mt-auto d-flex gap-2 justify-content-end">
                                <a href="details.php?id=<?php echo $fila['id']; ?>"
                                    class="btn btn-outline-primary btn-sm btn-action" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editor.php?id=<?php echo $fila['id']; ?>"
                                    class="btn btn-outline-success btn-sm btn-action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="completar_id" value="<?php echo $fila['id']; ?>">
                                    <button type="submit" class="btn btn-outline-success btn-sm btn-action"
                                        title="Mark as completed">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <a href="eliminar.php?id=<?php echo $fila['id']; ?>"
                                    class="btn btn-outline-danger btn-sm btn-action delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile;
            if (!$hayPendientes): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No pending tasks found.</div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Paginación tareas pendientes -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages_pendientes; $i++): ?>
                    <li class="page-item <?php echo $i == $page_pendientes ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page_pendientes=<?php echo $i; ?>&page_completadas=<?php echo $page_completadas; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <!-- Apartado tareas completadas -->
        <h4 class="mb-3 text-success"><i class="fas fa-check-circle me-2"></i>Completed Tasks</h4>
        <div class="row g-4">
            <?php $hayCompletadas = false;
            while ($fila = odbc_fetch_array($result_completadas)):
                $hayCompletadas = true; ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm h-100 border-0 bg-light" style="border-radius: 16px; opacity:0.85;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <div class="badge bg-success me-2" style="font-size:1rem;">#<?php echo $fila['id']; ?></div>
                                <h5 class="card-title mb-0 flex-grow-1" style="font-weight:600; color:#198754;">
                                    <?php echo htmlspecialchars($fila['titulo']); ?>
                                </h5>
                            </div>
                            <div class="mb-2 text-muted" style="font-size:0.95rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo !empty($fila['date']) ? date('d/m/Y H:i', strtotime($fila['date'])) : 'N/A'; ?>
                            </div>
                            <div class="mt-auto d-flex gap-2 justify-content-end">
                                <a href="details.php?id=<?php echo $fila['id']; ?>"
                                    class="btn btn-outline-primary btn-sm btn-action" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editor.php?id=<?php echo $fila['id']; ?>"
                                    class="btn btn-outline-success btn-sm btn-action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="deshacer_id" value="<?php echo $fila['id']; ?>">
                                    <button type="submit" class="btn btn-outline-warning btn-sm btn-action"
                                        title="Mark as pending">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <a href="eliminar.php?id=<?php echo $fila['id']; ?>"
                                    class="btn btn-outline-danger btn-sm btn-action delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile;
            if (!$hayCompletadas): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No completed tasks found.</div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Paginación tareas completadas -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages_completadas; $i++): ?>
                    <li class="page-item <?php echo $i == $page_completadas ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page_pendientes=<?php echo $page_pendientes; ?>&page_completadas=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sweetalert2.min.js"></script>
    <script src="assets/js/darkmode.js"></script>
    <script>
        // Animación de entrada para el body al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('body-animate');
        });

        // Mostrar toast si hay parámetros success o error en la URL
        (function () {
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

        // Confirmación moderna para eliminar, completar y deshacer
        document.querySelectorAll('.delete').forEach(link => {
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

        // Confirmación moderna para completar
        document.querySelectorAll('button[title="Mark as completed"]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Complete task?',
                    text: 'Are you sure you want to mark this task as completed?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, complete!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-popup',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.closest('form').submit();
                    }
                });
            });
        });

        // Confirmación moderna para deshacer
        document.querySelectorAll('button[title="Mark as pending"]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Mark as pending?',
                    text: 'Do you want to mark this task as pending again?',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, mark as pending!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal2-popup',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.closest('form').submit();
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
odbc_free_result($result_pendientes);
odbc_free_result($result_completadas);
odbc_close($conexion_access);
?>