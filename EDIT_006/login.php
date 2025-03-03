<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];

    $query = "SELECT * FROM usuarios WHERE email = '$email' AND aprobado = TRUE";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $usuario = mysqli_fetch_assoc($result);
        if (password_verify($contraseña, $usuario['contraseña'])) {
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['rol'] = $usuario['rol'];
            header("Location: index.php");
            exit();
        } else {
            echo "<p style='color: red; text-align: center;'>Contraseña incorrecta.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>Usuario no encontrado o no aprobado.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
                  @font-face {
    font-family: 'Poppins';
    src: url('$ROHWJOX.woff2') format('woff2'),
      url('$ROHWJOX.woff2') format('woff');
    font-weight: normal;
    font-style: normal;
  }

  @font-face {
    font-family: 'Poppins';
    src: url('$ROHWJOX.woff2') format('woff2'),
      url('$ROHWJOX.woff2') format('woff');
    font-weight: bold;
    font-style: normal;
  }
        /* Estilos generales */
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg,rgb(255, 255, 255), #2575fc); /* Fondo degradado */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Contenedor del formulario */
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Título */
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }

        /* Campos de entrada */
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0;
            font-family: "Poppins", sans-serif;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        /* Botón de inicio de sesión */
        button {
            width: 100%;
            padding: 0.75rem;
            font-family: "Poppins", sans-serif;
            background: #2575fc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        button:hover {
            background: #1a5bbf;
        }

        /* Enlace de registro */
        a {
            display: block;
            margin-top: 1rem;
            color: #2575fc;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="contraseña" placeholder="Password" required>
            <br>
            <a href="password.php">Forgot password?</a>
            <button type="submit">Log in</button>
        </form>
        
        <a href="registrar.php">Sign up</a>
    </div>
</body>
</html>