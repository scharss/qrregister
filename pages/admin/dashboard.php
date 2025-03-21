<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .icon {
            width: 64px;
            height: 64px;
            margin-bottom: 15px;
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
        <h2 class="mb-4">Panel de Administración</h2>
        <div class="row">
            <!-- Gestión de Grupos -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" fill="#0d6efd"/>
                        </svg>
                        <h3>Gestión de Grupos</h3>
                        <p class="text-muted">Administrar grupos y asignaciones</p>
                        <a href="grupos.php" class="btn btn-primary">Administrar</a>
                    </div>
                </div>
            </div>
            
            <!-- Gestión de Estudiantes -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L1 9l11 7 9-5.7V17h2V9L12 2zm0 4.3L5.5 9 12 12.7 18.5 9 12 6.3z" fill="#198754"/>
                            <path d="M3 16v2h2v-2H3zm4 0v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" fill="#198754"/>
                        </svg>
                        <h5 class="card-title">Gestión de Estudiantes</h5>
                        <p class="card-text">Administrar estudiantes y códigos QR</p>
                        <a href="estudiantes.php" class="btn btn-success">Administrar</a>
                    </div>
                </div>
            </div>
            
            <!-- Gestión de Profesores -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 17V9H4v8h16zm0-10c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V9c0-1.1.9-2 2-2h16z" fill="#0dcaf0"/>
                            <path d="M12 14c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3z" fill="#0dcaf0"/>
                        </svg>
                        <h5 class="card-title">Gestión de Profesores</h5>
                        <p class="card-text">Administrar profesores y permisos</p>
                        <a href="profesores.php" class="btn btn-info">Administrar</a>
                    </div>
                </div>
            </div>
            
            <!-- Gestión de Administradores -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z" fill="#ffc107"/>
                        </svg>
                        <h5 class="card-title">Gestión de Administradores</h5>
                        <p class="card-text">Administrar usuarios administradores</p>
                        <a href="administradores.php" class="btn btn-warning">Administrar</a>
                    </div>
                </div>
            </div>
            
            <!-- Reportes -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z" fill="#dc3545"/>
                            <path d="M7 12h2v5H7zm4-3h2v8h-2zm4-3h2v11h-2z" fill="#dc3545"/>
                        </svg>
                        <h5 class="card-title">Reportes</h5>
                        <p class="card-text">Ver reportes de asistencia</p>
                        <a href="reportes.php" class="btn btn-danger">Ver Reportes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 