<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar si el correo existe en la base de datos
    $query = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // Generar un token único
        $token = bin2hex(random_bytes(32)); // Token seguro de 64 caracteres

        // Guardar el token en la base de datos
        $query = "UPDATE usuarios SET token_recuperacion = '$token' WHERE email = '$email'";
        mysqli_query($conn, $query);

        // Crear el enlace de recuperación
        $enlace = "http://tudominio.com/restablecer.php?token=$token";

        // Enviar el correo electrónico
        $asunto = "Recuperación de Contraseña";
        $mensaje = "Haz clic en el siguiente enlace para restablecer tu contraseña: $enlace";
        $headers = "From: no-reply@tudominio.com";

        if (mail($email, $asunto, $mensaje, $headers)) {
            echo "<p style='color: green; text-align: center;'>Se ha enviado un enlace de recuperación a tu correo.</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>Error al enviar el correo.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>El correo no está registrado.</p>";
    }
}
?>