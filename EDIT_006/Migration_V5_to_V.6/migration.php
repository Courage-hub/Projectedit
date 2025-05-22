<?php
session_start();

$errorMsg = null; // Para mostrar errores en la interfaz

// Función para conectar a la base de datos
function connectDB($host, $user, $password, $dbname = null)
{
    $conn = @new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    return $conn;
}

// Procesar formulario inicial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['source'])) {
    try {
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
            if (!$target->query("DROP DATABASE IF EXISTS " . $_POST['new_db'])) {
                throw new Exception("No se pudo eliminar la base de datos destino: " . $target->error);
            }
        }

        // Crear la base de datos
        if (!$target->query("CREATE DATABASE IF NOT EXISTS " . $_POST['new_db'])) {
            throw new Exception("No se pudo crear la base de datos destino: " . $target->error);
        }
        if (!$target->select_db($_POST['new_db'])) {
            throw new Exception("No se pudo seleccionar la base de datos destino: " . $target->error);
        }

        // Copiar estructura y datos
        $tables = $source->query("SHOW TABLES");
        if (!$tables) {
            throw new Exception("No se pudieron obtener las tablas de la base de datos origen: " . $source->error);
        }
        while ($table = $tables->fetch_array()) {
            $create = $source->query("SHOW CREATE TABLE " . $table[0]);
            if (!$create) {
                throw new Exception("No se pudo obtener la estructura de la tabla {$table[0]}: " . $source->error);
            }
            $createArr = $create->fetch_array();
            if (!$target->query($createArr[1])) {
                throw new Exception("No se pudo crear la tabla {$table[0]} en destino: " . $target->error);
            }

            // Copiar datos
            $data = $source->query("SELECT * FROM " . $table[0]);
            if ($data) {
                while ($row = $data->fetch_assoc()) {
                    $columns = implode("`, `", array_keys($row));
                    $values = implode("', '", array_map([$target, 'real_escape_string'], array_values($row)));
                    if (!$target->query("INSERT INTO " . $table[0] . " (`$columns`) VALUES ('$values')")) {
                        throw new Exception("Error insertando datos en {$table[0]}: " . $target->error);
                    }
                }
            }
        }

        /****************************************************
         * SQL QUE SE EJECUTA AUTOMÁTICAMENTE AL COPIAR
         ****************************************************/
        $sql = "
            ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT 'TBD';
            ALTER TABLE fpproject ADD COLUMN titulo VARCHAR(70) DEFAULT NULL;
        ";
        if (!$target->multi_query($sql)) {
            throw new Exception("Error ejecutando SQL adicional: " . $target->error);
        }
        /****************************************************/

        $_SESSION['new_db_conn'] = [
            'host' => $_POST['target']['host'],
            'user' => $_POST['target']['user'],
            'password' => $_POST['target']['password'],
            'dbname' => $_POST['new_db']
        ];
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        // Limpiar sesión para evitar estados inconsistentes
        session_unset();
    }
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
        body {
            background: #f4f6fa;
            transition: background 0.3s, color 0.3s;
        }
        .card,
        .btn,
        .form-control,
        .input-group,
        .table,
        .alert,
        .toast {
            border-radius: 0.75rem !important;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }
        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        .shadow-lg {
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.08)!important;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-lg, .btn {
            font-size: 1.1rem;
        }
        .card {
            border: none;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .alert-info {
            background: #f8fbff;
            border-color: #b6e0fe;
        }
        /* --- SWEET MODE 2 (Dark Mode) --- */
        body.sweet-mode2 {
            background: #181c24 !important;
            color: #e3e6ed !important;
        }
        body.sweet-mode2 .card,
        body.sweet-mode2 .card-header,
        body.sweet-mode2 .card-footer {
            background: #232837 !important;
            color: #e3e6ed !important;
            border-color: #232837 !important;
        }
        body.sweet-mode2 .form-control,
        body.sweet-mode2 .input-group-text {
            background: #232837 !important;
            color: #e3e6ed !important;
            border-color: #353b4d !important;
        }
        body.sweet-mode2 .table {
            background: #232837 !important;
            color: #e3e6ed !important;
        }
        body.sweet-mode2 .table th,
        body.sweet-mode2 .table td {
            background: #232837 !important;
            color: #e3e6ed !important;
            border-color: #353b4d !important;
        }
        body.sweet-mode2 .table-light {
            background: #232837 !important;
            color: #e3e6ed !important;
        }
        body.sweet-mode2 .alert-info {
            background: #232837 !important;
            color: #e3e6ed !important;
            border-color: #353b4d !important;
        }
        body.sweet-mode2 .btn-primary,
        body.sweet-mode2 .btn-success,
        body.sweet-mode2 .btn-warning,
        body.sweet-mode2 .btn-secondary {
            color: #fff !important;
            border: none;
        }
        body.sweet-mode2 .btn-primary { background: #4f8cff !important; }
        body.sweet-mode2 .btn-success { background: #2ecc71 !important; }
        body.sweet-mode2 .btn-warning { background: #f1c40f !important; color: #232837 !important; }
        body.sweet-mode2 .btn-secondary { background: #636e88 !important; }
        body.sweet-mode2 .btn-outline-secondary {
            background: transparent !important;
            color: #e3e6ed !important;
            border-color: #636e88 !important;
        }
        body.sweet-mode2 .toast {
            background: #232837 !important;
            color: #e3e6ed !important;
            border-color: #353b4d !important;
        }
        body.sweet-mode2 pre {
            background: #181c24 !important;
            color: #e3e6ed !important;
        }
        body.sweet-mode2 .bg-white { background: #232837 !important; }
        body.sweet-mode2 .bg-light { background: #232837 !important; }
        body.sweet-mode2 .text-primary { color: #4f8cff !important; }
        body.sweet-mode2 .text-success { color: #2ecc71 !important; }
        body.sweet-mode2 .text-warning { color: #f1c40f !important; }
        body.sweet-mode2 .text-secondary { color: #636e88 !important; }
        body.sweet-mode2 .border { border-color: #353b4d !important; }
        /* --- END SWEET MODE 2 --- */
        @media (max-width: 767px) {
            .card-body, .card-header, .card-footer {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .container-fluid, .container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
        }
        /* Botón flotante para modo oscuro */
        #toggle-sweetmode2 {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            background: #232837;
            color: #f1c40f;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            transition: background 0.3s, color 0.3s;
        }
        #toggle-sweetmode2:hover {
            background: #353b4d;
            color: #ffe066;
        }
    </style>
</head>

<body>
    <button id="toggle-sweetmode2" title="Modo oscuro/claro">
        <i class="fas fa-moon"></i>
    </button>
    <div class="container py-5">
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger shadow-sm mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-9">
                <?php if (!isset($_SESSION['source'])) { ?>
                    <!-- Improved initial form -->
                    <div class="card shadow-lg">
                        <div class="card-header bg-white border-bottom-0">
                            <h2 class="mb-0 text-center text-primary">
                                <i class="fas fa-database me-2"></i> Database migration from EDIT_V.5 to EDIT_V.6
                            </h2>
                        </div>
                        <div class="card-body p-4">
                            <form method="post">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h3 class="mb-0 h5"><i class="fas fa-server me-2"></i> Server 1 (V.5)</h3>
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
                                                            <i class="fas fa-eye"></i>
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
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-header bg-success text-white">
                                                <h3 class="mb-0 h5"><i class="fas fa-server me-2"></i> Server 2 (V.6)</h3>
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
                                                            <i class="fas fa-eye"></i>
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

                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-primary btn-lg px-5" id="startMigration">
                                        <i class="fas fa-arrow-right me-2"></i> Start Migration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php } elseif (!isset($_POST['migrate'])) { ?>
                    <!-- Database editor -->
                    <div class="card shadow-lg">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h2 class="mb-0 h4 text-primary"><i class="fas fa-edit me-2"></i> Database Editor</h2>
                            <div>
                                <button id="save-changes" class="btn btn-warning me-2">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                <a href="?reset=1" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-4">
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

                        <div class="card-footer text-end bg-white border-top-0">
                            <form method="post">
                                <button type="submit" name="migrate" class="btn btn-success btn-lg px-4">
                                    <i class="fas fa-check-circle me-2"></i> Migrate Database
                                </button>
                            </form>
                        </div>
                    </div>

                <?php } else { ?>
                    <!-- Migration confirmation -->
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h2 class="mb-0 h4"><i class="fas fa-check-circle me-2"></i> Migration Complete</h2>
                        </div>
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                                <h3 class="mt-3 display-6">Migration Successful!</h3>
                            </div>

                            <div class="alert alert-info text-start shadow-sm p-4">
                                <h5 class="alert-heading"><i class="fas fa-clipboard-list me-2"></i>Summary:</h5>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Source:</strong>
                                        <?= $_SESSION['source']['dbname'] ?>@<?= $_SESSION['source']['host'] ?></li>
                                    <li><strong>Target:</strong>
                                        <?= $_SESSION['new_db'] ?>@<?= $_SESSION['target']['host'] ?></li>
                                    <li class="mt-2"><strong>Executed SQL:</strong>
                                        <pre class="mt-2 p-3 bg-white border rounded small">ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) DEFAULT 'TBD';</pre>
                                    </li>
                                </ul>
                            </div>

                            <a href="?reset=1" class="btn btn-primary btn-lg mt-4 px-5">
                                <i class="fas fa-sync-alt me-2"></i> New Migration
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script>
        // Sweet Mode 2 toggle
        (function() {
            const btn = document.getElementById('toggle-sweetmode2');
            const icon = btn.querySelector('i');
            function setMode(dark) {
                if (dark) {
                    document.body.classList.add('sweet-mode2');
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    document.body.classList.remove('sweet-mode2');
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
                localStorage.setItem('sweetmode2', dark ? '1' : '0');
            }
            btn.addEventListener('click', function() {
                setMode(!document.body.classList.contains('sweet-mode2'));
            });
            // Auto-load mode
            if (localStorage.getItem('sweetmode2') === '1') setMode(true);
        })();
    </script>
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