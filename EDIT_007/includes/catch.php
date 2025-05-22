<?php
// includes/catch.php
// Funciones para mostrar errores amigables con SweetAlert2

function mostrarErrorConexion() {
    if (!headers_sent()) {
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Error de conexión</title>";
        echo '<link rel="stylesheet" href="/EDIT/EDIT_007/assets/css/bootstrap.min.css">';
        echo '<link rel="stylesheet" href="/EDIT/EDIT_007/assets/css/all.min.css">';
        echo "<style>
            body { 
                font-family: 'Arial', sans-serif; 
                background-color: #f5f9ff; 
                display: flex; 
                justify-content: center; 
                align-items: center; 
                height: 100vh; 
                margin: 0; 
            }
            .swal2-popup { 
                border-radius: 15px !important; 
                box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; 
            }
            .swal2-title { 
                font-size: 1.8rem !important; 
                color: #2575fc !important; 
            }
            .swal2-icon.swal2-error { 
                border-color: #d81b60 !important; 
                color: #d81b60 !important; 
            }
        </style>";
        echo "</head><body>";
    }
    echo "<script src='/EDIT/EDIT_007/assets/js/sweetalert2.min.js'></script>\n";
    // Si falla el script local, intentar con CDN
    echo "<script>if(typeof Swal === 'undefined') { document.write('<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"><\/script>'); }</script>\n";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con la base de datos. Por favor, contacte con el administrador.',
                confirmButtonColor: '#2575fc',
                confirmButtonText: 'Volver',
                customClass: {
                    popup: 'shadow-lg',
                    title: 'text-danger',
                    confirmButton: 'btn btn-primary px-4'
                }
            }).then(() => { 
                window.history.back(); 
            });
        } else {
            alert('No se pudo conectar con la base de datos. Por favor, contacte con el administrador.');
            window.history.back();
        }
    });
    </script>";
    echo "</body></html>";
    exit();
}

function mostrarErrorPersonalizado($mensaje = 'Ha ocurrido un error inesperado.') {
    if (!headers_sent()) {
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Error</title>";
        echo '<link rel="stylesheet" href="/EDIT/EDIT_007/assets/css/bootstrap.min.css">';
        echo '<link rel="stylesheet" href="/EDIT/EDIT_007/assets/css/all.min.css">';
        echo "<style>
            body { 
                font-family: 'Arial', sans-serif; 
                background-color: #f5f9ff; 
                display: flex; 
                justify-content: center; 
                align-items: center; 
                height: 100vh; 
                margin: 0; 
            }
            .swal2-popup { 
                border-radius: 15px !important; 
                box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; 
            }
            .swal2-title { 
                font-size: 1.8rem !important; 
                color: #2575fc !important; 
            }
            .swal2-icon.swal2-error { 
                border-color: #d81b60 !important; 
                color: #d81b60 !important; 
            }
        </style>";
        echo "</head><body>";
    }
    echo "<script src='/EDIT/EDIT_007/assets/js/sweetalert2.min.js'></script>\n";
    // Si falla el script local, intentar con CDN
    echo "<script>if(typeof Swal === 'undefined') { document.write('<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"><\/script>'); }</script>\n";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . addslashes($mensaje) . "',
                confirmButtonColor: '#2575fc',
                confirmButtonText: 'Volver',
                customClass: {
                    popup: 'shadow-lg',
                    title: 'text-danger',
                    confirmButton: 'btn btn-primary px-4'
                }
            }).then(() => { 
                window.history.back(); 
            });
        } else {
            alert('" . addslashes($mensaje) . "');
            window.history.back();
        }
    });
    </script>";
    echo "</body></html>";
    exit();
}
