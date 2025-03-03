<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_GET['token'];
    $nueva_contraseña = password_hash($_POST['nueva_contraseña'], PASSWORD_BCRYPT);

    // Verificar si el token es válido
    $query = "SELECT * FROM usuarios WHERE token_recuperacion = '$token'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // Actualizar la contraseña y eliminar el token
        $query = "UPDATE usuarios SET contraseña = '$nueva_contraseña', token_recuperacion = NULL WHERE token_recuperacion = '$token'";
        mysqli_query($conn, $query);

        echo "<p style='color: green; text-align: center;'>Contraseña actualizada correctamente.</p>";
    } else {
        echo "<p style='color: red; text-align: center;'>Token inválido o expirado.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
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
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>Restablecer Contraseña</h1>
        <form method="POST">
            <input type="password" name="nueva_contraseña" placeholder="Nueva contraseña" required>
            <button type="submit">Restablecer contraseña</button>
        </form>
    </div>
</body>
</html>