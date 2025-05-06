<?php
session_start();

// Función para conectar a la base de datos
function connectDB($host, $user, $password, $dbname = null)
{
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    return $conn;
}

// Procesar formulario inicial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['source'])) {
    $_SESSION['source'] = $_POST['source'];
    $_SESSION['target'] = $_POST['target'];
    $_SESSION['new_db'] = $_POST['new_db'];

    // Conectar a la base de datos fuente (origen)
    $source = connectDB(
        $_POST['source']['host'],
        $_POST['source']['user'],
        $_POST['source']['password'],
        $_POST['source']['dbname']
    );

    // Conectar al servidor destino
    $target = connectDB(
        $_POST['target']['host'],
        $_POST['target']['user'],
        $_POST['target']['password']
    );

    // Crear nueva base de datos en el destino
    if ($_POST['db_exists'] === 'yes') {
        // Eliminar la base de datos si ya existe
        $target->query("DROP DATABASE IF EXISTS " . $_POST['new_db']);
        echo "Base de datos eliminada y recreada.";
    }

    // Crear la base de datos
    $target->query("CREATE DATABASE IF NOT EXISTS " . $_POST['new_db']);
    $target->select_db($_POST['new_db']);

    // Copiar estructura y datos
    $tables = $source->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        $create = $source->query("SHOW CREATE TABLE " . $table[0])->fetch_array();
        $target->query($create[1]);

        // Copiar datos
        $data = $source->query("SELECT * FROM " . $table[0]);
        while ($row = $data->fetch_assoc()) {
            $columns = implode("`, `", array_keys($row));
            $values = implode("', '", array_values($row));
            $target->query("INSERT INTO " . $table[0] . " (`$columns`) VALUES ('$values')");
        }
    }

    /****************************************************
     * SQL QUE SE EJECUTA AUTOMÁTICAMENTE AL COPIAR
     ****************************************************/
    $sql = "
        ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT 'TBD';
    ";
    $target->multi_query($sql);
    /****************************************************/

    $_SESSION['new_db_conn'] = [
        'host' => $_POST['target']['host'],
        'user' => $_POST['target']['user'],
        'password' => $_POST['target']['password'],
        'dbname' => $_POST['new_db']
    ];
}

// Guardar cambios editados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    if (isset($_SESSION['new_db_conn'])) {
        $db = connectDB(
            $_SESSION['new_db_conn']['host'],
            $_SESSION['new_db_conn']['user'],
            $_SESSION['new_db_conn']['password'],
            $_SESSION['new_db_conn']['dbname']
        );

        foreach ($_POST['changes'] as $change) {
            $table = $db->real_escape_string($change['table']);
            $id = (int) $change['id'];
            $column = $db->real_escape_string($change['column']);
            $value = $db->real_escape_string($change['value']);

            $db->query("UPDATE $table SET `$column` = '$value' WHERE id = $id");
        }
        echo "Cambios guardados correctamente";
        exit;
    }
}

// Procesar migración final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate'])) {
    $_SESSION['migration_success'] = true;
}

// Limpiar sesión
if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Database Migrator</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css"> <!-- Font Awesome -->
    <style>
        /* Bordes más redondeados para toda la página */
        .card, .btn, .form-control, .input-group, .table, .alert, .toast{
            border-radius:1rem !important; /* Ajusta el valor según lo que necesites */
        }

        .card-header{
            border-radius: 1rem 1rem 0 0 !important; /* Bordes redondeados solo en la parte superior */
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if (!isset($_SESSION['source'])) { ?>
                    <!-- Improved initial form -->
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h2 class="mb-0 text-center"><i class="bi bi-database"></i> Database migration from EDIT_V.5 to
                                EDIT_V.6</h2>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h3 class="mb-0"><i class="fa fa-server"></i> Server 1 (V.5)</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Host</label>
                                                    <input type="text" name="source[host]" class="form-control"
                                                        placeholder="E.g.: localhost or IP" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">User</label>
                                                    <input type="text" name="source[user]" class="form-control"
                                                        placeholder="MySQL User" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="source[password]" class="form-control"
                                                            placeholder="Password" id="sourcePassword">
                                                        <button class="btn btn-outline-secondary password-toggle"
                                                            type="button" data-target="sourcePassword">
                                                            <i class=""></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Database</label>
                                                    <input type="text" name="source[dbname]" class="form-control"
                                                        placeholder="Database name" value="edit_0005" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h3 class="mb-0"><i class="fa fa-server"></i> Server 2 (V.6)</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Host</label>
                                                    <input type="text" name="target[host]" class="form-control"
                                                        placeholder="E.g.: localhost or IP" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">User</label>
                                                    <input type="text" name="target[user]" class="form-control"
                                                        placeholder="MySQL User" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="target[password]" class="form-control"
                                                            placeholder="Password" id="targetPassword">
                                                        <button class="btn btn-outline-secondary password-toggle"
                                                            type="button" data-target="targetPassword">
                                                            <i class=""></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">New Database</label>
                                                    <input type="text" name="new_db" class="form-control"
                                                        placeholder="Name for the new database" value="edit_0006" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary btn-lg" id="startMigration">
                                        <i class="fa fa-arrow-right"></i> Start Migration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php } elseif (!isset($_POST['migrate'])) { ?>
                    <!-- Database editor -->
                    <div class="card shadow-lg">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><i class="bi bi-pencil-square"></i> Database Editor</h2>
                            <div>
                                <button id="save-changes" class="btn btn-warning me-2">
                                    <i class="fa fa-save"></i> Save Changes
                                </button>
                                <a href="?reset=1" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
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
                                    <div class="card mb-4">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0"><?= htmlspecialchars($table[0]) ?></h3>
                                            <button class="btn btn-sm btn-outline-primary rename-btn"
                                                data-table="<?= htmlspecialchars($table[0]) ?>">
                                                <i class="fa fa-pencil-alt"></i> Rename
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive table-container">
                                                <table class="table table-hover table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <?php
                                                            $columns = $new_db->query("DESCRIBE " . $table[0]);
                                                            while ($col = $columns->fetch_assoc()) {
                                                                echo "<th>" . htmlspecialchars($col['Field']) . " <small class='text-muted'>(" . htmlspecialchars($col['Type']) . ")</small></th>";
                                                            }
                                                            ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $data = $new_db->query("SELECT * FROM " . $table[0] . " LIMIT 50");
                                                        while ($row = $data->fetch_assoc()) {
                                                            echo "<tr>";
                                                            foreach ($row as $column => $value) {
                                                                echo "<td class='edit-cell' 
                                                                  contenteditable='true' 
                                                                  data-table='" . htmlspecialchars($table[0]) . "' 
                                                                  data-id='" . ($row['id'] ?? '') . "' 
                                                                  data-column='" . htmlspecialchars($column) . "'>"
                                                                    . htmlspecialchars($value) . "</td>";
                                                            }
                                                            echo "</tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                            }
                            ?>
                        </div>

                        <div class="card-footer text-end">
                            <form method="post">
                                <button type="submit" name="migrate" class="btn btn-success btn-lg">
                                    <i class="fa fa-check-circle"></i> Migrate Database
                                </button>
                            </form>
                        </div>
                    </div>

                <?php } else { ?>
                    <!-- Migration confirmation -->
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h2 class="mb-0"><i class="bi bi-check2-circle"></i> Migration Complete</h2>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fa fa-check-circle text-success" style="font-size: 5rem;"></i>
                                <h3 class="mt-3">Migration Successful!</h3>
                            </div>

                            <div class="alert alert-info text-start">
                                <h5>Summary:</h5>
                                <ul>
                                    <li><strong>Source:</strong>
                                        <?= $_SESSION['source']['dbname'] ?>@<?= $_SESSION['source']['host'] ?></li>
                                    <li><strong>Target:</strong>
                                        <?= $_SESSION['new_db'] ?>@<?= $_SESSION['target']['host'] ?></li>
                                    <li><strong>Executed SQL:</strong>
                                        <pre
                                            class="mt-2 p-2 bg-light rounded">ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT 'TBD';</pre>
                                    </li>
                                </ul>
                            </div>

                            <a href="?reset=1" class="btn btn-primary btn-lg mt-3">
                                <i class="bi bi-arrow-repeat"></i> New Migration
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Toggle para mostrar/ocultar contraseñas
            $('.password-toggle').click(function () {
                const target = $(this).data('target');
                const input = $('#' + target);
                const icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Guardar cambios
            $('#save-changes').click(function () {
                let changes = [];
                $('td[contenteditable="true"]').each(function () {
                    changes.push({
                        table: $(this).data('table'),
                        id: $(this).data('id'),
                        column: $(this).data('column'),
                        value: $(this).text()
                    });
                });

                if (changes.length > 0) {
                    $.post(window.location.href, {
                        save_changes: true,
                        changes: changes
                    }, function (response) {
                        const toast = `<div class="toast align-items-center text-white bg-success position-fixed bottom-0 end-0 m-3" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i> ${response}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;

                        $('body').append(toast);
                        $('.toast').toast({ autohide: true, delay: 3000 }).toast('show');
                    });
                } else {
                    const toast = `<div class="toast align-items-center text-white bg-warning position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-exclamation-triangle me-2"></i> No hay cambios para guardar
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

                    $('body').append(toast);
                    $('.toast').toast({ autohide: true, delay: 3000 }).toast('show');
                }
            });

            // Renombrar tabla
            $('.rename-btn').click(function () {
                const tableName = $(this).data('table');
                const newName = prompt("Nuevo nombre para la tabla " + tableName + ":", tableName);

                if (newName && newName !== tableName) {
                    if (confirm(`¿Cambiar ${tableName} por ${newName}?`)) {
                        $.post(window.location.href, {
                            rename_table: true,
                            old_name: tableName,
                            new_name: newName
                        }, function () {
                            location.reload();
                        });
                    }
                }
            });
        });
    </script>
    <script>
    document.getElementById('startMigration').addEventListener('click', function () {
        const userResponse = confirm("Is the database already created on server 2?s");
        const form = document.querySelector('form');

        if (userResponse) {
            // Si el usuario dice que la base de datos ya está creada
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'db_exists';
            input.value = 'yes';
            form.appendChild(input);
        } else {
            // Si el usuario dice que no sabe o no está creada
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'db_exists';
            input.value = 'no';
            form.appendChild(input);
        }

        form.submit();
    });
</script>
</body>

</html>