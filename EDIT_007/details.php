<?php
require_once("includes/functions.php");
require_once("includes/conexion_access.php"); // Cambiar include por require_once
verificarLogin(); // Verifica si el usuario está logueado

// Obtener la conexión
$conexion_access = obtenerConexionAccess();

// Obtener el ID del registro
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar el registro por ID
$query = "SELECT titulo, instruccion, user_id FROM fpproject WHERE id = $id";
$result = odbc_exec($conexion_access, $query);

// Verificar si se encontró el registro
if ($result && odbc_fetch_row($result)) {
    $row = [
        'titulo' => odbc_result($result, "titulo"),
        'instruccion' => odbc_result($result, "instruccion"),
        'user_id' => odbc_result($result, "user_id")
    ];
} else {
    echo "<script>alert('Record not found'); window.location.href='index.php';</script>";
    exit();
}

// Cerrar el recurso de consulta
odbc_free_result($result);

// Leer el archivo HTML generado si existe
$htmlFile = __DIR__ . "/datos/edit_data/edit_{$id}/edit_{$id}.html";
$htmlContent = '';
if (file_exists($htmlFile)) {
    $htmlContent = file_get_contents($htmlFile);
    $botones = '<div class="action-buttons-container" style="margin-top:2rem;display:flex;justify-content:space-between;align-items:center;">'
        .'<a href="index.php" class="btn-back"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>'
        .'<div class="action-buttons">';
    if ($row['user_id'] == $_SESSION['id']) {
        $botones .= '<a href="editor.php?id=' . $id . '" class="action-link"><i class="fas fa-edit me-1"></i> Edit</a>';
        $botones .= '<a href="eliminar.php?id=' . $id . '" class="action-link delete"><i class="fas fa-trash-alt me-1"></i> Delete</a>';
    } else {
        $botones .= '<span class="text-muted">No actions available</span>';
    }
    $botones .= '</div></div>';
    // Insertar los botones antes del último </div> (container-main)
    $htmlContent = preg_replace('/(<\/div>)(?!.*<\/div>)/', $botones.'$1', $htmlContent, 1);
} else {
    // Si no existe el archivo, mostrar mensaje o fallback
    $htmlContent = '<div class="alert alert-warning">No hay contenido estructurado disponible para este registro.</div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forvia - Details</title>
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
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f9ff;
            color: #333;
        }

        .container-main {
            padding: 1rem;
            max-width: 1000px;
            margin: 2rem auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2575fc;
            margin-bottom: 1rem;
        }

        .action-buttons-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .action-link {
            color: #2575fc;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .action-link:hover {
            color: #1a5bbf;
            text-decoration: underline;
        }

        .action-link.delete {
            color: #dc3545;
        }

        .action-link.delete:hover {
            color: #bb2d3b;
        }

        .btn-back {
            background-color: #2575fc;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            outline: none; 
        }

        .btn-back:hover {
            background-color: #1a5bbf;
            color: white;
            outline: 2px solid #1a5bbf; 
        }

        /* Dark mode styles */
        html[data-bs-theme="dark"] body {
            background-color: #1a1a1a !important;
            color: #e0e0e0 !important;
        }

        html[data-bs-theme="dark"] .container-main {
            background-color: #23272b !important;
            color: #e0e0e0 !important;
        }

        html[data-bs-theme="dark"] .btn-back {
            background-color: #343a40;
            color: #e0e0e0;
        }

        html[data-bs-theme="dark"] .btn-back:hover {
            background-color: #495057;
        }
    </style>
</head>

<body class="bg-light">
    <?php echo $htmlContent; ?>

    <!-- Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/darkmode.js"></script>
    <script src="assets/js/fontawesome.min.js"></script>
    <script src="assets/js/sweetalert2.min.js"></script>
    <script>
      // Confirmación moderna para eliminar
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
</body>

</html>