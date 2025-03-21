<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'profesor') {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear Asistencia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        #qr-reader {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }
        #qr-reader__scan_region {
            background: white;
        }
        #qr-reader__dashboard {
            padding: 10px;
        }
        .resultado {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Escanear Código QR</h2>
                        <div id="qr-reader"></div>
                        <div id="resultado" class="resultado" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Detener el escáner temporalmente
            html5QrcodeScanner.pause();

            // El decodedText será el ID del estudiante
            $.post('../../includes/asistencias/registrar.php', {
                estudiante_id: decodedText
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reanudar el escáner después de mostrar el mensaje
                        html5QrcodeScanner.resume();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        html5QrcodeScanner.resume();
                    });
                }
            })
            .fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la asistencia',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    html5QrcodeScanner.resume();
                });
            });
        }

        function onScanFailure(error) {
            // Manejar errores si es necesario
            console.warn(`Escaneo fallido: ${error}`);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader",
            { 
                fps: 10,
                qrbox: {width: 250, height: 250},
                aspectRatio: 1.0
            }
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html> 