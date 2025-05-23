<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];
    $recordar = isset($_POST['recordar']) ? true : false;

    // Consulta segura con prepared statements para evitar SQL Injection
    $query = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND aprobado = TRUE");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($contraseña, $usuario['contraseña'])) {
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($recordar) {
                // Establecer cookie que expira en 30 días
                setcookie('recordar_email', $email, time() + (30 * 24 * 60 * 60), "/");
            } else {
                // Eliminar cookie si existe
                if (isset($_COOKIE['recordar_email'])) {
                    setcookie('recordar_email', '', time() - 3600, "/");
                }
            }

            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado o no aprobado.";
    }

    $query->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forvia - Login</title>
    
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
            --light-color: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f9ff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }
        
        .card-header {
            background-color: white;
            color: var(--primary-color);
            text-align: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .brand-logo {
            font-weight: 700;
            font-size: 1.75rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .card-body {
            padding: 2rem;
            background-color: white;
        }
        
        .form-control {
            height: 48px;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.15);
        }
        
        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 500;
            border-radius: 8px;
            height: 48px;
            font-size: 1rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            background-color: var(--secondary-color);
        }
        
        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        
        .login-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .alert-danger {
            border-radius: 8px;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }
        
        .divider-text {
            padding: 0 12px;
        }
        
        .btn-outline-primary {
            height: 48px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="login-card card">
        <div class="card-header">
            <span class="brand-logo">Forvia</span>
            <h3>Login</h3>
        </div>
        <div class="card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_COOKIE['recordar_email']) ? htmlspecialchars($_COOKIE['recordar_email']) : ''; ?>" 
                           placeholder="your@email.com" required>
                </div>
                
                <div class="mb-3">
                    <label for="contraseña" class="form-label">Password</label>
                    <input type="password" class="form-control" id="contraseña" name="contraseña" placeholder="••••••••" required>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="recordar" name="recordar" 
                               <?php echo isset($_COOKIE['recordar_email']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="recordar">Remember me</label>
                    </div>
                    <a href="#" class="login-link">Forgot your password?</a>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </div>
                
                <div class="divider">
                    <span class="divider-text">or</span>
                </div>
                
                <div class="d-grid">
                    <a href="registrar.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Create a new account
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>