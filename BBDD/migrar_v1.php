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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrador Profesional de Bases de Datos</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-icons.css">
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
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }

        .card-header {
            background-color: var(--secondary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-warning {
            background-color: #f39c12;
            border-color: #f39c12;
        }

        .edit-cell {
            transition: background-color 0.3s;
        }

        .edit-cell:focus {
            background-color: #fffde7;
            outline: 2px solid var(--primary-color);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if (!isset($_SESSION['source'])) { ?>
                    <!-- Formulario inicial mejorado -->
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h2 class="mb-0 text-center"><i class="bi bi-database"></i> Configurar Migración</h2>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary text-white">
                                                <h3 class="mb-0"><i class=""></i> Servidor Origen</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Host</label>
                                                    <input type="text" name="source[host]" class="form-control"
                                                        placeholder="Ej: localhost o IP" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Usuario</label>
                                                    <input type="text" name="source[user]" class="form-control"
                                                        placeholder="Usuario MySQL" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contraseña</label>
                                                    <div class="input-group">
                                                        <input type="password" name="source[password]" class="form-control"
                                                            placeholder="Contraseña" id="sourcePassword">
                                                        <button class="btn btn-outline-secondary password-toggle"
                                                            type="button" data-target="sourcePassword">
                                                            <i class=""></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Base de datos</label>
                                                    <input type="text" name="source[dbname]" class="form-control"
                                                        placeholder="Nombre de la base de datos" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h3 class="mb-0"><i class=""></i> Servidor Destino</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Host</label>
                                                    <input type="text" name="target[host]" class="form-control"
                                                        placeholder="Ej: localhost o IP" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Usuario</label>
                                                    <input type="text" name="target[user]" class="form-control"
                                                        placeholder="Usuario MySQL" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contraseña</label>
                                                    <div class="input-group">
                                                        <input type="password" name="target[password]" class="form-control"
                                                            placeholder="Contraseña" id="targetPassword">
                                                        <button class="btn btn-outline-secondary password-toggle"
                                                            type="button" data-target="targetPassword">
                                                            <i class=""></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nueva base de datos</label>
                                                    <input type="text" name="new_db" class="form-control"
                                                        placeholder="Nombre para la nueva base de datos" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="  "></i>Continuar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php } elseif (!isset($_POST['migrate'])) { ?>
                    <!-- Editor de base de datos -->
                    <div class="card shadow-lg">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h2 class="mb-0"><i class="bi bi-pencil-square"></i> Editor de Base de Datos</h2>
                            <div>
                                <button id="save-changes" class="btn btn-warning me-2">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                                <a href="?reset=1" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
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
                                                <i class="bi bi-pencil"></i> Renombrar
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
                                    <i class="bi bi-check-circle"></i> Migrar Base de Datos
                                </button>
                            </form>
                        </div>
                    </div>

                <?php } else { ?>
                    <!-- Confirmación de migración -->
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h2 class="mb-0"><i class="bi bi-check2-circle"></i> Migración Completa</h2>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                                <h3 class="mt-3">¡Migración exitosa!</h3>
                            </div>

                            <div class="alert alert-info text-start">
                                <h5>Resumen:</h5>
                                <ul>
                                    <li><strong>Origen:</strong>
                                        <?= $_SESSION['source']['dbname'] ?>@<?= $_SESSION['source']['host'] ?></li>
                                    <li><strong>Destino:</strong>
                                        <?= $_SESSION['new_db'] ?>@<?= $_SESSION['target']['host'] ?></li>
                                    <li><strong>SQL ejecutado:</strong>
                                        <pre
                                            class="mt-2 p-2 bg-light rounded">ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT 'TBD';</pre>
                                    </li>
                                </ul>
                            </div>

                            <a href="?reset=1" class="btn btn-primary btn-lg mt-3">
                                <i class="bi bi-arrow-repeat"></i> Nueva Migración
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
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
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
</body>

</html>