<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Profesores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../includes/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Profesores</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoProfesor">
                Nuevo Profesor
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label for="length" class="col-form-label">Mostrar</label>
                        </div>
                        <div class="col-auto">
                            <select name="length" id="length" class="form-select">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="length" class="col-form-label">registros</label>
                        </div>
                        <div class="col-md-4 ms-auto">
                            <div class="input-group">
                                <input type="search" class="form-control" placeholder="Buscar..." id="searchBox">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tablaProfesores" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Correo</th>
                                <th>Documento</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $db = new Database();
                            $conn = $db->connect();
                            
                            $stmt = $conn->prepare("
                                SELECT id, nombre, apellidos, correo, documento, created_at 
                                FROM usuarios 
                                WHERE rol_id = 2
                                ORDER BY created_at DESC
                            ");
                            $stmt->execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['nombre']}</td>";
                                echo "<td>{$row['apellidos']}</td>";
                                echo "<td>{$row['correo']}</td>";
                                echo "<td>{$row['documento']}</td>";
                                echo "<td>{$row['created_at']}</td>";
                                echo "<td>
                                        <button class='btn btn-info btn-sm btn-editar' data-id='{$row['id']}'>Editar</button>
                                        <button class='btn btn-danger btn-sm btn-eliminar' data-id='{$row['id']}'>Eliminar</button>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Profesor -->
    <div class="modal fade" id="modalNuevoProfesor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Profesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoProfesor">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarProfesor()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Profesor -->
    <div class="modal fade" id="modalEditarProfesor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Profesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarProfesor">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" id="edit_apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="correo" id="edit_correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" id="edit_documento" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarProfesor()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tablaProfesores').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });

            // Manejar el envío del formulario de nuevo profesor
            $('#formNuevoProfesor').on('submit', function(e) {
                e.preventDefault();
                guardarProfesor();
            });

            // Editar profesor
            $('.btn-editar').click(function() {
                const id = $(this).data('id');
                editarProfesor(id);
            });

            // Eliminar profesor
            $('.btn-eliminar').click(function() {
                const id = $(this).data('id');
                eliminarProfesor(id);
            });
        });

        function guardarProfesor() {
            const formData = new FormData($('#formNuevoProfesor')[0]);
            
            // Validar campos
            const nombre = formData.get('nombre').trim();
            const apellidos = formData.get('apellidos').trim();
            const correo = formData.get('correo').trim();
            const documento = formData.get('documento').trim();
            const password = formData.get('password');
            const confirm_password = formData.get('confirm_password');

            if (!nombre || !apellidos || !correo || !documento || !password || !confirm_password) {
                Swal.fire('Error', 'Todos los campos son requeridos', 'error');
                return;
            }

            if (!/^\d+$/.test(documento)) {
                Swal.fire('Error', 'El documento debe contener solo números', 'error');
                return;
            }

            if (password.length < 6) {
                Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }

            if (password !== confirm_password) {
                Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                return;
            }

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar datos al servidor
            $.ajax({
                url: '../../includes/profesores/crear.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message
                        }).then(() => {
                            $('#modalNuevoProfesor').modal('hide');
                            $('#formNuevoProfesor')[0].reset();
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error en la petición:', xhr);
                    let mensaje = 'Error al guardar profesor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }

        function editarProfesor(id) {
            $.get('../../includes/profesores/obtener.php', { id: id })
                .done(function(response) {
                    if (response.success) {
                        $('#edit_id').val(response.profesor.id);
                        $('#edit_nombre').val(response.profesor.nombre);
                        $('#edit_apellidos').val(response.profesor.apellidos);
                        $('#edit_correo').val(response.profesor.correo);
                        $('#edit_documento').val(response.profesor.documento);
                        $('#modalEditarProfesor').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error al obtener datos del profesor', 'error');
                });
        }

        function actualizarProfesor() {
            const formData = new FormData($('#formEditarProfesor')[0]);
            
            // Validar campos requeridos
            const nombre = formData.get('nombre').trim();
            const apellidos = formData.get('apellidos').trim();
            const correo = formData.get('correo').trim();
            const documento = formData.get('documento').trim();
            const password = formData.get('password');
            const confirm_password = formData.get('confirm_password');

            if (!nombre || !apellidos || !correo || !documento) {
                Swal.fire('Error', 'Los campos nombre, apellidos, correo y documento son requeridos', 'error');
                return;
            }

            if (!/^\d+$/.test(documento)) {
                Swal.fire('Error', 'El documento debe contener solo números', 'error');
                return;
            }

            if (password) {
                if (password.length < 6) {
                    Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
                    return;
                }

                if (password !== confirm_password) {
                    Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                    return;
                }
            }

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Actualizando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '../../includes/profesores/editar.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $('#modalEditarProfesor').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error en la petición:', xhr);
                    let mensaje = 'Error al actualizar el profesor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }

        function eliminarProfesor(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('../../includes/profesores/eliminar.php', { id: id })
                        .done(function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error', 'Error al eliminar el profesor', 'error');
                        });
                }
            });
        }
    </script>
</body>
</html> 