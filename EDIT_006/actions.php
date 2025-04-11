<?php

session_start();
include("conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del registro
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consultar el registro por ID
$query = "SELECT titulo, instruccion FROM fpproject WHERE id = $id";
$result = mysqli_query($conn, $query);

// Verificar si se encontró el registro
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "<script>alert('Record not found'); window.location.href='index.php';</script>";
    exit();
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
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .btn-back {
            margin-top: 2rem;
        }

        /* Dark mode styles */
        html[data-bs-theme="dark"] body {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }

        html[data-bs-theme="dark"] .container-main {
            background-color: #2c2c2c;
            color: #e0e0e0;
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

<body>
    <div class="container-main">
        <h1 class="title"><?php echo htmlspecialchars($row['titulo']); ?></h1>
        <div class="instruction">
            <?php echo htmlspecialchars_decode($row['instruccion']); ?>
        </div>
        <a href="index.php" class="btn btn-outline-primary btn-back">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/darkmode.js"></script>
    <script src="assets/js/fontawesome.min.js"></script>
</body>

</html>