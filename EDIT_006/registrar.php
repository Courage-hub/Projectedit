<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $departamento = $_POST['departamento'];
    $email = $_POST['email'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);

    $query = "INSERT INTO usuarios (nombre, apellido, departamento, email, contraseña) VALUES ('$nombre', '$apellido', '$departamento', '$email', '$contraseña')";
    if (mysqli_query($conn, $query)) {
        echo "<p style='color: green; text-align: center;'>Registro enviado para aprobación.</p>";
    } else {
        echo "<p style='color: red; text-align: center;'>Error:</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registrarse</title>
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
            background: linear-gradient(135deg, rgb(255, 255, 255), #2575fc);
            /* Fondo degradado */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Contenedor del formulario */
        .register-container {
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
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0;
            font-family: "Poppins", sans-serif;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        /* Botón de registro */
        button {
            width: 100%;
            padding: 0.75rem;
            background: #2575fc;
            color: white;
            font-family: "Poppins", sans-serif;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        button:hover {
            background: #1a5bbf;
        }

        /* Enlace de inicio de sesión */
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
    <div class="register-container">
        <h1>Sign up</h1>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Name" required>
            <input type="text" name="apellido" placeholder="Last name" required>
            <select name="departamento" placeholder="Select your department"required>
                <option value="IT department">IT department</option>
                <option value="Marketing">Marketing</option>
                <option value="Robotics">Robotics</option>
            </select>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="contraseña" placeholder="Password" required>
            <button type="submit">Sign up</button>
        </form>
        <a href="login.php">Log in</a>
    </div>
</body>

</html>