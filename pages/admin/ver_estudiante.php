<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: estudiantes.php');
    exit;
}

$estudiante_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->connect();

// Obtener información del estudiante
try {
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$estudiante_id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        header('Location: estudiantes.php');
        exit;
    }

    // Obtener total de asistencias
    $stmt = $conn->prepare("SELECT COUNT(*) FROM asistencias WHERE estudiante_id = ?");
    $stmt->execute([$estudiante_id]);
    $total_asistencias = $stmt->fetchColumn();

} catch (PDOException $e) {
    header('Location: estudiantes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Estudiante - <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sistema de Asistencia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="estudiantes.php">Volver a Estudiantes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">Información del Estudiante</h2>
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?></p>
                                <p><strong>Documento:</strong> <?php echo htmlspecialchars($estudiante['documento']); ?></p>
                                <p><strong>Grupo:</strong> <?php echo $estudiante['grupo_nombre'] ? htmlspecialchars($estudiante['grupo_nombre']) : 'Sin grupo asignado'; ?></p>
                                <p><strong>Total Asistencias:</strong> <?php echo $total_asistencias; ?></p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarEstudianteModal">
                                    Editar Estudiante
                                </button>
                            </div>
                            <div class="col-md-4 text-center">
                                <img src="<?php echo $estudiante['qr_code']; ?>" alt="Código QR" class="img-fluid mb-2" style="max-width: 200px;">
                                <br>
                                <a href="<?php echo $estudiante['qr_code']; ?>" download="qr-<?php echo $estudiante['documento']; ?>.png" class="btn btn-success">
                                    Descargar QR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title mb-0">Registro de Asistencias</h3>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="refreshAsistencias">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                        </div>
                        <table id="tablaAsistencias" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Profesor</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Cambiar Grupo</h3>
                        <form id="formCambiarGrupo">
                            <div class="mb-3">
                                <label for="grupo" class="form-label">Seleccionar Grupo</label>
                                <select class="form-control" id="grupo" required>
                                    <option value="">Seleccione un grupo</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Cambiar Grupo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Estudiante -->
    <div class="modal fade" id="editarEstudianteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarEstudiante">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" 
                                   value="<?php echo htmlspecialchars($estudiante['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" 
                                   value="<?php echo htmlspecialchars($estudiante['apellidos']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="documento" class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documento" 
                                   value="<?php echo htmlspecialchars($estudiante['documento']); ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEdicion">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable de asistencias
            const tabla = $('#tablaAsistencias').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '../../includes/estudiantes/obtener_asistencias.php',
                    type: 'GET',
                    data: function(d) {
                        d.estudiante_id = <?php echo $estudiante_id; ?>;
                    }
                },
                columns: [
                    { data: 'fecha_hora' },
                    { data: 'profesor_nombre' }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
                    emptyTable: 'No hay registros de asistencia',
                    zeroRecords: 'No se encontraron registros'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
            });

            // Botón de actualizar
            $('#refreshAsistencias').click(function() {
                const $btn = $(this);
                const $icon = $btn.find('i');
                
                // Deshabilitar botón y agregar animación
                $btn.prop('disabled', true);
                $icon.addClass('fa-spin');
                
                // Recargar datos
                tabla.ajax.reload(function() {
                    // Restaurar botón después de la recarga
                    setTimeout(function() {
                        $btn.prop('disabled', false);
                        $icon.removeClass('fa-spin');
                    }, 500);
                });
            });

            // Cargar grupos en el select
            $.get('../../includes/grupos/listar.php', function(grupos) {
                const select = $('#grupo');
                grupos.forEach(grupo => {
                    const selected = grupo.id == <?php echo $estudiante['grupo_id'] ?? 'null'; ?> ? 'selected' : '';
                    select.append(`<option value="${grupo.id}" ${selected}>${grupo.nombre}</option>`);
                });
            });

            // Cambiar grupo
            $('#formCambiarGrupo').on('submit', function(e) {
                e.preventDefault();
                const grupo_id = $('#grupo').val();

                $.post('../../includes/estudiantes/cambiar_grupo.php', {
                    estudiante_id: <?php echo $estudiante_id; ?>,
                    grupo_id: grupo_id
                }, function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Grupo actualizado correctamente',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });

            // Guardar edición del estudiante
            $('#btnGuardarEdicion').click(function() {
                const datos = {
                    id: <?php echo $estudiante_id; ?>,
                    nombre: $('#nombre').val(),
                    apellidos: $('#apellidos').val(),
                    documento: $('#documento').val()
                };

                if (!datos.nombre || !datos.apellidos || !datos.documento) {
                    Swal.fire('Error', 'Todos los campos son requeridos', 'error');
                    return;
                }

                $.post('../../includes/estudiantes/editar.php', datos, function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Estudiante actualizado correctamente',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });
        });
    </script>
</body>
</html> 