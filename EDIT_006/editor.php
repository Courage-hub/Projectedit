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
    
    <!-- Summernote resources -->
    <link href="summernote-0.8.18-dist/bootstrap.min.css" rel="stylesheet">
    <script src="summernote-0.8.18-dist/jquery-3.5.1.min.js"></script>
    <script src="summernote-0.8.18-dist/bootstrap.min.js"></script>
    <link href="summernote-0.8.18-dist/summernote.min.css" rel="stylesheet">
    <script src="summernote-0.8.18-dist/summernote.min.js"></script>

</head>

<body class="bg-light">
    <?php
    session_start();
    include("conexion.php");

    // Verificar si el usuario está logueado
    if (!isset($_SESSION['nombre']) || !isset($_SESSION['apellido'])) {
        header("Location: login.php");
        exit();
    }

    if (isset($_POST['editar'])) {
        $id = $_POST['id'];
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $instruccion = mysqli_real_escape_string($conn, $_POST['instruccion']);
        $user_id = $_SESSION['id'];

        // Verificar si el usuario actual es el creador del registro
        $check_query = "SELECT * FROM fpproject WHERE id = '$id' AND user_id = '$user_id'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // Actualizar el registro si el usuario es el creador
            $sql = "UPDATE fpproject SET titulo='$titulo', instruccion='$instruccion' WHERE id=$id";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                echo "<script>alert('The data was updated correctly');
                location.assign('index.php');
                </script>";
            } else {
                echo "<script>alert('ERROR: The data could not be updated.');</script>";
            }
        } else {
            echo "<script>alert('You do not have permission to edit this record.');
            location.assign('index.php');
            </script>";
        }
    }

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $user_id = $_SESSION['id'];

        // Verificar si el usuario actual es el creador del registro
        $sql = "SELECT * FROM fpproject WHERE id = '$id' AND user_id = '$user_id'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        } else {
            echo "<script>alert('You do not have permission to access this record.');
            location.assign('index.php');
            </script>";
            exit();
        }
    }
    ?>

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
                                <textarea name="instruccion" id="summernote" class="input"><?= $row['instruccion'] ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="submit" class="btn btn-success px-4 py-2" name="editar">
                                    <i class="fas fa-sync-alt me-2"></i>Update
                                </button>
                                <a href="index.php" class="btn btn-danger px-4 py-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Edit your instruction here...',
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
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
    }
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-success, .btn-danger {
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
        
        .card {
            border-radius: 10px;
            border: none;
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .note-editor {
            border-radius: 5px;
        }
    </style>
</body>
</html>