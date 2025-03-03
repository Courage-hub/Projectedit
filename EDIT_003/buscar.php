<?php
include("conexion.php");

// Verifica si se han enviado datos de búsqueda
if (isset($_GET['id']) || isset($_GET['instruccion'])) {
    $id = $_GET['id'];
    $instruccion = $_GET['instruccion'];

    // Consulta SQL para buscar registros
    $query = "SELECT * FROM fpproject WHERE id LIKE '%$id%' AND instruccion LIKE '%$instruccion%'";
    $result = mysqli_query($conn, $query);

    // Muestra los resultados de la búsqueda en una nueva tabla
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Instruction</th>";
    echo "<th>Date</th>";
    echo "<th>Shares</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";



    while ($filas = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $filas['id'] . "</td>";
        echo "<td>" . $filas['instruccion'] . "</td>";
        echo "<td>" . date('d/m/Y H:i:s', strtotime($filas['date'])) . "</td>";
        echo "<td><a href='editor.php?id=" . $filas['id'] . "'>Edit</a></td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

    mysqli_free_result($result);
} else {
    echo "No se han proporcionado criterios de búsqueda.";
}

mysqli_close($conn);
?>
<br>
<center> <a href="index.php"><button class="danger">← Back</button></a>
</center>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Poppins", sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* Mínimo alto de la ventana */
    }

    table {
        margin-top: 5%;
        width: 100%;

    }

    td,
    tr,
    th {
        border: 1px solid #dddddd;
        text-align: center;
    }

    .id {
        width: 10px;
    }


    .title {
        width: 1000px;
    }

    .name {
        width: 50px;
    }

    .date {
        width: 10px;
    }

    .shares {
        width: 25px;
    }

    .danger {
        
        font-family: "Poppins", sans-serif;
        border: none;
        color: white;
        padding: 14px 28px;
        cursor: pointer;
        border-radius: 5px;
        display: inline-flex;
    }

    .danger {
        background-color: #f44336;
    }

    .danger:hover {
        background: #da190b;
    }
</style>