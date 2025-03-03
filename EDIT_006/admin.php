<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Procesar la aprobación o denegación de registros
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $accion = $_POST['accion'];

    if ($accion == 'aprobar') {
        $query = "UPDATE usuarios SET aprobado = TRUE WHERE id = $id";
        mysqli_query($conn, $query);
        echo "<p>Usuario aprobado correctamente.</p>";
    } elseif ($accion == 'denegar') {
        $query = "DELETE FROM usuarios WHERE id = $id";
        mysqli_query($conn, $query);
        echo "<p>Usuario denegado correctamente.</p>";
    }
}

// Obtener registros pendientes de aprobación
$query = "SELECT * FROM usuarios WHERE aprobado = FALSE";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Aprobar Registros</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .acciones {
            display: flex;
            gap: 10px;
        }

        /* Estilos para las pestañas */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        .tab button:hover {
            background-color: #ddd;
        }

        .tab button.active {
            background-color: #ccc;
        }

        .tabcontent {
            display: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-top: none;
        }

        /* Estilos para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .editable {
            cursor: pointer;
        }

        .editable:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="tab">
        <button class="tablinks" onclick="openTab(event, 'Inicio')" id="defaultOpen">Register</button>
        <button class="tablinks" onclick="openTab(event, 'Usuarios')">User</button>
    </div>
    <div id="Inicio" class="tabcontent">
        <h1>Pending Records</h1>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Last name</th>
                    <th>Departament</th>
                    <th>Email</th>
                    <th>Shares</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nombre']; ?></td>
                        <td><?php echo $row['apellido']; ?></td>
                        <td><?php echo $row['departamento']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td class="acciones">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="accion" value="aprobar">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="accion" value="denegar">Disable</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>There are no records pending approval.</p>
        <?php endif; ?>
    </div>
    <div id="Usuarios" class="tabcontent">
        <h2>User Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Last name</th>
                    <th>Departament</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Shares</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include("conexion.php");

                // Obtener la lista de usuarios
                $query = "SELECT * FROM usuarios";
                $result = mysqli_query($conn, $query);

                while ($usuario = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                    <td class='editable' data-id='{$usuario['id']}' data-field='nombre'>{$usuario['nombre']}</td>
                    <td class='editable' data-id='{$usuario['id']}' data-field='apellido'>{$usuario['apellido']}</td>
                    <td class='editable' data-id='{$usuario['id']}' data-field='departamento'>{$usuario['departamento']}</td>
                    <td class='editable' data-id='{$usuario['id']}' data-field='email'>{$usuario['email']}</td>
                    <td class='editable' data-id='{$usuario['id']}' data-field='rol'>{$usuario['rol']}</td>
                    <td>
                        <button onclick='aprobarUsuario({$usuario['id']})'>Approve</button>
                        <button onclick='deshabilitarUsuario({$usuario['id']})'>Disable</button>
                    </td>
                </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript para las pestañas y la edición en línea -->
    <script>
        // Funcionalidad de las pestañas
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Abrir la pestaña por defecto
        document.getElementById("defaultOpen").click();

        // Funcionalidad de edición en línea
        document.querySelectorAll('.editable').forEach(cell => {
            cell.addEventListener('click', () => {
                const id = cell.getAttribute('data-id');
                const field = cell.getAttribute('data-field');
                const currentValue = cell.innerText;
                const newValue = prompt(`Editar ${field}:`, currentValue);

                if (newValue !== null && newValue !== currentValue) {
                    fetch('actualizar_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id, field, value: newValue })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                cell.innerText = newValue;
                            } else {
                                alert('Error updating the user.');
                            }
                        });
                }
            });
        });

        // Funcionalidad para aprobar/deshabilitar usuarios
        function aprobarUsuario(id) {
            if (confirm('Are you sure you approve this user?')) {
                fetch('actualizar_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, field: 'aprobado', value: 1 })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User successfully approved.');
                            location.reload();
                        } else {
                            alert('Error when approving the user.');
                        }
                    });
            }
        }

        function deshabilitarUsuario(id) {
            if (confirm('Are you sure to disable this user?')) {
                fetch('actualizar_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, field: 'aprobado', value: 0 })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User disabled correctly.');
                            location.reload();
                        } else {
                            alert('Error when disabling the user.');
                        }
                    });
            }
        }
    </script>



























</body>

</html>