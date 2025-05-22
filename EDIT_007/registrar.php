<?php
include("includes/conexion_access.php"); // Cambia la conexión a Access

function ejecutarConsulta($query) {
    $conexion_access = obtenerConexionAccess();
    $result_access = odbc_exec($conexion_access, $query);
    if (!$result_access) {
        throw new Exception("Access DB Error: " . odbc_errormsg($conexion_access));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nombre = str_replace("'", "''", $_POST['nombre']);
        $apellido = str_replace("'", "''", $_POST['apellido']);
        $departamento = str_replace("'", "''", $_POST['departamento']);
        $email = str_replace("'", "''", $_POST['email']);
        $clave = password_hash($_POST['clave'], PASSWORD_BCRYPT);
        $rol = "usuario";

        // Usar comillas simples para campos en Access
        $query_access = "INSERT INTO usuarios (nombre, apellido, departamento, email, [clave], aprobado, rol) 
                  VALUES ('$nombre', '$apellido', '$departamento', '$email', '$clave', FALSE, '$rol')";

        // Insertar en Access
        ejecutarConsulta($query_access);
        
        $success = "Registration submitted for approval.";
    } catch (Exception $e) {
        $error = "Error registering: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forvia - Register</title>    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/all.min.css" rel="stylesheet">
    <link href="assets/fonts/poppins/Poppins-Regular.woff2" as="font" type="font/woff2" crossorigin>
    
    <!-- Estilos globales -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        /* Estilos específicos para registrar.php (si se requieren) */

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
            --light-color: #f8f9fa;
            --border-color: #dee2e6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f9ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 900px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: row;
            background: white;
        }

        .brand-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex: 1;
            min-height: 500px;
        }

        .form-section {
            padding: 3rem 2.5rem;
            flex: 1.5;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-logo {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
        }

        .brand-title {
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .brand-description {
            font-size: 0.95rem;
            opacity: 0.9;
            max-width: 300px;
            margin-top: 1.5rem;
        }

        .form-control,
        .form-select {
            height: 50px;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
            border: 1px solid var(--border-color);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.15);
        }

        .btn-register {
            background-color: var(--primary-color);
            border: none;
            height: 50px;
            font-weight: 500;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .btn-register:hover {
            background-color: var(--secondary-color);
        }

        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            transition: all 0.2s ease;
        }

        .login-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .register-container {
                max-width: 750px;
            }
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                max-width: 500px;
            }

            .brand-section {
                min-height: auto;
                padding: 2rem 1.5rem;
            }

            .form-section {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .brand-logo {
                font-size: 1.8rem;
            }

            .brand-title {
                font-size: 1.5rem;
            }

            .form-section {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="brand-section">
            <div class="brand-logo">Forvia</div>
            <div class="brand-title">Create Account</div>
            <div class="brand-description">
                Join our platform and start collaborating with your team
            </div>
            <div class="mt-4">
                <i class="fas fa-users fa-3x" style="opacity: 0.8;"></i>
            </div>
        </div>

        <div class="form-section">
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="nombre" placeholder="First Name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="apellido" placeholder="Last Name" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select class="form-select" name="departamento" required>
                        <option value="" selected disabled>Select your department</option>
                        <option value="IT department">IT Department</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Robotics">Robotics</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="your@email.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="clave" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-register w-100">
                    <i class="fas fa-user-plus me-2"></i> Register
                </button>

                <a href="login.php" class="login-link">
                    Already have an account? Log in
                </a>
            </form>
        </div>
    </div>

    <script src="assets\js\botstrap.bundle.min.js"></script>
</body>

</html>