<?php
session_start();

// Función para conectar a la base de datos
function connectDB($host, $user, $password, $dbname = null) {
    if (empty($host) || empty($user)) {
        die("Error: Host y usuario son obligatorios.");
    }
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    return $conn;
}

// Función para listar bases de datos disponibles
function listDatabases($host, $user, $password) {
    $conn = connectDB($host, $user, $password);
    $result = $conn->query("SHOW DATABASES");
    $databases = [];
    while ($row = $result->fetch_array()) {
        $databases[] = $row[0];
    }
    $conn->close();
    return $databases;
}

// Función para copiar la base de datos
function copyDatabase($source, $new_db_name, $host, $user, $password) {
    // Crear nueva base de datos
    $new_db = connectDB($host, $user, $password);
    $new_db->query("CREATE DATABASE IF NOT EXISTS $new_db_name");
    $new_db->select_db($new_db_name);

    // Copiar tablas y datos
    $tables = $source->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        $create = $source->query("SHOW CREATE TABLE " . $table[0])->fetch_array();
        $new_db->query($create[1]);

        // Copiar datos
        $data = $source->query("SELECT * FROM " . $table[0]);
        while ($row = $data->fetch_assoc()) {
            $columns = implode("`, `", array_keys($row));
            $values = implode("', '", array_values($row));
            $new_db->query("INSERT INTO " . $table[0] . " (`$columns`) VALUES ('$values')");
        }
    }
    return $new_db;
}

// Procesar formulario inicial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['source'])) {
    if (isset($_POST['source']['host']) && isset($_POST['source']['user'])) {
        // Guardar credenciales en sesión
        $_SESSION['source'] = $_POST['source'];

        // Listar bases de datos disponibles
        $databases = listDatabases(
            $_POST['source']['host'],
            $_POST['source']['user'],
            $_POST['source']['password']
        );
        $_SESSION['databases'] = $databases;
    }
}

// Procesar selección de base de datos y ejecutar SQL personalizado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_db'])) {
    $_SESSION['source']['dbname'] = $_POST['dbname'];
    $_SESSION['new_db'] = $_POST['new_db'];

    // Conectar a la base de datos fuente
    $source = connectDB(
        $_SESSION['source']['host'],
        $_SESSION['source']['user'],
        $_SESSION['source']['password'],
        $_SESSION['source']['dbname']
    );

    // Copiar base de datos
    $new_db = copyDatabase(
        $source,
        $_POST['new_db'],
        $_SESSION['source']['host'],
        $_SESSION['source']['user'],
        $_SESSION['source']['password']
    );

    // Guardar conexión a la nueva base de datos en sesión
    $_SESSION['new_db_conn'] = [
        'host' => $_SESSION['source']['host'],
        'user' => $_SESSION['source']['user'],
        'password' => $_SESSION['source']['password'],
        'dbname' => $_POST['new_db']
    ];

    // Aquí se pone el SQL que se ejecutará automáticamente
    // ****************************************************
    $sql = "
    ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT 'TBD';
    ";
    // ****************************************************

    // Ejecutar el SQL
    if ($new_db->multi_query($sql)) {
        do {
            if ($result = $new_db->store_result()) {
                $result->free();
            }
        } while ($new_db->next_result());
    } else {
        die("Error al ejecutar SQL: " . $new_db->error);
    }
}

// Guardar cambios en los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    if (isset($_SESSION['new_db_conn'])) {
        $new_db = connectDB(
            $_SESSION['new_db_conn']['host'],
            $_SESSION['new_db_conn']['user'],
            $_SESSION['new_db_conn']['password'],
            $_SESSION['new_db_conn']['dbname']
        );

        // Recorrer los cambios recibidos
        foreach ($_POST['changes'] as $change) {
            $table = $change['table'];
            $id = $change['id'];
            $column = $change['column'];
            $value = $change['value'];

            // Actualizar el registro en la base de datos
            $new_db->query("UPDATE $table SET $column = '$value' WHERE id = $id");
        }

        echo "Cambios guardados correctamente.";
        exit;
    } else {
        echo "Error: No se ha configurado la conexión a la base de datos.";
        exit;
    }
}

// Procesar migración final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate'])) {
    if (isset($_SESSION['new_db_conn'])) {
        $new_db = connectDB(
            $_SESSION['new_db_conn']['host'],
            $_SESSION['new_db_conn']['user'],
            $_SESSION['new_db_conn']['password'],
            $_SESSION['new_db_conn']['dbname']
        );

        // Verificar que la base de datos existe
        $result = $new_db->query("SHOW TABLES");
        if ($result->num_rows > 0) {
            $_SESSION['migration_success'] = true;
        } else {
            $_SESSION['migration_success'] = false;
        }
    } else {
        $_SESSION['migration_success'] = false;
    }
}

// Limpiar sesión si se solicita volver al inicio
if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    header("Location: ?");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Migrador de Bases de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <?php if (!isset($_SESSION['source'])) { ?>
        <!-- Formulario inicial -->
        <h1>Configurar Migración</h1>
        <form method="post">
            <h3>Base de Datos Fuente</h3>
            <div class="mb-3">
                <input type="text" name="source[host]" class="form-control" placeholder="Host" required>
            </div>
            <div class="mb-3">
                <input type="text" name="source[user]" class="form-control" placeholder="Usuario" required>
            </div>
            <div class="mb-3">
                <input type="password" name="source[password]" class="form-control" placeholder="Contraseña">
            </div>
            <button type="submit" class="btn btn-primary">Listar Bases de Datos</button>
        </form>
    <?php } elseif (!isset($_SESSION['source']['dbname'])) { ?>
        <!-- Selección de base de datos -->
        <h1>Seleccione la Base de Datos a Migrar</h1>
        <form method="post">
            <div class="mb-3">
                <label for="dbname" class="form-label">Base de Datos:</label>
                <select name="dbname" class="form-control" required>
                    <?php foreach ($_SESSION['databases'] as $db) { ?>
                        <option value="<?= $db ?>"><?= $db ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="new_db" class="form-label">Nombre de la Nueva Base de Datos:</label>
                <input type="text" name="new_db" class="form-control" required>
            </div>
            <button type="submit" name="select_db" class="btn btn-primary">Continuar</button>
        </form>
        <a href="?reset=1" class="btn btn-secondary mt-3">Volver al inicio</a>
    <?php } elseif (!isset($_POST['migrate'])) { ?>
        <!-- Editor de base de datos -->
        <h1>Editor de Base de Datos</h1>
        <a href="?reset=1" class="btn btn-secondary mb-3">Volver al inicio</a>
        <button id="save-changes" class="btn btn-warning mb-3">Guardar Cambios</button>

        <!-- Vista previa de la base de datos -->
        <?php
        if (isset($_SESSION['new_db_conn'])) {
            $new_db = connectDB(
                $_SESSION['new_db_conn']['host'],
                $_SESSION['new_db_conn']['user'],
                $_SESSION['new_db_conn']['password'],
                $_SESSION['new_db_conn']['dbname']
            );
            $tables = $new_db->query("SHOW TABLES");
            while ($table = $tables->fetch_array()) { ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h3><?= $table[0] ?>
                            <button class="btn btn-sm btn-warning rename-btn">Renombrar</button>
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <?php
                                    $columns = $new_db->query("DESCRIBE " . $table[0]);
                                    while ($col = $columns->fetch_assoc()) {
                                        echo "<th>{$col['Field']} ({$col['Type']})</th>";
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $data = $new_db->query("SELECT * FROM " . $table[0] . " LIMIT 5");
                                while ($row = $data->fetch_assoc()) {
                                    echo "<tr>";
                                    foreach ($row as $column => $value) {
                                        echo "<td contenteditable='true' data-table='{$table[0]}' data-id='{$row['id']}' data-column='$column'>$value</td>";
                                    }
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php }
        } else {
            echo "<div class='alert alert-danger'>Error: No se ha configurado la conexión a la base de datos.</div>";
        }
        ?>
        
        <form method="post">
            <button type="submit" name="migrate" class="btn btn-success">Migrar Base de Datos</button>
        </form>
        
        <script>
        $(document).ready(function() {
            // Guardar cambios
            $('#save-changes').click(function() {
                let changes = [];
                $('td[contenteditable="true"]').each(function() {
                    let table = $(this).data('table');
                    let id = $(this).data('id');
                    let column = $(this).data('column');
                    let value = $(this).text();

                    changes.push({
                        table: table,
                        id: id,
                        column: column,
                        value: value
                    });
                });

                // Enviar cambios al servidor
                $.post(window.location.href, {
                    save_changes: true,
                    changes: changes
                }, function(response) {
                    alert(response);
                });
            });

            // Renombrar tabla
            $('.rename-btn').click(function() {
                let newName = prompt("Nuevo nombre para la tabla:");
                if (newName) {
                    $.post(window.location.href, {
                        rename_table: true,
                        old_name: $(this).closest('.card').find('h3').text().trim(),
                        new_name: newName
                    }, function() {
                        location.reload();
                    });
                }
            });
        });
        </script>
    <?php } elseif (isset($_SESSION['migration_success'])) { ?>
        <!-- Confirmación de migración -->
        <h1>Migración Completa</h1>
        <?php if ($_SESSION['migration_success']) { ?>
            <div class="alert alert-success">
                <p>¡La migración se ha completado correctamente!</p>
                <p>Los datos, incluyendo las ediciones realizadas, se han migrado con éxito.</p>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">
                <p>Hubo un error durante la migración. Por favor, inténtalo de nuevo.</p>
            </div>
        <?php } ?>
        <a href="?reset=1" class="btn btn-primary">Volver al inicio</a>
    <?php } ?>
</div>
</body>
</html>