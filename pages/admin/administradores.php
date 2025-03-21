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
    <title>Administrar Administradores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
            <h2>Administrar Administradores</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoAdmin">
                Nuevo Administrador
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaAdmins" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $db = new Database();
                            $conn = $db->connect();
                            
                            $stmt = $conn->prepare("
                                SELECT id, nombre, correo as email, created_at 
                                FROM usuarios 
                                WHERE rol_id = 1
                                ORDER BY created_at DESC
                            ");
                            $stmt->execute();
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['nombre']}</td>";
                                echo "<td>{$row['email']}</td>";
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

    <!-- Modal Nuevo Administrador -->
    <div class="modal fade" id="modalNuevoAdmin" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Administrador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoAdmin">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
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
                    <button type="button" class="btn btn-primary" onclick="guardarAdmin()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Administrador -->
    <div class="modal fade" id="modalEditarAdmin" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Administrador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarAdmin">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
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
                    <button type="button" class="btn btn-primary" onclick="actualizarAdmin()">Guardar Cambios</button>
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
            $('#tablaAdmins').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });

            // Manejar el envío del formulario de nuevo administrador
            $('#formNuevoAdmin').on('submit', function(e) {
                e.preventDefault();
                guardarAdmin();
            });

            // Editar administrador
            $('.btn-editar').click(function() {
                const id = $(this).data('id');
                editarAdmin(id);
            });

            // Eliminar administrador
            $('.btn-eliminar').click(function() {
                const id = $(this).data('id');
                eliminarAdmin(id);
            });
        });

        function guardarAdmin() {
            const formData = new FormData($('#formNuevoAdmin')[0]);
            
            // Validar campos
            const nombre = formData.get('nombre').trim();
            const email = formData.get('email').trim();
            const documento = formData.get('documento').trim();
            const password = formData.get('password');
            const confirm_password = formData.get('confirm_password');

            if (!nombre || !email || !documento || !password || !confirm_password) {
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
                url: '../../includes/administradores/crear.php',
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
                            $('#modalNuevoAdmin').modal('hide');
                            $('#formNuevoAdmin')[0].reset();
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error en la petición:', xhr);
                    let mensaje = 'Error al guardar administrador';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }

        function editarAdmin(id) {
            $.get('../../includes/administradores/obtener.php', { id: id })
                .done(function(response) {
                    if (response.success) {
                        $('#edit_id').val(response.admin.id);
                        $('#edit_nombre').val(response.admin.nombre);
                        $('#edit_email').val(response.admin.email);
                        $('#modalEditarAdmin').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Error', 'Error al obtener datos del administrador', 'error');
                });
        }

        function actualizarAdmin() {
            const formData = new FormData($('#formEditarAdmin')[0]);
            
            if (formData.get('password') && formData.get('password') !== formData.get('confirm_password')) {
                Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                return;
            }

            $.ajax({
                url: '../../includes/administradores/editar.php',
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
                            $('#modalEditarAdmin').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al actualizar el administrador', 'error');
                }
            });
        }

        function eliminarAdmin(id) {
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
                    $.post('../../includes/administradores/eliminar.php', { id: id })
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
                            Swal.fire('Error', 'Error al eliminar el administrador', 'error');
                        });
                }
            });
        }
    </script>
</body>
</html> 